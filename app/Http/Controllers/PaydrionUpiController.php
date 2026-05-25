<?php

namespace App\Http\Controllers; 
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\PaydrionService;

class PaydrionUpiController extends Controller
{
     protected $paydrion;

    public function __construct(PaydrionService $paydrion)
    {
        $this->paydrion = $paydrion;
    }
         /* ============================================================
       🔐 LOCAL AUTH
    ============================================================ */

    public function localAuth($apiKey, $merchantId, $request)
    {
        
        $clientIp = $request->ip();

        $merchant = DB::table('remittances')
            ->where('apikey', $apiKey)
            ->where('remId', $merchantId)
            ->first();

        if (!$merchant) {

            return response()->json([
                'success' => false,
                'message' => 'Invalid API Key or Merchant ID'
            ], 401);
        }

        if ($merchant->status !== 'success') {

            return response()->json([
                'success' => false,
                'message' => 'Merchant account inactive'
            ], 403);
        }

        if ($merchant->upipayout != 1) {

            return response()->json([
                'success' => false,
                'message' => 'aeps setement p2 service inactive'
            ], 403);
        }

         if ($merchant->callback_url == null) {
            return response()->json([
                'status' => false,
                'message' => 'CallBack Url not setup. Please contact Admin.',
            ], 400);
        }

        if ($merchant->isKyc != 1) {

            return response()->json([
                'success' => false,
                'message' => 'KYC Pending'
            ], 403);
        }

        $service = DB::table('apis')
            ->where('name', 'UPI_PAYPUT')
            ->first();

        if (!$service || $service->status != 1) {

            return response()->json([
                'success' => false,
                'message' => $service->message ?? 'Service inactive'
            ], 403);
        }

           $commissions = DB::table('commissions')
            ->where('packagesId', $merchant->packageId)
            ->where('service', 'UPI-PAYOUT')
            ->get() ?? [];

        if ($commissions->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No commission structure found for your package. Please contact Admin.'
                ], 400);
            }

        /*
        |--------------------------------------------------------------------------
        | IP CHECK
        |--------------------------------------------------------------------------
        */

        if ($merchant->ipAddress != null) {

            $allowedIps = explode(',', $merchant->ipAddress);

            if (!in_array($clientIp, $allowedIps)) {

                return response()->json([
                    'success' => false,
                    'message' => 'IP not whitelisted'
                ], 403);
            }
        }

        return $merchant;
    }

    /* ============================================================
       🔐 VALIDATE AUTH
    ============================================================ */

    private function validateAuth(Request $request)
    {
        $apiKey     = $request->header('X-API-KEY');
        $merchantId = $request->header('X-MERCHANT-ID');

        if (!$apiKey || !$merchantId) {

            return response()->json([
                'success' => false,
                'message' => 'X-API-KEY and X-MERCHANT-ID required'
            ], 401);
        }

        return $this->localAuth($apiKey, $merchantId, $request);
    }

    /* ============================================================
       💰 CALCULATE CHARGES
    ============================================================ */

    private function calculateCharges($amount,$auth)
    {
        
        /*
        |--------------------------------------------------------------------------
        | EXAMPLE SETTINGS
        |--------------------------------------------------------------------------
        |
        | Charge  = 2%
        | TDS     = 5% on commission
        | GST     = optional
        |
        */

       

        // Fetch local commissions for the remittance package
        $commissions = DB::table('commissions')
            ->where('packagesId', $auth->packageId)
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

        $total_debit=$amount+($charges+$tds);

         return [

            
            'charge_amount'  => round($charges, 2),

            
            'tds_amount'     => round($tds, 2),
            'total_debit' =>round($total_debit,2),

           

           
        ];
    }



    public function upiPayout(Request $request)
    {

        Log::channel('PaydrionUpi')->info("Request Body Of UPI Payout of Paydrion", [
            'ip' => $request->ip(),
            'payload' => $request->all()
        ]);


         /*
        |--------------------------------------------------------------------------
        | AUTH VALIDATION
        |--------------------------------------------------------------------------
        */

        $auth = $this->validateAuth($request);

        if ($auth instanceof \Illuminate\Http\JsonResponse) {
            return $auth;
        }

        $remittance=$auth;
        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [
                'mobileNo'          => 'required|string|max:15',
                'txnAmount'         => 'required|numeric|min:100',
                'accountNo'         => 'required|string|max:20',
                'accountHolderName' => 'required|string|max:150',
                'RefNo'             => 'required|string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed.',
                    'errors'  => $validator->errors()
                ], 422);
            }

        // ✅ Check Duplicate RefNo
            $existingTxn = DB::table('upipayout')
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
            ->where('service', 'UPI-PAYOUT')
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

    if ($item->service === 'UPI-PAYOUT' && $amount >= $from && $amount <= $to) {
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
        $paymentId  = 'UPI' . strtoupper(Str::random(10));
        $rawPayload = $request->all();

        DB::table('upipayout')->insert([
            'remId'            => $remittance->remId,
            'email'            => $remittance->email,
            'payment_id'       => $paymentId,
            'amount'           => $amount,
            'charge'           => $charges,
            'tds'              => $tds,
            'status'           => 'Initiated',
            'opening_balance'  => $openingBal,
            'closing_balance'  => $closingBal,
            'bank_name'        => "UPI",
            'ifsc_code'        => "UPI",
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

            $payload=[

            'amount'      => $amount,
            'account_no'  => $request->accountNo,
            'mobile'      => $request->mobileNo,
            'ifsc'        => strtoupper($request->ifscCode),
            'name'        => $request->accountHolderName,
            'bank_name'   => $request->bankName,
            'order_id'    => $paymentId,
            'mode'        => 'UPI',
            'latitude'    => $request->latitude ?? '0',
            'longitude'   => $request->longitude ?? '0',
            ];

                // ✅ Step 8: Call Bank API
        $bankResponse = [];
        try {
            $bankResponse = $this->paydrion->payoutUpi($payload)->json();
                           
            Log::channel('PaydrionUpi')->info("Received from Bank Of Upi Payout of Paydrion", [
            'ip' => $request->ip(),
            'merchant_id'=>$auth->$remId,
            'response' => $bankResponse
             ]);
            //return $bankResponse; die();
            // ✅ If success → update payout table with UTR + transactionId
            if (!empty($bankResponse['code']) && $bankResponse['code'] === 'TXN') {
                DB::table('upipayout')
                    ->where('refId', $request->RefNo)
                    ->update([
                        'bank_ref_no' => $bankResponse['data']['RRN'] ?? null,

                        'status'     => $bankResponse['data']['Status'] ?? 'Pending',
                        'updated_at' => now(),
                        'responseBody'   => json_encode($bankResponse),
                        'order_id'        => $bankResponse['data']['VendorID'] ?? null,
                    ]);

                    DB::table('users')
                    ->where('id', 1)
                    ->decrement('balance', $amount);
            }

            } catch (\Exception $e) {
               
                 Log::channel('PaydrionUpi')->info("Bank API Error: Upi Payout of Paydrion", [
                'merchant_id'=>$auth->$remId,
                'response' => $e->getMessage()
                ]);
                $bankResponse = [
                    "status"  => false,
                    "message" => "Bank API call failed",
                    "error"   => $e->getMessage()
                ];
            

                // ✅ Step 10: Final Response
                return response()->json([
                    "status"   => ($bankResponse['data']['Status'] ?? false) == true,
                    'success'  =>'Initiated',
                    "message"  =>  $bankResponse['mess'] ?? "UPI Payout initiated.",
                    'remId'            => $remittance->remId,
                    'email'            => $remittance->email,
                    'payment_id'       => $paymentId,
                    'utr'              => $bankResponse['data']['RRN'] ?? null,
                    'amount'           => $amount,
                    'charge'           => $charges,
                    'gst'              => $tds,
                    'opening_balance'  => $openingBal,
                    'closing_balance'  => $closingBal,
                    'acc_no'           => $request->accountNo,
                    'beneficiary_name' => $request->accountHolderName,
                    'refId'            => $request->RefNo,
                    // 'requestBody'      => json_encode($rawPayload),
                    'created_at'       => now(),
                    'updated_at'       => now(),
                ]);

        

            }
             catch (\Exception $e) {

           // return $e;
                Log::channel('PaydrionUpi')->info("Bank API Error: Upi Payout of Paydrion", [
                'merchant_id'=>$auth->$remId,
                'response' => $e->getMessage()
                ]);
                return response()->json([
                    "status"  => false,
                    "message" => "Unexpected server error",
                    "error"   => $e->getMessage()
                ], 500);
            }



    }

    //status chek IMPS

        public function checkPayoutStatus(Request $request)
{
            $auth = $this->validateAuth($request);

        if ($auth instanceof \Illuminate\Http\JsonResponse) {
            return $auth;
        }

        $remittance=$auth;


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
        $transaction = DB::table('upipayout')
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
