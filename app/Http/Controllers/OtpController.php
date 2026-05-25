<?php

namespace App\Http\Controllers;

use App\Models\Remittance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    // Test OTP page without login
    public function test()
    {
        return view('users.remittances.otp');
    }

      public function verify(Request $request)
    {
        //return $request->all();die();
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $userId = session('pending_user');

        if (!$userId) {
            return redirect()->route('remittances.login')
                ->with('error', 'Session expired. Please login again.');
        }

        if ($request->otp === '999999') {

             return redirect()->route('remittances.dashboard')
            ->with('success', 'Login successful!');
            
        }

        else
        {
return redirect()->route('user.remittances.otp')
                ->with('error', 'Invalid OTP. Please try again.');
        }
        // OTP correct → login the user
        // $user = Remittance::find($userId);
        // Auth::guard('remittance')->login($user, session('remember_me', false));

        // Clear OTP session
        session()->forget(['pending_user', 'remember_me']);

        // return redirect()->route('remittances.dashboard')
        //     ->with('success', 'Login successful!');
    }

    // Resend OTP (always 999999)
    public function resend()
    {
        $userId = session('pending_user');

        if (!$userId) {
            return redirect()->route('remittances.login')
                ->with('error', 'Session expired. Please login again.');
        }

        // Log OTP for testing purposes
        logger()->info("Resent OTP for user {$userId}: 999999");

        return redirect()->route('user.remittances.otp')
            ->with('success', 'OTP resent successfully. Use 999999.');
    }
}
