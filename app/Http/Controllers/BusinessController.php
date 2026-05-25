<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BusinessController extends Controller
{
    public function index()
    {
        $businesses = Business::latest()->paginate(10);
        return view('businesses.index', compact('businesses'));
    }

    public function create()
    {
        //return "hello";die();  
        return view('businesses.create');
    }

    public function store(Request $request)
    {
       
        $request->validate([
            'domain_name' => 'required|unique:businesses',
            'businessId' => 'required|unique:businesses',
            'businessEmail' => 'required|email|unique:businesses',
            'name' => 'required',
            'title' => 'required',
            'city' => 'required',
            'pin' => 'required',
            'sidebar_color' => 'required',
            'icon_color' => 'required',
        ]);
return $request->all();die();
        $data = $request->all();

        // Handle file uploads
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('favicons', 'public');
        }

        Business::create($data);

        return redirect()->route('businesses.index')->with('success', 'Business created successfully!');
    }

    public function show(Business $business)
    {
        return view('businesses.show', compact('business'));
    }

    public function edit(Business $business)
    {
        return view('businesses.edit', compact('business'));
    }

    public function update(Request $request, Business $business)
    {
        $request->validate([
            'domain_name' => 'required|unique:businesses,domain_name,' . $business->id,
            'business_id' => 'required|unique:businesses,business_id,' . $business->id,
            'business_email' => 'required|email|unique:businesses,business_email,' . $business->id,
            'name' => 'required',
            'title' => 'required',
            'city' => 'required',
            'pin' => 'required',
            'sidebar_color' => 'required',
            'icon_color' => 'required',
        ]);

        $data = $request->all();

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('logos', 'public');
        }
        if ($request->hasFile('favicon')) {
            $data['favicon'] = $request->file('favicon')->store('favicons', 'public');
        }

        $business->update($data);

        return redirect()->route('businesses.index')->with('success', 'Business updated successfully!');
    }

    public function destroy(Business $business)
    {
        $business->delete();
        return redirect()->route('businesses.index')->with('success', 'Business deleted successfully!');
    }
}
