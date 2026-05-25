<?php

// namespace App\Http\Controllers;

// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Password;

// class UserForgotPasswordController extends Controller
// {
//     /**
//      * Show the forgot password form for users.
//      */
//     public function showForgotForm()
//     {
//         return view('auth.user-forgot-password');
//     }

//     /**
//      * Handle sending password reset link.
//      */
//     public function sendResetLink(Request $request)
//     {
//         $request->validate([
//             'email' => 'required|email|exists:users,email',
//         ]);

//         $status = Password::sendResetLink(
//             $request->only('email')
//         );

//         return $status === Password::RESET_LINK_SENT
//             ? back()->with('status', 'Password reset link has been sent to your email.')
//             : back()->withErrors(['email' => 'Unable to send reset link. Please try again.']);
//     }
// }









namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\Remittance;

class UserForgotPasswordController extends Controller
{
    // Show forgot password form
    public function showForm()
    {
        return view('auth.user-forgot-password'); // Blade file
    }

    // Handle forgot password request
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:remittances,email',
        ]);

        $email = strtolower(trim($request->email));

        $user = Remittance::where('email', $email)->first();

        // Generate temporary token (store in DB or use as query param)
        $token = Str::random(64);
        $resetLink = url('/user/reset-password/'.$token.'?email='.$user->email);

        // Send email
        Mail::send('emails.user-forgot-password', ['url' => $resetLink, 'user' => $user], function($message) use ($user) {
            $message->to($user->email)
                    ->subject('Reset Your Credx Pay Password');
        });

        return back()->with('status', 'We have emailed your password reset link!');
    }
}
