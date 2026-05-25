<?php

namespace App\Http\Controllers;

use App\Helpers\AeronpayHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Helpers\PayoutV6Helper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;


class mainAPIController extends Controller
{
      public function initiate(Request $request)
    {
        try {
            // 🔹 Custom Validation
            $validator = Validator::make($request->all(), [
                'amount'                   => 'required|numeric|min:1',
                'beneficiaryName'          => 'required|string|max:100',
                'beneficiaryAccountNumber' => 'required|digits_between:8,18',
                'beneficiaryIfscCode'      => 'required|string|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
                'paymentMode'              => 'nullable|in:IMPS,NEFT',
                'senderMobile'             => 'required|digits:10',
                'callbackUrl'             => 'required|url',
                'txnId' => 'required|string',
            ], [
                'amount.required' => 'Transfer amount is required',
                'amount.numeric'  => 'Amount must be a number',
                'beneficiaryName.required' => 'Beneficiary name is required',
                'beneficiaryAccountNumber.required' => 'Account number is required',
                'beneficiaryIfscCode.regex' => 'Invalid IFSC code format',
                'senderMobile.digits' => 'Sender mobile must be 10 digits',
                'callbackUrl.required' => 'Callback URL is required',
                'callbackUrl.url' => 'Callback URL must be a valid URL',
            ]);

            if ($validator->fails()) {
                Log::warning("PayoutV6 Validation Failed", [
                    'errors' => $validator->errors()->toArray(),
                    'request' => $request->all()
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 🔹 Collect Validated Data
            $data = $validator->validated();

            // 🔹 Call Helper
            $response = PayoutV6Helper::initiate($data);

            // 🔹 Log API Response
            Log::info("PayoutV6 API Response", [
                'request' => $data,
                'response' => $response
            ]);

            // 🔹 Standard API Response
            return response()->json([
                'status' => $response['success'] ?? false,
                'message' => $response['message'] ?? 'Something went wrong',
                'data' => $response
            ], 200);

        } catch (\Exception $e) {
            // 🔹 Log Exception
            Log::error("PayoutV6 Exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function upi_initiate(Request $request)
    {
        try {
            // 🔹 Custom Validation
            $validator = Validator::make($request->all(), [
                'amount'                   => 'required|numeric|min:1',
                'upiId'          => 'required',
                'txnId' => 'required',
                'name'      => 'required',
                'callbackUrl'             => 'required|url',
                
            ], [
                'amount.required' => 'Transfer amount is required',
                'amount.numeric'  => 'Amount must be a number',
                'upiId.required' => 'UPI ID is required',
                'txnId.required' => 'Transaction ID is required',
                'name.required' => 'Name is required',
                'callbackUrl.required' => 'Callback URL is required',
                'callbackUrl.url' => 'Callback URL must be a valid URL',
                
            ]);

            if ($validator->fails()) {
                Log::warning("PayoutV6 UPI Validation Failed", [
                    'errors' => $validator->errors()->toArray(),
                    'request' => $request->all()
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 🔹 Collect Validated Data
            $data = $validator->validated();

            // 🔹 Call Helper
            $response = PayoutV6Helper::initiateUPI($data);

            // 🔹 Log API Response
            Log::info("PayoutV6 API UPI Response", [
                'request' => $data,
                'response' => $response
            ]);

            // 🔹 Standard API Response
            return response()->json([
                'status' => $response['success'] ?? false,
                'message' => $response['message'] ?? 'Something went wrong',
                'data' => $response
            ], 200);

        } catch (\Exception $e) {
            // 🔹 Log Exception
            Log::error("PayoutV6 Exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

     public function upi_initiate_status(Request $request)
    {
        try {
            // 🔹 Custom Validation
            $validator = Validator::make($request->all(), [
                'order_id'                   => 'required',
            
            ], [
                'order_id.required' => 'Order ID is required',
                
                
            ]);

            if ($validator->fails()) {
                Log::warning("PayoutV6 Validation Failed", [
                    'errors' => $validator->errors()->toArray(),
                    'request' => $request->all()
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 🔹 Collect Validated Data
            $data = $validator->validated();

            // 🔹 Call Helper
            $response = PayoutV6Helper::initiateUPIStatus($data);

            // 🔹 Log API Response
            Log::info("PayoutV6 API Response", [
                'request' => $data,
                'response' => $response
            ]);

            // 🔹 Standard API Response
            return response()->json([
                'status' => $response['success'] ?? false,
                'message' => $response['message'] ?? 'Something went wrong',
                'data' => $response
            ], 200);

        } catch (\Exception $e) {
            // 🔹 Log Exception
            Log::error("PayoutV6 Exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


         public function payout_initiate_status(Request $request)
    {
        try {
            // 🔹 Custom Validation
            $validator = Validator::make($request->all(), [
                'order_id'                   => 'required',
            
            ], [
                'order_id.required' => 'Order ID is required',
                
                
            ]);

            if ($validator->fails()) {
                Log::warning("PayoutV6 Validation Failed", [
                    'errors' => $validator->errors()->toArray(),
                    'request' => $request->all()
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 🔹 Collect Validated Data
            $data = $validator->validated();

            // 🔹 Call Helper
            $response = PayoutV6Helper::initiateStatus($data);

            // 🔹 Log API Response
            Log::info("PayoutV6 API Response", [
                'request' => $data,
                'response' => $response
            ]);

            // 🔹 Standard API Response
            return response()->json([
                'status' => $response['success'] ?? false,
                'message' => $response['message'] ?? 'Something went wrong',
                'data' => $response
            ], 200);

        } catch (\Exception $e) {
            // 🔹 Log Exception
            Log::error("PayoutV6 Exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

       public function UpiCallback(Request $request)
    {
        Log::info("UPI Callback Received", [
            'payload' => $request->all()
        ]);

        $orderId = $request->input('orderId');
        $txnId = $request->input('txnId');
        $status = $request->input('status');
        $amount = $request->input('txn_amount');
        $utr = $request->input('utr');
        $description = $request->input('description');
        $transaction= DB::table('upipayout')
            ->where('order_Id', $orderId)
            ->first();
        if (!$transaction) {
            Log::warning("UPI Callback: Transaction Not Found", [
                'orderId' => $orderId,
                'txnId' => $txnId
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found'
            ], 404);    
        }
        
        if ($transaction->status == 'Success') {
    return response()->json([
        'status' => true,
        'message' => 'Already processed'
    ]);
}
          
        try {
            $finalStatus = 'Pending';
                if (strtolower($status) === 'success') {
                    $finalStatus = 'Success';
                } elseif (strtolower($status) === 'failed') {
                    $finalStatus = 'Failed';
                }
            DB::table('upipayout')
                ->where('order_Id', $orderId)
    
                ->update([
                        'status'     => $finalStatus,
                        'bank_ref_no'  => $utr,
                        'responseBody'   => json_encode($request->all()),
                    ]);

//            //insert into admin ledger
//                if ($finalStatus == 'Success') {

//     $adminBalance = DB::table('users')->where('id',1)->first();

//     $adminOpenBalance = $adminBalance->balance;
//     $adminCloseBalance = $adminOpenBalance - $amount;

//     DB::table('users')
//         ->where('id',1)
//         ->update([
//             'balance' => $adminCloseBalance
//         ]);

//     DB::table('ledgers')->insert([
//         'merchant_id' => $transaction->remId,
//         'type' => 'debit',
//         'amount' => $amount,
//         'service' => 'UPI-PAYOUT',
//         'txn_id' => $txnId,
//         'payment_id' => $orderId,
//         'utr' => $utr,
//         'status'=>'Success',
//         'description' => "Payout for Order ID: $orderId",
//         'opening_balance' => $adminOpenBalance,
//         'closing_balance' => $adminCloseBalance,
//         'created_at' => now(),
//         'updated_at' => now(),
//     ]);

// }

            //send callback to merchant
            $callbackUrl = DB::table('remittances')
                ->where('remId', $transaction->remId)
                ->value('callback_url');

             if (!$callbackUrl) {
                    Log::warning("Callback URL not found for merchant", [
                        'remId' => $transaction->remId
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Callback URL not found for merchant'
                    ], 404);
                }
            $callbackData = [
                'service' =>'UPI-PAYOUT',
                'order_id' => $orderId,
                 'payment_id' => $transaction->payment_id,
                'reference_id' => $transaction->refId,
                'status' => $status,
                'amount' => $amount,
                'rrn' => $utr,
                'message' => $description
            ];
                $callbackResponse = Http::timeout(10)->post($callbackUrl, $callbackData);
    
                        Log::info("Callback Response", [
                            'order_id' => $transaction->order_id,
                            'callback_url' => $callbackUrl,
                            'payload' => $callbackData,
                            'response' => $callbackResponse->body()
                        ]);
            return response()->json([
                'status' => true,
                'message' => 'Callback processed successfully'
            ], 200);
        } catch (\Exception $e) {
            Log::error("UPI Callback Processing Error", [
                'error' => $e->getMessage(),
                'orderId' => $orderId,
                'txnId' => $txnId
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error processing callback',
                'error' => $e->getMessage()
            ], 500);
        }
    
    }
    public function PayoutCallback(Request $request)
    {
        Log::info("Payout Callback Received", [
            'payload' => $request->all()
        ]);

        $orderId = $request->input('orderId');
        $txnId = $request->input('txnId');
        $status = $request->input('status');
        $amount = $request->input('txn_amount');
        $utr = $request->input('utr');
        $description = $request->input('description');

        $transaction= DB::table('xpresspayout')
            ->where('orderId', $orderId)
            ->first();
            //return $transaction;

             if (!$transaction) {
            Log::warning("UPI Callback: Transaction Not Found", [
                'orderId' => $orderId,
                'txnId' => $txnId
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
        if ($transaction->status == 'Success') {
    return response()->json([
        'status' => true,
        'message' => 'Already processed'
    ]);
}
            //return $transaction;
       
        try {
            $finalStatus = 'Pending';
                if (strtolower($status) === 'success') {
                    $finalStatus = 'Success';
                } elseif (strtolower($status) === 'failed') {
                    $finalStatus = 'Failed';
                }
            DB::table('xpresspayout')
                ->where('orderId', $orderId)
    
                ->update([
                        'status'     => $finalStatus,
                        'bank_ref_no'  => $utr,
                        'responseBody'   => json_encode($request->all()),
                    ]);
                    

            //send callback to merchant
            $callbackUrl = DB::table('remittances')
                ->where('remId', $transaction->remId)
                ->value('callback_url');
  
             if (!$callbackUrl) {
                    Log::warning("Callback URL not found for merchant", [
                        'remId' => $transaction->remId
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Callback URL not found for merchant'
                    ], 404);
                }

             
            $callbackData = [
                'service' =>'PAYOUT',
                'orderId' => $orderId,
                'txnId' => $transaction->refId,
                'payment_id'=>$txnId,
                'status' => $status,
                'amount' => $amount,
                'utr' => $utr,
                'rrn' => $utr,
                'description' => $description
            ];

             $callbackResponse = Http::timeout(10)->post($callbackUrl, $callbackData);

                    Log::info("Callback Response", [
                        'order_id' => $transaction->orderId,
                        'callback_url' => $callbackUrl,
                        'payload' => $callbackData,
                        'response' => $callbackResponse->body()
                    ]);
        } catch (\Exception $e) {
            Log::error("UPI Callback Processing Error", [
                'error' => $e->getMessage(),
                'orderId' => $orderId,
                'txnId' => $txnId
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error processing callback',
                'error' => $e->getMessage()
            ], 500);
        }

        // Process the callback data as needed
        // For example, update transaction status in the database

        return response()->json([
            'status' => true,
            'message' => 'Callback received successfully'
        ], 200);

    }
    
    //aeps satelemet
    public function PayoutCallbackSTLM(Request $request)
    {   
        //return $request;
        Log::info("Payout Statemnt Callback Received", [
            'payload' => $request->all()
        ]);

        $orderId = $request->input('orderId');
        $txnId = $request->input('txnId');
        $status = $request->input('status');
        $amount = $request->input('txn_amount');
        $utr = $request->input('utr');
        $description = $request->input('description');

        $transaction= DB::table('aeps_stlm')
            ->where('orderId', $orderId)
            ->first();
            //return $transaction;

             if (!$transaction) {
            Log::warning("UPI Callback: Transaction Not Found", [
                'orderId' => $orderId,
                'txnId' => $txnId
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found'
            ], 404);
        }
        if ($transaction->status == 'Success') {
    return response()->json([
        'status' => true,
        'message' => 'Already processed'
    ]);
}
            //return $transaction;
       
        try {
            $finalStatus = 'Pending';
                if (strtolower($status) === 'success') {
                    $finalStatus = 'Success';
                } elseif (strtolower($status) === 'failed') {
                    $finalStatus = 'Failed';
                }
            DB::table('aeps_stlm')
                ->where('orderId', $orderId)
    
                ->update([
                        'status'     => $finalStatus,
                        'bank_ref_no'  => $utr,
                        'responseBody'   => json_encode($request->all()),
                    ]);
                    

            //send callback to merchant
            $callbackUrl = DB::table('remittances')
                ->where('remId', $transaction->remId)
                ->value('callback_url');

             if (!$callbackUrl) {
                    Log::warning("Callback URL not found for merchant", [
                        'remId' => $transaction->remId
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Callback URL not found for merchant'
                    ], 404);
                }

             
            $callbackData = [
                'service' =>'STLM',
                'orderId' => $orderId,
                'txnId' => $txnId,
                'payment_id'=>$transaction->payment_id,
                'status' => $status,
                'amount' => $amount,
                'utr' => $utr,
                'description' => $description
            ];

             $callbackResponse = Http::timeout(10)->post($callbackUrl, $callbackData);

                    Log::info("Callback Response", [
                        'order_id' => $transaction->orderId,
                        'callback_url' => $callbackUrl,
                        'payload' => $callbackData,
                        'response' => $callbackResponse->body()
                    ]);
        } catch (\Exception $e) {
            Log::error("UPI Callback Processing Error", [
                'error' => $e->getMessage(),
                'orderId' => $orderId,
                'txnId' => $txnId
            ]);
            return response()->json([
                'status' => false,
                'message' => 'Error processing callback',
                'error' => $e->getMessage()
            ], 500);
        }

        // Process the callback data as needed
        // For example, update transaction status in the database

        return response()->json([
            'status' => true,
            'message' => 'Callback received successfully'
        ], 200);

    }



    //aeronpay
     public function upi_initiateV2(Request $request)
    {
        try {
            // 🔹 Custom Validation
            $validator = Validator::make($request->all(), [
                'amount'         => 'required|numeric|min:1',
                'refId'          => 'required',
                'phone' => 'required',
                'name'      => 'required',
                'vpa'      => 'required',
                
                
            ], [
                'amount.required' => 'Transfer amount is required',
                'amount.numeric'  => 'Amount must be a number',
                'vpa.required' => 'UPI ID is required',
                'refId.required' => 'Reference ID is required',
                'name.required' => 'Name is required',
                
                
            ]);

            if ($validator->fails()) {
                Log::warning(" UPI V2 Validation Failed", [
                    'errors' => $validator->errors()->toArray(),
                    'request' => $request->all()
                ]);

                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 🔹 Collect Validated Data
            $data = $validator->validated();

            // 🔹 Call Helper
            $response = AeronpayHelper::initiateupipayout($data, request());

            // 🔹 Log API Response
            Log::info("Aeronapy main API UPI Response", [
                'request' => $data,
                'response' => $response
            ]);

            // 🔹 Standard API Response
            return $response;

        } catch (\Exception $e) {
            // 🔹 Log Exception
            Log::error("PayoutV6 Exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function initiateV2(Request $request)
{
    try {
        // 🔹 Custom Validation
        $validator = Validator::make($request->all(), [
            'amount'       => 'required|numeric|min:1|max:100000',
            'refId'        => 'required|string|max:50',
            'phone'        => 'required|digits:10',
            'name'         => 'required|string|max:100',
            'bankAccount'  => 'required|digits_between:9,18',
            'ifsc'         => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
        ], [
            'amount.required'      => 'Transfer amount is required',
            'amount.numeric'       => 'Amount must be a number',
            'amount.min'           => 'Minimum amount should be 1',
            'amount.max'           => 'Maximum amount limit exceeded',

            'refId.required'       => 'Reference ID is required',
            'refId.unique'         => 'Reference ID already exists',

            'phone.required'       => 'Mobile number is required',
            'phone.digits'         => 'Mobile number must be 10 digits',

            'name.required'        => 'Name is required',

            'bankAccount.required' => 'Bank account number is required',
            'bankAccount.digits_between' => 'Invalid bank account number',

            'ifsc.required'        => 'IFSC code is required',
            'ifsc.regex'           => 'Invalid IFSC code format',
        ]);

        // 🔹 Validation Failed
        if ($validator->fails()) {
            Log::warning("IMPS Payout V2 Validation Failed", [
                'errors' => $validator->errors()->toArray(),
                'request' => $request->all()
            ]);

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 🔹 Collect Validated Data
        $data = $validator->validated();

        // 🔹 Extra Security (Trim / Clean Data)
        $data['name'] = trim($data['name']);

        // 🔹 Call Helper
        $response = AeronpayHelper::initiate($data);

        // 🔹 Log API Response
        Log::info("Aeronpay IMPS API Response", [
            'request'  => $data,
            'response' => $response
        ]);

        // 🔹 Standard API Response

        return $response;
        // return response()->json([
        //     'status'  => $response['success'] ?? false,
        //     'message' => $response['message'] ?? 'Something went wrong',
        //     'data'    => $response
        // ], 200);

    } catch (\Exception $e) {

        // 🔹 Log Exception
        Log::error("PayoutV2 Exception", [
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
            'request' => $request->all()
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'Internal server error',
            'error'   => $e->getMessage()
        ], 500);
    }
}
}
