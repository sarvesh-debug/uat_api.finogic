<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackageController extends Controller
{
    public function index()
    {
        $packages = Package::latest()->get();
        return view('packages.index', compact('packages'));
    }

    public function create()
    {
        return view('packages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'packageName' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        Package::create([
            'packageName' => $request->packageName,
            'created_by' => Auth::user()->name ?? 'Admin',
            'status' => $request->status ?? 1,
        ]);

        return redirect()->route('packages.index')->with('success', 'Package created successfully!');
    }

    public function edit(Package $package)
    {
        return view('packages.edit', compact('package'));
    }

    public function update(Request $request, Package $package)
    {
        $request->validate([
            'packageName' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $package->update([
            'packageName' => $request->packageName,
            'status' => $request->status ?? 0,
        ]);

        return redirect()->route('packages.index')->with('success', 'Package updated successfully!');
    }

    public function destroy(Package $package)
    {
        $package->delete();
        return redirect()->route('packages.index')->with('success', 'Package deleted successfully!');
    }
}
