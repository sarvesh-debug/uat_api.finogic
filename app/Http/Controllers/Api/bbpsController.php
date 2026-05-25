<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\bbpsHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class bbpsController extends Controller
{
    /* ============================================================
       🔐 AUTH FUNCTION (UNCHANGED - YOUR SYSTEM IS GOOD)
    ============================================================ */
    public function localAuth($apiKey, $merchantId, $request)
    {
        $clientIp = $request->ip();

        $merchant = DB::table('remittances')
            ->where('apikey', $apiKey)
            ->where('remId', $merchantId)
            ->first();

        if (!$merchant) {
            return $this->errorResponse('INVALID_CREDENTIALS', 'Invalid API Key or Merchant ID', 401, $clientIp);
        }

        if ($merchant->status !== 'success') {
            return $this->errorResponse('MERCHANT_INACTIVE', 'Merchant account is inactive', 403);
        }

             $service = DB::table('apis')
    ->where('name', 'BBPS')
    ->first();

if (!$service || $service->status != 1) {
    return response()->json([
        'success' => false,
        'error'   => [
            'code'    => 'SERVICE_INACTIVE',
            'message' => $service->message ?? 'Service is currently inactive'
        ],
        'meta' => [
            'merchantId' => $merchantId,
            'timestamp'  => now()
        ]
    ], 403);
}
        if ($merchant->isBBPS != 1) {
            return $this->errorResponse('SERVICE_DISABLED', 'BBPS Service inactive', 403);
        }

        if ($merchant->isKyc != 1) {
            return $this->errorResponse('KYC_PENDING', 'Merchant KYC pending', 403);
        }

        if ($merchant->ipAddress) {
            $allowedIps = explode(',', $merchant->ipAddress);

            if (!in_array($clientIp, $allowedIps)) {
                return $this->errorResponse('IP_NOT_WHITELISTED', 'Access denied from this IP', 403, $clientIp);
            }
        }

        return $merchant;
    }

    private function validateAuth(Request $request, $requestId)
    {
        $apiKey = $request->header('X-API-KEY');
        $merchantId = $request->header('X-MERCHANT-ID');

        if (!$apiKey || !$merchantId) {
            return $this->errorResponse('AUTH_HEADER_MISSING', 'Headers missing', 401, null, $requestId);
        }

        $auth = $this->localAuth($apiKey, $merchantId, $request);

        return ($auth instanceof JsonResponse) ? $auth : true;
    }

    /* ============================================================
       🔥 COMMON ERROR FORMAT
    ============================================================ */
    private function errorResponse($code, $message, $statusCode, $ip = null, $requestId = null)
    {
        return response()->json([
            'success' => false,
            'status'  => 'FAILED',
            'message' => $message,
            'error'   => [
                'code' => $code,
                'message' => $message
            ],
            'meta' => [
                'request_id' => $requestId ?? 'req_' . Str::uuid(),
                'ip' => $ip,
                'timestamp' => now()
            ]
        ], $statusCode);
    }

    /* ============================================================
       ✅ 1. GET CATEGORY
    ============================================================ */
    public function getCategory(Request $request)
    {

    //return $request;
        $requestId = 'req_' . Str::uuid();

        $auth = $this->validateAuth($request, $requestId);
        if ($auth instanceof JsonResponse) return $auth;

        try {
            $response = bbpsHelper::getCategory();


            
           return $response;

        //    if($response['success']==true)
        //     {
        //         return response()->json([
        //         'success' => $response['success'],
        //         'message' => "Categories fetched",
        //         'data'    => $response['data'],
        //         'meta' => [
        //             'request_id' => $requestId,
        //             'timestamp'  => now()
        //         ]
        //     ]);
        //     }

        //     return response()->json([
        //         'success' => $response['success'] ?? false,
        //         'message' => "Categories fetched Failed",
        //         'data'    => $response['data'],
        //         'meta' => [
        //             'request_id' => $requestId,
        //             'timestamp'  => now()
        //         ]
        //     ]);

        } catch (\Exception $e) {

        //return $e;
            return $this->errorResponse('SERVER_ERROR', $e->getMessage(), 500, null, $requestId);
        }
    }

    /* ============================================================
       ✅ 2. GET BILLERS
    ============================================================ */
    public function getBillers(Request $request)
    {
        $requestId = 'req_' . Str::uuid();

        $auth = $this->validateAuth($request, $requestId);
        if ($auth instanceof JsonResponse) return $auth;

        $validator = Validator::make($request->all(), [
            'category_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('VALIDATION_ERROR', $validator->errors()->first(), 422, null, $requestId);
        }

        try {
            $response = bbpsHelper::getBillers($validator->validated());
            return $response;
            // return response()->json([
            //     'success' => $response['status'],
            //     'message' => $response['message'],
            //     'data'    => $response['data'],
            //     'meta' => [
            //         'request_id' => $requestId,
            //         'timestamp'  => now()
            //     ]
            // ]);

        } catch (\Exception $e) {
            return $this->errorResponse('SERVER_ERROR', $e->getMessage(), 500, null, $requestId);
        }
    }

    /* ============================================================
       ✅ 3. GET BILLER FIELDS
    ============================================================ */
    public function getBillerFields(Request $request)
    {
        $requestId = 'req_' . Str::uuid();

        $auth = $this->validateAuth($request, $requestId);
        if ($auth instanceof JsonResponse) return $auth;

        $validator = Validator::make($request->all(), [
            'biller_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('VALIDATION_ERROR', $validator->errors()->first(), 422, null, $requestId);
        }

        try {
            $response = bbpsHelper::getBillerFields($validator->validated());

            return $response;

            // return response()->json([
            //     'success' => $response['status'],
            //     'message' => $response['message'],
            //     'data'    => $response['data'],
            //     'meta' => [
            //         'request_id' => $requestId,
            //         'timestamp'  => now()
            //     ]
            // ]);

        } catch (\Exception $e) {
            return $this->errorResponse('SERVER_ERROR', $e->getMessage(), 500, null, $requestId);
        }
    }

    /* ============================================================
       ✅ 4. FETCH BILL
    ============================================================ */
    public function fetchBill(Request $request)
    {
        $requestId = 'req_' . Str::uuid();

        $auth = $this->validateAuth($request, $requestId);
        if ($auth instanceof JsonResponse) return $auth;

        $validator = Validator::make($request->all(), [
            'biller_id'  => 'required|string',
            'enquiry_id' => 'required|string',
            'params'     => 'required|array'
        ]);
         $merchantId = $request->header('X-MERCHANT-ID');

        if ($validator->fails()) {
            return $this->errorResponse('VALIDATION_ERROR', $validator->errors()->first(), 422, null, $requestId);
        }

        try {
            $response = bbpsHelper::fetchBill($validator->validated(),$merchantId);

            return $response;
            // return response()->json([
            //     'success' => $response['status'],
            //     'message' => $response['message'],
            //     'data'    => $response['data'],
            //     'meta' => [
            //         'request_id' => $requestId,
            //         'timestamp'  => now()
            //     ]
            // ]);

        } catch (\Exception $e) {
            return $this->errorResponse('SERVER_ERROR', $e->getMessage(), 500, null, $requestId);
        }
    }

    /* ============================================================
       ✅ 5. PAY BILL
    ============================================================ */
    public function payBill(Request $request)
    {
        $requestId = 'req_' . Str::uuid();

        $auth = $this->validateAuth($request, $requestId);
        if ($auth instanceof JsonResponse) return $auth;

        $validator = Validator::make($request->all(), [
            'biller_id'    => 'required|string',
            'enquiry_id'   => 'required|string',
            'amount'       => 'required|numeric|min:1',
            'externalRef' => 'required|string',
            'category_key' => 'required|string'
            
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('VALIDATION_ERROR', $validator->errors()->first(), 422, null, $requestId);
        }
        $validated = $validator->validated();
        $merchantId = $request->header('X-MERCHANT-ID');
        $amount=$request->amount;
        $category_key=$request->category_key;
    

        try {
             //DB::beginTransaction();

        // 3️⃣ Idempotency Check
        $exists = DB::table('utility_payments')
            ->where('external_ref', $validated['externalRef'])
            ->lockForUpdate()
            ->first();

        if ($exists) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'statuscode'  => 'ERR' ?? null,
            'status' => 'Duplicate transaction reference' ?? 'Transaction Failed',
                'message' => 'Duplicate transaction reference',
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 409);
        }

        // 4️⃣ Wallet Balance Check
        $wallet = DB::table('remittances')
            ->where('remId', $merchantId)
            ->lockForUpdate()
            ->first();

        $merchant=$wallet;
        $openingBal=$wallet->amount;
        if (!$wallet || $wallet->amount < $validated['amount']) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'statuscode'  => 'ERR' ?? null,
            'status' => 'Insufficient wallet balance' ?? 'Transaction Failed',
                'message' => 'Insufficient wallet balance',
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 400);
        }

        // Fetch local commissions for the remittance package
            $commissions = DB::table('commissions')
                ->where('packagesId', $merchant->packageId)
                ->where('service', $category_key)
                ->get() ?? [];
          //dd($merchant->packageId,$category_key);

          //return $commissions;

        if ($commissions->isEmpty()) {
                return response()->json([
                     'statuscode'  => 'ERR' ?? null,
            'status' => 'Package Not Found' ?? 'Transaction Failed',
                
                    'success'  => false,
                    'message' => 'No commission structure found for your package. Please contact Admin.'
                ], 400);
            }
        // return $commissions;die();
            // Initialize charges and tds
            $charges = 0;
            $tds = 0;
            $comm=0;

            // Loop through commissions to find the applicable range
        foreach ($commissions as $item) {
        // Ensure numeric comparison
        $from = (float) $item->from_amount;
        $to   = (float) $item->to_amount;

        if ($item->service === $category_key && $amount >= $from && $amount <= $to) {
            // Calculate charges
            $charges = $item->charge_in === 'Percentage'
                ? $amount * ((float) $item->charge) / 100
                : (float) $item->charge;
            
            $comm=$item->commissions_in==='Percentage'
                ? $amount *((float)$item->commissions)/100
                :(float) $item->commissions;

            // Calculate TDS
            $tds = $item->tds_in === 'Percentage'
                ? $charges * ((float) $item->tds) / 100
                : (float) $item->tds;

            // Exit loop once a matching commission is found
            break;
        }
    }


            // $charges and $tds now have the calculated values
        //dd($charges, $tds);die();

            // ✅ Fallback charges
        //   if ($comm == 0 && $amount >= 100) {
        //         $comm = $amount * 0.01;
        //         $tds  = $comm * 0.05; // example 5%
        //     }

            $totalDeduct = $amount + $charges + $tds;
            $closingBal  = $openingBal - $totalDeduct;
              if (!$totalDeduct || $totalDeduct < $totalDeduct) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                 'statuscode'  => 'ERR' ?? null,
            'status' => 'Insufficient wallet balance' ?? 'Transaction Failed',
                'message' => 'Insufficient wallet balance with charges',
                'meta' => [
                    'request_id' => $requestId,
                    'timestamp'  => now()
                ]
            ], 400);
        }

        // 5️⃣ Debit Wallet (Temporary Hold)
        DB::table('remittances')
            ->where('remId', $merchantId)
            ->update([
                'amount' => $closingBal,
            ]);

        // 6️⃣ Create Transaction Log (PENDING)
        DB::table('utility_payments')->insert([
                'request_id'        => $requestId,
                'biller_id'         =>$validated['biller_id'],
                'enquiry_id'        =>$validated['enquiry_id'],
                'external_ref'     => $validated['externalRef'],
                'transactionId'     =>'',
                'remId'       => $request->header('X-MERCHANT-ID'),

                'outlet_id'         => "1233456",
                'transaction_amount'            => $validated['amount'],
                'charges'           => $charges ?? 0,
                'tds'               => $tds ?? 0,
                'commission'        => $comm ?? 0,

            
                'opening_balance'   =>$openingBal,
                'closing_balance'   =>$closingBal,

                'response_body'      => null,
                'provider_response' => null, // update after API response

                'status'            => 'PENDING',
                'service'           =>$category_key,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);

            //return "yah tak sab thik hai";
           $response = bbpsHelper::payBill($validator->validated());
            // $response = [
            //     'success' => true,
            //     'data' => [
            //         'transactionId' => 'TXN' . time(),
            //         'status' => 'success',
            //         'externalRef' => 'REF' . rand(100000, 999999),
            //         'enquiryId' => 'ENQ' . rand(1000000000, 9999999999),
            //         'responseData' => [
            //             'externalRef' => 'TXN' . time(),
            //             'poolReferenceId' => Str::random(20),
            //             'txnValue' => 320,
            //             'txnReferenceId' => strtoupper(Str::random(16)),
            //             'inputParams' => [
            //                 'input' => [
            //                     [
            //                         'paramName' => 'K Number',
            //                         'paramValue' => '210454059809'
            //                     ]
            //                 ]
            //             ]
            //         ]
            //     ],
            //     'message' => 'Payment processed successfully',
            // ];
            
            $statusCode = $response['success'] ?? null;
            $status = $response['data']['status'] ?? null;
        //    / dd($statusCode,$status);
                info('NewBBPS Response',$response);
            if ($statusCode === true && $status === "success") {

                DB::table('utility_payments')
                    ->where('external_ref', $validated['externalRef'])
                    ->update([
                        'status' => 'SUCCESS',
                        'transactionId' => $response['data']['transactionId'],
                        'provider_response'=>$response,
                       
                    ]);

                return response()->json([
                    'success' => true,
                    'statuscode'  => 'TXN' ?? null,
                    'message' => 'Payment Processed Successfully',
                    'data'    => $response['data'] ?? null,
                    'meta' => [
                        'request_id'  => $requestId,
                        'statuscode'  => $statusCode,
                        'actcode'     => $response['actcode'] ?? null,
                        'ipay_uuid'   => $response['ipay_uuid'] ?? null,
                        'orderid'     => $response['orderid'] ?? null,
                        'environment' => $response['environment'] ?? null,
                        'timestamp'   => $response['timestamp'] ?? now(),
                    ]
                ], 200);
            }

            DB::table('utility_payments')
    ->where('external_ref', $validated['externalRef'])
    ->update([
        'status' => 'FAILED',
    ]);

DB::table('remittances')
    ->where('remId', $merchantId)
    ->update([
        'amount' => $openingBal,
    ]);

return response()->json([
    'success' => false,
    'statuscode'  => 'ERR' ?? null,
    'status' => $status ?? 'Transaction Failed',
    'message' => $status ?? 'Payment Failed',
    'meta' => [
        'request_id'  => $requestId,
        'statuscode'  => $statusCode,
        'actcode'     => $response['actcode'] ?? null,
        'ipay_uuid'   => $response['ipay_uuid'] ?? null,
        'orderid'     => $response['orderid'] ?? null,
        'environment' => $response['environment'] ?? null,
        'timestamp'   => $response['timestamp'] ?? now(),
    ]
], 400);
        } catch (\Exception $e) {

        //return $e;
            return $this->errorResponse('SERVER_ERROR', $e->getMessage(), 500, null, $requestId);
        }
    }


    public function getCommissionList()
    {
       
    $packages = DB::table('packages')->get();

    //return $packages->first()->id;die();
    // Default selected package (ID)
    $selectedPackage = request('packages', $packages->first()->id);
        $services = DB::table('bbps_services')->get();

        $existingPlans = DB::table('commissions')
        ->where('packagesId', $selectedPackage)
        ->get()
        ->keyBy('service'); // 🔥 service code as key


       return view('commission.bbpsService', compact('services', 'existingPlans','packages','selectedPackage'));
    }
    public function saveBbpsCharges(Request $request)
{

//return $request;
    // $request->validate([
    //     'packages'       => 'required|integer|exists:packages,id',
    //     'service_code.*' => 'required|string',
    //     'from_amount.*'  => 'nullable|numeric|min:0',
    //     'to_amount.*'    => 'nullable|numeric|min:0',
    //     'charges.*'      => 'nullable|numeric|min:0',
    //     'charges_type.*' => 'required|in:flat,percentage',
    //     'commission.*'   => 'nullable|numeric|min:0',
    //     'commission_type.*' => 'required|in:flat,percentage',
    //     'tds.*'          => 'nullable|numeric|min:0',
    //     'tds_type.*'     => 'required|in:flat,percentage',
    // ]);

    try {
        DB::beginTransaction();

        foreach ($request->service_code as $i => $code) {

            // default values agar null ho
            $from = $request->from_amount[$i] ?? 0;
            $to   = $request->to_amount[$i] ?? 0;
            $charge = $request->charges[$i] ?? 0;
            $commission = $request->commission[$i] ?? 0;
            $tds = $request->tds[$i] ?? 0;

            DB::table('commissions')->updateOrInsert(
                [
                    'packagesId' => $request->packages,
                    'service'    => $code
                ],
                [
                    'from_amount'     => $from,
                    'to_amount'       => $to,
                    'charge'          => $charge,
                    'charge_in'       => $request->charges_type[$i],
                    'commissions'      => $commission,
                    'commissions_in'   => $request->commission_type[$i],
                    'tds'             => $tds,
                    'tds_in'          => $request->tds_type[$i],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }

        DB::commit();

        return back()->with('success', 'BBPS charges saved successfully');

    } catch (\Exception $e) {
       // return $e;
        DB::rollBack();
         Log::error('BBPS Charges Save Error: '.$e->getMessage(), [
            'trace' => $e->getTraceAsString()
        ]);
        return back()->with('error', 'Something went wrong while saving BBPS charges.');
    }
}




 /* =====================================================
     * DMT SECTION
     * ===================================================== */

    private function filterBBPS($request)
    {
        // $query = DB::table('utility_payments'); // no merchant restriction
    $query = DB::table('utility_payments as up')
    ->leftJoin('bbps_services as bs', 'up.service', '=', 'bs.category_code')
    ->select(
        'up.*',
        'bs.category_name as service_name'
    );
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('external_ref', 'like', "%$search%")
                  ->orWhere('service', 'like', "%$search%")
                  ->orWhere('remId', 'like', "%$search%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function bbpsIndex(Request $request)
    {
        $txn = $this->filterBBPS($request)
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        // $serviceNa=DB::table('bbps_services')->get();

          //return $txn;
        return view('admin.reports.bbps_report', compact('txn'));
    }

    public function bbpsExport(Request $request)
    {
        $transactions = $this->filterDmt($request)
            ->orderByDesc('created_at')
            ->get();
        return new StreamedResponse(function () use ($transactions) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Merchant ID',
                'Payment ID',
                'Amount',
                'Charges',
                'Closing Balance',
                'Beneficiary',
                'Status',
                'Date'
            ]);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->merchant_id,
                    $t->externalRef,
                    $t->amount,
                    $t->charges,
                    $t->closing_balance,
                    $t->beneficiaryName,
                    $t->status,
                    $t->created_at
                ]);
            }

            fclose($handle);

        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=Admin_DMT_Report.csv"
        ]);
    }



    public function createMerchant(Request $request)
{
    //return $request;
    // ✅ Validation
    $request->validate([
        'mobile'           => 'required|digits:10',
        'name'             => 'required|string',
        'gender'           => 'required|in:M,F',
        'pan'              => 'required|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
        'email'            => 'required|email',
        'address_full'     => 'required|string',
        'address_city'     => 'required|string',
        'address_pincode'  => 'required|digits:6',
        'aadhaar'          => 'required|digits:12',
        'dateOfBirth'      => 'required|date_format:Y-m-d',
        'latitude'         => 'required',
        'longitude'        => 'required',
        'bankAccountNo'    => 'required|digits_between:9,18',
        'bankIfsc'         => 'required|regex:/^[A-Z]{4}0[A-Z0-9]{6}$/'
    ]);

    // ✅ Payload prepare
    $data = [
        "mobile" => $request->mobile,
        "name" => $request->name,
        "gender" => $request->gender,
        "pan" => strtoupper($request->pan),
        "email" => $request->email,

        "address" => [
            "full" => $request->address_full,
            "city" => $request->address_city,
            "pincode" => $request->address_pincode,
        ],

        "aadhaar" => $request->aadhaar,
        "dateOfBirth" => $request->dateOfBirth,
        "latitude" => $request->latitude,
        "longitude" => $request->longitude,
        "bankAccountNo" => $request->bankAccountNo,
        "bankIfsc" => strtoupper($request->bankIfsc),
    ];

    // ✅ Helper call
    $response = bbpsHelper::createMerchant($data);
    return $response;
    //return response()->json($response);
}
}