<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Advance;
use App\Models\AdvanceAmountLog;
use App\Models\Bank;
use App\Models\ChannelUser;
use App\Models\Product;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\Transaction;
use App\Models\User;
use App\Exports\ReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $roleId = Auth::user()->roles[0]->id;
            if (in_array($roleId, [35, 37])) {
                abort(403, 'Unauthorized access to reports.');
            }
            return $next($request);
        });
    }

    /**
     * Check if the current user is Admin or Checker (full visibility).
     */
    private function isAdmin()
    {
        return in_array(Auth::user()->roles[0]->id, [1, 36]);
    }

    /**
     * Get filtered user IDs based on role.
     * Admin/Checker see all, Channel sees own + associates.
     */
    private function getFilteredUserIds()
    {
        $user = Auth::user();
        $roleId = $user->roles[0]->id;

        if (in_array($roleId, [1, 36])) {
            return null;
        }

        $userIds = [$user->id];
        $associateIds = ChannelUser::where('channel_id', $user->id)
            ->pluck('associate_channel_id')
            ->toArray();
        return array_merge($userIds, $associateIds);
    }

    /**
     * Apply date range filter to a query.
     */
    private function applyDateFilter($query, Request $request, $column = 'created_at')
    {
        if ($request->filled('date_from')) {
            $query->whereDate($column, '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate($column, '<=', $request->date_to);
        }
        return $query;
    }

    /**
     * Apply user ID filter to a query.
     */
    private function applyUserFilter($query, $userIds, $column = 'user_id')
    {
        if ($userIds !== null) {
            $query->whereIn($column, $userIds);
        }
        return $query;
    }

    /**
     * Reports landing page.
     */
    public function index()
    {
        $reports = [
            ['title' => 'TDS Report', 'description' => 'TDS deductions on settlements and transactions', 'route' => 'reports.tds', 'icon' => 'fas fa-percentage', 'color' => '#00B3FF'],
            ['title' => 'Commission Report', 'description' => 'Commission details across applications', 'route' => 'reports.commission', 'icon' => 'fas fa-coins', 'color' => '#2DD683'],
            ['title' => 'Advance Report', 'description' => 'Advance payments and deductions', 'route' => 'reports.advance', 'icon' => 'fas fa-hand-holding-usd', 'color' => '#FED142'],
            ['title' => 'Application Report', 'description' => 'Complete application data with all details', 'route' => 'reports.application', 'icon' => 'fas fa-file-alt', 'color' => '#FA8B3A'],
            ['title' => 'Channel Performance', 'description' => 'Per-channel aggregated performance stats', 'route' => 'reports.channel-performance', 'icon' => 'fas fa-chart-line', 'color' => '#6C63FF'],
            ['title' => 'Bank Disbursement', 'description' => 'Bank-wise disbursement summary', 'route' => 'reports.bank-disbursement', 'icon' => 'fas fa-university', 'color' => '#3366CC'],
            ['title' => 'Product-wise Report', 'description' => 'Product-wise application breakdown', 'route' => 'reports.product-wise', 'icon' => 'fas fa-box-open', 'color' => '#E91E63'],
            ['title' => 'Settlement Report', 'description' => 'Settlement records and distribution details', 'route' => 'reports.settlement', 'icon' => 'fas fa-receipt', 'color' => '#009688'],
            ['title' => 'Transaction Report', 'description' => 'Transaction records with payment details', 'route' => 'reports.transaction', 'icon' => 'fas fa-exchange-alt', 'color' => '#FF5722'],
            ['title' => 'Monthly Summary', 'description' => 'Monthly aggregated business summary', 'route' => 'reports.monthly-summary', 'icon' => 'fas fa-calendar-alt', 'color' => '#795548'],
        ];

        return view('Frontend.Reports.index', compact('reports'));
    }

    /**
     * TDS Report
     */
    public function tdsReport(Request $request)
    {
        $isAdmin = $this->isAdmin();

        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $query = SettlementDistribution::query()
                ->join('users', 'settlement_distributions.user_id', '=', 'users.id')
                ->leftJoin('applications', 'settlement_distributions.application_id', '=', 'applications.id')
                ->select(
                    'settlement_distributions.*',
                    'users.first_name',
                    'users.last_name',
                    'applications.app_id',
                    'applications.disburse_amount',
                    'applications.commission_rate',
                    'applications.sharing_commission'
                );

            $this->applyUserFilter($query, $userIds, 'settlement_distributions.user_id');
            $this->applyDateFilter($query, $request, 'settlement_distributions.created_at');

            if ($request->filled('user_id')) {
                $query->whereIn('settlement_distributions.user_id', (array) $request->user_id);
            }

            $query->orderBy('settlement_distributions.created_at', 'desc');

            $totals = [
                'gross_amount' => (clone $query)->sum('settlement_distributions.gross_amount'),
                'tds' => (clone $query)->sum('settlement_distributions.tds'),
                'net_amount' => (clone $query)->sum('settlement_distributions.amount'),
            ];

            if ($isAdmin) {
                $totalsData = (clone $query)->get();
                $totals['company_commission'] = $totalsData->sum(function ($row) {
                    return ($row->disburse_amount * $row->commission_rate) / 100;
                });
                $totals['company_net'] = $totals['company_commission'] - $totals['gross_amount'];
            }

            $dt = DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->editColumn('app_id', function ($row) {
                    return $row->app_id ?? '-';
                })
                ->editColumn('disburse_amount', function ($row) {
                    return isset($row->disburse_amount) ? indianNumberFormat($row->disburse_amount) : '-';
                })
                ->editColumn('gross_amount', function ($row) {
                    return indianNumberFormat($row->gross_amount);
                })
                ->addColumn('tds_percentage_display', function ($row) {
                    return ($row->tds_percentage ?? '-') . '%';
                })
                ->editColumn('tds', function ($row) {
                    return indianNumberFormat($row->tds);
                })
                ->editColumn('amount', function ($row) {
                    return indianNumberFormat($row->amount);
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                });

            if ($isAdmin) {
                $dt->addColumn('commission_rate_display', function ($row) {
                    return ($row->commission_rate ?? '-') . '%';
                })
                ->addColumn('company_commission', function ($row) {
                    $amt = ($row->disburse_amount * $row->commission_rate) / 100;
                    return indianNumberFormat($amt);
                })
                ->addColumn('company_net', function ($row) {
                    $compComm = ($row->disburse_amount * $row->commission_rate) / 100;
                    $channelShare = $row->gross_amount;
                    return indianNumberFormat($compComm - $channelShare);
                });
            }

            return $dt->with('totals', $totals)->make(true);
        }

        return view('Frontend.Reports.tds', compact('isAdmin'));
    }

    /**
     * Commission Report
     */
    public function commissionReport(Request $request)
    {
        $banks = Bank::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $isAdmin = $this->isAdmin();

        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $query = Application::query()
                ->join('users', 'applications.user_id', '=', 'users.id')
                ->leftJoin('banks', 'applications.bank_id', '=', 'banks.id')
                ->leftJoin('products', 'applications.product_id', '=', 'products.id')
                ->select(
                    'applications.*',
                    'users.first_name',
                    'users.last_name',
                    'banks.name as bank_name',
                    'products.name as product_name'
                );

            $this->applyUserFilter($query, $userIds, 'applications.user_id');
            $this->applyDateFilter($query, $request, 'applications.created_at');

            if ($request->filled('user_id')) {
                $query->whereIn('applications.user_id', (array) $request->user_id);
            }
            if ($request->filled('bank_id')) {
                $query->where('applications.bank_id', $request->bank_id);
            }
            if ($request->filled('product_id')) {
                $query->where('applications.product_id', $request->product_id);
            }

            $query->orderBy('applications.created_at', 'desc');

            $totalsQuery = clone $query;
            $totalsData = $totalsQuery->get();
            $totals = [
                'disburse_amount' => $totalsData->sum('disburse_amount'),
                'sharing_commission_amount' => $totalsData->sum(function ($row) {
                    return ($row->disburse_amount * ($row->sharing_commission ?? 0)) / 100;
                }),
            ];

            if ($isAdmin) {
                $totals['commission_amount'] = $totalsData->sum(function ($row) {
                    return ($row->disburse_amount * $row->commission_rate) / 100;
                });
                $totals['company_net'] = $totals['commission_amount'] - $totals['sharing_commission_amount'];
            }

            $dt = DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->editColumn('app_id', function ($row) {
                    return $row->app_id ?? '-';
                })
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name ?? '-';
                })
                ->editColumn('product_name', function ($row) {
                    return $row->product_name ?? '-';
                })
                ->editColumn('disburse_amount', function ($row) {
                    return indianNumberFormat($row->disburse_amount);
                })
                ->addColumn('sharing_commission_display', function ($row) {
                    return ($row->sharing_commission ?? 0) . '%';
                })
                ->addColumn('sharing_amount', function ($row) {
                    return indianNumberFormat(($row->disburse_amount * ($row->sharing_commission ?? 0)) / 100);
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                });

            if ($isAdmin) {
                $dt->addColumn('commission_rate_display', function ($row) {
                    return $row->commission_rate . '%';
                })
                ->addColumn('commission_amount', function ($row) {
                    return indianNumberFormat(($row->disburse_amount * $row->commission_rate) / 100);
                })
                ->addColumn('company_net', function ($row) {
                    $comm = ($row->disburse_amount * $row->commission_rate) / 100;
                    $sharing = ($row->disburse_amount * ($row->sharing_commission ?? 0)) / 100;
                    return indianNumberFormat($comm - $sharing);
                });
            }

            return $dt->with('totals', $totals)->make(true);
        }

        return view('Frontend.Reports.commission', compact('banks', 'products', 'isAdmin'));
    }

    /**
     * Advance Report
     */
    public function advanceReport(Request $request)
    {
        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $query = AdvanceAmountLog::query()
                ->join('advances', 'advance_amount_logs.advance_id', '=', 'advances.id')
                ->join('users', 'advances.user_id', '=', 'users.id')
                ->select(
                    'advance_amount_logs.*',
                    'advances.user_id',
                    'advances.advance_amount as current_balance',
                    'users.first_name',
                    'users.last_name'
                );

            $this->applyUserFilter($query, $userIds, 'advances.user_id');
            $this->applyDateFilter($query, $request, 'advance_amount_logs.created_at');

            if ($request->filled('user_id')) {
                $query->whereIn('advances.user_id', (array) $request->user_id);
            }

            $query->orderBy('advance_amount_logs.created_at', 'desc');

            $totalsQuery = clone $query;
            $totalsData = $totalsQuery->get();
            $totals = [
                'total_added' => $totalsData->where('type', 'add')->sum('advance_amount'),
                'total_deducted' => $totalsData->where('type', 'deduct')->sum('advance_amount'),
            ];

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->editColumn('current_balance', function ($row) {
                    return indianNumberFormat($row->current_balance);
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                })
                ->editColumn('type', function ($row) {
                    if ($row->type == 'add') {
                        return '<span class="badge bg-success">Add</span>';
                    }
                    return '<span class="badge bg-danger">Deduct</span>';
                })
                ->editColumn('advance_amount', function ($row) {
                    return indianNumberFormat($row->advance_amount);
                })
                ->editColumn('remark', function ($row) {
                    return $row->remark ?? '-';
                })
                ->rawColumns(['type'])
                ->with('totals', $totals)
                ->make(true);
        }

        $isAdmin = $this->isAdmin();
        return view('Frontend.Reports.advance', compact('isAdmin'));
    }

    /**
     * Application Report
     */
    public function applicationReport(Request $request)
    {
        $banks = Bank::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        $isAdmin = $this->isAdmin();

        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $query = Application::query()
                ->join('users', 'applications.user_id', '=', 'users.id')
                ->leftJoin('banks', 'applications.bank_id', '=', 'banks.id')
                ->leftJoin('products', 'applications.product_id', '=', 'products.id')
                ->select(
                    'applications.*',
                    'users.first_name',
                    'users.last_name',
                    'banks.name as bank_name',
                    'products.name as product_name'
                );

            $this->applyUserFilter($query, $userIds, 'applications.user_id');
            $this->applyDateFilter($query, $request, 'applications.created_at');

            if ($request->filled('user_id')) {
                $query->whereIn('applications.user_id', (array) $request->user_id);
            }
            if ($request->filled('bank_id')) {
                $query->where('applications.bank_id', $request->bank_id);
            }
            if ($request->filled('product_id')) {
                $query->where('applications.product_id', $request->product_id);
            }
            if ($request->filled('status')) {
                $query->where('applications.status', $request->status);
            }

            $query->orderBy('applications.created_at', 'desc');

            $totalsQuery = clone $query;
            $totalsData = $totalsQuery->get();
            $totals = [
                'total_disburse' => $totalsData->sum('disburse_amount'),
                'count' => $totalsData->count(),
            ];

            $dt = DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->editColumn('app_id', function ($row) {
                    return $row->app_id ?? '-';
                })
                ->editColumn('customer_name', function ($row) {
                    return $row->customer_name ?? '-';
                })
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name ?? '-';
                })
                ->editColumn('product_name', function ($row) {
                    return $row->product_name ?? '-';
                })
                ->editColumn('disburse_amount', function ($row) {
                    return indianNumberFormat($row->disburse_amount);
                })
                ->addColumn('sharing_commission_display', function ($row) {
                    return ($row->sharing_commission ?? 0) . '%';
                })
                ->editColumn('status', function ($row) {
                    $badgeColors = ['pending' => 'warning', 'approved' => 'primary', 'completed' => 'success', 'rejected' => 'danger'];
                    $color = $badgeColors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                })
                ->rawColumns(['status']);

            if ($isAdmin) {
                $dt->addColumn('commission_rate_display', function ($row) {
                    return $row->commission_rate . '%';
                });
            }

            return $dt->with('totals', $totals)->make(true);
        }

        return view('Frontend.Reports.application', compact('banks', 'products', 'isAdmin'));
    }

    /**
     * Channel Performance Report
     */
    public function channelPerformanceReport(Request $request)
    {
        $isAdmin = $this->isAdmin();

        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $dateFrom = $request->date_from;
            $dateTo = $request->date_to;

            $query = User::query()
                ->whereHas('roles', function ($q) {
                    $q->whereIn('roles.id', [2]);
                });

            if ($userIds !== null) {
                $query->whereIn('id', $userIds);
            }

            if ($dateFrom || $dateTo) {
                $query->withCount(['applications as total_applications' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
                }])
                ->withCount(['applications as approved_applications' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'approved');
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
                }])
                ->withCount(['applications as completed_applications' => function ($q) use ($dateFrom, $dateTo) {
                    $q->where('status', 'completed');
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
                }])
                ->withSum(['applications as total_disburse' => function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) $q->whereDate('created_at', '>=', $dateFrom);
                    if ($dateTo) $q->whereDate('created_at', '<=', $dateTo);
                }], 'disburse_amount');
            } else {
                $query->withCount(['applications as total_applications'])
                    ->withCount(['applications as approved_applications' => function ($q) {
                        $q->where('status', 'approved');
                    }])
                    ->withCount(['applications as completed_applications' => function ($q) {
                        $q->where('status', 'completed');
                    }])
                    ->withSum('applications as total_disburse', 'disburse_amount');
            }

            $data = $query->orderBy('total_applications', 'desc')->get();

            foreach ($data as $channel) {
                $channelUserIds = [$channel->id];
                $associateIds = ChannelUser::where('channel_id', $channel->id)
                    ->pluck('associate_channel_id')
                    ->toArray();
                $allIds = array_merge($channelUserIds, $associateIds);

                $settlementQuery = Settlement::whereIn('user_id', $allIds);
                if ($dateFrom) $settlementQuery->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $settlementQuery->whereDate('created_at', '<=', $dateTo);
                $channel->total_settlement = $settlementQuery->where('status', 'completed')->sum('gross_amount');

                $commQuery = Application::whereIn('user_id', $allIds);
                if ($dateFrom) $commQuery->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $commQuery->whereDate('created_at', '<=', $dateTo);
                $channel->total_commission = $commQuery->sum(DB::raw('(disburse_amount * commission_rate) / 100'));

                $earningsQuery = Application::whereIn('user_id', $allIds);
                if ($dateFrom) $earningsQuery->whereDate('created_at', '>=', $dateFrom);
                if ($dateTo) $earningsQuery->whereDate('created_at', '<=', $dateTo);
                $channel->channel_earnings = $earningsQuery->sum(DB::raw('(disburse_amount * IFNULL(sharing_commission, 0)) / 100'));
            }

            $dt = DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('channel_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->editColumn('total_disburse', function ($row) {
                    return indianNumberFormat($row->total_disburse ?? 0);
                })
                ->editColumn('total_settlement', function ($row) {
                    return indianNumberFormat($row->total_settlement ?? 0);
                })
                ->addColumn('channel_earnings', function ($row) {
                    return indianNumberFormat($row->channel_earnings ?? 0);
                });

            if ($isAdmin) {
                $dt->editColumn('total_commission', function ($row) {
                    return indianNumberFormat($row->total_commission ?? 0);
                })
                ->addColumn('company_net', function ($row) {
                    return indianNumberFormat(($row->total_commission ?? 0) - ($row->channel_earnings ?? 0));
                });
            }

            return $dt->make(true);
        }

        return view('Frontend.Reports.channel_performance', compact('isAdmin'));
    }

    /**
     * Bank-wise Disbursement Report
     */
    public function bankDisbursementReport(Request $request)
    {
        $banks = Bank::orderBy('name')->get();
        $isAdmin = $this->isAdmin();

        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $selectColumns = [
                'banks.name as bank_name',
                'applications.bank_id',
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

            if ($userIds !== null) {
                $query->whereIn('applications.user_id', $userIds);
            }

            $this->applyDateFilter($query, $request, 'applications.created_at');

            if ($request->filled('bank_id')) {
                $query->where('applications.bank_id', $request->bank_id);
            }

            $data = $query->orderBy('total_disburse', 'desc')->get();

            $totals = [
                'app_count' => $data->sum('app_count'),
                'total_disburse' => $data->sum('total_disburse'),
                'completed_count' => $data->sum('completed_count'),
            ];

            $dt = DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('total_disburse', function ($row) {
                    return indianNumberFormat($row->total_disburse);
                })
                ->addColumn('avg_sharing_rate_display', function ($row) {
                    return number_format($row->avg_sharing_rate, 2) . '%';
                });

            if ($isAdmin) {
                $dt->editColumn('avg_commission_rate', function ($row) {
                    return number_format($row->avg_commission_rate, 2) . '%';
                });
            }

            return $dt->with('totals', $totals)->make(true);
        }

        return view('Frontend.Reports.bank_disbursement', compact('banks', 'isAdmin'));
    }

    /**
     * Product-wise Report
     */
    public function productWiseReport(Request $request)
    {
        $products = Product::orderBy('name')->get();
        $isAdmin = $this->isAdmin();

        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $query = Application::query()
                ->join('products', 'applications.product_id', '=', 'products.id')
                ->leftJoin('banks', 'applications.bank_id', '=', 'banks.id')
                ->select(
                    'products.name as product_name',
                    'banks.name as bank_name',
                    'applications.product_id',
                    'applications.bank_id',
                    DB::raw('COUNT(*) as app_count'),
                    DB::raw('SUM(applications.disburse_amount) as total_disburse'),
                    DB::raw('AVG(applications.commission_rate) as avg_commission_rate'),
                    DB::raw('AVG(IFNULL(applications.sharing_commission, 0)) as avg_sharing_rate')
                )
                ->groupBy('applications.product_id', 'products.name', 'applications.bank_id', 'banks.name');

            if ($userIds !== null) {
                $query->whereIn('applications.user_id', $userIds);
            }

            $this->applyDateFilter($query, $request, 'applications.created_at');

            if ($request->filled('product_id')) {
                $query->where('applications.product_id', $request->product_id);
            }

            $data = $query->orderBy('total_disburse', 'desc')->get();

            $totals = [
                'app_count' => $data->sum('app_count'),
                'total_disburse' => $data->sum('total_disburse'),
            ];

            $dt = DataTables::of($data)
                ->addIndexColumn()
                ->editColumn('bank_name', function ($row) {
                    return $row->bank_name ?? '-';
                })
                ->editColumn('total_disburse', function ($row) {
                    return indianNumberFormat($row->total_disburse);
                })
                ->addColumn('avg_sharing_rate_display', function ($row) {
                    return number_format($row->avg_sharing_rate, 2) . '%';
                });

            if ($isAdmin) {
                $dt->editColumn('avg_commission_rate', function ($row) {
                    return number_format($row->avg_commission_rate, 2) . '%';
                });
            }

            return $dt->with('totals', $totals)->make(true);
        }

        return view('Frontend.Reports.product_wise', compact('products', 'isAdmin'));
    }

    /**
     * Settlement Report
     */
    public function settlementReport(Request $request)
    {
        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $query = Settlement::query()
                ->join('users', 'settlements.user_id', '=', 'users.id')
                ->select(
                    'settlements.*',
                    'users.first_name',
                    'users.last_name'
                )
                ->withCount('distributions');

            $this->applyUserFilter($query, $userIds, 'settlements.user_id');
            $this->applyDateFilter($query, $request, 'settlements.created_at');

            if ($request->filled('user_id')) {
                $query->whereIn('settlements.user_id', (array) $request->user_id);
            }
            if ($request->filled('status')) {
                $query->where('settlements.status', $request->status);
            }

            $query->orderBy('settlements.created_at', 'desc');

            $totals = [
                'gross_amount' => (clone $query)->sum('settlements.gross_amount'),
                'amount' => (clone $query)->sum('settlements.amount'),
            ];

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('settlement_id', function ($row) {
                    return '#' . $row->id;
                })
                ->addColumn('user_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->editColumn('gross_amount', function ($row) {
                    return indianNumberFormat($row->gross_amount);
                })
                ->editColumn('amount', function ($row) {
                    return indianNumberFormat($row->amount);
                })
                ->editColumn('status', function ($row) {
                    if ($row->status == 'completed') {
                        return '<span class="badge bg-success">Completed</span>';
                    }
                    return '<span class="badge bg-warning">Pending</span>';
                })
                ->editColumn('settlement_date', function ($row) {
                    return $row->settlement_date ? Carbon::parse($row->settlement_date)->format('d-m-Y') : '-';
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                })
                ->rawColumns(['status'])
                ->with('totals', $totals)
                ->make(true);
        }

        $isAdmin = $this->isAdmin();
        return view('Frontend.Reports.settlement', compact('isAdmin'));
    }

    /**
     * Transaction Report
     */
    public function transactionReport(Request $request)
    {
        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();

            $query = Transaction::query()
                ->join('users', 'transactions.user_id', '=', 'users.id')
                ->select(
                    'transactions.*',
                    'users.first_name',
                    'users.last_name'
                );

            $this->applyUserFilter($query, $userIds, 'transactions.user_id');
            $this->applyDateFilter($query, $request, 'transactions.created_at');

            if ($request->filled('user_id')) {
                $query->whereIn('transactions.user_id', (array) $request->user_id);
            }
            if ($request->filled('status')) {
                $query->where('transactions.status', $request->status);
            }

            $query->orderBy('transactions.created_at', 'desc');

            $totals = [
                'gross_amount' => (clone $query)->sum('transactions.gross_amount'),
                'tds_amount' => (clone $query)->sum('transactions.tds_amount'),
                'advance_amount' => (clone $query)->sum('transactions.advance_amount'),
                'net_payable' => (clone $query)->sum('transactions.net_payable'),
            ];

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('user_name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name;
                })
                ->editColumn('gross_amount', function ($row) {
                    return indianNumberFormat($row->gross_amount);
                })
                ->editColumn('tds_amount', function ($row) {
                    return indianNumberFormat($row->tds_amount);
                })
                ->editColumn('advance_amount', function ($row) {
                    return indianNumberFormat($row->advance_amount);
                })
                ->editColumn('net_payable', function ($row) {
                    return indianNumberFormat($row->net_payable);
                })
                ->editColumn('status', function ($row) {
                    $badgeColors = ['pending' => 'warning', 'approved' => 'primary', 'completed' => 'success', 'rejected' => 'danger', 'cancelled' => 'secondary'];
                    $color = $badgeColors[$row->status] ?? 'secondary';
                    return '<span class="badge bg-' . $color . '">' . ucfirst($row->status) . '</span>';
                })
                ->editColumn('created_at', function ($row) {
                    return Carbon::parse($row->created_at)->format('d-m-Y');
                })
                ->rawColumns(['status'])
                ->with('totals', $totals)
                ->make(true);
        }

        $isAdmin = $this->isAdmin();
        return view('Frontend.Reports.transaction', compact('isAdmin'));
    }

    /**
     * Monthly Summary Report
     */
    public function monthlySummaryReport(Request $request)
    {
        $years = range(now()->year - 3, now()->year);
        $isAdmin = $this->isAdmin();

        if ($request->ajax()) {
            $userIds = $this->getFilteredUserIds();
            $year = $request->get('year', now()->year);

            $months = [];
            for ($m = 1; $m <= 12; $m++) {
                $appQuery = Application::whereYear('created_at', $year)->whereMonth('created_at', $m);
                if ($userIds !== null) $appQuery->whereIn('user_id', $userIds);
                $appCount = $appQuery->count();
                $totalDisburse = (clone $appQuery)->sum('disburse_amount');

                $commQuery = Application::whereYear('created_at', $year)->whereMonth('created_at', $m);
                if ($userIds !== null) $commQuery->whereIn('user_id', $userIds);
                $totalCommission = $commQuery->sum(DB::raw('(disburse_amount * commission_rate) / 100'));

                $channelPayouts = Application::whereYear('created_at', $year)->whereMonth('created_at', $m);
                if ($userIds !== null) $channelPayouts->whereIn('user_id', $userIds);
                $channelPayouts = $channelPayouts->sum(DB::raw('(disburse_amount * IFNULL(sharing_commission, 0)) / 100'));

                $settQuery = Settlement::whereYear('created_at', $year)->whereMonth('created_at', $m)->where('status', 'completed');
                if ($userIds !== null) $settQuery->whereIn('user_id', $userIds);
                $totalSettlement = $settQuery->sum('gross_amount');

                $txnQuery = Transaction::whereYear('created_at', $year)->whereMonth('created_at', $m);
                if ($userIds !== null) $txnQuery->whereIn('user_id', $userIds);
                $txnCount = $txnQuery->count();
                $netPayable = (clone $txnQuery)->sum('net_payable');

                $monthData = [
                    'month' => Carbon::create($year, $m, 1)->format('F'),
                    'app_count' => $appCount,
                    'total_disburse' => $totalDisburse,
                    'total_commission' => $totalCommission,
                    'channel_payouts' => $channelPayouts,
                    'company_net' => $totalCommission - $channelPayouts,
                    'total_settlement' => $totalSettlement,
                    'txn_count' => $txnCount,
                    'net_payable' => $netPayable,
                ];

                $months[] = $monthData;
            }

            $totals = [
                'app_count' => collect($months)->sum('app_count'),
                'total_disburse' => collect($months)->sum('total_disburse'),
                'total_commission' => collect($months)->sum('total_commission'),
                'channel_payouts' => collect($months)->sum('channel_payouts'),
                'company_net' => collect($months)->sum('company_net'),
                'total_settlement' => collect($months)->sum('total_settlement'),
                'txn_count' => collect($months)->sum('txn_count'),
                'net_payable' => collect($months)->sum('net_payable'),
            ];

            $dt = DataTables::of(collect($months))
                ->addIndexColumn()
                ->editColumn('total_disburse', function ($row) {
                    return indianNumberFormat($row['total_disburse']);
                })
                ->editColumn('total_commission', function ($row) {
                    return indianNumberFormat($row['total_commission']);
                })
                ->editColumn('channel_payouts', function ($row) {
                    return indianNumberFormat($row['channel_payouts']);
                })
                ->editColumn('company_net', function ($row) {
                    return indianNumberFormat($row['company_net']);
                })
                ->editColumn('total_settlement', function ($row) {
                    return indianNumberFormat($row['total_settlement']);
                })
                ->editColumn('net_payable', function ($row) {
                    return indianNumberFormat($row['net_payable']);
                });

            return $dt->with('totals', $totals)->make(true);
        }

        return view('Frontend.Reports.monthly_summary', compact('years', 'isAdmin'));
    }

    /**
     * Export any report to Excel.
     */
    public function export(Request $request, $type)
    {
        $userIds = $this->getFilteredUserIds();
        $isAdmin = $this->isAdmin();
        $filters = $request->all();
        $filename = $type . '_report_' . date('Y-m-d') . '.xlsx';

        return Excel::download(new ReportExport($type, $filters, $userIds, $isAdmin), $filename);
    }

    /**
     * AJAX search endpoint for Select2 user filter.
     */
    public function searchUsers(Request $request)
    {
        $term = $request->get('q');
        $userIds = $this->getFilteredUserIds();

        $query = User::select('id', 'first_name', 'last_name', 'email')
            ->when($term, function ($q) use ($term) {
                $q->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', '%' . $term . '%')
                        ->orWhere('last_name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(15);

        if ($userIds !== null) {
            $query->whereIn('id', $userIds);
        }

        $users = $query->get();

        $results = $users->map(function ($user) {
            $display = trim($user->first_name . ' ' . $user->last_name);
            if ($user->email) {
                $display .= ' (' . $user->email . ')';
            }
            return ['id' => $user->id, 'text' => $display];
        });

        return response()->json(['results' => $results]);
    }
}
