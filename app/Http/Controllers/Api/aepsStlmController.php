<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\InstantPayHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;



class aepsStlmController extends Controller
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

    if($merchant->packageId==0){
            
              return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_PACKAGE',
                    'message' => 'No Package Assigned. Please contact Admin'
                ],
                'meta' => [
                    'merchantId' => $merchantId,
                    'request_ip' => $clientIp,
                    'timestamp'  => now()
                ]
            ], 403);
        }

         if ($merchant->callback_url == null) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_CALLBACK',
                    'message' => 'CallBack Url not setup. Please contact Admin.'
                ],
                'meta' => [
                    'merchantId' => $merchantId,
                    'request_ip' => $clientIp,
                    'timestamp'  => now()
                ]
            ], 403);
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

    public function aepsStlm(Request $request)
    {
               $requestId = 'req_' . Str::uuid();

        // 1️⃣ Auth Validation
        $authCheck = $this->validateAuth($request, $requestId);
        if ($authCheck instanceof JsonResponse) {
            return $authCheck;
        }

        // 2️⃣ Request Validation
         $validator = Validator::make($request->all(), [
         'outLet' => 'required|string',
           'mobileNo'          => 'required|string|max:15',
            'txnAmount' => 'required|numeric|min:100|max:4999',
            'accountNo'         => 'required|string|max:20',
            'ifscCode'          => 'required|string|size:11',
            'bankName'          => 'required|string|max:150',
            'accountHolderName' => 'required|string|max:150',
            'RefNo'             => 'required|string|max:50',
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
    $merchantId = $request->header('X-MERCHANT-ID');
   $amount = $validated['txnAmount'];
    DB::beginTransaction();

        // 3️⃣ Idempotency Check
        $exists = DB::table('aeps_stlm')
             ->where('refId', $request->RefNo)
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
        if (!$wallet || $wallet->amount < $amount){

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
                ->where('service', 'STLM')
                ->get() ?? [];

        if ($commissions->isEmpty()) {
                return response()->json([
                     'statuscode'  => 'ERR' ?? null,
                     'status' => 'package not allow ' ?? 'Transaction Failed',
                
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

        if ($item->service === 'STLM' && $amount >= $from && $amount <= $to){
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


            $totalDeduct = $amount + $charges + $tds;
            $closingBal  = $openingBal - $totalDeduct;
             if ($openingBal < $totalDeduct) {
    DB::rollBack();

    return response()->json([
        'success' => false,
        'statuscode' => 'ERR',
        'status' => 'Insufficient wallet balance',
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


              // ✅ Step 6: Insert Payout Record
        $paymentId  = 'SCP' . strtoupper(Str::random(10));
        $rawPayload = $request->all();

        DB::table('aeps_stlm')->insert([
            'remId'            => $merchantId,
            'outlet'            =>$validated['outLet'],
            'email'            => $merchant->email,
            'payment_id'       => $paymentId,
            'amount'           => $amount,
            'charge'           => $charges,
            'tds'              => $tds,
            'status'           => 'Initiated',
            'opening_balance'  => $openingBal,
            'closing_balance'  => $closingBal,
            'bank_name'        => $request->bankName,
            'ifsc_code'        => $request->ifscCode,
            'acc_no'           => $request->accountNo,
            'beneficiary_name' => $request->accountHolderName,
            'refId'            => $request->RefNo,
            'requestBody'      => json_encode($rawPayload),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        
         // ✅ Step 8: Call Bank API
        $bankResponse = [];
        try {
            $AEPS_DUMMY=1;

        if ($AEPS_DUMMY == true) {

    // ✅ Dummy Response (Testing Mode)
    $bankResponse = [
        "status" => true,
        "message" => "Payment processed successfully",
        "data" => [
            "success" => true,
            "message" => "Payment processed successfully",
            "result" => [
                "status" => "Pending",
                "accountNumber" => $request->accountNo,
                "ifscCode" => strtoupper($request->ifscCode),
                "transactionId" => "DUMMYTXN" . rand(100000,999999),
                "amount" => (string)$amount,
                "utr" => null
            ],
            "timestamp" => now(),
            "txnId" => $paymentId
        ]
    ];

    Log::info("Dummy Bank Response Used", $bankResponse);

} else {

    // ✅ Real API Call
    $bankResponse = Http::timeout(30)
        ->post('https://api.credxpay.com/api/payout/v6/initiate', [
            'amount'                  => $amount,
            'senderMobile'            => $request->mobileNo,
            'beneficiaryIfscCode'     => strtoupper($request->ifscCode),
            'beneficiaryAccountNumber'=> $request->accountNo,
            'beneficiaryName'         => $request->accountHolderName,
            'paymentMode'             => 'IMPS',
            'txnId'                   => $paymentId,
            'callbackUrl'             => route('payout.callback.handler'),
        ])->json();

    Log::channel('fundtransfer')->info("Payout Response", $bankResponse);
}
                Log::channel('fundtransfer')->info("Payout Response Received from Bank", [
        'ip'       => $request->ip(),
        'response' => $bankResponse
    ]);
  
//return $bankResponse; die();
            // ✅ If success → update payout table with UTR + transactionId
            if (!empty($bankResponse['data']['success']) && $bankResponse['data']['success'] === true) {
                DB::table('aeps_stlm')
                    ->where('refId', $request->RefNo)
                    ->update([
                        'bank_ref_no' => $bankResponse['data']['result']['utr'] ?? null,

                        'status'     => $bankResponse['data']['result']['status'] ?? 'Success',
                        'updated_at' => now(),
                        'responseBody'   => json_encode($bankResponse),
                        'orderId'        => $bankResponse['data']['result']['transactionId'] ?? null,
                    ]);

                    DB::table('users')
                    ->where('id', 1)
                    ->decrement('balance', $amount);
                    DB::commit();
                    return response()->json([
                    'success' => true,
                    'status'  => 'INITIATED',
                    'message' => 'AEPS Settlement Initiated Successfully',
                    'data'    => [
                        'payment_id' => $paymentId,
                        'amount'     => $amount,
                        'orderId'   => $bankResponse['data']['result']['transactionId'] ?? null,
                        'beneficiary_name'=>$validated['accountHolderName'] ?? null,
                        'bank_name'=>$validated['bankname'] ?? null,
                        'acc_no'=>$validated['accountNo'] ?? null,
                        'ifsc_code'=>$validated['ifscCode'] ?? null,
                    ]
                ]);
                    
            }
            else {
                DB::table('aeps_stlm')
                    ->where('refId', $request->RefNo)
                    ->update([
                        'status' => 'FAILED',
                        'responseBody' => json_encode($bankResponse),
                        'updated_at' => now()
                    ]);

                // 💰 Refund wallet
                DB::table('remittances')
                    ->where('remId', $merchantId)
                    ->increment('amount', $totalDeduct);
            }
                        DB::commit();

        } 
        catch (\Exception $e) {
   

    Log::error("Bank API Error: " . $e->getMessage());
     DB::rollBack();
    return response()->json([
        'success' => false,
        'message' => 'Bank API failed',
        'error'   => $e->getMessage()
    ], 500);
}
    }

    

}
