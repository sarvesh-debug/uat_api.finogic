<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\InstantPayHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;




class aepsController extends Controller
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
     /* ===============================
       2️⃣ Merchant Service  Active
    ================================ */
    if ($merchant->isAEPS !== 1) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SRVICE',
                'message' => 'Merchant Aeps Service is inactive'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }
     $service = DB::table('apis')
    ->where('name', 'AEPS')
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

       /* ============================================================
       📌 OUTLET LOGIN STATUS
    ============================================================ */
    public function outletLoginStatus(Request $request)
    {
        $requestId = 'req_' . Str::uuid();

        // 1️⃣ Auth Validation
        $authCheck = $this->validateAuth($request, $requestId);
        if ($authCheck instanceof JsonResponse) {
            return $authCheck;
        }

        // 2️⃣ Request Validation
         $validator = Validator::make($request->all(), [
         'outLet' => 'required|string'
    ]);
        if ($validator->fails()) {

        $formattedErrors = [];

        foreach ($validator->errors()->toArray() as $field => $messages) {
            $formattedErrors[] = [
                'field'   => $field,
                'message' => $messages[0]
            ];
        }

        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Input validation failed'
            ],
            'details' => $formattedErrors,
            'meta'    => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 422);
    }

    $validated = $validator->validated();


        try {
            $response = InstantPayHelper::outletLoginStatus($validated);

            if (($response['statuscode'] ?? null) === 'TXN') {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction Successful',
                    'data'    => $response['data'] ?? null,
                    'meta' => [
                        'request_id'  => $requestId,
                        'actcode'     => $response['actcode'] ?? null,
                        'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                        'orderid'     => $response['orderid'] ?? null,
                        'environment' => $response['environment'] ?? null,
                        'timestamp'   => $response['timestamp'] ?? now(),
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => $response['status'] ?? 'Transaction Failed',
                'meta' => [
                    'request_id'  => $requestId,
                    'actcode'     => $response['actcode'] ?? null,
                    'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                    'orderid'     => $response['orderid'] ?? null,
                    'environment' => $response['environment'] ?? null,
                    'timestamp'   => $response['timestamp'] ?? now(),
                ]
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INTERNAL_SERVER_ERROR',
                    'message' => $e->getMessage()
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 500);
        }
    }


 
