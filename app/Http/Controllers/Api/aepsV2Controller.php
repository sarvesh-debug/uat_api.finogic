<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ChaganHelper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
    use App\Models\AepsMerchant;
    use Illuminate\Support\Facades\Log;

    
class aepsV2Controller extends Controller 
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


    // ================= CREATE MERCHANT =================


//use Illuminate\Support\Facades\Log;

public function createMerchant(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    $validator = Validator::make($request->all(), [
        'mobile' => 'required|digits:10',
        'name' => 'required|string',
        'gender' => 'required|in:M,F',
        'pan' => 'required',
        'email' => 'required|email',
        'address_full' => 'required',
        'address_city' => 'required',
        'address_pincode' => 'required|digits:6',
        'aadhaar' => 'required|digits:12',
        'dateOfBirth' => 'required',
        'latitude' => 'required',
        'longitude' => 'required',
        'bankAccountNo' => 'required',
        'bankIfsc' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'code' => 422,
            'requestId' => $requestId,
            'message' => 'Validation Error',
            'errors' => $validator->errors()
        ], 422);
    }

    $outletId = $request->header('X-MERCHANT-ID');

    if (!$outletId) {
        return response()->json([
            'status' => false,
            'code' => 400,
            'requestId' => $requestId,
            'message' => 'Outlet ID missing'
        ], 400);
    }

    $merchantDetails = DB::table('remittances')
        ->where('remId', $outletId)
        ->first();

    if (!$merchantDetails) {
        return response()->json([
            'status' => false,
            'code' => 404,
            'requestId' => $requestId,
            'message' => 'Invalid Merchant'
        ], 404);
    }

    // Payload
    $payload = [
        "mobile" => $request->mobile,
        "name" => $request->name,
        "gender" => $request->gender,
        "pan" => strtoupper($request->pan),
        "email" => $request->email,
        "address" => [
            "full" => $request->address_full,
            "city" => $request->address_city,
            "pincode" => $request->address_pincode
        ],
        "aadhaar" => $request->aadhaar,
        "dateOfBirth" => $request->dateOfBirth,
        "latitude" => $request->latitude,
        "longitude" => $request->longitude,
        "bankAccountNo" => $request->bankAccountNo,
        "bankIfsc" => strtoupper($request->bankIfsc),
    ];

    // ✅ Request Log
    Log::info('AEPS Create Merchant Request', [
        'requestId' => $requestId,
        'payload'   => $payload
    ]);

    $api = ChaganHelper::createMerchant($payload);

    // ✅ Response Log
    Log::info('AEPS Create Merchant Response', [
        'requestId' => $requestId,
        'response'  => $api
    ]);

    // ❗ FIXED CONDITION
    if ($api['status'] != true) {
        return response()->json([
            'status' => false,
            'code' => $api['code'],
            'requestId' => $requestId,
            'message' => $api['data']['providerResponse'] ?? 'Bank Server Down Time'
        ], $api['code']);
    }

    try {

        $aepsMerchantId = $api['data']['merchantId'] ?? null;

        AepsMerchant::create([
            'request_id' => $requestId,
            'outlet_id' => $aepsMerchantId,
            'merchant_id' => $outletId,
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'aadhaar' => $request->aadhaar,
            'pan' => strtoupper($request->pan),
            'request_payload' => $payload,
            'response_payload' => $api,
            'status' => 'success',
            'kyc_status' => $api['data']['kycStatus'] ?? 'pending'
        ]);

    } catch (\Exception $e) {

        // ❌ Error Log
        Log::error('AEPS DB Error', [
            'requestId' => $requestId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => false,
            'code' => 500,
            'requestId' => $requestId,
            'message' => 'DB Error'
        ], 500);
    }

    return response()->json([
        'status' => true,
        'code' => 200,
        'requestId' => $requestId,
        'merchantId' => $aepsMerchantId,
        'outletId' => $outletId,
        'message' => 'Merchant created successfully'
    ], 200);
}

    // ================= MERCHANT LIST =================
    public function merchantList(Request $request)
    {
        $payload = [
            "page"       => $request->input('page', 1),
            "limit"      => $request->input('limit', 10),
            "kycStatus"  => $request->input('kycStatus'),
            "mobile"     => $request->input('mobile'),
            "search"     => $request->input('search'),
        ];

        $api = ChaganHelper::merchantList(array_filter($payload));
        return response()->json($api, $api['code']);
    }

    // ================= LOGIN STATUS =================


