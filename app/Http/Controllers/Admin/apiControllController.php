<?php

namespace App\Http\Controllers\Admin;
use App\Models\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class apiControllController extends Controller
{
        public function index()
    {
        $apis = Api::latest()->get();
        return view('apis.index', compact('apis'));
    }

    public function create()
    {
        return view('apis.create');
    }

    public function store(Request $request)
    {
        Api::create($request->all());
        return redirect()->route('apis.index')->with('success', 'API Created');
    }

    public function edit($id)
    {
        $api = Api::findOrFail($id);
        return view('apis.edit', compact('api'));
    }

    public function update(Request $request, $id)
    {
        $api = Api::findOrFail($id);
        $api->update($request->all());

        return redirect()->route('apis.index')->with('success', 'API Updated');
    }

    public function destroy($id)
    {
        Api::findOrFail($id)->delete();
        return back()->with('success', 'API Deleted');
    }
}
