<?php

namespace App\Http\Controllers\SheetMatching;

use App\Http\Controllers\Controller;
use App\Models\SheetMatching;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Bank;
use App\Models\Product;
use Spatie\SimpleExcel\SimpleExcelReader;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class SheetMatchingController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Settlement';
        $user = Auth::user();
        $banks = Bank::get();
        $products = Product::get();
        $sheetmatching = SheetMatching::all();
        if ($request->ajax()) {

            $query = SheetMatching::with(['bank', 'product']);

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

            if ($request->group) {
                $query->where('group', $request->group);
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
                ->editColumn('app_id', function ($row) {
                    return $row->app_id ? $row->app_id : '-';
                })
                ->editColumn('case_location', function ($row) {
                    return $row->case_location ? $row->case_location : '-';
                })
                ->editColumn('customer_name', function ($row) {
                    return $row->customer_name ? $row->customer_name : '-';
                })
                ->editColumn('customer_firm_name', function ($row) {
                    return $row->customer_firm_name ? $row->customer_firm_name : '-';
                })
                ->editColumn('disbAmount', function ($row) {
                    return $row->disbAmount ? $row->disbAmount : '-';
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
                ->editColumn('payout_amount', function ($row) {
                    return $row->payout_amount ? $row->payout_amount : '-';
                })
                ->editColumn('payout_rate', function ($row) {
                    return $row->payout_rate ? $row->payout_rate : '-';
                })
                ->editColumn('date', function ($row) {
                    return $row->date ? $row->date : '-';
                })
                ->editColumn('month', function ($row) {
                    return $row->month ? $row->month : '-';
                })
                ->editColumn('pf_per', function ($row) {
                    return $row->pf_per ? $row->pf_per : '-';
                })
                ->editColumn('kli', function ($row) {
                    return $row->kli ? $row->kli : '-';
                })
                ->editColumn('kli_payout_per', function ($row) {
                    return $row->kli_payout_per ? $row->kli_payout_per : '-';
                })
                ->editColumn('kli_payout', function ($row) {
                    return $row->kli_payout ? $row->kli_payout : '-';
                })
                ->editColumn('kgi', function ($row) {
                    return $row->kgi ? $row->kgi : '-';
                })
                ->editColumn('kgi_payout_per', function ($row) {
                    return $row->kgi_payout_per ? $row->kgi_payout_per : '-';
                })
                ->editColumn('kgi_payout', function ($row) {
                    return $row->kgi_payout ? $row->kgi_payout : '-';
                })

                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= "<img onclick=\"window.location.href='" . url('/sheet-matching/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    $btn .= "<img class='delete-sheet-btn' data-sheet-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                    return $btn;
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }
        return view('Frontend.SheetMatching.index', compact('Route', 'banks', 'products', 'sheetmatching'));
    }
    public function addSheetData()
    {
        $Route = 'addSheet';
        $banks = Bank::get();
        return view('Frontend.SheetMatching.add', compact('Route', 'banks'));
    }
    public function storeSheetData(Request $request)
    {
        $validatedData = $request->validate([
            'bank_id' => 'required|string|max:255',
            'product_id' => 'required|string|max:255',
            'group' => 'required',
            'app_id' => 'required',
            'case_location' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'customer_firm_name' => 'nullable|string',
            'disbAmount' => 'required|string',
            'pf' => 'nullable|string', // Not required
            'subvention' => 'nullable|string', // Not required
            'roi' => 'nullable|string', // Not required
            'insurance' => 'nullable|string', // Not required
            'otc_pdd_status' => 'nullable|string', // Not required
            'payout_amount' => 'nullable|string', // Not required
            'payout_rate' => 'nullable|string', // Not required
            'date' => 'nullable|string', // Not required
            'month' => 'nullable|string', // Not required
            'pf_per' => 'nullable|string', // Not required
            'kli' => 'nullable|string', // Not required
            'kli_payout_per' => 'nullable|string', // Not required
            'kli_payout' => 'nullable|string', // Not required
            'kgi' => 'nullable|string', // Not required
            'kgi_payout_per' => 'nullable|string', // Not required
            'kgi_payout' => 'nullable|string', // Not required
        ]);

        // Check for unique combination of bank_id and product_id
        $exists = SheetMatching::where('bank_id', $request->bank_id)
            ->where('product_id', $request->product_id)
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors(['The combination of bank and product already exists.']);
        }
        SheetMatching::create($validatedData);

        return redirect()->route('sheet-matching.index')->with('success', 'Data added successfully');
    }

    public function edit($id)
    {
        $data = SheetMatching::findOrFail($id);
        $banks = Bank::get();
        $products = Product::get();

        return view('Frontend.SheetMatching.edit', compact('data', 'banks', 'products'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'bank_id' => 'required|string|max:255',
            'product_id' => 'required|string|max:255',
            'group' => 'required',
            'app_id' => 'required',
            'case_location' => 'nullable|string',
            'customer_name' => 'nullable|string',
            'customer_firm_name' => 'nullable|string',
            'disbAmount' => 'nullable|string',
            'pf' => 'nullable|string',
            'subvention' => 'nullable|string',
            'roi' => 'nullable|string',
            'insurance' => 'nullable|string',
            'otc_pdd_status' => 'nullable|string',
            'payout_amount' => 'nullable|string',
            'payout_rate' => 'nullable|string',
            'date' => 'nullable|string', // Not required
            'month' => 'nullable|string', // Not required
            'pf_per' => 'nullable|string', // Not required
            'kli' => 'nullable|string', // Not required
            'kli_payout_per' => 'nullable|string', // Not required
            'kli_payout' => 'nullable|string', // Not required
            'kgi' => 'nullable|string', // Not required
            'kgi_payout_per' => 'nullable|string', // Not required
            'kgi_payout' => 'nullable|string', // Not required
        ]);

        $dataSheet = SheetMatching::findOrFail($id);
        $dataSheet->delete();
        SheetMatching::create($validatedData);

        return redirect()->route('sheet-matching.index')->with('success', 'Data updated successfully');
    }

    public function destroy(SheetMatching $sheet)
    {

        $sheet->delete();

        flash()
            ->success('Sheet deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); // assuming it's an AJAX request
    }

    public function getFileData(Request $request)
    {
        // Handle the uploaded file
        $file = $request->file('file');
        $tempFilePath = $file->storeAs('tmp', 'uploaded.xlsx');

        // Create a SimpleExcelReader instance
        $excel = SimpleExcelReader::create(storage_path('app/' . $tempFilePath));

        // Get the rows as a LazyCollection
        $rows = $excel->getRows();

        // Convert the LazyCollection to a regular Collection to manipulate keys
        $firstRow = $rows->first(); // Get the first row as an array

        // Ensure there is a row to get keys from
        if (!$firstRow) {
            return response()->json(['error' => 'The file is empty or has no valid rows'], 400);
        }

        // Get the keys of the first row
        $keys = array_keys($firstRow);

        return response()->json($keys);
    }
}
