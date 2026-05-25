<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Http;


class InstantPayHelper
{

    ///////Marchant Onboarding Api /////////
    //
    public static function initiateSignup(array $requestData)
    {
        // Encrypt Aadhaar Number 
        $aadhaarNumber = $requestData['aadhaar'];
        $encryptionKey = env('IPAY_KEY');
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($aadhaarNumber, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
        $encryptedData = base64_encode($iv . $ciphertext);

        // Prepare API payload
        $payload = [
            'mobile'        => $requestData['mobile'],
            'email'         => $requestData['email'],
            'aadhaar'       => $encryptedData,
            'pan'           => $requestData['pan'],
            'bankAccountNo' => $requestData['bankAccountNo'],
            'bankIfsc'      => $requestData['bankIfsc'],
            'latitude'      => $requestData['latitude'],
            'longitude'     => $requestData['longitude'],
            'consent'       => $requestData['consent'],
        ];

        // Send request to InstantPay API
        $response = Http::timeout(180)->withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/user/outlet/signup/initiate', $payload);

        return $response->json();
    }

    //Signup eKYC Validate

    public static function initiateSignupVerify(array $requestData)
    {
        // Prepare API payload
        $payload = [
            'otpReferenceID'        => $requestData['otpReferenceID'],
            'otp'         => $requestData['otp'],
            'hash'       => $requestData['hash'],
        ];
         // Send request to InstantPay API
         $response = Http::timeout(180)->withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/user/outlet/signup/validate', $payload);

        return $response->json();

    }
//Mobile Change Initiate
    public static function MobileChangeInitiate(array $requestData)
    {
        $payload = [
            'existingMobileNumber'        => $requestData['existingMobileNumber'],
            'newMobileNumber'         => $requestData['newMobileNumber'],
        ];
        $response = Http::timeout(180)->withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/user/outlet/mobileUpdate', $payload);

        return $response->json();

    }
    //Mobile Change Validate
    public static function MobileChangeInitiateVerify(array $requestData)
    {
        $payload = [
            "existingMobileNumber" => $requestData['existingMobileNumber'],
        "newMobileNumber" => $requestData['newMobileNumber'],
        "otp" => [
            "existingMobileNumber" => $requestData['existingMobileNumberOTP'],
            "newMobileNumber" => $requestData['newMobileNumberOTP'],
        ]
        ];
        $response = Http::timeout(180)->withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/user/outlet/mobileUpdateVerify', $payload);

        return $response->json();

    }

    // Merchant List

    public static function merchantList(array $requestData)
    {
        $payload = [
            "pagination" => [
                "pageNumber" => $requestData['pageNumber'],
                "recordsPerPage" => $requestData['recordsPerPage'],
            ],
            "filters" => [
                // "outletId" =>$requestData['outletId'],
                // "mobile" => $requestData['mobile'],
                // "pan" => $requestData['pan'],
               "outletId" => 0,
            "mobile" => "",
            "pan" => "",
            ]
        ];
        
        $response = Http::withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/user/outlet/list', $payload);

        return $response->json();

    }
    // end Marchant API

    // AEPS Helper Start Here 

    //Aeps Outlet Login Status
    public static function outletLoginStatus(array $requestData)
    {
        $outLet=$requestData['outLet'];
        $payload = [
    
        ];
        $response = Http::withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id'  =>$outLet,
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/fi/aeps/outletLoginStatus', $payload);

        return $response->json();

    }

    //Aeps OutLet Login
    public static function outletLogin(array $requestData)
    {
       
        $outLet=$requestData['outLet'];
        $aadhaarNumber = $requestData['aadhaar'];
        $encryptionKey = env('IPAY_KEY');  // Ensure this is set in your .env file
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($aadhaarNumber, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
        $encryptedAadhaar = base64_encode($iv . $ciphertext);

        $biometricData = array_merge(
            ['encryptedAadhaar' => $encryptedAadhaar],
            is_array($requestData['biometricData']) ? $requestData['biometricData'] : json_decode($requestData['biometricData'], true) ?? []
        );
        
        $payload = [
            'type' => $requestData['type'],
            'latitude' => $requestData['latitude'],
            'longitude' => $requestData['longitude'],
            'externalRef' => $requestData['externalRef'],
            'biometricData' => $biometricData,
    
        ];
        $response = Http::withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id'  =>$outLet,
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/fi/aeps/outletLogin', $payload);

        return $response->json();

    }

     //Cash Withdrawal
     public static function cashWithdrawal(array $requestData)
     {
         $outLet=$requestData['outLet'];
         $aadhaarNumber = $requestData['aadhaar'];
         $encryptionKey = env('IPAY_KEY');  // Ensure this is set in your .env file
         $ivlen = openssl_cipher_iv_length('aes-256-cbc');
         $iv = openssl_random_pseudo_bytes($ivlen);
         $ciphertext = openssl_encrypt($aadhaarNumber, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
         $encryptedAadhaar = base64_encode($iv . $ciphertext);
 
        //  $biometricData = array_merge(
        //      ['encryptedAadhaar' => $encryptedAadhaar],
        //      json_decode($requestData['biometricData'], true)
        //  );
        $biometricData = array_merge(
            ['encryptedAadhaar' => $encryptedAadhaar],
            is_array($requestData['biometricData']) ? $requestData['biometricData'] : json_decode($requestData['biometricData'], true) ?? []
        );
         $payload = [
             'bankiin' => $requestData['bankiin'],
             'latitude' => $requestData['latitude'],
             'longitude' => $requestData['longitude'],
             'mobile' => $requestData['mobile'], 
             'amount' => $requestData['amount'], 
             'externalRef' => $requestData['externalRef'],
             'biometricData' => $biometricData,
     
         ];
         $response = Http::withHeaders([
             'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
             'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
             'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
             'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
             'X-Ipay-Outlet-Id'  =>$outLet,
             'Content-Type'        => 'application/json',
         ])->post('https://api.instantpay.in/fi/aeps/cashWithdrawal', $payload);
 
         return $response->json();
 
     }

     //Balance Inquiry
     public static function balanceInquiry(array $requestData)
     {
         $outLet=$requestData['outLet'];
         $aadhaarNumber = $requestData['aadhaar'];
         $encryptionKey = env('IPAY_KEY');  // Ensure this is set in your .env file
         $ivlen = openssl_cipher_iv_length('aes-256-cbc');
         $iv = openssl_random_pseudo_bytes($ivlen);
         $ciphertext = openssl_encrypt($aadhaarNumber, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
         $encryptedAadhaar = base64_encode($iv . $ciphertext);
 
         $biometricData = array_merge(
            ['encryptedAadhaar' => $encryptedAadhaar],
            is_array($requestData['biometricData']) ? $requestData['biometricData'] : json_decode($requestData['biometricData'], true) ?? []
        );
         $payload = [
             'bankiin' => $requestData['bankiin'],
             'latitude' => $requestData['latitude'],
             'longitude' => $requestData['longitude'],
             'mobile' => $requestData['mobile'], 
             'externalRef' => $requestData['externalRef'],
             'biometricData' => $biometricData,
     
         ];
         $response = Http::withHeaders([
             'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
             'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
             'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
             'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
             'X-Ipay-Outlet-Id'  =>$outLet,
             'Content-Type'        => 'application/json',
         ])->post('https://api.instantpay.in/fi/aeps/balanceInquiry', $payload);
 
         return $response->json();
 
     }
        //Mini Statement
     public static function miniStatement(array $requestData)
     {
         $outLet=$requestData['outLet'];
         $aadhaarNumber = $requestData['aadhaar'];
         $encryptionKey = env('IPAY_KEY');  // Ensure this is set in your .env file
         $ivlen = openssl_cipher_iv_length('aes-256-cbc');
         $iv = openssl_random_pseudo_bytes($ivlen);
         $ciphertext = openssl_encrypt($aadhaarNumber, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
         $encryptedAadhaar = base64_encode($iv . $ciphertext);
 
         $biometricData = array_merge(
            ['encryptedAadhaar' => $encryptedAadhaar],
            is_array($requestData['biometricData']) ? $requestData['biometricData'] : json_decode($requestData['biometricData'], true) ?? []
        );
         $payload = [
             'bankiin' => $requestData['bankiin'],
             'latitude' => $requestData['latitude'],
             'longitude' => $requestData['longitude'],
             'mobile' => $requestData['mobile'], 
             'externalRef' => $requestData['externalRef'],
             'biometricData' => $biometricData,
     
         ];
         $response = Http::withHeaders([
             'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
             'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
             'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
             'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
             'X-Ipay-Outlet-Id'  =>$outLet,
             'Content-Type'        => 'application/json',
         ])->post('https://api.instantpay.in/fi/aeps/miniStatement', $payload);
 
         return $response->json();
 
     }

     
 //Aeps Outlet Login Status
 public static function aepsBanks(array $requestData)
 {
     $outLet=$requestData['outLet'];
     $payload = [
 
     ];
     $response = Http::withHeaders([
         'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
         'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
         'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
         'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
         'X-Ipay-Outlet-Id'  =>$outLet,
         'Content-Type'        => 'application/json',
     ])->get('https://api.instantpay.in/fi/aeps/banks', $payload);

     return $response->json();

 }

        //end APES Sevices

        //Start BBPS Here 
        public static function getTelecomCircle(array $requestData)
        {
            $outLet=$requestData['outLet'];
            $payload = [
                
            ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('http://api.instantpay.in/marketplace/utilityPayments/telecomCircles', $payload);
    
            return $response->json();
    
        }


        public static function getRechargePlan(array $requestData)
        {
            $outLet=$requestData['outLet'];
            $payload = [
                'subProductCode' => $requestData['subProductCode'],
                'telecomCircle' => $requestData['telecomCircle'],
                'latitude' => $requestData['latitude'],
                'longitude' => $requestData['longitude'],
                'externalRef' => $requestData['externalRef'],
                
            ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('https://api.instantpay.in/marketplace/utilityPayments/rechargePlans', $payload);
    
            return $response->json();
    
        }
        public static function getCategory(array $requestData)
        {
            $outLet=$requestData['outLet'];
            $payload = [
                
            ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->get('https://api.instantpay.in/marketplace/utilityPayments/category', $payload);
    
            return $response->json();
    
        }

        public static function getBillers(array $requestData)
        {
            $outLet=$requestData['outLet'];
           

            $payload = [
                "pagination" => [
                    "pageNumber" => $requestData['pageNumber'],
                    "recordsPerPage" => $requestData['recordsPerPage'],
                ],
                "filters" => [
                    "categoryKey" => $requestData['categoryKey'],
                    "updatedAfterDate" => $requestData['updatedAfterDate'] ?? null,
                ]
            ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('https://api.instantpay.in/marketplace/utilityPayments/billers', $payload);
    
            return $response->json();
    
        }

        public static function getBillerDetails(array $requestData)
        {
            $outLet=$requestData['outLet'];
            $payload = [
                "billerId"=>$requestData['billerId']
                
            ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('https://api.instantpay.in/marketplace/utilityPayments/billerDetails', $payload);
    
            return $response->json();
    
        }


     public static function billEnquiry(array $requestData)
{
    $outLet = $requestData['outLet'];

    $payload = [
        "billerId" => $requestData['billerId'],
        "initChannel" => $requestData['initChannel'],
        "externalRef" => $requestData['externalRef'],

        "inputParameters" => [
            "param1" => data_get($requestData, 'inputParameters.param1'),
            "param2" => data_get($requestData, 'inputParameters.param2'),
            "param3" => data_get($requestData, 'inputParameters.param3'),
        ],

        "deviceInfo" => [
            "mac" => data_get($requestData, 'deviceInfo.mac'),
            "ip"  => data_get($requestData, 'deviceInfo.ip'),
        ],

        "remarks" => [
            "param1" => data_get($requestData, 'remarks.param1'),
        ],

        "transactionAmount" => $requestData['transactionAmount']
    ];

    try {

        $response = Http::timeout(30)
            ->retry(2, 200) // retry 2 times
            ->withHeaders([
                'X-Ipay-Auth-Code'     => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'     => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'   => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'     => $outLet,
                'Content-Type'         => 'application/json',
            ])
            ->post(
                'https://api.instantpay.in/marketplace/utilityPayments/prePaymentEnquiry',
                $payload
            );

        \Log::info('BBPS Bill Enquiry Request', [
            'payload' => $payload,
            'response' => $response->json()
        ]);

        return $response->json();

    } catch (\Throwable $e) {

        \Log::error('InstantPay API Error', [
            'error' => $e->getMessage(),
            'payload' => $payload
        ]);

        return [
            'statuscode' => 'ERR',
            'status' => 'API_FAILED',
            'message' => $e->getMessage()
        ];
    }
}

        public static function validateBillers(array $requestData)
        {
            $outLet=$requestData['outLet'];
           
            $payload = [
                "billerId" => $requestData['billerId'],
                "initChannel" => $requestData['initChannel'],
                "externalRef" => $requestData['externalRef'],
                "inputParameters" => [
                    "param1" => $requestData['inputParameters']['param1'],  // Fixed to access nested array
                ],
                "deviceInfo" => [
                    "mac" => $requestData['deviceInfo']['mac'],  // Fixed to access nested array
                    "ip" => $requestData['deviceInfo']['ip'],  // Fixed to access nested array
                ],
                "remarks" => [
                    "param1" => $requestData['remarks']['param1'],  // Fixed to access nested array
                ],
                "transactionAmount" => $requestData['transactionAmount']
            ];
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('https://api.instantpay.in/marketplace/utilityPayments/prePaymentEnquiry', $payload);
    
            return $response->json();
    
        }
        public static function paymentBiller(array $requestData)
        {
            $outLet=$requestData['outlet'];
           
            $payload = [
                "billerId" => $requestData['billerId'],
                "externalRef" => $requestData['externalRef'],
                "enquiryReferenceId" => $requestData['enquiryReferenceId'],
                "telecomCircle" => $requestData['telecomCircle'],
                "inputParameters" => [
                    "param1" => $requestData['inputParameters']['param1'],
                    "param2" => $requestData['inputParameters']['param2'] ?? '', 
                    "param3" => $requestData['inputParameters']['param3'] ?? '',// Fixed to access nested array
                ],
                "initChannel" => $requestData['initChannel'],
                "deviceInfo" => [
                    "terminalId" => $requestData['deviceInfo']['terminalId'],  // Fixed to access nested array
                    "mobile" => $requestData['deviceInfo']['mobile'],  // Fixed to access nested array
                    "postalCode" => $requestData['deviceInfo']['postalCode'],  // Fixed to access nested array
                    "geoCode" => $requestData['deviceInfo']['geoCode'],  // Fixed to access nested array
                ],
                "paymentMode" => $requestData['paymentMode'],
                "paymentInfo" => [
                    "Remarks" => $requestData['paymentInfo']['Remarks'],  // Fixed to access nested array
                ],
                "remarks" => [
                    "param1" => $requestData['remarks']['param1'],  // Fixed to access nested array
                ],
                "transactionAmount" => $requestData['transactionAmount'],
                "customerPan" => $requestData['customerPan'],
            ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('https://api.instantpay.in/marketplace/utilityPayments/payment', $payload);
    
            return $response->json();
    
        }

        // end of BBPS

    // ---------------------- Transaction Status --------------------------
    public static function transactionStatus(array $requestData){
    //   return "Hello";
    //   die();
        $payload = [
            'transactionDate' => $requestData['transactionDate'],
            'externalRef' => $requestData['externalRef'],
        ];

        // Send request to InstantPay API
        $response = Http::withHeaders([
            'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
            'Content-Type'        => 'application/json',
        ])->post('https://api.instantpay.in/reports/txnStatus', $payload);

        return $response->json();
    }

    // -------------------------------------- DMT API's -----------------------------------------
    // ------------------------ Bank Details --------------------------
    public static function getBankDetails(array $requestData){

        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
        $outLet= $requestData['outLet'] ?? '580942';
        $payload = [
           
        ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/banks', $payload);
    
        return $response->json();
    }

    // --------------------- Remitter Profile --------------------
    public static function remitterProfile(array $requestData) {
        $outLet=$requestData['outlet'];
       $payload=[
        'mobileNumber'=>$requestData['mobileNumber']

       ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/remitterProfile',$payload);
    
        return $response->json();
    }

    // --------------------- Remitter Registration --------------------
    public static function remitterRegistration($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
    
        // if (empty($requestData['mobileNumber']) || empty($requestData['aadhaarNumber']) || empty($requestData['referenceKey'])) {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }

        $outLet=$requestData['outlet'];
      
        $aadhaarNumber = $requestData['aadhaarNumber'];
        $encryptionKey = env('IPAY_KEY');
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext = openssl_encrypt($aadhaarNumber, 'aes-256-cbc', $encryptionKey, OPENSSL_RAW_DATA, $iv);
        $encryptedData = base64_encode($iv . $ciphertext);
        $payload=[
            'mobileNumber'=>$requestData['mobileNumber'],
            'encryptedAadhaar'=>$encryptedData,
            'referenceKey'=>$requestData['referenceKey']
            
    
           ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/remitterRegistration',$payload);
    
        return $response->json();
    }

    // --------------------- Verify Remitter Registration --------------------
    public static function verifyRemitterRegistration($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
    
        // if (empty($requestData['mobileNumber']) || empty($requestData['otp']) || empty($requestData['referenceKey'])) {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }
        $outLet=$requestData['outlet'];
        $payload=[
            'mobileNumber'=>$requestData['mobileNumber'],
            'otp'=>$requestData['otp'],
            'referenceKey'=>$requestData['referenceKey']
            
    
           ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/remitterRegistrationVerify',$payload);
    
        return $response->json();
    }

    // --------------------- Remitter KYC --------------------
   public static function remitterKyc($requestData) {
    $outLet = $requestData['outlet'];

    // Decode biometric data correctly
    $biometricData = json_decode($requestData['biometricData'], true);
    if (!is_array($biometricData)) {
        throw new \Exception('Invalid JSON in biometricData. It must be an array.');
    }

    $payload = [
        'mobileNumber' => $requestData['mobileNumber'],
        'referenceKey' => $requestData['referenceKey'],
        'latitude' => $requestData['latitude'],
        'longitude' => $requestData['longitude'],
        'captureType' => "FINGER",
        'externalRef' => $requestData['externalRef'],
        'consentTaken' => "Y",
        'biometricData' => $biometricData,
    ];

    $response = Http::withHeaders([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
        'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
        'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
        'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
        'X-Ipay-Outlet-Id' => $outLet,
    ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/remitterKyc', $payload);

    return $response->json();
}

    // --------------------- Verify Remitter Registration --------------------
    public static function beneficiaryRegistration($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
        // // Check for missing required fields
        // if (empty($requestData['beneficiaryMobileNumber']) || empty($requestData['remitterMobileNumber']) || empty($requestData['accountNumber']) || empty($requestData['ifsc']) || empty($requestData['bankId']) || empty($requestData['name']))
        // {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }
        $outLet=$requestData['outlet'];
        $payload=[
            'beneficiaryMobileNumber' => $requestData['beneficiaryMobileNumber'],
            'remitterMobileNumber' => $requestData['remitterMobileNumber'],
            'accountNumber' => $requestData['accountNumber'],
            'ifsc' => $requestData['ifsc'],
            'bankId' => $requestData['bankId'],
            'name' => $requestData['name'],
            
    
           ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/beneficiaryRegistration',$payload);
    
        return $response->json();
    }

    // --------------------- Beneficiary Registration Verify --------------------
    public static function verifyBeneficiaryRegistration($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
        // // Check for missing required fields
        // if (empty($requestData['remitterMobileNumber']) || empty($requestData['otp']) || empty($requestData['beneficiaryId']) || empty($requestData['referenceKey'])
        // ) {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }

        $outLet=$requestData['outlet'];
        $payload=[
            'remitterMobileNumber' => $requestData['remitterMobileNumber'],
            'otp' => $requestData['otp'],
            'beneficiaryId' => $requestData['beneficiaryId'],
            'referenceKey' => $requestData['referenceKey'],
            
    
           ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/beneficiaryRegistrationVerify',$payload);
    
        return $response->json();
    }

    // --------------------- Delete Beneficiary --------------------
    public static function deleteBeneficiary($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
        // // Check for missing required fields
        // if (empty($requestData['remitterMobileNumber']) || empty($requestData['otp']) || empty($requestData['beneficiaryId']) || empty($requestData['referenceKey'])
        // ) {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }
    
        $outLet=$requestData['outlet'];
        $payload=[
            'remitterMobileNumber' => $requestData['remitterMobileNumber'],
            'beneficiaryId' => $requestData['beneficiaryId'],
           ];
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/beneficiaryDelete',$payload);
    
        return $response->json();
    }

    // --------------------- Beneficiary Delete Verify --------------------
    public static function verifyDeleteBeneficiary($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
        // // Check for missing required fields
        // if (empty($requestData['remitterMobileNumber']) || empty($requestData['otp']) || empty($requestData['beneficiaryId']) || empty($requestData['referenceKey'])
        // ) {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }
        $outLet=$requestData['outlet'];
        $payload=[
           'remitterMobileNumber' => $requestData['remitterMobileNumber'],
            'otp' => $requestData['otp'],
            'beneficiaryId' => $requestData['beneficiaryId'],
            'referenceKey' => $requestData['referenceKey'],
           ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/beneficiaryDeleteVerify',$payload);
    
        return $response->json();
    }

    // --------------------- Generate Transaction OTP --------------------
    public static function generateTransactionOtp($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
        // // Check for missing required fields
        // if (empty($requestData['remitterMobileNumber']) || empty($requestData['otp']) || empty($requestData['beneficiaryId']) || empty($requestData['referenceKey'])
        // ) {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }
    
        $outLet=$requestData['outlet'];
        $payload=[
          'remitterMobileNumber' => $requestData['remitterMobileNumber'],
            'amount' => $requestData['amount'],
            'referenceKey' => $requestData['referenceKey'],
           ];

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/generateTransactionOtp', $payload);
    
        return $response->json();
    }

    // --------------------- Bio Auth Transaction --------------------
    // public static function bioAuthTransaction($requestData, $customerOutletId, $decodedBiometricData) {
    //     if (empty($customerOutletId)) {
    //         return [
    //             'success' => false,
    //             'message' => 'Customer Outlet ID is required',
    //         ];
    //     }
    
    //     // Check for missing required fields
    //     if (empty($requestData['remitterMobileNumber']) || empty($requestData['otp']) || empty($requestData['referenceKey']) || empty($requestData['latitude']) || empty($requestData['longitude']) || empty($requestData['amount']) || empty($requestData['externalRef']) || empty($requestData['consentTaken']) || empty($decodedBiometricData)
    //     ) {
    //         return [
    //             'success' => false,
    //             'message' => 'Required fields are missing',
    //         ];
    //     }
    
    //     $response = Http::withHeaders([
    //         'Accept' => 'application/json',
    //         'Content-Type' => 'application/json',
    //         'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
    //         'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
    //         'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
    //         'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
    //         'X-Ipay-Outlet-Id' => $customerOutletId,
    //     ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/bioAuthTransaction', [ 
    //        'remitterMobileNumber' => $requestData['remitterMobileNumber'],
    //         'otp' => $requestData['otp'],
    //         'referenceKey' => $requestData['referenceKey'],
    //         'latitude' => $requestData['latitude'],
    //         'longitude' => $requestData['longitude'],
    //         'amount' => $requestData['amount'],
    //         'externalRef' => $requestData['externalRef'],
    //         'consentTaken' => "Y",
    //         'biometricData' => $decodedBiometricData, // Sending parsed biometricData
    //     ]);
    
    //     return $response->json();
    // }

    // --------------------- DMT Transaction --------------------
    public static function dmtTransaction($requestData) {
        // if (empty($customerOutletId)) {
        //     return [
        //         'success' => false,
        //         'message' => 'Customer Outlet ID is required',
        //     ];
        // }
    
        // // Check for missing required fields
        // if (empty($requestData['remitterMobileNumber']) || empty($requestData['otp']) || empty($requestData['referenceKey']) || empty($requestData['latitude']) || empty($requestData['longitude']) || empty($requestData['transferAmount']) || empty($requestData['externalRef']) || empty($requestData['accountNumber']) || empty($requestData['ifsc']) || empty($requestData['transferMode'])
        // ) {
        //     return [
        //         'success' => false,
        //         'message' => 'Required fields are missing',
        //     ];
        // }

        $outLet=$requestData['outlet'];
        $payload=[
          'remitterMobileNumber' => $requestData['remitterMobileNumber'],
            'otp' => $requestData['otp'],
            'referenceKey' => $requestData['referenceKey'],
            'latitude' => $requestData['latitude'],
            'longitude' => $requestData['longitude'],
            'transferAmount' => $requestData['transferAmount'],
            'externalRef' => $requestData['externalRef'],
            'accountNumber' => $requestData['accountNumber'],
            'ifsc' => $requestData['ifsc'],
            'transferMode' => $requestData['transferMode'],
           ];
    
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Ipay-Auth-Code' => env('IPAY_AUTH_CODE'),
            'X-Ipay-Client-Id' => env('IPAY_CLIENT_ID'),
            'X-Ipay-Client-Secret' => env('IPAY_CLIENT_SECRET'),
            'X-Ipay-Endpoint-Ip' => env('IPAY_ENDPOINT_IP'),
            'X-Ipay-Outlet-Id' => $outLet,
        ])->post('https://api.instantpay.in/fi/remit/out/domestic/v2/transaction',$payload);
    
        return $response->json();
    }
    
        public static function accountVerify(array $requestData)
        {
            $outLet='580942';
             $payload = [
            "payee" => [
                "name"          => $requestData['name'] ?? 'CodeGraphi',
                "accountNumber" => $requestData['accountNumber'],
                "bankIfsc"      => $requestData['ifsc']
            ],
            "externalRef" => uniqid('TXN'),
            "consent"     => "Y",
            "pennyDrop"   => "YES",
            "latitude"    => $requestData['latitude'] ?? '',
            "longitude"   => $requestData['longitude'] ?? ''
        ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('https://api.instantpay.in/identity/verifyBankAccount', $payload);
    
            return $response->json();
    
        }


         public static function VPAVerify(array $requestData)
        {
            $outLet='580942';
             $payload = [
            "payee" => [
                "name"          => $requestData['name'] ?? 'CodeGraphi',
                "accountNumber" => $requestData['accountNumber'],
                "bankIfsc"      => $requestData['ifsc'] ?? '',
            ],
            "externalRef" => uniqid('TXN'),
            "consent"     => "Y",
            "isCached"   => "0",
            "latitude"    => $requestData['latitude'] ?? '',
            "longitude"   => $requestData['longitude'] ?? ''
        ];
            
            $response = Http::withHeaders([
                'X-Ipay-Auth-Code'    => env('IPAY_AUTH_CODE'),
                'X-Ipay-Client-Id'    => env('IPAY_CLIENT_ID'),
                'X-Ipay-Client-Secret'=> env('IPAY_CLIENT_SECRET'),
                'X-Ipay-Endpoint-Ip'  => env('IPAY_ENDPOINT_IP'),
                'X-Ipay-Outlet-Id'  =>$outLet,
                'Content-Type'        => 'application/json',
            ])->post('https://api.instantpay.in/identity/verifyBankAccount', $payload);
    
            return $response->json();
    
        }

}
