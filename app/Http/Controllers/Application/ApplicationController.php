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
use App\Models\ChannelUser;
use App\Models\Product;
use App\Models\RemarkStatus;
use App\Models\Settlement;
use App\Models\SheetMatching;
use App\Models\StaffAssign;
use App\Models\User;
use App\Notifications\NewApplicationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\SimpleExcel\SimpleExcelReader;
use Yajra\DataTables\Facades\DataTables;
use App\Models\BankMisTracker;
use App\Models\ApplicationActivityLog;


class ApplicationController extends Controller
{
    private function refreshMisMatchForApplication(Application $application): void
    {
        $bankProduct = BankProduct::where('bank_id', $application->bank_id)
            ->where('product_id', $application->product_id)
            ->first();

        if (!$bankProduct) {
            return;
        }

        $misRecord = null;
        if (!empty($application->bank_mis_id)) {
            $misRecord = BankMIS::where('id', $application->bank_mis_id)
                ->where('bank_id', $application->bank_id)
                ->where('product_id', $application->product_id)
                ->first();
        }

        if (!$misRecord) {
            if ($bankProduct->auto_generate_lan) {
                $misRecord = BankMIS::where('bank_id', $application->bank_id)
                    ->where('product_id', $application->product_id)
                    ->where('customer_name', $application->customer_name)
                    ->latest('id')
                    ->first();
            } else {
                $misRecord = BankMIS::where('bank_id', $application->bank_id)
                    ->where('product_id', $application->product_id)
                    ->where('app_id', $application->app_id)
                    ->latest('id')
                    ->first();
            }
        }

        if (!$misRecord) {
            if (!empty($application->bank_mis_id)) {
                $application->update(['bank_mis_id' => null]);
            }
            return;
        }

        $updateData = [
            'app_id_is_matched' => checkValueAndSetFlag($application, 'app_id', $misRecord->app_id),
            'case_location_is_matched' => checkValueAndSetFlag($application, 'case_location', $misRecord->case_location),
            'customer_name_is_matched' => checkValueAndSetFlag($application, 'customer_name', $misRecord->customer_name),
            'bank_id_is_matched' => checkValueAndSetFlag($application, 'bank_id', $misRecord->bank_id),
            'product_id_is_matched' => checkValueAndSetFlag($application, 'product_id', $misRecord->product_id),
            'group_is_matched' => checkValueAndSetFlag($application, 'group', $misRecord->group),
            'disburse_amount_is_matched' => checkValueAndSetFlag($application, 'disburse_amount', floatval($misRecord->disbAmount ?? 0)),
            'commission_rate_is_matched' => checkValueAndSetFlag($application, 'commission_rate', floatval($misRecord->payout_rate ?? 0)),
            'updated_at' => Carbon::now(),
            'bank_mis_id' => $misRecord->id,
        ];

        if ($bankProduct->auto_generate_lan) {
            BankMIS::where('id', $misRecord->id)->update(['app_id' => $application->app_id]);
            DB::table('applications')->where('id', $application->id)->update([
                'app_id_is_value' => $misRecord->app_id
            ]);
            $updateData['app_id_is_matched'] = 1;
        }

        $application->update($updateData);
    }

