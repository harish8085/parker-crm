<?php

namespace App\Http\Controllers\MISTracker;

use App\Models\Bank;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\BankMisTracker;

class MISTrackerController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'mis-tracker';
        $banks = Bank::all();
        $product = Product::all();


        if ($request->ajax()) {
            $query = BankMisTracker::with(['bank', 'product']);
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
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';

                    return '';
                })
                ->editColumn('bank_id', function ($row) {
                    return $row->bank_id ? $row->bank->name : '-';
                })
                ->editColumn('product_id', function ($row) {
                    return $row->product_id ? $row->product->name : '-';
                })
                ->editColumn('group', function ($row) {
                    return $row->group ? $row->group : '-';
                })
                ->editColumn('customer_name', function ($row) {
                    return $row->customer_name ? $row->customer_name : '-';
                })
                ->editColumn('customer_firm_name', function ($row) {
                    return $row->customer_firm_name ? $row->customer_firm_name : '-';
                })
                ->editColumn('location', function ($row) {
                    return $row->location ? $row->location : '-';
                })
                ->editColumn('case_location', function ($row) {
                    return $row->case_location ? $row->case_location : '-';
                })
                ->editColumn('disbAmount', function ($row) {
                    return $row->disbAmount ? $row->disbAmount : '-';
                })
                ->editColumn('payout_amount', function ($row) {
                    return $row->payout_amount ? $row->payout_amount : '-';
                })
                ->editColumn('payout_rate', function ($row) {
                    return $row->payout_rate ? $row->payout_rate : '-';
                })
                ->editColumn('pf', function ($row) {
                    return $row->pf ? $row->pf : '-';
                })
                ->editColumn('subvention', function ($row) {
                    return $row->subvention ? $row->subvention : '-';
                })
                ->editColumn('roi', function ($row) {
                    return $row->roi ? $row->roi : '-';
                })
                ->editColumn('insurance', function ($row) {
                    return $row->insurance ? $row->insurance : '-';
                })
                ->editColumn('otc_pdd_status', function ($row) {
                    return $row->otc_pdd_status ? $row->otc_pdd_status : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('invoice', 'view')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/invoice/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    // if (auth()->user()->hasPermission('invoice', 'delete')) {
                    //         $btn .= "<img class='delete-btn' data-bank-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                    // }
                    // i want to add check box 
                    return $btn;
                })
                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
            $query->where('id', $channelroleId);
        })->get();

        $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
            $query->where('id', $salesroleId);
        })->get();



        return view('Frontend.MISTracker.index', compact('Route', 'banks', 'product'));
    }
}
