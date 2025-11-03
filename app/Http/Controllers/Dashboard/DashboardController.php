<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Role;
use App\Models\Settlement;
use App\Models\StaffAssign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        
        $Route = 'Dashboard';
        $user = Auth::user();
        $role_id = $user->roles[0]->id;
        $total_application = 0;
        $pending_application = 0;
        $completed_application = 0;
        $rejected_application = 0;
        $total_channel_partner = 0;
        $total_sales_person = 0;
        $total_staff = 0;
        $total_roles = 0;
        $today_sales = 0;
        $monthly_sales = 0;
        $pending_settlement = 0;
        $total_settlement = 0;
        if ($user->roles[0]->id == 1) {
              $monthlyCounts = Settlement::select(DB::raw('MONTH(settlement_date) as month'), DB::raw('SUM(amount) as sum'))
            ->groupBy(DB::raw('MONTH(settlement_date)'))
            ->orderBy(DB::raw('MONTH(settlement_date)'), 'ASC')
            ->pluck('sum', 'month')
            ->toArray();
            $total_application = Application::count();
            $pending_application = Application::where('status', 'pending')->count();
            $completed_application = Application::where('status', 'completed')->count();
            $rejected_application = Application::where('status', 'rejected')->count();
            $total_channel_partner = User::where('user_type', 'channel')->where('status', 1)->count();
            $total_sales_person = User::where('user_type', 'sales')->where('status', 1)->count();
            $total_staff = User::where('status', 1)->where('user_type', 'staff')->count();
            $total_roles = Role::where('status', 1)->whereNotIn('id',[1,2,3])->count();
            $today_sales = Application::whereDate('disbursement_date', Carbon::now()->toDateString())->sum('disburse_amount');
            $monthly_sales = Application::whereYear('disbursement_date', Carbon::now()->year)
                ->whereMonth('disbursement_date', Carbon::now()->month)
                ->sum('disburse_amount');
            $pending_settlement = Settlement::where('status','pending')->sum('amount');
            $total_settlement = Settlement::where('status','completed')->sum('amount');

        } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $monthlyCounts = Settlement::where('user_id', Auth::id())
            ->select(DB::raw('MONTH(settlement_date) as month'), DB::raw('SUM(amount) as sum'))
            ->groupBy(DB::raw('MONTH(settlement_date)'))
            ->orderBy(DB::raw('MONTH(settlement_date)'), 'ASC')
            ->pluck('sum', 'month')
            ->toArray();
            $total_application = Application::where('user_id', Auth::id())->count();
            $pending_application = Application::where('user_id', Auth::id())->where('status', 'pending')->count();
            $completed_application = Application::where('user_id', Auth::id())->where('status', 'completed')->count();
            $rejected_application = Application::where('user_id', Auth::id())->where('status', 'rejected')->count();
            $today_sales = Application::where('user_id', Auth::id())->whereDate('disbursement_date', Carbon::now()->toDateString())->sum('disburse_amount');
            $monthly_sales = Application::where('user_id', Auth::id())->whereYear('disbursement_date', Carbon::now()->year)
                ->whereMonth('disbursement_date', Carbon::now()->month)
                ->sum('disburse_amount');

            $pending_settlement = Settlement::where('user_id', Auth::id())->where('status', 'pending')->sum('amount');
            $total_settlement = Settlement::where('user_id', Auth::id())->where('status', 'completed')->sum('amount');
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $monthlyCounts = Settlement::whereIn('user_id', $channel_assign)
            ->select(DB::raw('MONTH(settlement_date) as month'), DB::raw('SUM(amount) as sum'))
            ->groupBy(DB::raw('MONTH(settlement_date)'))
            ->orderBy(DB::raw('MONTH(settlement_date)'), 'ASC')
            ->pluck('sum', 'month')
            ->toArray();

            $total_application = Application::where('user_id', $channel_assign)->count();
            $pending_application = Application::where('user_id', $channel_assign)->where('status', 'pending')->count();
            $completed_application = Application::where('user_id', $channel_assign)->where('status', 'completed')->count();
            $rejected_application = Application::where('user_id', $channel_assign)->where('status', 'rejected')->count();
            $total_channel_partner = User::whereIn('id', $channel_assign)->where('user_type', 'channel')->count();
            $total_sales_person = User::whereIn('id', $channel_assign)->where('user_type', 'sales')->count();
            $total_staff = User::where('status', 1)->where('user_type', 'staff')->count();
            $total_roles = Role::where('status', 1)->whereNotIn('id', [1, 2, 3])->count();
            $today_sales = Application::whereIn('user_id',$channel_assign)->whereDate('disbursement_date', Carbon::now()->toDateString())->sum('disburse_amount');
            $monthly_sales = Application::whereIn('user_id', $channel_assign)->whereYear('disbursement_date', Carbon::now()->year)
                ->whereMonth('disbursement_date', Carbon::now()->month)
                ->sum('disburse_amount');
            $pending_settlement = Settlement::whereIn('user_id',$channel_assign)->where('status', 'pending')->sum('amount');
            $total_settlement = Settlement::whereIn('user_id',$channel_assign)->where('status', 'completed')->sum('amount');
        }
       


        $monthlyData = [];

        // Fill in the fetched data into the monthlyData array
        for ($i = 1; $i <= 12; $i++) {
            if (isset($monthlyCounts[$i])) {
                array_push($monthlyData, $monthlyCounts[$i]);
            } else {
                array_push($monthlyData, 0);
            }
        }

        $monthlyData = json_encode($monthlyData);
        return view('Frontend.Dashboard.index', compact(
            'Route', 'total_application', 'pending_application', 'completed_application',
             'rejected_application', 'total_channel_partner', 'total_sales_person', 'role_id',
             'total_staff','total_roles','monthlyData','today_sales','monthly_sales',
            'pending_settlement',
            'total_settlement'
            ));
    }
}