public function loginStatus(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Custom Validator
    $validator = Validator::make($request->all(), [
        'merchantId' => 'required|string',
        'type'       => 'required|in:deposit,withdraw',
    ]);

    // ❌ Validation Failed (Proper JSON)
    if ($validator->fails()) {
        return response()->json([
            'status'    => false,
            'code'      => 422,
            'requestId' => $requestId,
            'message'   => 'Validation Error',
            'errors'    => $validator->errors()
        ], 422);
    }

    // 3️⃣ Payload
    $payload = [
        "merchantId" => $request->merchantId,
        "type"       => $request->type
    ];

    // 4️⃣ API Call
    $api = ChaganHelper::loginStatus($payload);

    // 5️⃣ Safe Response Handling
    return response()->json([
        'status'    => $api['status'] ?? false,
        'code'      => $api['code'] ?? 500,
        'requestId' => $requestId,
        'data'      => $api['data'] ?? null,
        'message'   => $api['message'] ?? 'Something went wrong'
    ], $api['code'] ?? 500);
}

    // ================= AEPS LOGIN =================
    

public function aepsLogin(Request $request)
{
    //return $request;
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Custom Validation
    $validator = Validator::make($request->all(), [
        'merchantId' => 'required|string',
        'transType'  => 'required|in:deposit,withdraw',
        'bioType'    => 'required|in:FINGER,FACE',

        // 🔐 Biometric Required Fields
        'dc'         => 'required',
        'ci'         => 'required',
        'hmac'       => 'required',
        'dpId'       => 'required',
        'mc'         => 'required',
        'pidDataType'=> 'required',
        'mi'         => 'required',
        'rdsId'      => 'required',
        'sessionKey' => 'required',
        'pidData'    => 'required',

        // Optional but recommended
        'fCount'     => 'nullable',
        'errCode'    => 'nullable',
        'pCount'     => 'nullable',
        'fType'      => 'nullable',
        'iCount'     => 'nullable',
        'pType'      => 'nullable',
        'srno'       => 'nullable',
        'qScore'     => 'nullable',
        'nmPoints'   => 'nullable',
        'rdsVer'     => 'nullable',
    ]);

    // ❌ Validation Failed
    if ($validator->fails()) {
        return response()->json([
            'status'    => false,
            'code'      => 422,
            'requestId' => $requestId,
            'message'   => 'Validation Error',
            'errors'    => $validator->errors()
        ], 422);
    }

    // 3️⃣ Payload Prepare
    $payload = [
        "merchantId" => $request->merchantId,
        "transType"  => $request->transType,
        "bioType"    => $request->bioType,

        // biometric block
        "dc"         => $request->dc,
        "ci"         => $request->ci,
        "hmac"       => $request->hmac,
        "dpId"       => $request->dpId,
        "mc"         => $request->mc,
        "pidDataType"=> $request->pidDataType,
        "mi"         => $request->mi,
        "rdsId"      => $request->rdsId,
        "sessionKey" => $request->sessionKey,
        "fCount"     => $request->fCount,
        "errCode"    => $request->errCode,
        "pCount"     => $request->pCount,
        "fType"      => $request->fType,
        "iCount"     => $request->iCount,
        "pType"      => $request->pType,
        "srno"       => $request->srno,
        "pidData"    => $request->pidData,
        "qScore"     => $request->qScore,
        "nmPoints"   => $request->nmPoints,
        "rdsVer"     => $request->rdsVer,
    ];
    //return $payload;

    // 4️⃣ API Call
    $api = ChaganHelper::aepsLogin($payload);
//return $api;
    // 5️⃣ Standard Response
    return response()->json([
        'status'    => $api['status'] ?? false,
        'code'      => $api['code'] ?? 500,
        'requestId' => $requestId,
        'data'      => $api['data'] ?? null,
        'message'   => $api['message'] ?? 'AEPS Login Response'
    ], $api['code'] ?? 500);
}
    // ================= AEPS PAYMENT =================


