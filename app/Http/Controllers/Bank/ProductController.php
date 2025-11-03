<?php

namespace App\Http\Controllers\Bank;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BankProduct;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        $Route = 'Product';
        $banks = Bank::all();
        $product = Product::all();
        if ($request->ajax()) {

            $query = Product::query();

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
    
            if ($request->product_name) {
                    $query->where('name', $request->product_name);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->name ? $row->name : '-'; 
                })
                ->editColumn('group', function ($row) {
                    return $row->group ? $row->group : '-'; 
                })
                ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('product', 'update')) {
                            $btn .= "<img onclick=\"window.location.href='" . url('/product/update/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                        }
                        if (auth()->user()->hasPermission('product', 'delete')) {
                           
                                $btn .= "<img class='delete-product-btn' data-product-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                            
                            
                        }
                        return $btn;
                    })
                ->rawColumns(['action'])
                ->make(true);
        }


        return view('Frontend.Product.index', compact('Route', 'banks','product'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'required|string|max:255',
        ]);
        $checkExist = Product::where(['name' => $request->name, 'group' => $request->group])->get();
        if ($checkExist->isNotEmpty()) {

            return redirect()->to('/product')->with('error', 'Product  already exist.');
        }
        Product::create($request->all());
        return redirect()->to('/product')->with('success', 'Product created successfully.');
    }

    public function getProduct($product)
    {
        $Route = 'Edit Product';
        $product = Product::findOrFail($product);
        $products = Product::get();
        return view('Frontend.Product.edit', compact('product','products'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'required|string|max:255',
        ]);
        $checkExist = Product::where(['name' => $request->name, 'group' => $request->group])->where('id', '!=', $product->id)->get();
        if ($checkExist->isNotEmpty()) {
            flash()
                ->error('Product name already exist')
                ->flash();
            return true;
        }
        $product->update($request->all());
        flash()
            ->success('Product updated successfully ')
            ->flash();

        return redirect()->route('product.index');
    }
    public function destroy(Product $product)
    {

        $product->delete();
        $bank_product = BankProduct::where('product_id', $product->id)->delete();
        flash()
            ->success('Product deleted successfully ')
            ->flash();

        return response()->json(['success' => true]); // assuming it's an AJAX request
    }
}
