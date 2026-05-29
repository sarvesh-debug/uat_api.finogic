<?php

namespace App\Services;

use App\Helpers\PayoutHelper;
use Illuminate\Support\Str;

class PayoutService
{
    /*
    |--------------------------------------------------------------------------
    | CREATE CONTACT
    |--------------------------------------------------------------------------
    */

    public function createContact($request)
    {
        $payload = [
            "firstName" => $request->first_name,
            "lastName" => $request->last_name,
            "email" => $request->email,
            "mobile" => $request->mobile,
            "type" => "customer",
            "accountType" => "bank_account",
            "accountNumber" => $request->account_number,
            "ifsc" => $request->ifsc,
            "referenceId" => "REF".rand(111111,999999),
        ];

        return PayoutHelper::createContact($payload);
    }

    /*
    |--------------------------------------------------------------------------
    | GET CONTACT
    |--------------------------------------------------------------------------
    */

    public function getContact($contactId)
    {
        return PayoutHelper::getContact($contactId);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER
    |--------------------------------------------------------------------------
    */

    public function createOrder($request)
    {
        $payload = [
            "contactId" => $request->contactId,
            "amount" => $request->amount,
            "purpose" => "salary_disbursement",
            "mode" => $request->mode,
            "narration" => "Payout Transfer",
            "remark" => "API Transfer",
            "clientRefId" => $request->clientRefId,
        ];

        //return $payload;
        return PayoutHelper::createOrder($payload);
    }
}
