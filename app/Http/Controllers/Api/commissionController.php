<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Commission;

class commissionController extends Controller
{
    //  public function commList($id)
    //  {
    //     // Fetch commission data based on the provided ID
    //     $commissions = \DB::table('commissions')->where('packagesId', $id)->get();
    //     $packageId=$id;
    //     // Return a view with the commission data       
    //    return view('commission.addCommission', compact('commissions', 'packageId'));
    //  }

     public function index($id)
    {
         $commissions = \DB::table('commissions')->where('packagesId', $id)->get();
        $packageId=$id;

        //return $commissions;die();
        // Return a view with the commission data       
       return view('commission.addCommission', compact('commissions', 'packageId'));
    }

    public function store(Request $request)
    {
       //return $request;die();
        $request->validate([
            'packagesId' => 'required|string',
            'service' => 'required|string',
            'from_amount' => 'required|numeric|min:0',
            'to_amount' => 'required|numeric|min:0',
            'charge_in' => 'required|string',
            'charge' => 'required|numeric',
            'commissions_in' => 'nullable|string',
            'commissions' => 'nullable|numeric',
            'tds_in' => 'required|string',
            'tds' => 'required|numeric',
        ]);

        Commission::create($request->all());

        return redirect()->back()->with('success', 'Commission Added Successfully!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'packages' => 'required|string',
            'service' => 'required|string',
            'from_amount' => 'required|numeric|min:0',
            'to_amount' => 'required|numeric|min:0',
            'charge_in' => 'required|string',
            'charge' => 'required|numeric',
            'commissions_in' => 'required|string',
            'commissions' => 'required|numeric',
            'tds_in' => 'required|string',
            'tds' => 'required|numeric',
        ]);

        $commission = Commission::findOrFail($id);
        $commission->update($request->all());

        return redirect()->back()->with('success', 'Commission Updated Successfully!');
    }

    public function destroy($id)
    {
        $commission = Commission::findOrFail($id);
        $commission->delete();

        return redirect()->back()->with('success', 'Commission Deleted Successfully!');
    }
}
