<?php

namespace App\Services;

use App\Helpers\IPaymentHelper;

class PayccService
{
    public function initKyc($request)
    {
        $response = IPaymentHelper::initKyc($request);

        if (($response['code'] ?? '') == '0x0200') {

            return [
                'success' => true,
                'message' => 'KYC initiated successfully',
                'data' => [
                    'kycId' => $response['data']['kid'] ?? null,
                    'redirectUrl' => $response['data']['url'] ?? null
                ]
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'KYC initiation failed'
        ];
    }

    public function kycStatus($kid)
    {
        $response = IPaymentHelper::kycStatus($kid);

        if (($response['code'] ?? '') == '0x0200') {

            return [
                'success' => true,
                'message' => 'KYC details fetched successfully',
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'KYC fetch failed'
        ];
    }

    public function customerCheck($request)
    {
        $response = IPaymentHelper::customerCheck($request);

        if (($response['code'] ?? '') == '0x0200') {

            return [
                'success' => true,
                'message' => 'Customer found',
                'data' => $response['data']
            ];
        }

        return [
            'success' => false,
            'message' => $response['message'] ?? 'Customer not found'
        ];
    }

}