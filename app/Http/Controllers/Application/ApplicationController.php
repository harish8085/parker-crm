<?php

namespace App\Http\Controllers\Application;

use App\Exports\ApplicationExport;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessMISDataJob;
use App\Jobs\ProcessSettlement;
use App\Models\Application;
use App\Models\Bank;
use App\Models\BankMIS;
use App\Models\BankPayout;
use App\Models\BankProduct;
use App\Models\Product;
use App\Models\RemarkStatus;
use App\Models\Service;
use App\Models\Settlement;
use App\Models\SheetMatching;
use App\Models\StaffAssign;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\SimpleExcel\SimpleExcelReader;
use Yajra\DataTables\Facades\DataTables;

class ApplicationController extends Controller
{

    public function index(Request $request)
    {
        $Route = 'Application';
        $user = Auth::user();
        $channelroleId = 2;
        $salesroleId = 3;
        $applications = Application::all();
        $users = User::all();
        $bank = Bank::all();
        $product = Product::all();
        // Fetching channels and sales persons
        $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
            $query->where('id', $channelroleId);
        })->get();

        $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
            $query->where('id', $salesroleId);
        })->get();
        if ($request->ajax()) {
            $query = Application::with(['bank', 'product'])->orderBy('id', 'desc');

            $query = $this->sortData($request->date, $request->date_range, $query);

            if ($request->partner_name) {
                $query->where('user_id', $request->partner_name);
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

            if ($request->status) {
                $query->where('status', $request->status);
            }

            if ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
                $query = $query->where('user_id', Auth::id());
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('checkbox', function ($row) {
                        if ($row->status !== 'completed') {
                            return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                        }
                        return '';
                    })
                    ->editColumn('app_id', function ($row) {
                        // Determine CSS class based on app_id_is_matched
                        $class = $row->app_id_is_matched ? 'text-success' : 'text-danger';

                        // Prepare app_id_is_value for display
                        $app_id_value = $row->app_id_is_value ? '(' . $row->app_id_is_value . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $row->app_id .
                            '<p class="id-desc">' . $app_id_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('customer_name', function ($row) {
                        // Determine CSS class based on customer_name_is_matched
                        $class = $row->customer_name_is_matched ? 'text-success' : 'text-danger';

                        // Prepare customer_name_is_value for display
                        $customer_name_value = $row->customer_name_is_value ? '(' . $row->customer_name_is_value . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $row->customer_name .
                            '<p class="id-desc">' . $customer_name_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('bank_id', function ($row) {
                        // Determine CSS class based on bank_id_is_matched
                        $class = $row->bank_id_is_matched ? 'text-success' : 'text-danger';

                        // Get the bank name (or fallback to '-')
                        $bank_name = $row->bank ? $row->bank->name : '-';

                        // Prepare bank_sec_name for display
                        $bank_sec_name = $row->bank_sec_name ? '(' . $row->bank_sec_name . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $bank_name .
                            '<p class="id-desc">' . $bank_sec_name . '</p>' .
                            '</div>';
                    })
                    ->editColumn('product_id', function ($row) {
                        // Determine CSS class based on product_id_is_matched
                        $class = $row->product_id_is_matched ? 'text-success' : 'text-danger';

                        // Get the product name (or fallback to '-')
                        $product_name = $row->product ? $row->product->name : '-';

                        // Prepare product_sec_name for display
                        $product_sec_name = $row->product_sec_name ? '(' . $row->product_sec_name . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $product_name .
                            '<p class="id-desc">' . $product_sec_name . '</p>' .
                            '</div>';
                    })
                    ->editColumn('disburse_amount', function ($row) {
                        // Determine CSS class based on disburse_amount_is_matched
                        $class = $row->disburse_amount_is_matched ? 'text-success' : 'text-danger';

                        // Format the disburse amount using indianNumberFormat helper function
                        $disburse_amount = $row->disburse_amount ? '₹ ' . indianNumberFormat($row->disburse_amount) : '-';

                        // Format disburse_amount_is_value for display in parentheses, or fallback to '-'
                        $disburse_amount_is_value = $row->disburse_amount_is_value
                            ? '(' . indianNumberFormat($row->disburse_amount_is_value) . ')'
                            : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $disburse_amount .
                            '<p class="id-desc">' . $disburse_amount_is_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('commission_rate', function ($row) {
                        // Determine CSS class based on commission_rate_is_matched
                        $class = $row->commission_rate_is_matched ? 'text-success' : 'text-danger';

                        // Format the commission rate
                        $commission_rate = $row->commission_rate ? $row->commission_rate . ' %' : '-';

                        // Format commission_rate_is_value for display in parentheses, or fallback to '-'
                        $commission_rate_is_value = $row->commission_rate_is_value
                            ? '(' . indianNumberFormat($row->commission_rate_is_value) . ')'
                            : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $commission_rate .
                            '<p class="id-desc">' . $commission_rate_is_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('status', function ($row) {
                        $status = $row->status ?? '-';
                        $statusClass = '';

                        $settlementStatus = Settlement::where('application_id', $row->id)->first();

                        if ($settlementStatus) {
                            switch ($settlementStatus->status) {
                                case 'checker':
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                    break;
                                case 'bankPending':
                                    $statusClass = 'in-progress';
                                    $statusText = 'In progress';
                                    break;
                                case 'pending':
                                    $statusClass = 'in-progress';
                                    $statusText = 'In progress';
                                    break;
                                case 'completed':
                                    $statusClass = 'completed';
                                    $statusText = 'Completed';
                                    break;
                                case 'rejected':
                                    $statusClass = 'rejected';
                                    $statusText = 'Rejected';
                                    break;
                                case '-':
                                default:
                                    $statusClass = strtolower($status);
                                    break;
                            }
                        } else {
                            switch ($status) {
                                case 'in-progress':
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                    break;
                                case 'completed':
                                    $statusClass = 'completed';
                                    $statusText = 'Case Matched';
                                    break;
                                case 'pending':
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                    break;
                                case 'rejected':
                                    $statusClass = 'rejected';
                                    $statusText = 'Rejected';
                                    break;
                                case '-':
                                default:
                                    $statusClass = strtolower($status);
                                    break;
                            }
                        }



                        return '<button class="status-buttons ' . $statusClass . '">' . $statusText . '</button>';
                    })
                    ->editColumn('remark', function ($row) {
                        return '<div class="table-row ">' .
                            $row->remark ?? '-' .
                            '</div>';
                    })
                    ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('application', 'view')) {
                            $btn .= "<a href='" . e(url('/application/view/' . $row->id)) . "'>
                                        <img src='" . asset('assets/images/eye-icon.svg') . "' alt='View'>
                                     </a>";
                        }

                        if (auth()->user()->hasPermission('application', 'update') && in_array($row->status, ['pending', 'in-progress'])) {
                            $btn .= "<a href='" . e(url('/application/update/' . $row->id)) . "'>
                                        <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                                     </a>";
                        }

                        if (auth()->user()->hasPermission('application', 'delete') && in_array($row->status, ['pending', 'in-progress'])) {
                            $btn .= "<img class='delete-btn' data-application-id='" . e($row->id) . "' 
                                      src='" . asset('assets/images/delete-icon.svg') . "' alt='Delete'>";
                        }

                        return $btn;
                    })
                    ->rawColumns(['checkbox', 'app_id', 'customer_name', 'bank_id', 'product_id', 'disburse_amount', 'commission_rate', 'status', 'remark', 'action'])
                    ->make(true);
            } else {
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('checkbox', function ($row) {
                        if ($row->status !== 'completed') {
                            return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                        }
                        return '';
                    })
                    ->editColumn('user_id', function ($row) {
                        return $row->user ? $row->user->first_name . ' ' . $row->user->last_name : '-';
                    })
                    ->editColumn('app_id', function ($row) {
                        // Determine CSS class based on app_id_is_matched
                        $class = $row->app_id_is_matched ? 'text-success' : 'text-danger';

                        // Prepare app_id_is_value for display
                        $app_id_value = $row->app_id_is_value ? '(' . $row->app_id_is_value . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $row->app_id .
                            '<p class="id-desc">' . $app_id_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('customer_name', function ($row) {
                        // Determine CSS class based on customer_name_is_matched
                        $class = $row->customer_name_is_matched ? 'text-success' : 'text-danger';

                        // Prepare customer_name_is_value for display
                        $customer_name_value = $row->customer_name_is_value ? '(' . $row->customer_name_is_value . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $row->customer_name .
                            '<p class="id-desc">' . $customer_name_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('bank_id', function ($row) {
                        // Determine CSS class based on bank_id_is_matched
                        $class = $row->bank_id_is_matched ? 'text-success' : 'text-danger';

                        // Get the bank name (or fallback to '-')
                        $bank_name = $row->bank ? $row->bank->name : '-';

                        // Prepare bank_sec_name for display
                        $bank_sec_name = $row->bank_sec_name ? '(' . $row->bank_sec_name . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $bank_name .
                            '<p class="id-desc">' . $bank_sec_name . '</p>' .
                            '</div>';
                    })
                    ->editColumn('product_id', function ($row) {
                        // Determine CSS class based on product_id_is_matched
                        $class = $row->product_id_is_matched ? 'text-success' : 'text-danger';

                        // Get the product name (or fallback to '-')
                        $product_name = $row->product ? $row->product->name : '-';

                        // Prepare product_sec_name for display
                        $product_sec_name = $row->product_sec_name ? '(' . $row->product_sec_name . ')' : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $product_name .
                            '<p class="id-desc">' . $product_sec_name . '</p>' .
                            '</div>';
                    })
                    ->editColumn('disburse_amount', function ($row) {
                        // Determine CSS class based on disburse_amount_is_matched
                        $class = $row->disburse_amount_is_matched ? 'text-success' : 'text-danger';

                        // Format the disburse amount using indianNumberFormat helper function
                        $disburse_amount = $row->disburse_amount ? '₹ ' . indianNumberFormat($row->disburse_amount) : '-';

                        // Format disburse_amount_is_value for display in parentheses, or fallback to '-'
                        $disburse_amount_is_value = $row->disburse_amount_is_value
                            ? '(' . indianNumberFormat($row->disburse_amount_is_value) . ')'
                            : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $disburse_amount .
                            '<p class="id-desc">' . $disburse_amount_is_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('commission_rate', function ($row) {
                        // Determine CSS class based on commission_rate_is_matched
                        $class = $row->commission_rate_is_matched ? 'text-success' : 'text-danger';

                        // Format the commission rate
                        $commission_rate = $row->commission_rate ? $row->commission_rate . ' %' : '-';

                        // Format commission_rate_is_value for display in parentheses, or fallback to '-'
                        $commission_rate_is_value = $row->commission_rate_is_value
                            ? '(' . indianNumberFormat($row->commission_rate_is_value) . ')'
                            : '-';

                        // Return the HTML structure
                        return '<div class="row-color table-row ' . $class . '">' .
                            $commission_rate .
                            '<p class="id-desc">' . $commission_rate_is_value . '</p>' .
                            '</div>';
                    })
                    ->editColumn('status', function ($row) {
                        $status = $row->status ?? '-';
                        $statusClass = $status == 'in-progress' ? 'inprogress' : strtolower($status);
                        $statusText = str_replace('-', ' ', $status);
                        $statusText = ucwords($statusText);
                        return '<button class="status-buttons ' . $statusClass . '">' . $statusText . '</button>';
                    })
                    ->editColumn('channel_status', function ($row) {

                        return '<button class="status-buttons completed"> Case matched </button>';
                    })
                    ->editColumn('remark', function ($row) {
                        $remarks = RemarkStatus::where('status', 1)->get();


                        $select = '<select class="form-control remark-dropdown" data-id="' . $row->id . '" '
                            . ($row->status == "completed" ? 'disabled' : '') . '>
                            <option value="" selected disabled>Select Remark</option>';
                        foreach ($remarks as $label) {
                            $selected = $row->remark == $label->title ? 'selected' : '';
                            $select .= '<option value="' . $label->title . '" ' . $selected . '>' . $label->title . '</option>';
                        }
                        $select .= '</select>';

                        return $select;
                    })

                    ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('application', 'view')) {
                            $btn .= "<a href='" . e(url('/application/view/' . $row->id)) . "'>
                                        <img src='" . asset('assets/images/eye-icon.svg') . "' alt='View'>
                                     </a>";
                        }

                        if (auth()->user()->hasPermission('application', 'update') && in_array($row->status, ['pending', 'in-progress', 'rejected'])) {
                            $btn .= "<a href='" . e(url('/application/update/' . $row->id)) . "'>
                                        <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                                     </a>";
                        }

                        if (auth()->user()->hasPermission('application', 'delete') && in_array($row->status, ['pending', 'in-progress', 'rejected'])) {
                            $btn .= "<img class='delete-btn' data-application-id='" . e($row->id) . "' 
                                      src='" . asset('assets/images/delete-icon.svg') . "' alt='Delete'>";
                        }

                        return $btn;
                    })
                    ->rawColumns(['checkbox', 'app_id', 'customer_name', 'bank_id', 'product_id', 'disburse_amount', 'commission_rate', 'status', 'remark', 'action'])
                    ->make(true);
            }
        }
        return view('Frontend.Application.index', compact('Route', 'applications', 'sales', 'channels', 'users', 'user', 'bank', 'product'));
    }

    private function sortData($date, $date_range, $query)
    {
        if ($date) {
            $now = Carbon::now();
            if ($date == 'today') {
                $today = Carbon::today()->toDateString();
                $query = $query->whereDate('created_at', $today);
            } elseif ($date == 'yesterday') {
                $yesterday = Carbon::yesterday()->toDateString();
                $query = $query->whereDate('created_at', $yesterday);
            } elseif ($date == 'this_week') {
                $weekStartDate = $now->startOfWeek()->toDateString();
                $weekEndDate = $now->endOfWeek()->toDateString();
                $query = $query->whereDate('created_at', '>=', $weekStartDate)
                    ->whereDate('created_at', '<=', $weekEndDate);
            } elseif ($date == 'last_week') {
                $subWeek = $now->subWeek();
                $lastWeekStartDate = $subWeek->startOfWeek()->toDateString();
                $lastWeekEndDate = $subWeek->endOfWeek()->toDateString();
                $query = $query->whereDate('created_at', '>=', $lastWeekStartDate)
                    ->whereDate('created_at', '<=', $lastWeekEndDate);
            } elseif ($date == 'this_month') {
                $startOfMonth = $now->startOfMonth()->toDateString();
                $endOfMonth = $now->endOfMonth()->toDateString();
                $query = $query->whereDate('created_at', '>=', $startOfMonth)
                    ->whereDate('created_at', '<=', $endOfMonth);
            } elseif ($date == 'last_month') {
                $subMonth = $now->subMonth();
                $startOfMonth = $subMonth->startOfMonth()->toDateString();
                $endOfMonth = $subMonth->endOfMonth()->toDateString();
                $query = $query->whereDate('created_at', '>=', $startOfMonth)
                    ->whereDate('created_at', '<=', $endOfMonth);
            } elseif ($date == 'last_3_months') {
                $thirdLastMonthStart = $now->subMonths(2)->startOfMonth()->toDateString();
                $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                $query = $query->whereDate('created_at', '>=', $thirdLastMonthStart)
                    ->whereDate('created_at', '<=', $lastOneMonthEnd);
            } elseif ($date == 'last_6_months') {
                $Last6thMonthStart = $now->subMonths(5)->startOfMonth()->toDateString();
                $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                $query = $query->whereDate('created_at', '>=', $Last6thMonthStart)
                    ->whereDate('created_at', '<=', $lastOneMonthEnd);
            } elseif ($date == 'this_year') {
                $thisYearStart = $now->startOfYear()->toDateString();
                $thisYearEnd = $now->endOfYear()->toDateString();
                $query = $query->whereDate('created_at', '>=', $thisYearStart)
                    ->whereDate('created_at', '<=', $thisYearEnd);
            } elseif ($date == 'last_year') {
                $lastYear = $now->subYear();
                $lastYearStart = $lastYear->startOfYear()->toDateString();
                $lastYearEnd = $lastYear->endOfYear()->toDateString();
                $query = $query->whereDate('created_at', '>=', $lastYearStart)
                    ->whereDate('created_at', '<=', $lastYearEnd);
            } elseif ($date == 'custom' && isset($date_range)) {
                if (strpos($date_range, 'to') !== false) {
                    $dates = explode('to', $date_range);
                    $startDate = trim($dates[0]);
                    $endDate = trim($dates[1]);
                    $query = $query->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                } else {
                    throw new \Exception('Date range is not provided or is incorrectly formatted.');
                }
            }
        }

        return $query;
    }

    public function add()
    {
        $Route = 'Application';
        $user = Auth::user();
        $banks = Bank::get();
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

        return view('Frontend.Application.create', compact('Route', 'channels', 'sales', 'banks', 'states'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            // Validate the form data
            $validatedData = $request->validate([
                'app_id' => 'required',
                'disbursement_date' => 'required|date',
                'case_location' => 'nullable|string|max:255',
                'case_state' => 'nullable|string|max:255',
                'customer_name' => 'required|string|max:255',
                'bank_id' => 'required|string|max:255',
                'product_id' => 'required|string|max:255',
                'group' => 'required|string|max:255',
            ]);

            // Create a new Application instance with the validated data
            if ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
                $user_id = $user->id;
            } else {
                $user_id = $request->channel_sales_id;
            }
            $application = new Application();
            $application->user_id = $user_id;
            $application->app_id = $request->app_id;
            $application->disbursement_date = date('Y-m-d', strtotime($request->disbursement_date));
            $application->case_location = $request->case_location;
            $application->case_state = $request->case_state;
            $application->customer_name = $request->customer_name;
            $application->customer_firm_name = $request->firm_name;
            $application->bank_id = $request->bank_id;
            $application->product_id = $request->product_id;
            $application->group = $request->group;
            $application->commission_rate = $request->commission_rate;
            if ($request->group == 'Secured') {
                $application->fresh_or_bt = $request->fresh_bt;
                $application->any_subvention = $request->any_subvention;
            } else {
                $application->otc_or_pdd_status = $request->otc_pdd;
                $application->pf_taken = $request->pf_taken;
            }
            $application->disburse_amount = $request->disburse_amount;
            $application->banker_name = $request->banker_name;
            $application->banker_number = $request->banker_number;
            $application->banker_email = $request->banker_email;
            $application->created_by = Auth::id();
            // Save the application to the database
            $application->save();
            return redirect()->to('/application')->with('success', 'Application created successfully.');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
    }

    public function show($id)
    {
        $Route = 'Edit Application';
        // Retrieve the staff member by ID
        $application = Application::findOrFail($id);
        $user = Auth::user();
        $banks = Bank::get();
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
        $districts = [];
        // $products = BankProduct::where(['bank_id' => $application->bank_id, 'group' => $application->group])->get();
        $productIdsArray = BankProduct::where('bank_id', $application->bank_id)
            ->pluck('product_id')
            ->toArray();

        $products = Product::whereIn('id', $productIdsArray)->where('group', $application->group)->get();

        foreach ($states as $state) {
            if ($state['state_code'] === $application->case_state) {
                $districts = $state['districts'];
            }
        }
        // Pass the staff member data to the edit view
        return view('Frontend.Application.show', compact(
            'Route',
            'application',
            'channels',
            'sales',
            'banks',
            'states',
            'districts',
            'products'
        ));
    }

    public function edit($id)
    {
        $Route = 'Edit Application';
        // Retrieve the staff member by ID
        $application = Application::findOrFail($id);
        $user = Auth::user();
        $banks = Bank::get();
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
        $districts = [];
        $productIdsArray = BankProduct::where('bank_id', $application->bank_id)
            ->pluck('product_id')
            ->toArray();

        $bankProducts = Product::whereIn('id', $productIdsArray)->where('group', $application->group)->get();

        foreach ($states as $state) {
            if ($state['state_code'] === $application->case_state) {
                $districts = $state['districts'];
            }
        }
        // Pass the staff member data to the edit view
        return view('Frontend.Application.edit', compact(
            'Route',
            'application',
            'channels',
            'sales',
            'banks',
            'states',
            'districts',
            'bankProducts'
        ));
    }

    public function update(Request $request, $id)
    {
        // Validate the form data
        $user = Auth::user();

        // Find the application by ID
        $application = Application::findOrFail($id);
        if ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $application->user_id = $user->id;
        } else {
            $application->user_id = $request->channel_sales_id;
        }
        // Update the application with the validated data
        $application->app_id = $request->app_id;
        $application->disbursement_date = date('Y-m-d', strtotime($request->disbursement_date));
        $application->case_location = $request->case_location;
        $application->case_state = $request->case_state;
        $application->customer_name = $request->customer_name;
        $application->customer_firm_name = $request->firm_name;
        $application->bank_id = $request->bank_id;
        $application->product_id = $request->product_id;
        $application->group = $request->group;
        $application->remark = '';
        $application->commission_rate = $request->commission_rate;
        if ($request->status) {
            $application->status = $request->status;
        }
        if ($request->group == 'Secured') {
            $application->fresh_or_bt = $request->fresh_bt;
            $application->any_subvention = $request->any_subvention;
        } else {
            $application->otc_or_pdd_status = $request->otc_pdd;
            $application->pf_taken = $request->pf_taken;
        }
        $application->disburse_amount = $request->disburse_amount;
        $application->banker_name = $request->banker_name;
        $application->banker_number = $request->banker_number;
        $application->banker_email = $request->banker_email;

        // Save the updated application to the database
        $application->save();
        if ($request->status == 'completed') {
            ProcessSettlement::dispatch($application);
        } elseif ($request->status != 'rejected') {
            ProcessMISDataJob::dispatch($application->bank_id, $application->product_id, $request->status);
        }

        // Redirect back with a success message
        return redirect()->to('/application')->with('success', 'Application updated successfully.');
    }

    public function destroy(Application $application)
    {
        $application->delete();
        return true;
    }

    public function filter(Request $request)
    {
        $Route = 'Application';
        $user = Auth::user();
        $query = Application::query();

        if ($user->roles[0]->id == 1) {
        } else if ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $query->where('user_id', Auth::id());
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $query->whereIn('user_id', $channel_assign);
        }

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
            $query->where('user_id', $request->user_id);
        }

        $query->orderBy('id', 'desc');
        // Execute the query and fetch results
        $applications = $query->paginate(25);
        return view('Frontend.Application.Table.application_table', compact('Route', 'applications'));
    }

    public function exportApplication()
    {
        return Excel::download(new ApplicationExport(), 'application.xlsx');
    }

    public function uploadView()
    {
        $Route = 'Application';
        $user = Auth::user();
        $role_id = $user->roles[0]->id;
        $user_id = $user->id;
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
        return view('Frontend.Application.uploadMIS', compact('Route', 'channels', 'sales', 'user_id', 'role_id'));
    }

    public function storeExcel(Request $request)
    {
        try {

            // Define the expected headers for validation
            $expectedHeaders = [
                'S.NO',
                'APP ID',
                'DISBURSEMENT DATE',
                'CASE LOCATION',
                'CASE STATE',
                'CUSTOMER NAME',
                'CUSTOMER\'S FIRM NAME',
                'BANK NAME',
                'PRODUCT NAME',
                'GROUP',
                'FRESH/BT',
                'ANY SUBVENTION',
                'DISBURSE AMOUNT',
                'OTC/PDD STATUS',
                'PF TAKEN',
                'COMMISSION RATE',
                'BANKER NAME',
                'BANKER NO.',
                'BANKER EMAIL',
            ];

            $user = Auth::user();
            $createdBy = $user->id;
            $userId = $request->user_id;

            // Read the uploaded Excel file
            $file = $request->file('csv_file');
            $tempFilePath = $file->storeAs('tmp', 'uploaded.xlsx');

            // Parse the Excel file using Spatie SimpleExcel Reader
            $rows = SimpleExcelReader::create(storage_path('app/' . $tempFilePath))
                ->getRows()
                ->toArray();
            // Get the headers of the first row (usually the header)
            $headers = array_keys($rows[0]);

            // Initialize an array to hold any header mismatch errors
            $headerErrors = [];

            // Check each header against the expected headers
            foreach ($expectedHeaders as $index => $expectedHeader) {
                if (!isset($headers[$index]) || $headers[$index] !== $expectedHeader) {
                    // If headers don't match, record the error
                    $headerErrors[] = [
                        'expected' => $expectedHeader,
                        'found' => isset($headers[$index]) ? $headers[$index] : 'N/A',
                        'index' => $index + 1, // Header index (1-based)
                    ];
                }
            }

            // If there are header errors, return a detailed error response
            if (!empty($headerErrors)) {
                return redirect()->back()->withErrors(['error' => 'Header format mismatch'])->withInput();
            }

            // Iterate through each row of the Excel data and insert into the database
            $successCount = 0; // Variable to count successful entries


            foreach ($rows as $row) {
                // Ensure the DISBURSE AMOUNT is not empty before proceeding
                if ($row['DISBURSE AMOUNT'] !== '') {
                    $appId = ($row['APP ID'] == '') ? null : $row['APP ID'];

                    // Check if the app_id exists in the database
                    $existingApplication = null;

                    if (!is_null($appId)) {
                        // Check for an existing record with the same app_id
                        $existingApplication = Application::where('app_id', $appId)->first();
                    }

                    // If app_id exists and FRESH/BT is not 'tranche', skip this row
                    if ($existingApplication && strtolower($row['FRESH/BT']) != 'tranche') {
                        continue;  // Skip the current row
                    }

                    // Proceed to insert if app_id is new, or if FRESH/BT is 'tranche'
                    $bank_id = Bank::where('name', $row['BANK NAME'])->value('id');
                    $bank = Bank::where('name', $row['BANK NAME'])->first();
                    $product_id = Product::where('name', $row['PRODUCT NAME'])->value('id');
                    $group = Product::where('id', $product_id)->value('group');


                    $bank_product = BankProduct::where('bank_id', $bank_id)->where('product_id', $product_id)->first();
                    $application = new Application();
                    // Associate the loan application with the authenticated user
                    $application->user_id = $userId;
                    // Map Excel data to model attributes
                    if ($bank_product) {
                        if ($bank_product->auto_generate_lan) {
                            $application->app_id = strtoupper(Str::slug($bank->short_name) . '_' . strtoupper(Str::slug($row['PRODUCT NAME'])) . '_' . rand(1111111, 9999999));
                        } else {
                            $application->app_id = $row['APP ID'] ?? NULL;
                        }
                    } else {
                        $application->app_id = $row['APP ID'] ?? NULL;
                    }

                    // Convert DateTimeImmutable to a string format
                    $disbursementDate = $row['DISBURSEMENT DATE'];
                    if ($disbursementDate instanceof \DateTimeImmutable) {
                        $application->disbursement_date = $disbursementDate->format('Y-m-d');
                    } else {
                        $application->disbursement_date = null;  // Handle cases where the date is invalid
                    }

                    // Assign the rest of the fields from the Excel data
                    $application->case_location = trim(htmlspecialchars($row['CASE LOCATION']));
                    $application->case_state = trim(htmlspecialchars($row['CASE STATE']));
                    $application->customer_name = trim(htmlspecialchars($row['CUSTOMER NAME']));
                    $application->customer_firm_name = trim(htmlspecialchars($row['CUSTOMER\'S FIRM NAME']));
                    $application->bank_id = $bank_id;
                    $application->product_id = $product_id;
                    $application->group = $group;
                    $application->fresh_or_bt = trim(htmlspecialchars($row['FRESH/BT']));
                    $application->any_subvention = trim(htmlspecialchars($row['ANY SUBVENTION']));
                    $application->disburse_amount = str_replace(",", "", $row['DISBURSE AMOUNT']);
                    $application->otc_or_pdd_status = trim(htmlspecialchars($row['OTC/PDD STATUS']));
                    $application->pf_taken = trim(htmlspecialchars($row['PF TAKEN']));
                    $application->commission_rate = round((float) $row['COMMISSION RATE'], 2);
                    $application->banker_name = trim(htmlspecialchars($row['BANKER NAME']));
                    $application->banker_number = trim(htmlspecialchars($row['BANKER NO.']));
                    $application->banker_email = trim(htmlspecialchars($row['BANKER EMAIL']));
                    $application->created_by = $createdBy;

                    // Save the loan application record to the database
                    $application->save();
                    ProcessMISDataJob::dispatch($bank_id, $product_id);
                    $successCount++;  // Increment the count of successful insertions
                }
            }


            // After the loop, if there were successful entries, create the toast
            if ($successCount > 0) {
                $toastMessage = ($successCount > 1) ? "$successCount applications were" : "One application was";
                $toastMessage .= " uploaded successfully.";
                return redirect()->to('/application')->with('success', $toastMessage);
            } else {
                // If no records were inserted
                return response()->json([
                    'error' => 'No new records added',
                    'message' => 'No new records were added to the database.',
                    'error_code' => 'ERR_NO_NEW_RECORDS'
                ], 200);
            }
        } catch (\Throwable $th) {
            return $th;
            return redirect()->back()->withErrors(['error' => 'Something went wrong with your Excel data'])->withInput();
        }
    }

    public function uploadMISView()
    {
        $Route = 'Application';
        $banks = Bank::get();
        return view('Frontend.Application.Bank-MIS.index', compact('Route', 'banks'));
    }

    public function uploadMIS(Request $request)
    {
        try {
            // Validate that the file is XLSX format
            $request->validate([
                'xlsx_file' => 'required|file|mimes:xlsx',
                'bank_id' => 'required',
                'product_id' => 'required',
            ]);

            $bank_id = $request->bank_id;
            $product_id = $request->product_id;
            $file = $request->file('xlsx_file');
            $tempFilePath = $file->storeAs('tmp', 'uploaded.xlsx');

            $group = Product::where('id', $product_id)->value('group');
            $excel = SimpleExcelReader::create(storage_path('app/' . $tempFilePath));
            $rows = $excel->getRows()->toArray();

            // Fetch the sheet data
            $sheetData = SheetMatching::where(['bank_id' => $bank_id, 'product_id' => $product_id])->first();
            if (!$sheetData) {
                return redirect()->to('/bank_mis')->with('error', 'No header mappings found for the selected bank and product.');
            }

            // Convert sheetData to an array and remove unnecessary fields
            $keysMapping = $sheetData->toArray();
            unset($keysMapping['id'], $keysMapping['bank_id'], $keysMapping['product_id'], $keysMapping['group'], $keysMapping['created_at'], $keysMapping['updated_at']);

            foreach ($rows as $row) {
                if ($row) {
                    // Extract values based on mapped keys
                    $data = [
                        'bank_id' => $bank_id,
                        'product_id' => $product_id,
                    ];
                    foreach ($keysMapping as $excelKey => $dataKey) {
                        if (isset($row[$dataKey])) {
                            $data[$excelKey] = $row[$dataKey];
                        }
                    }

                    // Calculate 'payout_rate' and 'payout_amount' if not provided
                    if (!isset($data['payout_rate'])) {
                        if (isset($data['payout_amount'], $data['disbAmount'])) {
                            $payoutAmount = (float) $data['payout_amount'];
                            $disbursementAmount = (float) $data['disbAmount'];

                            // Fetch rate from database or calculate
                            $rate = BankPayout::where('bank_id', $data['bank_id'])
                                ->where('product_id', $data['product_id'])
                                ->value('rate');
                            $data['payout_rate'] = $rate ?: ($disbursementAmount != 0 ? $payoutAmount / $disbursementAmount : 0);
                        }
                    }

                    if (!isset($data['payout_amount'])) {
                        if (isset($data['payout_rate'], $data['disbAmount'])) {
                            $disbursementAmount = (float) $data['disbAmount'];
                            $data['payout_amount'] = $disbursementAmount * ((float) $data['payout_rate'] / 100);
                        }
                    }

                    // Check if the record already exists based on all relevant fields
                    $existingMIS = BankMIS::where('bank_id', $data['bank_id'])
                        ->where('product_id', $data['product_id'])
                        ->where('app_id', $data['app_id'] ?? NULL)
                        //    ->where('payout_rate', $data['payout_rate'] ?? NULL)
                        ->where('location', $data['location'] ?? NULL)
                        //    ->where('payout_amount', $data['payout_amount'] ?? NULL)
                        ->where('customer_firm_name', $data['customer_firm_name'] ?? NULL)
                        ->where('pf', $data['pf'] ?? NULL)
                        ->where('subvention', $data['subvention'] ?? NULL)
                        ->where('roi', $data['roi'] ?? NULL)
                        ->where('insurance', $data['insurance'] ?? NULL)
                        ->where('group', $group ?? NULL)
                        ->where('customer_name', $data['customer_name'] ?? NULL)
                        ->where('disbAmount', $data['disbAmount'] ?? NULL)
                        ->where('case_location', $data['case_location'] ?? NULL)
                        ->where('otc_pdd_status', $data['otc_pdd_status'] ?? NULL)
                        ->first();

                    // If the record exists, skip inserting it
                    if ($existingMIS) {
                        continue;  // Skip this row if it already exists
                    }

                    // Insert the data if it doesn't already exist
                    $bank = new BankMIS();
                    $bank->bank_id = $data['bank_id'];
                    $bank->product_id = $data['product_id'];
                    $bank->app_id = isset($data['app_id']) ? $data['app_id'] : NULL;
                    $bank->payout_rate = ($data['payout_rate'] != '') ? round(floatval($data['payout_rate']), 2) : NULL;
                    $bank->location = isset($data['location']) ? $data['location'] : NULL;
                    $bank->payout_amount = isset($data['payout_amount']) ? floatval($data['payout_amount']) : NULL;
                    $bank->customer_firm_name = isset($data['customer_firm_name']) ? $data['customer_firm_name'] : NULL;
                    $bank->pf = isset($data['pf']) ? $data['pf'] : NULL;
                    $bank->subvention = isset($data['subvention']) ? $data['subvention'] : NULL;
                    $bank->roi = isset($data['roi']) ? $data['roi'] : NULL;
                    $bank->insurance = isset($data['insurance']) ? $data['insurance'] : NULL;
                    $bank->group = isset($group) ? $group : NULL;
                    $bank->customer_name = isset($data['customer_name']) ? $data['customer_name'] : NULL;
                    $bank->disbAmount = isset($data['disbAmount']) ? $data['disbAmount'] : NULL;
                    $bank->case_location = isset($data['case_location']) ? $data['case_location'] : NULL;
                    $bank->otc_pdd_status = isset($data['otc_pdd_status']) ? $data['otc_pdd_status'] : NULL;
                    $bank->save();

                    // Update the group field in the second save
                    $bank->group = $group;
                    $bank->save();
                }
            }

            // Dispatch Job for processing
            ProcessMISDataJob::dispatch($bank_id, $product_id);

            return redirect()->to('/bank_mis')->with('success', 'File uploaded successfully. Data processing will continue in the background.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong with your Excel data'])->withInput();
        }
    }

    public function bulkDelete(Request $request)
    {
        $applicationIds = $request->application_ids;

        if (!$applicationIds || !is_array($applicationIds)) {
            return response()->json(['message' => 'No applications selected.'], 400);
        }

        // Delete applications that are not completed
        $deletedCount = Application::whereIn('id', $applicationIds)
            ->where('status', '!=', 'completed')
            ->delete();

        return response()->json(['message' => $deletedCount . ' applications deleted successfully.'], 200);
    }

    public function updateRemark(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'remark' => 'required',
        ]);

        $application = Application::findOrFail($request->id);
        $application->remark = $request->remark;
        $application->save();

        return response()->json(['success' => true, 'message' => 'Remark updated successfully']);
    }
}
