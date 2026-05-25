<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class aeronpayCallBackController extends Controller
{
    public function callBackManger(Request $request)
    {
        // 🔹 Log Incoming Callback Data
        // Log::info("Aeronpay Callback Received", [
        //     'data' => $request->all()
        // ]);
          Log::channel('fundtransfer')->info("Aeronpay Callback Received", [
            'data' => $request->all()
        ]);

        $data=$request->all();

        $payment_id=$data['client_referenceId'];
        $utr=$data['utr'];
        $status=$data['status'];
        $statusCode=$data['statusCode'];
        $amount=$data['txn_amount'];
        $orderId=$data['transactionId'];

        //dd($payment_id,$utr,$status,$statusCode,$amount,$orderId);

        $upi=DB::table('upipayout')->where('payment_id',$payment_id)->first();
        $imps=DB::table('xpresspayout')->where('payment_id',$payment_id)->first();

        if($upi)
            {
                Log::info("UPI Callback Received", [
                    'payload' => $request->all()
                ]);
                
                DB::table('aeronpay_callback')
                ->insert([
                    'service' =>'UPI',
                    'refid' =>$payment_id,
                    'status' =>$status,
                    'amount' =>$amount,
                    'responsedata' =>json_encode($request->all()),
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
                // $orderId = $request->input('orderId');
                // $txnId = $request->input('txnId');
                // $status = $request->input('status');
                // $amount = $request->input('txn_amount');
                // $utr = $request->input('utr');
                // $description = $request->input('description');
                $transaction= DB::table('upipayout')
                    ->where('payment_id', $payment_id)
                    ->first();
                if (!$transaction) {
                    Log::warning("UPI Callback: Transaction Not Found", [
                        'payment_id' => $payment_id,
                        'txnId' => $orderId
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
                        ->where('payment_id', $payment_id)
            
                        ->update([
                                'status'     => $finalStatus,
                                'order_id' =>$orderId,
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
                        // 'service' =>'UPI-PAYOUT_P2',
                        // 'payment_id' => $transaction->payment_id,
                        // 'reference_id' => $transaction->refId,
                        // 'status' => $status,
                        // 'amount' => $amount,
                        // 'rrn' => $utr,

                        'service' =>'UPI-PAYOUT',
                        'order_id' => $orderId,
                        'payment_id' => $transaction->payment_id,
                        'reference_id' => $transaction->refId,
                        'status' => $status,
                        'amount' => $amount,
                        'rrn' => $utr,
                        'message' => "CallBack Send Successfully"
                        
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
                        'Payment_Id' => $payment_id,
                        'txnId' => $orderId
                    ]);
                    return response()->json([
                        'status' => false,
                        'message' => 'Error processing callback',
                        'error' => $e->getMessage()
                    ], 500);
                }
            }
        elseif($imps)
            {
                        Log::info("Payout Callback Received", [
                    'payload' => $request->all()
                ]);
                   

                DB::table('aeronpay_callback')
                ->insert([
                    'service' =>'IMPS',
                    'refid' =>$payment_id,
                    'status' =>$status,
                    'amount' =>$amount,
                    'responsedata' =>json_encode($request->all()),
                    'created_at'=>now(),
                    'updated_at'=>now(),
                ]);
                // $orderId = $request->input('orderId');
                // $txnId = $request->input('txnId');
                // $status = $request->input('status');
                // $amount = $request->input('txn_amount');
                // $utr = $request->input('utr');
                // $description = $request->input('description');

                $transaction= DB::table('xpresspayout')
                    ->where('payment_id', $payment_id)
                    ->first();
                    //return $transaction;

                    if (!$transaction) {
                    Log::warning("UPI Callback: Transaction Not Found", [
                        'payment_id' => $payment_id,
                        'txnId' => $orderId
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
                        ->where('payment_id', $payment_id)
            
                        ->update([
                                'status'     => $finalStatus,
                                'bank_ref_no'  => $utr,

                                'orderId' =>$orderId,
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
                        // 'service' =>'PAYOUT_P2',
                        // 'txnId' => $transaction->refId,
                        // 'payment_id'=>$payment_id,
                        // 'status' => $status,
                        // 'amount' => $amount,
                        // 'utr' => $utr,
                        // 'rrn' => $utr,

                         'service' =>'PAYOUT',
                        'orderId' => $orderId,
                        'txnId' => $transaction->refId,
                        'payment_id'=>$transaction->payment_id,
                        'status' => $status,
                        'amount' => $amount,
                        'utr' => $utr,
                        'rrn' => $utr,
                        'description' => "CallBack Send Successfully"
                        
                    ];

                    $callbackResponse = Http::timeout(10)->post($callbackUrl, $callbackData);

                            Log::info("Callback Response", [
                                'payment_id' => $transaction->payment_id,
                                'callback_url' => $callbackUrl,
                                'payload' => $callbackData,
                                'response' => $callbackResponse->body()
                            ]);
                } catch (\Exception $e) {
                    Log::error("IMPS Callback Processing Error", [
                        'error' => $e->getMessage(),
                        'payment_id' => $payment_id,
                        'txnId' => $transaction->refid
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
            else
            {
                 return response()->json([
                'message' => 'Invalid callback data'
            ], 400);

            }

        // 🔹 Optional: Validate required fields (testing ke liye basic)
        if (!$request->has('status')) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid callback data'
            ], 400);
        }

        // 🔹 Testing Response to Provider
        return response()->json([
            'status'  => 'success',
            'message' => 'Callback received successfully'
        ], 200);
    }
}