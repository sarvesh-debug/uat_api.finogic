<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\InstantPayHelper;

class txnStatusController extends Controller
{   
    //
    public function transactionStatus(Request $request) {
        
        $request->validate([
            'transactionDate' => 'required',
            'externalRef' => 'required',
            
        ]);
        
   
        // Call the helper function
       $response = InstantPayHelper::transactionStatus($request->all());

return $response;
die();
       if (isset($response['statuscode']) && $response['statuscode'] === 'TXN') {
            return response()->json([
                'Provider' =>'Support Team CodeGraphi Technology',
                'success' => true,
                'response' =>$response,
            ]);
        }

        return response()->json([
            'Provider' =>'Support Team CodeGraphi Technology',
            'success' => false,
            'actcode'=>$response['actcode'],
            'message' => $response['status'] ?? 'Unknown error',
            'timestamp'=>$response['timestamp'],
            'ipay_uuid'=>$response['ipay_uuid'],
            'orderid'=>$response['orderid'],
            'environment'=>$response['environment'],

        ], 400);
    }
}
 