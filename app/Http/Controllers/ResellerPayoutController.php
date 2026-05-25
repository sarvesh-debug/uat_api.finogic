<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\FinSmartService;
use App\Models\FinsmartPayout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Log;

use DB;


class ResellerPayoutController extends Controller
{
   protected $finsmart;

    public function __construct(FinSmartService $finsmart)
    {
        $this->finsmart = $finsmart;
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
    public function sendPayout(Request $request)
    {

        try{    
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

        if (!in_array($clientIp, $whitelistedIps)) {

            // Log the attempt for admin review
            Log::warning("IP BLOCKED: {$clientIp} tried payout for remId {$remittance->remId}");

            return response()->json([
                'status'  => false,
                'message' => "Access denied. Your IP ($clientIp) is not whitelisted."
            ], 403);
        }


           $validator = Validator::make($request->all(), [
            'account_no' => 'required',
            'ifsc_code' => 'required',
            'beneficiary_name' => 'required',
            'amount' => 'required|numeric|min:5',
            'transaction_note' => 'nullable|string',
            'bene_email' => 'nullable|email',
            'bene_mobile' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'amount' => 'required|numeric|min:5',
            'refNo' => 'required|string',
        ]);

         if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }


          // ✅ Check Duplicate RefNo
        $existingTxn = DB::table('reseller_payouts')
            ->where('remId', $remittance->remId)
            ->where('refId', $request->refNo)
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

            //return $adminBalance->balance; die();

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

        if (!$walletAmount || $walletAmount < $request->amount) {
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient wallet balance. Please add funds.'
            ], 400);
        }

        $openingBal = $walletAmount;
        $amount     = $request->amount;

        // ✅ Step 5: Fetch Charges & Commission
        $charges = 0;
        $tds     = 0;

        
       // Fetch local commissions for the remittance package
        $commissions = DB::table('commissions')
            ->where('packagesId', $remittance->packageId)
            ->where('service', 'PAYOUT_P3')
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

      //  dd($openingBal, $amount, $charges, $tds, $totalDeduct, $closingBal);die();
        
        if ($closingBal < 0) {
            return response()->json([
                'status'  => false,
                'message' => 'Insufficient balance after charges & TDS.'
            ], 400);
        }
        DB::table('remittances')
                    ->where('remId', $remittance->remId)
                    ->where('email', $remittance->email)
                    ->decrement('amount', $totalDeduct);
        DB::beginTransaction();

           $rawPayload = $request->all();

        DB::table('reseller_payouts')->insert([
            'remId'            => $remittance->remId,
            'email'            => $remittance->email,
            'amount'           => $amount,
            'charges'           => $charges,
            'tds'              => $tds,
            'status'           => 'PENDING',
            'openingBalance'  => $openingBal,
            'closingBalance'  => $closingBal,
            'bank_name'        => $request->bankName,
            'ifsc_code'        => $request->ifsc_code,
            'acc_no'           => $request->account_no,
            'beneficiary_name' => $request->beneficiary_name,
            'refId'            => $request->refNo,
            'requestData'      => json_encode($rawPayload),
            'txnId'            => $request->refNo,
            'pgType'            => 'P3',
            'initiate_ip'       => $request->ip(),
            'remarks'           => $request->transaction_note ?? 'Payout Initiated',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
  $bankResponse = [];

        try {

            $reference_id = $request->refNo;
            // 1️⃣ Deduct wallet here

            $payload = [
                "account_no" => $request->account_no,
                "ifsc_code" => $request->ifsc_code,
                "beneficiary_name" => $request->beneficiary_name,
                "amount" => $request->amount,
                "transaction_note" => $request->transaction_note ?? 'Payout',
                "reference_id" => $reference_id,
                "payment_mode" => "IMPS",
                "bene_email" => $request->bene_email,
                "bene_mobile" => $request->bene_mobile,
                "latitude" => $request->latitude ?? "22.67",
                "longitude" => $request->longitude ?? "45.67",
            ];

            $response = $this->finsmart->initiatePayout($payload);

           
           $resposneCode = $response['status'] ?? '500';
           $message = $response['message'] ?? 'No message from bank';
           $transaction_id = $response['data']['transaction_id'] ?? null;
           $utr = $response['data']['utr'] ?? null;
           $status = ($response['data']['response_code'] ?? 1) == 2 ? 'PENDING' : 'SUCCESS';

           //dd($status,$utr,$transaction_id);die();

             Log::channel('fundtransfer')->info("Bank Response", [
                'reference_id' => $reference_id,
                'response_code' => $resposneCode,
                'message' => $message,
                'full_response' => $response
            ]); 
            if ($resposneCode != 1) {
                // If API call failed, refund wallet immediately
                DB::table('remittances')
                    ->where('remId', $remittance->remId)
                    ->where('email', $remittance->email)
                    ->increment('amount', $totalDeduct);

                // Update payout record as failed
                DB::table('reseller_payouts')
                    ->where('refId', $reference_id)
                    ->update([
                        'status' => 'FAILED',
                        'remarks' => $message,
                        'resposeData' => json_encode($response),
                    ]); 

                return response()->json([
                    'status' => false,
                    'message' => 'Payout failed: ' . ($message ?? 'Unknown error'),
                    'details' => $response
                ], 500);
            }


            DB::table('reseller_payouts')
                ->where('refId', $reference_id)
                ->update([
                    'status' => $status,
                    'txnId' => $transaction_id,
                    'utr' => $utr,
                    'remarks' => $message,
                    'responseData' => json_encode($response),
                ]);

            DB::commit();
        //return $response;
             // ✅ Step 10: Final Response
        return response()->json([
            "status"   => ($resposneCode ?? false) == 1,
            "message"  =>  $response['message'] ?? "Payout initiated.",
            'remId'            => $remittance->remId,
            'email'            => $remittance->email,
            'ref_no'       => $request->refNo,
            'utr'              => $utr,
            'transaction_id'   => $transaction_id,
            'amount'           => $amount,
            'charge'           => $charges,
            'gst'              => $tds,
            'status'           => $status,
            'opening_balance'  => $openingBal,
            'closing_balance'  => $closingBal,
            'ifsc_code'        => $request->ifsc_code,
            'acc_no'           => $request->account_no,
            'beneficiary_name' => $request->beneficiary_name,
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        } catch (\Exception $e) {

           // DB::rollBack();
            return response()->json(['errorAPI' => $e->getMessage()]);
        }

        }
        catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()]);
        }
        
    } 


