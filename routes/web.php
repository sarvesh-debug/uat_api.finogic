<?php

use App\Http\Controllers\Admin\apiControllController;
use App\Http\Controllers\eKycController;
use App\Http\Controllers\pgController;
use App\Http\Controllers\summaryCotroller;
use App\Http\Controllers\upiaeronpayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TrackController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\RemittanceController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\superAdminController;
use App\Http\Controllers\Api\PayoutV2Controller;
use App\Http\Controllers\Api\bbpsController;

use App\Http\Controllers\Api\commissionController;
use App\Http\Controllers\ManulFundController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\Auth\ResetPasswordController;

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Admin\RemittanceController as AdminRemittanceController;

// use App\Http\Controllers\UserForgotPasswordController;

// use App\Http\Controllers\UserResetPasswordController;


use App\Http\Controllers\User\UserForgotPasswordController;
use App\Http\Controllers\User\UserResetPasswordController;

use App\Http\Controllers\Admin\FundRequestController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\upipayoutController;
use App\Http\Controllers\RefundController;
use App\Http\Controllers\pg1Controller;
use App\Http\Controllers\pg2Controller;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\manulaUtrUpationController;
use App\Http\Controllers\creditDebitController;

Route::get('/admin/login', [AuthController::class, 'loginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login');


Route::get('/test-status', function () {
    \Artisan::call('upi:auto-status');
    return redirect()->Route('admin.upi.reports');
})->name('cron.job');


Route::get('/test-status/payout', function () {
    \Artisan::call('payout:auto-status');
    return redirect()->Route('transaction.admin');
})->name('cron.job.payout');

Route::get('/test-status/payout/stlm', function () {
    \Artisan::call('payoutaeps:auto-status');
    return redirect()->Route('transaction.admin.stlm');
})->name('cron.job.payoutaeps');

Route::get('/test-status/pg', function () {
    \Artisan::call('pg:auto-status');
    return redirect()->Route('pg.report.admin');
})->name('cron.job.pg');


Route::get('/test-status/pg/p1', function () {
    \Artisan::call('pg1:auto-status');
    return redirect()->Route('pg.report.admin.pipe1');
})->name('cron.job.pg.p1');

Route::get('/test-status/pg/p2', function () {
    \Artisan::call('pg2:auto-status');
    return redirect()->Route('pg.report.admin.pipe2');
})->name('cron.job.pg.p2');
// OTP test page (bypasses login)
Route::get('/test-otp', [OtpController::class, 'test'])->name('otp.test');

// OTP verification (POST)
Route::post('/password/otp', [OtpController::class, 'verify'])->name('otp.verify'); // verify OTP
Route::any('/password/otp/resend', [OtpController::class, 'resend'])->name('otp.resend'); 
// Resend OTP
Route::get('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');

Route::get('/forgot', [AuthController::class, 'forgotForm'])->name('forgot.form');
Route::post('/forgot', [AuthController::class, 'sendResetLink'])->name('forgot.send');
 Route::get('remitter/kyc/form', [RemittanceController::class, 'kycForm'])->name('remittances.kyc.form');
 Route::get('remitter/profile', [RemittanceController::class, 'profile'])->name('remittances.profile');
 Route::post('remitter/kyc', [RemittanceController::class, 'kycStore'])->name('remittances.kyc.store');


Route::middleware('auth')->group(function () {
   Route::get('/admin/dashboard',[DashboardController::class,'index'])
->middleware('auth')
->name('admin.dashboard');

    Route::resource('businesses', BusinessController::class);
    Route::get('/add-bank', [BankController::class, 'index'])->name('banks.create');
    Route::post('/add-bank', [BankController::class, 'store'])->name('banks.store');
    Route::post('/store', [BankController::class, 'store'])->name('banks.store');
    Route::put('/update/{id}', [BankController::class, 'update'])->name('banks.update');
    Route::delete('/delete/{id}', [BankController::class, 'destroy'])->name('banks.destroy');
    Route::get('/fund/request', [BankController::class, 'fundRequest'])->name('banks.fund.request');
    Route::post('/fund/request/{id}/accept', [BankController::class, 'fundRequestAccept'])->name('admin.fund.approve');
    Route::post('/fund/request/{id}/reject', [BankController::class, 'fundRequestReject'])->name('admin.fund.reject');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


Route::get('/remittances/forgot', [RemittanceController::class, 'forgot'])->name('remittances.forgot');
// Route::get('/remittances/add-fund', function () {
//    dd(auth('remittance')->user());
// })->name('remittances.add.fund');
 Route::get('/remittances/add-fund', [RemittanceController::class, 'addFund'])->name('remittances.add.fund');
 Route::post('/remittances/add-fund', [RemittanceController::class, 'addFundStore'])->name('remittances.add.fund.post');
 Route::get('/remittances/add-fund/history', [RemittanceController::class, 'fundHistory'])->name('remittances.add.fund.his');
 Route::get('/remittances/charges', [RemittanceController::class, 'charges'])->name('remittances.charges');

    //Route::get('/track/transaction', [TrackController::class, 'txnTrack'])->name('track.transaction.rem');

    Route::get('/track/transaction', [TrackController::class, 'txnTrack'])
    ->name('track.transaction.rem');

 Route::get('/track/transaction/dmt', [TrackController::class, 'txnDmt'])->name('track.dmt.rem');
 Route::get('/track/transaction/aeps', [TrackController::class, 'txnAeps'])->name('track.aeps.rem');
 Route::get('/track/transaction/v2/aeps', [TrackController::class, 'txnAepsv2'])->name('track.aeps.rem.v2');

Route::get('/track/transaction/export/csv', [TrackController::class, 'exportCsv'])
    ->name('track.transaction.export.csv');

Route::get('/track/transaction/export/excel', [TrackController::class, 'exportExcel'])
    ->name('track.transaction.export.excel');

Route::get('/track/dmt/export/csv', [TrackController::class, 'exportCsvDMT'])
    ->name('track.dmt.export.csv');

Route::get('/track/transaction/export/excel', [TrackController::class, 'exportExcel'])
    ->name('track.transaction.export.excel');


      // DMT
    Route::get('/dmt-reports', [TrackController::class, 'dmtIndex'])
        ->name('admin.dmt.reports');

    Route::get('/dmt-reports/export', [TrackController::class, 'dmtExport'])
        ->name('admin.dmt.reports.export');

    Route::get('/bbps-reports', [bbpsController::class, 'bbpsIndex'])
        ->name('admin.bbps.reports');

    Route::get('/bbps-reports/export', [bbpsController::class, 'bbpsExport'])
        ->name('admin.bbps.reports.export');

    //cms
      Route::get('/cms-reports', [TrackController::class, 'cmsIndex'])
        ->name('admin.cms.reports');

    Route::get('/cms-reports/export', [TrackController::class, 'cmsExport'])
        ->name('admin.cms.reports.export');

    // AEPS
    Route::get('/aeps-reports', [TrackController::class, 'aepsIndex'])
        ->name('admin.aeps.reports');

    Route::get('/aeps-reports/export', [TrackController::class, 'aepsExport'])
        ->name('admin.aeps.reports.export');

     // AEPS
    Route::get('v2/aeps-reports', [TrackController::class, 'aepsIndexv2'])
        ->name('admin.aeps.reports.v2');

    Route::get('v2/aeps-reports/export', [TrackController::class, 'aepsExport'])
        ->name('admin.aeps.reports.export.v2');




//XpressPayout Status Updation

Route::post('/xpressPayout/api/status',[TrackController::class,'chkPayoutApiStatus'])->name('chkPayoutApiStatus');
Route::post('/xpressPayout/api/status/v2',[TrackController::class,'chkPayoutApiStatusV2'])->name('chkPayoutApiStatusv2');
Route::post('/upi/api/status',[TrackController::class,'chkUpiApiStatus'])->name('chkUpiApiStatus');
Route::post('/pg/api/status',[TrackController::class,'chkPGApiStatus'])->name('chkPGApiStatus');
Route::post('/pg/api/status/pipe1',[TrackController::class,'chkPGApiStatusp1'])->name('chkPGApiStatus.pipe1');
Route::post('/pg/api/status/pipe2',[TrackController::class,'chkPGApiStatusp2'])->name('chkPGApiStatus.pipe2');


Route::get('/track/transaction/admin', [TrackController::class, 'txnTrackAdmin'])->name('transaction.admin');
Route::get('/track/transaction/stlm/admin', [TrackController::class, 'txnTrackAdminSTLM'])->name('transaction.admin.stlm');
Route::post('/track/transaction/update', [TrackController::class, 'txnAction'])->name('transactions.update');
Route::get('payment/gateway/report',[pgController::class,'pgReport'])->name('pg.report');
Route::get('payment/gateway/report/export',[pgController::class,'pgExport'])->name('pg.report.export');


Route::get('/track/transaction/v2/admin', [TrackController::class, 'txnTrackAdminV2'])->name('transaction.adminV2');


//
Route::get('payment/gateway/report/ad',[pgController::class,'pgReportAdmin'])->name('pg.report.admin');
Route::get('payment/gateway/report/export/ad',[pgController::class,'pgExportAdmin'])->name('pg.report.export.admin');

//
Route::get('payment/gateway/report/p1',[pg1Controller::class,'pgReport'])->name('pg1.report');
Route::get('payment/gateway/report/export/p1',[pg1Controller::class,'pgExport'])->name('pg1.report.export');
//
Route::get('payment/gateway/report/ad/pipe1',[pg1Controller::class,'pgReportAdmin'])->name('pg.report.admin.pipe1');
Route::get('payment/gateway/report/export/ad/pipe1',[pg1Controller::class,'pgExportAdmin'])->name('pg.report.export.admin.pipe1');


//pg2
Route::get('payment/gateway/report/p2',[pg2Controller::class,'pgReport'])->name('pg2.report');
Route::get('payment/gateway/report/export/p2',[pg2Controller::class,'pgExport'])->name('pg2.report.export');
//
Route::get('payment/gateway/report/ad/pipe2',[pg2Controller::class,'pgReportAdmin'])->name('pg.report.admin.pipe2');
Route::get('payment/gateway/report/export/ad/pipe2',[pg2Controller::class,'pgExportAdmin'])->name('pg.report.export.admin.pipe2');

//admin Ledger
 Route::get('/ledger', [\App\Http\Controllers\AdminLedgerController::class, 'index'])
        ->name('admin.ledger.index');

    Route::get('/ledger/export', [\App\Http\Controllers\AdminLedgerController::class, 'export'])
        ->name('admin.ledger.export');
//docs
Route::get('/api/docs', [TrackController::class, 'docs'])->name('api.docs');
Route::get('/api/docs/{slug}', [TrackController::class, 'serviceDocs'])
    ->name('developer.service.docs');
// new 
Route::get('/start_txn', [RemittanceController::class, 'start_txn'])->name('start_txn');
// send money
Route::post('/send_money', [RemittanceController::class, 'send_money'])->name('send_money');
// web.php
Route::get('/add_beneficiary/{reference_Key}', [RemittanceController::class, 'add_beneficiary'])->name('add_beneficiary');
Route::post('/add_beneficiary', [RemittanceController::class, 'add_beneficiary_store'])->name('add_beneficiary.store');
Route::get('/certificate', [RemittanceController::class, 'certificate'])->name('certificate');
// Route::middleware(['web','auth:remittance'])->group(function () {
//     // Route::get('/remittances/dashboard', function () {
//     //     return view('users.dashboard');
//     // })->name('remittances.dashboard');
// });
//     Route::get('/remittances/dashboard', [AuthController::class, 'dashboard'])->name('remittances.dashboard');
// new 
Route::get('/start_txn', [RemittanceController::class, 'start_txn'])->name('start_txn');
Route::get('/add_beneficiary', [RemittanceController::class, 'add_beneficiary'])->name('add_beneficiary');
Route::get('/certificate', [RemittanceController::class, 'certificate'])->name('certificate');

//leger

//reports for admin

    Route::get('admin/upi-reports', [upipayoutController::class, 'upiReport'])->name('admin.upi.reports');

    Route::get('admin/upi-reports/export',[upipayoutController::class, 'exportCsv'])->name('admin.upi.reports.export');

    Route::get('admin/v2/upi-reports', [upiaeronpayController::class, 'upiReport'])->name('admin.upi.v2.reports');

    Route::get('admin/v2/upi-reports/export',[upiaeronpayController::class, 'exportCsv'])->name('admin.upi.v2.reports.export');
Route::middleware(['web','auth:remittance'])->group(function () {
    // Route::get('/remittances/dashboard', function () {
    //     return view('users.dashboard');
    // })->name('remittances.dashboard');

    Route::get('/remittances/dashboard', [AuthController::class, 'dashboard'])->name('remittances.dashboard');
    // Route::resource('remittances', RemittanceController::class);

    Route::post('/remittances/logout', [RemittanceController::class, 'logout'])->name('remittances.logout');
});

// web.php
Route::get('/verify-otp', [RemittanceController::class, 'showOtpForm'])->name('remittances.verifyOtpForm');
Route::post('/verify-otp', [RemittanceController::class, 'verifyOtp'])->name('remittances.verifyOtp');

//admin
// routes/web.php
Route::get('/admin/verify-otp', [AuthController::class, 'showOtpForm'])->name('admin.verifyOtpForm');
Route::post('/admin/verify-otp', [AuthController::class, 'verifyOtp'])->name('admin.verifyOtp');

   // Show Login Page
Route::get('/remittance/login', [RemittanceController::class, 'loginf'])->name('remittances.login');

// Handle Login Submit
Route::post('/remittance/login', [RemittanceController::class, 'login'])->name('remittances.login.post');

// Show Signup Page
Route::get('/remittances/create', [RemittanceController::class, 'create'])->name('remittances.create');

// Handle Signup Form
Route::post('/remittances', [RemittanceController::class, 'store'])->name('remittances.store');

// (Optional) make homepage go to login
Route::get('/', fn() => redirect()->route('remittances.login'));


Route::get('/superadmin/login', [superAdminController::class, 'loginForm'])->name('superadmin.login.form');
Route::post('/superadmin/login/store',[superAdminController::class,'login'])->name('superadmin.login.store');
Route::post('/superadmin/login/out',[superAdminController::class,'logout'])->name('superadmin.login.out');

Route::get('/superadmin/dashboard',[superAdminController::class,'dashboard'])->name('superadmin.dashboard');

//adminrise
Route::get('admin/rise/fund',[BankController::class,'loadFund'])->name('admin.addFund');

Route::post('admin/fund.store',[BankController::class,'loadFundStore'])->name('admin.fundStore');

Route::get('admin.fund/request',[superAdminController::class,'fundRequest'])->name('admin.fundData');

Route::post('/fund/request/{id}/accept/admin', [superAdminController::class, 'fundRequestAccept'])->name('superadmin.fund.approve');
Route::post('/fund/request/{id}/reject/admin', [superAdminController::class, 'fundRequestReject'])->name('superadmin.fund.reject');

Route::get('/superadmin/txn/all', [superAdminController::class, 'allTxn'])->name('superadmin.txn.all');
Route::get('/superadmin/txn/all/admin', [superAdminController::class, 'allTxnAdmin'])->name('superadmin.txn.all.admin');

Route::get('users/list',[superAdminController::class,'userList'])->name('superadmin.user.list');



Route::get('/superadmin/forgot', [superAdminController::class, 'forgotPasswordForm'])->name('superadmin.forgot.form');
Route::post('/superadmin/forgot', [superAdminController::class, 'sendResetLink'])->name('superadmin.forgot.send');

Route::post('/otp/verify', [superAdminController::class, 'verify'])->name('superadmin.otp.verify');
Route::post('password/reset', [superAdminController::class, 'resetPassword'])->name('superadmin.password.reset');




// admin 


Route::get('forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('forgot.send');

Route::get('reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password', [ResetPasswordController::class, 'reset'])->name('password.update');


Route::get('/business/rejected', function () {
    return view('businesses.rejected');
})->name('business.rejected');



Route::post('/admin/remittence/{remId}/generate-key', [AdminRemittanceController::class, 'generateKey']);

Route::post('/admin/remittence/{id}/register-ip',[AdminRemittanceController::class,'registerIp']);

Route::post('/admin/remittence/{id}/callback-url',[AdminRemittanceController::class,'saveCallbackUrl']);

Route::post('/admin/remittence/{id}/toggle-service', [AdminRemittanceController::class, 'toggleService'])->name('remittence.toggleService');


 Route::post('/admin/remittances/{id}/lock-amount', [AdminRemittanceController::class, 'lockRemitterAmount'])
    ->name('remittances.lockAmount');

    Route::get('/admin/remittance/locked-amounts', [AdminRemittanceController::class, 'lockTransactionsList'])
    ->name('admin.lockTransactionsList');

//refund
Route::post('/refund/process', [RefundController::class, 'processRefund'])->name('refund.process');
Route::post('/refund/process/v2', [RefundController::class, 'processRefundV2'])->name('refund.processv2');
Route::post('/refund/process/upi', [RefundController::class, 'processRefundUPI'])->name('refund.process.upi');
Route::post('/refund/process/upi/v2', [RefundController::class, 'processRefundUPIv2'])->name('refund.process.upiv2');
 Route::get('admin/refund-reports', [RefundController::class, 'refundReportAdmin'])->name('admin.refund.reports');
    Route::get('admin/refund-reports/page', [RefundController::class, 'refundReportAdminPage'])->name('admin.refund.reports.page');
    Route::get('admin/refund-reports/export', [RefundController::class, 'refundExport'])->name('admin.refund.reports.export');

//manul utl update
Route::post('/manual/utr/updation', [manulaUtrUpationController::class, 'manualProcess'])->name('manual.process.v1');

//user refunds
 Route::get('user/refund-reports', [RefundController::class, 'refundReportUser'])->name('user.refund.reports');


Route::post('/admin/release-locked-amount', [AdminRemittanceController::class, 'releaseLockedAmount'])->name('admin.releaseLockedAmount');


Route::get('/admin/remittances', [AdminRemittanceController::class, 'index'])->name('admin.remittances.index');
Route::get('admin/remittances/{id}', [AdminRemittanceController::class, 'show'])->name('admin.remittances.show');

Route::post('/admin/remittances/{id}/assign-package', [AdminRemittanceController::class, 'assignPackage'])->name('remittances.assign-package');

 // Approve / Reject actions
Route::get('/remittances/{id}/approve', [AdminRemittanceController::class, 'approve'])->name('remittances.approve');
Route::get('/remittances/{id}/reject', [AdminRemittanceController::class, 'reject'])->name('remittances.reject');


Route::post('/remittances/{id}/reject', [AdminRemittanceController::class, 'reject'])->name('remittances.reject');
Route::post('/admin/remittances/{id}/reject', [AdminRemittanceController::class, 'reject'])->name('remittances.reject');

Route::view('/terms', 'terms');
Route::view('/privacy', 'privacy');

// Route::prefix('admin')->name('admin.')->group(function() {
    // Rejected remittances
    Route::get('/remittances/rejected', [AdminRemittanceController::class, 'rejected'])->name('remittances.rejected');
// });

//ledger
Route::get('account/ledger',[LedgerController::class,'index'])->name('ledger.index');
Route::get('/ledger/export', [LedgerController::class, 'export'])->name('ledger.export');

// Route::prefix('admin')->name('admin.')->group(function () {
//     Route::get('/remittances/rejected', [RemittanceController::class, 'rejected'])->name('remittances.rejected');
// });

 Route::get('rem/upiHistory',[upipayoutController::class,'upiHistory'])->name('upiHistory');
    Route::get('/upi/export/csv', [upipayoutController::class, 'upiExportCsv'])
    ->name('upi.export.csv');


Route::get('password/otp', [OtpController::class, 'test'])->name('user.remittances.otp');



Route::get('commission/package/list',[commissionController::class,'indexPck'])->name('admin.package.list');

Route::post('payout/v2/callback',[PayoutV2Controller::class,'callback'])->name('payout.v2.callback');


// user panel forget pass 

Route::get('user/forgot-password', [UserForgotPasswordController::class, 'showForm'])->name('user.forgot.password');
Route::post('user/forgot-password', [UserForgotPasswordController::class, 'sendResetLink'])->name('user.forgot.password.send');

Route::get('user/reset-password/{token}', [UserResetPasswordController::class, 'showResetForm'])->name('user.reset.password.form');
Route::post('user/reset-password', [UserResetPasswordController::class, 'resetPassword'])->name('user.reset.password');



Route::get('fund-requests', [FundRequestController::class, 'index'])->name('admin.fundrequests.index');

 Route::get('/manul-fund/create', [ManulFundController::class, 'create'])->name('manul_fund.create');
    Route::post('/manul-fund/store', [ManulFundController::class, 'store'])->name('manul_fund.store');
       Route::get('/manul-fund', [ManulFundController::class, 'index'])->name('manul_fund.index');


       Route::resource('packages', PackageController::class);
       Route::resource('commission', commissionController::class);
       Route::get('packages/{id}/comm', [commissionController::class, 'index'])->name('packages.comm');


       Route::get('/remittances/{id}/kyc-status', [RemittanceController::class, 'status'])->name('remittances.kyc.status');

       Route::get('/commission-form', [bbpsController::class, 'showForm'])->name('commission-form');
    Route::get('/commission-form/bbps', [bbpsController::class, 'getCommissionList'])->name('commission-form.newbbps');
    Route::post('/bbps/new/charges/save', [bbpsController::class, 'saveBbpsCharges'])->name('bbps.charges.save.new');
//instant pay 
use App\Http\Controllers\InstantPayBalanceController;

Route::get('/instantpay-balance', [InstantPayBalanceController::class,'checkBalance']);

Route::resource('apis', apiControllController::class);

Route::get('/summary-report', [summaryCotroller::class, 'index'])->name('summary.view');
Route::get('/user/summary-report', [summaryCotroller::class, 'indexUser'])->name('summary.view.user');

//eKyc Apply
Route::get('merchant/ekyc/docs/{id}',[eKycController::class,'applyEkyc'])->name('merchnat.ekyc');
Route::get('/ekyc/{id}/pdf', [eKycController::class, 'downloadEkycPdf'])
    ->name('ekyc.pdf');

//api switching
Route::resource('apisSwitch', ApiController::class);

Route::post('/apis/update-status', [ApiController::class, 'updateStatus'])->name('apis.updateStatus');


Route::get('/mantra/test', function () {
    return view('mantra');
});

//credit debit

Route::get('credit/debit/file',[creditDebitController::class,'index'])->name('credit.debit.v1');
Route::post('/merchant/credit', [creditDebitController::class, 'credit'])->name('merchant.credit');
Route::post('/merchant/debit', [creditDebitController::class, 'debit'])->name('merchant.debit');
Route::get('/reports', [creditDebitController::class, 'reports'])->name('reports.v1');