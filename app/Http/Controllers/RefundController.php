<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
class RefundController extends Controller
{
    public function processRefund(Request $request)
    {
              //return $request->all();
        $transactionId = $request->input('txn_id');
        $amount = $request->input('amount');
        //return response()->json(['transaction_id' => $transactionId, 'amount' => $amount]);
        $refundCharges = $request->input('refund_charges', false);

        //return $refundCharges;
        $transaction =DB::table('xpresspayout')->where('id', $transactionId)->first();
       // return $transaction;
        $paymmentId = $transaction->payment_id;
        $amount = $transaction->amount;
        $merchantId = $transaction->remId;
        $tds= $transaction->tds;
        $charges = $transaction->charge;

        if ($refundCharges) {
            $charges = $transaction->charge + $transaction->tds;
        } else {
            $charges = 0;
                $tds = 0;
        }

        $totalRefundAmount = $amount + $charges;

        $refundId = 'CDRUF' . strtoupper(Str::random(10));

            // return response()->json([
            //     'merchant_id' => $merchantId,
            //     'refund_id' => $refundId,
            //     'transaction_id' => $paymmentId,
            //     'amount' => $amount,
            //     'tds' => $tds,
            //     'charges' => $charges,
            //     'total_refund_amount' => $totalRefundAmount
            // ]);

        // Save refund details to the database
        DB::table('refunds')->insert([
            'service' => 'Payout',
            'service_ref_id' => $refundId,
            'user_id' => $merchantId,
            'transaction_id' => $paymmentId,
            'reason' => $request->input('reason'),
            'opening_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount'),
            'closing_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount') + $totalRefundAmount,
            'processed_by' => auth()->user()->name,
            'amount' => $totalRefundAmount,
            'status' => 'Refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //update main xpress payout table with refund status
        DB::table('xpresspayout')->where('id', $transactionId)->update([
            'status' => 'Refunded',
            'updated_at' => now(),
        ]);

        //update current balance of merchant
        DB::table('remittances')->where('remId', $merchantId)
            ->increment('amount', $totalRefundAmount); 

        // Implement your refund logic here, e.g., call payment gateway API

        // For demonstration, we'll just return a success response
       return redirect()
        ->route('admin.refund.reports.page')
        ->with('success', 'Refund processed successfully');


    }   


    public function processRefundUPI(Request $request)
    {
             // return $request->all();
        $transactionId = $request->input('txn_id');
        $amount = $request->input('amount');
        //return response()->json(['transaction_id' => $transactionId, 'amount' => $amount]);
        $refundCharges = $request->input('refund_charges', false);

        //return $refundCharges;
        $transaction =DB::table('upipayout')->where('id', $transactionId)->first();
       // return $transaction;
        $paymmentId = $transaction->payment_id;
        $amount = $transaction->amount;
        $merchantId = $transaction->remId;
        $tds= $transaction->tds;
        $charges = $transaction->charge;

        if ($refundCharges) {
            $charges = $transaction->charge + $transaction->tds;
        } else {
            $charges = 0;
                $tds = 0;
        }

        $totalRefundAmount = $amount + $charges;

        $refundId = 'CDRUF' . strtoupper(Str::random(10));

            // return response()->json([
            //     'merchant_id' => $merchantId,
            //     'refund_id' => $refundId,
            //     'transaction_id' => $paymmentId,
            //     'amount' => $amount,
            //     'tds' => $tds,
            //     'charges' => $charges,
            //     'total_refund_amount' => $totalRefundAmount
            // ]);

        // Save refund details to the database
        DB::table('refunds')->insert([
            'service' => 'UPI_PAYOUT',
            'service_ref_id' => $refundId,
            'user_id' => $merchantId,
            'transaction_id' => $paymmentId,
            'reason' => $request->input('reason'),
            'opening_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount'),
            'closing_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount') + $totalRefundAmount,
            'processed_by' => auth()->user()->name,
            'amount' => $totalRefundAmount,
            'status' => 'Refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //update main xpress payout table with refund status
        DB::table('upipayout')->where('id', $transactionId)->update([
            'status' => 'Refunded',
            'updated_at' => now(),
        ]);

        //update current balance of merchant
        DB::table('remittances')->where('remId', $merchantId)
            ->increment('amount', $totalRefundAmount); 

        // Implement your refund logic here, e.g., call payment gateway API

        // For demonstration, we'll just return a success response
       return redirect()
        ->route('admin.refund.reports.page')
        ->with('success', 'Refund processed successfully');


    }   



    public function refundReportAdminPage(Request $request)
    {
        return view('admin.reports.message');
    }

    public function refundReportAdmin(Request $request)
{
    $refunds = DB::table('refunds')
                ->orderBy('created_at', 'desc')
                ->paginate(10);

    return view('admin.reports.refund_report', compact('refunds'));
}


