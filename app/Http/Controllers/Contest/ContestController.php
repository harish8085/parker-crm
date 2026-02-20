<?php

namespace App\Http\Controllers\Contest;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    function index()
    {
        return view('Frontend.Contest.index');
    }
}
