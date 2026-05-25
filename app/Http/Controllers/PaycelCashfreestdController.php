<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaycelCashfreestdController extends Controller
{
    public function handleResponse(Request $request)    
    {
        try {

            /*
            |--------------------------------------------------------------------------
            | STEP 1 : Validate Input
            |--------------------------------------------------------------------------
            */

            $request->validate([

                'pycid'           => 'required',
                'customer_id'     => 'required',
                'customer_name'   => 'required',
                'customer_phone'  => 'required',
                'customer_email'  => 'required|email',

            ]);

            /*
            |--------------------------------------------------------------------------
            | STEP 2 : Create Payload Array
            |--------------------------------------------------------------------------
            */

            $payloadArray = [

                "pycid" => $request->pycid,

                "customer_details" => [

                    "customer_id"    => $request->customer_id,
                    "customer_name"  => $request->customer_name,
                    "customer_phone" => $request->customer_phone,
                    "customer_email" => $request->customer_email,

                ]
            ];

            /*
            |--------------------------------------------------------------------------
            | STEP 3 : Convert Payload To JSON
            |--------------------------------------------------------------------------
            */

            $jsonPayload = json_encode($payloadArray);

            /*
            |--------------------------------------------------------------------------
            | STEP 4 : Base64 Encode Payload
            |--------------------------------------------------------------------------
            */

            $encodedPayload = base64_encode($jsonPayload);

            /*
            |--------------------------------------------------------------------------
            | STEP 5 : Log Request
            |--------------------------------------------------------------------------
            */

            Log::info('Paycel Encoded Payload', [

                'payload' => $encodedPayload

            ]);

            /*
            |--------------------------------------------------------------------------
            | STEP 6 : Hit Paycel API
            |--------------------------------------------------------------------------
            */

           $response = Http::withoutVerifying()
                ->withHeaders([

                    'Authorization' =>'qw3456ytdszvbi90987654',
                    'Content-Type'  => 'application/json',

                ])->post(

                    'https://paycel.in/member/nextlevel/cashfreestd/response',

                    [
                        'payLoad' => $encodedPayload
                    ]
                );

            // return $response;

            return response()->json([

    /*
    |--------------------------------------------------------------------------
    | Request URL
    |--------------------------------------------------------------------------
    */

    'url' => 'https://paycel.in/member/nextlevel/cashfreestd/response',

    /*
    |--------------------------------------------------------------------------
    | Request Headers
    |--------------------------------------------------------------------------
    */

    'headers' => [

        'Authorization' => 'qw3456ytdszvbi90987654',
        'Content-Type'  => 'application/json',

    ],

    /*
    |--------------------------------------------------------------------------
    | Request Payload
    |--------------------------------------------------------------------------
    */

    'request_payload' => [

        'payLoad' => $encodedPayload

    ],

    /*
    |--------------------------------------------------------------------------
    | Response Status
    |--------------------------------------------------------------------------
    */

    'status_code' => $response->status(),

    /*
    |--------------------------------------------------------------------------
    | Response Headers
    |--------------------------------------------------------------------------
    */

    // 'response_headers' => $response->headers(),

    /*
    |--------------------------------------------------------------------------
    | Response Body
    |--------------------------------------------------------------------------
    */

    'response_body' => $response->body(),

    /*
    |--------------------------------------------------------------------------
    | Response JSON
    |--------------------------------------------------------------------------
    */

    'response_json' => $response->json(),

]);

            /*
            |--------------------------------------------------------------------------
            | STEP 7 : Get API Response
            |--------------------------------------------------------------------------
            */

            $api_response = $response->object();

            /*
            |--------------------------------------------------------------------------
            | STEP 8 : Log Response
            |--------------------------------------------------------------------------
            */

            Log::info('Paycel API Response', [

                'response' => $api_response

            ]);

            /*
            |--------------------------------------------------------------------------
            | STEP 9 : Check Success Response
            |--------------------------------------------------------------------------
            */

            if (
                isset($api_response->response_code) &&
                $api_response->response_code == 200 &&
                $api_response->status == "Success"
            ) {

                /*
                |--------------------------------------------------------------------------
                | STEP 10 : Create Encoded Redirect ID
                |--------------------------------------------------------------------------
                */

                $bothid =
                    $api_response->body->user_id .
                    '/' .
                    $api_response->body->paycel_re_id;

                $id = base64_encode($bothid);

                /*
                |--------------------------------------------------------------------------
                | STEP 11 : Redirect User
                |--------------------------------------------------------------------------
                */

                return redirect(
                    "https://paycel.in/app/chagnstd/load-money/pg/" . $id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Failed Response
            |--------------------------------------------------------------------------
            */

            return response()->json([

                'status'  => false,
                'message' => 'Paycel API Failed',
                'response' => $api_response

            ], 400);

        } catch (\Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Exception Error
            |--------------------------------------------------------------------------
            */

            Log::error('Paycel Error', [

                'message' => $e->getMessage()

            ]);

            return response()->json([

                'status'  => false,
                'message' => $e->getMessage()

            ], 500);
        }
    }
}