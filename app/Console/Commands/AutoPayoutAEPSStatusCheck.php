<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AutoPayoutAEPSStatusCheck extends Command
{
    protected $signature = 'payoutaeps:auto-status';
    protected $description = 'Auto check pending payout status and send callback';

    public function handle()
    {
        $this->info("Starting Auto Payout Status Check...");

       $transactions = DB::table('aeps_stlm')
    ->whereIn('status', ['Pending', 'Initiated'])
    ->where('callback_sent', 0)
    ->where('orderId','!=',null)
    ->where('updated_at', '<', now()->subSeconds(30))
    ->orderByDesc('id')   // latest records
    ->limit(20)
    ->get();

        if ($transactions->isEmpty()) {
            $this->info("No pending transactions found.");
            return;
        }

        foreach ($transactions as $txn) {

            try {

                $this->info("Checking Order ID: " . $txn->orderId);

                // ✅ Correct Merchant Fetch
                $merchant = DB::table('remittances')
                    ->where('remId', $txn->remId)
                    ->first(); // use first()

                if (!$merchant || empty($merchant->callback_url)) {
                    Log::warning("Callback URL not found", [
                        'order_id' => $txn->orderId,
                        'merchant' =>$merchant
                    ]);
                    continue;
                }

                $callbackUrl = $merchant->callback_url;

                // 🔹 Call Status API
                $response = \App\Helpers\PayoutV6Helper::initiateStatus([
                    'order_id' => $txn->orderId
                ]);
                    //             {
                    //   "response": {
                    //     "status": true,
                    //     "message": "Payment Status.",
                    //     "data": {
                    //       "message": "Payment Status.",
                    //       "success": true,
                    //       "result": {
                    //         "status": "success",
                    //         "transactionId": "CTCP668AC0959FA084B2",
                    //         "amount": 100,
                    //         "utr": "606514413961"
                    //       },
                    //       "code": 200,
                    //       "timestamp": "2026-03-06T08:54:57.086Z"
                    //     }
                    //   }
                    // }

                Log::info("Auto Payout Status Response", [
                    'order_id' => $txn->orderId,
                    'response' => $response
                ]);

             if (empty($response['success']) || empty($response['result'])) {
                        continue;
                    }

                    $apiStatus = strtoupper($response['result']['status'] ?? 'FAILED');

                      if ($apiStatus === 'SUCCESS') {
                        $finalStatus = 'Success';
                    } elseif ($apiStatus === 'FAILED') {
                        $finalStatus = 'Failed';
                    } else {
                        $finalStatus = 'Pending';
                    }

                // 🔹 Update DB
                DB::table('aeps_stlm')
                    ->where('id', $txn->id)
                    ->update([
                        'status'       => $finalStatus,
                        'bank_ref_no' => $response['result']['utr'] ?? null,
                        'responseBody' => json_encode($response),
                        'updated_at'   => now()
                    ]);

                // 🔹 Send Callback only if final state
                if (in_array($finalStatus, ['Success', 'Failed'])) {

                    $payload = [
                        'service' =>'STLM',
                        'order_id' => $txn->orderId,
                        'payment_id' => $txn->payment_id,
                        'reference_id' => $txn->refId,
                        'status'   => $finalStatus,
                        'amount'   => $txn->amount,
                        'rrn' => $response['result']['utr'] ?? null,
                        'message' => $response['message'] ?? ''
                                                ];

                    $callbackResponse = Http::timeout(10)->post($callbackUrl, $payload);

                    Log::info("Callback Response", [
                        'order_id' => $txn->orderId,
                        'callback_url' => $callbackUrl,
                        'payload' => $payload,
                        'response' => $callbackResponse->body()
                    ]);

                    if ($callbackResponse->successful()) {

                        DB::table('aeps_stlm')
                            ->where('id', $txn->id)
                            ->update([
                                'callback_sent' => 1
                            ]);

                        $this->info("Callback sent successfully for Order ID: " . $txn->orderId);
                    }
                }

            } catch (\Exception $e) {

                Log::error("Auto Payout Status Error", [
                    'order_id' => $txn->orderId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Auto Payout Status Check Completed.");
    }
}