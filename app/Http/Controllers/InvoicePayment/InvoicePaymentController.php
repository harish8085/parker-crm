<?php

namespace App\Http\Controllers\InvoicePayment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Bank;
use App\Models\BankMIS;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\Models\InvoicePaymentView;
use Illuminate\Support\Facades\Log;

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

        $query = InvoicePaymentView::with('applicationNos');

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
                ->editColumn('referance_no1', function ($row) {
                    return $row->referance_no1 ? $row->referance_no1 : '-';
                })
                ->editColumn('referance_no2', function ($row) {
                    return $row->referance_no2 ? $row->referance_no2 : '-';
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
                        $btn .= "<button 
                        type='button' 
                        class='btn btn-sm view-btn' 
                        data-row='" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . "'
                        style='background: none; border: none; cursor: pointer; padding: 0;'>
                                    <img src='" . asset('assets/images/eye-icon.svg') . "' alt='View'>
                                </button>";
                    }

                    if (auth()->user()->hasPermission('invoice_payment', 'update')) {
                        $btn .= "
                        <button 
                            type='button' 
                            class='btn btn-sm edit-btn'
                            data-row='" . htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8') . "'
                            style='background: none; border: none; cursor: pointer; padding: 0;'>
                            
                            <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                        </button>";
                    }

                    if (auth()->user()->hasPermission('invoice_payment', 'delete')) {
                        $btn .= "
                            <button 
                                type='button' 
                                class='btn btn-sm delete-btn' 
                                data-id='{$row->id}'
                                data-url='" . url('invoice_payment/' . $row->id) . "'
                                 onclick='deleteInvoicePayment( $row->id)'
                                style='background: none; border: none; cursor: pointer; padding: 0;'>
                                <img src='" . asset('assets/images/delete-icon.svg') . "' alt='Delete'>
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
        $applicationNos = $invoicePayment->applicationNos->pluck('application_no')->toArray();
        $bankMisRecords = BankMIS::with(['bank', 'product'])
            ->whereIn('app_id', $applicationNos)
            ->get();

        foreach ($bankMisRecords as $record) {
            $applicationDetails[] = [
                'application_no' => $record->app_id,
                'applicant_name' => $record->customer_name ?? '',
                'product_name' => $record->product ? $record->product->name : '',
                'bank_mis_no' => $record->bank_mis_no ?? '',
                'mis_amount' => $record->payout_amount ? '₹' . number_format($record->payout_amount, 2) : '-',
                'mis_month' => $record->bank_mis_month ? date('M-Y', strtotime($record->bank_mis_month)) : '-',
            ];
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
            'payment_paid1' => 'nullable|min:0',
            'payment_paid2' => 'nullable|min:0',
            'payment_date1' => 'nullable|date',
            'payment_date2' => 'nullable|date',
            'remaining_amount' => 'nullable|min:0',
            'referance_no1' => 'nullable|string|max:255',
            'referance_no2' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'dsa_gst_no' => 'nullable|string|max:255',
            'bank_gst_no' => 'nullable|string|max:255',
            'invoice_no' => 'nullable|string|max:255',
            'invoice_date' => 'nullable|date',
            'bank_address' => 'nullable|string|max:500',
            'bank_hsn_code' => 'nullable|string|max:255',
            'payment_received_bank' => 'nullable|string|max:255',

        ]);


        try {
            $invoicePayment = InvoicePaymentView::findOrFail($id);
            $referenceNo1 = isset($validated['referance_no1']) ? trim((string) $validated['referance_no1']) : '';
            $referenceNo2 = isset($validated['referance_no2']) ? trim((string) $validated['referance_no2']) : '';

            if ($referenceNo1 !== '' && $referenceNo2 !== '' && strcasecmp($referenceNo1, $referenceNo2) === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'UTR No 1 and UTR No 2 must be different.'
                ], 422);
            }

            if ($referenceNo1 !== '') {
                $existsRef1 = InvoicePaymentView::where('id', '!=', $id)
                    ->where(function ($query) use ($referenceNo1) {
                        $query->where('referance_no1', $referenceNo1)
                            ->orWhere('referance_no2', $referenceNo1);
                    })
                    ->exists();

                if ($existsRef1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'UTR No 1 / Reference No already exists. Please use a unique value.'
                    ], 422);
                }
            }

            if ($referenceNo2 !== '') {
                $existsRef2 = InvoicePaymentView::where('id', '!=', $id)
                    ->where(function ($query) use ($referenceNo2) {
                        $query->where('referance_no1', $referenceNo2)
                            ->orWhere('referance_no2', $referenceNo2);
                    })
                    ->exists();

                if ($existsRef2) {
                    return response()->json([
                        'success' => false,
                        'message' => 'UTR No 2 / Reference No already exists. Please use a unique value.'
                    ], 422);
                }
            }

            // add payment paid in to existing paid amount
            if (isset($validated['payment_paid'])) {
                $validated['payment_paid'] = $invoicePayment->payment_paid + $validated['payment_paid1'] + $validated['payment_paid2'];
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

    function getInvoiceCases(Request $request)
    {
        $applicationNos = $request->input('application_nos', []);
        $invoicedApplications = [];
        // i want whole row details of each application id which match with the app_id of bank_mis 

        if (!$applicationNos || !is_array($applicationNos)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request.'
            ], 400);
        }

        // Get BankMIS records with their relationships
        $invoicedApplications = BankMIS::with(['bank', 'product'])
            ->whereIn('app_id', $applicationNos)
            ->get();

        if ($invoicedApplications->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No records found.'
            ], 404);
        }

        // Calculate total payout amount
        $totalPayoutAmount = $invoicedApplications->sum('payout_amount');

        // Format data for modal display
        $cases = $invoicedApplications->map(function ($record) {
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

