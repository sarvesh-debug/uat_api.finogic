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

    public function createOrder(Request $request)
    {
        $request->validate([
            'contact_id' => 'required',
            'amount' => 'required',
            'mode' => 'required',
        ]);

        return response()->json(
            $this->payoutService->createOrder($request)
        );
    }
}
