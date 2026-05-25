<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ManulFund;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManulFundController extends Controller
{
    // Show form
    public function create()
    {
        return view('manulfund');
    }

    // Store data
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'remark' => 'nullable|string|max:255',
        ]);

        // Get last closing balance
        $lastBalance = DB::table('users')->where('id', Auth::id())->value('balance');
        $lastBalance = $lastBalance ?? 0;

        $amount = $request->amount;
        $clbalance = $lastBalance + $amount; // new closing balance

        ManulFund::create([
            'amount' => $amount,
            'opbalance' => $lastBalance,
            'clbalance' => $clbalance,
            'remark' => $request->remark, 
            'added_by' => Auth::id() ?? null,
        ]);
            DB::table('users')->where('id', Auth::id())->increment('balance', $amount);
        return redirect()->back()->with('success', 'Amount added successfully!');
    }

    public function index()
{
    $funds = \App\Models\ManulFund::with('user') ->latest()
        ->paginate(10);

    return view('manualRec', compact('funds'));
}

}
