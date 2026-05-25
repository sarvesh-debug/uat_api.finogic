<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SummaryExport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class summaryCotroller extends Controller
{
    public function summary(Request $request)
    {
        /* ================= SERVICE NAME ================= */

        $services = [
            'AV' => 'Account Verification',
            'XP' => 'Xpress Payout',
            'UPI' => 'UPI Payout',
            'FUND' => 'Fund Request',
            'AEPS' => 'AEPS',
            'DMT' => 'DMT',
            'REFUND' => 'Refund'
        ];

        /* ================= INPUT ================= */

        $filter   = $request->get('filter');
        $fromDate = $request->get('from_date');
        $toDate   = $request->get('to_date');
        $txnId    = $request->get('txn_id');
        $service  = $request->get('service');
        $remId    = $request->get('mer_id');

        /* ================= DATE FILTER (FINAL FIX) ================= */

        // Normalize empty values
        $fromDate = !empty($fromDate) ? $fromDate : null;
        $toDate   = !empty($toDate) ? $toDate : null;

        if (!empty($filter)) {

            switch ($filter) {
                case 'today':
                    $fromDate = Carbon::today()->startOfDay();
                    $toDate   = Carbon::today()->endOfDay();
                    break;

                case 'yesterday':
                    $fromDate = Carbon::yesterday()->startOfDay();
                    $toDate   = Carbon::yesterday()->endOfDay();
                    break;

                case 'this_month':
                    $fromDate = Carbon::now()->startOfMonth();
                    $toDate   = Carbon::now()->endOfMonth();
                    break;

                default:
                    $fromDate = Carbon::today()->startOfDay();
                    $toDate   = Carbon::today()->endOfDay();
            }

        } elseif ($fromDate && $toDate) {

            $fromDate = Carbon::parse($fromDate)->startOfDay();
            $toDate   = Carbon::parse($toDate)->endOfDay();

        } else {

            // Default = Today
            $fromDate = Carbon::today()->startOfDay();
            $toDate   = Carbon::today()->endOfDay();
        }

        /* ================= COMMON QUERY FUNCTION ================= */

        $getName = fn($id) => DB::table('remittances')->where('remId',$id)->value('name');

        /* ================= ALL SERVICES ================= */

        $av = DB::table('account_verifications')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($txnId, fn($q)=>$q->where('remId',$txnId))
            ->get()
            ->map(fn($i)=>(object)[
                'type'=>"OUT",
                'sub_service'=>'-'.$i->type,
                'name'=>$getName($i->remId),
                'remId'=>$i->remId,
                'txn_id'=>$i->remId,
                'ref_id'=>'-',
                'status' => strtoupper($i->status ?? 'Success'),
                'service_name'=>$services['AV'],
                'amount'=>0,
                'charges'=>$i->charges ?? 0,
                'tds'=>$i->tds ?? 0,
                'opening_balance'=>$i->opbalance ?? 0,
                'closing_balance'=>$i->clbalance ?? 0,
                'created_at'=>$i->created_at,
            ]);

        $xp = DB::table('xpresspayout')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($txnId, fn($q)=>$q->where('payment_id',$txnId))
            ->get()
            ->map(fn($i)=>(object)[
                'type'=>"OUT",
                'sub_service'=>'-',
                'name'=>$getName($i->remId),
                'remId'=>$i->remId,
                'txn_id'=>$i->payment_id,
                'ref_id'=>$i->refId ?? '-',
                'service_name'=>$services['XP'],
                'amount'=>$i->amount ?? 0,
                'status' => strtoupper($i->status ?? 'PENDING'),
                'charges'=>$i->charge ?? 0,
                'tds'=>$i->tds ?? 0,
                'opening_balance'=>$i->opening_balance ?? 0,
                'closing_balance'=>$i->closing_balance ?? 0,
                'created_at'=>$i->created_at,
            ]);

        $upi = DB::table('upipayout')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($txnId, fn($q)=>$q->where('payment_id',$txnId))
            ->get()
            ->map(fn($i)=>(object)[
                'type'=>"OUT",
                'sub_service'=>'-',
                'name'=>$getName($i->remId),
                'remId'=>$i->remId,
                'txn_id'=>$i->payment_id,
                'ref_id'=>$i->refId ?? '-',
                'status' => strtoupper($i->status ?? 'PENDING'),
                'service_name'=>$services['UPI'],
                'amount'=>$i->amount ?? 0,
                'charges'=>$i->charge ?? 0,
                'tds'=>$i->tds ?? 0,
                'opening_balance'=>$i->opening_balance ?? 0,
                'closing_balance'=>$i->closing_balance ?? 0,
                'created_at'=>$i->created_at,
            ]);

        $rf = DB::table('rem_fundrequest')
            ->where('status',1)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get()
            ->map(fn($i)=>(object)[
                'type'=>"IN",
                'sub_service'=>'-',
                'name'=>$getName($i->rid),
                'remId'=>$i->rid,
                'txn_id'=>$i->rid,
                'ref_id'=>$i->utr ?? '-',
                'service_name'=>$services['FUND'],
                'amount'=>$i->amount ?? 0,
                'charges'=>$i->charges ?? 0,
                'tds'=>$i->tds ?? 0,
                'status' => strtoupper($i->status ?? 'Success'),
                'opening_balance'=>$i->openingBalance ?? 0,
                'closing_balance'=>$i->closingBalance ?? 0,
                'created_at'=>$i->created_at,
            ]);

        $aeps = DB::table('merchant_aeps_transactions')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($txnId, fn($q)=>$q->where('orderid',$txnId))
            ->get()
            ->map(fn($i)=>(object)[
                'type'=>$i->transaction_mode == 'CR' ? 'IN' : 'OUT',
                'sub_service'=>ucfirst(strtolower($i->transaction_type)),
                'name'=>$getName($i->merchant_id),
                'remId'=>$i->merchant_id,
                'txn_id'=>$i->orderid,
                'ref_id'=>$i->orderid,
                'service_name'=>$services['AEPS'],
                'amount'=>$i->transaction_amount ?? 0,
                'charges'=>$i->charges ?? 0,
                'commission'=>$i->commission ?? 0,
                'status' => strtoupper($i->status ?? 'PENDING'),
                'tds'=>$i->tds ?? 0,
                'opening_balance'=>$i->opening_balance ?? 0,
                'closing_balance'=>$i->closing_balance ?? 0,
                'created_at'=>$i->created_at,
            ]);

        $dmt = DB::table('dmt_transactions')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($txnId, fn($q)=>$q->where('externalRef',$txnId))
            ->get()
            ->map(fn($i)=>(object)[
                'type'=>"OUT",
                'sub_service'=>'-',
                'name'=>$getName($i->merchant_id),
                'remId'=>$i->merchant_id,
                'txn_id'=>$i->externalRef,
                  'ref_id'=>$i->externalRef,
                'service_name'=>$services['DMT'],
                'status' => strtoupper($i->status ?? 'PENDING'),
                'amount'=>$i->amount ?? 0,
                'charges'=>$i->charges ?? 0,
                'commission'=>$i->commission ?? 0,
                'tds'=>$i->tds ?? 0,
                'opening_balance'=>$i->opening_balance ?? 0,
                'closing_balance'=>$i->closing_balance ?? 0,
                'created_at'=>$i->created_at,
            ]);

        $refund = DB::table('refunds')
            ->where('status','Refunded')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->when($txnId, fn($q)=>$q->where('service_ref_id',$txnId))
            ->get()
            ->map(fn($i)=>(object)[
                'type'=>"IN",
                'sub_service'=>$i->service,
                'name'=>$getName($i->user_id),
                'remId'=>$i->user_id,
                'txn_id'=>$i->service_ref_id,
                'ref_id'=>$i->transaction_id,
                'service_name'=>$services['REFUND'],
                'amount'=>$i->amount ?? 0,
                'status' => strtoupper($i->status ?? 'PENDING'),
                'charges'=>$i->charges ?? 0,
                'tds'=>$i->tds ?? 0,
                'opening_balance'=>$i->opening_balance ?? 0,
                'closing_balance'=>$i->closing_balance ?? 0,
                'created_at'=>$i->created_at,
            ]);

        /* ================= MERGE ================= */

        $collection = collect()
            ->merge($av)->merge($xp)->merge($upi)
            ->merge($rf)->merge($aeps)->merge($dmt)->merge($refund)
            ->sortByDesc('created_at')
            ->values();

        if ($service) $collection = $collection->where('service_name', $service);
        if ($remId) $collection = $collection->where('remId', $remId);

        /* ================= SUMMARY ================= */

        $summary = [
            'total_transactions'=>$collection->count(),
            'total_in_amount'=>$collection->where('type','IN')->sum('amount'),
            'total_out_amount'=>$collection->where('type','OUT')->sum('amount'),
            'total_charges'=>$collection->sum('charges'),
            'total_tds'=>$collection->sum('tds'),
            'total_commission'=>$collection->sum(fn($i)=>$i->commission ?? 0),

              // ✅ STATUS WISE AMOUNT
            'success_amount'  => $collection->where('status','SUCCESS')->sum('amount'),
            'pending_amount'  => $collection->where('status','PENDING')->sum('amount'),
            'failed_amount'   => $collection->where('status','FAILED')->sum('amount'),
            'refunded_amount' => $collection->where('status','REFUNDED')->sum('amount'),
        ];

       $serviceSummary = $collection->groupBy('service_name')->map(fn($items,$name)=>[
    'service_name'=>$name,
    'total_transactions'=>$items->count(),
    'total_amount'=>$items->sum('amount'),

    'success_amount'=>$items->where('status','SUCCESS')->sum('amount'),
    'pending_amount'=>$items->where('status','PENDING')->sum('amount'),
    'failed_amount'=>$items->where('status','FAILED')->sum('amount'),
    'refunded_amount'=>$items->where('status','REFUNDED')->sum('amount'),

    'total_charges'=>$items->sum('charges'),
    'total_tds'=>$items->sum('tds'),
    'total_commission'=>$items->sum(fn($i)=>$i->commission ?? 0),
])->values();

        /* ================= PAGINATION ================= */

        $perPage = 5000;
        $page = $request->get('page',1);

        $records = new LengthAwarePaginator(
            $collection->slice(($page-1)*$perPage,$perPage)->values(),
            $collection->count(),
            $perPage,
            $page,
            ['path'=>$request->url(),'query'=>$request->query()]
        );

        return response()->json([
            'summary'=>$summary,
            'service_summary'=>$serviceSummary,
            'data'=>$records
        ]);
    }


public function export(Request $request)
{
    // 🔥 same data nikaalne ke liye summary function call
    $response = $this->summary($request);
    $data = $response->getData(true);

    $rows = $data['data']['data'];

    $filename = "summary_" . date('Ymd_His') . ".csv";

    $headers = [
        "Content-type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma" => "no-cache",
        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
        "Expires" => "0"
    ];

    $columns = [
        'Date',
        'Name',
        'RemID',
        'Txn ID',
        'Ref ID',
        'Service',
        'Type',
        'Status',
        'Opening',
        'Amount',
        'Charges',
        'Closing'
    ];

    $callback = function() use($rows, $columns) {

        $file = fopen('php://output', 'w');

        // Header row
        fputcsv($file, $columns);

        // Data rows
        foreach ($rows as $row) {
            fputcsv($file, [
                $row['created_at'],
                $row['name'],
                $row['remId'],
                $row['txn_id'],
                $row['ref_id'],
                $row['service_name'],
                $row['type'],
                $row['status'],
                $row['opening_balance'],
                $row['amount'],
                $row['charges'],
                $row['closing_balance'],
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

 public function index(Request $request)
{
    $query = [
        'filter'    => $request->filter, // ✅ ADD THIS
        'from_date' => $request->from_date,
        'to_date'   => $request->to_date,
        'txn_id'    => $request->txn_id,
        'service'   => $request->service,
        'mer_id'    => $request->mer_id,
        'page'      => $request->page ?? 1,
    ];

    // 🔥 EMPTY VALUES REMOVE (IMPORTANT)
    $query = array_filter($query, function ($value) {
        return $value !== null && $value !== '';
    });

    $response = Http::get('https://api.aarpiz.in/api/v1/all/summary', $query);

    $data = $response->json();

    return view('summary.index', compact('data'));
}

//business api
public function business(Request $request): JsonResponse
{
    try {
        // Optional: pagination parameters
        $validator = Validator::make($request->all(), [
            'page' => 'nullable|integer|min:1',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $limit = $request->limit ?? 10;

        // Fetch data with pagination
        $retailers = DB::table('remittances')
                        ->orderBy('id', 'desc')
                        ->paginate($limit);

        if ($retailers->isEmpty()) {
            return response()->json([
                'status' => true,
                'message' => 'No data found',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => true,
            'message' => 'Retailers fetched successfully',
            'data' => $retailers->items(),
            'pagination' => [
                'current_page' => $retailers->currentPage(),
                'last_page' => $retailers->lastPage(),
                'total' => $retailers->total(),
                'per_page' => $retailers->perPage(),
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Something went wrong',
            'error' => $e->getMessage()
        ], 500);
    }
}


public function indexuser(Request $request)
{
    $query = [
        'filter'    => $request->filter, // ✅ ADD THIS
        'from_date' => $request->from_date,
        'to_date'   => $request->to_date,
        'txn_id'    => $request->txn_id,
        'service'   => $request->service,
        'mer_id'    => Auth::guard('remittance')->user()->remId,
        'page'      => $request->page ?? 1,
    ];

    // 🔥 EMPTY VALUES REMOVE (IMPORTANT)
    $query = array_filter($query, function ($value) {
        return $value !== null && $value !== '';
    });

    $response = Http::get('https://uatapi.credxpay.com/api/v1/all/summary', $query);

    $data = $response->json();
    $merId=Auth::guard('remittance')->user()->remId;

    return view('summary.usersummay', compact('data','merId'));
}
}