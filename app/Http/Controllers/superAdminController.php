<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuperAdmin; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
class superAdminController extends Controller
{
     public function loginForm() {
        return view('superadmin.auth.login');
    }


     public function login(Request $request)
    {
       // return $request;die();
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // find super admin
        $superAdmin = SuperAdmin::where('email', $request->email)->first();

        if ($superAdmin && Hash::check($request->password, $superAdmin->password)) {
            // store in session
            session(['superadmin_id' => $superAdmin->id]);
            return redirect()->route('superadmin.dashboard')->with('success', 'Welcome, '.$superAdmin->name);
        }

        return back()->withErrors(['email' => 'Invalid email or password.']);
    }

    // 🔹 Dashboard (protected page)
    public function dashboard()
    {
        if (!session('superadmin_id')) {
            return redirect()->route('superadmin.login.form')->withErrors(['auth' => 'Please login first']);
        }

        $superAdmin = SuperAdmin::find(session('superadmin_id'));
        return view('superadmin.dashboard', compact('superAdmin'));
    }

    // 🔹 Logout
    public function logout()
    {
        session()->forget('superadmin_id');
        return redirect()->route('superadmin.login.form')->with('success', 'Logged out successfully');
    }

     public function fundRequest()
{
     //$rid = auth('remittance')->user()->remId;
     //return $rid;die();
    $fundRequests = DB::table('adm_fundrequest')
        ->get();
//return $fundRequests;die();
    return view('superadmin.fundReq', compact('fundRequests'));
}

