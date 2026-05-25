<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\Remittance;

class UserResetPasswordController extends Controller
{
    // Show reset password form
    public function showResetForm(Request $request, $token)
    {
        $email = $request->query('email');
        return view('auth.user-reset-password', compact('token', 'email'));
    }

    // Handle reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:remittances,email',
            'password' => 'required|min:6|confirmed',
            'token' => 'required'
        ]);

        $email = strtolower(trim($request->email));
        $user = Remittance::where('email', $email)->first();

        $user->password = Hash::make($request->password);
        $user->save();

        // Send confirmation email
        Mail::send('emails.user-reset-confirm', ['user' => $user], function($message) use ($user) {
            $message->to($user->email)
                    ->subject('Your Credx Pay Password Has Been Reset');
        });

        return redirect()->route('remittances.login')->with('status', 'Your password has been reset successfully!');
    }
}