/* ============================================================
   📌 OUTLET LOGIN (Biometric)
============================================================ */
public function outletLogin(Request $request)
{
    
    //return $request;
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Manual Validation (for formatted errors)
    $validator = Validator::make($request->all(), [
        'outLet'        => 'required|string',
        'type'          => 'required|string',
        'latitude'      => 'required|numeric',
        'longitude'     => 'required|numeric',
        'aadhaar'       => 'required|digits:12',
        'externalRef'   => 'required',
        'biometricData' => 'required'
    ]);

    if ($validator->fails()) {

        $formattedErrors = [];

        foreach ($validator->errors()->toArray() as $field => $messages) {
            $formattedErrors[] = [
                'field'   => $field,
                'message' => $messages[0]
            ];
        }

        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'VALIDATION_ERROR',
                'message' => 'Input validation failed'
            ],
            'details' => $formattedErrors,
            'meta'    => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 422);
    }

    $validated  = $validator->validated();
    $merchantId = $request->header('X-MERCHANT-ID');

    $chkLoggedIn = DB::table('merchant_aeps_transactions')
        ->where('merchant_id', $merchantId)
        ->where('outlet_id', $validated['outLet'])
        ->where('transaction_type', 'LOGIN')
        ->where('account_last_four','!=',null)
        ->whereDate('created_at', now()->toDateString())
        ->exists();
        
    if ($chkLoggedIn) {
        return response()->json([
            'success' => true,
            'message' => 'Outlet already logged in today',
            'data'   => [
                'code'    => 'ALREADY_LOGGED_IN',
                
            ],
            'meta' => [
                'statuscode'  => 'ALREADY_LOGGED_IN',
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 400);
    }

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
        $minBalance        = 10.00;
        $chargeBalance     = 0.95;

        if ($merchantOpBalance < $minBalance) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'LOW_BALANCE_ERROR',
                    'message' => 'Insufficient wallet balance',
                ],
                'data' => [
                    'available_balance' => $merchantOpBalance,
                    'required_balance'  => $minBalance,
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 422);
        }

        // 4️⃣ Call Provider
        $response = InstantPayHelper::outletLogin($validated);

        if (($response['statuscode'] ?? null) === 'TXN') {

            $openingBalance = $merchantOpBalance;
            $closingBalance = $merchantOpBalance - $chargeBalance;

            // 1️⃣ Deduct Wallet
            DB::table('remittances')
                ->where('remId', $merchantId)
                ->decrement('amount', $chargeBalance);

            // 2️⃣ Store in merchant_aeps_transactions
            DB::table('merchant_aeps_transactions')->insert([
                'request_id'        => $requestId,
                'merchant_id'       => $merchantId,
                'outlet_id'         => $validated['outLet'],
                'transaction_type'  => 'LOGIN',
                'transaction_mode'  => 'DR',
                'provider_status'   => $response['statuscode'] ?? null,
                'actcode'           => $response['actcode'] ?? null,
                'ipay_uuid'         => $response['ipay_uuid'] ?? null,
                'orderid'           => $response['orderid'] ?? null,
                'environment'       => $response['environment'] ?? null,

                'tds' =>$tds ?? 0,
                'commission' =>$comm ?? 0,
                'charges' =>$charges ?? 0,

                'transaction_amount'=> $chargeBalance,
                'payable_amount'    => $chargeBalance,
                'opening_balance'   => $openingBalance,
                'closing_balance'   => $closingBalance,

                'bank_name'         => null,
                'account_last_four' => $response['data']['aadhaarLastFour'] ?? null,
                'external_ref'      => null,
                'operator_id'       => null,
                'is_onus_txn'       => null,

                'provider_response' => json_encode($response),
                'status'            => 'SUCCESS',

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $response['status'] ?? 'Transaction Successful',
                'data'    => [
                    'aadhaarLastFour' => $response['data']['aadhaarLastFour'] ?? null,
                    'openingBalance'  => $openingBalance,
                    'closingBalance'  => $closingBalance,
                    'payableValue'    => $chargeBalance,
                ],
                'meta' => [
                    'request_id'  => $requestId,
                    'statuscode'  => $response['statuscode'] ?? null,
                    'actcode'     => $response['actcode'] ?? null,
                    'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                    'orderid'     => $response['orderid'] ?? null,
                    'environment' => $response['environment'] ?? null,
                    'timestamp'   => $response['timestamp'] ?? now(),
                ]
            ], 200);
        }

        DB::rollBack();

        return response()->json([
            'success' => false,
            'message' => $response['status'] ?? 'Transaction Failed',
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
        
        //return $e;
        

        DB::rollBack();

        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'INTERNAL_SERVER_ERROR',
                'message' => $e->getMessage()
            ],
            'meta' => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 500);
    }
}


