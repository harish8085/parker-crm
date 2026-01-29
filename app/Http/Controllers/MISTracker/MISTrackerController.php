<?php

namespace App\Http\Controllers\MISTracker;

use App\Models\Bank;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use Carbon\Carbon;
use App\Models\BankMisTracker;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Auth;

class MISTrackerController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'MisTracker';
        $banks = Bank::all();
        $product = Product::all();
        $mistracker = BankMisTracker::all();

        if ($request->ajax()) {

            $query = BankMisTracker::query();

            // DATE FILTER
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

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                })

                ->editColumn('bank_mis_month', fn($row) => $row->bank_mis_month ?? '-')
                ->editColumn('bank', fn($row) => $row->bank ?? '-')
                ->editColumn('product', fn($row) => $row->product ?? '-')
                ->editColumn('status', function ($row) {
                    $badge = 'secondary';
                    if ($row->status === 'pending') {
                        $badge_text = 'Pending';
                        $badge = 'warning';
                    }

                    if ($row->status === 'received') {
                        $badge_text = 'Received';
                        $badge = 'success';
                    }

                    return '<span class="badge bg-' . $badge . '">' . ucfirst($badge_text) . '</span>';
                })


                ->addColumn('action', function ($row) {
                    $btn = '';

                    // if (auth()->user()->hasPermission('invoice', 'view')) {
                    //     $btn .= "<img onclick=\"window.location.href='" . url('/invoice/view/' . $row->id) . "'\" 
                    //           src='" . asset('assets/images/eye-icon.svg') . "'>";
                    // }

                    // if (auth()->user()->hasPermission('invoice', 'delete')) {
                    //     $btn .= "<img class='delete-btn' data-bank-id='" . $row->id . "' 
                    //           src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                    // }

                    return $btn;
                })

                ->rawColumns(['checkbox', 'action'])
                ->make(true);
        }

        return view('Frontend.MISTracker.index', compact('Route', 'mistracker', 'banks', 'product'));
    }

    public function filter(Request $request)
    {
        $Route = 'MisTracker';
        $user = Auth::user();
        $query = BankMisTracker::query();

        if ($request->bank_name !== null && $request->bank_name !== 'All') {
            $query->where('bank', $request->bank_name);
        }
        if ($request->product_name !== null && $request->product_name !== 'All') {
            $query->where('product', $request->product_name);
        }
        if ($request->from_date !== null) {
            $query->whereDate('bank_mis_month', '>=', $request->from_date);
        }
        if ($request->to_date !== null) {
            $query->whereDate('bank_mis_month', '<=', $request->to_date);
        }
        if ($request->status !== null) {
            $query->where('status',  $request->status);
        }

        $query->orderBy('id', 'desc');
        // Execute the query and fetch results
        $bank = $query->paginate(25);
        return view('Frontend.MISTracker.Table.mis_tracker_table', compact('Route', 'bank'));
    }
}
