<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Remittance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class RemittanceController extends Controller
{
    // All remittances
   public function index()
{
    $remittances = Remittance::with('package')->where('status','!=','rejected')->latest()->get();

    //return $remittances;die();
    return view('admin.remittances.index', compact('remittances'));
}
// RemittanceController.php
public function assignPackage(Request $request, $id) {
    $request->validate([
        'packageId' => 'required|exists:packages,id'
    ]);

    $remittance = Remittance::findOrFail($id);
    $remittance->packageId = $request->packageId;
    $remittance->save();

    return redirect()->back()->with('success', 'Package assigned successfully!');
}

    // Show rejected remittances
    public function rejected()
    {
        $remittances = Remittance::where('status', 'rejected')->latest()->get();
        return view('admin.remittances.rejected', compact('remittances'));
    }

    // Show details of a remittance
    public function show($id)
    {
        $remittance = Remittance::findOrFail($id);
        return view('admin.remittances.show', compact('remittance'));
    }

    // Approve remittance
    public function approve($id)
    {
        $remittance = Remittance::findOrFail($id);
        $remittance->status = 'success';
        $remittance->isKyc = 1; // Set KYC to 1 on approval
        $remittance->save();

        return redirect()->back()->with('success', 'Remittance approved successfully and KYC updated.');
    }

    // Reject remittance
    public function reject($id, Request $request)
    {
        $remittance = Remittance::findOrFail($id);
        $remittance->status = 'rejected';
        $remittance->remarks = $request->remarks ?? 'Rejected by Admin';
        $remittance->isKyc = 0; // Reset KYC to 0 on rejection
        $remittance->save();

        return redirect()->back()->with('success', 'Remittance rejected successfully and KYC reset.');
    }


     public function generateApiKey(Request $request, $rtId)

    {
        // 1. Check if remittance record exists
        $remittance = DB::table('remittances')->where('remId', $rtId)->first();

        if (!$remittance) {
            return response()->json([
                'status'  => 'error',
                'message' => "Remittance ID {$rtId} not found."
            ], 404);
        }

        // 2. Generate unique API key
        do {
            $plainKey = 'cg_xpyt_' . bin2hex(random_bytes(32)); // e.g. cg_xpyt_abcd123...
            $exists = DB::table('remittances')
                        ->where('apikey', $plainKey)
                        ->exists();
        } while ($exists); // regenerate if duplicate (rare)

        // 3. Update record with API key
        DB::table('remittances')
            ->where('remId', $rtId)
            ->update([
                'apikey'     => $plainKey,
                'updated_at' => now()
            ]);

        // 4. Return response with key (only once)
        return response()->json([
            'status'   => 'success',
            'message'  => 'API Key generated successfully.',
            'remId'    => $rtId,
            'api_key'  => $plainKey, //  Show only once
        ]);
    }


    public function success()
    {
        $remittances = Remittance::where('status', 'success')->latest()->get();
        return view('admin.remittances.success', compact('remittances'));
    }


    public function generateKey($remId)
{
      // 1. Check if remittance record exists
        $remittance = DB::table('remittances')->where('remId', $remId)->first();

        if (!$remittance) {
            return response()->json([
                'status'  => 'error',
                'message' => "Remittance ID {$remId} not found."
            ], 404);
        }

        // 2. Generate unique API key
        do {
            $plainKey = 'cg_xpyt_' . bin2hex(random_bytes(32)); // e.g. cg_xpyt_abcd123...
            $exists = DB::table('remittances')
                        ->where('apikey', $plainKey)
                        ->exists();
        } while ($exists); // regenerate if duplicate (rare)

        // 3. Update record with API key
        DB::table('remittances')
            ->where('remId', $remId)
            ->update([
                'apikey'     => $plainKey,
                'updated_at' => now()
            ]);

        // 4. Return response with key (only once)
        return response()->json([
            'status'   => 'success',
            'message'  => 'API Key generated successfully.',
            'remId'    => $remId,
            'api_key'  => $plainKey, //  Show only once
        ]);
}

public function toggleService(Request $request, $id)
{
   // return $request;die();
    $rem = Remittance::findOrFail($id);
    $service = $request->input('service');

    if (!in_array($service, ['payout1','payout2','payout5','upipayout','upipayout2','pgpayout','pgpayout1','pgpayout2','isDMT','isAEPS','isBBPS','isAcc','isVPA','ccpay'])) {
        return response()->json(['status'=>'error','message'=>'Invalid service']);
    }

    $rem->$service = $rem->$service == 1 ? 0 : 1;
    $rem->save();

    return redirect()->back()->with('service_status', [
    'status' => 'success',
    'message' => ucfirst($service) . ' ' . ($rem->$service ? 'Activated' : 'Deactivated')
]);

    // return response()->json([
    //     'status' => 'success',
    //     'service' => $service,
    //     'new_status' => $rem->$service,
    //     'message' => ucfirst($service) . ' ' . ($rem->$service ? 'Activated' : 'Deactivated')
    // ]);
}



public function lockRemitterAmount(Request $request, $id)
{
   

    $request->validate([
     
        'amount' => 'required|numeric|min:1',
        'remark' => 'nullable|string|max:255'
    ]);
    //return $request;die();
    $remId = DB::table('remittances')->where('id', $id)->value('remId');
    //return $remId;die();
    DB::beginTransaction();

    try {
        $rem = DB::table('remittances')->where('remId', $remId)->first();

        //return $rem;die();
        if (!$rem) {
            return back()->with('error', 'Remitter not found.');
        }

        if ($rem->amount < $request->amount) {
            return back()->with('error', 'Insufficient available balance.');
        }
     
        // Calculate new balances
        $newAvailable = $rem->amount - $request->amount;
        $newLocked = $rem->lockBalance + $request->amount;

        DB::table('remittances')->where('remId', $remId)->update([
            'amount' => $newAvailable,
            'lockBalance' => $newLocked,
            'updated_at' => now(),
        ]);

        // Insert into lock transaction log
        DB::table('lock_transactions')->insert([
            'remId' => $remId,
            'amount' => $request->amount,
            'before_available' => $rem->amount,
            'after_available' => $newAvailable,
            'before_locked' => $rem->lockBalance ?? 0,
            'after_locked' => $newLocked,
            'type' => 'LOCK',
            'remark' => $request->remark ?? 'Locked by admin',
            'created_by' => auth()->user()->name ?? 'Admin',
            'created_at' => now(),
        ]);

        DB::commit();

        return back()->with('success', 'Amount locked successfully for Remitter ID: ' . $request->remId);

    } catch (\Exception $e) {
        return $e;die();
        DB::rollBack();
        return back()->with('error', 'Error locking amount: ' . $e->getMessage());
    }
}


public function lockTransactionsList()
{
    $transactions = DB::table('lock_transactions')
        ->orderByDesc('id')
        ->paginate(10);

    return view('admin.remittances.lock-list', compact('transactions'));
}


public function releaseLockedAmount(Request $request)
{
    //return $request;die();
    $request->validate([
        'remId' => 'required|string|exists:remittances,remId',
        'amount' => 'required|numeric|min:0.01',
        'remark' => 'nullable|string',
    ]);

    $rem = Remittance::where('remId', $request->remId)->first();

    //return $rem;die();  
    if ($rem->lockBalance < $request->amount) {
        return back()->with('error', 'Insufficient locked balance.');
    }

    // Update balances
    $beforeLocked = $rem->lockBalance;
    $beforeAvailable = $rem->amount;

    $rem->lockBalance -= $request->amount;
    $rem->amount += $request->amount;
    $rem->save();

    // Log in lock_transactions
    DB::table('lock_transactions')->insert([
        'remId' => $rem->remId,
        'amount' => $request->amount,
        'before_available' => $beforeAvailable,
        'after_available' => $rem->amount,
        'before_locked' => $beforeLocked ?? 0,
        'after_locked' => $rem->lockBalance,
        'type' => 'UNLOCK',
        'remark' => $request->remark,
        'created_by' => auth()->user()->name ?? 'Admin',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return back()->with('success', 'Locked amount successfully released.');
}


public function registerIp(Request $request,$id)
{

$remittance = DB::table('remittances')->where('remId',$id)->first();

if(!$remittance){
return response()->json([
'status'=>false,
'message'=>'User not found'
]);
}

DB::table('remittances')
->where('remId',$id)
->update([
'ipAddress'=>$request->ipAddress
]);

return response()->json([
'status'=>true,
'message'=>'IP Address updated'
]);

}
public function saveCallbackUrl(Request $request,$id)
{

$remittance = DB::table('remittances')->where('remId',$id)->first();

if(!$remittance){
return response()->json([
'status'=>false,
'message'=>'User not found'
]);
}

DB::table('remittances')
->where('remId',$id)
->update([
'callback_url'=>$request->callbackUrl
]);

return response()->json([
'status'=>true,
'message'=>'Callback URL updated'
]);

}

}
