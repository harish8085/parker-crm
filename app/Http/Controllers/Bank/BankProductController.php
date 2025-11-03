<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BankProductController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Bank';
        $banks = Bank::all();
        $products = Product::all();
        if ($request->ajax()) {

            $query = BankProduct::with(['bank', 'product']);
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
                ->editColumn('product_id', function ($row) {
                    return $row->product && $row->product->name ? $row->product->name : '-';
                })
                ->editColumn('product_group', function ($row) {
                    return $row->product ? $row->product->group : '-'; 
                })
                ->editColumn('auto_generate_lan', function ($row) {
                    return $row->auto_generate_lan ? 'Yes' : 'No'; 
                })
                ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('application', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/bank/update/product/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }

                        if (auth()->user()->hasPermission('application', 'delete')) {
                           
                                $btn .= "<img class='delete-bankProduct-btn' data-bankproduct-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('Frontend.BankProduct.index', compact('Route','banks','products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|string|max:255',
            'product_id' => 'required|string|max:255',
            'auto_generate_lan' => 'required|string|max:10',
        ]);

        $checkExist = BankProduct::where([ 'product_id' => $request->product_id, 'bank_id' => $request->bank_id])->get();
        if ($checkExist->isNotEmpty()) {

            return redirect()->to('/bank/view/product')->with('error', 'Bank product name already exist.');
        }
        BankProduct::create($request->all());
        return redirect()->to('/bank/view/product')->with('success', 'Bank product created successfully.');
    }

    public function getBankProduct($bankProduct)
    {
        $Route = 'Edit Bank Product';
        $bank = BankProduct::findOrFail($bankProduct);
        $banks = Bank::get();
        $products = Product::get();
        return view('Frontend.BankProduct.edit', compact('bank','banks','products'));
    }

    public function getProduct(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'group' => 'required|string',
        ]);

         $productIdsArray = BankProduct::where('bank_id', $request->bank_id)
            ->pluck('product_id')
            ->toArray();



        $products = Product::whereIn('id',$productIdsArray)->where('group',$request->group)->get();
        return $products;
    }

    public function getAllProduct(Request $request)
    {
        $request->validate([
            'bank_id' => 'required|exists:banks,id',
        ]);

        $productIdsArray = BankProduct::where('bank_id', $request->bank_id)
            ->pluck('product_id')
            ->toArray();



        $products = Product::whereIn('id', $productIdsArray)->get();
        return $products;
    }
    public function update(Request $request, BankProduct $bankProduct)
    {
        $request->validate([
            'bank_id' => 'required|string|max:255',
            'product_id' => 'required|string|max:255',
            'auto_generate_lan' => 'required|boolean|max:10',
        ]);
         $checkExist = BankProduct::where([ 'product_id' => $request->product_id, 'bank_id' => $request->bank_id])->where('id', '!=', $bankProduct->id)->get();
        if ($checkExist->isNotEmpty()) {
            flash()
                ->error('Bank product name already exist')
                ->flash();
            return true;
        }
        $bankProduct->update($request->all());
        flash()
            ->success('Bank product updated successfully ')
            ->flash();

        return redirect()->route('bank-product.index');
    }
    public function destory(BankProduct $bankProduct)
    {

        $bankProduct->delete();
        flash()
            ->success('Bank product deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); // assuming it's an AJAX request
    }
}
