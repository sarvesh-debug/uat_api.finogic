<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class AutoPG2StatusCheck extends Command
{
    protected $signature = 'pg2:auto-status';
    protected $description = 'Auto check pending PG2 payout status and send callback';

    public function handle()
    {
        $this->info("Starting Auto PG_P2 Status Check...");

        $transactions = DB::table('pgmanage2')
    ->whereIn('status', ['PENDING','Initiated'])
    ->where('callback_sent', 0)
    ->whereNotNull('orderId')
    ->where('orderId','!=','')
    ->where('orderId','!=','N/A')
    ->where('updated_at','<', now()->subSeconds(30))
    ->orderBy('id','desc')   // last inserted first
    ->limit(20)
    ->get();

        if ($transactions->isEmpty()) {
            $this->info("No pending transactions found.");
            return;
        }

        foreach ($transactions as $txn) {

            try {

                $this->info("Checking Order ID: ".$txn->orderId);

                // Merchant fetch
                $merchant = DB::table('remittances')
                    ->where('remId', $txn->remId)
                    ->first();

                if (!$merchant || empty($merchant->callback_url)) {

                    Log::warning("Callback URL not found", [
                        'order_id' => $txn->orderId
                    ]);

                    continue;
                }

                $callbackUrl = $txn->callbackUrl;

                /*
                |--------------------------------
                | CALL PG STATUS API
                |--------------------------------
                */

                $response = \App\Helpers\PayoutV6Helper::pgStatus([
                    'order_id' => $txn->orderId
                ]);

                Log::info("Auto PG_P2 Status Response", [
                    'order_id' => $txn->orderId,
                    'response' => $response
                ]);

                if (empty($response['success']) || empty($response['data'])) {
                    Log::warning("Invalid PG_P2 API Response", [
                        'order_id' => $txn->orderId
                    ]);
                    continue;
                }

                $data = $response['data'];

                /*
                |--------------------------------
                | STATUS MAPPING
                |--------------------------------
                */

                $apiStatus = strtoupper($data['status'] ?? 'FAILED');

                $statusMap = [
                    'SUCCESS' => 'SUCCESS',
                    'FAILURE' => 'FAILED',
                    'FAILED'  => 'FAILED',
                    'PENDING' => 'PENDING',
                    'PROCESSING' => 'PENDING'
                ];

                $finalStatus = $statusMap[$apiStatus] ?? 'PENDING';

                /*
                |--------------------------------
                | UPDATE DATABASE
                |--------------------------------
                */

                $updated = DB::table('pgmanage2')
                    ->where('id', $txn->id)
                    ->where('status','!=',$finalStatus)
                    ->update([
                        'status' => $finalStatus,
                        'bank_ref_no' => $data['rrn'] ?? null,
                        'responseData' => json_encode($response),
                        'updated_at' => now()
                    ]);

                Log::info("PG_P2 Update Result", [
                    'id' => $txn->id,
                    'order_id' => $txn->orderId,
                    'apiStatus' => $apiStatus,
                    'finalStatus' => $finalStatus,
                    'updated_rows' => $updated
                ]);

                /*
                |--------------------------------
                | SEND CALLBACK IF FINAL
                |--------------------------------
                */

               if (in_array($finalStatus, ['SUCCESS','FAILED'])){

                    // $payload = [
                    //     'service' => 'PG',
                    //     'order_id' => $txn->orderId,
                    //     'payment_id' => $txn->orderId,
                    //     'reference_id' => $txn->refId,
                    //     'status' => $finalStatus,
                    //     'amount' => $txn->amount,
                    //     'rrn' => $data['rrn'] ?? null,
                    //     'message' => $data['responseDescription'] ?? ''
                    // ];

                     $payload = [
                'method'    => 'PAYINP1',
                'refId'     => $txn->orderId,
                'txnId'     => $data['rrn'] ?? null,
                'orderId'   => $txn->orderId,
                'amount'    => $txn->amount,
                'netAmount' => $finalStatus,
                'message'   => 'Payment Status Callback',
                'tds'       => $tds ?? 0 ,
                'gst'       => $charges ?? 0,
                'user'      => $userData ?? 'include user info', // include user info
            ];

                    $callbackResponse = Http::timeout(10)
                        ->asJson()
                        ->post($callbackUrl, $payload);

                    Log::info("Callback Debug",[
                        'url'=>$callbackUrl,
                        'payload'=>$payload,
                        'status'=>$callbackResponse->status(),
                        'body'=>$callbackResponse->body()
                    ]);

                    Log::info("Callback Response", [
                        'order_id' => $txn->orderId,
                        'callback_url' => $callbackUrl,
                        'payload' => $payload,
                        'response' => $callbackResponse->body(),
                       
                    ]);

                    if ($callbackResponse->successful()) {

                        DB::table('pgmanage2')
                            ->where('id', $txn->id)
                            ->update([
                                'callback_sent' => 1
                            ]);

                        $this->info("Callback sent successfully for Order ID: ".$txn->orderId);
                    }

                }

            } catch (\Exception $e) {

                Log::error("Auto PG_P2 Status Error", [
                    'order_id' => $txn->orderId,
                    'error' => $e->getMessage()
                ]);

            }

        }

        $this->info("Auto PG_P2 Status Check Completed.");
    }
}