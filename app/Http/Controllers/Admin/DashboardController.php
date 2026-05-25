<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Remittance;
use Illuminate\Http\Request;

class DashboardController extends Controller
{


    public function index()
    {

        $filter = request()->get('filter', 'today');

        // Base Query for xpresspayout
$xpressQuery = DB::table('xpresspayout')
    ->select('status', 'amount', 'created_at');

// Base Query for upipayout
$upiQuery = DB::table('upipayout')
    ->select('status', 'amount', 'created_at');

// Union both tables
$baseQuery = $xpressQuery->unionAll($upiQuery);

// Wrap union for further filtering
$baseQuery = DB::query()->fromSub($baseQuery, 'payouts');

// Date Filters
if ($filter == 'today') {
    $baseQuery->whereDate('created_at', Carbon::today());
} elseif ($filter == 'yesterday') {
    $baseQuery->whereDate('created_at', Carbon::yesterday());
} elseif ($filter == 'month') {
    $baseQuery->whereMonth('created_at', Carbon::now()->month)
        ->whereYear('created_at', Carbon::now()->year);
}

// Transaction counts
$txnPending = (clone $baseQuery)->where('status', 'pending')->count();
$txnFailed  = (clone $baseQuery)->where('status', 'failed')->count();
$txnSuccess = (clone $baseQuery)->where('status', 'success')->count();
$txnRefunded = (clone $baseQuery)->where('status', 'Refunded')->count();

// Amount sums
$txnSuccessSum = (clone $baseQuery)
    ->where('status', 'success')
    ->sum('amount');

$txnFailedSum = (clone $baseQuery)
    ->where('status', 'failed')
    ->sum('amount');

$txnPendingSum = (clone $baseQuery)
    ->where('status', 'pending')
    ->sum('amount');
$txnRefundedSum=(clone( $baseQuery))
    ->where('status','Refunded')
    ->sum('amount');

        // All API Users (remittances table)
        $apiUsersCount = DB::table('remittances')->count();

        $activeUsers   = DB::table('remittances')->where('status', 'success')->count();
        $deactiveUsers = DB::table('remittances')->where('status', 'rejected')->count();
        $pendingUsers = DB::table('remittances')->where('status', 'pending')->count();
        $newUsers      = DB::table('remittances')->whereDate('created_at', today())->count();

        $fundRequests = DB::table('rem_fundrequest')->count();

        $kycPending = DB::table('remittances')->where('isKyc', 0)->count();

        $totalBalance = DB::table('remittances')->sum('amount');
        $totalLockbalance = DB::table('remittances')->sum('lockBalance');
        $availableBalance = $totalBalance + $totalLockbalance;


        // =============================
        // Latest 5 Transactions
        // =============================

        $xpress = DB::table('xpresspayout')
            ->select(
                'remId',
                'payment_id as txn_id',
                'amount',
                'status',
                'created_at',
                DB::raw("'XPRESS' as service")
            );

        $upi = DB::table('upipayout')
            ->select(
                'remId',
                'payment_id as txn_id',
                'amount',
                'status',
                'created_at',
                DB::raw("'UPI' as service")
            );

        $dmt = DB::table('dmt_transactions')
            ->select(
                'merchant_id as remId',
                'externalRef as txn_id',
                'amount',
                'status',
                'created_at',
                DB::raw("'DMT' as service")
            );

        $aeps = DB::table('merchant_aeps_transactions')
            ->select(
                'merchant_id as remId',
                'orderid as txn_id',
                'transaction_amount as amount',
                'status',
                'created_at',
                DB::raw("'AEPS' as service")
            );


        $latestTransactions = $xpress
            ->unionAll($upi)
            ->unionAll($dmt)
            ->unionAll($aeps)
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // =============================
// Fund Request Analytics
// =============================

$fundQuery = DB::table('rem_fundrequest');

if ($filter == 'today') {
    $fundQuery->whereDate('created_at', Carbon::today());
} elseif ($filter == 'yesterday') {
    $fundQuery->whereDate('created_at', Carbon::yesterday());
} elseif ($filter == 'month') {
    $fundQuery->whereMonth('created_at', Carbon::now()->month)
              ->whereYear('created_at', Carbon::now()->year);
}

// counts
$fundPending = (clone $fundQuery)->where('status',0)->count();
$fundAccept  = (clone $fundQuery)->where('status',1)->count();
$fundReject  = (clone $fundQuery)->where('status',2)->count();

// sums
$fundPendingSum = (clone $fundQuery)->where('status',0)->sum('amount');
$fundAcceptSum  = (clone $fundQuery)->where('status',1)->sum('amount');
$fundRejectSum  = (clone $fundQuery)->where('status',2)->sum('amount');

        $pendingFund=DB::table('rem_fundrequest')->where('status',0)->count();


        //return $pendingFund;
      

        return view('dashboard', compact(
            'txnPending',
            'txnFailed',
            'txnSuccess',
            'txnRefunded',
            'apiUsersCount',
            'activeUsers',
            'deactiveUsers',
            'pendingUsers',
            'newUsers',
            'fundRequests',
            'kycPending',
            'totalBalance',
            'totalLockbalance',
            'availableBalance',
            'txnSuccessSum',
            'txnFailedSum',
            'latestTransactions',
            'txnPendingSum',
            'txnRefundedSum',
            'filter',
            'pendingFund',
            'fundPending',
            'fundAccept',
            'fundReject',
            'fundPendingSum',
            'fundAcceptSum',
            'fundRejectSum',
        ));
    }

}