<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankProduct;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BankController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Bank';
        $banks = Bank::all();
        if ($request->ajax()) {

            $query = Bank::query();
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
                $query->where('name', $request->bank_name);
            }
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->name ? $row->name : '-';
                })
                ->editColumn('short_name', function ($row) {
                    return $row->short_name ? $row->short_name : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('bank', 'update')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/bank/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    }

                    if (auth()->user()->hasPermission('bank', 'delete')) {
                        $btn .= "<img class='delete-bank-btn' data-bank-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                    }
                    return $btn;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Frontend.Bank.index', compact('Route', 'banks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255'
        ]);

        $checkExist = Bank::where(['name' => $request->name])->get();
        if ($checkExist->isNotEmpty()) {

            return redirect()->to('/bank')->with('error', 'Bank name already exist.');
        }
        $checkExist = Bank::where(['short_name' => $request->short_name])->get();
        if ($checkExist->isNotEmpty()) {

            return redirect()->to('/bank')->with('error', 'Short name already exist.');
        }
        Bank::create($request->all());
        return redirect()->to('/bank')->with('success', 'Bank created successfully.');
    }

    public function getBank($id)
    {
        $Route = 'Edit Bank';
        $bank = Bank::findOrFail($id);
        $banks = Bank::all();
        return view('Frontend.Bank.edit', compact('bank', 'banks'));
    }

    public function update(Request $request, Bank $bank)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:255',
        ]);
        $checkExist = Bank::where(['name' => $request->name])->where('id', '!=', $bank->id)->get();
        if ($checkExist->isNotEmpty()) {
            flash()
                ->error('Bank name already exist')
                ->flash();
            return true;
        }
        $checkExist = Bank::where(['short_name' => $request->short_name])->where('id', '!=', $bank->id)->get();
        if ($checkExist->isNotEmpty()) {
            flash()
                ->error('Short name already exist')
                ->flash();
            return true;
        }
        $bank->update($request->all());
        flash()
            ->success('Bank updated successfully ')
            ->flash();

        return redirect()->route('bank.index');
    }
    public function destory(Bank $bank)
    {

        $bank->delete();
        $bank_product = BankProduct::where('bank_id', $bank->id)->delete();

        flash()
            ->success('Bank deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); // assuming it's an AJAX request
    }
}
