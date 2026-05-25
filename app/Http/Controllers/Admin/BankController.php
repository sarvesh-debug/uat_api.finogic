<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Aws\S3\Exception\S3Exception;
use Illuminate\Support\Facades\Storage; // ✅ Add this
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
class BankController extends Controller
{
    public function index()
    {
        $banks = DB::table('banks')->orderBy('created_at', 'ASC')->get();

        return view('banks.index', compact('banks'));
    }  
    public function store(Request $request)
{
    //return $request;die();
    try {
        // ✅ Manual validation (gives more control)
        $validator = Validator::make($request->all(), [
            'bank_name'           => 'required|string|max:255',
            'account_no'          => 'required|string|max:50|unique:banks,account_no',
            'ifsc'                => 'required|string|max:20',
            'account_holder_name' => 'required|string|max:255',
            'status'              => 'required|in:active,inactive',
        ]);
    //return $request;die();

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)   // send validation errors back
                ->withInput();             // keep old input in form
        }

        // ✅ Insert data
        DB::table('banks')->insert([
            'bank_name'            => $request->bank_name,
            'account_no'           => $request->account_no,
            'ifsc'                 => $request->ifsc,
            'account_holder_name'  => $request->account_holder_name,
            'status'               => $request->status,
            'emp_id'               => auth()->id(),
            'created_at'           => now(),
            'updated_at'           => now(),
        ]);

       // return "ok";

       return redirect()->back()->with('success', 'Bank added successfully.');

    } catch (\Exception $e) {
        // ✅ Log error for debugging
        Log::error('Bank Store Error: ' . $e->getMessage());

       // return $e;
        return redirect()->back()
            ->with('error', 'Something went wrong! Please try again.')
            ->withInput();
    }
} 

public function update(Request $request, $id)
{
   // return $request;die();
    
    try {
        // ✅ Find the bank record
        $bank = DB::table('banks')->where('id', $id)->first();

        if (!$bank) {
            return redirect()->back()->with('error', 'Bank record not found.');
        }

        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'bank_name'           => 'required|string|max:255',
            'account_no'          => 'required|string|max:50|unique:banks,account_no,' . $bank->id,
            'ifsc'                => 'required|string|max:20',
            'account_holder_name' => 'required|string|max:255',
            'status'              => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        //return $validator;die();
        // ✅ Update record
        DB::table('banks')
            ->where('id', $id)
            ->update([
                'bank_name'           => $request->bank_name,
                'account_no'          => $request->account_no,
                'ifsc'                => $request->ifsc,
                'account_holder_name' => $request->account_holder_name,
                'status'              => $request->status,
                'updated_at'          => now(),
            ]);

        return redirect()->back()->with('success', 'Bank updated successfully.');
       //return $e;

    } catch (\Exception $e) {
        Log::error('Bank Update Error: ' . $e->getMessage());
        return $e;
        return redirect()->back()
            ->with('error', 'Something went wrong while updating the bank.')
            ->withInput();
     }
}


    /**
     * Delete a bank.
     */
    public function destroy($id)
    {
         //return $id;die();
        $bank = DB::table('banks')->where('id', $id)->first();
        DB::table('banks')->where('id', $id)->delete();

        return redirect()->back()->with('success', 'Bank deleted successfully.');
    }

    public function fundRequest()
{
     //$rid = auth('remittance')->user()->remId;
     //return $rid;die();
    $fundRequests = DB::table('rem_fundrequest')
        ->leftJoin('banks', 'rem_fundrequest.bank_id', '=', 'banks.id')
        ->select('rem_fundrequest.*', 'banks.bank_name', 'banks.account_no')
        ->orderBy('rem_fundrequest.created_at', 'DESC')
        ->get();
//return $fundRequests;die();
    return view('banks.fundRequest', compact('fundRequests'));
}

