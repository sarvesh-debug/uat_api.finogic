<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\AeronpayHelper;
use Illuminate\Support\Facades\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
class upiaeronpayController extends Controller
{
      public function localAuth($apiKey, $merchantId, $request)
{
    $clientIp = $request->ip();

    $merchant = DB::table('remittances')
        ->where('apikey', $apiKey)
        ->where('remId', $merchantId)
        ->first();

        //return $merchant;
    /* ===============================
       1️⃣ API Key / Merchant Validation
    ================================ */
    if (!$merchant) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'INVALID_CREDENTIALS',
                'message' => 'Invalid API Key or Merchant ID'
            ],
            'meta' => [
                'timestamp'  => now(),
                'request_ip' => $clientIp
            ]
        ], 401);
    }

    /* ===============================
       2️⃣ Merchant Status Check
    ================================ */
    if ($merchant->status !== 'success') {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE',
                'message' => 'Merchant account is inactive'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }

    $service = DB::table('apis')
    ->where('name', 'UPI_PAYOUT2')
    ->first();

if (!$service || $service->status != 1) {
    return response()->json([
        'success' => false,
        'error'   => [
            'code'    => 'SERVICE_INACTIVE',
            'message' => $service->message ?? 'Service is currently inactive'
        ],
        'meta' => [
            'merchantId' => $merchantId,
            'timestamp'  => now()
        ]
    ], 403);
}
     /* ===============================
       2️⃣ Merchant Service  Active
    ================================ */
    if ($merchant->upipayout2 !== 1) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SRVICE',
                'message' => 'Merchant UPI Payout pipe 2 Service is inactive'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }
     /* ===============================
       2️⃣ Merchant Service  Active
    ================================ */
    if ($merchant->isKyc !== 1) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SERVICE',
                'message' => 'Merchant kYC Is pending'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }

    /* ===============================
       3️⃣ IP Whitelisting Check
    ================================ */
    if ($merchant->ipAddress !== null) {

        $allowedIps = is_array($merchant->ipAddress)
            ? $merchant->ipAddress
            : explode(',', $merchant->ipAddress);

        if (!in_array($clientIp, $allowedIps)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'IP_NOT_WHITELISTED',
                    'message' => 'Access denied from this IP address'
                ],
                'meta' => [
                    'merchantId' => $merchantId,
                    'request_ip' => $clientIp,
                    'timestamp'  => now()
                ]
            ], 403);
        }
    }

    /* ===============================
       ✅ Auth Successful
    ================================ */
    return $merchant;
}

    /* ============================================================
       🔐 COMMON HEADER + AUTH VALIDATION
    ============================================================ */
    private function validateAuth(Request $request, string $requestId)
    {
        $apiKey     = $request->header('X-API-KEY');
        $merchantId = $request->header('X-MERCHANT-ID');

        if (!$apiKey || !$merchantId) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'AUTH_HEADER_MISSING',
                    'message' => 'X-API-KEY and X-MERCHANT-ID headers are required'
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 401);
        }

        $auth = $this->localAuth($apiKey, $merchantId, $request);

        if ($auth instanceof JsonResponse) {
            return $auth;
        }

        return true;
    }
    
    /* ===============================
       🚀 UPI PAYOUT
    ================================ */
    public function upipayout(Request $request)
    {

    //return "OKKKK";
        $requestId = 'REQ_' . Str::random(10);

        /* ✅ AUTH */
        $auth = $this->validateAuth($request, $requestId);
        if ($auth instanceof JsonResponse) {
            return $auth;
        }

        if ($auth instanceof JsonResponse) {
                return $auth;
            }

        // ✅ Merchant fetch karo properly
        $remittance = DB::table('remittances')
            ->where('apikey', $request->header('X-API-KEY'))
            ->where('remId', $request->header('X-MERCHANT-ID'))
            ->first();

        try {

            /* ✅ VALIDATION */
            $validator = Validator::make($request->all(), [
                'amount' => 'required|numeric|min:1',
                'vpa'    => 'required|string',
                'name'   => 'required|string',
                'phone'  => 'required|digits:10',
                'refId'  => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            /* ✅ DUPLICATE CHECK */
            $exists = DB::table('upipayout2')
                ->where('refId', $request->refId)
                ->first();

            if ($exists) {
                return response()->json([
                    'status' => false,
                    'message' => 'Duplicate RefId'
                ], 409);
            }

            /* ✅ BALANCE CHECK */
            $wallet = $remittance->amount;

            if ($wallet < $request->amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Insufficient Balance'
                ], 400);
            }


            $openingBal = $wallet;
            $amount = $request->amount;

          $commissions = DB::table('commissions')
            ->where('packagesId', $remittance->packageId)
            ->where('service', 'UPI-PAYOUT2')
            ->get();

        if ($commissions->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No commission structure found for your package. Please contact Admin.'
            ], 400);
        }
     if ($remittance->callback_url == null) {
            return response()->json([
                'status' => false,
                'message' => 'CallBack Url not setup. Please contact Admin.',
            ], 400);
        }
        $charges = 0.0;
        $tds = 0.0;

        foreach ($commissions as $item) {
            $from = (float)$item->from_amount;
            $to   = (float)$item->to_amount;

            if ($item->service === 'UPI-PAYOUT2' && $amount >= $from && $amount <= $to) {
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


            $total = $amount + $charges + $tds;
            $closingBal = $openingBal - $total;

            /* ✅ SAVE INIT */
            $paymentId = 'UPI' . time();

            DB::table('upipayout2')->insert([
                'remId' => $remittance->remId,
                'email'=>$remittance->email ?? '',
                'payment_id' => $paymentId,
                'refId' => $request->refId,
                'amount' => $amount,
                'charge' => $charges,
                'tds' => $tds,
                'opening_balance' => $openingBal,
                'closing_balance' => $closingBal,
                'status' => 'Initiated',
                'acc_no' => $request->vpa,
                'beneficiary_name' => $request->name,
                'created_at' => now()
            ]);

            /* ✅ WALLET DEDUCT */
            DB::table('remittances')
                ->where('remId', $remittance->remId)
                ->update(['amount' => $closingBal]);

            /* ===============================
               🚀 CALL AERONPAY HELPER
            ================================ */
            // $apiResponse = AeronpayHelper::initiateupipayout([
            //     'merchant_id' => $remittance->remId,
            //     'email'=>$remittance->email,
            //     'phone'=>$remittance->phone,
            //     'amount' => $amount,
            //     'address'=>$remittance->city,
            //     'client_referenceId' => $paymentId,
            //     'vpa' => $request->vpa,
            //     'name' => $request->name,
    
            // ]);

              $apiResponse = Http::post('https://uatapi.credxpay.com/api/cd/v3/upi/txn', [
                'amount'                  => $amount,
                'vpa'            => $request->vpa,
                'name'     => strtoupper($request->name),
                'phone'=> $request->phone,
                'refId'         => $paymentId,
               
            ])->json();

       // return $apiResponse; 
        Log::channel('fundtransfer')->info("Payout Aeronpay Response Received from Bank", [
        'ip'       => $request->ip(),
        'response' => $apiResponse
    ]);
  //   Log::info("Aeron Response", $apiResponse);

            $status = strtolower($apiResponse['status'] ?? 'Pending');
            $statusCode=$apiResponse['statusCode'] ?? '404';

            /* ✅ UPDATE RECORD */
            DB::table('upipayout2')
                ->where('refId', $request->refId)
                ->update([
                    'responseBody' => json_encode($apiResponse),
                    'status' => $statusCode == 201 ? 'Pending' : 'Failed',
                    'updated_at' => now()
                ]);

            /* ✅ FAILURE REFUND */
            if ($status != true) {
                DB::table('remittances')
                    ->where('remId', $remittance->remId)
                    ->update(['amount' => $openingBal]);

               DB::table('upipayout2')
                ->where('refId', $request->refId)
                ->update([
                    'closing_balance' =>$wallet,
                ]);       
            }

            return response()->json([
                'status' => $status ,
                'message' => $statusCode == '201' ? 'Payout Initiated' : 'Failed',
                'data' => [
                    'payment_id' => $paymentId,
                    'ref_id'=>$request->refId,
                    'amount' => $amount,
                    'status' => $status
                ]
            ]);

        } catch (\Exception $e) {

            Log::error("UPI ERROR: " . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Server Error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //reports for admin 
   private function filterQuery($request)
    {
        $query = DB::table('upipayout2');

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

        return view('admin.reports.upi_v2_report', compact('upi'));
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


    public function checkUpiPayoutStatus(Request $request)
{       
     $requestId = 'REQ_' . Str::random(10);

     /* ✅ AUTH */
        $auth = $this->validateAuth($request, $requestId);
        if ($auth instanceof JsonResponse) {
            return $auth;
        }

        if ($auth instanceof JsonResponse) {
                return $auth;
            }

        // ✅ Merchant fetch karo properly
        $remittance = DB::table('remittances')
            ->where('apikey', $request->header('X-API-KEY'))
            ->where('remId', $request->header('X-MERCHANT-ID'))
            ->first();

    try {
        

        // 2) Validate input
        // Support either payment_id or RefNo to query status; prefer payment_id
        $validator = Validator::make($request->all(), [
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
                'message' => 'Provide Valid RefNo.'
            ], 422);
        }

        // 3) Find transaction by payment_id else RefNo
        $query = DB::table('upipayout2')
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


}

