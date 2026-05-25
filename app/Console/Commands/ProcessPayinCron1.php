<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ProcessPayinCron1 extends Command
{
    protected $signature = 'payin1:process';
    protected $description = 'Process Payin After Delay';

    public function handle()
    {
        Log::info("CRON STARTED");

        $txns = DB::table('pgmanage1')
            ->where('status', 'SUCCESS')
            ->where('callback_processed', 0)
            ->where('created_at', '<=', now()->subMinutes(4))
            ->limit(50)
            ->get();

        foreach ($txns as $txn) {

            DB::beginTransaction();

            try {

                $remittance = DB::table('remittances')
                    ->where('remId', $txn->remId)
                    ->lockForUpdate()
                    ->first();

                if (!$remittance) {
                    DB::rollBack();
                    continue;
                }

                $amount = (float) $txn->amount;

                $tds = 0;
                $charges = 0;

                $commissions = DB::table('commissions')
                    ->where('packagesId', $remittance->packageId)
                    ->where('service', 'PAYINP1')
                    ->get();

                foreach ($commissions as $item) {

                    if ($amount >= $item->from_amount && $amount <= $item->to_amount) {

                        $charges = $item->charge_in === 'Percentage'
                            ? $amount * $item->charge / 100
                            : $item->charge;

                        $tds = $item->tds_in === 'Percentage'
                            ? $charges * $item->tds / 100
                            : $item->tds;

                        break;
                    }
                }

                $netAmount = $amount - ($charges + $tds);

                DB::table('pgmanage1')
                    ->where('id', $txn->id)
                    ->update([
                        'tds' => $tds,
                        'charges' => $charges,
                        'openingBalance' => $remittance->amount,
                        'closingBalance' => $remittance->amount + $netAmount,
                        'callback_processed' => 1
                    ]);

                            
                                    DB::table('remittances')
                    ->where('remId', $txn->remId)
                    ->increment('amount', $netAmount);

                Log::info("WALLET UPDATED", [
                    'remId'        => $txn->remId,
                    'orderId'      => $txn->orderId,
                    'amount_added' => $netAmount,
                    'opening_bal'  => $remittance->amount,
                    'closing_bal'  => $remittance->amount + $netAmount
                ]);

                DB::commit();

                // CALLBACK
                if (!empty($txn->callbackUrl) && $txn->client_callback_sent == 0) {

                    $payload = [
                        'method' => 'PAYINP1',
                        'refId' => $txn->refId,
                        'txnId' => $txn->txnId,
                        'settlement_status' => 'SETTLED',
                        'orderId' => $txn->orderId,
                        'amount' => $txn->amount,
                        'netAmount' => $netAmount,
                        'status' => $txn->status,
                        'tds' => $tds,
                        'charges' => $charges,
                    ];

                    Http::post($txn->callbackUrl, $payload);

                    DB::table('pgmanage1')
                        ->where('id', $txn->id)
                        ->update(['client_callback_sent' => 1]);
                }
                Log::info('Callback TO Client',[
                    'payload' =>$payload,
                    'url' =>$txn->callbackUrl

                ]);

            } catch (\Exception $e) {

                DB::rollBack();

                Log::error("CRON ERROR", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("CRON COMPLETED");
    }
}