<?php
namespace App\Http\Controllers;
use App\Models\ApiPipe;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function index()
    {
        $apis = ApiPipe::latest()->get();

        //return $apis;
        // Group for dropdown
        $groupedServices = $apis->groupBy('service');

        return view('apiService.index', compact('apis', 'groupedServices'));
    }

    public function create()
    {
        return view('apis.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'service' => 'required',
            'pipe' => 'required',
        ]);

        ApiPipe::create($request->all());

        return redirect()->route('apiService.index')->with('success', 'API Added Successfully');
    }

    public function edit(ApiPipe $api)
    {
        return view('apiService.edit', compact('api'));
    }

    public function update(Request $request, ApiPipe $api)
    {
        $request->validate([
            'service' => 'required',
            'pipe' => 'required',
        ]);

        $api->update($request->all());

        return redirect()->route('apiService.index')->with('success', 'API Updated Successfully');
    }

    public function destroy(ApiPipe $api)
    {
        $api->delete();

        return back()->with('success', 'API Deleted Successfully');
    }

    public function updateStatus(Request $request)
{
    $pipes = $request->pipes;

    if (!$pipes) {
        return back()->with('error', 'No data selected');
    }

    foreach ($pipes as $service => $selectedPipe) {

        if (!$selectedPipe) continue;

        // Step 1: Sabko inactive kar do (status = 0)
        \DB::table('api_pipes')
            ->where('service', $service)
            ->update(['status' => 0]);

        // Step 2: Selected pipe ko active kar do (status = 1)
        \DB::table('api_pipes')
            ->where('service', $service)
            ->where('pipe', $selectedPipe)
            ->update(['status' => 1]);
    }

    return back()->with('success', 'Pipe Status Updated Successfully');
}
}