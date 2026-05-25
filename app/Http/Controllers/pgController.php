<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ChagansPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessPayinAfterDelay;
use Illuminate\Support\Facades\Http;



class pgController extends Controller
{
    /**
     * Authenticate API Key (Local Auth)
     */
    public function localAuth($cgapi)
    {
        $remittance = DB::table('remittances')->where('apikey', $cgapi)->first();

        if (!$remittance) {
            response()->json([
                'status'  => false,
                'message' => 'Unauthorized or invalid API token.'
            ], 401)->send();
            exit;
        }

        return $remittance;
    }

    /**
     * Create Payment Request API
     */
    public function pay(Request $request)
    {
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
        // ---------------------------------------------------------
        // 📌 Validate Input
        // ---------------------------------------------------------
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:100',
            'pgType' => 'required|string|max:20',
            'RefNo'  => 'required|string|max:50',
            'callbackUrl' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }
           $package = DB::table('packages')->where('id',$remittance->packageId)->first();
            if(!$package || $package->status != 1){
                return response()->json([
                    'status'  => false,
                    'message' => 'Assigned Package is Inactive. Please contact Admin.',
                ], 400);
            }
             // Fetch local commissions for the remittance package
        $commissions = DB::table('commissions')
            ->where('packagesId', $remittance->packageId)
            ->where('service', 'PAYIN')
            ->get() ?? [];

      if ($commissions->isEmpty()) {
            return response()->json([
                'status'  => false,
                'message' => 'No commission structure found for your package. Please contact Admin.'
            ], 400);
        }
        // ---------------------------------------------------------
        // 🔁 Duplicate RefNo Check
        // ---------------------------------------------------------
        $existingTxn = DB::table('pgmanage')
            ->where('remId', $remittance->remId)
            ->where('refId', $request->RefNo)
            ->first();

        if ($existingTxn) {
            return response()->json([
                'status'  => false,
                'message' => 'Duplicate RefNo. Transaction with this RefNo already exists.'
            ], 409);
        }

        // ---------------------------------------------------------
        // 🆕 Create Local Transaction (DB Insert)
        // ---------------------------------------------------------
        $txnId = uniqid("TXN");

        DB::table('pgmanage')->insert([
            'remId'        => $remittance->remId,
            'refId'        => $request->RefNo,
            'txnId'        => $txnId,
            'amount'       => $request->amount,
            'pgType'       => $request->pgType,
            'status'       => 'PENDING',
            'callbackUrl'  => $request->callbackUrl ?? null,
            'initiate_ip'  => $clientIp,
            'created_at'   => now(),
            'updated_at'   => now()
        ]);

        // ---------------------------------------------------------
        // 🚀 Step 2: Initiate Payment Using Service Class
        // ---------------------------------------------------------
        $pg = new ChagansPaymentService();

        $response = $pg->createPaymentRequest(
            amount: $request->amount,
            pgType: $request->pgType,
            txnId: $txnId,
            callback:$request->landing,
            webhook:"https://api.credxpay.com/api/dynamic/pg/callback",
        );

        // ---------------------------------------------------------
        // 📌 Update PG Response in DB
        // ---------------------------------------------------------
        DB::table('pgmanage')
            ->where('txnId', $txnId)
            ->update([
                'responseData' => json_encode($response),
                'updated_at'   => now(),
                'orderId' =>$response['data']['data']['orderId'] ?? 'N/A'
            ]);

        // ---------------------------------------------------------
        // 📌 Return Response
        // ---------------------------------------------------------
        return response()->json($response);
    }

    /**
 * Check Payment Status API
 */ 
