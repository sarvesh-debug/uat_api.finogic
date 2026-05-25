<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class upipayoutController extends Controller
{
    public function localAuth($cgapi)
{
    $storedCgapi = DB::table('remittances')->where('apikey',$cgapi)->value('apikey');
    $remittance = DB::table('remittances')->where('apikey',$cgapi)->first();
    if (!$storedCgapi || $cgapi !== $storedCgapi) {
        // Return JSON error response if no match
        response()->json([
            'status' => false,
            'message' => 'Unauthorized or invalid api token.'
        ], 401)->send();
        exit;
    }

    // If match, return true or proceed silently
    return $remittance;
}


public function upipayout(Request $request)
{
    try {
        // 1) Authenticate
        $remittance = $this->localAuth($request->input('apikey'));
        if (!$remittance) {
            return response()->json(['status' => false, 'message' => 'Unauthorized. Invalid API key.'], 401);
        }
         $paymentId = 'SCP' . strtoupper(Str::random(10));
        Log::channel('fundtransfer')->info("Fund Transfer Request", [
            'ip' => $request->ip(),
            'payment_id'=>$paymentId,
            'payload' => $request->all()
        ]);

        // 2) IP Whitelist
        $clientIp = $request->ip();
        $whitelistedIps = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->pluck('ipAddress')
            ->toArray();

        if (!in_array($clientIp, $whitelistedIps)) {
            return response()->json([
                'status' => false,
                'message' => "Access denied. Your IP ($clientIp) is not whitelisted."
            ], 403);
        }

        // 3) Service check
        $service = DB::table('apis')->where('name', 'UPI_PAYPUT')->first();
        if (!$service || $service->status != 1) {
            return response()->json([
                'status' => false,
                'message' => $service->message ?? 'Service is inactive'
            ], 403);
        }

        // 4) Validation
        $validator = Validator::make($request->all(), [
            'txnAmount' => 'required|numeric|min:100',
            'upiId'     => 'required|string',
            'name'      => 'required|string',
            'RefNo'     => 'required|string|max:50',
            'apikey'    => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // 5) Duplicate Ref
        if (DB::table('upipayout')
            ->where('remId', $remittance->remId)
            ->where('refId', $request->RefNo)
            ->exists()) {
            return response()->json(['status' => false, 'message' => 'Duplicate RefNo'], 409);
        }

        // 6) Checks
        $admin = DB::table('users')->where('id', 1)->first();

        if ($admin && $admin->balance < $request->txnAmount) {
            return response()->json(['status' => false, 'message' => 'Please contact Admin'], 400);
        }

        if ($remittance->packageId == 0) {
            return response()->json(['status' => false, 'message' => 'No Package Assigned'], 400);
        }

        if (!$remittance->callback_url) {
            return response()->json(['status' => false, 'message' => 'Callback URL not set'], 400);
        }

        $package = DB::table('packages')->where('id', $remittance->packageId)->first();
        if (!$package || $package->status != 1) {
            return response()->json(['status' => false, 'message' => 'Inactive Package'], 400);
        }

        if ($remittance->isKyc != 1 || $remittance->status != 'success') {
            return response()->json(['status' => false, 'message' => 'KYC not verified'], 403);
        }

        if ($remittance->upipayout != 1) {
            return response()->json(['status' => false, 'message' => 'Service disabled'], 403);
        }

        // 7) Wallet
        $walletAmount = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->value('amount');

        if ($walletAmount < $request->txnAmount) {
            return response()->json(['status' => false, 'message' => 'Insufficient balance'], 400);
        }

        $openingBal = (float)$walletAmount;
        $amount = (float)$request->txnAmount;

        // 8) Charges
        $commissions = DB::table('commissions')
            ->where('packagesId', $remittance->packageId)
            ->where('service', 'UPI-PAYOUT')
            ->get();

        $charges = 0;
        $tds = 0;

        foreach ($commissions as $item) {
            if ($amount >= $item->from_amount && $amount <= $item->to_amount) {
                $charges = $item->charge_in == 'Percentage'
                    ? $amount * $item->charge / 100
                    : $item->charge;

                $tds = $item->tds_in == 'Percentage'
                    ? $charges * $item->tds / 100
                    : $item->tds;
                break;
            }
        }

        if ($charges == 0) {
            $charges = $amount * 0.01;
            $tds = $charges * 0.18;
        }

        $total = $amount + $charges + $tds;
        $closingBal = $openingBal - $total;

        if ($closingBal < 0) {
            return response()->json(['status' => false, 'message' => 'Low balance after charges'], 400);
        }

        // 🔥 TRANSACTION START
        DB::beginTransaction();

       
        DB::table('upipayout')->insert([
            'remId' => $remittance->remId,
            'email' => $remittance->email,
            'payment_id' => $paymentId,
            'amount' => $amount,
            'charge' => $charges,
            'tds' => $tds,
            'status' => 'Initiated',
            'opening_balance' => $openingBal,
            'closing_balance' => $closingBal,
            'acc_no' => $request->upiId,
            'beneficiary_name' => $request->name,
            'refId' => $request->RefNo,
            'requestBody' => json_encode($request->all()),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->update(['amount' => $closingBal]);

        DB::commit(); // 🔥 MAIN FIX

        // ================= API PIPE =================
        $apiPipe = DB::table('api_pipes')
            ->where('service', 'UPI_PAYOUT')
            ->where('status', 1)
            ->first();

        if (!$apiPipe) {
            throw new \Exception("No API Pipe");
        }

        // ================= AERONPAY =================
        if ($apiPipe->pipe == 'Aeronpay') {

            try {
                $apiResponse = Http::timeout(20)
                ->retry(2, 300)
                ->post(
                    'https://api.credxpay.com/api/cd/v3/upi/txn',
                    [
                        'amount' => $amount,
                        'vpa' => $request->upiId,
                        'name' => strtoupper($request->name),
                        'phone' => $remittance->phone,
                        'refId' => $paymentId,
                    ]
                )->json();

                Log::channel('fundtransfer')->info("Aeronpay UPI Response", [
                    'ip' => $request->ip(),
                    'payment_id'=>$paymentId,
                    'response' => $apiResponse,
                ]);


            } catch (\Exception $e) {

                Log::channel('fundtransfer')->info("Aeronpay UPI Response Failed", [
                    'ip' => $request->ip(),
                    'payment_id'=>$paymentId,
                     'response' =>$e->getMessage()
                ]);

                DB::table('upipayout')->where('payment_id', $paymentId)->update([
                    'status' => 'Pending',
                    'responseBody' => json_encode(['error' => 'timeout']),
                    'pipe' => 'Aeronpay'
                ]);

                return response()->json([
                   'status' => true, // timeout = pending = true
                    'message' => 'Payout Initiated (Timeout)',
                // ✅ ADD THIS
                    'data' => [
                        'status'=>'pending',
                        'ref_id' => $request->RefNo ?? null,
                        'payment_id' => $paymentId ?? null,
                    ]
                ]);
            }

            $statusCode = $apiResponse['statusCode'] ?? 500;
            $finalStatus = ($statusCode == 200) ? 'Success' : (($statusCode == 201) ? 'Pending' : 'Failed');
            $status = strtolower($finalStatus);

            DB::table('upipayout')->where('payment_id', $paymentId)->update([
                'status' => $finalStatus,
                'order_id' => $apiResponse['data']['transactionId'] ?? null,
                'responseBody' => json_encode($apiResponse),
                'pipe' => 'Aeronpay'
            ]);

            if (!in_array($statusCode, [200, 201])) {
                DB::table('remittances')->where('remId', $remittance->remId)
                    ->update(['amount' => $openingBal]);

                DB::table('upipayout')->where('payment_id', $paymentId)
                    ->update(['closing_balance' => $openingBal]);

                $closingBal = $openingBal;
            }

            return response()->json([
                // 'status' => $status,
                 'status' => ($status === 'failed') ? false : true, // ✅ boolean
                'message' => $statusCode == 201 ? 'Payout Initiated' : ($statusCode == 200 ? 'Success' : 'Failed'),
                'data' => [
                    'remId' => $remittance->remId,
                    'email' => $remittance->email,
                    'payment_id' => $paymentId,
                    'ref_id' => $request->RefNo,
                    'amount' => $amount,
                    'status' => $status,
                    'upi_id' => $request->upiId,
                    'utr' => $apiResponse['data']['utr'] ?? null,
                    'charge' => $charges,
                    'gst' => $tds,
                    'opening_balance' => $openingBal,
                    'closing_balance' => $closingBal,
                    'refId' => $request->RefNo,
                ]
            ]);
        }

        // ================= CHAGAN =================
        elseif ($apiPipe->pipe == 'Chagan') {

            try {
                $resp = Http::timeout(20)
                ->retry(2, 300)
                ->post(
                    'https://api.credxpay.com/api/upi/payout/v6/initiate',
                    [
                        'amount' => $amount,
                        'upiId' => $request->upiId,
                        'txnId' => $paymentId,
                        'name' => $request->name,
                        'callbackUrl' => route('upi.callback.handler'),
                    ]
                )->json();

                Log::channel('fundtransfer')->info("Chagan UPI Response", [
                    'ip' => $request->ip(),
                    'payment_id'=>$paymentId,
                    'response' => $resp,
                ]);
            } catch (\Exception $e) {

                Log::channel('fundtransfer')->info("Chagan UPI Response Failed", [
                    'ip' => $request->ip(),
                    'payment_id'=>$paymentId,
                    'response' =>$e->getMessage()
                ]);

                DB::table('upipayout')->where('payment_id', $paymentId)->update([
                    'status' => 'Pending',
                    'responseBody' => json_encode(['error' => 'timeout']),
                    'pipe' => 'Chagan'
                ]);

                return response()->json([
                  'status' => true, // timeout = pending
                    'message' => 'Payout Initiated (Timeout)',
                // ✅ ADD THIS
                    'data' => [
                        'status'=>'pending',
                        'ref_id' => $request->RefNo ?? null,
                        'payment_id' => $paymentId ?? null,
                    ]
                ]);
            }

            $success = data_get($resp, 'data.success', false);

            $normalized = [
                'is_success' => $success,
                'status' => $success ? 'pending' : 'failed',
                'utr' => data_get($resp, 'data.txnId'),
                'bank_txn_id' => data_get($resp, 'data.txnId'),
                'message' => data_get($resp, 'message'),
                'code' => data_get($resp, 'data.code', 200),
            ];

            DB::table('upipayout')->where('payment_id', $paymentId)->update([
                'status' => $success ? 'Pending' : 'Failed',
                'responseBody' => json_encode($resp),
                'pipe' => 'Chagan'
            ]);

            if (!$success) {
                DB::table('remittances')->where('remId', $remittance->remId)
                    ->update(['amount' => $openingBal]);

                $closingBal = $openingBal;
            }

            $httpStatus = $normalized['is_success'] ? 200 : ($normalized['status'] === 'pending' ? 202 : 400);

            return response()->json([
                // 'status' => $normalized['is_success'],
                'status' => ($normalized['status'] === 'failed') ? false : true, // ✅ FIXED
                'message' => $normalized['is_success']
                    ? 'Payout successful'
                    : ($normalized['status'] === 'pending'
                        ? 'Payout Pending'
                        : ($normalized['message'] ?: 'Payout failed')),
                'data' => [
                    'remId' => $remittance->remId,
                    'email' => $remittance->email,
                    'payment_id' => $paymentId,
                    'ref_id' => $request->RefNo,
                    'upi_id' => $request->upiId,
                    'utr' => $normalized['utr'],
                    'bank_txn_id' => $normalized['bank_txn_id'],
                    'amount' => $amount,
                    'charge' => $charges,
                    'gst' => $tds,
                    'status' => $normalized['status'],
                    'opening_balance' => $openingBal,
                    'closing_balance' => $closingBal,
                    'refId' => $request->RefNo,
                    'provider_code' => $normalized['code'],
                ]
            ], $httpStatus);
        }

        throw new \Exception("Invalid Pipe");

   } catch (\Throwable $e) {

    

    Log::error("UPI Payout Error", [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'ref_id' => $request->RefNo ?? null,
        'payment_id' => $paymentId ?? null,
    ]);

    return response()->json([
        'status' => false,
        'message' => 'Transaction failed',

        // ✅ ADD THIS
        'data' => [
            'ref_id' => $request->RefNo ?? null,
            'payment_id' => $paymentId ?? null,
        ]

    ], 500);
}
}

public function callBack() 
{
    
}


public function checkUpiPayoutStatus(Request $request)
{
    try {
        // 1) Authenticate
        $remittance = $this->localAuth($request->input('apikey'));
        if (!$remittance) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid API key.'
            ], 401);
        }

        // 2) Validate input
        // Support either payment_id or RefNo to query status; prefer payment_id
        $validator = Validator::make($request->all(), [
            'order_id' => 'nullable|string',
              'RefNo' => 'nullable|string',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // At least one required
if (!$request->filled('order_id') && !$request->filled('RefNo')) {
    return response()->json([
        'status'  => false,
        'message' => 'Provide either order_id or RefNo.'
    ], 422);
}

        // 3) Find transaction by payment_id else RefNo
        $query = DB::table('upipayout')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email);

        if ($request->filled('order_id')) {
            $query->where('order_id', $request->payment_id);
        } else {
            $query->where('refId', $request->RefNo);
        }

        $txn = $query->first();

        if (!$txn) {
            return response()->json([
                'status'  => false,
                'message' => 'Transaction not found.'
            ], 404);
        }

        // 4) Build normalized view from stored columns
        // Response body may contain provider details (txnId/utr/code/status)
        $resp = $txn->responseBody ? json_decode($txn->responseBody, true) : [];

        // Safe access with data_get to avoid undefined indexes
        $bankTxnId = data_get($resp, 'data.txnId') ?: ($txn->bank_ref_no ?? null);
        $utr       = data_get($resp, 'data.result.utr') ?? data_get($resp, 'data.utr') ?? null;
        $provCode  = (int) (data_get($resp, 'data.code', 0) ?: 0);

       // return $bankTxnId;die();
        // Status normalization from stored status
        // Stored values: Initiated|Success|Pending|Failed
        $normalizedStatus = strtolower($txn->status); // success|pending|failed|initiated
        $isSuccess = ($normalizedStatus === 'success');

        // 5) Return consistent envelope
        return response()->json([
            'status'  => $isSuccess,
            'message' => $isSuccess
                ? 'Payout successful'
                : ($normalizedStatus === 'pending'
                    ? 'Payout pending'
                    : ($normalizedStatus === 'initiated' ? 'Payout initiated' : 'Payout failed')),
            'data'    => [
                'remId'            => $txn->remId,
                'email'            => $txn->email,
                'payment_id'       => $txn->payment_id,
                // 'upi_id'           => $txn->acc_no ? null : null, // not stored for UPI; keep null for compatibility
                // 'utr'              => $utr,
                'bank_txn_id'      => $bankTxnId,
                'amount'           => (float) $txn->amount,
                'charge'           => (float) $txn->charge,
                'gst'              => (float) $txn->tds,
                'status'           => $normalizedStatus,
                'opening_balance'  => (float) $txn->opening_balance,
                'closing_balance'  => (float) $txn->closing_balance,
                'upi_id'           => $txn->acc_no,
                'beneficiary_name' => $txn->beneficiary_name,
                'refId'            => $txn->refId,
                'provider_code'    => $provCode,
                'created_at'       => $txn->created_at,
                'updated_at'       => $txn->updated_at,
            ]
        ], $isSuccess ? 200 : ($normalizedStatus === 'pending' || $normalizedStatus === 'initiated' ? 202 : 400));

    } catch (\Exception $e) {
        Log::error('Exception during UPI payout status check', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'An unexpected error occurred while checking transaction status.',
            'error'   => $e->getMessage()
        ], 500);
    }
}





public function upiHistory(Request $request)
{
    $rid = auth('remittance')->user()->remId;

    $fromDate = $request->from_date;
    $toDate   = $request->to_date;
    $search   = $request->search;

    $query = DB::table('upipayout')
        ->where('remId', $rid);

    /*
    |--------------------------------------------------------------------------
    | Date Filter
    |--------------------------------------------------------------------------
    */
    if (!empty($fromDate) && !empty($toDate)) {
        $query->whereBetween('created_at', [
            $fromDate . ' 00:00:00',
            $toDate . ' 23:59:59'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Search Filter (Payment ID / UTR / Name / Amount)
    |--------------------------------------------------------------------------
    */
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('payment_id', 'like', "%$search%")
              ->orWhere('bank_ref_no', 'like', "%$search%")
              ->orWhere('beneficiary_name', 'like', "%$search%")
              ->orWhere('amount', 'like', "%$search%");
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Final Pagination
    |--------------------------------------------------------------------------
    */
    $upi = $query
        ->orderBy('id', 'desc')
        ->paginate(20)
        ->appends($request->query());

    return view('users.upi_history', compact('upi'));
}



public function upiExportCsv(Request $request)
{
    $rid = auth('remittance')->user()->remId;

    $fromDate = $request->from_date;
    $toDate   = $request->to_date;
    $search   = $request->search;
    $status   = $request->status;

    $query = DB::table('upipayout')
        ->where('remId', $rid);

    // Date Filter
    if (!empty($fromDate) && !empty($toDate)) {
        $query->whereBetween('created_at', [
            $fromDate . ' 00:00:00',
            $toDate . ' 23:59:59'
        ]);
    }

    // Search Filter
    if (!empty($search)) {
        $query->where(function ($q) use ($search) {
            $q->where('payment_id', 'like', "%$search%")
              ->orWhere('bank_ref_no', 'like', "%$search%")
              ->orWhere('beneficiary_name', 'like', "%$search%");
        });
    }

    // Status Filter
    if (!empty($status)) {
        $query->where('status', $status);
    }

    $records = $query->orderBy('id', 'desc')->get();

    $fileName = 'UPI_Payout_Report_' . now()->format('Ymd_His') . '.csv';

    $headers = [
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=$fileName",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $columns = [
        'Payment ID',
        'Beneficiary',
        'Bank Name',
        'IFSC',
        'UTR',
        'Amount',
        'Charge',
        'GST',
        'Opening Balance',
        'Closing Balance',
        'Status',
        'Date'
    ];

    $callback = function () use ($records, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($records as $row) {
            fputcsv($file, [
                $row->payment_id,
                $row->beneficiary_name,
                $row->bank_name,
                $row->ifsc_code,
                $row->bank_ref_no,
                $row->amount,
                $row->charge,
                $row->tds,
                $row->opening_balance,
                $row->closing_balance,
                $row->status,
                \Carbon\Carbon::parse($row->created_at)->format('d-m-Y H:i')
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

//reports for admin 
   private function filterQuery($request)
    {
        $query = DB::table('upipayout');

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [
                $request->from_date . ' 00:00:00',
                $request->to_date . ' 23:59:59'
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('payment_id', 'like', "%$search%")
                  ->orWhere('bank_ref_no', 'like', "%$search%")
                  ->orWhere('beneficiary_name', 'like', "%$search%")
                  ->orWhere('remId', 'like', "%$search%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function upiReport(Request $request)
    {
        $upi = $this->filterQuery($request)
            ->orderByDesc('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.reports.upi_report', compact('upi'));
    }

    public function exportCsv(Request $request)
    {
        $records = $this->filterQuery($request)
            ->orderByDesc('id')
            ->get();

        $fileName = 'Admin_UPI_Report_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($records) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'RemID','Payment ID','Beneficiary','Bank',
                'UTR','Amount','Charge','TDS','Opening',
                'Closing','Status','Date'
            ]);

            foreach ($records as $row) {
                fputcsv($file, [
                    $row->remId,
                    $row->payment_id,
                    $row->beneficiary_name,
                    $row->bank_name,
                    $row->bank_ref_no,
                    $row->amount,
                    $row->charge,
                    $row->tds,
                    $row->opening_balance,
                    $row->closing_balance,
                    $row->status,
                    Carbon::parse($row->created_at)->format('d-m-Y H:i')
                ]);
            }

            fclose($file);

        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName"
        ]);
    }

}
