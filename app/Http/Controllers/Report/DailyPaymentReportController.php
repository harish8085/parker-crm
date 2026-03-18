<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\InteractsWithReportData;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DailyPaymentReportController extends Controller
{
    use InteractsWithReportData;
    private const SETTLEMENT_TYPE_MAP = [
        'commission' => 'COMM',
        'contest' => 'CONTEST',
        'insurance' => 'INSURANCE',
    ];

    public function index(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = Transaction::query()->with(['user', 'settlement', 'bankAllocations.bankAccount']);
        $this->applyUserScope($query, 'user_id', $visibleUserIds);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('settlement_type')) {
            $query->whereHas('settlement', function ($settlementQuery) use ($request) {
                $settlementQuery->where('settlement_type', $request->settlement_type);
            });
        }

        $this->applyDateRangeFilter($query, 'completed_at', $request->from_date, $request->to_date);

        $query->latest();

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('date_display', function ($row) {
                    $date = $row->completed_at ?? $row->approved_at ?? $row->created_at;
                    return $date ? $date->format('d-m-Y') : '-';
                })
                ->addColumn('type_display', function ($row) {
                    $type = strtolower((string) optional($row->settlement)->settlement_type);
                    return self::SETTLEMENT_TYPE_MAP[$type] ?? strtoupper($type ?: '-');
                })
                ->addColumn('status_display', function ($row) {
                    return ucwords((string) ($row->status ?: '-'));
                })
                ->addColumn('referral_name', function ($row) {
                    return $row->user ? trim(($row->user->first_name ?? '') . ' ' . ($row->user->last_name ?? '')) : '-';
                })
                ->addColumn('amount_display', function ($row) {
                    $allocation = $row->bankAllocations->first();
                    $amount = $allocation ? (float) $allocation->amount : (float) $row->net_payable;
                    return number_format($amount, 2);
                })
                ->addColumn('remark_display', function ($row) {
                    if (!empty($row->rejection_reason)) {
                        return $row->rejection_reason;
                    }
                    if ((float) $row->advance_amount > 0) {
                        return 'Advance Comm';
                    }
                    return '-';
                })
                ->addColumn('account_holder_name', function ($row) {
                    $account = optional($row->bankAllocations->first())->bankAccount;
                    return $account->holder_name ?? '-';
                })
                ->addColumn('account_number', function ($row) {
                    $account = optional($row->bankAllocations->first())->bankAccount;
                    return $account->account_number ?? '-';
                })
                ->addColumn('ifsc_code', function ($row) {
                    $account = optional($row->bankAllocations->first())->bankAccount;
                    return $account->ifsc_code ?? '-';
                })
                ->addColumn('pan_number', function ($row) {
                    $account = optional($row->bankAllocations->first())->bankAccount;
                    return $account->pan_number ?? ($row->user->pan_number ?? '-');
                })
                ->addColumn('aadhar_number', function ($row) {
                    $account = optional($row->bankAllocations->first())->bankAccount;
                    return $account->aadhar_number ?? ($row->user->aadhar_number ?? '-');
                })
                ->make(true);
        }

        $users = $this->reportUsersForFilter($visibleUserIds);

        return view('Frontend.Report.daily-payment.index', compact('users'));
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function export(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = Transaction::query()->with(['user', 'settlement', 'bankAllocations.bankAccount']);
        $this->applyUserScope($query, 'user_id', $visibleUserIds);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('settlement_type')) {
            $query->whereHas('settlement', function ($settlementQuery) use ($request) {
                $settlementQuery->where('settlement_type', $request->settlement_type);
            });
        }

        $this->applyDateRangeFilter($query, 'completed_at', $request->from_date, $request->to_date);

        $totalAmount = 0;
        $rows = $query->latest()->get()->values()->map(function ($record, $index) use (&$totalAmount) {
            $allocation = $record->bankAllocations->first();
            $account = $allocation ? $allocation->bankAccount : null;
            $amount = $allocation ? (float) $allocation->amount : (float) $record->net_payable;
            $totalAmount += $amount;
            $date = $record->completed_at ?? $record->approved_at ?? $record->created_at;
            $type = strtolower((string) optional($record->settlement)->settlement_type);

            return [
                $index + 1,
                $date ? $date->format('d-m-Y') : '-',
                self::SETTLEMENT_TYPE_MAP[$type] ?? strtoupper($type ?: '-'),
                ucwords((string) ($record->status ?: '-')),
                $record->user ? trim(($record->user->first_name ?? '') . ' ' . ($record->user->last_name ?? '')) : '-',
                number_format($amount, 2),
                !empty($record->rejection_reason) ? $record->rejection_reason : ((float) $record->advance_amount > 0 ? 'Advance Comm' : '-'),
                $account->holder_name ?? '-',
                $account->account_number ?? '-',
                $account->ifsc_code ?? '-',
                $account->pan_number ?? ($record->user->pan_number ?? '-'),
                $account->aadhar_number ?? ($record->user->aadhar_number ?? '-'),
            ];
        });

        $rows->push([
            '',
            '',
            '',
            'TOTAL',
            '',
            number_format($totalAmount, 2),
            '',
            '',
            '',
            '',
            '',
            '',
        ]);

        return $this->csvDownloadResponse('daily-payment-report.csv', [
            'S.NO',
            'DATE',
            'TYPE',
            'Status',
            'REFERRAL NAME',
            'AMOUNT',
            'REMARK',
            'A/C HOLDER NAME',
            'A/C NUMBER',
            'IFSC CODE',
            'PAN',
            'ADHAR',
        ], $rows);
    }
}
