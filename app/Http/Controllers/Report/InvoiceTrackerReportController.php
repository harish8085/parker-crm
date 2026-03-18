<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\InteractsWithReportData;
use App\Models\InvoicePaymentView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InvoiceTrackerReportController extends Controller
{
    use InteractsWithReportData;

    public function index(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = InvoicePaymentView::query()->with('applicationNos');
        $this->applyInvoiceScopeByUsers($query, $visibleUserIds);

        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', '%' . trim($request->bank_name) . '%');
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('mis_month')) {
            $query->where('mis_month', $request->mis_month);
        }

        $this->applyDateRangeFilter($query, 'invoice_date', $request->from_date, $request->to_date);

        $query->latest();

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('application_nos', function ($row) {
                    return $row->applicationNos->pluck('application_no')->implode(', ') ?: '-';
                })
                ->editColumn('payment_amount', function ($row) {
                    return number_format((float) $row->payment_amount, 2);
                })
                ->editColumn('remaining_amount', function ($row) {
                    return number_format((float) $row->remaining_amount, 2);
                })
                ->addColumn('invoice_date_display', function ($row) {
                    return $row->invoice_date ? date('Y-m-d', strtotime($row->invoice_date)) : '-';
                })
                ->addColumn('payment_status_badge', function ($row) {
                    $status = $row->payment_status ?: 'pending';
                    $badge = 'warning';
                    if (strcasecmp($status, 'paid') === 0) {
                        $badge = 'success';
                    } elseif (strcasecmp($status, 'failed') === 0) {
                        $badge = 'danger';
                    }
                    return '<span class="badge bg-' . $badge . '">' . ucfirst($status) . '</span>';
                })
                ->rawColumns(['payment_status_badge'])
                ->make(true);
        }

        return view('Frontend.Report.invoice-tracker.index');
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function export(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = InvoicePaymentView::query()->with('applicationNos');
        $this->applyInvoiceScopeByUsers($query, $visibleUserIds);

        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', '%' . trim($request->bank_name) . '%');
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('mis_month')) {
            $query->where('mis_month', $request->mis_month);
        }

        $this->applyDateRangeFilter($query, 'invoice_date', $request->from_date, $request->to_date);

        $rows = $query->latest()->get()->map(function ($record) {
            return [
                $record->invoice_no,
                $record->mis_month,
                $record->bank_name,
                $record->payment_status,
                $record->payment_amount,
                $record->remaining_amount,
                optional($record->invoice_date)->format('Y-m-d'),
                $record->applicationNos->pluck('application_no')->implode(', '),
            ];
        });

        return $this->csvDownloadResponse('invoice-tracker-report.csv', [
            'Invoice No',
            'MIS Month',
            'Bank Name',
            'Payment Status',
            'Payment Amount',
            'Remaining Amount',
            'Invoice Date',
            'Application Nos',
        ], $rows);
    }

    private function applyInvoiceScopeByUsers($query, ?array $visibleUserIds): void
    {
        if (!is_array($visibleUserIds)) {
            return;
        }

        $query->whereExists(function ($existsQuery) use ($visibleUserIds) {
            $existsQuery->select(DB::raw(1))
                ->from('invoice_application_nos as ian')
                ->join('applications as a', 'a.app_id', '=', 'ian.application_no')
                ->whereColumn('ian.invoice_payment_view_id', 'invoice_payment_view.id')
                ->whereIn('a.user_id', $visibleUserIds);
        });
    }
}
