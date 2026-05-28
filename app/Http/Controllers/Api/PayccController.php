<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PayccService;
use App\Traits\ApiResponseTrait;

class PayccController extends Controller
{
    use ApiResponseTrait;

    protected $service;

    public function __construct(PayccService $service)
    {
        $this->service = $service;
    }

    public function initKyc(Request $request)
    {
        $response = $this->service->initKyc($request->all());

        if ($response['success']) {
            return $this->successResponse(
                $response['message'],
                $response['data']
            );
        }

        return $this->errorResponse($response['message']);
    }

    public function kycStatus($kid)
    {
        $response = $this->service->kycStatus($kid);

        if ($response['success']) {
            return $this->successResponse(
                $response['message'],
                $response['data']
            );
        }

        return $this->errorResponse($response['message']);
    }

    public function customerCheck(Request $request)
    {
        $response = $this->service->customerCheck($request->all());

        if ($response['success']) {
            return $this->successResponse(
                $response['message'],
                $response['data']
            );
        }

        return $this->errorResponse($response['message']);
    }

    public function addCard(Request $request)
    {
        $response = $this->service->addCard($request->all());

        if ($response['success']) {
            return $this->successResponse(
                $response['message'],
                $response['data']
            );
        }

        return $this->errorResponse($response['message']);
    }

    public function deleteCard(Request $request)
    {
        $response = $this->service->deleteCard($request->all());

        if ($response['success']) {
            return $this->successResponse($response['message']);
        }

        return $this->errorResponse($response['message']);
    }

    public function categories(Request $request)
    {
        return response()->json(
            $this->service->categories($request->all())
        );
    }

    public function banks(Request $request)
    {
        return response()->json(
            $this->service->banks($request->all())
        );
    }

    public function addBank(Request $request)
    {
        return response()->json(
            $this->service->addBank($request->all())
        );
    }

    public function cards(Request $request)
    {
        return response()->json(
            $this->service->cards($request->all())
        );
    }
}