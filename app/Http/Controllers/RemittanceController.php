<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Remittance;
use Illuminate\Support\Facades\Storage; // ✅ Add this
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;




use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Aws\S3\Exception\S3Exception;
use Illuminate\Http\UploadedFile;

use Exception;
class RemittanceController extends Controller

{
 
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return view('users.remittances.create');
    }

    public function loginf()
    {
        return view('users.remittances.login');
    }
    public function forgot()
    {
        return view('users.remittances.forgot');
    }


    // new 
    public function start_txn()
    {
        return view('users.start_txn');
    }

    public function add_beneficiary()
    {
        return view('users.add_beneficiary');
    }

    public function certificate()
    {
        return view('users.certificate');
    }


    

    /**
     * Store a newly created resource in storage.
     */
   



public function store(Request $request)
{
    try {
        $data = $request->validate([
            'name'       => ['required','string','max:255'],
            'brand_name' => ['required','string','max:255'],
            'phone'      => ['required','regex:/^[0-9]{10,15}$/','unique:remittances,phone'],
            'email'      => ['required','email','max:255','unique:remittances,email'],
            'gst_pan'    => ['nullable','string','max:50','regex:/^[A-Z0-9]+$/i'],
            'services'   => ['required','string','max:120'],
            'referral'   => ['nullable','string']
        ]);

        $randomUpper = strtoupper(Str::random(2));
        $phoneLast4  = substr($data['phone'], -4);
        $rtid        = "CDXP{$randomUpper}{$phoneLast4}";
        $Password = "CDXP" . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

        Remittance::create([
            'name'       => $data['name'],
            'brand_name' => $data['brand_name'],
            'phone'      => $data['phone'],
            'email'      => $data['email'],
            'remId'      => $rtid,
            'gst_pan'    => $data['gst_pan'] ?? null,
            'services'   => $data['services'],
            'referral'   => $data['referral'] ?? null, // ✅ fixed
            'password'   => Hash::make($Password),
        ]);

        // Email to user
        // $emailPayload1 = [
        //     'api_key' => "codegraphi@qazxcv",
        //     'to'      => $data['email'],
        //     'subject' => 'Welcome to CodeGraphi Xpress Payout Platform',
        //     'message' => "Dear {$data['brand_name']},\n\n"
        //                ."🎉 Your remittance account has been created successfully!\n\n"
        //                ."📌 Account Details:\n"
        //                ."Remittance ID: {$rtid}\n"
        //                ."Registered Email: {$data['email']}\n"
        //                ."Registered Phone: {$data['phone']}\n"
        //                ."Default Password: xclient@123\n\n"
        //                ."Regards,\nTeam CodeGraphi"
        // ];

        // $userEmailResponse = Http::post('https://email.codegraphi.in/api/send-email', $emailPayload1);
        Mail::send('emails.remittance_welcome', [
        'brand_name' => $data['brand_name'],
        'rtid'       => $rtid,
        'email'      => $data['email'],
        'phone'      => $data['phone'],
        'password'  => $Password,
    ], function ($message) use ($data) {
        $message->to($data['email'])
                ->subject('Welcome to Finogic Platform');
    });
                // Email to admin
            $emails = [
            "sarvesh@codegraphi.com",
            "developer@codegraphi.com"
        ];

        foreach ($emails as $email) {
            $emailPayload2 = [
                'api_key' => "codegraphi@qazxcv",
                'to'      => $email,
                'subject' => 'New Remittance Account Created',
                'message' => "A new remittance account has been created:\n\n"
                        ."📌 Account Details:\n"
                        ."Remittance ID: {$rtid}\n"
                        ."Registered Email: {$data['email']}\n"
                        ."Registered Phone: {$data['phone']}\n"
                        ."Default Password: xclient@123\n\n"
                        ."Regards,\nTeam CodeGraphi"
            ];

            $response = Http::post('https://email.codegraphi.in/api/send-email', $emailPayload2);

            if (!$response->successful()) {
                Log::error("Failed to send admin email to {$email}: " . $response->body());
            }
        }

        return redirect()->route('remittances.login')
    ->with('signup_success', 'Your account has been created and credentials are sent to your registered email.');

        if (!$userEmailResponse->successful()) {
            Log::error('User email sending failed: ' . $userEmailResponse->body());
        }

        if (!$adminEmailResponse->successful()) {
            Log::error('Admin email sending failed: ' . $adminEmailResponse->body());
        }

        return redirect()->route('remittances.login')
            ->with('success', 'Your account has been created and credentials are sent to your registered email.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->validator)->withInput();
    } catch (Exception $e) {
        Log::error('Signup Error: '.$e->getMessage());
        return back()->with('error', 'Something went wrong, please try again later.');
    }
}


        
//     public function login(Request $request)
// {
//     //return $request;die();
//     try {
//         // ✅ Validate inputs
//         $request->validate([
//             'login'    => ['required','string'], // can be email or phone
//             'password' => ['required','string'],
//             'remember' => ['nullable','boolean'],
//         ]);

//         // ✅ Detect email vs phone
//         $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

//         $credentials = [
//             $field     => $request->login,
//             'password' => $request->password,
//         ];

//         // ✅ Attempt login
//         if (Auth::guard('remittance')->attempt($credentials, $request->boolean('remember'))) {
//             $request->session()->regenerate();
//             return redirect()->route('remittances.dashboard')->with('success', 'Login successful! 🎉');
//             //return "done ";
//         }

//         // ❌ Wrong credentials
//         return back()
//             ->withErrors(['login' => 'Invalid credentials, please try again.'])
//             ->onlyInput('login');

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         // Laravel validation exception (shows form validation errors)
//         return back()
//             ->withErrors($e->errors())
//             ->withInput();

//     } catch (\Exception $e) {
//         // Any unexpected error
//         Log::error('Login Error: ' . $e->getMessage()); // log for debugging

//         return back()
//             ->withErrors(['login' => 'Something went wrong. Please try again later.'])
//             ->withInput();
//     }
// }


public function login(Request $request)
{
    try {
        // ✅ Validate inputs
        $request->validate([
            'login'    => ['required','string'],
            'password' => ['required','string'],
            'remember' => ['nullable','boolean'],
        ]);

        // ✅ Detect email or phone
        $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $credentials = [
            $field     => $request->login,
            'password' => $request->password,
        ];

        // ✅ Attempt login
        if (Auth::guard('remittance')->attempt($credentials, $request->boolean('remember'))) {

            $user = Auth::guard('remittance')->user();

            // 🔐 Generate OTP
            $otp = rand(100000, 999999);
            $user->otp_code = $otp;
            $user->otp_expires_at = now()->addMinutes(5);
            $user->save();

            // ✅ Save session
            session(['otp_user_id' => $user->id]);

            // 🚫 Logout (OTP verify hone tak login hold)
            Auth::guard('remittance')->logout();

            // ================================
            // 🌐 GET USER INFO
            // ================================

            $ip = $request->ip() ?? 'N/A';
            $device = $request->header('User-Agent') ?? 'Unknown Device';
            $location = "Unknown Location";

            // 🔥 Localhost handling
            if ($ip === '127.0.0.1' || $ip === '::1') {
                $location = "Localhost (Testing Mode)";
            } else {
                try {
                    // ✅ Better than file_get_contents
                    $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                    $data = $response->json();

                    if (isset($data['status']) && $data['status'] === 'success') {
                        $location = $data['city'] . ', ' . $data['regionName'] . ', ' . $data['country'];
                    }
                } catch (\Exception $e) {
                    $location = "Location Fetch Failed";
                }
            }

            // ================================
            // 📧 SEND OTP EMAIL (WITH DETAILS)
            // ================================

            Mail::send('emails.remittance_otp', [
                'otp'      => $otp,
                'name'     => $user->name,
                'ip'       => $ip,
                'device'   => $device,
                'location' => $location
            ], function ($message) use ($user) {
                $message->to($user->email)
                        ->subject('Log in OTP - Finogic Platform');
            });

            return redirect()->route('remittances.verifyOtpForm')
                ->with('success', 'OTP sent to your email. Please verify.');
        }

        // ❌ Wrong credentials
        return back()
            ->withErrors(['login' => 'Invalid credentials, please try again.'])
            ->onlyInput('login');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        Log::error('Login Error: ' . $e->getMessage());
        return back()->withErrors(['login' => 'Something went wrong.'])->withInput();
    }
}

// public function login(Request $request)
// {
//     try {
//         // ✅ Validate inputs
//         $request->validate([
//             'login'    => ['required','string'], // can be email or phone
//             'password' => ['required','string'],
//             'remember' => ['nullable','boolean'],
//         ]);

//         // ✅ Detect email vs phone
//         $field = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

//         $credentials = [
//             $field     => $request->login,
//             'password' => $request->password,
//         ];

//         // ✅ Attempt login
//         if (Auth::guard('remittance')->attempt($credentials, $request->boolean('remember'))) {
//     $user = Auth::guard('remittance')->user();

//     // Generate OTP
//     $otp = rand(100000, 999999);
//     $user->otp_code = $otp;
//     $user->otp_expires_at = now()->addMinutes(5);
//     $user->save();

//     // Save only user ID in session
//     session(['otp_user_id' => $user->id]);

//     // Logout to prevent half login
//     Auth::guard('remittance')->logout();

//     // Send OTP
//     // Mail::raw("Your OTP is: {$otp}", function ($message) use ($user) {
//     //     $message->to($user->email)->subject('Your Login OTP');
//     // });
//      // ✅ Send OTP Email (use $user->email instead of $data)
//     Mail::send('emails.remittance_otp', [
//         'otp' => $otp,
//         'name' => $user->name, // optional
//     ], function ($message) use ($user) {
//         $message->to($user->email)
//                 ->subject('Log in OTP - CredXpay Payout Platform');
//     });

//     return redirect()->route('remittances.verifyOtpForm')
//         ->with('success', 'OTP sent to your email. Please verify.');
// }


//         // ❌ Wrong credentials
//         return back()
//             ->withErrors(['login' => 'Invalid credentials, please try again.'])
//             ->onlyInput('login');

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         return back()->withErrors($e->errors())->withInput();
//     } catch (\Exception $e) {
//         Log::error('Login Error: ' . $e->getMessage());
//        // return back()->withErrors(['login' => 'Something went wrong. Please try again later.'])->withInput();
//     }
// }

public function showOtpForm(Request $request)
{
    return view ('users.remittances.otp');
}

public function verifyOtp(Request $request)
{
    $request->validate(['otp' => 'required|numeric']);

    $userId = session('otp_user_id');
    $user = Remittance::find($userId);

    if ($user && $user->otp_code == $request->otp && $user->otp_expires_at > now()) {
        // Clear OTP
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        // Now login properly
        Auth::guard('remittance')->login($user);

        // Remove session key
        session()->forget('otp_user_id');

        return redirect()->route('remittances.dashboard')
            ->with('success', 'Login successful! 🎉');
    }

     return redirect()->route('remittances.verifyOtpForm')
        ->with('error', 'Invalid or expired OTP');
   // return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
}



    public function addFund()
    {
        $bankDetails = DB::table('banks')->where('status', 'active')->orderBy('created_at', 'ASC')->get();
        //return $bankDetails;
        return view('users.fund.index', compact('bankDetails'));
    }
    public function logout(Request $request)
    {
        Auth::guard('remittance')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('remittances.login')->with('success', 'Logout successful!');
    }

 public function addFundStore(Request $request)
{
    $request->validate([
        'bank'          => ['required','integer','exists:banks,id'],
        'ifsc'          => ['required','string','max:20'],
        'account_no'    => ['required','string','max:30'],
        'amount'        => ['required','numeric','min:100'],
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
                Log::warning('S3 store() returned empty/false for slip_image upload');
                $proofPath = null;
            }
        }

        DB::table('rem_fundrequest')->insert([
            'bank_id'        => $request->bank,
            'amount'         => $request->amount,
            'utr'            => $request->utr,
            'ifsc'           => $request->ifsc,
            'account_no'     => $request->account_no,
            'date'           => $request->date,
            'mode'           => $request->mode,
            'remark'         => $request->remark,
            'rid'            => Auth::guard('remittance')->user()->remId,
            'request_by'     => Auth::guard('remittance')->user()->name,
            'phone'          => Auth::guard('remittance')->user()->phone,
            'slip_images'    => $proofPath ? json_encode([$proofPath]) : null,
            // single image now, convert to JSON if multiple
            'status'         => 0,
            'employeeId'     => auth()->id(),
            'openingBalance' => Auth::guard('remittance')->user()->balance ?? 0,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return back()->with('success', '✅ Fund request submitted successfully!');

    } catch (\Exception $e) {
        Log::error('Add Fund Error: '.$e->getMessage());
        return back()->with('error', '❌ Something went wrong. Please try again later.');
    }
}

public function fundHistory()
{
     $rid = auth('remittance')->user()->remId;
     //return $rid;die();
    $fundRequests = DB::table('rem_fundrequest')
        ->leftJoin('banks', 'rem_fundrequest.bank_id', '=', 'banks.id')
        ->select('rem_fundrequest.*', 'banks.bank_name', 'banks.account_no')
        ->orderBy('rem_fundrequest.created_at', 'DESC')->where('rem_fundrequest.rid', $rid)
        ->get();
//return $fundRequests;die();
    return view('users.fund.history', compact('fundRequests'));
}


public function kycForm()
{
    return view('users.auth.kyc');
}
public function kycStore(Request $request)
{

    // Uppercase convert
    $request->merge([
        'panno'    => strtoupper($request->panno),
        'ifsccode' => strtoupper($request->ifsccode),
    ]);

    // Base Validation
    $validated = $request->validate([

        // Personal
        'fullname'     => ['required','string','max:255'],
        'panno'        => ['required','regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/'],
        'aadhar_no'    => ['required','regex:/^[1-9]\d{11}$/'],
        'pincode'      => ['required','regex:/^\d{6}$/'],
        'city'         => ['required','string','max:255'],

        //website
        'websitelink' => ['required','url','max:255'],

        // Business
        'brandname'        => ['required','string','max:255'],
        'businesstype'     => ['required','string'],
        'businesscategory' => ['required','string'],

        // Office
        'officeaddress' => ['required','string','max:255'],
        'pin'           => ['required','regex:/^\d{6}$/'],

        // Bank
        'accountno' => ['required','regex:/^\d{9,18}$/'],
        'ifsccode'  => ['required','regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'],

        // Docs
        'panupload'    => ['required','file','mimes:jpg,jpeg,png,pdf','max:2048'],
        'aadharupload' => ['required','file','mimes:jpg,jpeg,png,pdf','max:2048'],

        // Optional
        'cancelled_cheque' => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:2048'],
        'bank_passbook'    => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:2048'],
    ]);

    // Any one bank document required
    if(!$request->hasFile('cancelled_cheque') && !$request->hasFile('bank_passbook')){
        return back()->withErrors([
            'bank_doc' => 'Upload Cancelled Cheque OR Bank Passbook'
        ]);
    }

    // Business Type Validation
    $type = $request->businesstype;

    if($type == "private"){
        $request->validate([
            'gst_no' => ['required'],
            'company_pan' => ['required']
        ]);
    }

    if($type == "proprietorship"){
        $request->validate([
            'gst_prop' => ['required']
        ]);
    }

    if($type == "partnership"){
        $request->validate([
            'pan_partner' => ['required']
        ]);
    }

    if($type == "llp"){
        $request->validate([
            'gst_llp' => ['required'],
            'pan_llp' => ['required']
        ]);
    }

    if($type == "individual"){
        $request->validate([
            'udyam_no' => ['required']
        ]);
    }

    try {

        $uploads = [];
        $useS3 = env('S3_UPLOAD');

        // File Upload Helper
        $uploadFile = function($file) use ($useS3){

            if($useS3){

                $path = $file->store('kyc_docs','s3');
                return Storage::disk('s3')->url($path);

            }else{

                $filename = uniqid().'_'.$file->getClientOriginalName();
                $file->move(public_path('kyc_docs'),$filename);

                return asset('kyc_docs/'.$filename);
            }
        };

        // PAN Upload
        if($request->hasFile('panupload')){
            $uploads['pan_doc_url'] = $uploadFile($request->file('panupload'));
        }

        // Aadhaar Upload
        if($request->hasFile('aadharupload')){
            $uploads['aadhaar_doc_url'] = $uploadFile($request->file('aadharupload'));
        }

        // Cancelled Cheque
        if($request->hasFile('cancelled_cheque')){
            $uploads['cancelled_cheque'] = $uploadFile($request->file('cancelled_cheque'));
        }

        // Bank Passbook
        if($request->hasFile('bank_passbook')){
            $uploads['bank_passbook'] = $uploadFile($request->file('bank_passbook'));
        }

        // Business Type Data
        $businessData = [];

        if($type == "private"){
            $businessData = [
                'gst_pan'     => $request->gst_no,
                'businesspan' => $request->company_pan
            ];
        }

        if($type == "proprietorship"){
            $businessData = [
                'gst_pan' => $request->gst_prop
            ];
        }

        if($type == "partnership"){
            $businessData = [
                'businesspan' => $request->pan_partner
            ];
        }

        if($type == "llp"){
            $businessData = [
                'gst_pan'     => $request->gst_llp,
                'businesspan' => $request->pan_llp
            ];
        }

        if($type == "individual"){
            $businessData = [
                'udyam_no' => $request->udyam_no
            ];
        }
        $businessDocs = [];

        // GST Doc
        if($request->hasFile('gst_doc')){
            $businessDocs['gst_doc_url'] = $uploadFile($request->file('gst_doc'));
        }

        // Company PAN Doc
        if($request->hasFile('company_pan_doc')){
            $businessDocs['company_pan_doc'] = $uploadFile($request->file('company_pan_doc'));
        }

        // COI
        if($request->hasFile('coi_doc')){
            $businessDocs['coi_doc'] = $uploadFile($request->file('coi_doc'));
        }

        // MOA
        if($request->hasFile('moa_doc')){
            $businessDocs['moa_doc'] = $uploadFile($request->file('moa_doc'));
        }

        // AOA
        if($request->hasFile('coa_doc')){
            $businessDocs['coa_doc'] = $uploadFile($request->file('coa_doc'));
        }

        // Partnership Deed
        if($request->hasFile('partnership_doc')){
            $businessDocs['partnership_doc'] = $uploadFile($request->file('partnership_doc'));
        }

        // Udyam
        if($request->hasFile('udyam_doc')){
            $businessDocs['udyam_doc'] = $uploadFile($request->file('udyam_doc'));
        }

        // Live Camera Photo (Base64)
            if ($request->live_photo) {

                $image = $request->live_photo;

                // Remove base64 prefix
                $image = str_replace('data:image/png;base64,', '', $image);
                $image = str_replace(' ', '+', $image);

                $imageName = 'live_' . time() . '.png';

                if ($useS3) {

                        $filePath = 'kyc_docs/' . $imageName;

                        Storage::disk('s3')->put(
                            $filePath,
                            base64_decode($image),
                            [
                                'visibility' => 'public',
                                'ContentType' => 'image/png'
                            ]
                        );

                        $uploads['live_photo'] = Storage::disk('s3')->url($filePath);
                    }
                else {

                    // Local fallback
                    $path = public_path('kyc_docs/' . $imageName);
                    file_put_contents($path, base64_decode($image));

                    $uploads['live_photo'] = asset('kyc_docs/' . $imageName);
                }
            }
        // Update KYC
        DB::table('remittances')
        ->where('id',auth('remittance')->id())
        ->update(array_merge([

            // Personal
            'panno'     => $validated['panno'],
            'aadhar_no' => $validated['aadhar_no'],
            'pincode'   => $validated['pincode'],
            'city'      => $validated['city'],

            // Business
            'brand_name'       => $validated['brandname'],
            'businesstype'     => $validated['businesstype'],
            'businesscategory' => $validated['businesscategory'],

            // Office
            'office_address' => $validated['officeaddress'],
            'pin'           => $validated['pin'],

            //web
            'websitelink'   =>$validated['websitelink'],

            // Bank
            'recipient_name'    => $validated['fullname'],
            'recipient_account' => $validated['accountno'],
            'recipient_ifsc'    => $validated['ifsccode'],

            // Limits
            'monthly_limit' => 200000,
            'perday_limit'  => 20000,

            // KYC Status
            'isKyc'  => 1,
            'status' => 'pending',

            // Docs
            'pan_doc_url'       => $uploads['pan_doc_url'] ?? null,
            'aadhaar_doc_url'   => $uploads['aadhaar_doc_url'] ?? null,
            'cancelled_cheque'  => $uploads['cancelled_cheque'] ?? null,
            'bank_passbook'     => $uploads['bank_passbook'] ?? null,
            'live_photo'        => $uploads['live_photo'] ?? null,

            'updated_at' => now()

        ], $businessData,$businessDocs));

        return redirect()
        ->route('remittances.kyc.status',auth('remittance')->id())
        ->with('success','KYC submitted successfully');

    } catch (\Exception $e) {

        Log::error('KYC Error: '.$e->getMessage());
        return $e;
        return back()->with('error','Something went wrong');

    }
}


public function status($id) {
  $remittance = DB::table('remittances')->where('id', $id)->first();
  abort_if(!$remittance, 404);
  return view('users.remittances.kyc-status', compact('remittance'));
}

// public function kycStore(Request $request)
// {
//     return $request;die();
//     // Validation (basic fields + optional docs)
//     $request->validate([
//         'panno'             => ['required', 'string', 'max:255'],
//         'fullname'          => ['required', 'string', 'max:255'],
//         'aadhar_no'         => ['required', 'string', 'max:255'],
//         'pincode'           => ['required', 'string', 'max:20'],
//         'city'              => ['required', 'string', 'max:255'],
//         'recipient_name'    => ['required', 'string', 'max:255'],
//         'recipient_account' => ['required', 'string', 'max:50'],
//         'recipient_ifsc'    => ['required', 'string', 'max:20'],
//         'kyc_verified'      => ['required', 'in:0,1'],

//         'pan_card'      => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:2048'],
//         'aadhar_card'   => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:2048'],
//         'address_proof' => ['nullable','file','mimes:jpg,jpeg,png,pdf','max:2048'],
//     ]);
  
//     try {
//         $panPath = null;
//         $aadharPath = null;
//         $addressPath = null;

//         // Upload PAN
//         if ($request->hasFile('pan_card')) {
//             $storedPan = $request->file('pan_card')->store('kyc_docs', 's3');
//             if ($storedPan) {
//                 $panPath = Storage::disk('s3')->url($storedPan);
//             }
//         }

//         // Upload Aadhaar
//         if ($request->hasFile('aadhar_card')) {
//             $storedAadhar = $request->file('aadhar_card')->store('kyc_docs', 's3');
//             if ($storedAadhar) {
//                 $aadharPath = Storage::disk('s3')->url($storedAadhar);
//             }
//         }

//         // Upload Address Proof if exists
//         if ($request->hasFile('address_proof')) {
//             $storedAddress = $request->file('address_proof')->store('kyc_docs', 's3');
//             if ($storedAddress) {
//                 $addressPath = Storage::disk('s3')->url($storedAddress);
//             }
//         }

//         // Save into DB
//         DB::table('remittances')
//             ->where('id', auth('remittance')->user()->id)
//             ->update([
//                 'panno'             => $request->panno,
//                 'name'          => $request->fullname,
//                 'aadhar_no'         => $request->aadhar_no,
//                 'pincode'           => $request->pincode,
//                 'city'              => $request->city,
//                 'recipient_name'    => $request->recipient_name,
//                 'recipient_account' => $request->recipient_account,
//                 'recipient_ifsc'    => $request->recipient_ifsc,
//                 'monthly_limit'     => 200000,
//                 'perday_limit'     => 20000,
//                 'isKYC'             => $request->kyc_verified,
//                 'updated_at'        => now(),
//             ]);

//         return back()->with('success', '✅ KYC submitted successfully!');

//     } catch (\Exception $e) {
//         Log::error('KYC Upload Error: '.$e->getMessage());
//         return $e;die();
//         //return back()->with('error', '❌ Something went wrong. Please try again later.');
//     }
// }

public function profile()
{
    $user = auth('remittance')->user();
    return view('users.auth.profile', compact('user'));
}

public function charges()
{   
    $packageId = auth('remittance')->user()->packageId;

    // BBPS categories
    $bbps = DB::table('bbps_services')->get()->keyBy('category_code');

    // Fetch commissions
    $commissions = DB::table('commissions')
        ->where('packagesId', $packageId)
        ->get()
        ->map(function($item) use ($bbps) {

            // 👇 check if service exists in bbps
            $serviceName = $bbps[$item->service]->category_name ?? $item->service;

            return [
                'service' => $item->service,
                'service_name' => $serviceName, // 👈 ye print karna hai
                'from_amount' => (float) $item->from_amount,
                'to_amount'   => (float) $item->to_amount,
                'charge'      => (float) $item->charge,
                'charge_type' => strtolower($item->charge_in ?? 'flat'),
                'status'      => $item->status ?? 'active',
            ];
        });

    $slabs = collect($commissions);

    $charges = (object) [
        'min_charge' => $slabs->min('charge'),
        'max_charge' => $slabs->max('charge'),
    ];

    return view('users.auth.charges', compact('slabs', 'charges'));
}

/**
 * Display the specified resource.
 */
public function show(Remittance $remittance)
{
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Remittance $remittance)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Remittance $remittance)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Remittance $remittance)
    {
        //
    }
    public function fetchBalance(Request $request)
{
    $request->validate([
        'email' => 'required|email'
    ]);

    $email = $request->input('email');

    // Fetch account from DB
    $account = Remittance::where('email', $email)->first();

    if (!$account) {
        return response()->json([
            'status' => false,
            'message' => 'Account not found'
        ], 404);
    }

    // Return balance directly from DB
    return response()->json([
        'status' => true,
        'data' => [
            'balance' => $account->amount,
            'currency' => 'INR', // optional
            'updated_at' => $account->updated_at
        ],
        'message' => 'Balance fetched successfully'
    ]);
}
          
}  
   
  
