<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ChannelUser;
use App\Models\Settlement;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role_id = $user->roles[0]->id;

        switch ($role_id) {
            case 1:
                return $this->adminDashboard();
            case 2:
                return $this->channelDashboard();
            case 35:
                return $this->makerDashboard();
            case 36:
                return $this->checkerDashboard();
            case 37:
                return $this->associateDashboard();
            default:
                return $this->channelDashboard();
        }
    }

    private function adminDashboard()
    {
        $Route = 'Dashboard';

        // Application stats
        $total_application = Application::count();
        $pending_application = Application::where('status', 'pending')->count();
        $completed_application = Application::where('status', 'completed')->count();
        $rejected_application = Application::where('status', 'rejected')->count();
        $approved_application = Application::where('status', 'approved')->count();

        // Settlement stats
        $pending_settlement = Settlement::where('status', 'pending')->sum('amount');
        $total_settlement = Settlement::where('status', 'completed')->sum('amount');

        // User stats
        $total_channel_partner = User::where('user_type', 'channel')->where('status', 1)->count();
        $total_associate = User::where('user_type', 'Associate_Channel')->where('status', 1)->count();

        // Transaction stats
        $pending_transactions = Transaction::where('status', 'pending')->count();
        $approved_transactions = Transaction::where('status', 'approved')->count();
        $completed_transactions = Transaction::where('status', 'completed')->count();
        $cancelled_transactions = Transaction::where('status', 'cancelled')->count();

        // Top 5 performing channels by total disbursement
        $topChannels = User::where('user_type', 'channel')
            ->where('status', 1)
            ->select('users.id', 'users.first_name', 'users.last_name')
            ->withCount('applications')
            ->addSelect([
                'total_disburse' => Application::selectRaw('COALESCE(SUM(disburse_amount), 0)')
                    ->whereColumn('applications.user_id', 'users.id'),
            ])
            ->having('total_disburse', '>', 0)
            ->orderByDesc('total_disburse')
            ->limit(5)
            ->get();

        // Monthly settlement data for bar chart
        $monthlyCounts = Settlement::select(
            DB::raw('MONTH(settlement_date) as month'),
            DB::raw('SUM(amount) as sum')
        )
            ->groupBy(DB::raw('MONTH(settlement_date)'))
            ->orderBy(DB::raw('MONTH(settlement_date)'), 'ASC')
            ->pluck('sum', 'month')
            ->toArray();

        $monthlyData = json_encode($this->fillMonthlyData($monthlyCounts));

        return view('Frontend.Dashboard.admin', compact(
            'Route',
            'total_application', 'pending_application', 'completed_application', 'rejected_application', 'approved_application',
            'pending_settlement', 'total_settlement',
            'total_channel_partner', 'total_associate',
            'pending_transactions', 'approved_transactions', 'completed_transactions', 'cancelled_transactions',
            'topChannels',
            'monthlyData'
        ));
    }

    private function makerDashboard()
    {
        $Route = 'Dashboard';

        // Queue stats
        $pending_applications = Application::where('status', 'pending')->count();

        $approved_today = Application::where('status', 'approved')
            ->whereDate('updated_at', Carbon::today())
            ->count();

        $approved_this_month = Application::where('status', 'approved')
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->count();

        $rejected_this_month = Application::where('status', 'rejected')
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->count();

        // Overall stats
        $total_processed = Application::whereIn('status', ['approved', 'rejected', 'completed'])->count();
        $total_approved = Application::whereIn('status', ['approved', 'completed'])->count();
        $approval_rate = $total_processed > 0 ? round(($total_approved / $total_processed) * 100, 1) : 0;

        // Monthly trend data (approved vs rejected per month)
        $monthlyApproved = Application::whereIn('status', ['approved', 'completed'])
            ->select(DB::raw('MONTH(updated_at) as month'), DB::raw('COUNT(*) as cnt'))
            ->whereYear('updated_at', Carbon::now()->year)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->pluck('cnt', 'month')
            ->toArray();

        $monthlyRejected = Application::where('status', 'rejected')
            ->select(DB::raw('MONTH(updated_at) as month'), DB::raw('COUNT(*) as cnt'))
            ->whereYear('updated_at', Carbon::now()->year)
            ->groupBy(DB::raw('MONTH(updated_at)'))
            ->pluck('cnt', 'month')
            ->toArray();

        $monthlyApprovedData = json_encode($this->fillMonthlyData($monthlyApproved));
        $monthlyRejectedData = json_encode($this->fillMonthlyData($monthlyRejected));

        return view('Frontend.Dashboard.maker', compact(
            'Route',
            'pending_applications', 'approved_today', 'approved_this_month', 'rejected_this_month',
            'total_processed', 'approval_rate',
            'monthlyApprovedData', 'monthlyRejectedData'
        ));
    }

    private function checkerDashboard()
    {
        $Route = 'Dashboard';

        // Queue stats
        $awaiting_review = Application::where('status', 'approved')->count();

        $completed_this_month = Application::where('status', 'completed')
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->count();

        $rejected_this_month = Application::where('status', 'rejected')
            ->whereYear('updated_at', Carbon::now()->year)
            ->whereMonth('updated_at', Carbon::now()->month)
            ->count();

        // Transaction stats
        $pending_transactions = Transaction::where('status', 'pending')->count();
        $approved_transactions = Transaction::where('status', 'approved')->count();
        $completed_transactions = Transaction::where('status', 'completed')->count();
        $cancelled_transactions = Transaction::where('status', 'cancelled')->count();

        // Settlement stats
        $pending_settlement = Settlement::where('status', 'pending')->sum('amount');
        $total_settlement = Settlement::where('status', 'completed')->sum('amount');

        // Monthly transaction completion trend
        $monthlyTransactions = Transaction::where('status', 'completed')
            ->select(DB::raw('MONTH(completed_at) as month'), DB::raw('COUNT(*) as cnt'))
            ->whereYear('completed_at', Carbon::now()->year)
            ->groupBy(DB::raw('MONTH(completed_at)'))
            ->pluck('cnt', 'month')
            ->toArray();

        $monthlySettlements = Settlement::where('status', 'completed')
            ->select(DB::raw('MONTH(settlement_date) as month'), DB::raw('SUM(amount) as sum'))
            ->whereYear('settlement_date', Carbon::now()->year)
            ->groupBy(DB::raw('MONTH(settlement_date)'))
            ->pluck('sum', 'month')
            ->toArray();

        $monthlyTransactionData = json_encode($this->fillMonthlyData($monthlyTransactions));
        $monthlySettlementData = json_encode($this->fillMonthlyData($monthlySettlements));

        return view('Frontend.Dashboard.checker', compact(
            'Route',
            'awaiting_review', 'completed_this_month', 'rejected_this_month',
            'pending_transactions', 'approved_transactions', 'completed_transactions', 'cancelled_transactions',
            'pending_settlement', 'total_settlement',
            'monthlyTransactionData', 'monthlySettlementData'
        ));
    }

    private function channelDashboard()
    {
        $Route = 'Dashboard';
        $userId = Auth::id();

        // For Channel users, also include their associates' applications
        $userIds = [$userId];
        $associateIds = ChannelUser::where('channel_id', $userId)->pluck('associate_channel_id')->toArray();
        $userIds = array_merge($userIds, $associateIds);

        // Application stats
        $total_application = Application::whereIn('user_id', $userIds)->count();
        $pending_application = Application::whereIn('user_id', $userIds)->where('status', 'pending')->count();
        $completed_application = Application::whereIn('user_id', $userIds)->where('status', 'completed')->count();
        $rejected_application = Application::whereIn('user_id', $userIds)->where('status', 'rejected')->count();

        // Financial stats
        $today_sales = Application::whereIn('user_id', $userIds)
            ->whereDate('disbursement_date', Carbon::today())
            ->sum('disburse_amount');
        $monthly_sales = Application::whereIn('user_id', $userIds)
            ->whereYear('disbursement_date', Carbon::now()->year)
            ->whereMonth('disbursement_date', Carbon::now()->month)
            ->sum('disburse_amount');
        $pending_settlement = Settlement::whereIn('user_id', $userIds)->where('status', 'pending')->sum('amount');
        $total_settlement = Settlement::whereIn('user_id', $userIds)->where('status', 'completed')->sum('amount');

        // Transaction stats
        $pending_transactions = Transaction::whereIn('user_id', $userIds)->where('status', 'pending')->count()
            + Transaction::whereIn('user_id', $userIds)->where('status', 'approved')->count();
        $completed_transactions = Transaction::whereIn('user_id', $userIds)->where('status', 'completed')->count();

        // Monthly settlement data for bar chart
        $monthlyCounts = Settlement::whereIn('user_id', $userIds)
            ->select(DB::raw('MONTH(settlement_date) as month'), DB::raw('SUM(amount) as sum'))
            ->groupBy(DB::raw('MONTH(settlement_date)'))
            ->orderBy(DB::raw('MONTH(settlement_date)'), 'ASC')
            ->pluck('sum', 'month')
            ->toArray();

        $monthlyData = json_encode($this->fillMonthlyData($monthlyCounts));

        return view('Frontend.Dashboard.channel', compact(
            'Route',
            'total_application', 'pending_application', 'completed_application', 'rejected_application',
            'today_sales', 'monthly_sales', 'pending_settlement', 'total_settlement',
            'pending_transactions', 'completed_transactions',
            'monthlyData'
        ));
    }

    private function associateDashboard()
    {
        $Route = 'Dashboard';
        $userId = Auth::id();

        // Only pending and completed
        $pending_application = Application::where('user_id', $userId)->where('status', 'pending')->count();
        $completed_application = Application::where('user_id', $userId)->where('status', 'completed')->count();

        return view('Frontend.Dashboard.associate', compact(
            'Route',
            'pending_application', 'completed_application'
        ));
    }

    /**
     * Fill monthly data array (1-12) with values, defaulting to 0
     */
    private function fillMonthlyData(array $monthlyCounts): array
    {
        $data = [];
        for ($i = 1; $i <= 12; $i++) {
            $data[] = $monthlyCounts[$i] ?? 0;
        }
        return $data;
    }
}
