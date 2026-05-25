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

class oldUPIController extends Controller
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
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid API key.'
            ], 401);
        }

        Log::channel('fundtransfer')->info("Fund Transfer Request", [
    'ip' => $request->ip(),
    'payload' => $request->all()
]);

        // -----------------------------------------
        // ✅ Step 2: Check IP Whitelist
        // -----------------------------------------
        $clientIp = $request->ip();

       // return $clientIp; die();

        // Fetch whitelisted IPs for this user (store comma-separated or in another table)
        $whitelistedIps = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->pluck('ipAddress')
            ->toArray();
//return $whitelistedIps; die();
        if (!in_array($clientIp, $whitelistedIps)) {

            // Log the attempt for admin review
            Log::warning("IP BLOCKED: {$clientIp} tried payout for remId {$remittance->remId}");

            return response()->json([
                'status'  => false,
                'message' => "Access denied. Your IP ($clientIp) is not whitelisted."
            ], 403);
        }
        $service = DB::table('apis')
                ->where('name', 'UPI_PAYPUT')
                ->first();

            if (!$service || $service->status != 1) {
                return response()->json([
                            'status'  => false,
                            'message' => $service->message ?? 'Service is currently inactive'
                        ], 403);
            }
        // 2) Validate
        $validator = Validator::make($request->all(), [
            'txnAmount' => 'required|numeric|min:1',
            'upiId'     => 'required|string',
            'name'      => 'required|string',
            'RefNo'     => 'required|string|max:50',
            'apikey'    => 'required|string',
            
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 3) Duplicate Ref
        $existingTxn = DB::table('upipayout')
            ->where('remId', $remittance->remId)
            ->where('refId', $request->RefNo)
            ->first();

        if ($existingTxn) {
            return response()->json([
                'status'  => false,
                'message' => 'Duplicate RefNo. Transaction with this RefNo already exists.'
            ], 409);
        }

        // 4) Gates: Admin balance, Package, KYC, Service toggle
        $admin = DB::table('users')->where('id', 1)->first();
        if ($admin && $admin->balance < $request->txnAmount) {
            return response()->json([
                'status'  => false,
                'message' => 'Please contact Admin.',
            ], 400);
        }

        if ($remittance->packageId == 0) {
            return response()->json([
                'status' => false,
                'message' => 'No Package Assigned. Please contact Admin.',
            ], 400);
        }

         if ($remittance->callback_url == null) {
            return response()->json([
                'status' => false,
                'message' => 'CallBack Url not setup. Please contact Admin.',
            ], 400);
        }

        $package = DB::table('packages')->where('id', $remittance->packageId)->first();
        if (!$package || $package->status != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Assigned Package is Inactive. Please contact Admin.',
            ], 400);
        }

        if ($remittance->isKyc != 1 || $remittance->status != 'success') {
            return response()->json([
                'status' => false,
                'message' => 'KYC not verified or payout not allowed.',
            ], 403);
        }

        if ($remittance->upipayout != 1) {
            return response()->json([
                'status' => false,
                'message' => 'UPI Payout service not enabled for your account. Please contact Admin.',
            ], 403);
        }

        // 5) Wallet and charges
        $walletAmount = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email)
            ->value('amount');

        if (!$walletAmount || $walletAmount < $request->txnAmount) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient wallet balance. Please add funds.'
            ], 400);
        }

        $openingBal = (float)$walletAmount;
        $amount = (float)$request->txnAmount;

        $commissions = DB::table('commissions')
            ->where('packagesId', $remittance->packageId)
            ->where('service', 'UPI-PAYOUT')
            ->get();

        if ($commissions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No commission structure found for your package. Please contact Admin.'
            ], 400);
        }

        $charges = 0.0;
        $tds = 0.0;

        foreach ($commissions as $item) {
            $from = (float)$item->from_amount;
            $to   = (float)$item->to_amount;

            if ($item->service === 'UPI-PAYOUT' && $amount >= $from && $amount <= $to) {
                $charges = $item->charge_in === 'Percentage'
                    ? $amount * ((float)$item->charge) / 100
                    : (float)$item->charge;

                $tds = $item->tds_in === 'Percentage'
                    ? $charges * ((float)$item->tds) / 100
                    : (float)$item->tds;

                break;
            }
        }

        if ($charges == 0 && $amount >= 100) {
            $charges = $amount * 0.01; // default 1%
            $tds     = $charges * 0.18; // default 18% GST on charge
        }

        $totalDeduct = $amount + $charges + $tds;
        $closingBal  = $openingBal - $totalDeduct;

        if ($closingBal < 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient balance after charges & TDS.'
            ], 400);
        }

        // 6) Prepare payout record and bank call with consistency
        $paymentId  = 'SCP' . strtoupper(Str::random(10));
        $rawPayload = $request->all();

        $finalResponse = null;
        $normalized = [
            'is_success'  => false,
            'status'      => 'initiated',
            'utr'         => null,
            'bank_txn_id' => null,
            'message'     => 'Initiated',
            'code'        => 200,
        ];

        DB::beginTransaction();
        try {
            // Insert initiated record
            DB::table('upipayout')->insert([
                'remId'            => $remittance->remId,
                'email'            => $remittance->email,
                'payment_id'       => $paymentId,
                'amount'           => $amount,
                'charge'           => $charges,
                'tds'              => $tds,
                'status'           => 'Initiated',
                'opening_balance'  => $openingBal,
                'closing_balance'  => $closingBal,
                'bank_name'        => $request->bankName ?? null,
                'ifsc_code'        => $request->ifscCode ?? null,
                'acc_no'           => $request->upiId,
                'beneficiary_name' => $request->name,
                'refId'            => $request->RefNo,
                'requestBody'      => json_encode($rawPayload),
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            // Deduct wallet now
            DB::table('remittances')
                ->where('remId', $remittance->remId)
                ->where('email', $remittance->email)
                ->update(['amount' => $closingBal]);

            // 7) Call bank API
            $bankResponseArr = Http::post('https://api.credxpay.com/api/upi/payout/v6/initiate', [
                'amount' => $amount,
                'upiId'  => $request->upiId,
                'txnId'  => $paymentId, // use our payment id
                'name'   => $request->name,
                'callbackUrl' => route('upi.callback.handler'),

            ])->json();

            $finalResponse = $bankResponseArr ?? [];
                 Log::channel('fundtransfer')->info("UPI Payout Response Received from Bank", [
                    'ip'       => $request->ip(),
                    'response' => $bankResponseArr
    ]);
            // Normalize to handle your given shape:
            // {
            //   "status": true,
            //   "message": "Payment successful",
            //   "data": {
            //      "success": true, "status": "success", "message": "Payment successful",
            //      "txnId": "...", "amount": 1, "orderId": "...", "code": 200, "timestamp": "..."
            //   }
            // }
            $success = (bool) data_get($finalResponse, 'data.success', false);
            $status  = strtolower((string) data_get($finalResponse, 'data.status', 'failed'));
            $msg     = (string) data_get($finalResponse, 'data.message', data_get($finalResponse, 'message', ''));
            $txnId   = (string) data_get($finalResponse, 'data.txnId');
            $utr     = (string) data_get($finalResponse, 'data.txnId'); // if present in future
            $code    = (int) data_get($finalResponse, 'data.code', 200);
            $orderId = (string) data_get($finalResponse, 'data.orderId');

            $normalized = [
                'is_success'  => $success && $status === 'success' || $status ==='pending',
                'status'      => $status,
                'order_id'     => $orderId ?: null,
                'utr'         => $utr ?: null,
                'bank_txn_id' => $txnId ?: null,
                'message'     => $msg ?: ($success ? 'Payment successful' : 'Payment failed'),
                'code'        => $code,
            ];

            // Update payout row
            DB::table('upipayout')
                ->where('refId', $request->RefNo)
                ->update([
                    'bank_ref_no'  => $normalized['bank_txn_id'],
                    'order_id'     => $normalized['order_id'],
                    'status'       => $normalized['is_success'] ? 'Pending' : ($normalized['status'] === 'pending' ? 'Pending' : 'Failed'),
                    'responseBody' => json_encode($finalResponse),
                    'updated_at'   => now(),
                ]);

            if ($normalized['is_success']) {
                // On success, decrement admin principal by amount
                if ($admin) {
                    DB::table('users')->where('id', 1)->update([
                        'balance' => DB::raw('balance - ' . (float)$amount)
                    ]);
                }
                DB::commit();
            } else {
                // On failure or non-success, refund user wallet
                DB::table('remittances')
                    ->where('remId', $remittance->remId)
                    ->where('email', $remittance->email)
                    ->update(['amount' => $openingBal]);

                DB::table('upipayout')
                    ->where('refId', $request->RefNo)
                    ->update([
                        'closing_balance' => $openingBal,
                        'status'          => ($normalized['status'] === 'pending') ? 'Pending' : 'Failed',
                        'updated_at'      => now(),
                    ]);

                DB::commit();
            }

        } catch (\Throwable $t) {
            DB::rollBack();
            Log::error("UPI Payout Txn Error: ".$t->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Transaction failed. Rolled back.',
                'error'   => $t->getMessage()
            ], 500);
        }

        

        // 9) Final client response
        $httpStatus = $normalized['is_success'] ? 200 : ($normalized['status'] === 'Pending' ? 202 : 400);

        return response()->json([
            'status'  => $normalized['is_success'],
            'message' => $normalized['is_success'] ? 'Payout successful' : ($normalized['status'] === 'Pending' ? 'Payout Pending' : ($normalized['message'] ?: 'Payout failed')),
            'data'    => [
                'remId'           => $remittance->remId,
                'email'           => $remittance->email,
                'payment_id'      => $paymentId,
                'upi_id'          => $request->upiId,
                'utr'             => $normalized['utr'],
                'bank_txn_id'     => $normalized['bank_txn_id'],
                'amount'          => $amount,
                'charge'          => $charges,
                'gst'             => $tds,
                'status'          => $normalized['status'],
                'opening_balance' => $normalized['is_success'] ? $openingBal : $openingBal,
                'closing_balance' => $normalized['is_success'] ? $closingBal : $openingBal,
                'refId'           => $request->RefNo,
                'provider_code'   => $normalized['code'],
            ]
        ], $httpStatus);

    } catch (\Exception $e) {
        return $e;
        Log::error("Payout Error: " . $e->getMessage());
        return response()->json([
            'status'  => false,
            'message' => 'Unexpected server error',
            'error'   => $e->getMessage()
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