public function checkPayoutStatus(Request $request)
{
    try {

        // ✅ Validation
        $request->validate([
            'refNo' => 'required|string'
        ]);

        $reference_id = $request->input('refNo');

        // ✅ Call Service
        $response = $this->finsmart->checkStatus($reference_id);

        $status = ($response['response_code'] ?? 1) == 2 ? 'PENDING' : 'SUCCESS';
        $statusCode = $response['status'] ?? 1;

            Log::channel('fundtransfer')->info("Payout Status Check", [
                'reference_id' => $reference_id,
                'status_code' => $statusCode,
                'message' => $response['message'] ?? 'No message from bank',
                'full_response' => $response
            ]);
        if ($statusCode ==0)
            {
                $closingBal = DB::table('reseller_payouts')->where('refId', $reference_id)->value('closingBalance');
                DB::table('remittances')
                    ->where('amount', $closingBal)
                    ->increment('amount', $closingBal);
                // Update payout record as failed
                DB::table('reseller_payouts')
                    ->where('refId', $reference_id)
                    ->update([
                        'status' => 'FAILED',
                        'remarks' => $response['message'] ?? 'Status updated',
                        'responseData' => json_encode($response),
                    ]);
            }
        elseif($statusCode == 1)
            {
                // Update payout record as success
                DB::table('reseller_payouts')
                    ->where('refId', $reference_id)
                    ->update([
                        'status' => $status,
                        'remarks' => $response['message'] ?? 'Status updated',
                        'responseData' => json_encode($response),
                    ]); 
            }
        else{
            // Update payout record as failed
                DB::table('reseller_payouts')
                    ->where('refId', $reference_id)
                    ->update([
                        'status' => 'FAILED',
                        'remarks' => $response['message'] ?? 'Status updated',
                        'responseData' => json_encode($response),
                    ]); 
        }   

        return response()->json([
            'status'  => true,
            'message' => 'Status fetched successfully',
            'utr' => $response['utr'] ?? null,
            'transaction_id' => $response['transaction_id'] ?? null,
            'bank_status' => $response['message'] ?? null,
            'response_code' => $response['response_code'] ?? null,
            'amount' => $response['amount'] ?? null,
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage(),
            'errors'  => $e->errors()
        ], 422);

    } catch (\Exception $e) {

        Log::error('Payout Status Check Error', [
            'refNo' => $request->input('refNo'),
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong while checking payout status'
        ], 500);
    }
}

public function checkWalletBalance(Request $request)
{
    try {

        $response = $this->finsmart->checkBalance();

        return response()->json([
            'status'  => true,
            'message' => 'Wallet balance fetched successfully',
            'data'    => $response
        ], 200);

    } catch (\Exception $e) {

        Log::error('Wallet Balance Fetch Error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Unable to fetch wallet balance'
        ], 500);
    }
}



    public function handle(Request $request)
{
    if($request->service != "PAYOUT"){
        return response()->json(['message' => 'ignored']);
    }


    $data = $request->data;
    $reference_id=$data['ref_id'];

    $payout = DB::table('reseller_payouts')->where('refId', $data['ref_id'])->first();

    if(!$payout){
        return response()->json(['message' => 'not found']);
    }

    if($data['subStatus'] == 101){
        $payout->status = 'SUCCESS';
    } elseif($data['subStatus'] == 100) {

        $payout->status = 'failed';
        // refund wallet
        $closingBal = DB::table('reseller_payouts')->where('refId', $reference_id)->value('closingBalance');
                DB::table('remittances')
                    ->where('amount', $closingBal)
                    ->increment('amount', $closingBal);
                // Update payout record as failed
                DB::table('reseller_payouts')
                    ->where('refId', $reference_id)
                    ->update([
                        'status' => 'FAILED',
                        'remarks' => $response['message'] ?? 'Status updated',
                        'responseData' => json_encode($response),
                    ]);
    } elseif($data['subStatus'] == 106) {
        $payout->status = 'reversed';
    }

    $payout->utr = $data['utr'];
    $payout->transaction_id = $data['transaction_id'];
    $payout->save();

    return response()->json(['message' => 'updated']);
}

}
