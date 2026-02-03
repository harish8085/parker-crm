<?php

namespace App\Http\Controllers\SampleSheet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SampleSheetController extends Controller
{
    public function index()
    {
        return view('Frontend.sample-sheet.index');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:xlsx'
        ]);

        // path where sample exists
        $destination = public_path('assets/sample/sample.xlsx');

        // remove old if exists
        if (file_exists($destination)) {
            unlink($destination);
        }

        // always save with same filename sample.xlsx
        $request->file('csv_file')->move(public_path('assets/sample'), 'sample.xlsx');

        return back()->with('success', 'Sample Updated Successfully');
    }
}