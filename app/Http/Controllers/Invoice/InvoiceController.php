<?php

namespace App\Http\Controllers\Invoice;
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

class InvoiceController extends Controller
{
     public function index(Request $request){
        $Route = 'Invoice';
        $user = Auth::user();
        $channelroleId = 2;
        $salesroleId = 3;
        $bank = Bank::all();
        $product = Product::all();

        $invoicedAppIds = InvoicePaymentView::get()
                ->map(function($invoice) {
                    // Split the comma-separated application numbers
                    return explode(',', $invoice->application_no);
                })
                ->flatten()
                ->map(function($appId) {
                    return trim($appId);
                })
                ->unique()
                ->toArray();

            $query = BankMIS::with(['bank','product'])->whereNotIn('app_id', $invoicedAppIds);

            // Filter/remove out records that already have invoices
            $query->whereNotIn('app_id', $invoicedAppIds);
            

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
                ->rawColumns(['checkbox','action'])
                ->make(true);
        }

            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        return view('Frontend.Invoice.index',compact('channels','sales','bank','product'));
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
        return view('Frontend.Invoice.Table.invoice_table', compact('Route', 'bank'));
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'invoice_no' => 'required|string',
            'invoice_date' => 'required|date',
            'bank_gst_no' => 'required|string',
            'bank_hsn_code' => 'required|string',
            'bank_address' => 'nullable|string',
            'in_state' => 'required|in:yes,no',
            'taxable_value' => 'required|numeric|min:0',
            'invoice_value' => 'required|numeric|min:0',
            'payment_received_bank' => 'required|string',
            'mis_month' => 'nullable|string',
            'company_name' => 'nullable|string',
            'group_name' => 'nullable|string',
            'dsa_gst_no' => 'nullable|string',
            'cgst' => 'required|numeric|min:0',
            'sgst' => 'required|numeric|min:0',
            'igst' => 'required|numeric|min:0',
            'tds' => 'nullable|numeric|min:0',
            'payment_amount' => 'required|numeric|min:0',
            'remaining_amount' => 'nullable|numeric|min:0',
            'mis_ids' => 'required|array',
            'mis_ids.*' => 'integer',
        ]);

        try {
            $bankMisRecords = BankMIS::with(['bank', 'product'])
                ->whereIn('id', $validated['mis_ids'])
                ->get();

            if ($bankMisRecords->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No records found.'
                ], 404);
            }

            $bankName = $bankMisRecords->first()->bank->name ?? '-';
            $applicationNumbers = $bankMisRecords->pluck('app_id')->implode(',');

            $invoicePayment = InvoicePaymentView::create([
                'bank_name' => $bankName,
                'bank_address' => $validated['bank_address'] ?? '',
                'invoice_no' => $validated['invoice_no'],
                'invoice_date' => $validated['invoice_date'],
                'bank_gst_no' => $validated['bank_gst_no'],
                'bank_hsn_code' => $validated['bank_hsn_code'],
                'dsa_gst_no' => $validated['dsa_gst_no'] ?? '',
                'application_no' => $applicationNumbers,
                'taxable_value' => $validated['taxable_value'],
                'invoice_value' => $validated['invoice_value'],
                'payment_received_bank' => $validated['payment_received_bank'],
                'CGST' => $validated['cgst'],
                'SGST' => $validated['sgst'],
                'IGST' => $validated['igst'],
                'TDS' => $validated['tds'] ?? 0,
                'payment_amount' => $validated['payment_amount'],
                'remaining_amount' => $validated['remaining_amount'] ?? 0,
                'payment_status' => 'pending',
                'mis_month' => $validated['mis_month']?? '',
                'group_name' => $validated['group_name'] ?? '',
                'company_name' => $validated['company_name'] ?? '',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Invoice saved successfully!',
                'data' => $invoicePayment
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generate(Request $request)
    {
        $misIds = $request->mis_ids;

        if (!$misIds || !is_array($misIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request.'
            ], 400);
        }

        // Get BankMIS records with their relationships
        $bankMisRecords = BankMIS::with(['bank', 'product'])
            ->whereIn('id', $misIds)
            ->get();

        if ($bankMisRecords->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No records found.'
            ], 404);
        }

        // Calculate total payout amount
        $totalPayoutAmount = $bankMisRecords->sum('payout_amount');
        
        // Format data for modal display
        $cases = $bankMisRecords->map(function ($record) {
            return [
                'id' => $record->id,
                'app_id' => $record->app_id,
                'bank_name' => $record->bank->name ?? '-',
                'product_name' => $record->product->name ?? '-',
                'month' => $record->bank_mis_month ?? '-',
                'month_year' => $record->bank_mis_month ?? '-',
                'payout_rate' => $record->payout_rate ?? '-',
                'group' => $record->group ?? '-',
                'customer_name' => $record->customer_name ?? '-',
                'payoutAmount' => $record->payout_amount ?? 0,
                'disbAmount' => $record->disbAmount ?? 0,
            ];
        })->toArray();

        return response()->json([
            'success' => true,
            'cases' => $cases,
            'totalPayoutAmount' => $totalPayoutAmount
        ]);
    }
}
