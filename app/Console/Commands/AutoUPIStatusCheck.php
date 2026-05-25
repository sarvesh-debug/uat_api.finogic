<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AutoUPIStatusCheck extends Command
{
    protected $signature = 'upi:auto-status';
    protected $description = 'Auto check pending UPI payout status and send callback';

    public function handle()
    {
        $this->info("Starting Auto UPI Status Check...");

        $transactions = DB::table('upipayout')
            ->whereIn('status', ['Pending', 'Initiated'])
            ->where('callback_sent', 0)
            ->where('updated_at', '<', now()->subSeconds(30))
            ->limit(20)
            ->get();

        if ($transactions->isEmpty()) {
            $this->info("No pending transactions found.");
            return;
        }

        foreach ($transactions as $txn) {

            try {

                $this->info("Checking Order ID: " . $txn->order_id);

                // ✅ Correct Merchant Fetch
                $merchant = DB::table('remittances')
                    ->where('remId', $txn->remId)
                    ->first(); // use first()

                if (!$merchant || empty($merchant->callback_url)) {
                    Log::warning("Callback URL not found", [
                        'order_id' => $txn->order_id
                    ]);
                    continue;
                }

                $callbackUrl = $merchant->callback_url;

                // 🔹 Call Status API
                $response = \App\Helpers\PayoutV6Helper::initiateUPIStatus([
                    'order_id' => $txn->order_id
                ]);
            //     $response = [
            //     "status" => true,
            //     "message" => "Order already Processed",
            //     "data" => [
            //         "success" => true,
            //         "status" => "SUCCESS",
            //         "amount" => $txn->amount,
            //         "orderId" => $txn->order_id,
            //         "rrn" => "123456789012",
            //         "code" => 200,
            //         "timestamp" => now()
            //     ]
            // ];

                Log::info("Auto UPI Status Response", [
                    'order_id' => $txn->order_id,
                    'response' => $response
                ]);

                if (empty($response['status']) || empty($response)) {
                    continue;
                }

                $apiStatus = strtoupper($response['status'] ?? 'FAILED');

                if ($apiStatus === 'SUCCESS') {
                    $finalStatus = 'Success';
                } elseif ($apiStatus === 'FAILED') {
                    $finalStatus = 'Failed';
                } else {
                    $finalStatus = 'Pending';
                }

                // 🔹 Update DB
                DB::table('upipayout')
                    ->where('id', $txn->id)
                    ->update([
                        'status'       => $finalStatus,
                        'bank_ref_no'  => $response['rrn'] ?? null,
                        'responseBody' => json_encode($response),
                        'updated_at'   => now()
                    ]);

                // 🔹 Send Callback only if final state
                if (in_array($finalStatus, ['Success', 'Failed'])) {

                    $payload = [
                        'service' =>'UPI-PAYOUT',
                        'order_id' => $txn->order_id,
                        'payment_id' => $txn->payment_id,
                        'reference_id' => $txn->refId,
                        'status'   => $finalStatus,
                        'amount'   => $txn->amount,
                        'rrn'      => $response['rrn'] ?? null,
                        'message'  => $response['message'] ?? ''
                    ];

                    $callbackResponse = Http::timeout(10)->post($callbackUrl, $payload);

                    Log::info("Callback Response", [
                        'order_id' => $txn->order_id,
                        'callback_url' => $callbackUrl,
                        'payload' => $payload,
                        'response' => $callbackResponse->body()
                    ]);

                    if ($callbackResponse->successful()) {

                        DB::table('upipayout')
                            ->where('id', $txn->id)
                            ->update([
                                'callback_sent' => 1
                            ]);

                        $this->info("Callback sent successfully for Order ID: " . $txn->order_id);
                    }
                }

            } catch (\Exception $e) {

                Log::error("Auto UPI Status Error", [
                    'order_id' => $txn->order_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->info("Auto UPI Status Check Completed.");
    }
}