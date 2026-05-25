<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\InstantPayHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;


class dmtController extends Controller
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
            'statuscode'  => 'ERR' ?? null,
                'message' => 'Invalid API Key or Merchant ID',
                 'status' => 'Invalid API Key or Merchant ID',
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
            'statuscode'  => 'ERR' ?? null,
                'message' => 'Merchant account is inactive',
                 'status' => 'Merchant account is inactive',
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
    if ($merchant->isDMT !== 1) {
        return response()->json([
            'success' => false,
            'statuscode'  => 'ERR' ?? null,
                'message' => 'Merchant dmt Service is inactive',
                 'status' => 'Merchant dmt Service is inactive',
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SRVICE',
                'message' => 'Merchant dmt Service is inactive'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'timestamp'  => now()
            ]
        ], 403);
    }
     $service = DB::table('apis')
    ->where('name', 'DMT')
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
            'statuscode'  => 'ERR' ?? null,
                'message' => 'Merchant kYC Is pending',
                 'status' => 'Merchant kYC Is pending',
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
                'statuscode'  => 'ERR' ?? null,
                'message' => 'Access denied from this IP address',
                 'status' => 'Access denied from this IP address',
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
                'statuscode'  => 'ERR' ?? null,
                'message' => 'X-API-KEY and X-MERCHANT-ID headers are required',
                 'status' => 'X-API-KEY and X-MERCHANT-ID headers are required',
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
    // ---------------- Get Bank Details --------------------


