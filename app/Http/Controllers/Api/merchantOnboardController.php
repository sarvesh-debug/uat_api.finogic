<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\InstantPayHelper;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\InstMerchant;


class merchantOnboardController extends Controller
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


public function initiateSignup(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    /* ===============================
       1️⃣ Validate Required Headers
    ================================ */
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

    /* ===============================
       2️⃣ Authentication
    ================================ */
    $auth = $this->localAuth($apiKey, $merchantId, $request);

   // return $auth;

    if ($auth instanceof \Illuminate\Http\JsonResponse) {
        return $auth; // Stop execution if auth failed
    }

    /* ===============================
       3️⃣ Validate Request Body
    ================================ */
    $validator = Validator::make($request->all(), [
        'mobile'        => 'required|digits:10',
        'email'         => 'required|email',
        'aadhaar'       => 'required|digits:12',
        'pan'           => 'required|string|size:10',
        'bankAccountNo' => 'required|string',
        'bankIfsc'      => 'required|string',
        'latitude'      => 'required',
        'longitude'     => 'required',
        'consent'       => 'required|in:Y,N',
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

    /* ===============================
       4️⃣ Provider API Call
    ================================ */
    try {
        $response = InstantPayHelper::initiateSignup($validated);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'PROVIDER_EXCEPTION',
                'message' => 'Upstream provider error'
            ],
            'meta' => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 502);
    }
         InstMerchant::create([
                'merchant_id'      => $merchantId,
                'request_id'       => $requestId,
                'mobile'           => $validated['mobile'],
                'email'            => $validated['email'],
                'aadhaar'          => $validated['aadhaar'],
                'pan'              => $validated['pan'],
                'bank_account_no'  => $validated['bankAccountNo'],
                'bank_ifsc'        => $validated['bankIfsc'],
                'latitude'         => $validated['latitude'],
                'longitude'        => $validated['longitude'],
                'otp_reference_id' => $response['data']['otpReferenceID'] ?? null,
                'hash'             => $response['data']['hash'] ?? null,
                'ipay_uuid'        => $response['ipay_uuid'] ?? null,
                'orderid'          => $response['orderid'] ?? null,
                'status'           => 'otp_sent',
                'provider_response'=> json_encode($response)
            ]);
    /* ===============================
       5️⃣ Success Response
    ================================ */
    if (isset($response['statuscode']) && $response['statuscode'] === 'TXN') {
       
           

        return response()->json([
            'success' => true,
            'code'  => $response['statuscode'] ?? 'TXN',
            'message' => 'OTP has been sent to Aadhaar linked mobile number',
            'data'    => [
                'otpReferenceId' => $response['data']['otpReferenceID'] ?? null,
                'hash'           => $response['data']['hash'] ?? null,
                'aadhaar'        => $response['data']['aadhaar'] ?? null,
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 200);
    }

    /* ===============================
       6️⃣ Failure Response
    ================================ */
    return response()->json([
        'success' => false,
        'error'   => [
           'code'  => $response['statuscode'] ?? 'ERR',
            'message' => $response['status'] ?? 'Transaction failed'
        ],
        'bank_reference' => [
            'ipay_uuid' => $response['ipay_uuid'] ?? null,
            'orderid'   => $response['orderid'] ?? null
        ],
        'meta' => [
            'merchantId' => $merchantId,
            'request_id' => $requestId,
            'timestamp'  => now()
        ]
    ], 400);
}
    // //Signup eKYC Validate


public function initiateSignupVerify(Request $request)
{
    $requestId = 'req_' . Str::uuid();

    /* ===============================
       1️⃣ Validate Required Headers
    ================================ */
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

    /* ===============================
       2️⃣ Authentication
    ================================ */
    $auth = $this->localAuth($apiKey, $merchantId, $request);

    if ($auth instanceof \Illuminate\Http\JsonResponse) {
        return $auth;
    }

    /* ===============================
       3️⃣ Validate Request Body
    ================================ */
    $validator = Validator::make($request->all(), [
        'otpReferenceID' => 'required|string',
        'otp'            => 'required|digits:6',
        'hash'           => 'required|string',
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

    /* ===============================
       4️⃣ Provider API Call
    ================================ */
    try {
        $response = InstantPayHelper::initiateSignupVerify($validated);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'PROVIDER_EXCEPTION',
                'message' => 'Upstream provider error'
            ],
            'meta' => [
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 502);
    }

      $merchantRecord = InstMerchant::where('otp_reference_id', $validated['otpReferenceID'])->first();
    /* ===============================
       5️⃣ Success Response
    ================================ */
    if (isset($response['statuscode']) && $response['statuscode'] === 'TXN') {

      

        if ($merchantRecord) {
            $merchantRecord->update([
                'otp'         => $validated['otp'],
                'outlet_id'   => $response['data']['outletId'] ?? null,
                'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                'orderid'     => $response['orderid'] ?? null,
                'status'      => 'verified',
                'provider_response' => json_encode($response)
            ]);
        }


        return response()->json([
            'success' => true,
            'code'  => $response['statuscode'] ?? 'TXN',
            'message' => 'Merchant onboarding successful',
            'data'    => [
                'outletId' => $response['data']['outletId'] ?? null,
                'full_response_data' => $response['data'] ?? null
            ],
            'bank_reference' => [
                'ipay_uuid' => $response['ipay_uuid'] ?? null,
                'orderid'   => $response['orderid'] ?? null
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 200);
    }

    /* ===============================
       6️⃣ Failure Response
    ================================ */
            if ($merchantRecord) {
            $merchantRecord->update([
                'status' => 'failed',
                'provider_response' => json_encode($response)
            ]);
        }
            return response()->json([
        'success' => false,
        'error'   => [
            'code'  => $response['statuscode'] ?? 'ERR',
            'message' => $response['status'] ?? 'OTP verification failed'
        ],
        'bank_reference' => [
            'ipay_uuid' => $response['ipay_uuid'] ?? null,
            'orderid'   => $response['orderid'] ?? null
        ],
        'meta' => [
            'merchantId' => $merchantId,
            'request_id' => $requestId,
            'timestamp'  => now()
        ]
    ], 400);
}


public function clientList(Request $request)
{
    $requestId = 'req_' . \Str::uuid();

    /* ===============================
       1️⃣ Validate Required Headers
    ================================ */
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

    /* ===============================
       2️⃣ Authentication
    ================================ */
    $auth = $this->localAuth($apiKey, $merchantId, $request);

    if ($auth instanceof \Illuminate\Http\JsonResponse) {
        return $auth;
    }

    /* ===============================
       3️⃣ Fetch Client Data
    ================================ */
    try {

        $clients = \App\Models\InstMerchant::where('merchant_id', $merchantId)
            ->whereNotNull('outlet_id')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($client) {
                return [
                    'pan_no'       =>$client->pan,
                    'mobile'      => $client->mobile,
                    'email'       => $client->email,
                    'outlet_id'   => $client->outlet_id,
                    'longitude'   => $client->longitude,
                    'latitude'     => $client->latitude,
                    'status'      => $client->status,
                    'addedAt'     => $client->created_at->toDateTimeString(), // 🔥 renamed here
                ];
            });

        return response()->json([
            'success' => true,
            'code'    => 'CLIENT_LIST_FETCHED',
            'message' => 'Client list fetched successfully',
            'data'    => $clients,
            'meta'    => [
                'merchantId' => $merchantId,
                'total'      => $clients->count(),
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 200);

    } catch (\Exception $e) {

        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'SERVER_ERROR',
                'message' => 'Unable to fetch client list'
            ],
            'meta' => [
                'merchantId' => $merchantId,
                'request_id' => $requestId,
                'timestamp'  => now()
            ]
        ], 500);
    }
}
    //Mobile Change Initiate

    public function MobileChangeInitiate(Request $request)
    {
        $request->validate([
            'existingMobileNumber'        => 'required',
            'newMobileNumber'         => 'required',
       f
        ]);

        $response = InstantPayHelper::MobileChangeInitiate($request->all());
        if (isset($response['statuscode']) && $response['statuscode'] === 'TXN') {
            return response()->json([
                'success' => true,
            //'respose' =>$response,
            'status' =>'Outlet mobile change request accepted, please verify OTP to complete this process',
            'data' =>$response['data'] ?? null,
            'timestamp' => $response['timestamp'] ?? null,
            'ipay_uuid'           => $response['ipay_uuid'] ?? null,
            'orderid'           => $response['orderid'] ?? null,
            'environment'=>$response['environment'],
            ],200);
        }
    
    
        return response()->json([
            'Provider' =>'Support Team CodeGraphi Technology',
            'success' => false,
            'actcode'=>$response['actcode'],
            'message' => $response['status'] ?? 'Unknown error',
            'timestamp'=>$response['timestamp'],
            'ipay_uuid'=>$response['ipay_uuid'],
            'orderid'=>$response['orderid'],
            'environment'=>$response['environment'],
    
        ], 400);


    }

    // Mobile Change Validate
    public function MobileChangeInitiateVerify(Request $request)
    {
        $request->validate([
            'existingMobileNumber'        => 'required',
            'newMobileNumber'         => 'required',
            'existingMobileNumberOTP'        => 'required',
            'newMobileNumberOTP'         => 'required',
       
        ]);

        $response = InstantPayHelper::MobileChangeInitiateVerify($request->all());
        if (isset($response['statuscode']) && $response['statuscode'] === 'TXN') {
            return response()->json([
                'success' => true,
            //'respose' =>$response,
            'status' =>"Mobile Number successfully changed",
            'data' =>$response['data'] ?? null,
            'timestamp' => $response['timestamp'] ?? null,
            'ipay_uuid'           => $response['ipay_uuid'] ?? null,
            'orderid'           => $response['orderid'] ?? null,
            'environment'=>$response['environment'],
            ],200);
        }
    
    
        return response()->json([
            'Provider' =>'Support Team CodeGraphi Technology',
            'success' => false,
            'actcode'=>$response['actcode'],
            'message' => $response['status'] ?? 'Unknown error',
            'timestamp'=>$response['timestamp'],
            'ipay_uuid'=>$response['ipay_uuid'],
            'orderid'=>$response['orderid'],
            'environment'=>$response['environment'],
    
        ], 400);


    }
        // Merchant List
    public function merchantList(Request $request)
    {
        $request->validate([
            'pageNumber' => 'required|integer|min:1',
            'recordsPerPage' => 'required|integer|min:1',
            'outletId' => 'nullable|string',
            'mobile' => 'nullable|string',
            'pan' => 'nullable|string',
       
        ]);

        $response = InstantPayHelper::merchantList($request->all());

        //return $response;
        if (isset($response['statuscode']) && $response['statuscode'] === 'TXN') {
            return response()->json([
                'success' => true,
            //'respose' =>$response,
            'status' =>"Transaction Successful",
            'data' =>$response['data'] ?? null,
            'timestamp' => $response['timestamp'] ?? null,
            'ipay_uuid'           => $response['ipay_uuid'] ?? null,
            'orderid'           => $response['orderid'] ?? null,
            'environment'=>$response['environment'],
            ],200);
        }
    
    
        return response()->json([
            'Provider' =>'Support CredxPay',
            'success' => false,
            'actcode'=>$response['actcode'],
            'message' => $response['status'] ?? 'Unknown error',
            'timestamp'=>$response['timestamp'],
            'ipay_uuid'=>$response['ipay_uuid'],
            'orderid'=>$response['orderid'],
            'environment'=>$response['environment'],
    
        ], 400);

    }
            
    

}
