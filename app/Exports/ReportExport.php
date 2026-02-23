<?php

namespace App\Exports;

use App\Models\Application;
use App\Models\Advance;
use App\Models\AdvanceAmountLog;
use App\Models\ChannelUser;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromCollection, WithHeadings
{
    protected $type;
    protected $filters;
    protected $userIds;
    protected $isAdmin;
    protected $counter = 0;

    public function __construct($type, $filters, $userIds, $isAdmin = true)
    {
        $this->type = $type;
        $this->filters = $filters;
        $this->userIds = $userIds;
        $this->isAdmin = $isAdmin;
    }

    public function collection()
    {
        switch ($this->type) {
            case 'tds': return $this->tdsData();
            case 'commission': return $this->commissionData();
            case 'advance': return $this->advanceData();
            case 'application': return $this->applicationData();
            case 'channel-performance': return $this->channelPerformanceData();
            case 'bank-disbursement': return $this->bankDisbursementData();
            case 'product-wise': return $this->productWiseData();
            case 'settlement': return $this->settlementData();
            case 'transaction': return $this->transactionData();
            case 'monthly-summary': return $this->monthlySummaryData();
            default: return collect([]);
        }
    }

    public function headings(): array
    {
        switch ($this->type) {
            case 'tds':
                if ($this->isAdmin) {
                    return ['S No.', 'User Name', 'Application ID', 'Disburse Amount', 'Commission Rate', 'Company Commission', 'Channel Share', 'TDS %', 'TDS Amount', 'Net Amount', 'Company Net', 'Date'];
                }
                return ['S No.', 'User Name', 'Application ID', 'Disburse Amount', 'Gross Amount', 'TDS %', 'TDS Amount', 'Net Amount', 'Date'];

            case 'commission':
                if ($this->isAdmin) {
                    return ['S No.', 'User Name', 'Application ID', 'Bank', 'Product', 'Disburse Amount', 'Commission Rate', 'Commission Amount', 'Sharing Commission', 'Channel Earning', 'Company Net', 'Date'];
                }
                return ['S No.', 'User Name', 'Application ID', 'Bank', 'Product', 'Disburse Amount', 'Commission Rate', 'Commission Amount', 'Date'];

            case 'advance':
                return ['S No.', 'User Name', 'Current Balance', 'Log Date', 'Type', 'Amount', 'Remark'];

            case 'application':
                if ($this->isAdmin) {
                    return ['S No.', 'User Name', 'App ID', 'Customer Name', 'Bank', 'Product', 'Disburse Amount', 'Commission Rate', 'Sharing Commission', 'Status', 'Created Date'];
                }
                return ['S No.', 'User Name', 'App ID', 'Customer Name', 'Bank', 'Product', 'Disburse Amount', 'Commission Rate', 'Status', 'Created Date'];

            case 'channel-performance':
                if ($this->isAdmin) {
                    return ['S No.', 'Channel Name', 'Total Applications', 'Approved', 'Completed', 'Total Disbursement', 'Company Commission', 'Channel Earnings', 'Company Net', 'Total Settlement'];
                }
                return ['S No.', 'Channel Name', 'Total Applications', 'Approved', 'Completed', 'Total Disbursement', 'My Earnings', 'Total Settlement'];

            case 'bank-disbursement':
                if ($this->isAdmin) {
                    return ['S No.', 'Bank Name', 'Application Count', 'Total Disburse Amount', 'Avg Commission Rate', 'Avg Sharing Rate', 'Completed Count'];
                }
                return ['S No.', 'Bank Name', 'Application Count', 'Total Disburse Amount', 'Avg Commission Rate', 'Completed Count'];

            case 'product-wise':
                if ($this->isAdmin) {
                    return ['S No.', 'Product Name', 'Bank Name', 'Application Count', 'Total Disburse Amount', 'Avg Commission Rate', 'Avg Sharing Rate'];
                }
                return ['S No.', 'Product Name', 'Bank Name', 'Application Count', 'Total Disburse Amount', 'Avg Commission Rate'];

            case 'settlement':
                return ['S No.', 'Settlement ID', 'User Name', 'Distributions', 'Gross Amount', 'Amount', 'Status', 'Settlement Date', 'Created Date'];

            case 'transaction':
                return ['S No.', 'Transaction ID', 'User Name', 'Gross Amount', 'TDS Amount', 'Advance Deduction', 'Net Payable', 'Status', 'Date'];

            case 'monthly-summary':
                if ($this->isAdmin) {
                    return ['S No.', 'Month', 'Total Applications', 'Total Disbursement', 'Company Commission', 'Channel Payouts', 'Company Net', 'Total Settlement', 'Total Transactions', 'Net Payable'];
                }
                return ['S No.', 'Month', 'Total Applications', 'Total Disbursement', 'My Commission', 'Total Settlement', 'Total Transactions', 'Net Payable'];

            default:
                return [];
        }
    }

    private function applyDateFilter($query, $column = 'created_at')
    {
        if (!empty($this->filters['date_from'])) {
            $query->whereDate($column, '>=', $this->filters['date_from']);
        }
        if (!empty($this->filters['date_to'])) {
            $query->whereDate($column, '<=', $this->filters['date_to']);
        }
        return $query;
    }

    private function applyUserFilter($query, $column = 'user_id')
    {
        if ($this->userIds !== null) {
            $query->whereIn($column, $this->userIds);
        }
        return $query;
    }

    private function tdsData()
    {
        $query = SettlementDistribution::query()
            ->join('users', 'settlement_distributions.user_id', '=', 'users.id')
            ->leftJoin('applications', 'settlement_distributions.application_id', '=', 'applications.id')
            ->select('settlement_distributions.*', 'users.first_name', 'users.last_name', 'applications.app_id', 'applications.disburse_amount', 'applications.commission_rate', 'applications.sharing_commission');

        $this->applyUserFilter($query, 'settlement_distributions.user_id');
        $this->applyDateFilter($query, 'settlement_distributions.created_at');

        if (!empty($this->filters['user_id'])) {
            $query->whereIn('settlement_distributions.user_id', (array) $this->filters['user_id']);
        }

        $isAdmin = $this->isAdmin;
        return $query->orderBy('settlement_distributions.created_at', 'desc')->get()->map(function ($row) use ($isAdmin) {
            $this->counter++;
            $compComm = ($row->disburse_amount * $row->commission_rate) / 100;

            if ($isAdmin) {
                return [
                    $this->counter,
                    $row->first_name . ' ' . $row->last_name,
                    $row->app_id ?? '-',
                    $row->disburse_amount ?? '-',
                    ($row->commission_rate ?? '-') . '%',
                    round($compComm, 2),
                    $row->gross_amount,
                    ($row->tds_percentage ?? '-') . '%',
                    $row->tds,
                    $row->amount,
                    round($compComm - $row->gross_amount, 2),
                    Carbon::parse($row->created_at)->format('d-m-Y'),
                ];
            }

            return [
                $this->counter,
                $row->first_name . ' ' . $row->last_name,
                $row->app_id ?? '-',
                $row->disburse_amount ?? '-',
                $row->gross_amount,
                ($row->tds_percentage ?? '-') . '%',
                $row->tds,
                $row->amount,
                Carbon::parse($row->created_at)->format('d-m-Y'),
            ];
        });
    }

    private function commissionData()
    {
        $query = Application::query()
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('banks', 'applications.bank_id', '=', 'banks.id')
            ->leftJoin('products', 'applications.product_id', '=', 'products.id')
            ->select('applications.*', 'users.first_name', 'users.last_name', 'banks.name as bank_name', 'products.name as product_name');

        $this->applyUserFilter($query, 'applications.user_id');
        $this->applyDateFilter($query, 'applications.created_at');

        if (!empty($this->filters['user_id'])) $query->whereIn('applications.user_id', (array) $this->filters['user_id']);
        if (!empty($this->filters['bank_id'])) $query->where('applications.bank_id', $this->filters['bank_id']);
        if (!empty($this->filters['product_id'])) $query->where('applications.product_id', $this->filters['product_id']);

        $isAdmin = $this->isAdmin;
        return $query->orderBy('applications.created_at', 'desc')->get()->map(function ($row) use ($isAdmin) {
            $this->counter++;
            $commAmount = ($row->disburse_amount * $row->commission_rate) / 100;
            $sharingAmount = ($row->disburse_amount * ($row->sharing_commission ?? 0)) / 100;

            if ($isAdmin) {
                return [
                    $this->counter,
                    $row->first_name . ' ' . $row->last_name,
                    $row->app_id ?? '-',
                    $row->bank_name ?? '-',
                    $row->product_name ?? '-',
                    $row->disburse_amount,
                    $row->commission_rate . '%',
                    round($commAmount, 2),
                    ($row->sharing_commission ?? 0) . '%',
                    round($sharingAmount, 2),
                    round($commAmount - $sharingAmount, 2),
                    Carbon::parse($row->created_at)->format('d-m-Y'),
                ];
            }

            return [
                $this->counter,
                $row->first_name . ' ' . $row->last_name,
                $row->app_id ?? '-',
                $row->bank_name ?? '-',
                $row->product_name ?? '-',
                $row->disburse_amount,
                ($row->sharing_commission ?? 0) . '%',
                round($sharingAmount, 2),
                Carbon::parse($row->created_at)->format('d-m-Y'),
            ];
        });
    }

    private function advanceData()
    {
        $query = AdvanceAmountLog::query()
            ->join('advances', 'advance_amount_logs.advance_id', '=', 'advances.id')
            ->join('users', 'advances.user_id', '=', 'users.id')
            ->select('advance_amount_logs.*', 'advances.user_id', 'advances.advance_amount as current_balance', 'users.first_name', 'users.last_name');

        $this->applyUserFilter($query, 'advances.user_id');
        $this->applyDateFilter($query, 'advance_amount_logs.created_at');

        if (!empty($this->filters['user_id'])) $query->whereIn('advances.user_id', (array) $this->filters['user_id']);

        return $query->orderBy('advance_amount_logs.created_at', 'desc')->get()->map(function ($row) {
            $this->counter++;
            return [
                $this->counter,
                $row->first_name . ' ' . $row->last_name,
                $row->current_balance,
                Carbon::parse($row->created_at)->format('d-m-Y'),
                ucfirst($row->type),
                $row->advance_amount,
                $row->remark ?? '-',
            ];
        });
    }

    private function applicationData()
    {
        $query = Application::query()
            ->join('users', 'applications.user_id', '=', 'users.id')
            ->leftJoin('banks', 'applications.bank_id', '=', 'banks.id')
            ->leftJoin('products', 'applications.product_id', '=', 'products.id')
            ->select('applications.*', 'users.first_name', 'users.last_name', 'banks.name as bank_name', 'products.name as product_name');

        $this->applyUserFilter($query, 'applications.user_id');
        $this->applyDateFilter($query, 'applications.created_at');

        if (!empty($this->filters['user_id'])) $query->whereIn('applications.user_id', (array) $this->filters['user_id']);
        if (!empty($this->filters['bank_id'])) $query->where('applications.bank_id', $this->filters['bank_id']);
        if (!empty($this->filters['product_id'])) $query->where('applications.product_id', $this->filters['product_id']);
        if (!empty($this->filters['status'])) $query->where('applications.status', $this->filters['status']);

        $isAdmin = $this->isAdmin;
        return $query->orderBy('applications.created_at', 'desc')->get()->map(function ($row) use ($isAdmin) {
            $this->counter++;
            $base = [
                $this->counter,
                $row->first_name . ' ' . $row->last_name,
                $row->app_id ?? '-',
                $row->customer_name ?? '-',
                $row->bank_name ?? '-',
                $row->product_name ?? '-',
                $row->disburse_amount,
            ];

            if ($isAdmin) {
                $base[] = $row->commission_rate . '%';
                $base[] = ($row->sharing_commission ?? 0) . '%';
            } else {
                $base[] = ($row->sharing_commission ?? 0) . '%';
            }

            $base[] = ucfirst($row->status);
            $base[] = Carbon::parse($row->created_at)->format('d-m-Y');
            return $base;
        });
    }

    private function channelPerformanceData()
    {
        $query = User::query()
            ->whereHas('roles', function ($q) { $q->whereIn('roles.id', [2]); })
            ->withCount(['applications as total_applications'])
            ->withCount(['applications as approved_applications' => function ($q) { $q->where('status', 'approved'); }])
            ->withCount(['applications as completed_applications' => function ($q) { $q->where('status', 'completed'); }])
            ->withSum('applications as total_disburse', 'disburse_amount');

        if ($this->userIds !== null) {
            $query->whereIn('id', $this->userIds);
        }

        $data = $query->orderBy('total_applications', 'desc')->get();

        foreach ($data as $channel) {
            $channelUserIds = [$channel->id];
            $associateIds = ChannelUser::where('channel_id', $channel->id)->pluck('associate_channel_id')->toArray();
            $allIds = array_merge($channelUserIds, $associateIds);

            $channel->total_settlement = Settlement::whereIn('user_id', $allIds)->where('status', 'completed')->sum('gross_amount');
            $channel->total_commission = Application::whereIn('user_id', $allIds)->sum(DB::raw('(disburse_amount * commission_rate) / 100'));
            $channel->channel_earnings = Application::whereIn('user_id', $allIds)->sum(DB::raw('(disburse_amount * IFNULL(sharing_commission, 0)) / 100'));
        }

        $this->counter = 0;
        $isAdmin = $this->isAdmin;
        return $data->map(function ($row) use ($isAdmin) {
            $this->counter++;

            if ($isAdmin) {
                return [
                    $this->counter,
                    $row->first_name . ' ' . $row->last_name,
                    $row->total_applications,
                    $row->approved_applications,
                    $row->completed_applications,
                    $row->total_disburse ?? 0,
                    round($row->total_commission ?? 0, 2),
                    round($row->channel_earnings ?? 0, 2),
                    round(($row->total_commission ?? 0) - ($row->channel_earnings ?? 0), 2),
                    $row->total_settlement ?? 0,
                ];
            }

            return [
                $this->counter,
                $row->first_name . ' ' . $row->last_name,
                $row->total_applications,
                $row->approved_applications,
                $row->completed_applications,
                $row->total_disburse ?? 0,
                round($row->channel_earnings ?? 0, 2),
                $row->total_settlement ?? 0,
            ];
        });
    }

    private function bankDisbursementData()
    {
        $selectColumns = [
            'banks.name as bank_name',
            DB::raw('COUNT(*) as app_count'),
            DB::raw('SUM(applications.disburse_amount) as total_disburse'),
            DB::raw('AVG(applications.commission_rate) as avg_commission_rate'),
            DB::raw('AVG(IFNULL(applications.sharing_commission, 0)) as avg_sharing_rate'),
            DB::raw("SUM(CASE WHEN applications.status = 'completed' THEN 1 ELSE 0 END) as completed_count"),
        ];

        $query = Application::query()
            ->join('banks', 'applications.bank_id', '=', 'banks.id')
            ->select($selectColumns)
            ->groupBy('applications.bank_id', 'banks.name');

        if ($this->userIds !== null) $query->whereIn('applications.user_id', $this->userIds);
        $this->applyDateFilter($query, 'applications.created_at');
        if (!empty($this->filters['bank_id'])) $query->where('applications.bank_id', $this->filters['bank_id']);

        $isAdmin = $this->isAdmin;
        return $query->orderBy('total_disburse', 'desc')->get()->map(function ($row) use ($isAdmin) {
            $this->counter++;
            $base = [
                $this->counter,
                $row->bank_name,
                $row->app_count,
                $row->total_disburse,
            ];

            if ($isAdmin) {
                $base[] = round($row->avg_commission_rate, 2) . '%';
                $base[] = round($row->avg_sharing_rate, 2) . '%';
            } else {
                $base[] = round($row->avg_sharing_rate, 2) . '%';
            }

            $base[] = $row->completed_count;
            return $base;
        });
    }

    private function productWiseData()
    {
        $query = Application::query()
            ->join('products', 'applications.product_id', '=', 'products.id')
            ->leftJoin('banks', 'applications.bank_id', '=', 'banks.id')
            ->select(
                'products.name as product_name',
                'banks.name as bank_name',
                DB::raw('COUNT(*) as app_count'),
                DB::raw('SUM(applications.disburse_amount) as total_disburse'),
                DB::raw('AVG(applications.commission_rate) as avg_commission_rate'),
                DB::raw('AVG(IFNULL(applications.sharing_commission, 0)) as avg_sharing_rate')
            )
            ->groupBy('applications.product_id', 'products.name', 'applications.bank_id', 'banks.name');

        if ($this->userIds !== null) $query->whereIn('applications.user_id', $this->userIds);
        $this->applyDateFilter($query, 'applications.created_at');
        if (!empty($this->filters['product_id'])) $query->where('applications.product_id', $this->filters['product_id']);

        $isAdmin = $this->isAdmin;
        return $query->orderBy('total_disburse', 'desc')->get()->map(function ($row) use ($isAdmin) {
            $this->counter++;
            $base = [
                $this->counter,
                $row->product_name,
                $row->bank_name ?? '-',
                $row->app_count,
                $row->total_disburse,
            ];

            if ($isAdmin) {
                $base[] = round($row->avg_commission_rate, 2) . '%';
                $base[] = round($row->avg_sharing_rate, 2) . '%';
            } else {
                $base[] = round($row->avg_sharing_rate, 2) . '%';
            }

            return $base;
        });
    }

    private function settlementData()
    {
        $query = Settlement::query()
            ->join('users', 'settlements.user_id', '=', 'users.id')
            ->select('settlements.*', 'users.first_name', 'users.last_name')
            ->withCount('distributions');

        $this->applyUserFilter($query, 'settlements.user_id');
        $this->applyDateFilter($query, 'settlements.created_at');
        if (!empty($this->filters['user_id'])) $query->whereIn('settlements.user_id', (array) $this->filters['user_id']);
        if (!empty($this->filters['status'])) $query->where('settlements.status', $this->filters['status']);

        return $query->orderBy('settlements.created_at', 'desc')->get()->map(function ($row) {
            $this->counter++;
            return [
                $this->counter,
                '#' . $row->id,
                $row->first_name . ' ' . $row->last_name,
                $row->distributions_count,
                $row->gross_amount,
                $row->amount,
                ucfirst($row->status),
                $row->settlement_date ? Carbon::parse($row->settlement_date)->format('d-m-Y') : '-',
                Carbon::parse($row->created_at)->format('d-m-Y'),
            ];
        });
    }

    private function transactionData()
    {
        $query = Transaction::query()
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->select('transactions.*', 'users.first_name', 'users.last_name');

        $this->applyUserFilter($query, 'transactions.user_id');
        $this->applyDateFilter($query, 'transactions.created_at');
        if (!empty($this->filters['user_id'])) $query->whereIn('transactions.user_id', (array) $this->filters['user_id']);
        if (!empty($this->filters['status'])) $query->where('transactions.status', $this->filters['status']);

        return $query->orderBy('transactions.created_at', 'desc')->get()->map(function ($row) {
            $this->counter++;
            return [
                $this->counter,
                $row->transaction_id,
                $row->first_name . ' ' . $row->last_name,
                $row->gross_amount,
                $row->tds_amount,
                $row->advance_amount,
                $row->net_payable,
                ucfirst($row->status),
                Carbon::parse($row->created_at)->format('d-m-Y'),
            ];
        });
    }

    private function monthlySummaryData()
    {
        $year = $this->filters['year'] ?? now()->year;
        $rows = collect();
        $isAdmin = $this->isAdmin;

        for ($m = 1; $m <= 12; $m++) {
            $appQuery = Application::whereYear('created_at', $year)->whereMonth('created_at', $m);
            if ($this->userIds !== null) $appQuery->whereIn('user_id', $this->userIds);
            $appCount = $appQuery->count();
            $totalDisburse = (clone $appQuery)->sum('disburse_amount');

            $commQuery = Application::whereYear('created_at', $year)->whereMonth('created_at', $m);
            if ($this->userIds !== null) $commQuery->whereIn('user_id', $this->userIds);
            $totalCommission = $commQuery->sum(DB::raw('(disburse_amount * commission_rate) / 100'));

            $channelPayouts = Application::whereYear('created_at', $year)->whereMonth('created_at', $m);
            if ($this->userIds !== null) $channelPayouts->whereIn('user_id', $this->userIds);
            $channelPayouts = $channelPayouts->sum(DB::raw('(disburse_amount * IFNULL(sharing_commission, 0)) / 100'));

            $settQuery = Settlement::whereYear('created_at', $year)->whereMonth('created_at', $m)->where('status', 'completed');
            if ($this->userIds !== null) $settQuery->whereIn('user_id', $this->userIds);
            $totalSettlement = $settQuery->sum('gross_amount');

            $txnQuery = Transaction::whereYear('created_at', $year)->whereMonth('created_at', $m);
            if ($this->userIds !== null) $txnQuery->whereIn('user_id', $this->userIds);
            $txnCount = $txnQuery->count();
            $netPayable = (clone $txnQuery)->sum('net_payable');

            $this->counter++;

            if ($isAdmin) {
                $rows->push([
                    $this->counter,
                    Carbon::create($year, $m, 1)->format('F'),
                    $appCount,
                    $totalDisburse,
                    round($totalCommission, 2),
                    round($channelPayouts, 2),
                    round($totalCommission - $channelPayouts, 2),
                    $totalSettlement,
                    $txnCount,
                    $netPayable,
                ]);
            } else {
                $rows->push([
                    $this->counter,
                    Carbon::create($year, $m, 1)->format('F'),
                    $appCount,
                    $totalDisburse,
                    round($channelPayouts, 2),
                    $totalSettlement,
                    $txnCount,
                    $netPayable,
                ]);
            }
        }

        return $rows;
    }
}
