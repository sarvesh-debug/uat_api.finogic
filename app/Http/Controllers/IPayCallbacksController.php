<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IPayCallbacksController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | PAYOUT CALLBACK
    |--------------------------------------------------------------------------
    */

    public function callback(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | LOG CALLBACK REQUEST
        |--------------------------------------------------------------------------
        */

        Log::channel('IPCallBack')->info(
            "iPay Callback Request Received",
            [
                'headers' => $request->headers->all(),
                'payload' => $request->all(),
                'ip'      => $request->ip()
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | GET CALLBACK DATA
        |--------------------------------------------------------------------------
        */

        $callbackData = $request->all();

        $event = $callbackData['event'] ?? null;

        /*
        |--------------------------------------------------------------------------
        | EVENT NOT FOUND
        |--------------------------------------------------------------------------
        */

        if (!$event) {

            Log::warning("Callback Event Missing", [
                'payload' => $callbackData
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Event not found'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | PAYOUT SUCCESS CALLBACK
        |--------------------------------------------------------------------------
        */

        if ($event == 'payout.transfer.success') {

            /*
            |--------------------------------------------------------------------------
            | RESPONSE FORMAT
            |--------------------------------------------------------------------------
            |
            | {
            |   "event":"payout.transfer.success",
            |   "code":"0x0200",
            |   "message":"Transaction Successful",
            |   "data":{...}
            | }
            |
            */

            $data = $callbackData['data'] ?? [];

            /*
            |--------------------------------------------------------------------------
            | FETCH VALUES
            |--------------------------------------------------------------------------
            */

            $orderRefId   = $data['orderRefId'] ?? null;

            $clientRefId  = $data['clientRefId'] ?? null;

            $contactId    = $data['contactId'] ?? null;

            $firstName    = $data['firstName'] ?? '';

            $lastName     = $data['lastName'] ?? '';

            $email        = $data['email'] ?? '';

            $phone        = $data['phone'] ?? '';

            $amount       = $data['amount'] ?? 0;

            $status       = $data['status'] ?? 'pending';

            $utr          = $data['utr'] ?? null;

            $accountNo    = $data['accountNumber'] ?? '';

            $ifsc         = $data['accountIFSC'] ?? '';

            $message      = $callbackData['message'] ?? '';

            /*
            |--------------------------------------------------------------------------
            | VALIDATE ORDER ID
            |--------------------------------------------------------------------------
            */

            if (!$orderRefId) {

                Log::warning("Callback OrderRefId Missing", [
                    'payload' => $callbackData
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'OrderRefId missing'
                ], 400);
            }

            /*
            |--------------------------------------------------------------------------
            | FIND TRANSACTION
            |--------------------------------------------------------------------------
            */

            $transaction = DB::table('xpresspayout')
                ->where('bank_ref_no', $orderRefId)
                ->first();

            /*
            |--------------------------------------------------------------------------
            | TRANSACTION NOT FOUND
            |--------------------------------------------------------------------------
            */

            if (!$transaction) {

                Log::warning("Payout Callback Transaction Not Found", [

                    'orderRefId'  => $orderRefId,

                    'clientRefId' => $clientRefId,

                    'payload'     => $callbackData
                ]);

                return response()->json([
                    'status'  => false,
                    'message' => 'Transaction not found'
                ], 404);
            }

            /*
            |--------------------------------------------------------------------------
            | ALREADY SUCCESS
            |--------------------------------------------------------------------------
            */

            if ($transaction->status == 'Success') {

                Log::info("Callback Already Processed", [

                    'orderRefId' => $orderRefId,

                    'txnId'      => $transaction->refId
                ]);

                return response()->json([
                    'status'  => true,
                    'message' => 'Already processed'
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | STATUS MAPPING
            |--------------------------------------------------------------------------
            */

            $providerStatus = strtolower($status);

            $finalStatus = 'Pending';

            if (
                $providerStatus == 'processed' ||
                $providerStatus == 'success'
            ) {

                $finalStatus = 'Success';

            } elseif (
                $providerStatus == 'failed' ||
                $providerStatus == 'reversed'
            ) {

                $finalStatus = 'Failed';

            } elseif (
                $providerStatus == 'queued' ||
                $providerStatus == 'pending'
            ) {

                $finalStatus = 'Pending';
            }

            DB::beginTransaction();

            try {

                /*
                |--------------------------------------------------------------------------
                | UPDATE PAYOUT TABLE
                |--------------------------------------------------------------------------
                */

                DB::table('xpresspayout')
                    ->where('id', $transaction->id)
                    ->update([

                        'status'         => $finalStatus,

                        'bank_ref_no'    => $utr,

                        'responseBody'   => json_encode($callbackData),

                        'updated_at'     => now(),
                    ]);

                /*
                |--------------------------------------------------------------------------
                | FAILED REFUND
                |--------------------------------------------------------------------------
                */

                if ($finalStatus == 'Failed') {

                    $refundAmount =
                        $transaction->amount +
                        $transaction->charge +
                        $transaction->tds;

                    DB::table('remittances')
                        ->where('remId', $transaction->remId)
                        ->increment(
                            'amount',
                            $refundAmount
                        );

                    Log::info("Payout Refund Processed", [

                        'remId'  => $transaction->remId,

                        'amount' => $refundAmount,

                        'refId'  => $transaction->refId
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | GET CALLBACK URL
                |--------------------------------------------------------------------------
                */

                $callbackUrl = DB::table('remittances')
                    ->where('remId', $transaction->remId)
                    ->value('callback_url');

                /*
                |--------------------------------------------------------------------------
                | CALLBACK PAYLOAD
                |--------------------------------------------------------------------------
                */

                $merchantPayload = [

                    'service'          => 'PAYOUT',

                    'status'           => $finalStatus,

                    'message'          => $message,

                    'txnId'            => $transaction->refId,

                    'providerTxnId'    => $clientRefId,

                    'orderRefId'       => $orderRefId,

                    'utr'              => $utr,

                    'amount'           => $amount,

                    'beneficiary_name' =>
                        trim($firstName . ' ' . $lastName),

                    'account_number'   => $accountNo,

                    'ifsc'             => $ifsc,

                    'contactId'        => $contactId,

                    'phone'            => $phone,

                    'email'            => $email,
                ];

                /*
                |--------------------------------------------------------------------------
                | SEND CALLBACK TO MERCHANT
                |--------------------------------------------------------------------------
                */

                if ($callbackUrl) {

                    $merchantResponse = Http::timeout(15)
                        ->post(
                            $callbackUrl,
                            $merchantPayload
                        );

                    Log::channel('IPCallBack')->info(
                        "Merchant Callback Response",
                        [

                            'callback_url' => $callbackUrl,

                            'payload'      => $merchantPayload,

                            'response'     => $merchantResponse->body(),

                            'status_code'  => $merchantResponse->status()
                        ]
                    );

                } 
                elseif($event=='paycc.request.success')
                    {
                         Log::channel('fundtransfer')->info("PG CALLBACK RECEIVED", [
                            'payload' => $request->all(),
                            'ip'      => $request->ip()
                        ]);

                        // ---------------------------------------------------------
                        // Map webhook fields
                        // ---------------------------------------------------------

                        $txnId   = $request->clientRefId ?? null;
                        $status  = $request->code ?? 'FAILED';
                        $amount  = (float)($request->amount ?? 0);
                        $orderId = $request->orderId ?? null;
                        $utr     = $request->utr ?? null;

                        // ---------------------------------------------------------
                        // Fetch Transaction
                        // ---------------------------------------------------------

                        $txn = DB::table('pgmanage')
                            ->where('txnId', $txnId)
                            ->first();

                        if (!$txn) {

                            Log::warning("PG CALLBACK → Transaction Not Found", [
                                'txnId'   => $txnId,
                                'orderId' => $orderId ?? $txnId
                            ]);

                            return response()->json([
                                'status'  => false,
                                'message' => 'Transaction not found.'
                            ], 404);
                        }

                        // ---------------------------------------------------------
                        // DUPLICATE PROTECTION (ONLY FINAL)
                        // ---------------------------------------------------------

                        if ($txn->callback_processed == 1) {

                            Log::warning("FINAL CALLBACK ALREADY PROCESSED", [
                                'orderId' => $orderId
                            ]);

                            return response()->json([
                                'status' => true,
                                'message' => 'Already processed'
                            ]);
                        }

                        // ---------------------------------------------------------
                        // Update Callback Response
                        // ---------------------------------------------------------

                        DB::table('pgmanage')
                            ->where('id', $txn->id)
                            ->update([
                                'txnId'        => $txnId,
                                'status'       => $status,
                                'amount'       => $amount,
                                'bank_ref_no'  => $utr,
                                'responseData' => json_encode($request->all(), JSON_UNESCAPED_SLASHES),
                                'updated_at'   => now()
                            ]);

                        Log::info("PG CALLBACK UPDATED", [
                            'txnId'   => $txnId,
                            'status'  => $status
                        ]);

                        // ---------------------------------------------------------
                        // 🔥 INSTANT CALLBACK (NON-SETTLED)
                        // ---------------------------------------------------------

                        if ($status === "0x0200" && $txn->initial_callback_sent == 0 && !empty($txn->callbackUrl)) {

                            try {

                                $payload = [
                                    'method'   => 'PAYIN',
                                    'refId'    => $txn->refId,
                                    'txnId'    => $txnId,
                                    'orderId'  => $orderId,
                                    'amount'   => $amount,
                                    'status'   => 'SUCCESS',
                                    'settlement_status' => 'NON-SETTLED', // 🔥 KEY
                                    'message'  => 'Payment Received, Settlement Pending'
                                ];

                                Http::post($txn->callbackUrl, $payload);

                                DB::table('pgmanage')
                                    ->where('id', $txn->id)
                                    ->update([
                                        'initial_callback_sent' => 1,
                                        'ready_for_process'     => 1
                                    ]);

                                Log::info("INITIAL CALLBACK SENT", [
                                    'payload' => $payload,
                                    'url'     => $txn->callbackUrl
                                ]);

                            } catch (\Exception $e) {

                                Log::error("INITIAL CALLBACK FAILED", [
                                    'error' => $e->getMessage()
                                ]);
                            }
                        }

                        // ---------------------------------------------------------
                        // Return Response to PG
                        // ---------------------------------------------------------

                        return response()->json([
                            'status'  => true,
                            'message' => 'Callback received successfully'
                        ], 200);
                    }
                else {

                    Log::warning(
                        "Merchant Callback URL Missing",
                        [
                            'remId' => $transaction->remId
                        ]
                    );
                }

                DB::commit();

                /*
                |--------------------------------------------------------------------------
                | FINAL SUCCESS RESPONSE
                |--------------------------------------------------------------------------
                */

                return response()->json([

                    'status'  => true,

                    'message' => 'Callback processed successfully'
                ], 200);

            } catch (\Exception $e) {

                DB::rollBack();

                Log::error("Payout Callback Processing Error", [

                    'error'        => $e->getMessage(),

                    'orderRefId'   => $orderRefId,

                    'clientRefId'  => $clientRefId,

                    'payload'      => $callbackData
                ]);

                return response()->json([

                    'status'  => false,

                    'message' => 'Callback processing failed',

                    'error'   => $e->getMessage()

                ], 500);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | UNKNOWN EVENT
        |--------------------------------------------------------------------------
        */

        Log::warning("Unhandled Callback Event", [

            'event'   => $event,

            'payload' => $callbackData
        ]);

        return response()->json([

            'status'  => false,

            'message' => 'Unhandled event type'

        ], 400);
    }
}