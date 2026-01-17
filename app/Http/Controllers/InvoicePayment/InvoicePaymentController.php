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

        if ($request->ajax()) {
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
                ->editColumn('application_no', function ($row) {
                    return $row->application_no ? $row->application_no : '-'; 
                })
                ->editColumn('taxable_value', function ($row) {
                    return $row->taxable_value ? '₹' . number_format($row->taxable_value, 2) : '-'; 
                })
                ->editColumn('invoive_value', function ($row) {
                    return $row->invoive_value ? '₹' . number_format($row->invoive_value, 2) : '-'; 
                })
                ->editColumn('CGST', function ($row) {
                    return $row->CGST ? '₹' . number_format($row->CGST, 2) : '-'; 
                })
                ->editColumn('SGST', function ($row) {
                    return $row->SGST ? '₹' . number_format($row->SGST, 2) : '-'; 
                })
                ->editColumn('IGST', function ($row) {
                    return $row->IGST ? '₹' . number_format($row->IGST, 2) : '-'; 
                })
                
                ->editColumn('payment_amount', function ($row) {
                    return $row->payment_amount ? '₹' . number_format($row->payment_amount, 2) : '-'; 
                })
                ->editColumn('payment_status', function ($row) {
                    $badge = 'secondary';
                    if ($row->payment_status == 'pending') {
                        $badge = 'warning';
                    } elseif ($row->payment_status == 'paid') {
                        $badge = 'success';
                    } elseif ($row->payment_status == 'failed') {
                        $badge = 'danger';
                    }
                    return '<span class="badge bg-' . $badge . '">' . ucfirst($row->payment_status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('invoice_payment', 'view')) {
                        $btn .= "<a href='" . url('/invoice_payment/view/' . $row->id) . "' class='btn btn-sm btn-info' title='View'>";
                        $btn .= "<img src='" . asset('assets/images/eye-icon.svg') . "' alt='View'></a>";
                    }

                    if (auth()->user()->hasPermission('invoice_payment', 'edit')) {
                        $btn .= " <a href='" . url('/invoice_payment/edit/' . $row->id) . "' class='btn btn-sm btn-primary' title='Edit'>";
                        $btn .= "<img src='" . asset('assets/images/edit-icon.svg') . "' alt='Edit'></a>";
                    }

                    return $btn;
                })
                ->rawColumns(['checkbox','action','payment_status'])
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
        $Route = 'InvoicePayment';
        $query = InvoicePaymentView::query();

        if ($request->status !== null && $request->status !== 'All') {
            $query->where('payment_status', $request->status);
        }
        if ($request->from_date !== null) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }
        if ($request->to_date !== null) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }
        if ($request->bank_name !== null) {
            $query->where('bank_name', $request->bank_name);
        }

        $query->orderBy('id', 'desc');
        $invoicePayments = $query->paginate(25);
        return view('Frontend.InvoicePayment.Table.invoice_payment_table', compact('Route', 'invoicePayments'));
    }

    public function show($id){
        $Route = 'View Invoice Payment';
        $invoicePayment = InvoicePaymentView::findOrFail($id);
        return view('Frontend.InvoicePayment.show', compact('invoicePayment', 'Route'));
    }

    public function edit($id){
        $Route = 'Edit Invoice Payment';
        $invoicePayment = InvoicePaymentView::findOrFail($id);
        return view('Frontend.InvoicePayment.edit', compact('invoicePayment', 'Route'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_status' => 'required|in:pending,paid,failed',
            'payment_recevied_bank' => 'nullable|string',
            'remaining_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $invoicePayment = InvoicePaymentView::findOrFail($id);
            $invoicePayment->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Invoice payment updated successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating invoice payment: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $invoicePayment = InvoicePaymentView::findOrFail($id);
            $invoicePayment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice payment deleted successfully!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting invoice payment: ' . $e->getMessage()
            ], 500);
        }
    }
}
