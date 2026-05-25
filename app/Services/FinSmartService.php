<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class FinSmartService
{
    protected $baseUrl;
    protected $token;
    protected $mid;

    public function __construct()
    {
        $this->baseUrl = config('services.finsmart.base_url');
        $this->token = config('services.finsmart.token');
        $this->mid = config('services.finsmart.mid');
    }

    private function headers()
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'mid' => $this->mid,
            'Accept' => 'application/json'
        ];
    }

    // public function initiatePayout($data)
    // {
    //    // return $this->headers();
    //     return Http::withHeaders($this->headers())
    //         ->post($this->baseUrl . '/api/payout/do-payout', $data)
    //         ->json();
    // }

    public function initiatePayout($data)
{
    if (env('FINSMART_MODE') == 'TEST') {

        return [
            "status" => 1,
            "message" => "Transaction is pending. Please check the status using the status check API before initiating this transaction again.",
            "data" => [
                "response_code" => 2,
                "subStatus" => "102",
                "ref_id" => $data['reference_id'] ?? '94344xxxx',
                "transaction_id" => "TESTTXN" . rand(10000,99999),
                "utr" => "TESTUTR" . rand(10000,99999),
                "payment_mode" => "IMPS",
                "amount" => (string)$data['amount'],
                "payment_remark" => $data['transaction_note'] ?? "test txn",
                "account_number" => $data['account_no'],
                "ifsc" => $data['ifsc_code'],
                "beneficiaryName" => $data['beneficiary_name']
            ]
        ];
    }

    return Http::withHeaders($this->headers())
        ->post($this->baseUrl . '/api/payout/do-payout', $data)
        ->json();
}

    public function checkStatus($reference_id)
    {

        if(env('FINSMART_MODE') == 'TEST')
            {
                
            }
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/api/payout/status-check', [
                'reference_id' => $reference_id
            ])
            ->json();
    }

    public function checkBalance()
    {
        return Http::withHeaders($this->headers())
            ->get($this->baseUrl . '/api/check-balance')
            ->json();
    }
}
