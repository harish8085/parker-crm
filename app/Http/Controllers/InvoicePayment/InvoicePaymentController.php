<?php

namespace App\Http\Controllers\InvoicePayment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Bank;
use App\Models\BankMIS;
use App\Models\Product;
use App\Models\StaffAssign;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\InvoicePaymentView;

class InvoicePaymentController extends Controller
{
     public function index(Request $request) {
        $Route = 'InvoicePayment';
        $user = Auth::user();
        $channelroleId = 2;
        $salesroleId = 3;
        $bank = Bank::all();
        $product = Product::all();

        $query = InvoicePaymentView::query();

        if ($request->ajax()) {
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
                $query->where('bank_name', $request->bank_name);
            }
    
            if ($request->product_name) {
                // Note: InvoicePaymentView doesn't have product_name, filter can be added if needed
            }
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                })
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name ? $row->bank_name : '-'; 
                })
                ->editColumn('bank_address', function ($row) {
                    return $row->bank_address ? $row->bank_address : '-'; 
                })
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no ? $row->invoice_no : '-'; 
                })
                ->editColumn('invoice_date', function ($row) {
                    return $row->invoice_date ? $row->invoice_date : '-'; 
                })
                ->editColumn('bank_gst_no', function ($row) {
                    return $row->bank_gst_no ? $row->bank_gst_no : '-'; 
                })
                ->editColumn('bank_hsn_code', function ($row) {
                    return $row->bank_hsn_code ? $row->bank_hsn_code : '-'; 
                })
                ->editColumn('dsa_pan', function ($row) {
                    return $row->dsa_pan ? $row->dsa_pan : '-'; 
                })
                ->editColumn('dsa_gst_no', function ($row) {
                    return $row->dsa_gst_no ? $row->dsa_gst_no : '-'; 
                })
                ->editColumn('payment_amount', function ($row) {
                    return $row->payment_amount ? $row->payment_amount : '-'; 
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('invoice_payment', 'view')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/invoice_payment/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    return $btn;
                })
                ->rawColumns(['checkbox','action'])
                ->make(true);
        }

            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        return view('Frontend.InvoicePayment.index',compact('channels','sales','bank','product'));
    }



    public function filter(Request $request){
        $Route = 'BankMIS';
        $user = Auth::user();
        $query = BankMIS::query();


        if ($request->status !== null && $request->status !== 'All') {
            $query->where('status', $request->status);
        }
        if ($request->from_date !== null) {
            $query->whereDate('disbursement_date', '>=', $request->from_date);
        }
        if ($request->to_date !== null) {
            $query->whereDate('disbursement_date', '<=', $request->to_date);
        }
        if ($request->user_id !== null) {
            $query->where('user_id',  $request->user_id);
        }

        $query->orderBy('id', 'desc');
        // Execute the query and fetch results
        $bank = $query->paginate(25);
        return view('Frontend.Bank_MIS.InvoicePayment.index', compact('Route', 'bank'));
    }


    public function show($id){
        $Route = 'Edit Bank MIS';
        $bank_mis = BankMIS::findOrFail($id);
        $bank = Bank::where('id',$bank_mis->bank_id)->get();
        $product = Product::where('id',$bank_mis->product_id)->get();
        return view('Frontend.Invoice.show',compact('bank_mis','bank','product'));
    }

    public function destroy(BankMIS $bank){
        $bank->delete();
        return true;
    }


    
}