public function bankDetails(Request $request)
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::getBankDetails($validated);

        if (($response['statuscode'] ?? null) === 'TXN') {

            return response()->json([
                'success' => true,
                
                'message' => 'Bank Details Fetched Successfully',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => $e->getMessage(),
            'status' => 'INTERNAL_SERVER_ERROR',
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
    
    // -------------------- Remitter Profile ---------------------


public function remitterProfile(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'       => 'required|string',
        'mobileNumber' => 'required|digits:10',
        'txnMode'      => 'nullable|in:ALL,IMPS,NEFT'
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
            'statuscode'  => 'ERR' ?? null,
            'message' => $formattedErrors,
            'status' => 'Input validation failed',
            
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::remitterProfile($validated);

        if (($response['statuscode'] ?? null) === 'TXN') {

            return response()->json([
                'success' => true,
                'statuscode'  => $response['statuscode'] ?? null,
                'message' => 'Remitter profile fetched successfully',
                 'status' => 'Remitter profile fetched successfully',
                'data'    => $response['data'] ?? null,
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

        return response()->json([
            'success' => false,
            'statuscode'  => $response['statuscode'] ?? null,
            'status' => $response['status'] ?? 'Transaction Failed',
            'message' => $response['status'] ?? 'Transaction Failed',
            
            'data'    => $response['data'] ?? null,
            'meta' => [
                'request_id'  => $requestId,
                'statuscode'  => $response['statuscode'] ?? null,
                'actcode'     => $response['actcode'] ?? null,
                'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                'orderid'     => $response['orderid'] ?? null,
                'environment' => $response['environment'] ?? null,
                'internalCode'=> $response['internalCode'] ?? null,
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
    // -------------------- Remitter Registration ---------------------


public function remitterRegistration(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'        => 'required|string',
        'mobileNumber'  => 'required|digits:10',
        'aadhaarNumber' => 'required|digits:12',
        'referenceKey'  => 'required|string',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::remitterRegistration($validated);

        // ✅ OTP Success Condition
        if (($response['statuscode'] ?? null) === 'OTP') {

            return response()->json([
                'success' => true,
                'statuscode'  => $response['statuscode'] ?? null,
                'message' => 'Remitter profile fetched successfully',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
            'statuscode'  => $response['statuscode'] ?? null,
            'status' => $response['status'] ?? 'Registration Failed',
            'message' => $response['status'] ?? 'Registration Failed',
            'meta' => [
                'request_id'   => $requestId,
                'statuscode'   => $response['statuscode'] ?? null,
                'actcode'      => $response['actcode'] ?? null,
                'ipay_uuid'    => $response['ipay_uuid'] ?? null,
                'orderid'      => $response['orderid'] ?? null,
                'environment'  => $response['environment'] ?? null,
                'internalCode' => $response['internalCode'] ?? null,
                'timestamp'    => $response['timestamp'] ?? now(),
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

    // -------------------- Verify Remitter Registration ---------------------
 

public function verifyRemitterRegistration(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'       => 'required|string',
        'mobileNumber' => 'required|digits:10',
        'otp'          => 'required|digits_between:4,8',
        'referenceKey' => 'required|string',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::verifyRemitterRegistration($validated);

        // ✅ Success Condition (KYC Completed / Verified)
        if (($response['statuscode'] ?? null) === 'KYC') {

            return response()->json([
                'success' => true,
                'statuscode'  => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'OTP Verification Failed',
                'message' => 'Remitter registration verified successfully',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
             'statuscode'  => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'OTP Verification Failed',
            'message' => $response['status'] ?? 'OTP Verification Failed',
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

    // -------------------- Remitter KYC ---------------------
 

public function remitterKyc(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'        => 'required|string',
        'mobileNumber'  => 'required|digits:10',
        'referenceKey'  => 'required|string',
        'latitude'      => 'required|numeric|between:-90,90',
        'longitude'     => 'required|numeric|between:-180,180',
        'externalRef'   => 'required|string|max:100',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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

    // 3️⃣ Optional: Validate biometricData JSON (if required)
    if (is_string($validated['biometricData'])) {
        json_decode($validated['biometricData'], true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'success' => false,
                'statuscode'  => 'ERR' ?? null,
            'message' => 'biometricData must be valid JSON' ?? 'Transaction Failed',
            'status' => 'biometricData must be valid JSON' ?? 'Transaction Failed',
                'error'   => [
                    'code'    => 'INVALID_BIOMETRIC_FORMAT',
                    'message' => 'biometricData must be valid JSON'
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 422);
        }
    }

    // 4️⃣ Provider Call
    try {

        $response = InstantPayHelper::remitterKyc($validated);

        // ✅ Success Condition
        if (($response['statuscode'] ?? null) === 'TXN') {

            return response()->json([
                'success' => true,
                'statuscode'  => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'OTP Verification Failed',
                'message' => 'Remitter KYC completed successfully',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
            'statuscode'  => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'OTP Verification Failed',
            'message' => $response['status'] ?? 'KYC Failed',
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

    // -------------------- Beneficiary Registration ---------------------


public function beneficiaryRegistration(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'                 => 'required|string',
        'beneficiaryMobileNumber'=> 'required|digits:10',
        'remitterMobileNumber'   => 'required|digits:10',
        'accountNumber'          => 'required|string|min:6|max:20',
        'ifsc'                   => 'required|string|size:11',
        'bankId'                 => 'required|string|max:50',
        'name'                   => 'required|string|max:255',
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
             'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::beneficiaryRegistration($validated);

        // ✅ Success Condition (OTP Sent)
        if (($response['statuscode'] ?? null) === 'OTP') {

            return response()->json([
                'success' => true,
                'statuscode'  => $response['statuscode'] ?? null,
            'status' =>'OTP sent successfully for beneficiary registration',
                'message' => 'OTP sent successfully for beneficiary registration',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
            'statuscode'  => $response['statuscode'] ?? null,
            'status' => $response['status'] ?? 'Registration Failed',
            'message' => $response['status'] ?? 'Beneficiary Registration Failed',
            'meta' => [
                'request_id'   => $requestId,
                'statuscode'   => $response['statuscode'] ?? null,
                'actcode'      => $response['actcode'] ?? null,
                'ipay_uuid'    => $response['ipay_uuid'] ?? null,
                'orderid'      => $response['orderid'] ?? null,
                'environment'  => $response['environment'] ?? null,
                'internalCode' => $response['internalCode'] ?? null,
                'timestamp'    => $response['timestamp'] ?? now(),
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

    // -------------------- Beneficiary Registration Verify ---------------------


public function verifyBeneficiaryRegistration(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'               => 'required|string',
        'remitterMobileNumber' => 'required|digits:10',
        'otp'                  => 'required|digits_between:4,8',
        'beneficiaryId'        => 'required|string|max:100',
        'referenceKey'         => 'required|string',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::verifyBeneficiaryRegistration($validated);

        // ✅ Success Condition
        if (($response['statuscode'] ?? null) === 'TXN') {

            return response()->json([
                'success' => true,
                'statuscode'  => $response['statuscode'] ?? null,
            'status' =>'Beneficiary verified successfully',
                'message' => 'Beneficiary verified successfully',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
            'statuscode'  => $response['statuscode'] ?? null,
            'status' => $response['status'] ?? 'Beneficiary Verification Failed',
            'message' => $response['status'] ?? 'Beneficiary Verification Failed',
            'meta' => [
                'request_id'   => $requestId,
                'statuscode'   => $response['statuscode'] ?? null,
                'actcode'      => $response['actcode'] ?? null,
                'ipay_uuid'    => $response['ipay_uuid'] ?? null,
                'orderid'      => $response['orderid'] ?? null,
                'environment'  => $response['environment'] ?? null,
                'internalCode' => $response['internalCode'] ?? null,
                'timestamp'    => $response['timestamp'] ?? now(),
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
    // -------------------- Beneficiary Delete ---------------------


public function deleteBeneficiary(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'               => 'required|string',
        'remitterMobileNumber' => 'required|digits:10',
        'beneficiaryId'        => 'required|string|max:100',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::deleteBeneficiary($validated);

        // ✅ Success Case (OTP Sent)
        if (($response['statuscode'] ?? null) === 'OTP') {

            return response()->json([
                'success' => true,
                'statuscode'   => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'Beneficiary Deletion Failed',
                'message' => 'OTP sent successfully for beneficiary deletion',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
            'statuscode'   => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'Beneficiary Deletion Failed',
            'message' => $response['status'] ?? 'Beneficiary Deletion Failed',
            'data'    => $response['data'] ?? null,
            'meta' => [
                'request_id'   => $requestId,
                'statuscode'   => $response['statuscode'] ?? null,
                'actcode'      => $response['actcode'] ?? null,
                'ipay_uuid'    => $response['ipay_uuid'] ?? null,
                'orderid'      => $response['orderid'] ?? null,
                'environment'  => $response['environment'] ?? null,
                'internalCode' => $response['internalCode'] ?? null,
                'timestamp'    => $response['timestamp'] ?? now(),
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

    // -------------------- Beneficiary Delete Verify --------------------

public function verifyDeleteBeneficiary(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'               => 'required|string',
        'remitterMobileNumber' => 'required|digits:10',
        'otp'                  => 'required|digits_between:4,8',
        'beneficiaryId'        => 'required|string|max:100',
        'referenceKey'         => 'required|string',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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

    // 3️⃣ Provider Call
    try {

        $response = InstantPayHelper::verifyDeleteBeneficiary($validated);

        // ✅ Success Case
        if (($response['statuscode'] ?? null) === 'TXN') {

            return response()->json([
                'success' => true,
                'statuscode'   => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'Beneficiary Deletion Failed',
                'message' => 'Beneficiary deleted successfully',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
            'statuscode'   => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'Beneficiary Deletion Failed',
            'message' => $response['status'] ?? 'Beneficiary Delete Verification Failed',
            'data'    => $response['data'] ?? null,
            'meta' => [
                'request_id'   => $requestId,
                'statuscode'   => $response['statuscode'] ?? null,
                'actcode'      => $response['actcode'] ?? null,
                'ipay_uuid'    => $response['ipay_uuid'] ?? null,
                'orderid'      => $response['orderid'] ?? null,
                'environment'  => $response['environment'] ?? null,
                'internalCode' => $response['internalCode'] ?? null,
                'timestamp'    => $response['timestamp'] ?? now(),
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

    // -------------------- Generate OTP for Txn  --------------------
   

public function generateTransactionOtp(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'               => 'required|string',
        'remitterMobileNumber' => 'required|digits:10',
        'amount'               => 'required|numeric|min:100|max:5000',
        'referenceKey'         => 'required|string',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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
    $merchantId = $request->header('X-MERCHANT-ID');


    // 3️⃣ Provider Call
    try {
        
         // 3️⃣ Lock Merchant Row (Race Condition Safe)
        $merchantDetails = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        if (!$merchantDetails) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'statuscode'  => 'ERR' ?? null,
            'message' => 'Invalid Merchant ID' ?? 'Transaction Failed',
            'status' => 'Invalid Merchant ID' ?? 'Transaction Failed',
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
        $minBalance        = 100.00;
        $txnAmount=    $request->amount;
        $merchantOpBalanceCmp=$merchantOpBalance-$minBalance;


        if ($merchantOpBalanceCmp < $minBalance) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'statuscode'  => 'ERR' ?? null,
            'message' => 'Insufficient wallet balance' ?? 'Transaction Failed',
            'status' => 'Insufficient wallet balance' ?? 'Transaction Failed',
                'error'   => [
                    'code'    => 'LOW_BALANCE_ERROR',
                    'message' => 'Insufficient wallet balance',
                ],
                'data' => [
                    'available_balance' => $merchantOpBalance,
                    'required_balance'  => $txnAmount+$minBalance,
                ],
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 422);
        }

        $response = InstantPayHelper::generateTransactionOtp($validated);

        // ✅ Success Case (OTP Generated)
        if (($response['statuscode'] ?? null) === 'OTP') {

            return response()->json([
                'success' => true,
                'statuscode'   => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'Beneficiary Deletion Failed',
                'message' => 'Transaction OTP generated successfully',
                'data'    => $response['data'] ?? null,
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

        // ❌ Failure Case
        return response()->json([
            'success' => false,
            'statuscode'   => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'Beneficiary Deletion Failed',
            'message' => $response['status'] ?? 'Transaction OTP Generation Failed',
            'data'    => $response['data'] ?? null,
            'meta' => [
                'request_id'   => $requestId,
                'statuscode'   => $response['statuscode'] ?? null,
                'actcode'      => $response['actcode'] ?? null,
                'ipay_uuid'    => $response['ipay_uuid'] ?? null,
                'orderid'      => $response['orderid'] ?? null,
                'environment'  => $response['environment'] ?? null,
                'internalCode' => $response['internalCode'] ?? null,
                'timestamp'    => $response['timestamp'] ?? now(),
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

    // -------------------- Transaction  --------------------


public function dmtTransaction(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'               => 'required|string',
        'remitterMobileNumber' => 'required|digits:10',
        'accountNumber'        => 'required|string|min:6|max:20',
        'ifsc'                 => 'required|string|size:11',
        'transferMode'         => 'required|in:IMPS,NEFT',
        'transferAmount'       => 'required|numeric|min:100|max:5000',
        'latitude'             => 'required|numeric',
        'longitude'            => 'required|numeric',
        'referenceKey'         => 'required|string',
        'otp'                  => 'required|digits_between:4,8',
        'externalRef'          => 'required|string|max:100',
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
            'statuscode'  => 'ERR' ?? null,
            'message' => 'Input validation failed' ?? 'Transaction Failed',
            'status' => 'Input validation failed' ?? 'Transaction Failed',
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
    $amount=$request->transferAmount;

    try {

        DB::beginTransaction();

        // 3️⃣ Idempotency Check
        $exists = DB::table('dmt_transactions')
            ->where('reference_key', $validated['referenceKey'])
            ->lockForUpdate()
            ->first();

        if ($exists) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'statuscode'  => 'ERR' ?? null,
            'status' => 'Duplicate transaction reference' ?? 'Transaction Failed',
                'message' => 'Duplicate transaction reference',
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 409);
        }

        // 4️⃣ Wallet Balance Check
        $wallet = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        $merchant=$wallet;
        $openingBal=$wallet->amount;
        if (!$wallet || $wallet->amount < $validated['transferAmount']) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'statuscode'  => 'ERR' ?? null,
            'status' => 'Insufficient wallet balance' ?? 'Transaction Failed',
                'message' => 'Insufficient wallet balance',
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 400);
        }

        // Fetch local commissions for the remittance package
            $commissions = DB::table('commissions')
                ->where('packagesId', $merchant->packageId)
                ->where('service', 'DMT')
                ->get() ?? [];

        if ($commissions->isEmpty()) {
                return response()->json([
                     'statuscode'  => 'ERR' ?? null,
            'status' => 'Insufficient wallet balance' ?? 'Transaction Failed',
                
                    'success'  => false,
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

        if ($item->service === 'DMT' && $amount >= $from && $amount <= $to) {
            // Calculate charges
            $charges = $item->charge_in === 'Percentage'
                ? $amount * ((float) $item->charge) / 100
                : (float) $item->charge;
            
            $comm=$item->commissions_in==='Percentage'
                ? $amount *((float)$item->commissions)/100
                :(float) $item->commissions;

            // Calculate TDS
            $tds = $item->tds_in === 'Percentage'
                ? $charges * ((float) $item->tds) / 100
                : (float) $item->tds;

            // Exit loop once a matching commission is found
            break;
        }
    }


            // $charges and $tds now have the calculated values
        //dd($charges, $tds);die();

            // ✅ Fallback charges
        //   if ($comm == 0 && $amount >= 100) {
        //         $comm = $amount * 0.01;
        //         $tds  = $comm * 0.05; // example 5%
        //     }

            $totalDeduct = $amount + $charges + $tds;
            $closingBal  = $openingBal - $totalDeduct;
              if (!$totalDeduct || $totalDeduct < $totalDeduct) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                 'statuscode'  => 'ERR' ?? null,
            'status' => 'Insufficient wallet balance' ?? 'Transaction Failed',
                'message' => 'Insufficient wallet balance with charges',
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 400);
        }

        // 5️⃣ Debit Wallet (Temporary Hold)
        DB::table('remittances')
            ->where('remId', $merchantId)
            ->update([
                'amount' => $closingBal,
            ]);

        // 6️⃣ Create Transaction Log (PENDING)
        DB::table('dmt_transactions')->insert([
                'request_id'        => $requestId,
                'reference_key'     => $validated['referenceKey'],
                'merchant_id'       => $request->header('X-MERCHANT-ID'),

                'outlet_id'         => $validated['outlet'],
                'amount'            => $validated['transferAmount'],

                'charges'           => $charges ?? 0,
                'tds'               => $tds ?? 0,
                'commission'        => $commission ?? 0,

                'accountNumber'     => $validated['accountNumber'],
                'ifsc'              => $validated['ifsc'],
                'externalRef'       => $validated['externalRef'] ?? null,
                'beneficiaryName'   => $validated['beneficiaryName'] ?? null,
                'opening_balance'   =>$openingBal,
                'closing_balance'   =>$closingBal,

                'request_body'      => json_encode($request->all()),
                'provider_response' => null, // update after API response

                'status'            => 'PENDING',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

        // 7️⃣ Call Provider
        $response = InstantPayHelper::dmtTransaction($validated);

        // ✅ SUCCESS
        if (($response['statuscode'] ?? null) === 'TXN') {

            DB::table('dmt_transactions')
                ->where('reference_key', $validated['referenceKey'])
                ->update([
                    'status'      => 'SUCCESS',
                    'provider_txn'=> $response['orderid'] ?? null,
                    'provider_response' => json_encode($response),
                    'beneficiaryName' =>$response['data']['beneficiaryName'] ?? null,
                    'updated_at'  => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'statuscode'   => $response['statuscode'] ?? null,
                'status' => $response['status'] ?? 'Transaction successful',
                'message' => 'Transaction successful',
                'data'    => [
                            'account'=>$response['data']['account'] ?? null,
                            'openingBal'=>$openingBal ?? null,
                            'amount'=>$amount ?? null,
                            'TotalCharges'=>$charges+$tds ?? null,
                            'closingBal'=>$closingBal ?? null,
                            'utr' => $response['data']['txnReferenceId'] ?? null,
                            
                            'beneficiaryAccount'=>$response['data']['beneficiaryAccount'] ?? null,
                            'beneficiaryIfsc'=>$response['data']['beneficiaryIfsc'] ?? null,
                            'accbeneficiaryNameount'=>$response['data']['beneficiaryName'] ?? null,
                ],
                'meta' => [
                    'request_id'  => $requestId,
                    'statuscode'  => $response['statuscode'] ?? null,
                    'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                    'orderid'     => $response['orderid'] ?? null,
                    'timestamp'   => $response['timestamp'] ?? now(),
                ]
            ], 200);
        }

        // ❌ FAILURE → Rollback Wallet
          DB::table('remittances')
            ->where('remId', $merchantId)
            ->update([
                'balance' => $openingBal,
            ]);

        DB::table('dmt_transactions')
            ->where('reference_key', $validated['referenceKey'])
            ->update([
                'status'     => 'FAILED',
                'provider_response' => json_encode($response),
                'updated_at' => now(),
            ]);

        DB::commit();

        return response()->json([
            'success' => false,
             'statuscode'  => $response['statuscode'] ?? null,
            'status' => 'Insufficient wallet balance' ?? 'Transaction Failed',
            'message' => $response['status'] ?? 'Transaction failed',
            'meta' => [
                'request_id'  => $requestId,
                'statuscode'  => $response['statuscode'] ?? null,
                'orderid'     => $response['orderid'] ?? null,
                'timestamp'   => $response['timestamp'] ?? now(),
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
                'timestamp'  => now()
            ]
        ], 500);
    }
}
    
    
    



public function accountVerify(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    // 1️⃣ Auth Validation
    $authCheck = $this->validateAuth($request, $requestId);
    if ($authCheck instanceof JsonResponse) {
        return $authCheck;
    }

    // 2️⃣ Request Validation
    $validator = Validator::make($request->all(), [
        'outlet'        => 'required|string',
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

    try {

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

            return response()->json([
                'success' => true,
                'message' => 'Account verified successfully',
                'data' => [
                    'payee_name' => $payeeName,
                    'account_no' => $maskedAccount,
                    'ifsc'       => $ifsc,
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

        // ❌ FAILURE
        return response()->json([
            'success' => false,
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