 public function refundReportUser(Request $request)
{
    $refunds = DB::table('refunds')
                ->where('user_id', Auth::guard('remittance')->user()->remId)
                ->orderBy('created_at', 'desc')
                ->paginate(10);
   // return $refunds;
    return view('users.txn.refundTxn', compact('refunds'));
}


//Aeronpay
 public function processRefundV2(Request $request)
    {
            //  return $request->all();
        $transactionId = $request->input('txn_id');
        $amount = $request->input('amount');
        //return response()->json(['transaction_id' => $transactionId, 'amount' => $amount]);
        $refundCharges = $request->input('refund_charges', false);

        //return $refundCharges;
        $transaction =DB::table('xpresspayout2')->where('id', $transactionId)->first();
       // return $transaction;
        $paymmentId = $transaction->payment_id;
        $amount = $transaction->amount;
        $merchantId = $transaction->remId;
        $tds= $transaction->tds;
        $charges = $transaction->charge;

        if ($refundCharges) {
            $charges = $transaction->charge + $transaction->tds;
        } else {
            $charges = 0;
                $tds = 0;
        }

        $totalRefundAmount = $amount + $charges;

        $refundId = 'CDRUF' . strtoupper(Str::random(10));

            // return response()->json([
            //     'merchant_id' => $merchantId,
            //     'refund_id' => $refundId,
            //     'transaction_id' => $paymmentId,
            //     'amount' => $amount,
            //     'tds' => $tds,
            //     'charges' => $charges,
            //     'total_refund_amount' => $totalRefundAmount
            // ]);

        // Save refund details to the database
        DB::table('refunds')->insert([
            'service' => 'Payout_P2',
            'service_ref_id' => $refundId,
            'user_id' => $merchantId,
            'transaction_id' => $paymmentId,
            'reason' => $request->input('reason'),
            'opening_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount'),
            'closing_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount') + $totalRefundAmount,
            'processed_by' => auth()->user()->name,
            'amount' => $totalRefundAmount,
            'status' => 'Refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //update main xpress payout table with refund status
        DB::table('xpresspayout2')->where('id', $transactionId)->update([
            'status' => 'Refunded',
            'updated_at' => now(),
        ]);

        //update current balance of merchant
        DB::table('remittances')->where('remId', $merchantId)
            ->increment('amount', $totalRefundAmount); 

        // Implement your refund logic here, e.g., call payment gateway API

        // For demonstration, we'll just return a success response
       return redirect()
        ->route('admin.refund.reports.page')
        ->with('success', 'Refund processed successfully');


    }
    
    public function processRefundUPIv2(Request $request)
    {
             // return $request->all();
        $transactionId = $request->input('txn_id');
        $amount = $request->input('amount');
        //return response()->json(['transaction_id' => $transactionId, 'amount' => $amount]);
        $refundCharges = $request->input('refund_charges', false);

        //return $refundCharges;
        $transaction =DB::table('upipayout2')->where('id', $transactionId)->first();
       // return $transaction;
        $paymmentId = $transaction->payment_id;
        $amount = $transaction->amount;
        $merchantId = $transaction->remId;
        $tds= $transaction->tds;
        $charges = $transaction->charge;

        if ($refundCharges) {
            $charges = $transaction->charge + $transaction->tds;
        } else {
            $charges = 0;
                $tds = 0;
        }

        $totalRefundAmount = $amount + $charges;

        $refundId = 'CDRUF' . strtoupper(Str::random(10));

            // return response()->json([
            //     'merchant_id' => $merchantId,
            //     'refund_id' => $refundId,
            //     'transaction_id' => $paymmentId,
            //     'amount' => $amount,
            //     'tds' => $tds,
            //     'charges' => $charges,
            //     'total_refund_amount' => $totalRefundAmount
            // ]);

        // Save refund details to the database
        DB::table('refunds')->insert([
            'service' => 'UPI_PAYOUT_P2',
            'service_ref_id' => $refundId,
            'user_id' => $merchantId,
            'transaction_id' => $paymmentId,
            'reason' => $request->input('reason'),
            'opening_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount'),
            'closing_balance' => DB::table('remittances')->where('remId', $merchantId)->value('amount') + $totalRefundAmount,
            'processed_by' => auth()->user()->name,
            'amount' => $totalRefundAmount,
            'status' => 'Refunded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        //update main xpress payout table with refund status
        DB::table('upipayout2')->where('id', $transactionId)->update([
            'status' => 'Refunded',
            'updated_at' => now(),
        ]);

        //update current balance of merchant
        DB::table('remittances')->where('remId', $merchantId)
            ->increment('amount', $totalRefundAmount); 

        // Implement your refund logic here, e.g., call payment gateway API

        // For demonstration, we'll just return a success response
       return redirect()
        ->route('admin.refund.reports.page')
        ->with('success', 'Refund processed successfully');


    }   

}
