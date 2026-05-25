<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\PaycelPgService;

class PaycelPgController extends Controller
{
    /**
     * Local API Auth
     */
    public function localAuth($apikey)
    {
        $remittance = DB::table('remittances')
            ->where('apikey', $apikey)
            ->first();

        if (!$remittance) {

            response()->json([
                'status'  => false,
                'message' => 'Invalid API Key'
            ], 401)->send();

            exit;
        }

        return $remittance;
    }

    /**
     * Create Payment
     */
    public function pay(Request $request)
    {
        $remittance = $this->localAuth($request->apikey);
        // return $request;die();
        // ---------------------------------------------------------
        // ✅ Step 1: Authenticate Business
        // ---------------------------------------------------------
        $remittance = $this->localAuth($request->input('apikey'));

        // ---------------------------------------------------------
        // 🔎 Log Request
        // ---------------------------------------------------------
        Log::channel('fundtransfer')->info("Fund Transfer Request", [
            'ip'      => $request->ip(),
            'payload' => $request->all()
        ]);

        $clientIp = $request->ip();

        // ---------------------------------------------------------
        // 🔐 IP Whitelisting Check
        // ---------------------------------------------------------
        $whitelistedIps = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->pluck('ipAddress')
            ->toArray();

        if (!in_array($clientIp, $whitelistedIps)) {

            Log::warning("IP BLOCKED: {$clientIp} tried payout for remId {$remittance->remId}");

            return response()->json([
                'status'  => false,
                'message' => "Access denied. Your IP ($clientIp) is not whitelisted."
            ], 403);
        }
         if ($remittance->pgpayout==0) {
            return response()->json([
                'status'  => false,
                'message' => 'Payment Gateway is not active on your account. Please contact Admin.'
            ], 400);
        }   
        $validator = Validator::make($request->all(), [

            'amount'         => 'required|numeric|min:1',
            'RefNo'          => 'required|unique:pgmanage1,refId',
            'customer_name'  => 'required',
            'customer_phone' => 'required',
            'customer_email' => 'required|email',
            'payment_mode'   => 'required',

        ]);

        if ($validator->fails()) {

            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $txnId = "TXN" . time();

        DB::table('pgmanage1')->insert([

            'remId'       => $remittance->remId,
            'refId'       => $request->RefNo,
            'txnId'       => $txnId,
            'amount'      => $request->amount,
            'status'      => 'PENDING',
            'pgType'       => 'Paycel',
            'created_at'  => now(),
            'updated_at'  => now()

        ]);

        $service = new PaycelPgService();

        $response = $service->createPaymentRequest([

            'customer_id'    => $remittance->remId,
            'customer_name'  => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email,

            'amount'         => $request->amount,
            'reference_id'   => $txnId,
            'payment_mode'   => $request->payment_mode,

            'callback_url'   => $request->callback_url,
            'webhook_url'    => env('PAYCEL_WEBHOOK')

        ]);


        //return $response;
        $responseData=$response;
        DB::table('pgmanage1')
            ->where('txnId', $txnId)
            ->update([

                'responseData' => json_encode($response),
                'updated_at'   => now(),
                'orderId'=> $responseData['data']['data']['ref_id'] ?? '0',

            ]);

        return response()->json($response);
    }

    /**
     * Check Transaction Status
     */
    public function checkStatus($referenceId)
    {
        $service = new PaycelPgService();

        $response = $service->checkStatus($referenceId);
        //return $response;
        return response()->json($response);
    }



    public function callback(Request $request)
{


    Log::channel('fundtransfer')->info("PG Paycel CALLBACK RECEIVED", [
        'payload' => $request->all(),
        'ip'      => $request->ip()
    ]);


    //return $request;
    // ---------------------------------------------------------
    // Map webhook fields
    // ---------------------------------------------------------

    $txnId   = $request->ppc_UniqueMerchantTxnID ?? null;
    $status  = strtoupper($request->ppc_TxnResponseMessage ?? 'FAILED');
    $amount  = (float)($request->ppc_Amount ?? 0);
    // $orderId = $request->orderId ?? null;
    $utr     = $request->ppc_RRN ?? null;

    // ---------------------------------------------------------
    // Fetch Transaction
    // ---------------------------------------------------------

    $txn = DB::table('pgmanage1')
        ->where('txnId', $txnId)
        ->first();

    if (!$txn) {

        Log::warning("PG Paycel CALLBACK → Transaction Not Found", [
            'txnId'   => $txnId,
            'amount' => $amount
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

    DB::table('pgmanage1')
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

    if ($status === "SUCCESS" && $txn->initial_callback_sent == 0 && !empty($txn->callbackUrl)) {

        try {

            $payload = [
                'method'   => 'PAYIN',
                'refId'    => $txn->refId,
                'txnId'    => $txnId,
               
                'amount'   => $amount,
                'status'   => 'SUCCESS',
                'settlement_status' => 'NON-SETTLED', // 🔥 KEY
                'message'  => 'Payment Received, Settlement Pending'
            ];

            Http::post($txn->callbackUrl, $payload);

            DB::table('pgmanage1')
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
}