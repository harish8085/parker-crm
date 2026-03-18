<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\InteractsWithReportData;
use App\Models\ChannelUser;
use App\Models\SettlementDistribution;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CreditorsReportController extends Controller
{
    use InteractsWithReportData;
    private array $parentNameCache = [];

    public function index(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = SettlementDistribution::query()
            ->with(['user', 'application.parentChannel', 'settlement']);

        $this->applyUserScope($query, 'user_id', $visibleUserIds);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('settlement_type')) {
            $query->where('settlement_type', $request->settlement_type);
        }

        $this->applyDateRangeFilter($query, 'created_at', $request->from_date, $request->to_date);

        $query->latest();

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('parent_name', function ($row) {
                    return $this->resolveParentName($row);
                })
                ->addColumn('channel_name', function ($row) {
                    return $row->user ? trim(($row->user->first_name ?? '') . ' ' . ($row->user->last_name ?? '')) : '-';
                })
                ->addColumn('payable_amt', function ($row) {
                    return number_format((float) $row->gross_amount, 2);
                })
                ->addColumn('paid_amt', function ($row) {
                    $paidAmount = strcasecmp((string) $row->payment_status, 'Success') === 0 ? (float) $row->amount : 0;
                    return number_format($paidAmount, 2);
                })
                ->addColumn('remaining_amt', function ($row) {
                    $grossAmount = (float) $row->gross_amount;
                    $paidAmount = strcasecmp((string) $row->payment_status, 'Success') === 0 ? (float) $row->amount : 0;
                    $remaining = $grossAmount - $paidAmount;
                    return number_format($remaining, 2);
                })
                ->addColumn('tds_amt', function ($row) {
                    return number_format((float) $row->tds, 2);
                })
                ->addColumn('net_remaining_amt', function ($row) {
                    $grossAmount = (float) $row->gross_amount;
                    $paidAmount = strcasecmp((string) $row->payment_status, 'Success') === 0 ? (float) $row->amount : 0;
                    $remaining = $grossAmount - $paidAmount;
                    $netRemaining = max(0, $remaining - (float) $row->tds);
                    return number_format($netRemaining, 2);
                })
                ->make(true);
        }

        $users = $this->reportUsersForFilter($visibleUserIds);

        return view('Frontend.Report.creditors.index', compact('users'));
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function export(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = SettlementDistribution::query()
            ->with(['user', 'application.parentChannel', 'settlement']);

        $this->applyUserScope($query, 'user_id', $visibleUserIds);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('settlement_type')) {
            $query->where('settlement_type', $request->settlement_type);
        }

        $this->applyDateRangeFilter($query, 'created_at', $request->from_date, $request->to_date);

        $rows = $query->latest()->get()->values()->map(function ($record, $index) {
            $grossAmount = (float) $record->gross_amount;
            $paidAmount = strcasecmp((string) $record->payment_status, 'Success') === 0 ? (float) $record->amount : 0;
            $remaining = $grossAmount - $paidAmount;
            $netRemaining = max(0, $remaining - (float) $record->tds);

            return [
                $index + 1,
                $this->resolveParentName($record),
                $record->user ? trim(($record->user->first_name ?? '') . ' ' . ($record->user->last_name ?? '')) : '-',
                number_format($grossAmount, 2),
                number_format($paidAmount, 2),
                number_format($remaining, 2),
                number_format((float) $record->tds, 2),
                number_format($netRemaining, 2),
            ];
        });

        return $this->csvDownloadResponse('creditors-report.csv', [
            'S.NO',
            'PARENT NAME',
            'CHANNEL NAME',
            'PAYABLE AMT',
            'PAID AMT',
            'REMAINING AMT',
            'TDS',
            'NET REMAINING AMT',
        ], $rows);
    }

    private function resolveParentName(SettlementDistribution $distribution): string
    {
        if ($distribution->application && $distribution->application->parentChannel) {
            return trim(($distribution->application->parentChannel->first_name ?? '') . ' ' . ($distribution->application->parentChannel->last_name ?? '')) ?: '-';
        }

        $userId = (int) $distribution->user_id;
        if (!$userId) {
            return '-';
        }

        if (array_key_exists($userId, $this->parentNameCache)) {
            return $this->parentNameCache[$userId];
        }

        $channelUser = ChannelUser::with('channel')->where('associate_channel_id', $userId)->first();
        if ($channelUser && $channelUser->channel) {
            $name = trim(($channelUser->channel->first_name ?? '') . ' ' . ($channelUser->channel->last_name ?? ''));
            $this->parentNameCache[$userId] = $name ?: '-';
            return $this->parentNameCache[$userId];
        }

        $this->parentNameCache[$userId] = '-';
        return '-';
    }
}
