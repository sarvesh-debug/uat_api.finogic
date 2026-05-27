<?php

use App\Http\Controllers\aeronpayCallBackController;
use App\Http\Controllers\Api\aepsStlmController;
use App\Http\Controllers\Api\aepsV2Controller;
use App\Http\Controllers\mainAPIController;
use App\Http\Controllers\pgController;
use App\Http\Controllers\summaryCotroller;
use App\Http\Controllers\upiaeronpayController;
use App\Http\Controllers\XpressaeronpayController;
use App\Http\Controllers\XpresspayoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PayoutV2Controller;
use App\Http\Controllers\RemittanceController;
use App\Http\Controllers\upipayoutController;
use App\Http\Controllers\pg1Controller;
use App\Http\Controllers\pg2Controller;

use App\Http\Controllers\Api\accountVerificationController;
use App\Http\Controllers\Api\vpaVerificationController;
use App\Http\Controllers\Api\dmtController;
use App\Http\Controllers\Api\merchantOnboardController;
use App\Http\Controllers\Api\bbpsController;
use App\Http\Controllers\Api\aepsController;
use App\Http\Controllers\Api\CreditCardController;
use App\Http\Controllers\PaycelPgController;
use App\Http\Controllers\PaydrionApiController;
use App\Http\Controllers\PaydrionUpiController;
use App\Http\Controllers\Api\aespIPContoller;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::post('/get/remittance',[App\Http\Controllers\XpresspayoutController::class,'getSender']);
Route::post('/add-beneficiary',[App\Http\Controllers\XpresspayoutController::class,'beneficiaryAdd']);
Route::post('/initiate-transaction',[App\Http\Controllers\XpresspayoutController::class,'sendPayout']);
Route::post('/get-transaction-status',[App\Http\Controllers\XpresspayoutController::class,'checkPayoutStatus']);
Route::post('/update-payout-status/{paymentId}', [App\Http\Controllers\XpresspayoutController::class, 'updatePayoutStatus']);



Route::prefix('cg/payout/v2')->group(function () {
    Route::post('/initiate', [PayoutV2Controller::class, 'initiate']);
    Route::post('/submit-otp', [PayoutV2Controller::class, 'submitOtp']);
    Route::post('/resend-otp', [PayoutV2Controller::class, 'resendOtp']);
    Route::post('/status', [PayoutV2Controller::class, 'status']);
    Route::post('/callback', [PayoutV2Controller::class, 'callback']); // For Passway server
    Route::post('/fetch-balance', [RemittanceController::class, 'fetchBalance']);

});


///////// Marchant Onbaording API////////////////////

        Route::post('/user/onboard/signup',[merchantOnboardController::class,'initiateSignup']);
        Route::post('/user/onboard/signup/verify',[merchantOnboardController::class,'initiateSignupVerify']);
        Route::post('/user/onboard/mobileChange',[merchantOnboardController::class,'MobileChangeInitiate']);
        Route::post('/user/onboard/mobileChange/verify',[merchantOnboardController::class,'MobileChangeInitiateVerify']);
        Route::post('/user/onboard/list',[merchantOnboardController::class,'merchantList']);
        Route::get('/v1/merchant/client-list', [merchantOnboardController::class, 'clientList']);


// ---------------------- DMT -----------------------------
        Route::post('/v1/dmt/BankDetails',[dmtController::class,'bankDetails']);
        Route::post('/v1/dmt/remitterProfile',[dmtController::class,'remitterProfile']);
        Route::post('/v1/dmt/remitterRegistration',[dmtController::class,'remitterRegistration']);
        Route::post('/v1/dmt/verifyRemitterRegistration',[dmtController::class,'verifyRemitterRegistration']);
        Route::post('/v1/dmt/remitterKyc',[dmtController::class,'remitterKyc']);
        Route::post('/v1/dmt/beneficiaryRegistration',[dmtController::class,'beneficiaryRegistration']);
        Route::post('/v1/dmt/verifyBeneficiaryRegistration',[dmtController::class,'verifyBeneficiaryRegistration']);
        Route::post('/v1/dmt/deleteBeneficiary',[dmtController::class,'deleteBeneficiary']);
        Route::post('/v1/dmt/verifyDeleteBeneficiary',[dmtController::class,'verifyDeleteBeneficiary']);
        Route::post('/v1/dmt/generateTransactionOtp',[dmtController::class,'generateTransactionOtp']);
        Route::post('/v1/dmt/bioAuthTransaction',[dmtController::class,'bioAuthTransaction']);
        Route::post('/v1/dmt/dmtTransaction',[dmtController::class,'dmtTransaction']);
        
         Route::post('/v1/account/verify',[dmtController::class,'accountVerify']);

         Route::post('/v2/account/verify',[accountVerificationController::class,'accountVerify']);


         Route::post('/v2/vpa/verify',[vpaVerificationController::class,'accountVerify']);

