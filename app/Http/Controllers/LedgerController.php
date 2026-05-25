<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
class LedgerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Common Query Builder (Used in index & export)
    |--------------------------------------------------------------------------
    */
    private function getTransactions($remId, $from, $to, $type)
    {
        /*
        |-----------------------------
        | 1️⃣ PG
        |-----------------------------
        */
        $pg = DB::table('pgmanage')
            ->where('remId', $remId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw("
                CAST(txnId AS CHAR) COLLATE utf8mb4_unicode_ci as reference,
                amount,
                charges,
                tds,
                commission,
                closingBalance as balance,
                UPPER(status) COLLATE utf8mb4_unicode_ci as status,
                created_at,
                'PG' COLLATE utf8mb4_unicode_ci as type
            ");

        /*
        |-----------------------------
        | 2️⃣ PAYOUT
        |-----------------------------
        */
        $payout = DB::table('xpresspayout')
            ->where('remId', $remId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw("
                CAST(payment_id AS CHAR) COLLATE utf8mb4_unicode_ci as reference,
                amount,
                charge as charges,
                tds,
                0 as commission,
                closing_balance as balance,
                UPPER(status) COLLATE utf8mb4_unicode_ci as status,
                created_at,
                'PAYOUT' COLLATE utf8mb4_unicode_ci as type
            ");

            $payout = DB::table('upipayout')
            ->where('remId', $remId)
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw("
                CAST(payment_id AS CHAR) COLLATE utf8mb4_unicode_ci as reference,
                amount,
                charge as charges,
                tds,
                0 as commission,
                closing_balance as balance,
                UPPER(status) COLLATE utf8mb4_unicode_ci as status,
                created_at,
                'PAYOUT' COLLATE utf8mb4_unicode_ci as type
            ");

        /*
        |-----------------------------
        | 3️⃣ FUND REQUEST (IN_PG)
        |-----------------------------
        */
        $fund = DB::table('rem_fundrequest')
            ->where('rid', $remId) // CORRECT FIELD
            ->where('status', 1)          // Only success
            ->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59'])
            ->selectRaw("
                CAST(rid AS CHAR) COLLATE utf8mb4_unicode_ci as reference,
                amount,
                charges,
                tds,
                0 as commission,
                closingBalance as balance,
                'SUCCESS' COLLATE utf8mb4_unicode_ci as status,
                created_at,
                'IN_PG' COLLATE utf8mb4_unicode_ci as type
            ");

        $merged = $pg->unionAll($payout)->unionAll($fund);

        $query = DB::query()->fromSub($merged, 't');

        if ($type !== 'ALL') {
            $query->where('type', $type);
        }

        return $query->orderBy('created_at', 'asc')->get();
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX PAGE
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $remId = Auth::guard('remittance')->user()->remId;

    $fromDate = $request->from_date;
    $toDate   = $request->to_date;
    $txnId    = $request->txn_id;
    $service  = $request->service;

    // Date range full-day support
    if ($fromDate && $toDate) {
        $fromDate = Carbon::parse($fromDate)->startOfDay();
        $toDate   = Carbon::parse($toDate)->endOfDay();
    }

    /*
    |--------------------------------------------------------------------------
    | 1️⃣ Account Verification (OUT)
    |--------------------------------------------------------------------------
    */

    $avQuery = DB::table('account_verifications')
        ->where('remId', $remId);

    if ($fromDate && $toDate) {
        $avQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $avQuery->where('remId', $txnId); // correct txn column
    }

    $av = $avQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'txn_id' => $item->remIds ?? '-',
            'service_name' => 'Account Verification-'.$item->type,
            'amount' => 0,
            'charges' => $item->charges ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->opbalance ?? 0,
            'closing_balance' => $item->clbalance ?? 0,
            'created_at' => $item->created_at,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | 2️⃣ XpressPayout (OUT)
    |--------------------------------------------------------------------------
    */

    $xpQuery = DB::table('xpresspayout')
        ->where('remId', $remId);

    if ($fromDate && $toDate) {
        $xpQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $xpQuery->where('payment_id', $txnId);
    }

    $xp = $xpQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'txn_id' => $item->payment_id ?? '-',
            'service_name' => 'XpressPayout',
            'amount' => $item->amount ?? 0,
            'charges' => $item->charge ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->opening_balance ?? 0,
            'closing_balance' => $item->closing_balance ?? 0,
            'created_at' => $item->created_at,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | 3️⃣ UPI Payout (OUT)
    |--------------------------------------------------------------------------
    */

    $upiQuery = DB::table('upipayout')
        ->where('remId', $remId);

    if ($fromDate && $toDate) {
        $upiQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $upiQuery->where(function ($q) use ($txnId) {
            $q->where('payment_id', $txnId)
              ->orWhere('payment_id', $txnId);
        });
    }

    $upi = $upiQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'txn_id' => $item->txn_id ?? $item->payment_id ?? '-',
            'service_name' => 'UPI Payout',
            'amount' => $item->amount ?? 0,
            'charges' => $item->charge ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->opening_balance ?? 0,
            'closing_balance' => $item->closing_balance ?? 0,
            'created_at' => $item->created_at,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | 4️⃣ Fund Request (IN)
    |--------------------------------------------------------------------------
    */

    $rfQuery = DB::table('rem_fundrequest')
        ->where('rid', $remId)
        ->where('status',1);

    if ($fromDate && $toDate) {
        $rfQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $rfQuery->where('rid', $txnId);
    }

    $rf = $rfQuery->get()->map(function ($item) {
        return (object)[
            'type' => "IN",
            'txn_id' => $item->remIds ?? '-',
            'service_name' => 'Fund Request',
            'amount' => $item->amount ?? 0,
            'charges' => $item->charges ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->openingBalance ?? 0,
            'closing_balance' => $item->closingBalance ?? 0,
            'created_at' => $item->created_at,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | 5️⃣ PG (IN)
    |--------------------------------------------------------------------------
    */

    $pgQuery = DB::table('pgmanage')
        ->where('remId', $remId)
        ->where('status', 'SUCCESS');

    if ($fromDate && $toDate) {
        $pgQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $pgQuery->where('txnId', $txnId);
    }

    $pg = $pgQuery->get()->map(function ($item) {
        return (object)[
            'type' => "IN",
            'txn_id' => $item->txnId ?? '-',
            'service_name' => 'PG',
            'amount' => $item->amount ?? 0,
            'charges' => $item->charges ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->openingBalance ?? 0,
            'closing_balance' => $item->closingBalance ?? 0,
            'created_at' => $item->created_at,
        ];
    });


     /*
    |--------------------------------------------------------------------------
    | 6 PG1 (IN)
    |--------------------------------------------------------------------------
    */

    $pgQuery1 = DB::table('pgmanage1')
        ->where('remId', $remId)
        ->where('status', 'SUCCESS');

    if ($fromDate && $toDate) {
        $pgQuery1->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $pgQuery1->where('txnId', $txnId);
    }

    $pg1 = $pgQuery1->get()->map(function ($item) {
        return (object)[
            'type' => "IN",
            'txn_id' => $item->txnId ?? '-',
            'service_name' => 'PG_P1',
            'amount' => $item->amount ?? 0,
            'charges' => $item->charges ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->openingBalance ?? 0,
            'closing_balance' => $item->closingBalance ?? 0,
            'created_at' => $item->created_at,
        ];
    });
 /*
    |--------------------------------------------------------------------------
    | 5️⃣ PG (IN)
    |--------------------------------------------------------------------------
    */

    $pgQuery2 = DB::table('pgmanage2')
        ->where('remId', $remId)
        ->where('status', 'SUCCESS');

    if ($fromDate && $toDate) {
        $pgQuery2->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $pgQuery2->where('txnId', $txnId);
    }

    $pg2 = $pgQuery2->get()->map(function ($item) {
        return (object)[
            'type' => "IN",
            'txn_id' => $item->txnId ?? '-',
            'service_name' => 'PG_P2',
            'amount' => $item->amount ?? 0,
            'charges' => $item->charges ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->openingBalance ?? 0,
            'closing_balance' => $item->closingBalance ?? 0,
            'created_at' => $item->created_at,
        ];
    });

     /*
    |--------------------------------------------------------------------------
    | 6 AEPS (IN)
    |--------------------------------------------------------------------------
    */

    $aepsQuery = DB::table('merchant_aeps_transactions')
        ->where('merchant_id', $remId);

    if ($fromDate && $toDate) {
        $aepsQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $aepsQuery->where('orderid', $txnId);
    }

    $aeps = $aepsQuery->get()->map(function ($item) {
        return (object)[
            'type' => "IN",
            'txn_id' => $item->orderid ?? '-',
            'service_name' => 'AEPS',
            'amount' => $item->transaction_amount ?? 0,
            'charges' => $item->charges ?? 0,
            'commission' => $item->commission ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->opening_balance ?? 0,
            'closing_balance' => $item->closing_balance ?? 0,
            'created_at' => $item->created_at,
        ];
    });
     /*
    |--------------------------------------------------------------------------
    | 6 DMT (OUT)
    |--------------------------------------------------------------------------
    */

    $DMTQuery = DB::table('dmt_transactions')
        ->where('merchant_id', $remId);

    if ($fromDate && $toDate) {
        $DMTQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $DMTQuery->where('externalRef', $txnId);
    }

    $DMT = $DMTQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'txn_id' => $item->externalRef ?? '-',
            'service_name' => 'DMT',
            'amount' => $item->amount ?? 0,
            'charges' => $item->charges ?? 0,
            'commission' => $item->commission ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->opening_balance ?? 0,
            'closing_balance' => $item->closing_balance ?? 0,
            'created_at' => $item->created_at,
        ];
    });

     $refund = DB::table('refunds')
        ->where('user_id', $remId)
        ->where('status','Refunded');

    if ($fromDate && $toDate) {
        $refund->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $refund->where('service_ref_id', $txnId);
    }

    $rfe = $refund->get()->map(function ($item) {
        return (object)[
            'type' => "IN",
            'txn_id' => $item->service_ref_id ?? '-',
            'service_name' => 'Refund'.'-'.$item->service,
            'amount' => $item->amount ?? 0,
            'charges' => $item->charges ?? 0,
            'tds' => $item->tds ?? 0,
            'opening_balance' => $item->opening_balance ?? 0,
            'closing_balance' => $item->closing_balance ?? 0,
            'created_at' => $item->created_at,
        ];
    });

    /*
    |--------------------------------------------------------------------------
    | Merge & Filter
    |--------------------------------------------------------------------------
    */

    $collection = collect()
        ->merge($av)
        ->merge($xp)
        ->merge($upi)
        ->merge($rf)
        ->merge($pg)
        ->merge($pg1)
        ->merge($pg2)
        ->merge($aeps)
        ->merge($DMT)
        ->merge($rfe)
        ->sortByDesc('created_at')
        ->values();

    if ($service) {
        $collection = $collection->where('service_name', $service);
    }

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    */

    $perPage = 30;
    $currentPage = LengthAwarePaginator::resolveCurrentPage();

    $currentItems = $collection
        ->slice(($currentPage - 1) * $perPage, $perPage)
        ->values();

    $records = new LengthAwarePaginator(
        $currentItems,
        $collection->count(),
        $perPage,
        $currentPage,
        [
            'path' => request()->url(),
            'query' => request()->query()
        ]
    );

    return view('users.txn.bankledger', compact('records'));
    }

    /*
    |--------------------------------------------------------------------------
    | CSV EXPORT
    |--------------------------------------------------------------------------
    */
    public function export(Request $request)
    {
        $remittance = Auth::guard('remittance')->user();
        $remId = $remittance->remId;

        $from = $request->from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
        $to   = $request->to ?? Carbon::now()->endOfMonth()->format('Y-m-d');
        $type = $request->type ?? 'ALL';

        $transactions = $this->getTransactions($remId, $from, $to, $type);

        $filename = "ledger_export_".date('YmdHis').".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function() use ($transactions) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date',
                'Reference',
                'Type',
                'Credit',
                'Debit',
                'Balance',
                'Status'
            ]);

            foreach ($transactions as $row) {

                $credit = 0;
                $debit  = 0;

                if ($row->status === 'SUCCESS') {

                    if ($row->type === 'PG' || $row->type === 'IN_PG') {
                        $credit = ($row->amount ?? 0)
                                + ($row->charges ?? 0)
                                + ($row->tds ?? 0)
                                + ($row->commission ?? 0);
                    }

                    if ($row->type === 'PAYOUT') {
                        $debit = ($row->amount ?? 0)
                               + ($row->charges ?? 0)
                               + ($row->tds ?? 0);
                    }
                }

                fputcsv($file, [
                    $row->created_at,
                    $row->reference,
                    $row->type,
                    $credit,
                    $debit,
                    $row->balance,
                    $row->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}