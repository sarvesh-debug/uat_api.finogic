<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayinAfterDelay implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $txnId;

    public function __construct($txnId)
    {
        $this->txnId = $txnId;
    }

    public function handle()
    {
        Log::info("PAYIN DELAY JOB STARTED", [
            'txnId' => $this->txnId
        ]);

        $txn = DB::table('pgmanage')->where('id', $this->txnId)->first();

        if (!$txn) {

            Log::error("PAYIN JOB FAILED → TRANSACTION NOT FOUND", [
                'txnId' => $this->txnId
            ]);

            return;
        }

        /*
        Duplicate Protection
        */
        if ($txn->callback_processed == 1) {

            Log::warning("PAYIN JOB DUPLICATE BLOCKED", [
                'orderId' => $txn->orderId
            ]);

            return;
        }

        if ($txn->status !== "SUCCESS") {

            Log::warning("PAYIN JOB STATUS NOT SUCCESS", [
                'orderId' => $txn->orderId
            ]);

            return;
        }

        DB::beginTransaction();

        try {

            $remittance = DB::table('remittances')
                ->where('remId', $txn->remId)
                ->lockForUpdate()
                ->first();

            if (!$remittance) {

                Log::error("REMITTANCE NOT FOUND", [
                    'remId' => $txn->remId
                ]);

                DB::rollBack();
                return;
            }

            $amount = (float) $txn->amount;

            $tds = 0;
            $charges = 0;

            /*
            Commission Calculation
            */

            $commissions = DB::table('commissions')
                ->where('packagesId', $remittance->packageId)
                ->where('service', 'PAYIN')
                ->get();

            foreach ($commissions as $item) {

                $from = (float) $item->from_amount;
                $to   = (float) $item->to_amount;

                if ($amount >= $from && $amount <= $to) {

                    $charges = $item->charge_in === 'Percentage'
                        ? $amount * ((float)$item->charge) / 100
                        : (float)$item->charge;

                    $tds = $item->tds_in === 'Percentage'
                        ? $charges * ((float)$item->tds) / 100
                        : (float)$item->tds;

                    break;
                }
            }

            $netAmount = $amount - ($charges + $tds);

            $openingBal = $remittance->amount;
            $closingBal = $openingBal + $netAmount;

            /*
            Update PG Transaction
            */

            DB::table('pgmanage')
                ->where('id', $txn->id)
                ->update([
                    'tds' => $tds,
                    'charges' => $charges,
                    'openingBalance' => $openingBal,
                    'closingBalance' => $closingBal,
                    'callback_processed' => 1,
                    'updated_at' => now()
                ]);

            /*
            Wallet Update
            */

            DB::table('remittances')
                ->where('remId', $txn->remId)
                ->increment('amount', $netAmount);

            DB::commit();

            Log::info("PAYIN WALLET UPDATED", [
                'orderId' => $txn->orderId,
                'netAmount' => $netAmount
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error("PAYIN JOB FAILED", [
                'error' => $e->getMessage(),
                'txnId' => $this->txnId
            ]);

            return;
        }

        /*
        CLIENT CALLBACK
        */

        if (!empty($txn->callbackUrl) && $txn->client_callback_sent == 0) {

            try {

                $payload = [
                    'method' => 'PAYIN',
                    'refId' => $txn->refId,
                    'txnId' => $txn->txnId,
                    'orderId' => $txn->orderId,
                    'amount' => $txn->amount,
                    'netAmount' => $netAmount,
                    'status' => $txn->status,
                    'tds' => $tds,
                    'charges' => $charges,
                    'message' => 'Payment Status Callback'
                ];

                Http::timeout(20)->post($txn->callbackUrl, $payload);

                DB::table('pgmanage')
                    ->where('id', $txn->id)
                    ->update([
                        'client_callback_sent' => 1
                    ]);

                Log::info("CLIENT CALLBACK SENT", [
                    'url' => $txn->callbackUrl,
                    'payload' => $payload
                ]);

            } catch (\Exception $e) {

                Log::error("CLIENT CALLBACK FAILED", [
                    'url' => $txn->callbackUrl,
                    'error' => $e->getMessage()
                ]);
            }
        }

        Log::info("PAYIN DELAY JOB COMPLETED", [
            'orderId' => $txn->orderId
        ]);
    }
}