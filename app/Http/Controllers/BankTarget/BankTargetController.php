<?php

namespace App\Http\Controllers\BankTarget;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankTarget;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BankTargetController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Bank';
        $banks = Bank::all();
        if ($request->ajax()) {

            $query = BankTarget::with(['bank']);

            if ($request->date) {
                $now = Carbon::now();
                if ($request->date == 'today') {
                    $today = Carbon::today()->toDateString();
                    $query = $query->whereDate('created_at', $today);
                } elseif ($request->date == 'yesterday') {
                    $yesterday = Carbon::yesterday()->toDateString();
                    $query = $query->whereDate('created_at', $yesterday);
                } elseif ($request->date == 'this_week') {
                    $weekStartDate = $now->startOfWeek()->toDateString();
                    $weekEndDate = $now->endOfWeek()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $weekStartDate)
                                  ->whereDate('created_at', '<=', $weekEndDate);
                } elseif ($request->date == 'last_week') {
                    $subWeek = $now->subWeek();
                    $lastWeekStartDate = $subWeek->startOfWeek()->toDateString();
                    $lastWeekEndDate = $subWeek->endOfWeek()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $lastWeekStartDate)
                                  ->whereDate('created_at', '<=', $lastWeekEndDate);
                } elseif ($request->date == 'this_month') {
                    $startOfMonth = $now->startOfMonth()->toDateString();
                    $endOfMonth = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $startOfMonth)
                                  ->whereDate('created_at', '<=', $endOfMonth);
                } elseif ($request->date == 'last_month') {
                    $subMonth = $now->subMonth();
                    $startOfMonth = $subMonth->startOfMonth()->toDateString();
                    $endOfMonth = $subMonth->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $startOfMonth)
                                  ->whereDate('created_at', '<=', $endOfMonth);
                } elseif ($request->date == 'last_3_months') {
                    $thirdLastMonthStart = $now->subMonths(2)->startOfMonth()->toDateString();
                    $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $thirdLastMonthStart)
                                  ->whereDate('created_at', '<=', $lastOneMonthEnd);
                } elseif ($request->date == 'last_6_months') {
                    $Last6thMonthStart = $now->subMonths(5)->startOfMonth()->toDateString();
                    $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $Last6thMonthStart)
                                  ->whereDate('created_at', '<=', $lastOneMonthEnd);
                } elseif ($request->date == 'this_year') {
                    $thisYearStart = $now->startOfYear()->toDateString();
                    $thisYearEnd = $now->endOfYear()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $thisYearStart)
                                  ->whereDate('created_at', '<=', $thisYearEnd);
                } elseif ($request->date == 'last_year') {
                    $lastYear = $now->subYear();
                    $lastYearStart = $lastYear->startOfYear()->toDateString();
                    $lastYearEnd = $lastYear->endOfYear()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $lastYearStart)
                                  ->whereDate('created_at', '<=', $lastYearEnd);
                } elseif ($request->date == 'custom' && isset($request->date_range)) {
                    if (strpos($request->date_range, 'to') !== false) {
                        $dates = explode('to', $request->date_range);
                        $startDate = trim($dates[0]);
                        $endDate = trim($dates[1]);
                        $query = $query->whereDate('created_at', '>=', $startDate)
                                      ->whereDate('created_at', '<=', $endDate);
                    } else {
                        throw new \Exception('Date range is not provided or is incorrectly formatted.');
                    }
                }
            }
            
            if ($request->bank_name) {
                $query->whereHas('bank', function ($q) use ($request) {
                    $q->where('name', $request->bank_name);
                });
            }
    
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bank_id', function ($row) {
                    return $row->bank && $row->bank->name ? $row->bank->name : '-';
                })
                ->editColumn('target_amount', function ($row) {
                    return $row->target_amount ? $row->target_amount : '-'; 
                })
                ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('bank-target', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/bank-target/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('bank-target', 'delete')) {
                           
                                $btn .= "<img class='delete-banktarget-btn' data-banktarget-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('Frontend.BankTarget.index', compact('Route', 'banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|string|max:255',
            'target_amount' => 'required|string|max:255'
        ]);

        $checkExist = BankTarget::where(['bank_id' => $request->bank_id])->get();
        if ($checkExist->isNotEmpty()) {

            return redirect()->to('/bank-target')->with('error', 'Bank target amount already exist.');
        }
        BankTarget::create($request->all());
        return redirect()->to('/bank-target')->with('success', 'Bank created successfully.');
    }

    public function getBankTarget(BankTarget $bankTarget)
    {
        $id = $bankTarget->id;
        $data = BankTarget::findorfail($id);
        $banks = Bank::all();
        return view("Frontend.BankTarget.edit",compact('data','banks'));
    }

    public function update(Request $request, BankTarget $bankTarget)
    {
        // $request->validate([
        //     'bank_id' => 'required|string|max:255',
        //     'target_amount' => 'required|string|max:255'
        // ]);
        $checkExist = BankTarget::where(['bank_id' => $request->bank_id])->where('id', '!=', $bankTarget->id)->get();
        if ($checkExist->isNotEmpty()) {
                return redirect()->to('/bank-target')->with('error', 'Bank name already exist');
        }
        $bankTarget->update($request->all());
            return redirect()->to('/bank-target')->with('success', 'Bank updated successfully ');

    }
    public function destory(BankTarget $bankTarget)
    {

        $bankTarget->delete();
        flash()
            ->success('Bank Target deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); // assuming it's an AJAX request
    }
}
