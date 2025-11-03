<?php

namespace App\Http\Controllers\Bank_MIS;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Bank;
use App\Models\BankMIS;
use App\Models\Product;
use App\Models\StaffAssign;
use App\Models\User;
use BankMis as GlobalBankMis;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class BankMisController extends Controller
{
    public function index(Request $request){
        $Route = 'BankMIS';
        $user = Auth::user();
        $channelroleId = 2;
        $salesroleId = 3;
        $bank = Bank::all();
        $product = Product::all();

        if ($request->ajax()) {
            $query = BankMIS::with(['bank','product']);

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
                $query->whereHas('bank', function ($q) use ($request) {
                    $q->where('name', $request->bank_name);
                });
            }
    
            if ($request->product_name) {
                $query->whereHas('product', function ($q) use ($request) {
                    $q->where('name', $request->product_name);
                });
            }
            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('checkbox', function ($row) {
                        return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                    
                    return '';
                })
                ->editColumn('bank_id', function ($row) {
                    return $row->bank_id ? $row->bank->name : '-'; 
                })
                ->editColumn('product_id', function ($row) {
                    return $row->product_id ? $row->product->name : '-'; 
                })
                ->editColumn('group', function ($row) {
                    return $row->group ? $row->group : '-'; 
                })
                ->editColumn('customer_name', function ($row) {
                    return $row->customer_name ? $row->customer_name : '-'; 
                })
                ->editColumn('customer_firm_name', function ($row) {
                    return $row->customer_firm_name ? $row->customer_firm_name : '-'; 
                })
                ->editColumn('location', function ($row) {
                    return $row->location ? $row->location : '-'; 
                })
                ->editColumn('case_location', function ($row) {
                    return $row->case_location ? $row->case_location : '-'; 
                })
                ->editColumn('disbAmount', function ($row) {
                    return $row->disbAmount ? $row->disbAmount : '-'; 
                })
                ->editColumn('payout_amount', function ($row) {
                    return $row->payout_amount ? $row->payout_amount : '-'; 
                })
                ->editColumn('payout_rate', function ($row) {
                    return $row->payout_rate ? $row->payout_rate : '-'; 
                })
                ->editColumn('pf', function ($row) {
                    return $row->pf ? $row->pf : '-'; 
                })
                ->editColumn('subvention', function ($row) {
                    return $row->subvention ? $row->subvention : '-'; 
                })
                ->editColumn('roi', function ($row) {
                    return $row->roi ? $row->roi : '-'; 
                })
                ->editColumn('insurance', function ($row) {
                    return $row->insurance ? $row->insurance : '-'; 
                })
                ->editColumn('otc_pdd_status', function ($row) {
                    return $row->otc_pdd_status ? $row->otc_pdd_status : '-'; 
                })
                ->addColumn('action', function ($row) {
                    $btn = '';

                    if (auth()->user()->hasPermission('bank_mis', 'view')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/bank_mis/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    if (auth()->user()->hasPermission('bank_mis', 'delete')) {
                        
                            $btn .= "<img class='delete-btn' data-bank-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete'>";
                        
                        
                    }
                    return $btn;
                })
                ->rawColumns(['checkbox','action'])
                ->make(true);
        }

            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        return view('Frontend.Bank_MIS.index',compact('channels','sales','bank','product'));
    }

    public function add()
    {
        $Route = 'BankMIS';
        $user = Auth::user();
        $banks = BankMIS::get();
        $channelroleId = 2;
        $salesroleId = 3;

        if ($user->roles[0]->id == 1) {
            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $channels = User::where('id', $user->id)->whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::where('id', $user->id)->whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $channels = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        }

        $states = getState();

        return view('Frontend.Bank_MIS.create', compact('Route', 'channels', 'sales', 'banks', 'states'));
    }


    public function filter(Request $request)
    {
        $Route = 'BankMIS';
        $user = Auth::user();
        $query = BankMIS::query();


        if ($request->status !== null && $request->status !== 'All') {
            $query->where('status', $request->status);
        }
        if ($request->from_date !== null) {
            $query->whereDate('disbursement_date', '>=', $request->from_date);
        }
        if ($request->to_date !== null) {
            $query->whereDate('disbursement_date', '<=', $request->to_date);
        }
        if ($request->user_id !== null) {
            $query->where('user_id',  $request->user_id);
        }

        $query->orderBy('id', 'desc');
        // Execute the query and fetch results
        $bank = $query->paginate(25);
        return view('Frontend.Bank_MIS.Table.bankMis_table', compact('Route', 'bank'));
    }

    public function show($id){
        $Route = 'Edit Bank MIS';
        $bank_mis = BankMIS::findOrFail($id);
        $bank = Bank::where('id',$bank_mis->bank_id)->get();
        $product = Product::where('id',$bank_mis->product_id)->get();
        return view('Frontend.Bank_MIS.show',compact('bank_mis','bank','product'));
    }

    public function destroy(BankMIS $bank)
    {
        $bank->delete();
        return true;
    }

    public function bulkDelete(Request $request)
    {
        $misIds = $request->mis_ids;
    
        if (!$misIds || !is_array($misIds)) {
            return response()->json(['message' => 'No data selected.'], 400);
        }
    
        // Check if any BankMIS IDs are associated with an Application
        $misIdsWithApplications = Application::whereIn('bank_mis_id', $misIds)->pluck('bank_mis_id')->toArray();
    
        // Filter out the misIds that are already associated with an application
        $misIdsToDelete = array_diff($misIds, $misIdsWithApplications);
    
        if (empty($misIdsToDelete)) {
            return response()->json(['message' => 'No MIS data available for deletion, as they are already associated with an application.'], 400);
        }
    
        // Delete only BankMIS records that are not associated with an application
        $deletedCount = BankMIS::whereIn('id', $misIdsToDelete)->delete();
    
        return response()->json(['message' => $deletedCount . ' applications deleted successfully.'], 200);
    }
    

}
