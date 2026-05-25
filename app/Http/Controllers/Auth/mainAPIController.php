<?php

namespace App\Http\Controllers;

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
            ->where('orderId', $orderId)
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
        try {
            $finalStatus = 'Pending';
                if (strtolower($status) === 'success') {
                    $finalStatus = 'Success';
                } elseif (strtolower($status) === 'failed') {
                    $finalStatus = 'Failed';
                }
            DB::table('upipayout')
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
                'service' =>'UPI-PAYOUT',
                'order_id' => $orderId,
                 'payment_id' => $transaction->payment_id,
                'reference_id' => $transaction->refId,
                'status' => $status,
                'amount' => $amount,
                'rrn' => $utr,
                'message' => $description
            ];
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
                'txnId' => $txnId,
                'status' => $status,
                'amount' => $amount,
                'utr' => $utr,
                'description' => $description
            ];

             $callbackResponse = Http::timeout(10)->post($callbackUrl, $callbackData);

                    Log::info("Callback Response", [
                        'order_id' => $transaction->order_id,
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

}