public function status(Request $request)
{
    // -----------------------------
    // 📌 Validate Input
    // -----------------------------
    $validator = Validator::make($request->all(), [
        'apikey' => 'required|string',
        'RefNo'  => 'required|string|max:50',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed.',
            'errors'  => $validator->errors()
        ], 422);
    }

    // -----------------------------
    // 🔐 Authenticate Business
    // -----------------------------
    $remittance = $this->localAuth($request->apikey);

    // -----------------------------
    // 🔍 Find Transaction
    // -----------------------------
    $txn = DB::table('pgmanage')
        ->where('remId', $remittance->remId)
        ->where('refId', $request->RefNo)
        ->first();

    if (!$txn) {
        return response()->json([
            'status'  => false,
            'message' => 'Transaction not found.',
        ], 404);
    }

    // -----------------------------
    // 📌 Format Response
    // -----------------------------
    return response()->json([
        'status'  => true,
        'message' => 'Transaction Status Fetched Successfully',
        'data'    => [
            'refId'      => $txn->refId,
            'txnId'      => $txn->txnId,
            'orderId'    => $txn->orderId,
            'amount'     => $txn->amount,
            'pgType'     => $txn->pgType,
            'status'     => $txn->status,
            // 'response'   => json_decode($txn->responseData),
            'refundData' => json_decode($txn->refundData),
            'created_at' => $txn->created_at,
            'updated_at' => $txn->updated_at,
        ]
    ]);
}


/**
 * Payment Callback / Webhook Handler
 * Chagans PG → Your Server
 */

