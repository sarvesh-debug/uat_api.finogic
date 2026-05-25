<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class CreditCardController extends Controller
{
    private $baseUrl = "https://connect.inspay.in/v4/credit_card";

    private $username;
    private $token;

    public function __construct()
    {
        $this->username = env('INSPAY_USERNAME');
        $this->token    = env('INSPAY_TOKEN');
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

        if ($merchant->ccpay != 1) {

            return response()->json([
                'success' => false,
                'message' => 'Credit Card service inactive'
            ], 403);
        }

        if ($merchant->isKyc != 1) {

            return response()->json([
                'success' => false,
                'message' => 'KYC Pending'
            ], 403);
        }

        $service = DB::table('apis')
            ->where('name', 'CCPAY')
            ->first();

        if (!$service || $service->status != 1) {

            return response()->json([
                'success' => false,
                'message' => $service->message ?? 'Service inactive'
            ], 403);
        }

           $commissions = DB::table('commissions')
            ->where('packagesId', $merchant->packageId)
            ->where('service', 'CCBILL')
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
            ->where('service', 'CCBILL')
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

    if ($item->service === 'CCBILL' && $amount >= $from && $amount <= $to) {
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

    /* ============================================================
       💳 BILL FETCH
    ============================================================ */

    public function billFetch(Request $request)
    {
        $auth = $this->validateAuth($request);

        if ($auth instanceof JsonResponse) {
            return $auth;
        }

        $request->validate([

            'mobile' => 'required',
            'card'   => 'required',
            'opcode' => 'required',
        ]);

        try {

            $orderId = 'CCF' . time() . rand(1000,9999);

            $payload = [

                "username" => $this->username,
                "token"    => $this->token,
                "mobile"   => $request->mobile,
                "card"     => $request->card,
                "opcode"   => $request->opcode,
                "orderid"  => $orderId,
            ];

            $response = Http::post(
                $this->baseUrl . '/bill_fetch',
                $payload
            );

            $apiResponse = $response->json();

           

           
            

            /*
            |--------------------------------------------------------------------------
            | STORE FETCH
            |--------------------------------------------------------------------------
            */

            DB::table('credit_card_transactions')->insert([

                'merchant_id'     => $auth->remId,
                'type'            => 'FETCH',

                'order_id'        => $orderId,

                'mobile'          => $request->mobile,
                'card_number'     => $request->card,
                'opcode'          => $request->opcode,

                'opening_balance' => $auth->amount,
                'closing_balance' => $auth->amount,

                'amount'          => 0,

                'charge'          => 0,
                'tds'             => 0,
                'commission'      => 0,

                'status'          => $apiResponse['status'] ?? 'pending',

                'api_response'    => json_encode($apiResponse),

                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            if($apiResponse['status']=='Failure')
                {
                 return response()->json([

                'success' => false,
                'message' => $apiResponse['message'] ?? 'Bill fetched Failed' ,

                'order_id' => $orderId,
                     ]);
                }

            return response()->json([

                'success' => true,
                'message' => 'Bill fetched successfully',

                'order_id' => $orderId,

                'data' => $apiResponse
            ]);

        } catch (\Exception $e) {

            return response()->json([

                'success' => false,
                'message' => $e->getMessage()

            ], 500);
        }
    }

    /* ============================================================
       💳 BILL PAY
    ============================================================ */

    public function billPay(Request $request)
    {
        $auth = $this->validateAuth($request);

        if ($auth instanceof JsonResponse) {
            return $auth;
        }
        //return $auth;
         $validator = Validator::make($request->all(), [
          
            'mobile'   => 'required',
            'card'     => 'required',
            'amount'   => 'required|numeric|min:1',
            'fetch_id' => 'required',
            'opcode'   => 'required',
            'refid'   => 'required',
        ]);

          if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        // DB::beginTransaction();

        try {

            $amount = $request->amount;
             $existingTxn = DB::table('credit_card_transactions')
            ->where('merchant_id', $auth->remId)
            ->where('refid', $request->refid)
            ->first();

        if ($existingTxn) {
            return response()->json([
                'status'  => false,
                'message' => 'Duplicate Refid. Transaction with this Refid already exists.'
            ], 409);
        }

            /*
            |--------------------------------------------------------------------------
            | CALCULATE CHARGES
            |--------------------------------------------------------------------------
            */

            $charges = $this->calculateCharges($amount,$auth);
            

           // return $charges;
            

            $totalDebit = $charges['total_debit'];

            $openingBalance = $auth->amount;

            /*
            |--------------------------------------------------------------------------
            | CHECK BALANCE
            |--------------------------------------------------------------------------
            */

            if ($openingBalance < $totalDebit) {

                return response()->json([

                    'success' => false,
                    'message' => 'Insufficient balance'

                ], 400);
            }

            $closingBalance = $openingBalance - $totalDebit;

            /*
            |--------------------------------------------------------------------------
            | DEDUCT BALANCE
            |--------------------------------------------------------------------------
            */

            DB::table('remittances')
                ->where('remId', $auth->remId)
                ->update([
                    'amount' => $closingBalance
                ]);

            $orderId = 'CCP' . time() . rand(1000,9999);

            $payload = [

                "username" => $this->username,
                "token"    => $this->token,
                "mobile"   => $request->mobile,
                "card"     => $request->card,
                "amount"   => $amount,
                "pan"      => $request->pan ?? "",
                "fetch_id" => $request->fetch_id,
                "opcode"   => $request->opcode,
                "orderid"  => $orderId,
            ];

            $response = Http::post(
                $this->baseUrl . '/bill_pay',
                $payload
            );

            $apiResponse = $response->json();

            Log::info('CCBILL API Response', [
                'response' => $apiResponse
            ]);

            /*
            |--------------------------------------------------------------------------
            | STORE TRANSACTION
            |--------------------------------------------------------------------------
            */

            DB::table('credit_card_transactions')->insert([

                'merchant_id'     => $auth->remId,
                'refid'          =>$request->refid,

                'type'            => 'PAY',

                'order_id'        => $orderId,

                'mobile'          => $request->mobile,

                'card_number'     => $request->card,

                'opcode'          => $request->opcode,

                'fetch_id'        => $request->fetch_id,

                'amount'          => $amount,

                'opening_balance' => $openingBalance,

                'closing_balance' => $closingBalance,

                'charge'          => $charges['charge_amount'],

                'tds'             => $charges['tds_amount'],

                'commission'      =>  0,

                'status'          => $apiResponse['status'] ?? 'pending',

                'api_response'    => json_encode($apiResponse),

                'created_at'      => now(),

                'updated_at'      => now(),
            ]);

            // DB::commit();

            if($apiResponse['status']=='Failure')
                {
                     DB::table('remittances')
                ->where('remId', $auth->remId)
                ->update([
                    'amount' => $openingBalance
                ]);

                return response()->json([

                'success' => false,

                'message' => 'Bill payment successful',
                'refid' =>$request->refid,

                'order_id' => $orderId,

                'opening_balance' => $openingBalance,

                'closing_balance' => $openingBalance,

                'charges' => [

                    'charge'     => $charges['charge_amount'],
                    'gst'        => $charges['tds_amount'],
                    'commission' => 0,
                ],

                'data' => $apiResponse

            ]);

                }

            return response()->json([

                'success' => true,

                'message' => 'Bill payment successful',
                'refid' =>$request->refid,

                'order_id' => $orderId,

                'opening_balance' => $openingBalance,

                'closing_balance' => $closingBalance,

                'charges' => [

                    'charge'     => $charges['charge_amount'],
                    'gst'        => $charges['tds_amount'],
                    'commission' => 0,
                ],

                'data' => $apiResponse

            ]);

        } catch (\Exception $e) {

            // DB::rollBack();

            return response()->json([

                'success' => false,

                'message' => $e->getMessage()

            ], 500);
        }
    }

    /* ============================================================
   🔍 CHECK STATUS
============================================================ */

public function checkStatus(Request $request)
{
    $auth = $this->validateAuth($request);

    if ($auth instanceof JsonResponse) {
        return $auth;
    }

    $request->validate([
        'order_id' => 'required'
    ]);

    try {

        /*
        |--------------------------------------------------------------------------
        | GET TRANSACTION
        |--------------------------------------------------------------------------
        */

        $txn = DB::table('credit_card_transactions')
            ->where('order_id', $request->order_id)
            ->where('merchant_id', $auth->remId)
            ->first();

        if (!$txn) {

            return response()->json([
                'success' => false,
                'message' => 'Transaction not found'
            ], 404);
        }

        /*
        |--------------------------------------------------------------------------
        | RETURN STATUS
        |--------------------------------------------------------------------------
        */

        return response()->json([

            'success' => true,

            'message' => 'Transaction status fetched',

            'data' => [

                'order_id'         => $txn->order_id,

                'type'             => $txn->type,

                'mobile'           => $txn->mobile,

                'amount'           => $txn->amount,

                'status'           => $txn->status,

                'opening_balance'  => $txn->opening_balance,

                'closing_balance'  => $txn->closing_balance,

                'charge'           => $txn->charge,

                'tds'              => $txn->tds, 

                'commission'       => $txn->commission,

                'provider_txn_id'  => $txn->provider_txn_id,

                'provider_message' => $txn->provider_message,

                'created_at'       => $txn->created_at,
            ]
        ]);

    } catch (\Exception $e) {

        return response()->json([

            'success' => false,

            'message' => $e->getMessage()

        ], 500);
    }
}


 /* ============================================================
       📋 CREDIT CARD BILL BANK LIST API
    ============================================================ */

    public function bankList(Request $request)
    {
        $auth = $this->validateAuth($request);

        if ($auth instanceof \Illuminate\Http\JsonResponse) {
            return $auth;
        }

        try {

            $banks = DB::table('cc_bill_banks')
                ->select(
                    'id',
                    'bank_name',
                    'op_code'
                )
                ->where('status', 1)
                ->orderBy('bank_name', 'ASC')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Credit card bill banks fetched successfully',
                'total'   => $banks->count(),
                'data'    => $banks
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}