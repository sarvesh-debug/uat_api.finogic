<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ManulaUtrUpationController extends Controller
{
    public function manualProcess(Request $request)
    {
        // ✅ Validation
        $request->validate([
            'txn_id' => 'required|integer|exists:upipayout,id',
            'utr'    => 'nullable|string|max:100',
            'status' => 'required|in:SUCCESS,FAILED,PENDING',
            'reason' => 'required|string',
            'remark' => 'nullable|string',
        ]);

        try {

            $transactionId = $request->txn_id;

            // ✅ Get transaction
            $transaction = DB::table('upipayout')
                ->where('id', $transactionId)
                ->first();

            if (!$transaction) {
                return back()->with('error', 'Transaction not found');
            }

            // ✅ Update transaction
            DB::table('upipayout')
                ->where('id', $transactionId)
                ->update([
                    'status'       => $request->status,
                    'bank_ref_no'  => $request->utr,
                    'remark'       => $request->remark ?? $request->reason,
                    'updated_at'   => now(),
                ]);

            // ✅ Fresh updated data
            $updatedTxn = DB::table('upipayout')
                ->where('id', $transactionId)
                ->first();

            // ✅ Get callback URL
            $callbackUrl = DB::table('remittances')
                ->where('remId', $updatedTxn->remId)
                ->value('callback_url');

            if ($callbackUrl) {

                $callbackData = [
                    'service'       => 'UPI-PAYOUT',
                    'order_id'      => $updatedTxn->order_id,
                    'payment_id'    => $updatedTxn->payment_id,
                    'reference_id'  => $updatedTxn->refId,
                    'status'        => $updatedTxn->status,
                    'amount'        => $updatedTxn->amount,
                    'rrn'           => $updatedTxn->bank_ref_no,
                    'remark'        => $updatedTxn->remark,
                ];

                try {
                    $response = Http::timeout(10)->post($callbackUrl, $callbackData);

                    Log::info("Manual Callback Sent", [
                        'txn_id' => $transactionId,
                        'url'    => $callbackUrl,
                        'data'   => $callbackData,
                        'resp'   => $response->body()
                    ]);

                } catch (\Exception $e) {

                    Log::error("Callback Failed", [
                        'txn_id' => $transactionId,
                        'error'  => $e->getMessage()
                    ]);
                }

            } else {

                Log::warning("Callback URL not found", [
                    'remId' => $updatedTxn->remId
                ]);
            }

            // ✅ Always redirect (UI flow safe)
            return redirect()
                ->route('admin.upi.reports')
                ->with('success', 'Manual update + callback processed');

        } catch (\Exception $e) {

            Log::error("Manual Update Error", [
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Something went wrong');
        }
    }
}