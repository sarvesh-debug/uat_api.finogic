<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class creditDebitController extends Controller
{
    public function index()
    {
        $merchantDetails=DB::table('remittances')->get();
        return view('admin.merchant.index', compact('merchantDetails'));  
    }

    public function credit(Request $request)
{
    $request->validate([
        'id' => 'required',
        'amount' => 'required|numeric|min:1',
        'remark' => 'required|string|max:255'
    ]);

    $merchant = DB::table('remittances')->where('remId', $request->id)->first();

    $openingBalance = $merchant->amount;
    $newAmount = $openingBalance + $request->amount;

    DB::table('remittances')->where('remId', $request->id)->update([
        'amount' => $newAmount
    ]);

    // 👉 Transaction Store
    DB::table('reserveAmount')->insert([
        'remittance_id' => $request->id,
        'type' => 'credit',
        'amount' => $request->amount,
        'opening_balance' => $openingBalance,
        'closing_balance' => $newAmount,
        'remark' => $request->remark,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return back()->with('success', 'Amount Credited Successfully');
}

public function debit(Request $request)
{
    $request->validate([
        'id' => 'required',
        'amount' => 'required|numeric|min:1',
        'remark' => 'required|string|max:255'
    ]);

    $merchant = DB::table('remittances')->where('remId', $request->id)->first();

    if ($merchant->amount < $request->amount) {
        return back()->with('error', 'Insufficient Balance');
    }

    $openingBalance = $merchant->amount;
    $newAmount = $openingBalance - $request->amount;

    DB::table('remittances')->where('remId', $request->id)->update([
        'amount' => $newAmount
    ]);

    // 👉 Transaction Store
    DB::table('reserveAmount')->insert([
        'remittance_id' => $request->id,
        'type' => 'debit',
        'amount' => $request->amount,
        'opening_balance' => $openingBalance,
        'closing_balance' => $newAmount,
        'remark' => $request->remark,
        'created_at' => now(),
        'updated_at' => now()
    ]);

    return back()->with('success', 'Amount Debited Successfully');
}
public function reports(Request $request)
{
    $query = DB::table('reserveAmount')
        ->join('remittances', function ($join) {
            $join->on(
                DB::raw('reserveAmount.remittance_id COLLATE utf8mb4_unicode_ci'),
                '=',
                DB::raw('remittances.remId COLLATE utf8mb4_unicode_ci')
            );
        })
        ->select(
            'reserveAmount.*',
            'remittances.remId',
            'remittances.email'
        );

    if ($request->type) {
        $query->where('reserveAmount.type', $request->type);
    }

    if ($request->from && $request->to) {
        $query->whereBetween('reserveAmount.created_at', [
            $request->from . ' 00:00:00',
            $request->to . ' 23:59:59'
        ]);
    }

    $reports = $query->orderBy('reserveAmount.id', 'desc')->paginate(10);

    return view('admin.merchant.reports', compact('reports'));
}
}
