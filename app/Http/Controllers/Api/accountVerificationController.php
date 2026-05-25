<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Helpers\InstantPayHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class accountVerificationController extends Controller
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
    ->where('name', 'ACCOUNT_VERIFY')
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
    if ($merchant->isAcc !== 1) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SRVICE',
                'message' => 'Merchant Account Verification Service is inactive'
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
    public function accountVerify(Request $request)
{
    //return $request;
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'accountNumber' => 'required|string|min:6|max:20',
        'ifsc'          => ['required','string','size:11','regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],
        'latitude'      => 'required|numeric',
        'longitude'     => 'required|numeric',
    ]);

    if ($validator->fails()) {

        $errors = [];

        foreach ($validator->errors()->toArray() as $field => $messages) {
            $errors[] = [
                'field' => $field,
                'message' => $messages[0]
            ];
        }

        return response()->json([
            'success' => false,
            'error'   => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Input validation failed'
            ],
            'details' => $errors,
            'meta' => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 422);
    }

    $validated = $validator->validated();
    $merchantId = $request->header('X-MERCHANT-ID');

    try {

      DB::beginTransaction();

        // 3️⃣ Lock Merchant Row (Race Condition Safe)
        $merchantDetails = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        if (!$merchantDetails) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'MERCHANT_NOT_FOUND',
                    'message' => 'Invalid Merchant ID'
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 404);
        }

        $merchantOpBalance = (float) $merchantDetails->amount;
        $amount        = 10.00;
        $chargeBalance     = 0.95;// Hypothetical charge for account verification
         // Fetch local commissions for the remittance package
            $commissions = DB::table('commissions')
                ->where('packagesId', $merchantDetails->packageId)
                ->where('service', 'ACC_VERIFICATION')
                ->get() ?? [];

        if ($commissions->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No commission structure found for your package. Please contact Admin.'
                ], 400);
            }
        // return $commissions;die();
            // Initialize charges and tds
            $charges = 0;
            $tds = 0;
            $comm=0;

            // Loop through commissions to find the applicable range
        foreach ($commissions as $item) {
        // Ensure numeric comparison
        $from = (float) $item->from_amount;
        $to   = (float) $item->to_amount;

        if ($item->service === 'ACC_VERIFICATION' && $amount >= $from && $amount <= $to) {
            // Calculate charges
            $charges = $item->charge_in === 'Percentage'
                ? $amount * ((float) $item->charge) / 100
                : (float) $item->charge;
            
            $comm=$item->commissions_in==='Percentage'
                ? $amount *((float)$item->commissions)/100
                :(float) $item->commissions;

            // Calculate TDS
            $tds = $item->tds_in === 'Percentage'
                ? $comm * ((float) $item->tds) / 100
                : (float) $item->tds;

            // Exit loop once a matching commission is found
            break;
        }
    }


            // $charges and $tds now have the calculated values
        //dd($charges, $tds);die();

            // ✅ Fallback charges
           if ($comm == 0 && $amount >= 100) {
                $comm = $amount * 0.01;
                $tds  = $comm * 0.05; // example 5%
            }

            $totalDeduct = $charges + $tds;
            $closingBal  = $merchantOpBalance - $totalDeduct;
              if (!$totalDeduct || $merchantOpBalance  < $totalDeduct) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Insufficient wallet balance with charges',
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 400);
        }
        // ✅ Deduct balance
        DB::table('remittances')
            ->where('remId', $merchantId)
            ->decrement('amount', $totalDeduct);

        $response = InstantPayHelper::accountVerify($validated);

        // ✅ SUCCESS
        if (($response['statuscode'] ?? null) === 'TXN') {


            $payeeName   = $response['data']['payee']['name']    ?? null;
            $accountNo   = $response['data']['payee']['account'] ?? null;
            $ifsc        = $response['data']['payee']['ifsc']    ?? null;

            // Mask account number (security)
            $maskedAccount = $accountNo 
                ? str_repeat('X', strlen($accountNo) - 4) . substr($accountNo, -4)
                : null;

                         

        // ✅ Insert into account_verifications log
        DB::table('account_verifications')->insert([
            'remId'           => $merchantId,
            'beneAccount'     => $accountNo,
            'ifsc'            => $ifsc,
            'beneName'        => $payeeName,
             'type'            =>'ACCOUNT',
            'charges'         => $charges,
            'tds'             => $tds,
            'opbalance' => $merchantOpBalance,
            'clbalance' => $merchantOpBalance - $totalDeduct,
            'created_at'      => now(),
            'updated_at'      => now()
        ]);

          DB::commit(); // ✅ MOST IMPORTANT FIX
            return response()->json([
                'success' => true,
                'statuscode' => $response['statuscode'] ?? null,
                'message' => 'Account verified successfully',
                'data' => [
                    'payee_name' => $payeeName,
                    'account_no' => $accountNo,
                    'ifsc'       => $ifsc,

                    'deducted_amount' => $totalDeduct,
                    'opening_balance' => $merchantOpBalance,
                    'closing_balance' => $merchantOpBalance - $totalDeduct,
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'statuscode' => $response['statuscode'] ?? null,
                    'ipay_uuid'  => $response['ipay_uuid'] ?? null,
                    'orderid'    => $response['orderid'] ?? null,
                    'timestamp'  => $response['timestamp'] ?? now(),
                ]
            ], 200);
        }

        //refund if failed
        DB::table('remittances')
            ->where('remId', $merchantId)
            ->increment('amount', $totalDeduct);
        // ❌ FAILURE
        return response()->json([
            'success' => false,
              'statuscode' => $response['statuscode'] ?? null,
            'message' => $response['status'] ?? 'Account verification failed',
            'meta' => [
                'request_id'  => $requestId,
                'statuscode'  => $response['statuscode'] ?? null,
                'actcode'     => $response['actcode'] ?? null,
                'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                'orderid'     => $response['orderid'] ?? null,
                'environment' => $response['environment'] ?? null,
                'timestamp'   => $response['timestamp'] ?? now(),
            ]
        ], 400);

    } catch (\Exception $e) {

        return $e;
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $e->getMessage()
            ],
            'meta' => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 500);
    }
}

}
