<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\InstantPayHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Helpers\AeronpayHelper; 
use Symfony\Component\HttpFoundation\JsonResponse;
class XpressaeronpayController extends Controller
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
    ->where('name', 'PAYOUT2')
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
    if ($merchant->payout5 !== 1) {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'MERCHANT_INACTIVE_SRVICE',
                'message' => 'Merchant Payout pipe 2 Service is inactive'
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

    public function sendPayout(Request $request)
{

    //return $request;die();
     $requestId = 'req_' . Str::uuid();
    try {
      
        
         /* ===============================
           🔐 AUTH VALIDATION
        ================================ */
        $authCheck = $this->validateAuth($request, $requestId);

        if ($authCheck instanceof JsonResponse) {
            return $authCheck;
        }

        // ✅ Get Merchant
        $remittance = DB::table('remittances')
            ->where('apikey', $request->header('X-API-KEY'))
            ->where('remId', $request->header('X-MERCHANT-ID'))
            ->first();

        // ✅ Step 2: Validate Input
        $validator = Validator::make($request->all(), [
            'mobileNo'          => 'required|string|max:15',
            'txnAmount'         => 'required|numeric|min:100',
            'accountNo'         => 'required|string|max:20',
            'ifscCode'          => 'required|string|size:11',
            'bankName'          => 'required|string|max:150',
            'accountHolderName' => 'required|string|max:150',
            'RefNo'             => 'required|string|max:50',
            'web'               => 'required|in:YES,OWN'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // ✅ Check Duplicate RefNo
        $existingTxn = DB::table('xpresspayout2')
            ->where('remId', $remittance->remId)
            ->where('refId', $request->RefNo)
            ->first();

        if ($existingTxn) {
            return response()->json([
                'status'  => false,
                'message' => 'Duplicate RefNo. Transaction with this RefNo already exists.'
            ], 409);
        }
        $adminBalance = DB::table('users')
    ->where('id', 1)
    ->first();

if ($adminBalance && $adminBalance->balance < $request->txnAmount) {
    return response()->json([
        'status'  => false,
        'message' => 'Please contact Admin.',
    ], 400); // Bad Request
}

        if($remittance->packageId==0){
            return response()->json([
                'status'  => false,
                'message' => 'No Package Assigned. Please contact Admin.',
            ], 400);
        }

         if ($remittance->callback_url == null) {
            return response()->json([
                'status' => false,
                'message' => 'CallBack Url not setup. Please contact Admin.',
            ], 400);
        }
         $package = DB::table('packages')->where('id',$remittance->packageId)->first();
            if(!$package || $package->status != 1){
                return response()->json([
                    'status'  => false,
                    'message' => 'Assigned Package is Inactive. Please contact Admin.',
                ], 400);
            }
        // ✅ Step 4: Check Wallet Balance
        $walletAmount = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email)
            ->value('amount');

        if (!$walletAmount || $walletAmount < $request->txnAmount) {
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient wallet balance. Please add funds.'
            ], 400);
        }

        $openingBal = $walletAmount;
        $amount     = $request->txnAmount;

        // ✅ Step 5: Fetch Charges & Commission
        $charges = 0;
        $tds     = 0;

        
       // Fetch local commissions for the remittance package
        $commissions = DB::table('commissions')
            ->where('packagesId', $remittance->packageId)
            ->where('service', 'PAYOUT2')
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

        // Loop through commissions to find the applicable range
       foreach ($commissions as $item) {
    // Ensure numeric comparison
    $from = (float) $item->from_amount;
    $to   = (float) $item->to_amount;

    if ($item->service === 'PAYOUT' && $amount >= $from && $amount <= $to) {
        // Calculate charges
        $charges = $item->charge_in === 'Percentage'
            ? $amount * ((float) $item->charge) / 100
            : (float) $item->charge;

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
        if ($charges == 0 && $amount >= 100) {
            $charges = $amount * 0.01; // 1%
            $tds     = $charges * 0.18;// 2%
        }

        $totalDeduct = $amount + $charges + $tds;
        $closingBal  = $walletAmount - $totalDeduct;

        if ($closingBal < 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient balance after charges & TDS.'
            ], 400);
        }

        // ✅ Step 6: Insert Payout Record
        $paymentId  = 'CREDX' . strtoupper(Str::random(10));
        $rawPayload = $request->all();

        DB::table('xpresspayout2')->insert([
            'remId'            => $remittance->remId,
            'email'            => $remittance->email,
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

        // ✅ Step 7: Deduct Wallet Balance
        DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email)
            ->update(['amount' => $closingBal]);

        // ✅ Step 8: Call Bank API
        $bankResponse = [];
        try {
            $bankResponse = Http::post('https://uatapi.credxpay.com/api/imps/initiate-transaction', [
                'refId' => $paymentId,
                'email'=>$remittance->email,
                'phone'=>$remittance->phone,
                'amount' => $amount,
                'address'=>$remittance->city,
                'name' =>$request->accountHolderName,
                'client_referenceId' => $paymentId,
                'bankAccount' => $request->accountNo,
                'ifsc' => $request->ifscCode,
               
            ])->json();

           

            //  $bankResponse = AeronpayHelper::initiate([
            //     'merchant_id' => $remittance->remId,
            //     'email'=>$remittance->email,
            //     'phone'=>$remittance->phone,
            //     'amount' => $amount,
            //     'address'=>$remittance->city,
            //     'client_referenceId' => $paymentId,
            //     'bankAccount' => $request->accountNo,
            //     'ifsc' => $request->ifscCode,
    
            // ]);
        Log::channel('fundtransfer')->info("Payout Aeronpay Response Received from Bank", [
        'ip'       => $request->ip(),
        'response' => $bankResponse
    ]);
//return $bankResponse; die();
            // ✅ If success → update payout table with UTR + transactionId
            if (!empty($bankResponse['statusCode']) && $bankResponse['statusCode'] === '201') {
                DB::table('xpresspayout2')
                    ->where('refId', $request->RefNo)
                    ->update([
                        'bank_ref_no' => $bankResponse['utr'] ?? null,
                        'status'     => $bankResponse['status'] ?? 'Pending',
                        'updated_at' => now(),
                        'responseBody'   => json_encode($bankResponse),
                        'orderId'        => $bankResponse['transactionId'] ?? null,
                    ]);

                    DB::table('users')
                    ->where('id', 1)
                    ->decrement('balance', $amount);
            }

        } catch (\Exception $e) {
            Log::error("Bank API Error: " . $e->getMessage());
            $bankResponse = [
                "status"  => false,
                "message" => "Bank API call failed",
                "error"   => $e->getMessage()
            ];
        }

        // ✅ Step 10: Final Response
        return response()->json([
            "status"   => ($bankResponse['status'] ?? false) == true,
            'success'  =>'Initiated',
            "message"  =>  $bankResponse['message'] ?? "Payout initiated.",
            'remId'            => $remittance->remId,
            'email'            => $remittance->email,
            'payment_id'       => $paymentId,
            'utr'              => $bankResponse['utr'] ?? null,
            'amount'           => $amount,
            'charge'           => $charges,
            'gst'              => $tds,
            'opening_balance'  => $openingBal,
            'closing_balance'  => $closingBal,
            'bank_name'        => $request->bankName,
            'ifsc_code'        => $request->ifscCode,
            'acc_no'           => $request->accountNo,
            'beneficiary_name' => $request->accountHolderName,
            'refId'            => $request->RefNo,
            // 'requestBody'      => json_encode($rawPayload),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        

    } catch (\Exception $e) {

    return $e;
        Log::error("Payout Error: " . $e->getMessage());
        return response()->json([
            "status"  => false,
            "message" => "Unexpected server error",
            "error"   => $e->getMessage()
        ], 500);
    }
}


    public function checkPayoutStatus(Request $request)
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
       


        // ✅ Step 2: Validate Input
        $validator = Validator::make($request->all(), [
            'RefNo' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // ✅ Step 3: Find Transaction
        $transaction = DB::table('xpresspayout2')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email)
            ->where('refId', $request->RefNo)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status'  => false,
                'message' => 'Transaction not found.'
            ], 404);
        }

        // ✅ Step 4: Return Transaction Status
        return response()->json([
            'status'  => true,
            'message' => 'Transaction status fetched successfully.',
            'data'    => [
                'payment_id'       => $transaction->payment_id,
                'amount'           => $transaction->amount,
                'utr'              => $transaction->bank_ref_no,
                'charges'          => $transaction->charge,
                'gst'              => $transaction->tds,
                'status'           => $transaction->status, // e.g., Initiated, Success, Failed, Pending
                'opening_balance'  => $transaction->opening_balance,
                'closing_balance'  => $transaction->closing_balance,
                'bank_name'        => $transaction->bank_name,
                'ifsc'             => $transaction->ifsc_code,
                'account_no'       => $transaction->acc_no,
                'beneficiary_name' => $transaction->beneficiary_name,
                'ref_no'           => $transaction->refId,
                'created_at'       => $transaction->created_at,
                'updated_at'       => $transaction->updated_at,
            ]
        ], 200);

    } catch (\Exception $e) {
        Log::channel('payoutv5')->error('Exception during status check', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'An unexpected error occurred while checking transaction status.',
            'error'   => $e->getMessage()
        ], 500);
    }
}
}
