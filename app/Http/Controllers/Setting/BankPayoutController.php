<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankPayout;
use App\Models\BankProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BankPayoutController extends Controller
{
    /**
     * Display a listing of the services.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $Route = 'Bank Payout';
        $user = Auth::user();
        $bank = Bank::all();
        $product = Product::all();
        $payouts = BankPayout::paginate(25);
        if ($request->ajax()) {

            $query = BankPayout::with(['bank','product']);

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
                ->editColumn('rate', function ($row) {
                    return $row->rate ? $row->rate : '-';
                })
                
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('application', 'update')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/bank-payout/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    }

                    if (auth()->user()->hasPermission('application', 'delete')) {
                    
                            $btn .= "<img class='delete-payout-btn' data-payout-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                        
                        
                    }
                    return $btn;
                })

                ->rawColumns(['action'])
                ->make(true);
        }
        return view('Frontend.Setting.BankPayout.index', compact('Route', 'payouts','bank','product'));
    }

    public function create()
    {
        $Route = 'Bank Payout';
        $user = Auth::user();
        $banks = Bank::all();
        return view('Frontend.Setting.BankPayout.create', compact('Route', 'banks'));
    }


    public function store(Request $request)
    {
        try {
            // Validate incoming request data
            $validatedData = $request->validate([
                'bank_id'     => 'required|string|max:255',
                'product_id'  => 'required|string|max:255',
                'group'  => 'required|string|max:255',
                'payout_rate' => 'required',
            ]);

            $bank_id = $request->bank_id;
            $product_id = $request->product_id;
            $group = $request->group;
            $payout_rate = $request->payout_rate;
            // Check if the service already exists for the given bank
            $checkExist = BankPayout::where('bank_id', $bank_id)->where('group', $group)->where('product_id', $product_id)->exists();
            if ($checkExist) {
                flash()
                    ->error('Payout rate already exists for this bank and product')
                    ->flash();
                return redirect()->to('bank-payout');
            }

            $bank_payout = new BankPayout();
            $bank_payout->bank_id = $bank_id;
            $bank_payout->product_id = $product_id;
            $bank_payout->group = $group;
            $bank_payout->rate = $payout_rate;
            $bank_payout->save();

            flash()
                ->success('Service created successfully ')
                ->flash();
            return redirect()->to('bank-payout');


            // Return success response
            return response()->json(['success' => true, 'message' => 'Hotel created successfully.']);
        } catch (\Throwable $th) {
            flash()
                ->error('Something went wrong ')
                ->flash();
            return redirect()->to('bank-payout');
        }
    }

    public function edit($id)
    {
        $Route = 'Bank Payout';
        $user = Auth::user();
        $payout = BankPayout::findorfail($id);
        $banks = Bank::all();
        $productIdsArray = BankProduct::where('bank_id', $payout->bank_id)
            ->pluck('product_id')
            ->toArray();

        $products = Product::whereIn('id', $productIdsArray)->where('group', $payout->group)->get();

        return view('Frontend.Setting.BankPayout.edit', compact('Route', 'payout', 'banks', 'products'));
    }

    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'bank_id'     => 'required|string|max:255',
                'product_id'  => 'required|string|max:255',
                'group'  => 'required|string|max:255',
                'payout_rate' => 'required',
            ]);

            $bank_id = $request->bank_id;
            $product_id = $request->product_id;
            $group = $request->group;
            $payout_rate = $request->payout_rate;
            // Check if the service already exists for the given bank

            // Find the existing service
            $bank_payout = BankPayout::find($id);
            if (!$bank_payout) {
                flash()
                    ->error('Payout not found ')
                    ->flash();
                return redirect()->to('bank-payout');
            }
            // Update the service
            $bank_payout->bank_id = $bank_id;
            $bank_payout->product_id = $product_id;
            $bank_payout->group = $group;
            $bank_payout->rate = $payout_rate;
            $bank_payout->save();

            // Return a success response
            flash()
                ->success('Service updated successfully ')
                ->flash();
            return redirect()->to('bank-payout');
        } catch (\Throwable $th) {
            //throw $th;
            flash()
                ->error('Something went wrong')
                ->flash();
            return redirect()->to('bank-payout');
        }
        // Validate incoming request data

    }

    public function destory($id)
    {
        $payout = BankPayout::findorfail($id);
        $payout->delete();
        flash()
            ->success('Bank Target deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); 
    }
}
