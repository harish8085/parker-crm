<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    function index()
    {
        return view('Frontend.Insurance.index');
    }
}
