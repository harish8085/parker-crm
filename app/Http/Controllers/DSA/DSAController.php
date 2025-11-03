<?php

namespace App\Http\Controllers\DSA;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\DSACode;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class DSAController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Bank';
        $banks =Bank::all();
        $product = Product::all();
        $dsa_codes = DSACode::all();
        if ($request->ajax()) {

            $query = DSACode::with(['bank','product']);

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
    
            if ($request->product_name) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('name', $request->product_name);
                });
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('bank_id', function ($row) {
                    return $row->bank && $row->bank->name ? $row->bank->name : '-';
                })
                ->editColumn('product_id', function ($row) {
                    return $row->product && $row->product->name ? $row->product->name : '-';
                })
                ->editColumn('group', function ($row) {
                    return $row->group ? $row->group : '-'; 
                })
                ->editColumn('code', function ($row) {
                    return $row->code ? $row->code : '-'; 
                })
                ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('dsa-code', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/dsa-code/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('dsa-code', 'delete')) {
                                $btn .= "<img class='delete-dsacode-btn' data-dsacode-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                        }
                        return $btn;
                    })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Frontend.DSA.index', compact('Route', 'banks', 'dsa_codes','product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|string|max:255',
            'product_id' => 'required|string|max:255',
            'code' => 'required|string|max:255',
            'group' => 'required|string|max:255'
        ]);

        $checkExist = DSACode::where(['bank_id'=> $request->bank_id,'product_id' => $request->product_id, 'group' => $request->group, 'code' => $request->code])->get();
        if ($checkExist->isNotEmpty()) {
            return redirect()->to('/dsa-code')->with('error', 'Bank Code already exists.');
        }
        DSACode::create($request->all());
        return redirect()->to('/dsa-code')->with('success', 'Bank Code created successfully');
    }


    public function edit($id)
    {
        $dsa =   DSACode::findorfail($id);
        $banks = Bank::all();
        $products = Product::all();
        return view("Frontend.DSA.edit",compact('dsa','banks','products'));
    }

    public function update(Request $request, DSACode $dsaCode)
    {
        $request->validate([
            'bank_id' => 'required|string|max:255',
            'product_id' => 'required|string|max:255',
            'dsa_code' => 'required|string|max:255',
            'group' => 'required|string|max:255'
        ]);

        $checkExist = DSACode::where(['product_id' => $request->product_id, 'group' => $request->group, 'code' => $request->code])->where('id', '!=', $dsaCode->id)->get();
        if ($checkExist->isNotEmpty()) {
            return false;
        }
        $dsaCode->update($request->all());
        return redirect()->to('/dsa-code')->with('success', 'Bank Code updated successfully.');
    }

    public function destory(DSACode $dsaCode)
    {
        $dsaCode->delete();
        flash()
            ->success('Bank Code deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); // assuming it's an AJAX request
    }
}