public function cashWithdrawal(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Check (agar already method hai to use karo)
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Manual Validation (formatted error ke liye)
    $validator = Validator::make($request->all(), [
        'outLet'        => 'required|string',
        'bankiin'       => 'required|string',
        'latitude'      => 'required|numeric',
        'longitude'     => 'required|numeric',
        'mobile'        => 'required',
        'amount'        => 'required|numeric|min:1',
         'externalRef'   => 'required',
        'aadhaar'       => 'required|digits:12',
        'biometricData' => 'required'
    ]);

    if ($validator->fails()) {

        $errors = [];
        foreach ($validator->errors()->toArray() as $field => $messages) {
            $errors[] = [
                'field'   => $field,
                'message' => $messages[0]
            ];
        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Input validation failed'
            ],
            'details' => $errors,
            'meta' => [
                'request_id' => $requestId,
                'timestamp' => now()
            ]
        ], 422);
    }

    $validated  = $validator->validated();
    $merchantId = $request->header('X-MERCHANT-ID');
    

    try {

        DB::beginTransaction();

        // 3️⃣ Lock Merchant Wallet
        $merchant = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        if (!$merchant) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'MERCHANT_NOT_FOUND',
                    'message' => 'Invalid Merchant ID'
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp' => now()
                ]
            ], 404);
        }
                $merchantOpBalance  = (float) $merchant->amount;;

            $openingBal  = $merchantOpBalance;
            $amount     = $request->amount;
  
        // Fetch local commissions for the remittance package
            $commissions = DB::table('commissions')
                ->where('packagesId', $merchant->packageId)
                ->where('service', 'AEPS')
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

        if ($item->service === 'AEPS' && $amount >= $from && $amount <= $to) {
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

            $totalDeduct = ($amount + $comm) - $tds;
            $closingBal  = $openingBal + $totalDeduct;

        // 4️⃣ Call Provider
        $response = InstantPayHelper::cashWithdrawal($validated);

        if (($response['statuscode'] ?? null) === 'TXN') {

            $openingBalance = $merchantOpBalance ?? 0;
            $closingBalance = $closingBal ?? 0;
            DB::table('remittances')
            ->where('remId', $merchantId)
            ->update([
                'amount' => $closingBal,
                'updated_at' => now()
            ]);

            // 5️⃣ Store in merchant_aeps_transactions
            DB::table('merchant_aeps_transactions')->insert([
                'request_id'        => $requestId,
                'merchant_id'       => $merchantId,
                'outlet_id'         => $validated['outLet'],
                'transaction_type'  => 'CASH_WITHDRAWAL',
                'transaction_mode'  => 'CR',

                'tds' =>$tds ?? 0,
                'commission' =>$comm ?? 0,
                'charges' =>$charges ?? 0,

                'provider_status'   => $response['statuscode'] ?? null,
                'actcode'           => $response['actcode'] ?? null,
                'ipay_uuid'         => $response['ipay_uuid'] ?? null,
                'orderid'           => $response['orderid'] ?? null,
                'environment'       => $response['environment'] ?? null,

                'transaction_amount'=>  $amount ?? 0,
                'payable_amount'    => $totalDeduct ?? 0,
                'opening_balance'   => $openingBalance,
                'closing_balance'   => $closingBalance,

                'bank_name'         => $response['data']['bankName'] ?? null,
                'account_last_four' => substr($response['data']['accountNumber'] ?? '', -4),
                'external_ref'      => $response['data']['externalRef'] ?? null,
                'operator_id'       => $response['data']['operatorId'] ?? null,
                'is_onus_txn'       => $response['data']['isOnusTxn'] ?? null,

                'provider_response' => json_encode($response),
                'status'            => 'SUCCESS',

                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'statuscode' => $response['statuscode'] ?? null,
                'message' => $response['status'] ?? 'Transaction Successful',
                'data' => 
                        [
                           'transaction_amount'=>  $amount ?? 0,
                            'payable_amount'    => $totalDeduct ?? 0,
                            'opening_balance'   => $openingBalance,
                            'closing_balance'   => $closingBalance,

                            'bank_name'         => $response['data']['bankName'] ?? null,
                            'account_last_four' => substr($response['data']['accountNumber'] ?? '', -4),
                            'external_ref'      => $response['data']['externalRef'] ?? null,
                            'operator_id'       => $response['data']['operatorId'] ?? null,
                            'is_onus_txn'       => $response['data']['isOnusTxn'] ?? null, 
                        ],
                'meta' => [
                    'request_id' => $requestId,
                    'statuscode' => $response['statuscode'] ?? null,
                    'actcode' => $response['actcode'] ?? null,
                    'ipay_uuid' => $response['ipay_uuid'] ?? null,
                    'orderid' => $response['orderid'] ?? null,
                    'environment' => $response['environment'] ?? null,
                    'timestamp' => $response['timestamp'] ?? now(),
                ]
            ], 200);
        }

        DB::rollBack();

        return response()->json([
            'success' => false,
             'statuscode' => $response['statuscode'] ?? null,
            
            'message' => $response['status'] ?? 'Transaction Failed',
            'meta' => [
                'request_id' => $requestId,
                'statuscode' => $response['statuscode'] ?? null,
                'actcode' => $response['actcode'] ?? null,
                'ipay_uuid' => $response['ipay_uuid'] ?? null,
                'orderid' => $response['orderid'] ?? null,
                'environment' => $response['environment'] ?? null,
                'timestamp' => $response['timestamp'] ?? now(),
            ]
        ], 400);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'INTERNAL_SERVER_ERROR',
                'message' => $e->getMessage()
            ],
            'meta' => [
                'request_id' => $requestId,
                'timestamp' => now()
            ]
        ], 500);
    }
}

    public function balanceInquiry(Request $request)
{
    
   // return $request;
    $requestId = 'req_' . Str::uuid();

    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    $validator = Validator::make($request->all(), [
        'outLet'        => 'required|string',
        'bankiin'       => 'required|string',
        'latitude'      => 'required|numeric',
        'longitude'     => 'required|numeric',
        'mobile'        => 'required',
        'aadhaar'       => 'required|digits:12',
        'externalRef'   => 'required',
        'biometricData' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Input validation failed'
            ],
            'details' => $validator->errors(),
            'meta' => [
                'request_id' => $requestId,
                'timestamp' => now()
            ]
        ], 422);
    }

    $validated  = $validator->validated();
    $merchantId = $request->header('X-MERCHANT-ID');

    // 🔹 Provider Call First
    $response = InstantPayHelper::balanceInquiry($validated);

    if (($response['statuscode'] ?? null) !== 'TXN') {
        return response()->json([
            'success' => false,
             'statuscode' => $response['statuscode'] ?? null,
            'message' => $response['status'] ?? 'Transaction Failed',
            'meta' => [
                'request_id' => $requestId,
                'timestamp' => now()
            ]
        ], 400);
    }

    DB::beginTransaction();

    try {

        $merchant = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        if (!$merchant) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>'Merchant not found'],404);
        }

        $openingBal = (float) $merchant->amount;

        // 🔹 Fixed Charge Example
        $charge = 0; // ₹5 per BI (change as per slab)

        if ($openingBal < $charge) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>'Insufficient wallet balance'],400);
        }

        $closingBal = $openingBal - $charge;

        // Update Wallet
        DB::table('remittances')
            ->where('remId', $merchantId)
            ->update([
                'amount' => $closingBal,
                'updated_at' => now()
            ]);

        // Store Transaction
        DB::table('merchant_aeps_transactions')->insert([
            'request_id'        => $requestId,
            'merchant_id'       => $merchantId,
            'outlet_id'         => $validated['outLet'],
            'transaction_type'  => 'BALANCE_INQUIRY',
            'transaction_mode'  => 'DR',

            'tds' =>$tds ?? 0,
                'commission' =>$comm ?? 0,
                'charges' =>$charges ?? 0,

            'provider_status'   => $response['statuscode'],
            'actcode'           => $response['actcode'] ?? null,
            'ipay_uuid'         => $response['ipay_uuid'] ?? null,
            'orderid'           => $response['orderid'] ?? null,
            'environment'       => $response['environment'] ?? null,

            'transaction_amount'=> $charge,
            'opening_balance'   => $openingBal,
            'closing_balance'   => $closingBal,

            'provider_response' => json_encode($response),
            'status'            => 'SUCCESS',

            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::commit();

        $providerData = $response['data'] ?? [];

        return response()->json([
            'success' => true,
            'statuscode' => $response['statuscode'] ?? null,
            'message' => 'Balance Inquiry Successful',
            'data' => [
                'bankAccountBalance' => $providerData['bankAccountBalance'] ?? null,
                'payable_amount'     => $charge,
                'opening_balance'    => $openingBal,
                'closing_balance'    => $closingBal,
                'bank_name'          => $providerData['bankName'] ?? null,
                'account_last_four'  => isset($providerData['accountNumber']) 
                                        ? substr($providerData['accountNumber'], -4) 
                                        : null,
                'external_ref'       => $providerData['externalRef'] ?? null,
                'operator_id'        => $providerData['operatorId'] ?? null,
            ],
            'meta' => [
                'request_id' => $requestId,
                'statuscode' => $response['statuscode'] ?? null,
                'actcode'    => $response['actcode'] ?? null,
                'ipay_uuid'  => $response['ipay_uuid'] ?? null,
                'orderid'    => $response['orderid'] ?? null,
                'environment'=> $response['environment'] ?? null,
                'timestamp'  => $response['timestamp'] ?? now(),
            ]
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
    }
}



    public function miniStatement(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    $validator = Validator::make($request->all(), [
        'outLet'        => 'required|string',
        'bankiin'       => 'required|string',
        'latitude'      => 'required|numeric',
        'longitude'     => 'required|numeric',
        'mobile'        => 'required',
        'externalRef'   => 'required',
        'aadhaar'       => 'required|digits:12',
        'biometricData' => 'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Input validation failed'
            ],
            'details' => $validator->errors(),
            'meta' => [
                'request_id' => $requestId,
                'timestamp' => now()
            ]
        ], 422);
    }

    $validated  = $validator->validated();
    $merchantId = $request->header('X-MERCHANT-ID');

    // 🔹 Provider Call First
    $response = InstantPayHelper::miniStatement($request->all());

    if (($response['statuscode'] ?? null) !== 'TXN') {
        return response()->json([
            'success' => false,
            'statuscode' => $response['statuscode'] ?? null,
            'message' => $response['status'] ?? 'Transaction Failed',
            'meta' => [
                'request_id' => $requestId,
                'timestamp' => now()
            ]
        ], 400);
    }

    DB::beginTransaction();

    try {

        $merchant = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        if (!$merchant) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>'Merchant not found'],404);
        }

        $openingBal = (float) $merchant->amount;

        // 🔹 Fixed Charge Example
        $charge = 0.50; // ₹5 per BI (change as per slab)

        if ($openingBal < $charge) {
            DB::rollBack();
            return response()->json(['success'=>false,'message'=>'Insufficient wallet balance'],400);
        }

        $closingBal = $openingBal + $charge;

        // Update Wallet
        DB::table('remittances')
            ->where('remId', $merchantId)
            ->update([
                'amount' => $closingBal,
                'updated_at' => now()
            ]);

        // Store Transaction
        DB::table('merchant_aeps_transactions')->insert([
            'request_id'        => $requestId,
            'merchant_id'       => $merchantId,
            'outlet_id'         => $validated['outLet'],
            'transaction_type'  => 'BALANCE_STATEMENT',
            'transaction_mode'  => 'CR',
            
                'tds' =>$tds ?? 0,
                'commission' =>$comm ?? 0,
                'charges' =>$charges ?? 0,

            'provider_status'   => $response['statuscode'],
            'actcode'           => $response['actcode'] ?? null,
            'ipay_uuid'         => $response['ipay_uuid'] ?? null,
            'orderid'           => $response['orderid'] ?? null,
            'environment'       => $response['environment'] ?? null,

            'transaction_amount'=> $charge,
            'opening_balance'   => $openingBal,
            'closing_balance'   => $closingBal,

            'provider_response' => json_encode($response),
            'status'            => 'SUCCESS',

            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        DB::commit();

        $providerData = $response['data'] ?? [];

        return response()->json([
            'success' => true,
            'statuscode' => $response['statuscode'] ?? null,
            'message' => 'Balance Inquiry Successful',
            'data' => [
                'bankAccountBalance' => $providerData['bankAccountBalance'] ?? null,
                'payable_amount'     => $charge,
                'opening_balance'    => $openingBal,
                'closing_balance'    => $closingBal,
                'bank_name'          => $providerData['bankName'] ?? null,
                'account_last_four'  => isset($providerData['accountNumber']) 
                                        ? substr($providerData['accountNumber'], -4) 
                                        : null,
                'external_ref'       => $providerData['externalRef'] ?? null,
                'operator_id'        => $providerData['operatorId'] ?? null,
                'miniStatement'      =>$providerData['miniStatement'] ?? null,
            ],
            'meta' => [
                'request_id' => $requestId,
                'statuscode' => $response['statuscode'] ?? null,
                'actcode'    => $response['actcode'] ?? null,
                'ipay_uuid'  => $response['ipay_uuid'] ?? null,
                'orderid'    => $response['orderid'] ?? null,
                'environment'=> $response['environment'] ?? null,
                'timestamp'  => $response['timestamp'] ?? now(),
            ]
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['success'=>false,'message'=>$e->getMessage()],500);
    }
}
    

 public function aepsBanks(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 🔐 Auth Check
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // ✅ Validation
        $request->merge([
    'outLet' => $request->outLet ?? '580942'
    ]);
    $validator = Validator::make($request->all(), [
        'outLet' => 'required|string'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'Input validation failed'
            ],
            'details' => $validator->errors(),
            'meta' => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 422);
    }

    try {

        // 🔹 Provider Call
        $response = InstantPayHelper::aepsBanks($validator->validated());

        if (($response['statuscode'] ?? null) === 'TXN') {

            return response()->json([
                'success' => true,
                'message' => $response['status'] ?? 'Bank List Fetched Successfully',
                'data' => $response['data'] ?? [],
                'meta' => [
                    'request_id' => $requestId,
                    'statuscode' => $response['statuscode'] ?? null,
                    'actcode'    => $response['actcode'] ?? null,
                    'ipay_uuid'  => $response['ipay_uuid'] ?? null,
                    'orderid'    => $response['orderid'] ?? null,
                    'environment'=> $response['environment'] ?? null,
                    'timestamp'  => $response['timestamp'] ?? now(),
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'error' => [
                'code' => 'PROVIDER_ERROR',
                'message' => $response['status'] ?? 'Unable to fetch banks'
            ],
            'meta' => [
                'request_id' => $requestId,
                'statuscode' => $response['statuscode'] ?? null,
                'actcode'    => $response['actcode'] ?? null,
                'ipay_uuid'  => $response['ipay_uuid'] ?? null,
                'orderid'    => $response['orderid'] ?? null,
                'environment'=> $response['environment'] ?? null,
                'timestamp'  => $response['timestamp'] ?? now(),
            ]
        ], 400);

    } catch (\Exception $e) {

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