    public function fundRequestAccept(Request $request, $id)
{
   //return $request;die();
     //return $id;die();
   try {
        $fundRequest = DB::table('adm_fundrequest')->where('id', $id)->first();
        $remitterWallet = DB::table('users')->where('id', $fundRequest->rid)->first();
        $closingBalance = $remitterWallet->balance + $fundRequest->amount;
      //  return $closingBalance;die();
        if (!$fundRequest) {
            return redirect()->back()->withErrors(['msg' => 'Fund request not found.']);
        }

        if ($fundRequest->status != 0) {
            return redirect()->back()->withErrors(['msg' => 'This fund request has already been processed.']);
        }

        // Update the fund request status to accepted (1)
             // 1. Update fund request status
    try {
        DB::table('adm_fundrequest')
            ->where('id', $id)
            ->update([
                'status' => 1,
                'updated_at' => now(),
                'openingBalance' => $remitterWallet->balance,
                'closingBalance' => $closingBalance
            ]);
    } catch (\Exception $e) {
        Log::error("Error updating fund request status: " . $e->getMessage());
        return $e;die();
        return redirect()->back()->withErrors(['msg' => 'Failed to update fund request status.']);
    }

    // 3. Increment remitter wallet
    try {
        DB::table('users')
            ->where('id', $fundRequest->rid)
            ->increment('balance', $fundRequest->amount);
    } catch (\Exception $e) {
        Log::error("Error incrementing reseller wallet: " . $e->getMessage());
        return $e;die();
        return redirect()->back()->withErrors(['msg' => 'Failed to increment reseller wallet.']);
    }



        // Optionally, you can add logic here to update the reseller's balance or notify them

        return redirect()->back()->with('success', 'Fund request accepted successfully.');

    } catch (\Exception $e) {
        Log::error('Accept Fund Request Error: ' . $e->getMessage());
        return $e;die();
        return redirect()->back()->withErrors(['msg' => 'An error occurred while processing your request. Please try again later.']);
    }  
}

public function fundRequestReject(Request $request, $id)
{
    //return $id;die();
    try {
        $fundRequest = DB::table('adm_fundrequest')->where('id', $id)->first();

        if (!$fundRequest) {
            return redirect()->back()->withErrors(['msg' => 'Fund request not found.']);
        }

        if ($fundRequest->status != 0) {
            return redirect()->back()->withErrors(['msg' => 'This fund request has already been processed.']);
        }

        // Update the fund request status to rejected (2)
        DB::table('adm_fundrequest')
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

 public function allTxn()
    {
        // Get all transactions, latest first
$txn = DB::table('xpresspayout')
    ->orderBy('created_at', 'desc') // latest first
    ->get();


      //  return $txn;die();
        return view('superadmin.txnall',compact('txn'));
    }

    public function allTxnAdmin()
    {
        // Get all transactions, latest first
$txn = DB::table('xpresspayout')
    ->orderBy('created_at', 'desc') // latest first
    ->get();


      //  return $txn;die();
        return view('txnall',compact('txn'));
    }

    public function userList()
    {
        $user = DB::table('users')->where('id',1)->first();

        //return $user;die();
        return view('superadmin.userlist', compact('user'));
    }

       public function forgotPasswordForm()
    {
        return view('superadmin.auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        //return $request;die();
        $request->validate([
            'email' => 'required|email|exists:super_admins,email',
        ]);
           $otp = rand(100000, 999999);
       $emailExists = SuperAdmin::where('email', $request->email)->first();
        if (!$emailExists) {
            return back()->withErrors(['email' => 'Email not found in our records.']);
        }   


        $emailPayload = [
            'api_key' => 'codegraphi@qazxcv',
            'to'      => $emailExists->email,
            'subject' => 'Your Login OTP',
            'message' => "Dear {$emailExists->name},\n\nYour OTP for login is: {$otp}\nThis OTP will expire in 10 minutes.\n\nRegards,\nTeam codegraphi"
        ];
        $emailResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://email.codegraphi.in/api/send-email', $emailPayload);
        if (!$emailResponse->successful()) {
            Log::error('Email sending failed: ' . $emailResponse->body());
        }

        // Save data in session for OTP verification
        session([
            'emailA'    => $emailExists->email,
            'otp'       => $otp,
            'mpin'      => $emailExists->mpin,
           
        ]);

        return view('superadmin.auth.otpLogin')
            ->with('message', 'OTP sent to your phone and email.');
    }

    public function verify(Request $request)
    {
        $otp = implode('', $request->otp); 
           // return $otp;die();
        $storedOtp = session('otp');
        $email = session('emailA');

        if ($otp == $storedOtp) {
            // OTP is correct, log the user in
            $superAdmin = SuperAdmin::where('email', $email)->first();
            if ($superAdmin) {
                session(['superadmin_id' => $superAdmin->id]);
                // Clear OTP from session
                session()->forget(['otp', 'emailA']);
                return view('superadmin.auth.resetPass')->with('message', 'OTP verified. You can now reset your MPIN.');
            } else {
                return redirect()->route('superadmin.forgot.form')->withErrors(['email' => 'User not found. Please try again.']);
            }
        } else {
            return back()->withErrors(['otp' => 'Invalid OTP. Please try again.']);
        }
    }


   public function resetPassword(Request $request)
{
    // ✅ Check if session exists
    $superAdminId = session('superadmin_id');
    if (! $superAdminId) {
        return redirect()->route('superadmin.forgot.form')
                         ->withErrors(['email' => 'Unauthorized session. Please start over.']);
    }

    // ✅ Validate password & confirmation
    $request->validate([
        'password' => 'required|min:6|confirmed',
    ]);

    // ✅ Find SuperAdmin
    $superAdmin = SuperAdmin::find($superAdminId);
    if (! $superAdmin) {
        return redirect()->route('superadmin.forgot.form')
                         ->withErrors(['email' => 'User not found. Please try again.']);
    }

    // ✅ Update password (not mpin)
    $superAdmin->password = Hash::make($request->input('password'));
    $superAdmin->save();

    // ✅ Clear session
    session()->forget('superadmin_id');

    // ✅ Redirect with success
    return redirect()->route('superadmin.login.form')
                     ->with('status', 'Password reset successfully! Please login.');
}

}
