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

        if ($request->ajax()) {

            $query = BankMisTracker::query();

            // DATE FILTER 
            if ($request->filled('date')) {
                $now = Carbon::now();

                switch ($request->date) {

                    case 'today':
                        $query->whereDate('created_at', Carbon::today());
                        break;

                    case 'yesterday':
                        $query->whereDate('created_at', Carbon::yesterday());
                        break;

                    case 'this_week':
                        $query->whereBetween('created_at', [
                            $now->copy()->startOfWeek(),
                            $now->copy()->endOfWeek()
                        ]);
                        break;

                    case 'last_week':
                        $query->whereBetween('created_at', [
                            $now->copy()->subWeek()->startOfWeek(),
                            $now->copy()->subWeek()->endOfWeek()
                        ]);
                        break;

                    case 'this_month':
                        $query->whereBetween('created_at', [
                            $now->copy()->startOfMonth(),
                            $now->copy()->endOfMonth()
                        ]);
                        break;

                    case 'last_month':
                        $query->whereBetween('created_at', [
                            $now->copy()->subMonth()->startOfMonth(),
                            $now->copy()->subMonth()->endOfMonth()
                        ]);
                        break;

                    case 'last_3_months':
                        $query->whereBetween('created_at', [
                            $now->copy()->subMonths(2)->startOfMonth(),
                            $now->copy()->endOfMonth()
                        ]);
                        break;

                    case 'last_6_months':
                        $query->whereBetween('created_at', [
                            $now->copy()->subMonths(5)->startOfMonth(),
                            $now->copy()->endOfMonth()
                        ]);
                        break;

                    case 'this_year':
                        $query->whereBetween('created_at', [
                            $now->copy()->startOfYear(),
                            $now->copy()->endOfYear()
                        ]);
                        break;

                    case 'last_year':
                        $query->whereBetween('created_at', [
                            $now->copy()->subYear()->startOfYear(),
                            $now->copy()->subYear()->endOfYear()
                        ]);
                        break;

                    case 'custom':
                        if ($request->filled('date_range')) {
                            [$start, $end] = array_map('trim', explode('-', $request->date_range));
                            $query->whereBetween('created_at', [$start, $end]);
                        }
                        break;
                }
            }

            // OTHER FILTERS 
            if ($request->filled('bank_name')) {
                $query->where('bank', $request->bank_name);
            }

            if ($request->filled('product_name')) {
                $query->where('product', $request->product_name);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // DATATABLE 
            return DataTables::of($query)
                ->addIndexColumn()

                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                })

                ->editColumn('bank_mis_month', fn($row) => $row->bank_mis_month ?? '-')
                ->editColumn('bank', fn($row) => $row->bank ?? '-')
                ->editColumn('product', fn($row) => $row->product ?? '-')
                ->editColumn('total_cases', fn($row) => $row->total_cases ?? '-')
                ->editColumn('matched_cases', fn($row) => $row->matched_cases ?? '-')
                ->editColumn('unmatched_cases', fn($row) => $row->unmatched_cases ?? '-')
                ->editColumn('status', function ($row) {
                    $badge = 'secondary';
                    $text  = ucfirst($row->status);

                    if ($row->status === 'pending') {
                        $badge = 'warning';
                    } elseif ($row->status === 'received') {
                        $badge = 'success';
                    }
                    
                    return '<span class="badge bg-' . $badge . '">' . $text . '</span>';
                })

                ->rawColumns(['checkbox', 'status'])
                ->make(true);
        }

        return view('Frontend.MISTracker.index', compact('Route', 'banks', 'product'));
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
