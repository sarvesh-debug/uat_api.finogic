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
class oldPayoutController extends Controller
{

   public function bankDetails(Request $request)
{
    try {

        $validated = [
            'outLet' => $request->outLet ?? '580942'
        ];

        $response = InstantPayHelper::getBankDetails($validated);

        if (($response['statuscode'] ?? null) === 'TXN') {

            return response()->json([
                'success' => true,
                'message' => 'Bank Details Fetched Successfully',
                'data'    => $response['data'] ?? null,
                'meta' => [
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
            'statuscode'  => 'ERR',
            'message' => $e->getMessage(),
            'status' => 'INTERNAL_SERVER_ERROR',
            'error'   => [
                'code'    => 'INTERNAL_SERVER_ERROR',
                'message' => $e->getMessage()
            ],
            'meta' => [
                'request_id' => uniqid(),
                'timestamp'  => now()
            ]
        ], 500);
    }
}
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

    public function getSender(Request $request)
{
    Log::channel('getsender')->info("Get Sender Request", [
    'ip' => $request->ip(),
    'payload' => $request->all()
]);

    try {
        // ✅ 1. Validation
        $validator = Validator::make($request->all(), [
            'apikey' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // ✅ 2. Auth
        $remittance = $this->localAuth($request->input('apikey'));

       // return $remittance;die();
         $remittanceId = $remittance->remId;
         //return $remittanceId;die();
        if (!$remittance) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized or invalid API token.'
            ], 401);
        }

        // ✅ 3. Check KYC and Payout
        if ($remittance->isKyc != 1|| $remittance->status != 'success') {
            return response()->json([
                'status'  => false,
                'message' => 'KYC not verified or payout not allowed.',
            ], 403);
        }

        // ✅ 4. Fetch beneficiaries
        $remittanceId = $remittance->remId;
        $remittanceEmail = $remittance->email;
        $remittancePhone = $remittance->phone ;

        $beneficiaries = DB::table('payoutbene')
            ->where('remId', $remittanceId)
            ->get();

        // ✅ 5. Generate reference key (valid 15 min)
        $referenceKey = strtoupper(Str::random(12));
        $expiresAt = Carbon::now()->addMinutes(15);

        // Optional: Clean old keys first
        DB::table('reference_keys')->where('remId', $remittanceId)->delete();

        DB::table('reference_keys')->insert([
            'remId'   => $remittanceId,
            'phone'         => $remittancePhone,
            'reference_key' => $referenceKey,
            'expires_at'    => $expiresAt,
            'created_at'    => now()
        ]);

        // ✅ 6. Final response
        return response()->json([
            'status'         => true,
            'message'        => 'Beneficiaries fetched successfully.',
            'remittance_name'  => $remittance->name ?? '',
            'remittance_id'    => $remittanceId,
            'phone'          => $remittancePhone,
            'reference_key'  => $referenceKey,
            'expires_at'     => $expiresAt->toDateTimeString(),
            'beneficiaries'  => $beneficiaries
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong.',
            'error' => $e->getMessage()
        ], 500);
    }
}

 public function beneficiaryAdd(Request $request)
{

    try {

        // LOG: Incoming request
        Log::channel('beneficiary')->info("Incoming Beneficiary Request", [
            'ip'      => $request->ip(),
            'payload' => $request->all()
        ]);

        // Step 1: Validate request
        $validator = Validator::make($request->all(), [
            'remId'         => 'required|string',
            'reference_key' => 'required|string',
            'benename'      => 'required|string|max:150',
            'beneMobile'    => 'required|string|max:20',
            'accno'         => 'required|string|max:50',
            'bank_name'     => 'required|string|max:150',
            'ifsc'          => 'required|string|max:20',
            'latitude'      => 'required|string|max:20',
            'longitude'     => 'required|string|max:20'
        ]);

        if ($validator->fails()) {

            Log::channel('beneficiary')->warning("Validation Failed", [
                'errors' => $validator->errors()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Step 2: Validate reference key
        $keyRecord = DB::table('reference_keys')
            ->where('remId', $request->remId)
            ->where('reference_key', $request->reference_key)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$keyRecord) {

            Log::channel('beneficiary')->warning("Reference Key Invalid", [
                'remId' => $request->remId,
                'reference_key' => $request->reference_key
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Invalid or expired reference key.'
            ], 403);
        }

        // Wallet balance
        $openingBal = DB::table('remittances')
            ->where('remId', $request->remId)
            ->value('amount');

        Log::channel('beneficiary')->info("Wallet Check", [
            'remId'   => $request->remId,
            'opening' => $openingBal
        ]);

        if ($openingBal < 4) {

            Log::channel('beneficiary')->warning("Insufficient Balance", [
                'remId' => $request->remId,
                'balance' => $openingBal
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Insufficient wallet balance. Please add funds.'
            ], 400);
        }

        $closingBal = $openingBal - 4;

        // Step 3: Verify account via API
        Log::channel('beneficiary')->info("Calling Bank Verification API", [
            'accno' => $request->accno,
            'ifsc'  => $request->ifsc
        ]);

        $verifyResponse = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json',
        ])->post('https://codegraphi.com/B2B/api/v1/account/verify', [
            'outlet'        => '496699',
            'accountNumber' => $request->accno,
            'ifsc'          => $request->ifsc,
            'latitude'      => $request->latitude,
            'longitude'     => $request->longitude
        ]);

        Log::channel('beneficiary')->info("API Response", [
            'response' => $verifyResponse->json()
        ]);

        if ($verifyResponse->failed()) {

            Log::channel('beneficiary')->error("Verification API Failed", [
                'error' => $verifyResponse->body()
            ]);
            Log::channel('accountverify')->info("Account Verify Request", [
            'ip' => $request->ip(),
            'payload' => $request->all()
        ]);

            return response()->json([
                'status'  => false,
                'message' => 'Account verification API failed.',
                'error'   => $verifyResponse->body()
            ], 500);
        }

        $verifyData = $verifyResponse->json();

        if (!isset($verifyData['success']) || $verifyData['success'] != true) {

            Log::channel('beneficiary')->warning("Account Verification Failed", [
                'verifyResponse' => $verifyData
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Account verification failed.',
                'data'    => $verifyData
            ], 400);
        }

        $verifiedName    = $verifyData['payee_name'] ?? $request->benename;
        $verifiedAccount = $verifyData['account_no'] ?? $request->accno;
        $verifiedIfsc    = $verifyData['ifsc'] ?? $request->ifsc;

        // Deduct balance
        DB::table('remittances')
            ->where('remId', $request->remId)
            ->decrement('amount', 4);

        Log::channel('beneficiary')->info("Balance Deducted Successfully", [
            'opening' => $openingBal,
            'closing' => $closingBal
        ]);

        // Insert verification record
        DB::table('account_verifications')->insert([
            'remId'       => $request->remId,
            'beneAccount' => $verifiedAccount,
            'ifsc'        => $verifiedIfsc,
            'charges'     => 4,
            'tds'         => 0,
            'opbalance'   => $openingBal,
            'clbalance'   => $closingBal,
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        Log::channel('beneficiary')->info("Account Verification Logged");

        // Insert beneficiary
        $beneId = DB::table('payoutbene')->insertGetId([
            'remId'           => $request->remId,
            'remittancePhone' => $keyRecord->phone,
            'beneName'        => $verifiedName,
            'baneMobileNo'    => $request->beneMobile,
            'baneAccount'     => $verifiedAccount,
            'baneBankName'    => $request->bank_name,
            'baneIFSC'        => $verifiedIfsc,
            'accountType'     => "",
            'created_at'      => now(),
            'updated_at'      => now()
        ]);

        Log::channel('beneficiary')->info("Beneficiary Inserted", [
            'beneId' => $beneId
        ]);

        // Fetch data
        $addedBeneficiary = DB::table('payoutbene')->where('id', $beneId)->first();

        Log::channel('beneficiary')->info("Beneficiary Returned Successfully");

        return response()->json([
            'status'  => true,
            'message' => 'Beneficiary added successfully.',
            'data'    => $addedBeneficiary
        ]);

    } catch (\Exception $e) {

        Log::channel('beneficiary')->error("Exception Occurred", [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong.',
            'error'   => $e->getMessage()
        ], 500);
    }
}


public function sendPayout(Request $request)
{
    //return $request->all(); die();
    try {
        // ✅ Step 1: Authenticate Business
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
                ->where('name', 'PAYOUT')
                ->first();

            if (!$service || $service->status != 1) {
                return response()->json([
                            'status'  => false,
                            'message' => $service->message ?? 'Service is currently inactive'
                        ], 403);
            }

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
        $existingTxn = DB::table('xpresspayout')
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

        if ($request->web == 'YES') {
            // ✅ Step 3: Check Beneficiary
            $beneficiary = DB::table('payoutbene')
                ->where('remId', $remittance->remId)
                ->where('baneAccount', $request->accountNo)
                ->where('baneIFSC', $request->ifscCode)
                ->first();

            if (!$beneficiary) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Beneficiary not found. Please add beneficiary first.'
                ], 404);
            }
        }

         if ($remittance->isKyc != 1 || $remittance->status != 'success') {
            return response()->json([
                'status' => false,
                'message' => 'KYC not verified or payout not allowed.',
            ], 403);
        }
        if ($remittance->payout1 != 1) {
            return response()->json([
                'status' => false,
                'message' => 'Payout service not enabled for your account. Please contact Admin.',
            ], 403);
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
            ->where('service', 'PAYOUT')
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
        $paymentId  = 'HCHET' . strtoupper(Str::random(10));
        $rawPayload = $request->all();

        DB::table('xpresspayout')->insert([
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
            $bankResponse = Http::post('https://api.credxpay.com/api/payout/v6/initiate', [
                'amount'                  => $amount,
                'senderMobile'            => $request->mobileNo,
                'beneficiaryIfscCode'     => strtoupper($request->ifscCode),
                'beneficiaryAccountNumber'=> $request->accountNo,
                'beneficiaryName'         => $request->accountHolderName,
                'paymentMode'             => 'IMPS',
                'txnId'                   => $paymentId,
                'callbackUrl'             => route('payout.callback.handler'),
               
            ])->json();
                            Log::channel('fundtransfer')->info("Payout Response Received from Bank", [
                    'ip'       => $request->ip(),
                    'response' => $bankResponse
                ]);
            //return $bankResponse; die();
            // ✅ If success → update payout table with UTR + transactionId
            if (!empty($bankResponse['data']['success']) && $bankResponse['data']['success'] === true) {
                DB::table('xpresspayout')
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
            }

        } catch (\Exception $e) {
            Log::error("Bank API Error: " . $e->getMessage());
            $bankResponse = [
                "status"  => false,
                "message" => "Bank API call failed",
                "error"   => $e->getMessage()
            ];
        }

            
        // ✅ Step 9: Send Email Notifications (only one mail, not multiple)
        $emailPayload = [
            'api_key' => "codegraphi@qazxcv",
            'to'      => "support@codegraphi.com",
            'subject' => 'Payout Initiated Successfully',
            'message' => "Dear Team,\n\n"
                ."Your payout request has been initiated.\n\n"
                ."📌 Transaction Details:\n"
                ."Reference No: {$request->RefNo}\n"
                ."Payment ID: {$paymentId}\n"
                ."Beneficiary: {$request->accountHolderName}\n"
                ."Bank: {$request->bankName}\n"
                ."Account No: {$request->accountNo}\n"
                ."IFSC: {$request->ifscCode}\n"
                ."Amount: ₹{$amount}\n"
                ."Charges: ₹{$charges}\n"
                ."TDS: ₹{$tds}\n"
                ."Net Amount Deducted: ₹{$totalDeduct}\n\n"
                ."Regards,\n"
                ."Team CodeGraphi"
        ];

        $emailResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://email.codegraphi.in/api/send-email', $emailPayload);

        if (!$emailResponse->successful()) {
            Log::error("Email sending failed: " . $emailResponse->body());
        }

        // ✅ Step 10: Final Response
        return response()->json([
            "status"   => ($bankResponse['data']['success'] ?? false) == true,
            'success'  =>'Initiated',
            "message"  =>  $bankResponse['message'] ?? "Payout initiated.",
            'remId'            => $remittance->remId,
            'email'            => $remittance->email,
            'payment_id'       => $paymentId,
            'utr'              => $bankResponse['data']['result']['utr'] ?? null,
            'amount'           => $amount,
            'charge'           => $charges,
            'gst'              => $tds,
            'status'           => ($bankResponse['data']['success'] ?? false) == true,
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
    try {
        // ✅ Step 1: Authenticate Business
        $remittance = $this->localAuth($request->input('apikey'));
        if (!$remittance) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid API key.'
            ], 401);
        }
            Log::channel('payoutstatus')->info("Payout Status Request", [
    'ip' => $request->ip(),
    'payload' => $request->all()
]);


        // ✅ Step 2: Validate Input
        $validator = Validator::make($request->all(), [
            'payment_id' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // ✅ Step 3: Find Transaction
        $transaction = DB::table('xpresspayout')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email)
            ->where('payment_id', $request->payment_id)
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

    public function updatePayoutStatus(Request $request, $paymentId)
{
    try {
        $request->validate([
            'status'      => 'required|string',
            'bank_ref_no' => 'nullable|string|max:100',
        ]);

        // ✅ Find transaction
        $transaction = DB::table('xpresspayout')->where('payment_id', $paymentId)->first();
        //return $transaction; die();
        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found.'
            ], 404);
        }

        // ✅ Update transaction
        DB::table('xpresspayout')
            ->where('payment_id', $paymentId)
            ->update([
                'status'      => $request->status,
                'bank_ref_no' => $request->bank_ref_no,
                'updated_at'  => now(),
            ]);

        // ✅ Get client callback URL
        $callbackUrl = DB::table('remittances')
            ->where('remId', $transaction->remId)
            ->where('email', $transaction->email)
            ->value('callback_url');

        // ✅ Prepare callback payload
        $payload = [
            'payment_id'       => $transaction->payment_id,
            'amount'           => $transaction->amount,
            'status'           => $request->status,
            'bank_ref_no'      => $request->bank_ref_no,
            'account_no'       => $transaction->acc_no,
            'ifsc'             => $transaction->ifsc_code,
            'beneficiary_name' => $transaction->beneficiary_name,
            'ref_no'           => $transaction->refId,
            'updated_at'       => now(),
        ];

        // ✅ Send callback if URL exists
        if ($callbackUrl) {
            try {
                Http::timeout(10)->post($callbackUrl, $payload);
            } catch (\Exception $ex) {
                Log::channel('payoutv5')->error('Callback failed', [
                    'callback_url' => $callbackUrl,
                    'error' => $ex->getMessage()
                ]);
            }
        }
              $emailPayload = [
                'api_key' => "codegraphi@qazxcv",
                'to'      => $transaction->email,
               'subject' => 'Payout Transaction Successfully',
                    'message' => "Dear {$transaction->remId},\n\n"
            ."We are pleased to inform you that your payout request has been Successfully processed.\n\n"
            ."📌 Transaction Details:\n"
            ."Reference No: {$transaction->refId}\n"
            ."Payment ID: {$transaction->payment_id}\n"
            ."Bank Reference No: {$request->bank_ref_no}\n"
            ."Beneficiary: {$transaction->beneficiary_name}\n"
            ."Bank: {$transaction->bank_name}\n"
            ."Account No: {$transaction->acc_no}\n"
            ."IFSC: {$transaction->ifsc_code}\n"
            ."Amount: ₹{$transaction->amount}\n"
            ."Charges: ₹{$transaction->charge}\n"
            ."TDS: ₹{$transaction->tds}\n"

            ."You will receive a confirmation once the payout is processed by the bank.\n\n"
            ."Regards,\n"
            ."Team CodeGraphi"

                ];

        $emailResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://email.codegraphi.in/api/send-email', $emailPayload);
        //return $emailPayload; die();
        if (!$emailResponse->successful()) {
            Log::error('Email sending failed: ' . $emailResponse->body());
        }

        return response()->json([
            'status'  => true,
            'message' => 'Transaction updated and callback triggered.',
            'data'    => $payload
        ], 200);

    } catch (\Exception $e) {
        Log::channel('payoutv5')->error('Exception during payout update', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Error while updating transaction.',
            'error' => $e->getMessage()
        ], 500);
    }
}

// public function sendPayout(Request $request)
// {
//     try {
//         // ✅ Step 1: Authenticate Business
//         $remittance = $this->localAuth($request->input('apikey'));
//         if (!$remittance) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Unauthorized. Invalid API key.'
//             ], 401);
//         }

//         // ✅ Step 2: Validate Input
//         $validator = Validator::make($request->all(), [
//             'mobileNo'          => 'required|string|max:15',
//             'txnAmount'         => 'required|numeric|min:1',
//             'accountNo'         => 'required|string|max:20',
//             'ifscCode'          => 'required|string|size:11',
//             'bankName'          => 'required|string|max:150',
//             'accountHolderName' => 'required|string|max:150',
//             'RefNo'             => 'required|string|max:50',
//             'web'               => 'required|in:YES,OWN'
//         ]);

//         if ($validator->fails()) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Validation failed.',
//                 'errors'  => $validator->errors()
//             ], 422);
//         }

//         //Rrf no 
//         $existingTxn = DB::table('xpresspayout')
//             ->where('remId', $remittance->remId)
//             ->where('refId', $request->RefNo)
//             ->first();
//         if ($existingTxn) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Duplicate RefNo. Transaction with this RefNo already exists.'
//             ], 409);
//         }
//         if($request->web=='YES')
//         {
//             // ✅ Step 3: Check Beneficiary
//         $beneficiary = DB::table('payoutbene')
//             ->where('remId', $remittance->remId)
//             ->where('baneAccount', $request->accountNo)
//             ->where('baneIFSC', $request->ifscCode)
//             ->first();

//         if (!$beneficiary) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Beneficiary not found. Please add beneficiary first.'
//             ], 404);
//         }

//         }
        

//         // ✅ Step 4: Check Wallet Balance
//         $walletAmount = DB::table('remittances')
//             ->where('remId', $remittance->remId)
//             ->where('email', $remittance->email)
//             ->value('amount');

//         if (!$walletAmount || $walletAmount < $request->txnAmount) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Insufficient wallet balance. Please add funds.'
//             ], 400);
//         }

//         $openingBal = $walletAmount;
//         $amount     = $request->txnAmount;

//         // ✅ Step 5: Fetch Charges & Commission
//         $charges = 0;
//         $tds     = 0;

//         $response = Http::get('https://admin.codegraphi.in/api/commission-index', [
//             'email' => $remittance->email,
//         ]);

//         if ($response->successful()) {
//             $commissions = $response['data'] ?? [];
//             foreach ($commissions as $item) {
//                 if (
//                     $item['service'] === 'PAYOUT' &&
//                     $item['business_type'] === 'API' &&
//                     $amount >= (float) $item['from_amount'] &&
//                     $amount <= (float) $item['to_amount']
//                 ) {
//                     $charges = $item['charge_in'] === 'Percentage'
//                         ? $amount * ((float) $item['charge']) / 100
//                         : (float) $item['charge'];

//                     $tds = $item['tds_in'] === 'Percentage'
//                         ? $charges * ((float) $item['tds']) / 100
//                         : (float) $item['tds'];

//                     break;
//                 }
//             }
//         }

//         // ✅ Fallback charges
//         if ($charges == 0 && $amount >= 100) {
//             $charges = $amount * 0.01; // 1%
//             $tds     = $charges * 0.02; // 2%
//         }

//         $totalDeduct = $amount + $charges + $tds;
//         $closingBal  = $walletAmount - $totalDeduct;

//         if ($closingBal < 0) {
//             return response()->json([
//                 'status'  => false,
//                 'message' => 'Insufficient balance after charges & TDS.'
//             ], 400);
//         }

//         // ✅ Step 6: Insert Payout Record
//         $paymentId  = 'XPYT' . strtoupper(Str::random(10));
//         $rawPayload = $request->all();

//         DB::table('xpresspayout')->insert([
//             'remId'            => $remittance->remId,
//             'email'            => $remittance->email,
//             'payment_id'       => $paymentId,
//             'amount'           => $amount,
//             'charge'           => $charges,
//             'tds'              => $tds,
//             'status'           => 'Initiated',
//             'opening_balance'  => $openingBal,
//             'closing_balance'  => $closingBal,
//             'bank_name'        => $request->bankName,
//             'ifsc_code'        => $request->ifscCode,
//             'acc_no'           => $request->accountNo,
//             'beneficiary_name' => $request->accountHolderName,
//             'refId'            => $request->RefNo,
//             'requestBody'      => json_encode($rawPayload),
//             'created_at'       => now(),
//             'updated_at'       => now(),
//         ]);

//         // ✅ Step 7: Deduct Wallet Balance
//         DB::table('remittances')
//             ->where('remId', $remittance->remId)
//             ->where('email', $remittance->email)
//             ->update(['amount' => $closingBal]);

//             $emailPayload = [
//                 'api_key' => "codegraphi@qazxcv",
//                 'to'      => "sarvesh@codegraphi.com",
//                'subject' => 'Payout Initiated Successfully',
//                     'message' => "Dear Team,\n\n"
//             ."We are pleased to inform you that your payout request has been initiated successfully.\n\n"
//             ."📌 Transaction Details:\n"
//             ."Reference No: {$request->RefNo}\n"
//             ."Payment ID: {$paymentId}\n"
//             ."Beneficiary: {$request->accountHolderName}\n"
//             ."Bank: {$request->bankName}\n"
//             ."Account No: {$request->accountNo}\n"
//             ."IFSC: {$request->ifscCode}\n"
//             ."Amount: ₹{$amount}\n"
//             ."Charges: ₹{$charges}\n"
//             ."TDS: ₹{$tds}\n"
//             ."Net Amount Credited: ₹{$totalDeduct}\n\n"
//             ."You will receive a confirmation once the payout is processed by the bank.\n\n"
//             ."Regards,\n"
//             ."Team CodeGraphi"

//                 ];

//         $emailResponse = Http::withHeaders([
//             'Accept' => 'application/json',
//             'Content-Type' => 'application/json',
//         ])->post('https://email.codegraphi.in/api/send-email', $emailPayload);
//         //return $emailPayload; die();
//         if (!$emailResponse->successful()) {
//             Log::error('Email sending failed: ' . $emailResponse->body());
//         }


//         return response()->json([
//             'status'  => true,
//             'message' => 'Payout request initiated successfully.',
//             'data'    => [
//                 'status'           => 'Initiated',
//                 'payment_id'       => $paymentId,
//                 'amount'           => $amount,
//                 'charges'          => $charges,
//                 'tds'              => $tds,
//                 'total_deducted'   => $totalDeduct,
//                 'opening_balance'  => $openingBal,
//                 'closing_balance'  => $closingBal,
//                 'beneficiary_name' => $request->accountHolderName,
//                 'account_no'       => $request->accountNo,
//                 'ifsc'             => $request->ifscCode,
//                 'bank_name'        => $request->bankName,
//                 'ref_no'           => $request->RefNo,
//             ]
//         ], 200);

//     } catch (\Exception $e) {
//         Log::channel('payoutv5')->error('Exception during payout', [
//             'error' => $e->getMessage(),
//             'trace' => $e->getTraceAsString()
//         ]);

//         return response()->json([
//             'status'  => false,
//             'message' => 'An unexpected error occurred during payout.',
//             'error'   => $e->getMessage()
//         ], 500);
//     }
// }

}