//AePS  API //

        Route::post('/v1/aeps/outletLoginStatus',[aepsController::class,'outletLoginStatus']);
        Route::post('/v1/aeps/outletLogin',[aepsController::class,'outletLogin']);
        Route::post('/v1/aeps/cashWithdrawal',[aepsController::class,'cashWithdrawal']);
        Route::post('/v1/aeps/balanceInquiry',[aepsController::class,'balanceInquiry']);
        Route::post('/v1/aeps/miniStatement',[aepsController::class,'miniStatement']);
        Route::get('/v1/aeps/banks',[aepsController::class,'aepsBanks']);

Route::post('/payout/v6/initiate', [App\Http\Controllers\mainAPIController::class, 'initiate']);
Route::post('upi/payout/v6/initiate', [App\Http\Controllers\mainAPIController::class, 'upi_initiate']);
Route::post('upi/payout/v6/status', [App\Http\Controllers\mainAPIController::class, 'upi_initiate_status']);

Route::post('payout/payout/v6/status', [App\Http\Controllers\mainAPIController::class, 'payout_initiate_status']);

Route::post('/logs/view', [App\Http\Controllers\LogController::class, 'viewLogs']);



Route::post('dynamic/pg/request',[pgController::class,'pay']);
Route::post('dynamic/pg/status', [pgController::class, 'status']);
Route::post('dynamic/pg/callback', [pgController::class, 'callback']);


Route::post('dynamic/v1/pg/request',[pg1Controller::class,'pay']);
Route::post('dynamic/v1/pg/status', [pg1Controller::class, 'status']);
Route::post('dynamic/v1/pg/callback', [pg1Controller::class, 'callback']);

Route::post('dynamic/v2/pg/request',[pg2Controller::class,'pay']);
Route::post('dynamic/v2/pg/status', [pg2Controller::class, 'status']);
Route::post('dynamic/v2/pg/callback', [pg2Controller::class, 'callback']);
Route::post('dynamic/v2/callback', [pg2Controller::class, 'pgcallback']);

Route::post('dynamic/pg/wrap',[pgController::class,'wrapPG']);

Route::post('pipe2/payout/initiate', [App\Http\Controllers\ResellerPayoutController::class, 'sendPayout']);
Route::post('pipe2/payout/status', [App\Http\Controllers\ResellerPayoutController::class, 'checkPayoutStatus']);
Route::get('pipe2/wallet/balance', [App\Http\Controllers\ResellerPayoutController::class, 'checkWalletBalance']);
Route::post('pipe2/payout/update-status/{paymentId}', [App\Http\Controllers\ResellerPayoutController::class, 'updatePayoutStatus']);
Route::post('pipe2/payout/callback', [App\Http\Controllers\ResellerPayoutController::class, 'handle']);


Route::post('/cd/v2/upipayout', [App\Http\Controllers\upipayoutController::class,'upipayout']);

Route::post('/payouts/upi/status', [upipayoutController::class, 'checkUpiPayoutStatus']); // 200/202/400/404/422/500 [web:29][web:43]

//callback URL for Payway UPI Payouts
Route::post('/upi/callback/handler', [App\Http\Controllers\mainAPIController::class, 'UpiCallback'])->name('upi.callback.handler');
Route::post('payout/callback/handler', [App\Http\Controllers\mainAPIController::class, 'PayoutCallback'])->name('payout.callback.handler');
Route::post('payout/callback/handler/stlm', [App\Http\Controllers\mainAPIController::class, 'PayoutCallbackSTLM'])->name('payout.callback.handler.stlm');



