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

                } else {

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