public function callback(Request $request)
{
    Log::channel('fundtransfer')->info("PG CALLBACK RECEIVED", [
        'payload' => $request->all(),
        'ip'      => $request->ip()
    ]);

    // ---------------------------------------------------------
    // Map webhook fields
    // ---------------------------------------------------------

    $txnId   = $request->transactionId ?? null;
    $status  = strtoupper($request->result ?? 'FAILED');
    $amount  = (float)($request->amount ?? 0);
    $orderId = $request->orderId ?? null;
    $utr     = $request->rrn ?? null;

    // ---------------------------------------------------------
    // Fetch Transaction
    // ---------------------------------------------------------

    $txn = DB::table('pgmanage')
        ->where('orderId', $orderId)
        ->first();

    if (!$txn) {

        Log::warning("PG CALLBACK → Transaction Not Found", [
            'txnId'   => $txnId,
            'orderId' => $orderId
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

    if ($status === "SUCCESS" && $txn->initial_callback_sent == 0 && !empty($txn->callbackUrl)) {

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
// public function callback(Request $request)
// {
//     Log::channel('fundtransfer')->info("PG CALLBACK RECEIVED", [
//         'payload' => $request->all(),
//         'ip'      => $request->ip()
//     ]);

//     // ---------------------------------------------------------
//     // Map webhook fields to expected variables
//     // ---------------------------------------------------------
//     $txnId   = $request->transactionId ?? null;
//     $status  = strtoupper($request->result ?? 'FAILED');
//     $amount  = (float)($request->amount ?? 0);
//     $orderId = $request->orderId ?? null;

//     // Decode userData if exists
//     $userData = [];
//     if (!empty($request->userData)) {
//         $userData = json_decode($request->userData, true);
//     }

//     // ---------------------------------------------------------
//     // 1️⃣ Fetch Transaction
//     // ---------------------------------------------------------
//     $txn = DB::table('pgmanage')
//         ->where('orderId', $orderId)
//         ->first();

//     if (!$txn) {
//         Log::warning("PG CALLBACK → Transaction Not Found", [
//             'txnId'   => $txnId,
//             'orderId' => $orderId
//         ]);

//         return response()->json([
//             'status'  => false,
//             'message' => 'Transaction not found.'
//         ], 404);
//     }

//     $remittance = DB::table('remittances')->where('remId', $txn->remId)->first();

//     // ---------------------------------------------------------
//     // 2️⃣ Update Basic Callback Status
//     // ---------------------------------------------------------
//     DB::table('pgmanage')->where('id', $txn->id)->update([
//         'orderId'      => $orderId,
//         'status'       => $status,
//         'responseData' => json_encode($request->all(), JSON_UNESCAPED_SLASHES),
//         'updated_at'   => now()
//     ]);

//     Log::info("PG CALLBACK UPDATED", [
//         'txnId'   => $txnId,
//         'status'  => $status,
//         'response'=> $request->all()
//     ]);

//     // ---------------------------------------------------------
//     // 3️⃣ If SUCCESS → Wallet Update + Charges/TDS
//     // ---------------------------------------------------------
//     $tds = 0;
//     $charges = 0;
//     $netAmount = 0;

//     if ($status === "SUCCESS") {

//         // Wallet opening balance
//         $openingBal = $remittance->amount;

//         // Fetch Commission Slab for PAYIN
//         $commissions = DB::table('commissions')
//             ->where('packagesId', $remittance->packageId)
//             ->where('service', 'PAYIN')
//             ->get();

//         if ($commissions->isEmpty()) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'No commission structure found for this package.'
//             ], 400);
//         }

//         // Find Slab Match (Amount Range)
//         foreach ($commissions as $item) {

//             $from = (float)$item->from_amount;
//             $to   = (float)$item->to_amount;

//             if ($amount >= $from && $amount <= $to) {

//                 // Charges
//                 $charges = $item->charge_in === 'Percentage'
//                     ? $amount * ((float)$item->charge) / 100
//                     : (float)$item->charge;

//                 // TDS
//                 $tds = $item->tds_in === 'Percentage'
//                     ? $charges * ((float)$item->tds) / 100
//                     : (float)$item->tds;

//                 break; // slab found
//             }
//         }

//         // Net Received Amount
//         $netAmount = $amount - ($charges + $tds);
//         $closingBal = $openingBal + $netAmount;

//         // Update PG Table with computed values
//         DB::table('pgmanage')->where('id', $txn->id)->update([
//             'tds'            => $tds,
//             'charges'        => $charges,
//             'openingBalance' => $openingBal,
//             'closingBalance' => $closingBal
//         ]);

//         // Update User Wallet
//         DB::table('remittances')->where('remId', $txn->remId)->increment('amount', $netAmount);
//     }

//     // ---------------------------------------------------------
//     // 4️⃣ Send Client Callback
//     // ---------------------------------------------------------
//     if (!empty($txn->callbackUrl)) {
//         try {

//             $payload = [
//                 'method'    => 'PAYIN',
//                 'refId'     => $txn->refId,
//                 'txnId'     => $txnId,
//                 'orderId'   => $orderId,
//                 'amount'    => $amount,
//                 'netAmount' => $netAmount,
//                 'status'    => $status,
//                 'message'   => 'Payment Status Callback',
//                 'tds'       => $tds,
//                 'gst'       => $charges,
//                 'user'      => $userData, // include user info
//             ];

//             \Illuminate\Support\Facades\Http::post($txn->callbackUrl, $payload);

//             Log::info("CLIENT CALLBACK SENT", [
//                 'url'     => $txn->callbackUrl,
//                 'payload' => $payload
//             ]);

//         } catch (\Exception $e) {
//             Log::error("CLIENT CALLBACK FAILED", [
//                 'url'   => $txn->callbackUrl,
//                 'error' => $e->getMessage()
//             ]);
//         }
//     }

//     // ---------------------------------------------------------
//     // 5️⃣ Return PG Response
//     // ---------------------------------------------------------
//     return response()->json([
//         'status'  => true,
//         'message' => 'Callback received successfully'
//     ], 200);
// }


public function pgReport(Request $request)
{
    $query = DB::table('pgmanage')
        ->where('remId', Auth::guard('remittance')->user()->remId);
    //return $query;
    // 🔎 Filters
    if($request->status){
        $query->where('status',$request->status);
    }

    if($request->pgType){
        $query->where('pgType',$request->pgType);
    }

    if($request->from && $request->to){
        $query->whereBetween('created_at', [
            $request->from.' 00:00:00',
            $request->to.' 23:59:59'
        ]);
    }

    // 📊 Summary
    $summary = (clone $query)->selectRaw("
        COUNT(*) as total_txn,
        SUM(amount) as total_amount,
        SUM(CASE WHEN status='SUCCESS' THEN amount ELSE 0 END) as success_amount,
        SUM(CASE WHEN status='PENDING' THEN amount ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status='FAILED' THEN amount ELSE 0 END) as failed_amount
    ")->first();

    // 📄 Pagination
    $txn = $query->orderBy('created_at','desc')
                ->paginate(15)
                ->withQueryString();
                 
               // return $txn;die();

    return view('users.txn.pgreport', compact('txn','summary'));
}

public function pgExport(Request $request)
{
    $query = DB::table('pgmanage')
        ->where('remId', Auth::guard('remittance')->user()->remId);

    if($request->status){
        $query->where('status',$request->status);
    }

    if($request->pgType){
        $query->where('pgType',$request->pgType);
    }

    if($request->from && $request->to){
        $query->whereBetween('created_at', [
            $request->from.' 00:00:00',
            $request->to.' 23:59:59'
        ]);
    }

    $data = $query->orderBy('created_at','desc')->get();

    $filename = "pg_report_".date('YmdHis').".csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function() use ($data){
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Txn ID','Order ID','Amount','Charges','TDS','Opening','Closing','PG Type','Status','Date']);

        foreach($data as $row){
            fputcsv($file, [
                $row->txnId,
                $row->orderId,
                $row->amount,
                $row->charges,
                $row->tds,
                $row->openingBalance,
                $row->closingBalance,
                $row->pgType,
                $row->status,
                $row->created_at,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback,200,$headers);
}

public function pgReportAdmin(Request $request)
{
    $query = DB::table('pgmanage');

    // 🔎 Filters
    if($request->status){
        $query->where('status',$request->status);
    }
    

    if($request->pgType){
        $query->where('pgType',$request->pgType);
    }

    if($request->from && $request->to){
        $query->whereBetween('created_at', [
            $request->from.' 00:00:00',
            $request->to.' 23:59:59'
        ]);
    }
    if($request->remId){
        $remId=$request->remId;
        $query->where('remId',$remId);
    }

    // 📊 Summary
    $summary = (clone $query)->selectRaw("
        COUNT(*) as total_txn,
        SUM(amount) as total_amount,
        SUM(CASE WHEN status='SUCCESS' THEN amount ELSE 0 END) as success_amount,
        SUM(CASE WHEN status='PENDING' THEN amount ELSE 0 END) as pending_amount,
        SUM(CASE WHEN status='FAILED' THEN amount ELSE 0 END) as failed_amount
    ")->first();

    // 📄 Pagination
    $txn = $query->orderBy('created_at','desc')
                ->paginate(15)
                ->withQueryString();

    return view('txn.pgTxn', compact('txn','summary'));
}

public function pgExportAdmin(Request $request)
{
    $query = DB::table('pgmanage');

    if($request->status){
        $query->where('status',$request->status);
    }

    if($request->pgType){
        $query->where('pgType',$request->pgType);
    }

    if($request->from && $request->to){
        $query->whereBetween('created_at', [
            $request->from.' 00:00:00',
            $request->to.' 23:59:59'
        ]);
    }

    $data = $query->orderBy('created_at','desc')->get();

    $filename = "pg_report_".date('YmdHis').".csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function() use ($data){
        $file = fopen('php://output', 'w');
        fputcsv($file, ['Txn ID','Order ID','Amount','Charges','TDS','Opening','Closing','PG Type','Status','Date']);

        foreach($data as $row){
            fputcsv($file, [
                $row->txnId,
                $row->orderId,
                $row->amount,
                $row->charges,
                $row->tds,
                $row->openingBalance,
                $row->closingBalance,
                $row->pgType,
                $row->status,
                $row->created_at,
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback,200,$headers);
}
public function wrapPG(Request $request)
{
    $request->validate([
        'amount'   => 'required|numeric',
        'pgType'   => 'required|string',
        'txnId'    => 'required|string',
        'landing'  => 'required|url',
        'webhook'  => 'required|url',
    ]);

    $pg = new ChagansPaymentService();

    $response = $pg->createPaymentRequest(
        amount: $request->amount,
        pgType: $request->pgType,
        txnId: $request->txnId,
        callback: $request->landing,
        webhook: $request->webhook
    );

    return response()->json($response);
}

}
