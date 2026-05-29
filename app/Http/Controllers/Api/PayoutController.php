<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayoutService;
use Illuminate\Support\Facades\Validator;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Helpers\InstantPayHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PayoutController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }

    public function localAuth($cgapi)
    {
        $storedCgapi = DB::table('remittances')->where('apikey',$cgapi)->value('apikey');
        $remittance = DB::table('remittances')->where('apikey',$cgapi)->first();
        if (!$storedCgapi || $cgapi !== $storedCgapi) {
            // Return JSON error response if no match
            response()->json([
                'status' => false,
                'message' => 'Unauthorized or invalid api token.'
            ], 401)->send();
            exit;
        }

        // If match, return true or proceed silently
        return $remittance;
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE CONTACT
    |--------------------------------------------------------------------------
    */

public function createContact(Request $request)
{
    $request->validate([
        'apikey'         => 'required',
        'userId'         => 'required',
        'first_name'     => 'required',
        'last_name'      => 'nullable',
        'email'          => 'required|email',
        'mobile'         => 'required',
        'account_number' => 'required',
        'ifsc'           => 'required',
    ]);
    $validator = Validator::make($request->all(), [

    'apikey' => 'required',
    'userId' => 'required',

    'first_name' => 'required|string|max:100',

    'last_name' => 'nullable|string|max:100',

    'email' => 'required|email|max:255',

    'mobile' => 'required|digits_between:10,15',

    'account_number' => 'required|min:6|max:30',

    'ifsc' => 'required|string|size:11',

], [

    'apikey.required' => 'API Key is required.',
    'userId.required' => 'UserId  is required.',

    'first_name.required' => 'First Name is required.',

    'email.required' => 'Email is required.',

    'email.email' => 'Please enter a valid email address.',

    'mobile.required' => 'Mobile Number is required.',

    'mobile.digits_between' => 'Mobile Number must be between 10 to 15 digits.',

    'account_number.required' => 'Account Number is required.',

    'ifsc.required' => 'IFSC Code is required.',

    'ifsc.size' => 'IFSC Code must be 11 characters.'
]);

if ($validator->fails()) {

    return response()->json([

        'status' => false,

        'message' => 'Validation failed.',

        'errors' => $validator->errors(),

        'error_list' => $validator->errors()->all()

    ], 422);
}

    /*
    |--------------------------------------------------------------------------
    | AUTH CHECK
    |--------------------------------------------------------------------------
    */

    $remittance = $this->localAuth($request->apikey);

    if (!$remittance) {

        return response()->json([
            'status'  => false,
            'message' => 'Unauthorized. Invalid API key.'
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | REQUEST LOG
    |--------------------------------------------------------------------------
    */

    Log::channel('fundtransfer')->info(
        'Create Contact Request',
        [
            'ip'      => $request->ip(),
            'remId'   => $remittance->remId,
            'payload' => $request->all()
        ]
    );

    /*
    |--------------------------------------------------------------------------
    | IP WHITELIST CHECK
    |--------------------------------------------------------------------------
    */

    $clientIp = trim($request->ip());

    $whitelistedIps = DB::table('remittances')
        ->where('remId', $remittance->remId)
        ->pluck('ipAddress')
        ->map(fn ($ip) => trim($ip))
        ->toArray();

    if (!in_array($clientIp, $whitelistedIps)) {

        Log::warning(
            "IP BLOCKED",
            [
                'ip'    => $clientIp,
                'remId' => $remittance->remId
            ]
        );

        return response()->json([
            'status'  => false,
            'message' => "Access denied. Your IP ({$clientIp}) is not whitelisted."
        ], 403);
    }

    /*
    |--------------------------------------------------------------------------
    | DUPLICATE CONTACT CHECK
    |--------------------------------------------------------------------------
    */

    $existingContact = DB::table('payout_contacts')
        ->where('remId', $remittance->remId)
        ->where('account_number', $request->account_number)
        ->first();

    if ($existingContact) {

        return response()->json([
            'status'  => true,
            'message' => 'Contact already exists.',
            'data'    => [
                'contactId' => $existingContact->contact_id
            ]
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | RAW REQUEST
    |--------------------------------------------------------------------------
    */

    $rawPayload = [
        'firstName'     => $request->first_name,
        'lastName'      => $request->last_name,
        'email'         => $request->email,
        'mobile'        => $request->mobile,
        'accountNumber' => $request->account_number,
        'ifsc'          => $request->ifsc,
    ];

    try {

        /*
        |--------------------------------------------------------------------------
        | API HIT
        |--------------------------------------------------------------------------
        */

        $response = $this->payoutService->createContact($request);

        Log::channel('fundtransfer')->info(
            'Create Contact Response',
            [
                'remId'    => $remittance->remId,
                'response' => $response
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (
            isset($response['status']) &&
            $response['status'] == 'SUCCESS'
        ) {

            DB::table('payout_contacts')->insert([
                'remId'          => $remittance->remId,
                'userId'          => $request->userId,
                'contact_id'     => $response['data']['contactId'] ?? null,
                'first_name'     => $response['data']['firstName'] ?? null,
                'last_name'      => $response['data']['lastName'] ?? null,
                'email'          => $response['data']['email'] ?? null,
                'mobile'         => $response['data']['mobile'] ?? null,
                'account_number' => $response['data']['accountNumber'] ?? null,
                'ifsc'           => $response['data']['accountIFSC'] ?? null,
                'status'         => $response['status'],
                'requestBody'    => json_encode($rawPayload),
                'responseBody'   => json_encode($response),
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }

        return response()->json($response);

    } catch (\Exception $e) {

        Log::error(
            'Create Contact Exception',
            [
                'remId' => $remittance->remId,
                'error' => $e->getMessage()
            ]
        );

        return response()->json([
            'status'  => false,
            'message' => $e->getMessage()
        ], 500);
    }
}
    /*
    |--------------------------------------------------------------------------
    | GET CONTACT
    |--------------------------------------------------------------------------
    */

    public function getContact($contactId)
    {
        return response()->json(
            $this->payoutService->getContact($contactId)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

   /*
|--------------------------------------------------------------------------
| CREATE ORDER
|--------------------------------------------------------------------------
*/

public function createOrder(Request $request)
{
    try {

        /*
        |--------------------------------------------------------------------------
        | AUTH CHECK
        |--------------------------------------------------------------------------
        */

        $remittance = $this->localAuth($request->input('apikey'));

        if (!$remittance) {

            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized. Invalid API key.'
            ], 401);
        }

        /*
        |--------------------------------------------------------------------------
        | LOG REQUEST
        |--------------------------------------------------------------------------
        */

        Log::channel('fundtransfer')->info(
            "Fund Transfer Request",
            [
                'ip'      => $request->ip(),
                'payload' => $request->all()
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | IP WHITELIST CHECK
        |--------------------------------------------------------------------------
        */

        $clientIp = $request->ip();

        $whitelistedIps = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->pluck('ipAddress')
            ->toArray();

        if (!in_array($clientIp, $whitelistedIps)) {

            Log::warning(
                "IP BLOCKED: {$clientIp} tried payout for remId {$remittance->remId}"
            );

            return response()->json([
                'status'  => false,
                'message' => "Access denied. Your IP ($clientIp) is not whitelisted."
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | SERVICE CHECK
        |--------------------------------------------------------------------------
        */

        $service = DB::table('apis')
            ->where('name', 'PAYOUT')
            ->first();

        if (!$service || $service->status != 1) {

            return response()->json([
                'status'  => false,
                'message' => $service->message ?? 'Service is currently inactive'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | VALIDATION
        |--------------------------------------------------------------------------
        */

        $validator = Validator::make($request->all(), [

            'mobileNo'          => 'required|string|max:15',
            'txnAmount'         => 'required|numeric|min:100',
            'contact_id' =>          'required',
            'RefNo'             => 'required|string|max:50',

        ]);

        if ($validator->fails()) {

            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        /*
        |--------------------------------------------------------------------------
        | DUPLICATE REF CHECK
        |--------------------------------------------------------------------------
        */

        $existingTxn = DB::table('xpresspayout')
            ->where('remId', $remittance->remId)
            ->where('refId', $request->RefNo)
            ->first();

        if ($existingTxn) {

            return response()->json([
                'status'  => false,
                'message' => 'Duplicate RefNo. Transaction already exists.'
            ], 409);
        }

        /*
        |--------------------------------------------------------------------------
        | ADMIN BALANCE CHECK
        |--------------------------------------------------------------------------
        */

        $adminBalance = DB::table('users')
            ->where('id', 1)
            ->first();

        if ($adminBalance && $adminBalance->balance < $request->txnAmount) {

            return response()->json([
                'status'  => false,
                'message' => 'Please contact Admin.',
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | KYC CHECK
        |--------------------------------------------------------------------------
        */

        if ($remittance->isKyc != 1 || $remittance->status != 'success') {

            return response()->json([
                'status'  => false,
                'message' => 'KYC not verified or payout not allowed.',
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | PAYOUT ENABLE CHECK
        |--------------------------------------------------------------------------
        */

        if ($remittance->payout1 != 1) {

            return response()->json([
                'status'  => false,
                'message' => 'Payout service not enabled for your account.'
            ], 403);
        }

        /*
        |--------------------------------------------------------------------------
        | PACKAGE CHECK
        |--------------------------------------------------------------------------
        */

        if ($remittance->packageId == 0) {

            return response()->json([
                'status'  => false,
                'message' => 'No Package Assigned.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | CALLBACK CHECK
        |--------------------------------------------------------------------------
        */

        if ($remittance->callback_url == null) {

            return response()->json([
                'status'  => false,
                'message' => 'Callback URL not setup.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | PACKAGE ACTIVE CHECK
        |--------------------------------------------------------------------------
        */

        $package = DB::table('packages')
            ->where('id', $remittance->packageId)
            ->first();

        if (!$package || $package->status != 1) {

            return response()->json([
                'status'  => false,
                'message' => 'Assigned Package Inactive.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | WALLET CHECK
        |--------------------------------------------------------------------------
        */

        $walletAmount = DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email)
            ->value('amount');

        if (!$walletAmount || $walletAmount < $request->txnAmount) {

            return response()->json([
                'status'  => false,
                'message' => 'Insufficient wallet balance.'
            ], 400);
        }

        $openingBal = $walletAmount;

        $amount = $request->txnAmount;

        /*
        |--------------------------------------------------------------------------
        | COMMISSION
        |--------------------------------------------------------------------------
        */

        $commissions = DB::table('commissions')
            ->where('packagesId', $remittance->packageId)
            ->where('service', 'PAYOUT')
            ->get() ?? [];

        if ($commissions->isEmpty()) {

            return response()->json([
                'status'  => false,
                'message' => 'No commission structure found.'
            ], 400);
        }

        $charges = 0;
        $tds     = 0;

        foreach ($commissions as $item) {

            $from = (float) $item->from_amount;

            $to   = (float) $item->to_amount;

            if (
                $item->service === 'PAYOUT' &&
                $amount >= $from &&
                $amount <= $to
            ) {

                $charges = $item->charge_in === 'Percentage'
                    ? $amount * ((float) $item->charge) / 100
                    : (float) $item->charge;

                $tds = $item->tds_in === 'Percentage'
                    ? $charges * ((float) $item->tds) / 100
                    : (float) $item->tds;

                break;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK CHARGE
        |--------------------------------------------------------------------------
        */

        if ($charges == 0 && $amount >= 100) {

            $charges = $amount * 0.01;

            $tds = $charges * 0.18;
        }

        $totalDeduct = $amount + $charges + $tds;

        $closingBal = $walletAmount - $totalDeduct;

        if ($closingBal < 0) {

            return response()->json([
                'status'  => false,
                'message' => 'Insufficient balance after charges.'
            ], 400);
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT ID
        |--------------------------------------------------------------------------
        */

        $paymentId = 'HCHET' . strtoupper(Str::random(10));

        $rawPayload = $request->all();

        /*
        |--------------------------------------------------------------------------
        | INSERT PAYOUT
        |--------------------------------------------------------------------------
        */

        DB::table('xpresspayout')->insert([
            'remId'            => $remittance->remId,
            'email'            => $remittance->email,
            'payment_id'       => $paymentId,
            'amount'           => $amount,
            'charge'           => $charges,
            'tds'              => $tds,
            'status'           => 'Initiated',
            'opening_balance'  => $openingBal,
            'closing_balance'  => $closingBal,
            'bank_name'        => $request->bankName ?? '',
            'ifsc_code'        => $request->ifscCode ?? '',
            'acc_no'           => $request->contact_id ?? '',
            'beneficiary_name' => $request->accountHolderName ?? '',
            'refId'            => $request->RefNo,
            'requestBody'      => json_encode($rawPayload),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | WALLET DEDUCT
        |--------------------------------------------------------------------------
        */

        DB::table('remittances')
            ->where('remId', $remittance->remId)
            ->where('email', $remittance->email)
            ->update([
                'amount' => $closingBal
            ]);

      
        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER PAYLOAD
        |--------------------------------------------------------------------------
        */

        $contactRequest = new Request([
            'contactId'     => $request->contact_id,
            'amount'      =>  $request->txnAmount,
            'mode'          => 'IMPS',
            'clientRefId'         => $paymentId,
            
        ]);
       // return $contactRequest;

        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER API
        |--------------------------------------------------------------------------
        */

        $bankResponse = $this->payoutService
            ->createOrder($contactRequest);

            return $bankResponse;

        Log::channel('fundtransfer')->info(
            "Payout Response Received",
            [
                'ip'       => $request->ip(),
                'response' => $bankResponse
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | SUCCESS
        |--------------------------------------------------------------------------
        */

        if (
            isset($bankResponse['status']) &&
            $bankResponse['status'] == 'SUCCESS'
        ) {

            $providerStatus = strtolower(
                $bankResponse['data']['status'] ?? 'queued'
            );

            $finalStatus = 'Pending';

            if ($providerStatus == 'success') {

                $finalStatus = 'Success';
            }
            elseif ($providerStatus == 'failed') {

                $finalStatus = 'Failed';
            }
            elseif ($providerStatus == 'queued') {

                $finalStatus = 'Pending';
            }

            DB::table('xpresspayout')
                ->where('refId', $request->RefNo)
                ->update([

                    'bank_ref_no' =>
                        $bankResponse['data']['orderRefId'] ?? null,

                    'orderId' =>
                        $bankResponse['data']['clientRefId'] ?? null,

                    'status' =>
                        $finalStatus,

                    'responseBody' =>
                        json_encode($bankResponse),

                    'updated_at' => now(),
                ]);

            DB::table('users')
                ->where('id', 1)
                ->decrement('balance', $amount);

        } else {

            /*
            |--------------------------------------------------------------------------
            | FAILED
            |--------------------------------------------------------------------------
            */

            DB::table('remittances')
                ->where('remId', $remittance->remId)
                ->increment('amount', $totalDeduct);

            DB::table('xpresspayout')
                ->where('refId', $request->RefNo)
                ->update([

                    'status'       => 'Failed',

                    'responseBody' => json_encode($bankResponse),

                    'updated_at'   => now(),
                ]);

            return response()->json([

                'status'  => false,

                'message' =>
                    $bankResponse['message']
                    ?? 'Payout failed.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | FINAL RESPONSE
        |--------------------------------------------------------------------------
        */

        return response()->json([

            "status"   => true,

            "message"  =>
                $bankResponse['message']
                ?? "Payout initiated.",

            'remId'            => $remittance->remId,

            'email'            => $remittance->email,

            'payment_id'       => $paymentId,

            'utr'              =>
                $bankResponse['data']['orderRefId'] ?? null,

            'amount'           => $amount,

            'charge'           => $charges,

            'gst'              => $tds,

            'provider_status'  =>
                $bankResponse['data']['status'] ?? 'queued',

            'opening_balance'  => $openingBal,

            'closing_balance'  => $closingBal,

            'bank_name'        => $request->bankName,

            'ifsc_code'        => $request->ifscCode,

            'acc_no'           => $request->accountNo,

            'beneficiary_name' => $request->accountHolderName,

            'refId'            => $request->RefNo,

            'created_at'       => now(),

            'updated_at'       => now(),
        ]);

    } catch (\Exception $e) {

        return $e;
        Log::error(
            "Payout Error: " . $e->getMessage()
        );

        return response()->json([

            "status"  => false,

            "message" => "Unexpected server error",

            "error"   => $e->getMessage()

        ], 500);
    }
}


public function getContacts(Request $request)
{
    $validator = Validator::make($request->all(), [

        'apikey' => 'required',

        'userId' => 'required',

    ]);

    if ($validator->fails()) {

        return response()->json([

            'status' => false,

            'message' => 'Validation failed.',

            'errors' => $validator->errors()

        ], 422);
    }

    /*
    |--------------------------------------------------------------------------
    | AUTH CHECK
    |--------------------------------------------------------------------------
    */

    $remittance = $this->localAuth($request->apikey);

    if (!$remittance) {

        return response()->json([
            'status'  => false,
            'message' => 'Unauthorized. Invalid API key.'
        ], 401);
    }

    /*
    |--------------------------------------------------------------------------
    | CONTACT LIST
    |--------------------------------------------------------------------------
    */

    $contacts = DB::table('payout_contacts')
    ->where('remId', $remittance->remId)
    ->where('userId', $request->userId)
    ->orderBy('id', 'desc')
    ->get()
    ->map(function ($item) {

        unset(
            $item->requestBody,
            $item->responseBody
        );

        return $item;
    });

    if ($contacts->isEmpty()) {

        return response()->json([
            'status' => false,
            'message' => 'No contacts found.'
        ], 404);
    }

    return response()->json([

        'status' => true,

        'message' => 'Contacts fetched successfully.',

        'total_contacts' => $contacts->count(),

        'data' => $contacts

    ]);
}
}
