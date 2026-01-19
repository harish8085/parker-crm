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
    public function index(Request $request)
    {
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

        if ($request->payment_status && $request->payment_status != 'All') {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->ajax()) {

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                })
                ->editColumn('invoice_date', function ($row) {
                    return $row->invoice_date ? date('d-M-Y', strtotime($row->invoice_date)) : '-';
                })
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no ? $row->invoice_no : '-';
                })
                ->editColumn('group_name', function ($row) {
                    return $row->group_name ? $row->group_name : '-';
                })
                ->editColumn('mis_month', function ($row) {
                    return $row->mis_month ? date('M-Y', strtotime($row->mis_month)) : '-';
                })
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name ? $row->bank_name : '-';
                })
                ->editColumn('bank_address', function ($row) {
                    return $row->bank_address ? $row->bank_address : '-';
                })
                ->editColumn('bank_hsn_code', function ($row) {
                    return $row->bank_hsn_code ? $row->bank_hsn_code : '-';
                })
                ->editColumn('taxable_value', function ($row) {
                    return $row->taxable_value ? '₹' . number_format($row->taxable_value, 2) : '-';
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
                ->editColumn('TDS', function ($row) {
                    return $row->TDS ? '₹' . number_format($row->TDS, 2) : '-';
                })
                ->editColumn('invoice_value', function ($row) {
                    return $row->invoice_value ? '₹' . number_format($row->invoice_value, 2) : '-';
                })
                ->editColumn('bank_gst_no', function ($row) {
                    return $row->bank_gst_no ? $row->bank_gst_no : '-';
                })
                ->editColumn('dsa_gst_no', function ($row) {
                    return $row->dsa_gst_no ? $row->dsa_gst_no : '-';
                })
                ->editColumn('company_name', function ($row) {
                    return $row->company_name ? $row->company_name : '-';
                })
                ->editColumn('payment_received_bank', function ($row) {
                    return $row->payment_received_bank ? $row->payment_received_bank : '-';
                })
                ->editColumn('payment_amount', function ($row) {
                    return $row->payment_amount ? '₹' . number_format($row->payment_amount, 2) : '-';
                })
                ->editColumn('payment_status', function ($row) {
                    $badge = 'secondary';
                    if ($row->payment_status == 'pending') {
                        $badge_text = 'Pending';
                        $badge = 'warning';
                    } elseif ($row->payment_status == 'paid') {
                        $badge_text = 'Received';
                        $badge = 'success';
                    } elseif ($row->payment_status == 'failed') {
                        $badge_text = 'Failed';
                        $badge = 'danger';
                    }
                    return '<span class="badge bg-' . $badge . '">' . ucfirst($badge_text) . '</span>';
                })
                ->editColumn('remaining_amount', function ($row) {
                    return $row->remaining_amount ? '₹' . number_format($row->remaining_amount, 2) : '0';
                })
                ->editColumn('payment_paid', function ($row) {
                    return $row->payment_paid ? '₹' . number_format($row->payment_paid, 2) : '0';
                })
                ->editColumn('payment_date1', function ($row) {
                    return $row->payment_date1 ? $row->payment_date1 : '-';
                })
                ->editColumn('payment_date2', function ($row) {
                    return $row->payment_date2 ? $row->payment_date2 : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (auth()->user()->hasPermission('invoice_payment', 'view')) {
                        $btn .= "<a href='" . e(url('/invoice_payment/view/' . $row->id)) . "' onclick='viewInvoiceCasesList(" . $row->id . ")' style='cursor: pointer;'>
                                        <img src='" . asset('assets/images/eye-icon.svg') . "' alt='View'>
                                     </a>";
                    }
                    if (auth()->user()->hasPermission('invoice_payment', 'update')) {
                        $btn .= "<button type='button' class='btn btn-sm edit-btn' data-id='" . $row->id . "' data-payment-paid='" . ($row->payment_paid ?? '') . "' data-payment-date1='" . ($row->payment_date1 ?? '') . "' data-payment-date2='" . ($row->payment_date2 ?? '') . "' data-remaining-amount='" . ($row->remaining_amount ?? '0') . "' style='background: none; border: none; cursor: pointer; padding: 0;'>
                                    <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                                </button>";
                    }

                    return $btn;
                })
                ->rawColumns(['checkbox', 'action', 'payment_status'])
                ->make(true);
        }

        $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
            $query->where('id', $channelroleId);
        })->get();

        $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
            $query->where('id', $salesroleId);
        })->get();

        return view('Frontend.InvoicePayment.index', compact('channels', 'sales', 'bank', 'product'));
    }

    public function filter(Request $request)
    {
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

    public function show($id)
    {
        $Route = 'View Invoice Payment';
        $applicationDetails = [];
        $invoicePayment = InvoicePaymentView::findOrFail($id);
        // extrect all application no from application_no colum which are comma separated string search each application no in bank mis table and get the application details i want to show application details in show view.
        $applicationNos = explode(',', $invoicePayment->application_no);
        foreach ($applicationNos as $appNo) {
            $application = Application::where('application_no', trim($appNo))->first();
            if ($application) {
                $bankMIS = BankMIS::where('application_id', $application->id)->first();
                $applicationDetails[] = [
                    'application_no' => $application->application_no,
                    'applicant_name' => $application->applicant_name,
                    'product_name' => $application->product ? $application->product->product_name : '',
                    'bank_mis_no' => $bankMIS ? $bankMIS->bank_mis_no : '',
                    'mis_amount' => $bankMIS ? '₹' . number_format($bankMIS->mis_amount, 2) : '-',
                    'mis_month' => $bankMIS ? date('M-Y', strtotime($bankMIS->mis_month)) : '-',
                    'dis'
                ];
            }
        }
        return view('Frontend.InvoicePayment.view', compact('invoicePayment', 'Route', 'applicationDetails'));
    }


    public function edit($id)
    {
        $Route = 'Edit Invoice Payment';
        $invoicePayment = InvoicePaymentView::findOrFail($id);
        return view('Frontend.InvoicePayment.edit', compact('invoicePayment', 'Route'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'payment_paid' => 'nullable|numeric|min:0',
            'payment_date1' => 'nullable|date',
            'payment_date2' => 'nullable|date',
            'remaining_amount' => 'nullable|numeric|min:0',
        ]);

        try {
            $invoicePayment = InvoicePaymentView::findOrFail($id);

            // add payment paid in to existing paid amount
            if (isset($validated['payment_paid'])) {
                $validated['payment_paid'] = $invoicePayment->payment_paid + $validated['payment_paid'];
            }
            
            // Update payment status based on remaining amount
            if (isset($validated['remaining_amount'])) {
                if ($validated['remaining_amount'] == 0) {
                    $validated['payment_status'] = 'paid';
                } else {
                    $validated['payment_status'] = 'pending';
                }
            }
            
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