    public function index(Request $request)
    {
        $Route = 'Application';
        $user = Auth::user();
        $channelroleId = 2;
        $salesroleId = 3;
        $applications = Application::all();
        $associateChannelRoleId = 37;
        $users = User::with('roles:id')->whereHas('roles', function ($query) use ($channelroleId, $associateChannelRoleId) {
            $query->whereIn('id', [$channelroleId, $associateChannelRoleId]);
        })->get();
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


            $query = Application::with(['bank', 'product', 'user.roles', 'parentChannel'])->orderBy('id', 'desc');
            $formatPartnerName = function ($partner) {
                if (!$partner) {
                    return '-';
                }

                $identifier = $partner->Emp_Id ?: $partner->id;

                return trim($partner->first_name . ' ' . $partner->last_name) . ' (' . $identifier . ')';
            };
            $formatParentName = function ($parentChannel) {
                if (!$parentChannel) {
                    return '-';
                }

                $identifier = $parentChannel->Emp_Id ?: $parentChannel->id;
                return trim($parentChannel->first_name . ' ' . $parentChannel->last_name) . ' (' . $identifier . ')';
            };

            if($user->roles[0]->name == 'Channel' || $user->roles[0]->name == 'Associate_Channel') {
                
                if($user->roles[0]->name == 'Channel') {
                     
                    $channel_assign = ChannelUser::where('channel_id', Auth::id())->pluck('associate_channel_id');

                    // If there are channel assignments, show records where:
                    // 1. user_id is either the logged in user OR assigned associate channels
                    // 2. OR parent_channel_id matches the logged-in user (cases uploaded by associates)
                    if ($channel_assign->isNotEmpty()) {
                        $query->where(function ($q) use ($channel_assign, $user) {
                            $q->where(function ($subQ) use ($channel_assign, $user) {
                                $subQ->whereIn('user_id', $channel_assign)
                                    ->orWhere('user_id', $user->id);
                            })
                            ->orWhere('parent_channel_id', $user->id);
                        });
                    } else {
                        $query->where(function ($q) use ($user) {
                            $q->where('user_id', $user->id)
                                ->orWhere('parent_channel_id', $user->id);
                        });
                    }
                } else {

                    $query->where('user_id', Auth::id());
                }
            }

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


            if ($user->roles[0]->id == 2 || $user->roles[0]->id == 3 || $user->roles[0]->id == 35 || $user->roles[0]->id == 36 || $user->roles[0]->id == 37) {
                if($user->roles[0]->id == 36) {
                    $query->where('status', 'approved');
                }

                if($user->roles[0]->id == 35) {
                    $query->where('status', 'pending');
                }
                
                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('checkbox', function ($row) {
                        if ($row->status !== 'completed') {
                            return '<input type="checkbox" class="rowCheckbox" value="' . $row->id . '">';
                        }
                        return '';
                    })
                    ->editColumn('user_id', function ($row) use ($formatPartnerName) {
                        return $formatPartnerName($row->user);
                    })
                    ->addColumn('parent_name', function ($row) use ($formatParentName) {
                        if (!$row->parentChannel || !$row->user) {
                            return '-';
                        }
                        $isAssociate = $row->user->roles && $row->user->roles->contains('id', 37);
                        return $isAssociate ? $formatParentName($row->parentChannel) : '-';
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

                        $statusForUser = 'Pending';

                        if(auth()->user()->roles[0]->id == 36 || auth()->user()->roles[0]->id == 35){
                            if ( $status === 'approved') {
                                $statusForUser = 'Approved By Maker';
                            }
                        }

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
                                case 'approved':
                                    $statusClass = ($statusForUser =='Pending') ? 'pending' : 'in-progress';
                                    $statusText = $statusForUser;
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
                                    $statusText = 'completed';
                                    break;
                                case 'pending':
                                    $statusClass = 'pending';
                                    $statusText = 'Pending';
                                    break;
                                case 'rejected':
                                    $statusClass = 'rejected';
                                    $statusText = 'Rejected';
                                    break;
                                case 'approved':
                                    $statusClass = ($statusForUser =='Pending') ? 'pending' : 'in-progress';
                                    $statusText = $statusForUser;
                                        break;
                                case '-':
                                default:
                                    $statusClass = strtolower($status);
                                    break;
                            }
                        }



                        return '<button class="status-buttons ' . $statusClass . '">' . $statusText . '</button>';
                    })
                    // ->editColumn('remark', function ($row) {
                    //     return '<div class="table-row ">' .
                    //         $row->remark ?? '-' .
                    //         '</div>';
                    // })
                    ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('application', 'view')) {
                            $btn .= "<a href='" . e(url('/application/view/' . $row->id)) . "'>
                                        <img src='" . asset('assets/images/eye-icon.svg') . "' alt='View'>
                                     </a>";
                        }

                        // View Logs button - only for Admin, Maker, Checker
                        $currentRoleId = auth()->user()->roles[0]->id;
                        if (in_array($currentRoleId, [1, 35, 36])) {
                            $btn .= " <a href='javascript:void(0)' onclick='viewLogs(" . $row->id . ")' title='View Logs'><i class='fas fa-history' style='color:#6c757d;font-size:16px;'></i></a>";
                        }
                        if (auth()->user()->hasPermission('application', 'update')) {
                            // Channel/Sales/Associate: only edit when pending
                            if (in_array($currentRoleId, [2, 3, 37]) && $row->status === 'pending') {
                                $btn .= "<a href='" . e(url('/application/update/' . $row->id)) . "'>
                                            <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                                         </a>";
                            }
                            // Maker: edit when pending
                            if ($currentRoleId == 35 && $row->status === 'pending') {
                                $btn .= "<a href='" . e(url('/application/update/' . $row->id)) . "'>
                                            <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                                         </a>";
                            }
                            // Checker: edit when approved
                            if ($currentRoleId == 36 && $row->status === 'approved') {
                                $btn .= "<a href='" . e(url('/application/update/' . $row->id)) . "'>
                                            <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                                         </a>";
                            }
                            // Admin: edit when pending, in-progress, approved
                            if ($currentRoleId == 1 && in_array($row->status, ['pending', 'in-progress', 'approved'])) {
                                $btn .= "<a href='" . e(url('/application/update/' . $row->id)) . "'>
                                            <img src='" . asset('assets/images/Edit.svg') . "' alt='Edit'>
                                         </a>";
                            }
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
                    ->editColumn('user_id', function ($row) use ($formatPartnerName) {
                        return $formatPartnerName($row->user);
                    })
                    ->addColumn('parent_name', function ($row) use ($formatParentName) {
                        if (!$row->parentChannel || !$row->user) {
                            return '-';
                        }
                        $isAssociate = $row->user->roles && $row->user->roles->contains('id', 37);
                        return $isAssociate ? $formatParentName($row->parentChannel) : '-';
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
                        $statusClass = ($status == 'in-progress' || $status == 'approved') ? 'inprogress' : strtolower($status);
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

                        // View Logs button - only for Admin, Maker, Checker
                        $logRoleId = auth()->user()->roles[0]->id;
                        if (in_array($logRoleId, [1, 35, 36])) {
                            $btn .= " <a href='javascript:void(0)' onclick='viewLogs(" . $row->id . ")' title='View Logs'><i class='fas fa-history' style='color:#6c757d;font-size:16px;'></i></a>";
                        }

                        if (auth()->user()->hasPermission('application', 'update') && in_array($row->status, ['pending', 'in-progress', 'rejected'])) {
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
        $effectiveRoleId = $this->getEffectiveRoleId($user);
        $banks = Bank::get();
        $channelroleId = 2;
        $salesroleId = 3;
        $associateChannelRoleId = 37;

        if ($this->userHasAnyRole($user, [1, 35, 36])) {
            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->where('status', 1)->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } elseif ($this->userHasAnyRole($user, [2, 3, $associateChannelRoleId])) {
            $channels = User::where('id', $user->id)->whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->where('status', 1)->get();

            $sales = User::where('id', $user->id)->whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            // Ensure $channel_assign is an array
            if (empty($channel_assign) || !is_array($channel_assign)) {
                $channels = collect();
                $sales = collect();
            } else {
                $channels = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($channelroleId) {
                    $query->where('id', $channelroleId);
                })->get();

                $sales = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($salesroleId) {
                    $query->where('id', $salesroleId);
                })->get();
            }
        }

        $states = getState();


        return view('Frontend.Application.create', compact('Route', 'channels', 'sales', 'banks', 'states', 'effectiveRoleId'));
    }

    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $parsedDisbursementDate = $this->parseDisbursementDate($request->disbursement_date);
            // Validate the form data
            $validatedData = $request->validate([
                'app_id' => 'required',
                'disbursement_date' => 'required',
                'case_location' => 'nullable|string|max:255',
                'case_state' => 'nullable|string|max:255',
                'customer_name' => 'required|string|max:255',
                'customer_phone' => 'nullable|string|max:20',
                'bank_id' => 'required|string|max:255',
                'product_id' => 'required|string|max:255',
                'group' => 'required|string|max:255',
            ]);

            if (!$parsedDisbursementDate) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['disbursement_date' => 'Invalid disbursement date format. Use DD-MM-YYYY.']);
            }

            // Resolve selected target user based on role + selected user type
            $user_id = $this->resolveSelectedApplicationUserId($request, $user);
            $application = new Application();
            $application->user_id = $user_id;
            $application->app_id = $request->app_id;

            // Secured group: allow duplicate base app_id with sequential postfix (-001, -002, etc.)
            if ($request->group == 'Secured') {
                $application->app_id = generateUniqueAppId($request->app_id, Application::class);
            }

            $application->disbursement_date = date('Y-m-d', strtotime($request->disbursement_date));
            $application->case_location = $request->case_location;
            $application->case_state = $request->case_state;
            $application->customer_name = $request->customer_name;
            $application->customer_phone = $request->customer_phone;
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

            // Use submitted sharing_commission when provided, otherwise auto-set from role/channel mapping.
            if ($request->filled('sharing_commission')) {
                $application->sharing_commission = $request->sharing_commission;
            } elseif ($user->roles[0]->id == 37) {
                $parentChannel = ChannelUser::where('associate_channel_id', $user->id)->first();
                if ($parentChannel) {
                    $application->parent_channel_id = $parentChannel->channel_id;
                    $parentUser = User::find($parentChannel->channel_id);
                    $application->sharing_commission = $parentUser->user_commission ?? null;
                }
            } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
                $application->sharing_commission = $user->user_commission ?? null;
            } else {
                $selectedUser = User::find($user_id);
                if ($selectedUser) {
                    $parentChannel = ChannelUser::where('associate_channel_id', $selectedUser->id)->first();
                    if ($parentChannel) {
                        $application->parent_channel_id = $parentChannel->channel_id;
                        $parentUser = User::find($parentChannel->channel_id);
                        $application->sharing_commission = $parentUser->user_commission ?? null;
                    } else {
                        $application->sharing_commission = $selectedUser->user_commission ?? null;
                    }
                }
            }

            // Save the application to the database
            $application->save();

            // Log application creation
            ApplicationActivityLog::create([
                'application_id' => $application->id,
                'user_id' => Auth::id(),
                'action' => 'created',
                'description' => 'Application created with App ID: ' . $application->app_id,
            ]);

            // Send notification to admin users
            $adminUsers = User::whereHas('roles', function ($query) {
                $query->whereIn('id', [1, 35]); // Admin and maker role ID
            })->get();

            foreach ($adminUsers as $adminUser) {
                $adminUser->notify(new NewApplicationNotification($application));
            }
            sleep(2);
            $this->updateBankMisTrackerFromApplications();
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
        $sales = [];
        $channels = [];
        // if ($user->roles[0]->id == 1) {
        //     $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
        //         $query->where('id', $channelroleId);
        //     })->get();

        //     $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
        //         $query->where('id', $salesroleId);
        //     })->get();
        // } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
        //     $channels = User::where('id', $user->id)->whereHas('roles', function ($query) use ($channelroleId) {
        //         $query->where('id', $channelroleId);
        //     })->get();

        //     $sales = User::where('id', $user->id)->whereHas('roles', function ($query) use ($salesroleId) {
        //         $query->where('id', $salesroleId);
        //     })->get();
        // } else {
        //     $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
        //     $channel_assign = json_decode($channel_assign, true);
        //     $channels = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($channelroleId) {
        //         $query->where('id', $channelroleId);
        //     })->get();

        //     $sales = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($salesroleId) {
        //         $query->where('id', $salesroleId);
        //     })->get();
        // }
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

    /**
     * Get activity logs for an application (AJAX endpoint).
     * Visible only to Admin (1), Maker (35), and Checker (36).
     */
    public function getActivityLogs($id)
    {
        $user = Auth::user();
        $roleId = $user->roles[0]->id;

        if (!in_array($roleId, [1, 35, 36])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $logs = ApplicationActivityLog::where('application_id', $id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'description' => $log->description,
                    'changes' => $log->changes,
                    'user_name' => $log->user ? $log->user->first_name . ' ' . $log->user->last_name : 'System',
                    'created_at' => $log->created_at->format('d M Y, h:i A'),
                ];
            });

        return response()->json(['logs' => $logs]);
    }

    public function edit($id)
    {
        $Route = 'Edit Application';
        // Retrieve the staff member by ID
        $application = Application::findOrFail($id);
        $user = Auth::user();
        $effectiveRoleId = $this->getEffectiveRoleId($user);
        $banks = Bank::get();
        $channelroleId = 2;
        $salesroleId = 3;
        if ($this->userHasAnyRole($user, [1, 35, 36])) {
            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } elseif ($this->userHasAnyRole($user, [2, 3])) {
            $channels = User::where('id', $user->id)->whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::where('id', $user->id)->whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } else {
            // $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            // $channel_assign = json_decode($channel_assign, true);
            // $channels = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($channelroleId) {
            //     $query->where('id', $channelroleId);
            // })->get();

            // $sales = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($salesroleId) {
            //     $query->where('id', $salesroleId);
            // })->get();

            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
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

        $selectedUser = User::find($application->user_id);
        $selectedUserType = 'channel';
        if ($selectedUser && $selectedUser->roles()->where('id', 37)->exists()) {
            $selectedUserType = 'associate';
        } elseif ($selectedUser && $selectedUser->roles()->where('id', 3)->exists()) {
            $selectedUserType = 'sales';
        }

        $selectedChannelId = $application->parent_channel_id ?: $application->user_id;

        // Pass the staff member data to the edit view
        return view('Frontend.Application.edit', compact(
            'Route',
            'application',
            'channels',
            'sales',
            'banks',
            'states',
            'districts',
            'bankProducts',
            'effectiveRoleId',
            'selectedUserType',
            'selectedChannelId'
        ));
    }

    public function update(Request $request, $id)
    {
        // Validate the form data
        $user = Auth::user();
        $roleId = (int) ($user->roles[0]->pivot->role_id ?? $user->roles[0]->id ?? 0);

        // Find the application by ID
        $application = Application::findOrFail($id);
        $originalStatus = $application->status;
        $originalValues = $application->getAttributes();
        
        // Channel/Sales/Associate can only edit applications in 'pending' status
        if (in_array($user->roles[0]->pivot->role_id, [2, 3, 37])) {
            if ($application->status !== 'pending') {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['status' => 'You can only edit applications in Pending status.']);
            }
            // These roles cannot change application status
            if ($request->status && $request->status !== $application->status) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['status' => 'You do not have permission to change the application status.']);
            }
        }
        
        // Validate mandatory fields when completing an application
        if ($request->status === 'completed') {
            if (empty($request->commission_rate)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['commission_rate' => 'Commission Rate field cannot be empty when completing an application.']);
            }
            if (empty($request->disburse_amount)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['disburse_amount' => 'Disburse Amount field cannot be empty when completing an application.']);
            }
            if (empty($request->customer_name)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['customer_name' => 'Customer Name field cannot be empty when completing an application.']);
            }
            if (empty($request->bank_id)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['bank_id' => 'Bank field cannot be empty when completing an application.']);
            }
        }

        // Admin/Maker cannot approve until key fields are matched with bank values
        if ($request->status === 'approved' && in_array($roleId, [1, 35], true)) {
            $normalizeNumber = function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }

                $cleanValue = preg_replace('/[^\d.\-]/', '', (string) $value);
                return $cleanValue === '' ? null : (float) $cleanValue;
            };

            $requestAppId = trim((string) ($request->app_id ?? $application->app_id));
            $requestDisburseAmount = $normalizeNumber($request->disburse_amount ?? $application->disburse_amount);

            $isAppIdMatched = !empty($application->app_id_is_value)
                ? strcasecmp($requestAppId, trim((string) $application->app_id_is_value)) === 0
                : (bool) $application->app_id_is_matched;

            $isDisburseMatched = $application->disburse_amount_is_value !== null && $application->disburse_amount_is_value !== ''
                ? $requestDisburseAmount !== null && $requestDisburseAmount == $normalizeNumber($application->disburse_amount_is_value)
                : (bool) $application->disburse_amount_is_matched;

            if (!$isAppIdMatched || !$isDisburseMatched) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors([
                        'status' => 'Approval blocked. Application No and Disburse Amount must match bank fields before approving.'
                    ]);
            }
        }

        // Disbursement amount must remain exactly same as bank value when bank value is present.
        if ($application->disburse_amount_is_value !== null && $application->disburse_amount_is_value !== '' && $request->has('disburse_amount')) {
            $normalizeNumber = function ($value) {
                if ($value === null || $value === '') {
                    return null;
                }
                $cleanValue = preg_replace('/[^\d.\-]/', '', (string) $value);
                return $cleanValue === '' ? null : (float) $cleanValue;
            };

            $requestDisburseAmount = $normalizeNumber($request->disburse_amount);
            $bankDisburseAmount = $normalizeNumber($application->disburse_amount_is_value);
            if ($requestDisburseAmount !== null && $bankDisburseAmount !== null && $requestDisburseAmount != $bankDisburseAmount) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['disburse_amount' => 'Disburse Amount must exactly match bank disbursement amount.']);
            }
        }
        
        // if ($user->roles[0]->id == 2 || $user->roles[0]->id == 3 || $user->roles[0]->id == 35 || $user->roles[0]->id == 36) {
        //     $application->user_id = $user->id;
        // } else {
        //     $application->user_id = $request->channel_sales_id;
        // }
        // Update selected target user (channel/associate/sales) when provided from edit form
        if (
            $roleId != 37 &&
            ($request->filled('user_type') || $request->filled('channel_id') || $request->filled('associate_id') || $request->filled('sales_id') || $request->filled('channel_sales_id'))
        ) {
            $application->user_id = $this->resolveSelectedApplicationUserId($request, $user);
        }

        // Ensure parent_channel_id is set only for associate applications
        $selectedUser = User::find($application->user_id);
        $isAssociateUser = $selectedUser && $selectedUser->roles()->where('id', 37)->exists();
        if ($isAssociateUser) {
            $parentChannel = ChannelUser::where('associate_channel_id', $selectedUser->id)->first();
            $application->parent_channel_id = $parentChannel ? $parentChannel->channel_id : null;
        } else {
            $application->parent_channel_id = null;
        }

        // Update the application with the validated data
        $parsedDisbursementDate = $this->parseDisbursementDate($request->disbursement_date);
        if (!$parsedDisbursementDate) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['disbursement_date' => 'Invalid disbursement date format. Use DD-MM-YYYY.']);
        }

        $application->app_id = $request->app_id;

        // Secured group: if app_id was changed, ensure it doesn't conflict with existing records
        if ($request->group == 'Secured' && $request->app_id !== $application->getOriginal('app_id')) {
            $application->app_id = generateUniqueAppId($request->app_id, Application::class, [], $application->id);
        }

        $application->disbursement_date = date('Y-m-d', strtotime($request->disbursement_date));
        $application->case_location = $request->case_location;
        $application->case_state = $request->case_state;
        $application->customer_name = $request->customer_name;
        $application->customer_phone = $request->customer_phone;
        $application->customer_firm_name = $request->firm_name;
        $application->bank_id = $request->bank_id;
        $application->product_id = $request->product_id;
        $application->group = $request->group;
        $application->remark = '';
        $bankInputCommissionRate = $request->has('commission_rate') ? $request->commission_rate : $application->commission_rate;
        $application->commission_rate = $bankInputCommissionRate;
        
        // Only allow status update if user is not Channel/Sales/Associate
        if ($request->status && !in_array($user->roles[0]->pivot->role_id, [2, 3, 37])) {
            // Checker reject: revert status to 'pending' so Maker can re-review
            if ($user->roles[0]->pivot->role_id == 36 && $request->status === 'rejected') {
                $application->status = 'pending';
            } else {
                $application->status = $request->status;
            }
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

        // Update sharing_commission if provided, otherwise auto-populate from parent's commission rate
        if ($request->filled('sharing_commission')) {
            $application->sharing_commission = $request->sharing_commission;
        } elseif (!$application->sharing_commission) {
            // Auto-populate if not already set
            $selectedUser = User::find($application->user_id);
            if ($selectedUser) {
                $parentChannel = ChannelUser::where('associate_channel_id', $selectedUser->id)->first();
                if ($parentChannel) {
                    $parentUser = User::find($parentChannel->channel_id);
                    $application->sharing_commission = $parentUser->user_commission ?? null;
                } else {
                    $application->sharing_commission = $selectedUser->user_commission ?? null;
                }
            }
        }

        // Update parent_channel_id for associate channels
        if (Auth::user()->roles[0]->pivot->role_id == 37) {
            $parent_channel_id = ChannelUser::where('associate_channel_id', Auth::id())->first();
            if ($parent_channel_id) {
                $application->parent_channel_id = $parent_channel_id->channel_id;
            }
        }
        
        // Save the updated application to the database
        $application->save();
        $this->refreshMisMatchForApplication($application);

        // Log activity: track changes
        $trackedFields = ['app_id', 'customer_name', 'bank_id', 'product_id', 'disburse_amount', 'commission_rate', 'sharing_commission', 'status', 'case_location', 'case_state', 'disbursement_date', 'group'];
        $changes = [];
        foreach ($trackedFields as $field) {
            $oldVal = $originalValues[$field] ?? null;
            $newVal = $application->$field;
            if ($oldVal != $newVal) {
                $changes[$field] = ['old' => $oldVal, 'new' => $newVal];
            }
        }

        $newStatus = $application->status;
        if ($originalStatus !== $newStatus) {
            // Determine action type based on who changed and what the new status is
            if ($user->roles[0]->pivot->role_id == 35 && $newStatus === 'approved') {
                $action = 'approved';
                $description = 'Application approved by Maker.';
            } elseif ($user->roles[0]->pivot->role_id == 35 && $request->status === 'rejected') {
                $action = 'rejected';
                $description = 'Application rejected by Maker.';
            } elseif ($user->roles[0]->pivot->role_id == 36 && $newStatus === 'completed') {
                $action = 'completed';
                $description = 'Application marked as completed by Checker.';
            } elseif ($user->roles[0]->pivot->role_id == 36 && $request->status === 'rejected') {
                $action = 'checker_rejected';
                $rejectionReason = $request->rejection_reason ?? 'No reason provided';
                $description = 'Application rejected by Checker. Reason: ' . $rejectionReason . '. Status reverted to Pending.';
            } else {
                $action = 'status_changed';
                $description = 'Status changed from ' . ucfirst($originalStatus) . ' to ' . ucfirst($newStatus) . '.';
            }

            ApplicationActivityLog::create([
                'application_id' => $application->id,
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $description,
                'changes' => !empty($changes) ? $changes : null,
            ]);
        } elseif (!empty($changes)) {
            ApplicationActivityLog::create([
                'application_id' => $application->id,
                'user_id' => Auth::id(),
                'action' => 'updated',
                'description' => 'Application details updated.',
                'changes' => $changes,
            ]);
        }

        // Handle post-save actions based on status
        if ($request->status == 'completed') {
            ProcessSettlement::dispatch($application);
        } elseif ($user->roles[0]->pivot->role_id == 36 && $request->status === 'rejected') {
            // Checker rejected: notify all Makers
            $makerRoleId = 35;
            $makers = \App\Models\User::whereHas('roles', function ($q) use ($makerRoleId) {
                $q->where('id', $makerRoleId);
            })->get();

            $rejectionReason = $request->rejection_reason ?? 'No reason provided';
            foreach ($makers as $maker) {
                $maker->notify(new \App\Notifications\CheckerRejectedApplicationNotification($application, $rejectionReason));
            }
        } elseif ($request->status != 'rejected') {
            // Send notification to all checkers when application is approved by the maker
            if ($request->status == 'approved') {
                $checkerRoleId = 36;
                $checkers = \App\Models\User::whereHas('roles', function ($q) use ($checkerRoleId) {
                    $q->where('id', $checkerRoleId);
                })->get();

                $message = 'Application ' . $application->app_id . ' has been approved by the maker.';

                foreach ($checkers as $checker) {
                    $checker->notify(new \App\Notifications\UpdateApplicationNotificaion($application, $message));
                }
            }
            ProcessMISDataJob::dispatch($application->bank_id, $application->product_id, $request->status);
        } 

        // Redirect back with a success message
        return redirect()->to('/application')->with('success', 'Application updated successfully.');
    }

    public function destroy(Application $application)
    {
        // Log deletion before deleting
        ApplicationActivityLog::create([
            'application_id' => $application->id,
            'user_id' => Auth::id(),
            'action' => 'deleted',
            'description' => 'Application deleted. App ID: ' . $application->app_id,
        ]);

        $application->delete();
        sleep(2);
        $this->updateBankMisTrackerFromApplications();
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
        $role_id = $this->getEffectiveRoleId($user);
        $user_id = $user->id;
        $channelroleId = 2;
        $salesroleId = 3;
        $associateChannelRoleId = 37;
        
        if ($this->userHasAnyRole($user, [1, 35, 36])) {
            $channels = User::whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->where('status', 1)->get();

            $sales = User::whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } elseif ($this->userHasAnyRole($user, [2, 3])) {
            $channels = User::where('id', $user->id)->whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->where('status', 1)->get();

            $sales = User::where('id', $user->id)->whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } elseif ($this->userHasAnyRole($user, [$associateChannelRoleId])) {
            // Associate channels can only upload for themselves
            $channels = User::where('id', $user->id)->whereHas('roles', function ($query) use ($associateChannelRoleId) {
                $query->where('id', $associateChannelRoleId);
            })->where('status', 1)->get();

            $sales = User::where('id', $user->id)->whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $channels = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($channelroleId) {
                $query->where('id', $channelroleId);
            })->where('status', 1)->get();

            $sales = User::whereIn('id', $channel_assign)->whereHas('roles', function ($query) use ($salesroleId) {
                $query->where('id', $salesroleId);
            })->get();
        }
        sleep(2);
        $this->updateBankMisTrackerFromApplications();
        return view('Frontend.Application.uploadMIS', compact('Route', 'channels', 'sales', 'user_id', 'role_id'));
    }


    public function updateBankMisTrackerFromApplications()
    {
        // Get total, matched, and unmatched cases grouped by created_at (upload month), bank, and product
        $stats = DB::table('applications')
            ->join('banks', 'applications.bank_id', '=', 'banks.id')
            ->join('products', 'applications.product_id', '=', 'products.id')
            ->select(
                DB::raw("DATE_FORMAT(applications.created_at, '%b-%Y') as month_year"),
                'banks.name as bank_name',
                'products.name as product_name',
                DB::raw('COUNT(applications.id) as total_cases'),
                DB::raw("SUM(CASE WHEN applications.app_id_is_matched = 1 THEN 1 ELSE 0 END) as matched_cases"),
                DB::raw("SUM(CASE WHEN applications.app_id_is_matched IS NULL OR applications.app_id_is_matched = 0 THEN 1 ELSE 0 END) as unmatched_cases")
            )
            ->groupBy(
                DB::raw("DATE_FORMAT(applications.created_at, '%b-%Y')"),
                'banks.name',
                'products.name'
            )
            ->get();

        foreach ($stats as $row) {
            // Get unmatched application numbers for this group
            $unmatchedAppNos = DB::table('applications')
                ->join('banks', 'applications.bank_id', '=', 'banks.id')
                ->join('products', 'applications.product_id', '=', 'products.id')
                ->where(DB::raw("DATE_FORMAT(applications.created_at, '%b-%Y')"), $row->month_year)
                ->where('banks.name', $row->bank_name)
                ->where('products.name', $row->product_name)
                ->where(function ($q) {
                    $q->whereNull('applications.app_id_is_matched')
                        ->orWhere('applications.app_id_is_matched', 0);
                })
                ->pluck('applications.app_id')
                ->toArray();
            $unmatched_case_details = implode(',', $unmatchedAppNos);

            // Status: received if all matched, else pending
            $status = ($row->unmatched_cases == 0 && $row->matched_cases > 0)
                ? 'received'
                : 'pending';

            BankMisTracker::updateOrCreate(
                [
                    'bank_mis_month' => $row->month_year,
                    'bank'           => $row->bank_name,
                    'product'        => $row->product_name,
                ],
                [
                    'total_cases'    => $row->total_cases,
                    'matched_cases'  => $row->matched_cases,
                    'unmatched_cases' => $row->unmatched_cases,
                    'unmatched_case_details' => $unmatched_case_details,
                    'status'         => $status,
                ]
            );
        }
    }


    public function storeExcel(Request $request)
    {
        try {

            // Define required headers (extra headers are allowed)
            $requiredHeaders = [
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
            $userId = $this->resolveSelectedApplicationUserId($request, $user);

            // Read the uploaded Excel file
            $file = $request->file('csv_file');
            $tempFilePath = $file->storeAs('tmp', 'uploaded.xlsx');

            // Parse the Excel file using Spatie SimpleExcel Reader
            $rows = SimpleExcelReader::create(storage_path('app/' . $tempFilePath))
                ->getRows()
                ->toArray();
            // Get the headers of the first row (usually the header)
            $headers = array_keys($rows[0]);

            $missingHeaders = array_values(array_diff($requiredHeaders, $headers));
            if (!empty($missingHeaders)) {
                return redirect()->back()->withErrors(['error' => 'Header format mismatch'])->withInput();
            }

            // Iterate through each row of the Excel data and insert into the database
            $successCount = 0; // Variable to count successful entries
            $duplicateAppIds = []; // Track duplicates for Unsecured group


            foreach ($rows as $row) {
                // Ensure the DISBURSE AMOUNT is not empty before proceeding
                if ($row['DISBURSE AMOUNT'] !== '') {
                    $appId = ($row['APP ID'] == '') ? null : $row['APP ID'];

                    // Determine bank, product, and group early (needed for Secured duplicate logic)
                    $bank_id = Bank::where('name', $row['BANK NAME'])->value('id');
                    $bank = Bank::where('name', $row['BANK NAME'])->first();
                    $product_id = Product::where('name', $row['PRODUCT NAME'])->value('id');
                    $group = Product::where('id', $product_id)->value('group');

                    // Handle duplicate app_id based on group type
                    if (!is_null($appId)) {
                        $existingApplication = Application::where('app_id', $appId)->first();
                        if ($existingApplication) {
                            if ($group == 'Secured') {
                                // Secured: allow duplicate with sequential postfix (-001, -002, etc.)
                                $appId = generateUniqueAppId($appId, Application::class);
                            } else {
                                // Unsecured: skip duplicate (existing behavior)
                                $duplicateAppIds[] = $appId;
                                continue;
                            }
                        }
                    }

                    $bank_product = BankProduct::where('bank_id', $bank_id)->where('product_id', $product_id)->first();
                    $application = new Application();
                    $application->user_id = $userId;
                    if ($bank_product) {
                        if ($bank_product->auto_generate_lan) {
                            $application->app_id = strtoupper(Str::slug($bank->short_name) . '_' . strtoupper(Str::slug($row['PRODUCT NAME'])) . '_' . rand(1111111, 9999999));
                        } else {
                            $application->app_id = $appId;
                        }
                    } else {
                        $application->app_id = $appId;
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
                    $application->customer_phone = isset($row['CUSTOMER PHONE NUMBER']) ? trim((string) $row['CUSTOMER PHONE NUMBER']) : null;
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

                    // Auto-set parent_channel_id and sharing_commission from channel's user_commission
                    $selectedUser = User::find($userId);
                    if ($selectedUser) {
                        $parentChannel = ChannelUser::where('associate_channel_id', $selectedUser->id)->first();
                        if ($parentChannel) {
                            $application->parent_channel_id = $parentChannel->channel_id;
                            $parentUser = User::find($parentChannel->channel_id);
                            $application->sharing_commission = $parentUser->user_commission ?? null;
                        } else {
                            $application->sharing_commission = $selectedUser->user_commission ?? null;
                        }
                    }

                    // Save the loan application record to the database
                    $application->save();

                    // Log application creation from Excel upload
                    ApplicationActivityLog::create([
                        'application_id' => $application->id,
                        'user_id' => Auth::id(),
                        'action' => 'created',
                        'description' => 'Application created via Excel upload. App ID: ' . $application->app_id,
                    ]);

                    // Send notification to admin users
                    $adminUsers = User::whereHas('roles', function ($query) {
                        $query->whereIn('id', [1, 35]); // Admin and maker role ID
                    })->get();

                    foreach ($adminUsers as $adminUser) {
                        $adminUser->notify(new NewApplicationNotification($application));
                    }

                    ProcessMISDataJob::dispatch($bank_id, $product_id);
                    $successCount++;  // Increment the count of successful insertions
                }
            }

            // After the loop, if there were successful entries, create the toast
            if ($successCount > 0) {
                $toastMessage = ($successCount > 1) ? "$successCount applications were" : "One application was";
                $toastMessage .= " uploaded successfully.";
                $redirect = redirect()->to('/application')->with('success', $toastMessage);
                if (!empty($duplicateAppIds)) {
                    $duplicateList = implode(', ', array_unique($duplicateAppIds));
                    $warningMessage = "The following application number(s) already exist and were not added: $duplicateList. Please check your application numbers.";
                    $redirect->with('warning', $warningMessage);
                }
                return $redirect;
            } else {
                if (!empty($duplicateAppIds)) {
                    $duplicateList = implode(', ', array_unique($duplicateAppIds));
                    $warningMessage = "The following application number(s) already exist and were not added: $duplicateList. Please check your application numbers.";
                    return redirect()->back()->with('warning', $warningMessage)->withInput();
                }
                // If no records were inserted
                return response()->json([
                    'error' => 'No new records added',
                    'message' => 'No new records were added to the database.',
                    'error_code' => 'ERR_NO_NEW_RECORDS'
                ], 200);
            }
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Throwable $th) {
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
                'bank_mis_month' => 'required',
            ]);

            $bank_id = $request->bank_id;
            $product_id = $request->product_id;
            $file = $request->file('xlsx_file');
            $tempFilePath = $file->storeAs('tmp', 'uploaded.xlsx');

            $group = Product::where('id', $product_id)->value('group');
            $bank_mis_month = $request->bank_mis_month;
            $excel = SimpleExcelReader::create(storage_path('app/' . $tempFilePath));
            $rows = $excel->getRows()->toArray();

            // Fetch the sheet data
            $sheetData = SheetMatching::where(['bank_id' => $bank_id, 'product_id' => $product_id])->first();
            if (!$sheetData) {
                return redirect()->to('/bank_mis')->with('error', 'No header mappings found for the selected bank and product.');
            }

            // Define critical fields required for matching applications with bank MIS
            $criticalFields = [
                'app_id' => 'Application ID',
                'payout_rate' => 'Commission Rate',
                'disbAmount' => 'Disbursement Amount',
            ];

            // Check if critical fields are empty in SheetMatching
            $missingFields = [];
            foreach ($criticalFields as $fieldKey => $fieldLabel) {
                if (empty($sheetData->$fieldKey)) {
                    $missingFields[] = "$fieldLabel ({$fieldKey})";
                }
            }

            // If critical fields are missing, return error
            if (!empty($missingFields)) {
                $bank = Bank::find($bank_id);
                $product = Product::find($product_id);
                $missingFieldsList = implode(', ', $missingFields);
                $errorMessage = "Sheet matching configuration incomplete for {$bank->name} - {$product->name}. " .
                    "Please configure these critical columns in Sheet Matching: {$missingFieldsList}";
                return redirect()->to('/bank_mis')->with('error', $errorMessage);
            }

            // Convert sheetData to an array and remove unnecessary fields
            $keysMapping = $sheetData->toArray();
            unset($keysMapping['id'], $keysMapping['bank_id'], $keysMapping['product_id'], $keysMapping['group'], $keysMapping['created_at'], $keysMapping['updated_at']);

            $filteredMapping = [];
            foreach ($keysMapping as $excelKey => $dataKey) {
                $dataKey = is_string($dataKey) ? trim($dataKey) : $dataKey;
                if ($dataKey === null || $dataKey === '') {
                    continue;
                }
                $filteredMapping[$excelKey] = $dataKey;
            }
            $keysMapping = $filteredMapping;

            $headers = array_keys($rows[0] ?? []);
            $missingHeadersInFile = [];
            foreach ($keysMapping as $excelKey => $dataKey) {
                if (!in_array($dataKey, $headers, true)) {
                    $missingHeadersInFile[] = $dataKey;
                }
            }
            if (!empty($missingHeadersInFile)) {
                $missingHeadersList = implode(', ', array_unique($missingHeadersInFile));
                return redirect()->to('/bank_mis')->with('error', "Missing header(s) in uploaded file: {$missingHeadersList}");
            }

            $successCount = 0;
            $duplicateAppIds = [];  // Track app_ids that already exist

            foreach ($rows as $row) {
                if ($row) {
                    // Extract values based on mapped keys
                    $data = [
                        'bank_id' => $bank_id,
                        'product_id' => $product_id,
                    ];
                    foreach ($keysMapping as $excelKey => $dataKey) {
                        if (array_key_exists($dataKey, $row)) {
                            $data[$excelKey] = $row[$dataKey];
                        }
                    }

                    $appIdRaw = $data['app_id'] ?? null;
                    if ($appIdRaw === null || trim((string) $appIdRaw) === '') {
                        continue;
                    }
                    $data['app_id'] = trim((string) $appIdRaw);

                    // Handle duplicate app_id in BankMIS table
                    if (isset($data['app_id']) && !empty($data['app_id'])) {
                        $appIdExists = BankMIS::where('app_id', $data['app_id'])->exists();
                        if ($appIdExists) {
                            if ($group == 'Secured') {
                                // Secured: allow duplicate with sequential postfix (-001, -002, etc.)
                                $data['app_id'] = generateUniqueAppId($data['app_id'], BankMIS::class);
                            } else {
                                // Unsecured: skip duplicate (existing behavior)
                                $duplicateAppIds[] = $data['app_id'];
                                continue;
                            }
                        }
                    }

                    // Note: payout_rate and payout_amount are saved as-is from the mapped Excel columns
                    // No calculations or transformations are applied - raw values from Bank MIS are preserved

                    // Attach selected month to data so it is saved and used in duplicate checks
                    $data['bank_mis_month'] = $bank_mis_month;

                    // Check if the record already exists based on all relevant fields (including month)
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
                        ->where('bank_mis_month', $data['bank_mis_month'] ?? NULL)
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
                    $bank->bank_mis_month = $data['bank_mis_month'] ?? NULL;
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

                    $successCount++;  // Increment success count
                }
            }

            // Dispatch Job for processing
            ProcessMISDataJob::dispatch($bank_id, $product_id);
            // Prepare response message with duplicate app_ids warning if any
            $successMessage = 'File uploaded successfully. Data processing will continue in the background.';

            if (!empty($duplicateAppIds)) {
                $duplicateList = implode(', ', $duplicateAppIds);
                $warningMessage = "The following application number(s) already exist and were not added: $duplicateList. Please check your application numbers.";
                return redirect()->to('/bank_mis')->with('success', $successMessage)->with('warning', $warningMessage);
            }

            return redirect()->to('/bank_mis')->with('success', $successMessage);
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

        sleep(2);
        $this->updateBankMisTrackerFromApplications();

        // refrash the select all check box page..


        return response()->json(['message' => $deletedCount . ' applications deleted successfully.'], 200);
    }

    public function getAssociatedPartnersByChannel($channelId)
    {
        $user = Auth::user();
        $channelId = (int) $channelId;

        if (!$this->canAccessChannelForAssociateSelection($user, $channelId)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $associateIds = ChannelUser::where('channel_id', $channelId)->pluck('associate_channel_id');
        $associates = User::whereIn('id', $associateIds)
            ->get(['id', 'first_name', 'last_name', 'Emp_Id']);

        $data = $associates->map(function ($associate) {
            return [
                'id' => $associate->id,
                'name' => trim($associate->first_name . ' ' . $associate->last_name),
                'emp_id' => $associate->Emp_Id,
            ];
        });

        return response()->json($data);
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

    private function resolveSelectedApplicationUserId(Request $request, $authUser)
    {
        $roleId = $this->getEffectiveRoleId($authUser);

        // Sales/Associate can only create/upload their own cases
        if (in_array($roleId, [3, 37], true)) {
            return $authUser->id;
        }

        // Channel can upload/create own case or linked associate case
        if ($roleId === 2) {
            $userType = $request->input('user_type', 'channel');
            if ($userType === 'associate') {
                $associateId = (int) $request->input('associate_id');
                if (!$associateId) {
                    throw ValidationException::withMessages([
                        'associate_id' => 'Please select associate partner.',
                    ]);
                }

                $isLinkedAssociate = ChannelUser::where('channel_id', $authUser->id)
                    ->where('associate_channel_id', $associateId)
                    ->exists();

                if (!$isLinkedAssociate) {
                    throw ValidationException::withMessages([
                        'associate_id' => 'Selected associate partner is not linked with your channel.',
                    ]);
                }

                return $associateId;
            }

            return $authUser->id;
        }

        // Admin/Maker/Checker
        if (in_array($roleId, [1, 35, 36], true)) {
            return $this->resolveAdminLikeTargetUserId($request);
        }

        // Staff and other roles: constrained by assigned channels/sales
        return $this->resolveStaffTargetUserId($request, $authUser);
    }

    private function resolveAdminLikeTargetUserId(Request $request)
    {
        $userType = $request->input('user_type');
        if (!$userType) {
            throw ValidationException::withMessages([
                'user_type' => 'Please select user type.',
            ]);
        }

        if ($userType === 'channel') {
            $channelId = (int) ($request->input('channel_id') ?: $request->input('channel_sales_id'));
            if (!$channelId || !$this->userHasRole($channelId, 2)) {
                throw ValidationException::withMessages([
                    'channel_id' => 'Please select valid channel partner.',
                ]);
            }
            return $channelId;
        }

        if ($userType === 'sales') {
            $salesId = (int) ($request->input('sales_id') ?: $request->input('channel_sales_id'));
            if (!$salesId || !$this->userHasRole($salesId, 3)) {
                throw ValidationException::withMessages([
                    'sales_id' => 'Please select valid sales person.',
                ]);
            }
            return $salesId;
        }

        if ($userType === 'associate') {
            $channelId = (int) $request->input('channel_id');
            $associateId = (int) $request->input('associate_id');
            if (!$channelId || !$this->userHasRole($channelId, 2)) {
                throw ValidationException::withMessages([
                    'channel_id' => 'Please select channel partner for associate.',
                ]);
            }
            if (!$associateId) {
                throw ValidationException::withMessages([
                    'associate_id' => 'Please select valid associate partner.',
                ]);
            }

            $isLinkedAssociate = ChannelUser::where('channel_id', $channelId)
                ->where('associate_channel_id', $associateId)
                ->exists();
            if (!$isLinkedAssociate) {
                throw ValidationException::withMessages([
                    'associate_id' => 'Selected associate partner is not linked with selected channel.',
                ]);
            }

            return $associateId;
        }

        throw ValidationException::withMessages([
            'user_type' => 'Invalid user type selected.',
        ]);
    }

    private function resolveStaffTargetUserId(Request $request, $authUser)
    {
        $assignedRaw = StaffAssign::where('user_id', $authUser->id)->value('channel_sales_id');
        $assignedIds = json_decode($assignedRaw, true);
        $assignedIds = is_array($assignedIds) ? array_map('intval', $assignedIds) : [];

        $userType = $request->input('user_type');
        if (!$userType) {
            throw ValidationException::withMessages([
                'user_type' => 'Please select user type.',
            ]);
        }

        if ($userType === 'channel') {
            $channelId = (int) ($request->input('channel_id') ?: $request->input('channel_sales_id'));
            if (!$channelId || !in_array($channelId, $assignedIds, true) || !$this->userHasRole($channelId, 2)) {
                throw ValidationException::withMessages([
                    'channel_id' => 'Please select valid assigned channel partner.',
                ]);
            }
            return $channelId;
        }

        if ($userType === 'sales') {
            $salesId = (int) ($request->input('sales_id') ?: $request->input('channel_sales_id'));
            if (!$salesId || !in_array($salesId, $assignedIds, true) || !$this->userHasRole($salesId, 3)) {
                throw ValidationException::withMessages([
                    'sales_id' => 'Please select valid assigned sales person.',
                ]);
            }
            return $salesId;
        }

        if ($userType === 'associate') {
            $channelId = (int) $request->input('channel_id');
            $associateId = (int) $request->input('associate_id');
            if (!$channelId || !in_array($channelId, $assignedIds, true) || !$this->userHasRole($channelId, 2)) {
                throw ValidationException::withMessages([
                    'channel_id' => 'Please select valid assigned channel partner for associate.',
                ]);
            }
            if (!$associateId) {
                throw ValidationException::withMessages([
                    'associate_id' => 'Please select valid associate partner.',
                ]);
            }

            $isLinkedAssociate = ChannelUser::where('channel_id', $channelId)
                ->where('associate_channel_id', $associateId)
                ->exists();
            if (!$isLinkedAssociate) {
                throw ValidationException::withMessages([
                    'associate_id' => 'Selected associate partner is not linked with selected channel.',
                ]);
            }
            return $associateId;
        }

        throw ValidationException::withMessages([
            'user_type' => 'Invalid user type selected.',
        ]);
    }

    private function canAccessChannelForAssociateSelection($authUser, $channelId)
    {
        $roleId = $this->getEffectiveRoleId($authUser);
        if (in_array($roleId, [1, 35, 36], true)) {
            return true;
        }

        if ($roleId === 2) {
            return (int) $authUser->id === (int) $channelId;
        }

        $assignedRaw = StaffAssign::where('user_id', $authUser->id)->value('channel_sales_id');
        $assignedIds = json_decode($assignedRaw, true);
        $assignedIds = is_array($assignedIds) ? array_map('intval', $assignedIds) : [];

        return in_array((int) $channelId, $assignedIds, true);
    }

    private function userHasRole($userId, $roleId)
    {
        return User::where('id', $userId)->whereHas('roles', function ($query) use ($roleId) {
            $query->where('id', $roleId);
        })->exists();
    }

    private function userHasAnyRole($user, array $roleIds)
    {
        $userRoleIds = $user->roles->pluck('id')->map(function ($id) {
            return (int) $id;
        })->toArray();

        foreach ($roleIds as $roleId) {
            if (in_array((int) $roleId, $userRoleIds, true)) {
                return true;
            }
        }

        return false;
    }

    private function getEffectiveRoleId($user)
    {
        $priority = [1, 35, 36, 2, 3, 37];
        $userRoleIds = $user->roles->pluck('id')->map(function ($id) {
            return (int) $id;
        })->toArray();

        foreach ($priority as $roleId) {
            if (in_array($roleId, $userRoleIds, true)) {
                return $roleId;
            }
        }

        return isset($userRoleIds[0]) ? (int) $userRoleIds[0] : 0;
    }

    private function parseDisbursementDate($rawDate)
    {
        if (empty($rawDate)) {
            return null;
        }

        $value = trim((string) $rawDate);
        foreach (['d-m-Y', 'Y-m-d'] as $format) {
            try {
                $parsed = Carbon::createFromFormat($format, $value);
                if ($parsed && $parsed->format($format) === $value) {
                    return $parsed->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Try next format
            }
        }

        return null;
    }
}
