<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
class AuthController extends Controller
{
    // Show login form
    public function loginForm() {
        return view('auth.login');
    }

    // Handle login
    // public function login(Request $request) {
    //     $credentials = $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);

    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();
    //         return redirect()->route('dashboard');
    //     }

    //     return back()->withErrors(['email' => 'Invalid credentials.']);
    // }



public function login(Request $request)
{
    $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    if (Auth::validate($credentials)) {

        $user = \App\Models\User::where('email', $request->email)->first();

        // 🔐 Generate OTP
        $otp = rand(100000, 999999);
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        // ✅ Session save
        session(['admin_otp_user_id' => $user->id]);

        // 🌐 Get IP
        $ip = $request->ip();
        //$ip="147.93.100.129";

        // 💻 Get Device / Browser
        $device = $request->header('User-Agent');

        // 📍 Get Location from IP
        $location = "Unknown Location";
        try {
            $response = file_get_contents("http://ip-api.com/json/{$ip}");
            $data = json_decode($response);

            if ($data && $data->status == 'success') {
                $location = $data->city . ', ' . $data->regionName . ', ' . $data->country;
            }
        } catch (\Exception $e) {
            // fallback
            $location = "Location not found";
        }

        // dd($ip, $device);
        // die();
        // 📧 Send Mail with extra data
        Mail::send('emails.remittance_otp', [
            'otp' => $otp,
            'name' => $user->name,
            'ip' => $ip,
            'device' => $device,
            'location' => $location
        ], function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Admin Login OTP - Dashboard');
        });

        return redirect()->route('admin.verifyOtpForm')
            ->with('success', 'OTP sent to your email. Please verify.');
    }

    return back()->withErrors(['email' => 'Invalid credentials.']);
}

//     public function login(Request $request)
// {
//     $credentials = $request->validate([
//         'email' => 'required|email',
//         'password' => 'required'
//     ]);

//     // ✅ First validate credentials (without full login)
//     if (Auth::validate($credentials)) {
//         $user = \App\Models\User::where('email', $request->email)->first();

//         // Generate OTP
//         $otp = rand(100000, 999999);
//         $user->otp_code = $otp;
//         $user->otp_expires_at = now()->addMinutes(5);
//         $user->save();

//         // Save ID in session
//         session(['admin_otp_user_id' => $user->id]);

//         // Send OTP mail
//         // \Mail::send('emails.admin_otp', [
//         //     'otp' => $otp,
//         //     'name' => $user->name,
//         // ], function ($message) use ($user) {
//         //     $message->to($user->email)
//         //             ->subject('Admin Login OTP - Dashboard');
//         // });

//          Mail::send('emails.remittance_otp', [
//         'otp' => $otp,
//         'name' => $user->name, // optional
//     ], function ($message) use ($user) {
//         $message->to($user->email)
//                 ->subject('Admin Login OTP - Dashboard');
//     });
        

//         return redirect()->route('admin.verifyOtpForm')
//             ->with('success', 'OTP sent to your email. Please verify.');
//     }

//     return back()->withErrors(['email' => 'Invalid credentials.']);
// }

public function showOtpForm(Request $request)
{
    return view ('verify-otp');
}
public function verifyOtp(Request $request)
{
    $request->validate(['otp' => 'required|numeric']);

    $userId = session('admin_otp_user_id');
    $user = \App\Models\User::find($userId);

    if ($user && $user->otp_code == $request->otp && $user->otp_expires_at > now()) {
        // Clear OTP
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // ✅ Actual login
        Auth::login($user);

        // Remove session key
        session()->forget('admin_otp_user_id');

        return redirect()->route('admin.dashboard')->with('success', 'Welcome Admin 🎉');
    }
 return redirect()->route('admin.verifyOtpForm')
            ->with('error', 'Invalid credentials. Please check your OTP and try again.');
    //return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
}


    // Show forgot form
    public function forgotForm() {
        return view('auth.forgot');
    }

    // Send reset link
    public function sendResetLink(Request $request) {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }

    // Logout
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login.form');
    }
public function dashboard()
{
    $txnValue = DB::table('xpresspayout')->where('remId',Auth::guard('remittance')->user()->remId)->get();

    // ✅ Overall Status-wise
    $pendingCount = $txnValue->where('status', 'Initiated')->count();
    $pendingAmount = $txnValue->where('status', 'Initiated')->sum('amount');

    $successCount = $txnValue->where('status', 'SUCCESS')->count();
    $successAmount = $txnValue->where('status', 'SUCCESS')->sum('amount');

    $failedCount = $txnValue->where('status', 'FAILED')->count();
    $failedAmount = $txnValue->where('status', 'FAILED')->sum('amount');

    // ✅ Today Txns
    $todayTxns = $txnValue->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()]);
    $today = [
        'count'   => $todayTxns->count(),
        'amount'  => $todayTxns->sum('amount'),
        'success' => [
            'count'  => $todayTxns->where('status', 'SUCCESS')->count(),
            'amount' => $todayTxns->where('status', 'SUCCESS')->sum('amount'),
        ],
        'pending' => [
            'count'  => $todayTxns->where('status', 'Initiated')->count(),
            'amount' => $todayTxns->where('status', 'Initiated')->sum('amount'),
        ],
        'failed' => [
            'count'  => $todayTxns->where('status', 'FAILED')->count(),
            'amount' => $todayTxns->where('status', 'FAILED')->sum('amount'),
        ],
    ];

    // ✅ This Month Txns
    $monthTxns = $txnValue->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
    $month = [
        'count'   => $monthTxns->count(),
        'amount'  => $monthTxns->sum('amount'),
        'success' => [
            'count'  => $monthTxns->where('status', 'SUCCESS')->count(),
            'amount' => $monthTxns->where('status', 'SUCCESS')->sum('amount'),
        ],
        'pending' => [
            'count'  => $monthTxns->where('status', 'Initiated')->count(),
            'amount' => $monthTxns->where('status', 'Initiated')->sum('amount'),
        ],
        'failed' => [
            'count'  => $monthTxns->where('status', 'FAILED')->count(),
            'amount' => $monthTxns->where('status', 'FAILED')->sum('amount'),
        ],
    ];

    // ✅ Totals
    $total = [
        'count'  => $txnValue->count(),
        'amount' => $txnValue->sum('amount'),
    ];

    // ✅ Prepare Data
    $data = [
        'pending' => [
            'count' => $pendingCount,
            'amount' => $pendingAmount,
        ],
        'success' => [
            'count' => $successCount,
            'amount' => $successAmount,
        ],
        'failed' => [
            'count' => $failedCount,
            'amount' => $failedAmount,
        ],
        'total_today' => $today,
        'total_month' => $month,
        'total' => $total,
      'txnvalue' => $txnValue->sortByDesc('created_at')->take(5),

    ];

    //return $data;die();

    // ✅ Send data to view
    return view('users.dashboard', [
        'summary' => $data
    ]);
}

}