/*
|--------------------------------------------------------------------------
| BBPS API Routes (Reseller Ready)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    Route::prefix('bbps')->group(function () {

        // ✅ 1. Get Categories
        Route::get('/categories', [bbpsController::class, 'getCategory']);

        // ✅ 2. Get Billers by Category
        Route::post('/billers', [bbpsController::class, 'getBillers']);

        // ✅ 3. Get Biller Fields
        Route::post('/biller-fields', [bbpsController::class, 'getBillerFields']);

        // ✅ 4. Fetch Bill
        Route::post('/fetch-bill', [bbpsController::class, 'fetchBill']);

        // ✅ 5. Pay Bill
        Route::post('/pay-bill', [bbpsController::class, 'payBill']);

    });

});

Route::post('bbps/merchant',[bbpsController::class,'createMerchant']);

//credx pay bank list
        Route::post('/v1/list/BankDetails',[XpresspayoutController::class,'bankDetails']);

        Route::post('/v1/bank1/aeps/stlm',[aepsStlmController::class,'aepsStlm']);

//summary 
Route::get('v1/all/summary',[summaryCotroller::class,'summary']);
Route::post('v1/all/summary/business/list',[summaryCotroller::class,'business']);
Route::get('/summary/export', [summaryCotroller::class, 'export']);

//aeronopay callback

Route::post('v1/aeronpay/callback',[aeronpayCallBackController::class,'callBackManger']);

//Aeronpay UPI

Route::post('/cd/v3/upi/txn', [mainAPIController::class,'upi_initiateV2']);
Route::post('/cd/v3/upi/txn/user', [upiaeronpayController::class,'upipayout']);

Route::post('/payouts/upi/v3/status', [upiaeronpayController::class, 'checkUpiPayoutStatus']); // 200/202/400/404/422/500 [web:29][web:43]

Route::post('/payouts/imps/status', [XpressaeronpayController::class, 'checkPayoutStatus']); // 200/202/400/404/422/500 [web:29][web:43]


//aeronpay Payout IMPS
Route::post('/imps/initiate-transaction',[App\Http\Controllers\mainAPIController::class,'initiateV2']);
Route::post('/imps/initiate/txn',[App\Http\Controllers\XpressaeronpayController::class,'sendPayout']);


//AEPS v2
Route::prefix('aeps/v2')->group(function () {

    Route::post('/create-merchant', [aepsV2Controller::class, 'createMerchant']);
    Route::post('/merchant-list', [aepsV2Controller::class, 'merchantList']);

    Route::post('/login-status', [aepsV2Controller::class, 'loginStatus']);
    Route::post('/aeps-login', [aepsV2Controller::class, 'aepsLogin']);

    Route::post('/aeps-payment', [aepsV2Controller::class, 'aepsPayment']);
});


// routes/web.php

use App\Http\Controllers\PaycelCashfreestdController;

Route::post('/paycel/response', [PaycelCashfreestdController::class, 'handleResponse']);

//paycel pg
Route::post('dynamic/pg/request',[PaycelPgController::class,'pay']);
Route::post('dynamic/pg/status/{referenceId}', [PaycelPgController::class, 'checkStatus']);
Route::post('dynamic/paycel/pg/callback', [PaycelPgController::class, 'callback']);

//credit card bill pay inspay
Route::post('v1/credit-card/fetch', [CreditCardController::class, 'billFetch']);
Route::post('v1/credit-card/pay', [CreditCardController::class, 'billPay']);
Route::post('v1/credit-card/status', [CreditCardController::class, 'checkStatus']);


Route::get('/cc-bill-bank-list', [CreditCardController::class, 'bankList']);

//imps paydrion
Route::post('v1/imps-payout', [PaydrionApiController::class, 'impsPayout']);
Route::post('v1/check-payout-status', [PaydrionApiController::class, 'checkPayoutStatus']);

//imps paydrion
Route::post('v1/upi-payout', [PaydrionUpiController::class, 'upiPayout']);
Route::post('v1/check-upi-status', [PaydrionUpiController::class, 'checkPayoutStatus']);


//Aeps iPay
Route::prefix('aeps')->group(function () {

    Route::post('/merchant-onboarding', [aespIPContoller::class, 'merchantOnboarding']);

    Route::get('/merchant-kyc-status/{kid}', [aespIPContoller::class, 'merchantKycStatus']);

    Route::post('/2fa', [aespIPContoller::class, 'twoFactorAuth']);

    Route::post('/cash-withdrawal', [aespIPContoller::class, 'cashWithdrawal']);

    Route::post('/balance-enquiry', [aespIPContoller::class, 'balanceEnquiry']);

    Route::post('/mini-statement', [aespIPContoller::class, 'miniStatement']);

    Route::post('/transaction-status', [aespIPContoller::class, 'transactionStatus']);
});

//ipay payout
use App\Http\Controllers\Api\PayoutController;

Route::prefix('payout')->group(function () {

    Route::post('/contact/create', [PayoutController::class, 'createContact']);

    Route::get('/contact/{contactId}', [PayoutController::class, 'getContact']);

    Route::post('/order/create', [PayoutController::class, 'createOrder']);
});