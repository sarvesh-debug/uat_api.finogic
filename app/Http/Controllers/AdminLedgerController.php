<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminLedgerController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Common Query Builder
    |--------------------------------------------------------------------------
    */
    private function getTransactions($from, $to, $type = 'ALL', $remId = null)
{
    $fromDate = $from . ' 00:00:00';
    $toDate   = $to . ' 23:59:59';

    /*
    |--------------------------------------------------------------------------
    | PG
    |--------------------------------------------------------------------------
    */
    $pg = DB::table('pgmanage')
        ->selectRaw("
            CONVERT(remId USING utf8mb4) COLLATE utf8mb4_unicode_ci as remId,
            CONVERT(CAST(txnId AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci as reference,
            amount,
            charges,
            tds,
            commission,
            closingBalance as balance,
            CONVERT(UPPER(status) USING utf8mb4) COLLATE utf8mb4_unicode_ci as status,
            created_at,
            'PG' COLLATE utf8mb4_unicode_ci as type
        ")
        ->whereBetween('created_at', [$fromDate, $toDate]);

    if ($remId) {
        $pg->where('remId', $remId);
    }

    /*
    |--------------------------------------------------------------------------
    | PAYOUT
    |--------------------------------------------------------------------------
    */
    $payout = DB::table('xpresspayout')
        ->selectRaw("
            CONVERT(remId USING utf8mb4) COLLATE utf8mb4_unicode_ci as remId,
            CONVERT(CAST(payment_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci as reference,
            amount,
            charge as charges,
            tds,
            0 as commission,
            closing_balance as balance,
            CONVERT(UPPER(status) USING utf8mb4) COLLATE utf8mb4_unicode_ci as status,
            created_at,
            'PAYOUT' COLLATE utf8mb4_unicode_ci as type
        ")
        ->whereBetween('created_at', [$fromDate, $toDate]);

    if ($remId) {
        $payout->where('remId', $remId);
    }

     $payoutUPI = DB::table('upipayout')
        ->selectRaw("
            CONVERT(remId USING utf8mb4) COLLATE utf8mb4_unicode_ci as remId,
            CONVERT(CAST(payment_id AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci as reference,
            amount,
            charge as charges,
            tds,
            0 as commission,
            closing_balance as balance,
            CONVERT(UPPER(status) USING utf8mb4) COLLATE utf8mb4_unicode_ci as status,
            created_at,
            'PAYOUT' COLLATE utf8mb4_unicode_ci as type
        ")
        ->whereBetween('created_at', [$fromDate, $toDate]);

    if ($remId) {
        $payoutUPI->where('remId', $remId);
    }

    /*
    |--------------------------------------------------------------------------
    | FUND REQUEST
    |--------------------------------------------------------------------------
    */
    $fund = DB::table('rem_fundrequest')
        ->selectRaw("
            CONVERT(request_by USING utf8mb4) COLLATE utf8mb4_unicode_ci as remId,
            CONVERT(CAST(rid AS CHAR) USING utf8mb4) COLLATE utf8mb4_unicode_ci as reference,
            amount,
            charges,
            tds,
            0 as commission,
            closingBalance as balance,
            'SUCCESS' COLLATE utf8mb4_unicode_ci as status,
            created_at,
            'IN_PG' COLLATE utf8mb4_unicode_ci as type
        ")
        ->where('status', 1)
        ->whereBetween('created_at', [$fromDate, $toDate]);

    if ($remId) {
        $fund->where('rid', $remId);
    }

    

    /*
    |--------------------------------------------------------------------------
    | UNION
    |--------------------------------------------------------------------------
    */
    $merged = $pg->unionAll($payout)->unionAll($fund)->unionAll($payoutUPI);

    $query = DB::query()->fromSub($merged, 't');

    if ($type !== 'ALL') {
        $query->where('type', $type);
    }

    return $query->orderBy('created_at', 'asc')->get();
}

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
{
    $fromDate = $request->from_date;
    $toDate   = $request->to_date;
    $txnId    = $request->txn_id;
    $service  = $request->service;
    $remId     =$request->mer_id;

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

    $avQuery = DB::table('account_verifications');


    if ($fromDate && $toDate) {
        $avQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $avQuery->where('remId', $txnId); // correct txn column
    }

    $av = $avQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'sub_service'=> '-'.$item->type,
            'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId'=>$item->remId,
            'txn_id' => $item->remId ?? '-',
            'service_name' => 'Account Verification',
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

    $xpQuery = DB::table('xpresspayout');

    if ($fromDate && $toDate) {
        $xpQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $xpQuery->where('payment_id', $txnId);
    }

    $xp = $xpQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'sub_service'=> '-',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
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

      $xpQuery2 = DB::table('xpresspayout2');

    if ($fromDate && $toDate) {
        $xpQuery2->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $xpQuery2->where('payment_id', $txnId);
    }

    $xp2 = $xpQuery2->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'sub_service'=> '-',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
            'txn_id' => $item->payment_id ?? '-',
            'service_name' => 'XpressPayout_P2',
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
    | 2️⃣ AEPS setlememt (OUT)
    |--------------------------------------------------------------------------
    */

    $stlmQuery = DB::table('aeps_stlm');

    if ($fromDate && $toDate) {
        $stlmQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $stlmQuery->where('payment_id', $txnId);
    }

    $st = $stlmQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'sub_service'=> '-',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
            'txn_id' => $item->payment_id ?? '-',
            'service_name' => 'AEPS_STLM',
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

    $upiQuery = DB::table('upipayout');

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
            'sub_service'=> '-',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
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

     $upiQuery2 = DB::table('upipayout2');

    if ($fromDate && $toDate) {
        $upiQuery2->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $upiQuery2->where(function ($q) use ($txnId) {
            $q->where('payment_id', $txnId)
              ->orWhere('payment_id', $txnId);
        });
    }

    $upi2 = $upiQuery2->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'sub_service'=> '-',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
            'txn_id' => $item->txn_id ?? $item->payment_id ?? '-',
            'service_name' => 'UPI Payout_P2',
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
            'sub_service'=> '-',
             'name'=>DB::table('remittances')->where('remId',$item->rid)->value('name'),
            'remId' =>$item->rid,
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
            'sub_service'=> '- P',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
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
    | 5️⃣ PG (IN)1
    |--------------------------------------------------------------------------
    */

    $pgQuery1 = DB::table('pgmanage1')
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
            'sub_service'=> '- P1',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
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
    | 6 PG (IN)2
    |--------------------------------------------------------------------------
    */

    $pgQuery2 = DB::table('pgmanage2')
        ->where('status', 'SUCCESS');
        //->where('status', 'PENDING');

    if ($fromDate && $toDate) {
        $pgQuery2->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $pgQuery2->where('txnId', $txnId);
    }

    $pg2 = $pgQuery2->get()->map(function ($item) {
        return (object)[
            'type' => "IN",
            'sub_service'=> '- P2',
             'name'=>DB::table('remittances')->where('remId',$item->remId)->value('name'),
            'remId' =>$item->remId,
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
    | 6 AEPS (IN)
    |--------------------------------------------------------------------------
    */

    $aepsQuery = DB::table('merchant_aeps_transactions');

    if ($fromDate && $toDate) {
        $aepsQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $aepsQuery->where('orderid', $txnId)->where('orderid','!=',null);
    }
    
    $aeps = $aepsQuery->get()->map(function ($item) {
        if($item->transaction_mode == 'CR')
            {
                $mode='IN';
            }

        else
            {
                $mode='OUT';
            }
        return (object)[
            
            'type' => $mode,
            'sub_service'=> $item->transaction_type,
             'name'=>DB::table('remittances')->where('remId',$item->merchant_id)->value('name'),
            'remId' =>$item->merchant_id,
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

    $DMTQuery = DB::table('dmt_transactions');

    if ($fromDate && $toDate) {
        $DMTQuery->whereBetween('created_at', [$fromDate, $toDate]);
    }

    if ($txnId) {
        $DMTQuery->where('externalRef', $txnId);
    }

    $DMT = $DMTQuery->get()->map(function ($item) {
        return (object)[
            'type' => "OUT",
            'sub_service'=> '-',
            'name'=>DB::table('remittances')->where('remId',$item->merchant_id)->value('name'),
            'remId' =>$item->merchant_id,
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

    //Refund
    $refund = DB::table('refunds')
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
            'sub_service'=> $item->service,
            'name'=>DB::table('remittances')->where('remId',$item->user_id)->value('name'),
            'remId' =>$item->user_id,
            'txn_id' => $item->service_ref_id ?? '-',
            'service_name' => 'Refund',
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
        ->merge($xp2)
        ->merge($st)
        ->merge($upi)
        ->merge($upi2)
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

    if($remId)
        {
            $collection = $collection->where('remId', $remId);
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

    return view('admin.ledger.index', compact('records'));
}

    /*
    |--------------------------------------------------------------------------
    | EXPORT CSV
    |--------------------------------------------------------------------------
    */
    public function export(Request $request)
    {
        $from  = $request->from;
        $to    = $request->to;
        $type  = $request->type ?? 'ALL';
        $remId = $request->remId ?? null;

        $transactions = $this->getTransactions($from, $to, $type, $remId);

        $filename = "admin_ledger_" . date('YmdHis') . ".csv";

        return response()->stream(function () use ($transactions) {

            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date',
                'Remittance ID',
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
                        $credit = (float)$row->amount
                                + (float)$row->charges
                                + (float)$row->tds
                                + (float)$row->commission;
                    }

                    if ($row->type === 'PAYOUT') {
                        $debit = (float)$row->amount
                               + (float)$row->charges
                               + (float)$row->tds;
                    }
                }

                fputcsv($file, [
                    $row->created_at,
                    $row->remId,
                    $row->reference,
                    $row->type,
                    $credit,
                    $debit,
                    $row->balance,
                    $row->status,
                ]);
            }

            fclose($file);

        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename"
        ]);
    }
}