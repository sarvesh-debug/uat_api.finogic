<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TxnExport;

class TrackController extends Controller
{
private function filterQuery($request)
{
    $query = DB::table('xpresspayout')
        ->where('remId', Auth::guard('remittance')->user()->remId);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('payment_id', 'like', "%$search%")
              ->orWhere('beneficiary_name', 'like', "%$search%")
              ->orWhere('bank_ref_no', 'like', "%$search%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

public function txnTrack(Request $request)
{
    $txn = $this->filterQuery($request)
        ->orderBy('created_at', 'desc')
        ->paginate(30)
        ->withQueryString();

    return view('users.txn.txnTrack', compact('txn'));
}

public function exportCsv(Request $request)
{
    $transactions = $this->filterQuery($request)
        ->orderBy('created_at', 'desc')
        ->get();

    $response = new StreamedResponse(function () use ($transactions) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Payment ID',
            'Amount',
            'Charges',
            'Closing Balance',
            'Beneficiary',
            'UTR',
            'Status',
            'Date'
        ]);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->payment_id,
                $t->amount,
                $t->charge,
                $t->closing_balance,
                $t->beneficiary_name,
                $t->bank_ref_no,
                $t->status,
                $t->created_at
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}

public function exportExcel(Request $request)
{
    return Excel::download(new TxnExport($request), 'transactions.xlsx');
}    


private function filterQueryAdmin($request)
{
    $query = DB::table('xpresspayout');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('payment_id', 'like', "%$search%")
              ->orWhere('beneficiary_name', 'like', "%$search%")
              ->orWhere('bank_ref_no', 'like', "%$search%");
        });
    }
    if($request->filled('remId')){
        $remId=$request->remId;
          $query->where(function ($q) use ($remId) {
            $q->where('remId', 'like', "%$remId%")
              ->orWhere('beneficiary_name', 'like', "%$remId%")
              ->orWhere('bank_ref_no', 'like', "%$remId%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

public function exportCsvAdmin(Request $request)
{
    $transactions = $this->filterQueryAdmin($request)
        ->orderBy('created_at', 'desc')
        ->get();

    $response = new StreamedResponse(function () use ($transactions) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Payment ID',
            'Amount',
            'Charges',
            'Closing Balance',
            'Beneficiary',
            'UTR',
            'Status',
            'Date'
        ]);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->payment_id,
                $t->amount,
                $t->charge,
                $t->closing_balance,
                $t->beneficiary_name,
                $t->bank_ref_no,
                $t->status,
                $t->created_at
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}
public function txnTrackAdmin(Request $request)
    {

     $txn = $this->filterQueryAdmin($request)
        ->orderBy('created_at', 'desc')
        ->paginate(30)
        ->withQueryString();


      //  return $txn;die();
        return view('txn.index',compact('txn'));
    }
   public function txnAction(Request $request)
{
    //return $request;die();  
    $request->validate([
        'payment_id'  => 'required|string|exists:xpresspayout,payment_id',
        'status'      => 'required|string',
        'bank_ref_no' => 'required|string|max:100',
        'remarks'     => 'nullable|string|max:255',
    ]);

    $paymentId = $request->payment_id;

    try {
        $transaction = DB::table('xpresspayout')->where('payment_id', $paymentId)->first();

        if(!$transaction) {
            return response()->json(['status'=>false, 'message'=>'Transaction not found'], 404);
        }

        DB::table('xpresspayout')->where('payment_id', $paymentId)->update([
            'status' => $request->status,
            'bank_ref_no' => $request->bank_ref_no,
            'remark' => $request->remarks,
            'updated_at' => now(),
        ]);

        // Callback URL
        $callbackUrl = DB::table('remittances')
            ->where('remId', $transaction->remId)
            ->where('email', $transaction->email)
            ->value('callback_url');

        $payload = [
            'payment_id' => $transaction->payment_id,
            'amount' => $transaction->amount,
            'status' => $request->status,
            'bank_ref_no' => $request->bank_ref_no,
            'account_no' => $transaction->acc_no,
            'ifsc' => $transaction->ifsc_code,
            'beneficiary_name' => $transaction->beneficiary_name,
            'ref_no' => $transaction->refId,
            'updated_at' => now(),
        ];

        if($callbackUrl){
            try { Http::timeout(10)->post($callbackUrl, $payload); } 
            catch (\Exception $e){ Log::error($e->getMessage()); }
        }
	$emailPayload = [
                'api_key' => "codegraphi@qazxcv",
                'to'      => $transaction->email,
               'subject' => 'Payout Transaction Successfully',
                    'message' => "Dear {$transaction->remId},\n\n"
            ."We are pleased to inform you that your payout request has been Successfully processed.\n\n"
            ."📌 Transaction Details:\n"
            ."Reference No: {$transaction->refId}\n"
            ."Payment ID: {$transaction->payment_id}\n"
            ."Bank Reference No: {$request->bank_ref_no}\n"
            ."Beneficiary: {$transaction->beneficiary_name}\n"
            ."Bank: {$transaction->bank_name}\n"
            ."Account No: {$transaction->acc_no}\n"
            ."IFSC: {$transaction->ifsc_code}\n"
            ."Amount: ₹{$transaction->amount}\n"
            ."Charges: ₹{$transaction->charge}\n"
            ."TDS: ₹{$transaction->tds}\n"
            ."Remarks: {$request->remarks}\n"

            ."You will receive a confirmation once the payout is processed by the bank.\n\n"
            ."Regards,\n"
            ."Team CodeGraphi"

                ];

        $emailResponse = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])->post('https://email.codegraphi.in/api/send-email', $emailPayload);
       // return response()->json(['status'=>true, 'message'=>'Transaction updated', 'data'=>$payload]);
       return back()->with('success', 'Transaction updated');

    } catch(\Exception $e){
       // return response()->json(['status'=>false, 'message'=>'Error updating transaction','error'=>$e->getMessage()], 500);
        return back()->with('error', 'Error updating transaction');
    }

} 

    
public function docs()
{
    $user = Auth::guard('remittance')->user();

    if (!$user) {
        return redirect()->route('login');
    }

    $remId = $user->remId;

    $myservices = DB::table('remittances')
        ->where('remId', $remId)
        ->first();

    // Safety fallback (prevents null error in blade)
    if (!$myservices) {
        $myservices = (object)[];
    }

    $services = DB::table('services')
        ->orderBy('category')
        ->orderBy('api_name')
        ->get()
        ->groupBy(function ($item) {
            return strtolower(str_replace(' ', '', $item->category));
        });

    $categoryMeta = [
        'fintech' => [
            'name' => 'Fintech',
            'icon' => 'fa-solid fa-money-bill-wave',
        ],
        'landing' => [
            'name' => 'Landing',
            'icon' => 'fa-solid fa-globe',
        ],
        'verification' => [
            'name' => 'Verification',
            'icon' => 'fa-solid fa-shield-halved',
        ],
        'travels' => [
            'name' => 'Travels',
            'icon' => 'fa-solid fa-plane',
        ],
        'insurance' => [
            'name' => 'Insurance',
            'icon' => 'fa-solid fa-file-shield',
        ],
        'otherapi' => [
            'name' => 'Other Api',
            'icon' => 'fa-solid fa-layer-group',
        ],
    ];

    $categories = [];

    foreach ($services as $key => $items) {

        if (!isset($categoryMeta[$key])) {
            continue;
        }

        $categories[$key] = [
            'name' => $categoryMeta[$key]['name'],
            'icon' => $categoryMeta[$key]['icon'],
            'services' => $items->map(function ($service) {
                return [
                    'title'  => $service->api_name ?? 'Untitled',
                    'desc'   => $service->short_description ?? 'No description available',
                    'status' => $service->status ?? 'inactive',
                ];
            })->values()->toArray()
        ];
    }

    $serviceMap = [
        'Domestic Payouts'    => 'payout1',
        'DMT'          => 'isDMT',
        'AePS'                => 'isAEPS',
        'UPI Payout'          => 'upipayout',
        'PG Payout'           => 'pgpayout',
        'PayoutV2'            => 'payout2',
        'Merchant Onboarding' => 'isOnboarding',
    ];

    return view('apidocs_1', compact(
        'categories',
        'myservices',
        'serviceMap'
    ));
}

public function serviceDocs($slug)
{
    $service = DB::table('services')
        ->get()
        ->first(function ($item) use ($slug) {
            return Str::slug($item->api_name) === $slug;
        });

    if (!$service) {
        abort(404);
    }

    $viewPath = 'docs.' . $slug;

    if (!view()->exists($viewPath)) {
        abort(404);
    }

    return view($viewPath, compact('service'));
}
  public function chkPayoutApiStatus(Request $request)
{
    $response = \App\Helpers\PayoutV6Helper::initiateStatus([
        'order_id' => $request->orderId
    ]);

    $data = $response;

    //return $data;

    return view('txn.payout_status', compact('data'));
}

 public function chkPayoutApiStatusV2(Request $request)
{
    //return $request;
    $response = \App\Helpers\AeronpayHelper::status([

        'client_referenceId' => $request->client_referenceId,
        'date_of_transaction' => $request->date_of_transaction,
        'mobile' => $request->mobile
    ]);

    $data = $response;

    //return $data;

    return view('txn.payout_status_v2', compact('data'));
}

 public function chkUpiApiStatusV2(Request $request)
{
    //return $request;
    $response = \App\Helpers\AeronpayHelper::status([

        'client_referenceId' => $request->client_referenceId,
        'date_of_transaction' => $request->date_of_transaction,
        'mobile' => $request->mobile
    ]);

    $data = $response;

    //return $data;

    return view('txn.payout_status_v2', compact('data'));
}

 public function chkUpiApiStatus(Request $request)
{
    $response = \App\Helpers\PayoutV6Helper::initiateUPIStatus([
        'order_id' => $request->orderId
    ]);

    $data = $response;

   //return $data;

    return view('txn.upi_status', compact('data'));
}


 public function chkPGApiStatus(Request $request)
{
    $response = \App\Helpers\PayoutV6Helper::pgStatus([
        'order_id' => $request->orderId
    ]);

    $data = $response;

    //return $data;

    return view('txn.pg_status', compact('data'));
}

 public function chkPGApiStatusp1(Request $request)
{
    $response = \App\Helpers\PayoutV6Helper::pgStatus([
        'order_id' => $request->orderId
    ]);

    $data = $response;

    //return $data;

    return view('txn.pg1_status', compact('data'));
}
 public function chkPGApiStatusp2(Request $request)
{
    $response = \App\Helpers\PayoutV6Helper::pgStatus([
        'order_id' => $request->orderId
    ]);

    $data = $response;

    //return $data;

    return view('txn.pg2_status', compact('data'));
}

// report for admin 

 /* =====================================================
     * DMT SECTION
     * ===================================================== */

    private function filterDmt($request)
    {
        $query = DB::table('dmt_transactions'); // no merchant restriction

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('externalRef', 'like', "%$search%")
                  ->orWhere('beneficiaryName', 'like', "%$search%")
                  ->orWhere('outlet_id', 'like', "%$search%")
                  ->orWhere('merchant_id', 'like', "%$search%");
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    public function dmtIndex(Request $request)
    {
        $txn = $this->filterDmt($request)
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.reports.dmt_report', compact('txn'));
    }

    public function dmtExport(Request $request)
    {
        $transactions = $this->filterDmt($request)
            ->orderByDesc('created_at')
            ->get();

        return new StreamedResponse(function () use ($transactions) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Merchant ID',
                'Payment ID',
                'Amount',
                'Charges',
                'Closing Balance',
                'Beneficiary',
                'Status',
                'Date'
            ]);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->merchant_id,
                    $t->externalRef,
                    $t->amount,
                    $t->charges,
                    $t->closing_balance,
                    $t->beneficiaryName,
                    $t->status,
                    $t->created_at
                ]);
            }

            fclose($handle);

        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=Admin_DMT_Report.csv"
        ]);
    }


    /* =====================================================
     * AEPS SECTION
     * ===================================================== */

   private function filterAeps($request)
{
    $query = DB::table('merchant_aeps_transactions')->where('orderid','!=',null);
        

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('external_ref', 'like', "%$search%")
              ->orWhere('orderid', 'like', "%$search%")
              ->orWhere('outlet_id', 'like', "%$search%")
              ->orWhere('merchant_id', 'like', "%$search%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

    public function aepsIndex(Request $request)
    {
        $txn = $this->filterAeps($request)
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.reports.aeps_report', compact('txn'));
    }

    public function aepsExport(Request $request)
    {
        $transactions = $this->filterAeps($request)
            ->orderByDesc('created_at')
            ->get();

        return new StreamedResponse(function () use ($transactions) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Merchant ID',
                'Payment ID',
                'Amount',
                'Charges',
                'Closing Balance',
                'Status',
                'Date'
            ]);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->merchant_id,
                    $t->external_ref,
                    $t->amount,
                    $t->charges,
                    $t->closing_balance,
                    $t->status,
                    $t->created_at
                ]);
            }

            fclose($handle);

        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=Admin_AEPS_Report.csv"
        ]);
    }


 private function filterAepsv2($request)
{
    $query = DB::table('merchant_aeps_v2_transactions')->where('orderid','!=',null);
        

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('external_ref', 'like', "%$search%")
              ->orWhere('orderid', 'like', "%$search%")
              ->orWhere('outlet_id', 'like', "%$search%")
              ->orWhere('merchant_id', 'like', "%$search%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

    public function aepsIndexv2(Request $request)
    {
        $txn = $this->filterAepsv2($request)
            ->orderByDesc('created_at')
            ->paginate(30)
            ->withQueryString();

        return view('admin.reports.aepsv2_report', compact('txn'));
    }

    public function aepsExportv2(Request $request)
    {
        $transactions = $this->filterAepsv2($request)
            ->orderByDesc('created_at')
            ->get();

        return new StreamedResponse(function () use ($transactions) {

            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Merchant ID',
                'Payment ID',
                'Amount',
                'Charges',
                'Closing Balance',
                'Status',
                'Date'
            ]);

            foreach ($transactions as $t) {
                fputcsv($handle, [
                    $t->merchant_id,
                    $t->external_ref,
                    $t->amount,
                    $t->charges,
                    $t->closing_balance,
                    $t->status,
                    $t->created_at
                ]);
            }

            fclose($handle);

        }, 200, [
            "Content-Type" => "text/csv",
            "Content-Disposition" => "attachment; filename=Admin_AEPS_Report.csv"
        ]);
    }

    //DMT
  private function filterQueryDMT($request)
{
    $query = DB::table('dmt_transactions')
        ->where('merchant_id', Auth::guard('remittance')->user()->remId);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('externalRef', 'like', "%$search%")
              ->orWhere('beneficiaryName', 'like', "%$search%")
              ->orWhere('outlet_id', 'like', "%$search%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

public function txnDmt(Request $request)
{
    $txn = $this->filterQueryDMT($request)
        ->orderBy('created_at', 'desc')
        ->paginate(30)
        ->withQueryString();

    return view('users.txn.dmtTxn', compact('txn'));
}

public function exportCsvDMT(Request $request)
{
    $transactions = $this->filterQueryDMT($request)
        ->orderBy('created_at', 'desc')
        ->get();

    $response = new StreamedResponse(function () use ($transactions) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Payment ID',
            'Amount',
            'Charges',
            'Closing Balance',
            'Beneficiary',
            'UTR',
            'Status',
            'Date'
        ]);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->externalRef,
                $t->amount,
                $t->charges,
                $t->closing_balance,
                $t->beneficiaryName,
                $t->externalRef,
                $t->status,
                $t->created_at
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}

//AEPS
  private function filterQueryAEPS($request)
{
    $query = DB::table('merchant_aeps_transactions')
        ->where('merchant_id', Auth::guard('remittance')->user()->remId);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('external_ref', 'like', "%$search%")
              ->orWhere('orderid', 'like', "%$search%")
              ->orWhere('outlet_id', 'like', "%$search%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

public function txnAeps(Request $request)
{
    $txn = $this->filterQueryAeps($request)
        ->orderBy('created_at', 'desc')
        ->paginate(30)
        ->withQueryString();

    return view('users.txn.aepsTxn', compact('txn'));
}

public function exportCsvAeps(Request $request)
{
    $transactions = $this->filterQueryAeps($request)
        ->orderBy('created_at', 'desc')
        ->get();

    $response = new StreamedResponse(function () use ($transactions) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Payment ID',
            'Amount',
            'Charges',
            'Closing Balance',
            'Beneficiary',
            'UTR',
            'Status',
            'Date'
        ]);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->externalRef,
                $t->amount,
                $t->charges,
                $t->closing_balance,
                $t->beneficiaryName,
                $t->externalRef,
                $t->status,
                $t->created_at
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}

//AEPS
  private function filterQueryAEPSv2($request)
{
    $query = DB::table('merchant_aeps_v2_transactions')
        ->where('merchant_id', Auth::guard('remittance')->user()->remId);

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('external_ref', 'like', "%$search%")
              ->orWhere('orderid', 'like', "%$search%")
              ->orWhere('outlet_id', 'like', "%$search%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

public function txnAepsv2(Request $request)
{
    $txn = $this->filterQueryAepsv2($request)
        ->orderBy('created_at', 'desc')
        ->paginate(30)
        ->withQueryString();

    return view('users.txn.aepsTxnv2', compact('txn'));
}

public function exportCsvAepsv2(Request $request)
{
    $transactions = $this->filterQueryAepsv2($request)
        ->orderBy('created_at', 'desc')
        ->get();

    $response = new StreamedResponse(function () use ($transactions) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Payment ID',
            'Amount',
            'Charges',
            'Closing Balance',
            'Beneficiary',
            'UTR',
            'Status',
            'Date'
        ]);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->externalRef,
                $t->amount,
                $t->charges,
                $t->closing_balance,
                $t->beneficiaryName,
                $t->externalRef,
                $t->status,
                $t->created_at
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}

//aeps admin setlement
private function filterQueryAdminSTLM($request)
{
    $query = DB::table('aeps_stlm');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('payment_id', 'like', "%$search%")
              ->orWhere('beneficiary_name', 'like', "%$search%")
              ->orWhere('bank_ref_no', 'like', "%$search%");
        });
    }
    if($request->filled('remId')){
        $remId=$request->remId;
          $query->where(function ($q) use ($remId) {
            $q->where('remId', 'like', "%$remId%")
              ->orWhere('beneficiary_name', 'like', "%$remId%")
              ->orWhere('bank_ref_no', 'like', "%$remId%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

public function exportCsvAdminSTLM(Request $request)
{
    $transactions = $this->filterQueryAdminSTLM($request)
        ->orderBy('created_at', 'desc')
        ->get();

    $response = new StreamedResponse(function () use ($transactions) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Payment ID',
            'Amount',
            'Charges',
            'Closing Balance',
            'Beneficiary',
            'UTR',
            'Status',
            'Date'
        ]);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->payment_id,
                $t->amount,
                $t->charge,
                $t->closing_balance,
                $t->beneficiary_name,
                $t->bank_ref_no,
                $t->status,
                $t->created_at
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}
public function txnTrackAdminSTLM(Request $request)
    {

     $txn = $this->filterQueryAdminSTLM($request)
        ->orderBy('created_at', 'desc')
        ->paginate(30)
        ->withQueryString();


      //  return $txn;die();
        return view('txn.indexSTLM',compact('txn'));
    }



    //aeronpay IMPS
    private function filterQueryAdminV2($request)
{
    $query = DB::table('xpresspayout2');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('payment_id', 'like', "%$search%")
              ->orWhere('beneficiary_name', 'like', "%$search%")
              ->orWhere('bank_ref_no', 'like', "%$search%");
        });
    }
    if($request->filled('remId')){
        $remId=$request->remId;
          $query->where(function ($q) use ($remId) {
            $q->where('remId', 'like', "%$remId%")
              ->orWhere('beneficiary_name', 'like', "%$remId%")
              ->orWhere('bank_ref_no', 'like', "%$remId%");
        });
    }

    if ($request->filled('from_date')) {
        $query->whereDate('created_at', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('created_at', '<=', $request->to_date);
    }

    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    return $query;
}

public function exportCsvAdminv2(Request $request)
{
    $transactions = $this->filterQueryAdminV2($request)
        ->orderBy('created_at', 'desc')
        ->get();

    $response = new StreamedResponse(function () use ($transactions) {
        $handle = fopen('php://output', 'w');

        fputcsv($handle, [
            'Payment ID',
            'Amount',
            'Charges',
            'Closing Balance',
            'Beneficiary',
            'UTR',
            'Status',
            'Date'
        ]);

        foreach ($transactions as $t) {
            fputcsv($handle, [
                $t->payment_id,
                $t->amount,
                $t->charge,
                $t->closing_balance,
                $t->beneficiary_name,
                $t->bank_ref_no,
                $t->status,
                $t->created_at
            ]);
        }

        fclose($handle);
    });

    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition', 'attachment; filename="transactions.csv"');

    return $response;
}
public function txnTrackAdminV2(Request $request)
    {

     $txn = $this->filterQueryAdminV2($request)
        ->orderBy('created_at', 'desc')
        ->paginate(30)
        ->withQueryString();


      //  return $txn;die();
        return view('txn.index2',compact('txn'));
    }
}
