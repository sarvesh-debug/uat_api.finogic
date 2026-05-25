<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\PayoutV2;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
class PayoutV2Controller extends Controller
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
    private function formatResponse($apiResponse)
    {
        
        // If API error
        if (isset($apiResponse['status']) && in_array(strtoupper($apiResponse['status']), ['FAILED', 'ERROR'])) {
            return response()->json([
                'status' => 'error',
                'message' => $apiResponse['message'] ?? 'Transaction failed',
                'data' => $apiResponse
            ], 400);
        }

        // Success case
        return response()->json([
            'status' => 'success',
            'message' => $apiResponse['message'] ?? 'Request successful',
            'data' => $apiResponse
        ], 200);
    }

    /**
     * Initiate payout
     */


public function initiate(Request $request)
{

    $remittance = $this->localAuth($request->input('apikey'));
    // return $remittance;die();
         $remittanceId = $remittance->remId;
         //return $remittanceId;die();

        // ✅ 2. Validate API token
        if (!$remittance) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized or invalid API token.'
            ], 401);
        }
          if($remittance->packageId==0){
            return response()->json([
                'status'  => false,
                'message' => 'No Package Assigned. Please contact Admin.',
            ], 400);
        }
            $package = DB::table('packages')->where('id',$remittance->packageId)->first();
            if(!$package || $package->status != 1){
                return response()->json([
                    'status'  => false,
                    'message' => 'Assigned Package is Inactive. Please contact Admin.',
                ], 400);
            }
            
            //return $package;die();
            
            if($package->perday_limit < $request->amount){
                return response()->json([
                    'status'  => false,
                    'message' => 'Transaction amount exceeds your package limit of ₹'.$package->perday_limit,
                ], 400);
            }
        // ✅ 3. Check KYC and Payout
        if ($remittance->isKyc != 1|| $remittance->status != 'success') {
            return response()->json([
                'status'  => false,
                'message' => 'KYC not verified or payout not allowed.',
            ], 403);
        }

        if($remittance->payout2 != 1){
            return response()->json([
                'status'  => false,
                'message' => 'Payout service is not activated for this account.',
            ], 403);
        }
            // ✅ 4. Check balance
        if($remittance->amount < $request->amount){
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient balance for this transaction.',
            ], 403);
        }
        
    $request->validate([
        "txnType" => "required|string|in:IMPS,NEFT,RTGS,IFT",
        "amount" => "required|numeric|min:100",
        "valueDate" => "required|date",
        "benName" => "required|string",
        "benIFSC" => "required|string",
        "benAcctNo" => "required|string",
    ]);

    // Payment block (single nested array ✅)
    $payment = [
        "txnType" => $request->txnType,
        "amount" => $request->amount,
        "valueDate" => $request->valueDate,
        "benName" => $request->benName,
        "benIFSC" => $request->benIFSC,
        "benAcctNo" => $request->benAcctNo,
        "description" => "Test Payout",   // matches API docs
        "remark1" => "Testing from API"
    ];

    // Hardcoded + Auto generated values
    $fixedData = [
        "integrationId" => "8f3c5c4a-27d1-4e8f-9b45-1c6e2a4db8a7",
        "bankCode" => "4585",               // must be 4-char bank code, not IFSC
        "debitAcctNo" => "1947562149",
        "customerId" => "781586761",

        // Auto-generate unique values
        // "customerId" => "CUST" . strtoupper(Str::random(6)), 
        "custTxnRefNo" => "TXN" . now()->format('YmdHis') . rand(1000, 9999),
        "externalReferenceNumber" => "EXT" . uniqid(),
    ];

    // Merge
    $payload = array_merge($fixedData, [
        "payment" => $payment
    ]);

    // Debug check
     //return response()->json($payload);

    // Call Helper
    $response = PayoutV2::initiate($payload);

    return $this->formatResponse($response);
}



    /**
     * Submit OTP
     */
    public function submitOtp(Request $request)
    {
        $request->validate([
            "integrationId" => "required|string",
            "custTxnRefNo" => "required|string",
            "otp_value" => "required|digits:6",
        ]);

        $response = PayoutV2::submitOtp($request->all());

        return $this->formatResponse($response);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        $request->validate([
            "integrationId" => "required|string",
            "custTxnRefNo" => "required|string",
        ]);

        $response = PayoutV2::resendOtp($request->all());

        return $this->formatResponse($response);
    }

    /**
     * Check payout status
     */
    public function status(Request $request)
    {
        $request->validate([
            "integrationId" => "required|string",
            "custTxnRefNo" => "required|string",
        ]);

        $response = PayoutV2::status($request->all());

        return $this->formatResponse($response);
    }

    /**
     * Webhook / Callback endpoint (Passway will hit this)
     */
    public function callback(Request $request)
    {
        // Optionally save in DB
        // TransactionLog::create(['payload' => $request->all()]);

        return response()->json([
            'status' => 'success',
            'message' => 'Callback received',
            'data' => $request->all()
        ], 200);
    }
}