public function fundRequestAccept(Request $request, $id)
{
   // return $request;die();
     //return $id;die();
   try {
        $fundRequest = DB::table('rem_fundrequest')->where('id', $id)->first();
        $remitterWallet = DB::table('remittances')->where('remId', $fundRequest->rid)->first();
        $closingBalance = $remitterWallet->amount + $fundRequest->amount;
        //return $fundRequest->rid;die();
        if (!$fundRequest) {
            return redirect()->back()->withErrors(['msg' => 'Fund request not found.']);
        }

        if ($fundRequest->status != 0) {
            return redirect()->back()->withErrors(['msg' => 'This fund request has already been processed.']);
        }

        // Update the fund request status to accepted (1)
             // 1. Update fund request status
    try {
        DB::table('rem_fundrequest')
            ->where('id', $id)
            ->update([
                'status' => 1,
                'updated_at' => now(),
                'openingBalance' => $remitterWallet->amount,
                'closingBalance' => $closingBalance
            ]);
    } catch (\Exception $e) {
        Log::error("Error updating fund request status: " . $e->getMessage());
        //return $e;die();
        return redirect()->back()->withErrors(['msg' => 'Failed to update fund request status.']);
    }

    // 3. Increment remitter wallet
    try {
        DB::table('remittances')
            ->where('remId', $fundRequest->rid)
            ->increment('amount', $fundRequest->amount);
    } catch (\Exception $e) {
        Log::error("Error incrementing reseller wallet: " . $e->getMessage());
       // return $e;die();
        return redirect()->back()->withErrors(['msg' => 'Failed to increment reseller wallet.']);
    }



        // Optionally, you can add logic here to update the reseller's balance or notify them

        return redirect()->back()->with('success', 'Fund request accepted successfully.');

    } catch (\Exception $e) {
        Log::error('Accept Fund Request Error: ' . $e->getMessage());
        //return $e;die();
        return redirect()->back()->withErrors(['msg' => 'An error occurred while processing your request. Please try again later.']);
    }  
}

public function fundRequestReject(Request $request, $id)
{
    //return $id;die();
    try {
        $fundRequest = DB::table('rem_fundrequest')->where('id', $id)->first();

        if (!$fundRequest) {
            return redirect()->back()->withErrors(['msg' => 'Fund request not found.']);
        }

        if ($fundRequest->status != 0) {
            return redirect()->back()->withErrors(['msg' => 'This fund request has already been processed.']);
        }

        // Update the fund request status to rejected (2)
        DB::table('rem_fundrequest')
            ->where('id', $id)
            ->update([
                'status' => -1,
                'updated_at' => now(),
                'admin_remark'=>$request->remark
            ]);

        return redirect()->back()->with('success', 'Fund request rejected successfully.');

    } catch (\Exception $e) {
        Log::error('Reject Fund Request Error: ' . $e->getMessage());
        //return $e;die();
        return redirect()->back()->withErrors(['msg' => 'An error occurred while processing your request. Please try again later.']);
    }

}

    public function loadFund()
    {
          $bankDetails = DB::table('banks')->where('status', 'active')->orderBy('created_at', 'ASC')->get();
        return view('risefund',compact('bankDetails'));
    }

    public function loadFundStore(Request $request)
    {
        //return $request;die();

        $request->validate([
        'bank'          => ['nullable'],
        'ifsc'          => ['nullable','string','max:20'],
        'account_no'    => ['nullable','string','max:30'],
        'amount'        => ['nullable','numeric','min:100'],
        'manual_bank_name'=>['nullable'],
        'manual_account_no'=>['nullable'],
        'manual_ifsc'=>['nullable'],
        'manual_account_holder'=>['nullable'],
        'utr'           => ['required','string','max:255'],
        'mode'          => ['required','string','in:IMPS,NEFT,UPI,CASH'],
        'date'          => ['required','date','before_or_equal:today'],
        'slip_image'    => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:2048'],
    ]);

    try {
        $proofPath = null;

        // ✅ Upload to S3 if file exists
        if ($request->hasFile('slip_image')) {
            $storedPath = $request->file('slip_image')->store('fund_proofs', 's3');

            if ($storedPath && is_string($storedPath)) {
                $proofPath = Storage::disk('s3')->url($storedPath); // Public URL
            } else {
                \Log::warning('S3 store() returned empty/false for slip_image upload');
                $proofPath = null;
            }
        }

        DB::table('adm_fundrequest')->insert([
            'bank_id'        =>  1,
            'amount'         => $request->amount,
            'utr'            => $request->utr,
            'ifsc'           => $request->ifsc ?? $request->manual_ifsc,
            'account_no'     => $request->account_no ?? $request->manual_account_no,
            'date'           => $request->date,
            'mode'           => $request->mode,
            'remark'         => $request->remark,
            'rid'            => Auth::guard('remittance')->user()->remId ?? 1,
            'request_by'     => Auth::guard('remittance')->user()->name ?? 'COdeGra[hi',
            'phone'          => Auth::guard('remittance')->user()->phone ?? '9876543210',
            'slip_images'    => $proofPath ? json_encode([$proofPath]) : null,
            // single image now, convert to JSON if multiple
            'status'         => 0,
            'employeeId'     => auth()->id(),
            'openingBalance' => Auth::user()->balance ?? 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('success', '✅ Fund request submitted successfully!');

    } catch (\Exception $e) {
        \Log::error('Add Fund Error: '.$e->getMessage());
        return back()->with('error', '❌ Something went wrong. Please try again later.'.$e);
    }
    }

}