public function aepsPayment(Request $request)
{
    //return $request;
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }
    $txnid = 'CDAEPS' . strtoupper(Str::random(6));
    // 2️⃣ Validation
    $validator = Validator::make($request->all(), [
        'refId'      => 'required',
        'type'       => 'required|in:withdraw,deposit,balance,miniStatement',
        'iin'        => 'required',
        'adhar'      => 'required|digits:12',
        'cMobile'    => 'required|digits:10',
        'bioType'    => 'required|in:FINGER,FACE',

        // 🔐 Biometric Required
        'dc'         => 'required',
        'ci'         => 'required',
        'hmac'       => 'required',
        'dpId'       => 'required',
        'mc'         => 'required',
        'pidDataType'=> 'required',
        'mi'         => 'required',
        'rdsId'      => 'required',
        'sessionKey' => 'required',
        'pidData'    => 'required',

        // Optional
        'fCount'     => 'nullable',
        'errCode'    => 'nullable',
        'pCount'     => 'nullable',
        'fType'      => 'nullable',
        'iCount'     => 'nullable',
        'pType'      => 'nullable',
        'srno'       => 'nullable',
        'qScore'     => 'nullable',
        'nmPoints'   => 'nullable',
        'rdsVer'     => 'nullable',
    ]);

    // ❌ Validation Failed
    if ($validator->fails()) {
        return response()->json([
            'status'    => false,
            'code'      => 422,
            'requestId' => $requestId,
            'message'   => 'Validation Error',
            'errors'    => $validator->errors()
        ], 422);
    }
    
    // 3️⃣ Amount Logic (Important)
    $amount = $request->amount ?? 0;
    $merchantId=$request->header('X-MERCHANT-ID');
     // ✅ Check Duplicate RefNo
        $existingTxn = DB::table('merchant_aeps_v2_transactions')
            ->where('merchant_id', $merchantId)
            ->where('refId', $request->refId)
            ->first();

        if ($existingTxn) {
            return response()->json([
                'status'  => false,
                'code'     =>409,
                'message' => 'Duplicate RefNo. Transaction with this RefNo already exists.'
            ], 409);
        }

    if (in_array($request->type, ['withdraw', 'deposit'])) {
        if (!$amount || $amount <= 0) {
            return response()->json([
                'status'    => false,
                'code'      => 400,
                'requestId' => $requestId,
                'message'   => 'Amount is required for withdraw/deposit'
            ], 400);
        }
    }

    //     return response()->json([
    //     'status'    => true,
    //     'code'      => 200,
    //     'requestId' => $requestId,
    //     'message'   => 'Validation Passed',
    //     'data'      => $request->all()
    // ]);
    //return "yha tak sab thik hai";

    if (in_array($request->type, ['withdraw', 'deposit'])) 
        {
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

            
        }

    // 4️⃣ Payload
    $payload = [
        "merchantId" => $request->merchantId,
        "type"       => $request->type,
        "amount" => (string) $amount,
        "iin"        => $request->iin,
        "adhar"      => $request->adhar,
        "cMobile"    => $request->cMobile,
        "bioType"    => strtoupper($request->bioType),
        'txnId'     =>$txnid,

        // biometric block
        "dc"         => $request->dc,
        "ci"         => $request->ci,
        "hmac"       => $request->hmac,
        "dpId"       => $request->dpId,
        "mc"         => $request->mc,
        "pidDataType"=> $request->pidDataType,
        "mi"         => $request->mi,
        "rdsId"      => $request->rdsId,
        "sessionKey" => $request->sessionKey,
        "fCount"     => $request->fCount,
        "errCode"    => $request->errCode,
        "pCount"     => $request->pCount,
        "fType"      => $request->fType,
        "iCount"     => $request->iCount,
        "pType"      => $request->pType,
        "srno"       => $request->srno,
        "pidData"    => $request->pidData,
        "qScore"     => $request->qScore,
        "nmPoints"   => $request->nmPoints,
        "rdsVer"     => $request->rdsVer,
    ];

    // remove null values
    $payload = array_filter($payload, fn($v) => !is_null($v));

    // 5️⃣ API Call
    $api = ChaganHelper::aepsPayment($payload);
         Log::info('AEPS Create Merchant Response', [
                    'response' => $api
                 ]);
   // return $api;
    $response=$api;
   if (($response['status'] ?? false) === true && ($response['data']['status'] ?? '') === 'success') {
        
    $openingBalance = $openingBal ?? 0;
    $closingBalance = $closingBal ?? 0;

    DB::table('remittances')
        ->where('remId', $merchantId)
        ->increment('amount',$closingBalance);

    DB::table('merchant_aeps_v2_transactions')->insert([
        'refId'             =>$request->refId,
        'request_id'        => $requestId,
        'merchant_id'       => $merchantId,
        'outlet_id'         => $request->merchantId,
        'transaction_type'  => $request->type,
        'transaction_mode'  => 'CR',

        'tds'        => $tds ?? 0,
        'commission' => $comm ?? 0,
        'charges'    => $charges ?? 0,

        // ❌ Old fields removed
        'provider_status'   => $response['data']['status'] ?? null,
        'actcode'           => null,
        'ipay_uuid'         => null,
        'orderid'           => $response['data']['orderId'] ?? null,
        'environment'       => null,

        'transaction_amount'=> $amount ?? 0,
        'payable_amount'    => $totalDeduct ?? 0,
        'opening_balance'   => $openingBalance,
        'closing_balance'   => $closingBalance,

        'bank_name'         => $response['data']['bankName'] ?? null,
        'account_last_four' => substr($response['data']['accountNumber'] ?? '', -4),
        'external_ref'      => $response['data']['utr'] ?? null, // ✅ updated
        'operator_id'       => null,
        'is_onus_txn'       => null,

        'provider_response' => json_encode($api),
        'status'            => 'SUCCESS',

        'created_at'        => now(),
        'updated_at'        => now(),
    ]);

    DB::commit();

    return response()->json([
        'success' => true,
        'statuscode'=>'TXN',
        'type'=>$response['type'] ?? '',
        'message' => $response['message'] ?? 'Transaction Successful',
        'data' => [
            'transaction_amount'=> $amount ?? 0,
            'payable_amount'    => $totalDeduct ?? 0,
            'opening_balance'   => $openingBalance,
            'closing_balance'   => $closingBalance,

            'bank_name'         => $response['data']['bankName'] ?? null,
            'account_last_four' => substr($response['data']['accountNumber'] ?? '', -4),
            'utr'               => $response['data']['utr'] ?? null,
            'orderId'           => $response['data']['orderId'] ?? null,
            'balance'           => $response['data']['bankAccountBalance'] ?? null,
            'miniStatement'         => $response['data']['miniStatement'] ?? null,
        ],
        'meta' => [
            'request_id' => $requestId,
            'timestamp'  => now()
        ]
    ], 200);
}

// ❌ FAILED CASE
//DB::rollBack();

return response()->json([
    'success' => false,
    'statuscode'=>'ERR',
    'message' => $response['message'] ?? 'Transaction Failed',
    "remark" =>$response['data'],
    'meta' => [
        'request_id' => $requestId,
        'timestamp'  => now()
    ]
], 400);

    // // 6️⃣ Standard Response
    // return response()->json([
    //     'status'    => $api['status'] ?? false,
    //     'code'      => $api['code'] ?? 500,
    //     'requestId' => $requestId,
    //     'type'      => $request->type,
    //     'data'      => $api['data'] ?? null,
    //     'message'   => $api['message'] ?? 'AEPS Transaction Response'
    // ], $api['code'] ?? 500);
}
}