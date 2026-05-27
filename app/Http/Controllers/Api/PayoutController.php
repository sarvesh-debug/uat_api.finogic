<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayoutService;

class PayoutController extends Controller
{
    protected $payoutService;

    public function __construct(PayoutService $payoutService)
    {
        $this->payoutService = $payoutService;
    }



    /*
    |--------------------------------------------------------------------------
    | CREATE CONTACT
    |--------------------------------------------------------------------------
    */

    public function createContact(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'nullable',
            'email' => 'required|email',
            'mobile' => 'required',
            'account_number' => 'required',
            'ifsc' => 'required',
        ]);

        return response()->json(
            $this->payoutService->createContact($request)
        );
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
            'contact_id' => 'required',
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
            'acc_no'           => $request->accountNo ?? '',
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
        | CREATE CONTACT
        |--------------------------------------------------------------------------
        */

        $contactRequest = new Request([
            'first_name'     => $request->accountHolderName,
            'last_name'      => '',
            'email'          => $remittance->email,
            'mobile'         => $request->mobileNo,
            'account_number' => $request->accountNo,
            'ifsc'           => $request->ifscCode,
        ]);

        $contactResponse = $this->payoutService
            ->createContact($contactRequest);

        Log::channel('fundtransfer')->info(
            "Contact Response",
            [
                'response' => $contactResponse
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | CONTACT FAILED
        |--------------------------------------------------------------------------
        */

        if (
            !isset($contactResponse['status']) ||
            $contactResponse['status'] != 'SUCCESS'
        ) {

            DB::table('remittances')
                ->where('remId', $remittance->remId)
                ->increment('amount', $totalDeduct);

            DB::table('xpresspayout')
                ->where('refId', $request->RefNo)
                ->update([

                    'status'       => 'Failed',

                    'responseBody' => json_encode($contactResponse),

                    'updated_at'   => now(),
                ]);

            return response()->json([
                'status'  => false,
                'message' => $contactResponse['message']
                    ?? 'Contact creation failed.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | GET CONTACT ID
        |--------------------------------------------------------------------------
        */

        $contactId = $contactResponse['data']['contactId'] ?? null;

        if (!$contactId) {

            DB::table('remittances')
                ->where('remId', $remittance->remId)
                ->increment('amount', $totalDeduct);

            return response()->json([
                'status'  => false,
                'message' => 'Contact ID not found.'
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER PAYLOAD
        |--------------------------------------------------------------------------
        */

        $orderRequest = new Request([
            'contact_id' => $contactId,
            'amount'     => $amount,
            'mode'       => 'IMPS'
        ]);

        /*
        |--------------------------------------------------------------------------
        | CREATE ORDER API
        |--------------------------------------------------------------------------
        */

        $bankResponse = $this->payoutService
            ->createOrder($orderRequest);

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
}
