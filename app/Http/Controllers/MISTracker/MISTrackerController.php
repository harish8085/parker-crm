<?php

namespace App\Http\Controllers\MISTracker;
use App\Models\Bank;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MISTrackerController extends Controller
{
    public function index()
    {
         $Route = 'mis-tracker'; 
        $banks = Bank::all();
        return view('Frontend.MISTracker.index',compact('Route', 'banks'));
    }
}
