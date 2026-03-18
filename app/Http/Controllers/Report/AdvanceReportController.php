<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\InteractsWithReportData;
use App\Models\AdvanceAmountLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdvanceReportController extends Controller
{
    use InteractsWithReportData;

    public function index(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = AdvanceAmountLog::query()->with(['advance.user', 'createdBy']);

        $query->whereHas('advance', function ($advanceQuery) use ($visibleUserIds, $request) {
            if (is_array($visibleUserIds)) {
                $advanceQuery->whereIn('user_id', $visibleUserIds);
            }

            if ($request->filled('user_id')) {
                $advanceQuery->where('user_id', $request->user_id);
            }
        });

        if ($request->filled('advance_type')) {
            $query->where('type', $request->advance_type);
        }

        if ($request->filled('company')) {
            $company = strtolower(trim($request->company));
            $query->whereRaw('LOWER(COALESCE(remark, "")) LIKE ?', ['%' . $company . '%']);
        }

        if ($request->filled('min_amount')) {
            $query->whereRaw('CAST(advance_amount AS DECIMAL(15,2)) >= ?', [(float) $request->min_amount]);
        }

        if ($request->filled('max_amount')) {
            $query->whereRaw('CAST(advance_amount AS DECIMAL(15,2)) <= ?', [(float) $request->max_amount]);
        }

        $this->applyDateRangeFilter($query, 'advance_date', $request->from_date, $request->to_date);

        $query->orderBy('advance_date', 'desc')->orderBy('id', 'desc');

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('advance_type', function ($row) {
                    return strtolower((string) $row->type) === 'deduct' ? 'Recovery' : 'Advance';
                })
                ->addColumn('channel_name', function ($row) {
                    $channel = optional($row->advance)->user;
                    return $channel ? trim(($channel->first_name ?? '') . ' ' . ($channel->last_name ?? '')) : '-';
                })
                ->addColumn('advance_paid_to', function ($row) {
                    if (strtolower((string) $row->type) === 'deduct') {
                        return '-';
                    }

                    $channel = optional($row->advance)->user;
                    return $channel ? trim(($channel->first_name ?? '') . ' ' . ($channel->last_name ?? '')) : '-';
                })
                ->addColumn('advance_amt', function ($row) {
                    return strtolower((string) $row->type) === 'add'
                        ? number_format((float) $row->advance_amount, 2)
                        : '-';
                })
                ->addColumn('payment_date', function ($row) {
                    return strtolower((string) $row->type) === 'add'
                        ? ($row->advance_date ?: '-')
                        : '-';
                })
                ->addColumn('company_label', function ($row) {
                    return 'AADRIKA/PARKER/FSS';
                })
                ->addColumn('recovery_date', function ($row) {
                    return strtolower((string) $row->type) === 'deduct'
                        ? ($row->advance_date ?: '-')
                        : '-';
                })
                ->addColumn('recovery_amt', function ($row) {
                    return strtolower((string) $row->type) === 'deduct'
                        ? number_format((float) $row->advance_amount, 2)
                        : '-';
                })
                ->addColumn('net_advance', function ($row) {
                    return number_format($this->getRunningNetAdvance((int) $row->advance_id, $row->advance_date, (int) $row->id), 2);
                })
                ->make(true);
        }

        $users = $this->reportUsersForFilter($visibleUserIds);

        return view('Frontend.Report.advance.index', compact('users'));
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function export(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = AdvanceAmountLog::query()->with(['advance.user']);

        $query->whereHas('advance', function ($advanceQuery) use ($visibleUserIds, $request) {
            if (is_array($visibleUserIds)) {
                $advanceQuery->whereIn('user_id', $visibleUserIds);
            }

            if ($request->filled('user_id')) {
                $advanceQuery->where('user_id', $request->user_id);
            }
        });

        if ($request->filled('advance_type')) {
            $query->where('type', $request->advance_type);
        }

        if ($request->filled('company')) {
            $company = strtolower(trim($request->company));
            $query->whereRaw('LOWER(COALESCE(remark, "")) LIKE ?', ['%' . $company . '%']);
        }

        if ($request->filled('min_amount')) {
            $query->whereRaw('CAST(advance_amount AS DECIMAL(15,2)) >= ?', [(float) $request->min_amount]);
        }

        if ($request->filled('max_amount')) {
            $query->whereRaw('CAST(advance_amount AS DECIMAL(15,2)) <= ?', [(float) $request->max_amount]);
        }

        $this->applyDateRangeFilter($query, 'advance_date', $request->from_date, $request->to_date);

        $records = $query->orderBy('advance_id')->orderBy('advance_date')->orderBy('id')->get();
        $running = [];

        $rows = $records->values()->map(function ($record, $index) use (&$running) {
            $type = strtolower((string) $record->type);
            $amount = (float) $record->advance_amount;
            $advanceId = (int) $record->advance_id;
            $running[$advanceId] = ($running[$advanceId] ?? 0) + ($type === 'deduct' ? -$amount : $amount);
            $channel = optional($record->advance)->user;
            $channelName = $channel ? trim(($channel->first_name ?? '') . ' ' . ($channel->last_name ?? '')) : '-';

            return [
                $index + 1,
                $type === 'deduct' ? 'Recovery' : 'Advance',
                $channelName,
                $type === 'deduct' ? '-' : $channelName,
                $type === 'add' ? number_format($amount, 2) : '-',
                $type === 'add' ? $record->advance_date : '-',
                'AADRIKA/PARKER/FSS',
                $type === 'deduct' ? $record->advance_date : '-',
                $type === 'deduct' ? number_format($amount, 2) : '-',
                number_format((float) $running[$advanceId], 2),
            ];
        });

        return $this->csvDownloadResponse('advance-report.csv', [
            'S.NO',
            'ADVANCE TYPE',
            'CHANNEL NAME',
            'ADVANCE PAID TO',
            'ADVANCE AMT',
            'PAYMENT DATE',
            'COMPANY AADRIKA/PARKER/FSS',
            'RECOVERY DATE',
            'RECOVERY AMT',
            'NET ADVANCE',
        ], $rows);
    }

    private function getRunningNetAdvance(int $advanceId, ?string $advanceDate, int $logId): float
    {
        return (float) AdvanceAmountLog::query()
            ->where('advance_id', $advanceId)
            ->where(function ($query) use ($advanceDate, $logId) {
                $query->where('advance_date', '<', $advanceDate)
                    ->orWhere(function ($sameDateQuery) use ($advanceDate, $logId) {
                        $sameDateQuery->where('advance_date', '=', $advanceDate)
                            ->where('id', '<=', $logId);
                    });
            })
            ->select(DB::raw("COALESCE(SUM(CASE WHEN type = 'deduct' THEN -CAST(advance_amount AS DECIMAL(15,2)) ELSE CAST(advance_amount AS DECIMAL(15,2)) END), 0) as net_amount"))
            ->value('net_amount');
    }
}
