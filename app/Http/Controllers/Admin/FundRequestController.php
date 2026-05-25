<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FundRequestController extends Controller
{
    public function index()
    {
        // Fetch all fund requests
        $fundRequests = DB::table('adm_fundrequest')->orderBy('id', 'desc')->get();

        return view('admin.fundrequests.index', compact('fundRequests'));
    }
}
