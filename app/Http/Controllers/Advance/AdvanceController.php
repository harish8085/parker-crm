<?php

namespace App\Http\Controllers\Advance;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\AdvanceAmountLog;
use App\Models\AdvancePaymentCase;
use App\Models\Application;
use App\Models\BankProduct;
use App\Models\ChannelUser;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdvanceController extends Controller
{
    protected $advance;    
    /**
     * Method __construct
     *
     * @param Advance $advance [explicite description]
     *
     * @return void
     */
    public function __construct(Advance $advance)
    {
        $this->advance = $advance;
    }
        
    /**
     * Method index
     *
     * @return void
     */
    public function index(Request $request)
    {
        $Route = 'Advances';
        
        if ($request->ajax()) {
            $query = Advance::with('user')->orderBy('id', 'desc');           

            if ($request->user_id !== null && $request->user_id !== '') {
                $query->where('user_id', $request->user_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('user_id', function ($row) {
                    if($row->user) {
                        return $row->user->first_name.' '.$row->user->last_name;
                    }
                    return '-';
                })
                ->editColumn('advance_amount', function ($row) {
                    return $row->advance_amount ? '₹' . number_format($row->advance_amount, 2) : '-';
                })
                ->editColumn('advance_date', function ($row) {
                    return $row->advance_date ? date('d-m-Y', strtotime($row->advance_date)) : '-';
                })
                ->editColumn('advance_status', function ($row) {
                    $checked = $row->advance_status == 1 ? 'checked' : '';
                    $status = '<label class="toggle-switch">
                        <input type="checkbox" class="status-toggle" data-advance-id="' . $row->id . '" ' . $checked . '>
                        <span class="toggle-slider"></span>
                    </label>';
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= "<a href='" . url('/advance/view/' . $row->id) . "' title='View Details' style='cursor: pointer; display: inline-block;'>";
                    $btn .= "<svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='color: #007bff;'>";
                    $btn .= "<path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle>";
                    $btn .= "</svg></a>";
                    return $btn;
                })
                ->rawColumns(['advance_status', 'action'])
                ->make(true);
        } 
        return view('Frontend.Advance.index', compact('Route'));
    }

    /**
     * Show the form for creating a new advance.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $Route = 'Add Advance';
        $users = User::select('id', 'first_name', 'last_name', 'email')
            ->whereHas('roles', function ($query) {
                $query->where('roles.id', 2);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(10)
            ->get();

        return view('Frontend.Advance.create', compact('Route', 'users'));
    }

    /**
     * Store a newly created advance in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Validation and advance creation is moved inside try block. 
            $messages = [
                'user_id.required' => 'Please select a channel partner.',
                'user_id.exists' => 'The selected channel partner does not exist.',
                'advance_amount.required_if' => 'Please enter an advance amount for No Case type.',
                'advance_amount.numeric' => 'The advance amount must be a number.',
                'advance_amount.min' => 'The advance amount must be at least 1.',
                'advance_remark.max' => 'The advance remark must not exceed 500 characters.',
                'case_type.required' => 'Please select case type (Case / No Case).',
                'case_type.in' => 'Invalid case type selected.',
                'application_ids.required_if' => 'Please select at least one case when case type is Case.',
                'application_ids.array' => 'Cases selection must be a valid list.',
                'application_ids.*.exists' => 'One or more selected cases are invalid.',
            ];
    
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'advance_amount' => 'required_if:case_type,no_case|numeric|min:1',
                'advance_remark' => 'nullable|string|max:500',
                'case_type' => 'required|in:case,no_case',
                'application_ids' => 'required_if:case_type,case|array',
                'application_ids.*' => 'exists:applications,id',
            ], $messages);

            $perCaseAmounts = [];

            // If case_type is "case", calculate advance amount from applications and bank_products percentage
            if ($validated['case_type'] === 'case') {
                $applications = Application::whereIn('id', $validated['application_ids'] ?? [])
                    ->get(['id', 'bank_id', 'product_id', 'disburse_amount', 'app_id', 'customer_name']);

                if ($applications->isEmpty()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'No valid applications found for the selected cases.');
                }

                $totalAdvanceAmount = 0;

                foreach ($applications as $app) {
                    $bankProduct = BankProduct::where('bank_id', $app->bank_id)
                        ->where('product_id', $app->product_id)
                        ->first();

                    if (!$bankProduct || $bankProduct->percent === null) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Payout percentage is not configured for Bank/Product of case ' . $app->app_id . ' (' . $app->customer_name . ').');
                    }

                    $disburseAmount = (float) ($app->disburse_amount ?? 0);
                    $percent = (float) $bankProduct->percent;

                    $caseAmount = round($disburseAmount * $percent / 100, 2);

                    if ($caseAmount <= 0) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Calculated advance amount is zero for case ' . $app->app_id . '. Please check disbursement amount and percentage.');
                    }

                    $perCaseAmounts[$app->id] = $caseAmount;
                    $totalAdvanceAmount += $caseAmount;
                }

                if ($totalAdvanceAmount <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Calculated total advance amount is zero. Please verify selected cases and configuration.');
                }

                // Override advance_amount with calculated total
                $validated['advance_amount'] = $totalAdvanceAmount;
            }
    
            $advanceAmountLog = Advance::createAdvance([
                'user_id' => $validated['user_id'],
                'advance_amount' => $validated['advance_amount'],
                'advance_remark' => $validated['advance_remark'] ?? null,
                'advance_type' => 'add', // default type now
                'created_by' => auth()->user()->id,
            ]);

            // If there are specific cases selected, create records in advance_payment_cases
            if ($validated['case_type'] === 'case' && !empty($validated['application_ids']) && $advanceAmountLog) {
                // Load applications with product relationship to get product names
                $applicationsWithDetails = Application::with('product')
                    ->whereIn('id', $validated['application_ids'])
                    ->get()
                    ->keyBy('id');

                foreach ($validated['application_ids'] as $applicationId) {
                    $caseAmount = $perCaseAmounts[$applicationId] ?? null;
                    if ($caseAmount === null) {
                        // Should not happen, but guard anyway
                        continue;
                    }

                    $application = $applicationsWithDetails->get($applicationId);
                    $productName = $application && $application->product ? $application->product->name : null;
                    
                    // Get product percent from BankProduct
                    $productPercent = null;
                    if ($application) {
                        $bankProduct = BankProduct::where('bank_id', $application->bank_id)
                            ->where('product_id', $application->product_id)
                            ->first();
                        $productPercent = $bankProduct ? $bankProduct->percent : null;
                    }

                    \App\Models\AdvancePaymentCase::create([
                        'advance_amount_log_id' => $advanceAmountLog->id,
                        'application_id' => $applicationId,
                        'product' => $productName,
                        'product_percent' => $productPercent,
                        'advance_payment_amount' => $caseAmount,
                        'status' => 'active',
                    ]);
                }
            }
    
            return redirect()->route('advance.index')->with('success', 'Advance created successfully.');
        } catch (\Throwable $e) {
            // You might further log the exception here
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } 
       
    }

    /**
     * Search users with role id 2 for select picker.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $term = $request->get('q');

        $users = User::select('id', 'first_name', 'last_name', 'email')
            ->whereHas('roles', function ($query) {
                $query->where('roles.id', 2);
            })
            ->when($term, function ($query) use ($term) {
                $query->where(function ($innerQuery) use ($term) {
                    $innerQuery->where('first_name', 'like', '%' . $term . '%')
                        ->orWhere('last_name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(10)
            ->get();

        $results = $users->map(function ($user) {
            $display = trim($user->first_name . ' ' . $user->last_name);
            if ($user->email) {
                $display .= " ({$user->email})";
            }

            return [
                'id' => $user->id,
                'text' => $display,
            ];
        });

        return response()->json($results);
    }

    /**
     * Get applications (cases) for a given channel partner (user) for multi-select.
     * This is kept for backward compatibility but may not be used if checkbox list is used.
     */
    public function applicationsByUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $term = $request->get('q');
        $userId = $request->get('user_id');
        $eligibleUserIds = $this->getChannelWithAssociateUserIds((int) $userId);

        $applications = Application::select('id', 'app_id', 'customer_name', 'disburse_amount')
            ->whereIn('user_id', $eligibleUserIds)
            ->where('status', 'pending')
            ->whereNotNull('app_id')
            ->where('app_id', '!=', '')
            ->whereDoesntHave('advancePaymentCase')
            ->when($term, function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('app_id', 'like', '%' . $term . '%')
                        ->orWhere('customer_name', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('id', 'desc')
            ->get();

        $results = $applications->map(function ($app) {
            $text = $app->app_id;
            if ($app->customer_name) {
                $text .= ' - ' . $app->customer_name;
            }
            if ($app->disburse_amount) {
                $text .= ' (₹' . number_format($app->disburse_amount, 2) . ')';
            }

            return [
                'id' => $app->id,
                'text' => $text,
            ];
        });

        return response()->json($results);
    }

    /**
     * Get applications (cases) with full details for checkbox list display.
     */
    public function applicationsListByUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->get('user_id');
        $eligibleUserIds = $this->getChannelWithAssociateUserIds((int) $userId);

        $applications = Application::with(['bank', 'product'])
            ->whereIn('user_id', $eligibleUserIds)
            ->where('status', 'pending')
            ->whereNotNull('app_id')
            ->where('app_id', '!=', '')
            ->whereDoesntHave('advancePaymentCase')
            ->orderBy('id', 'desc')
            ->get([
                'id', 'app_id', 'customer_name', 'customer_firm_name', 
                'disburse_amount', 'disbursement_date', 'case_location', 
                'case_state', 'bank_id', 'product_id', 'group', 'fresh_or_bt'
            ]);

        $results = $applications->map(function ($app) {
            return [
                'id' => $app->id,
                'app_id' => $app->app_id,
                'customer_name' => $app->customer_name,
                'customer_firm_name' => $app->customer_firm_name,
                'disburse_amount' => $app->disburse_amount,
                'disbursement_date' => $app->disbursement_date,
                'case_location' => $app->case_location,
                'case_state' => $app->case_state,
                'bank_name' => $app->bank ? $app->bank->name : '-',
                'product_name' => $app->product ? $app->product->name : '-',
                'group' => $app->group,
                'fresh_or_bt' => $app->fresh_or_bt,
            ];
        });

        return response()->json($results);
    }

    /**
     * Return selected channel user id plus all linked associate user ids.
     *
     * @param int $channelUserId
     * @return array<int>
     */
    private function getChannelWithAssociateUserIds(int $channelUserId): array
    {
        $associateIds = ChannelUser::where('channel_id', $channelUserId)
            ->pluck('associate_channel_id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge([$channelUserId], $associateIds)));
    }

    /**
     * Calculate advance amount for selected cases (applications) without saving.
     * Used for real-time calculation on the frontend.
     */
    public function calculateCaseAmount(Request $request)
    {
        $validated = $request->validate([
            'application_ids' => 'required|array',
            'application_ids.*' => 'exists:applications,id',
        ]);

        $applications = Application::whereIn('id', $validated['application_ids'] ?? [])
            ->get(['id', 'bank_id', 'product_id', 'disburse_amount', 'app_id', 'customer_name']);

        if ($applications->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No valid applications found for the selected cases.',
            ], 422);
        }

        $perCase = [];
        $totalAdvanceAmount = 0;

        foreach ($applications as $app) {
            $bankProduct = BankProduct::where('bank_id', $app->bank_id)
                ->where('product_id', $app->product_id)
                ->first();

            if (!$bankProduct || $bankProduct->percent === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payout percentage is not configured for Bank/Product of case ' . $app->app_id . ' (' . $app->customer_name . ').',
                ], 422);
            }

            $disburseAmount = (float) ($app->disburse_amount ?? 0);
            $percent = (float) $bankProduct->percent;
            $caseAmount = round($disburseAmount * $percent / 100, 2);

            if ($caseAmount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Calculated advance amount is zero for case ' . $app->app_id . '. Please check disbursement amount and percentage.',
                ], 422);
            }

            $perCase[] = [
                'application_id' => $app->id,
                'app_id' => $app->app_id,
                'customer_name' => $app->customer_name,
                'disburse_amount' => $disburseAmount,
                'percent' => $percent,
                'advance_amount' => $caseAmount,
            ];

            $totalAdvanceAmount += $caseAmount;
        }

        if ($totalAdvanceAmount <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Calculated total advance amount is zero. Please verify selected cases and configuration.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'total_amount' => round($totalAdvanceAmount, 2),
            'cases' => $perCase,
        ]);
    }

    /**
     * Display the specified advance details with logs.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $Route = 'View Advance Details';
        $advance = Advance::with('user')->findOrFail($id);
        
        // Handle AJAX request for DataTables
        if ($request->ajax()) {
            $query = AdvanceAmountLog::with('createdBy')
                ->where('advance_id', $id)
                ->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('type', function ($row) {
                    $badgeClass = $row->type == 'add' ? 'log-type-add' : 'log-type-deduct';
                    return '<span class="log-type-badge ' . $badgeClass . '">' . ucfirst($row->type) . '</span>';
                })
                ->editColumn('advance_amount', function ($row) {
                    return '₹' . number_format($row->advance_amount, 2);
                })
                ->editColumn('advance_date', function ($row) {
                    return $row->advance_date ? date('d-m-Y', strtotime($row->advance_date)) : '-';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                })
                ->editColumn('created_by', function ($row) {
                    if ($row->createdBy) {
                        return $row->createdBy->first_name . ' ' . $row->createdBy->last_name;
                    }
                    return '-';
                })
                ->editColumn('remark', function ($row) {
                    return $row->remark ? $row->remark : '-';
                })
                ->addColumn('actions', function ($row) {
                    // Check if this log has associated payment cases
                    $hasCases = \App\Models\AdvancePaymentCase::where('advance_amount_log_id', $row->id)->exists();
                    if ($hasCases) {
                        return '<button type="button" class="btn btn-sm btn-info view-app-ids" data-log-id="' . $row->id . '" title="View Application IDs">
                                    <i class="fas fa-eye"></i>
                                </button>';
                    }
                    return '-';
                })
                ->rawColumns(['type', 'actions'])
                ->make(true);
        }

        return view('Frontend.Advance.show', compact('Route', 'advance'));
    }

    /**
     * Show the form for editing an existing advance (latest log & cases).
     */
    public function edit($id)
    {
        $Route = 'Edit Advance';
        $advance = Advance::with('user', 'advanceAmountLogs')->findOrFail($id);

        // Get latest log for this advance
        $latestLog = $advance->advanceAmountLogs()->latest('created_at')->first();

        $selectedApplicationIds = [];
        if ($latestLog) {
            $selectedApplicationIds = \App\Models\AdvancePaymentCase::where('advance_amount_log_id', $latestLog->id)
                ->pluck('application_id')
                ->toArray();
        }

        // Determine case type: if there are any cases linked, it's "case", else "no_case"
        $caseType = !empty($selectedApplicationIds) ? 'case' : 'no_case';

        // Preload selected applications for select2 display
        $preloadedApplications = [];
        if (!empty($selectedApplicationIds)) {
            $preloadedApplications = \App\Models\Application::whereIn('id', $selectedApplicationIds)
                ->get(['id', 'app_id', 'customer_name', 'disburse_amount']);
        }

        $users = User::select('id', 'first_name', 'last_name', 'email')
            ->whereHas('roles', function ($query) {
                $query->where('roles.id', 2);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(10)
            ->get();

        return view('Frontend.Advance.edit', compact(
            'Route',
            'advance',
            'users',
            'latestLog',
            'caseType',
            'selectedApplicationIds',
            'preloadedApplications'
        ));
    }

    /**
     * Update an existing advance & its latest log/cases with same logic as create.
     */
    public function update(Request $request, $id)
    {
        try {
            $messages = [
                'user_id.required' => 'Please select a channel partner.',
                'user_id.exists' => 'The selected channel partner does not exist.',
                'advance_amount.required_if' => 'Please enter an advance amount for No Case type.',
                'advance_amount.numeric' => 'The advance amount must be a number.',
                'advance_amount.min' => 'The advance amount must be at least 1.',
                'advance_remark.max' => 'The advance remark must not exceed 500 characters.',
                'case_type.required' => 'Please select case type (Case / No Case).',
                'case_type.in' => 'Invalid case type selected.',
                'application_ids.required_if' => 'Please select at least one case when case type is Case.',
                'application_ids.array' => 'Cases selection must be a valid list.',
                'application_ids.*.exists' => 'One or more selected cases are invalid.',
            ];

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'advance_amount' => 'required_if:case_type,no_case|numeric|min:1',
                'advance_remark' => 'nullable|string|max:500',
                'case_type' => 'required|in:case,no_case',
                'application_ids' => 'required_if:case_type,case|array',
                'application_ids.*' => 'exists:applications,id',
            ], $messages);

            $advance = Advance::with('advanceAmountLogs')->findOrFail($id);

            $perCaseAmounts = [];

            // If case_type is "case", recalculate advance amount from applications and bank_products percentage
            if ($validated['case_type'] === 'case') {
                $applications = Application::whereIn('id', $validated['application_ids'] ?? [])
                    ->get(['id', 'bank_id', 'product_id', 'disburse_amount', 'app_id', 'customer_name']);

                if ($applications->isEmpty()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'No valid applications found for the selected cases.');
                }

                $totalAdvanceAmount = 0;

                foreach ($applications as $app) {
                    $bankProduct = BankProduct::where('bank_id', $app->bank_id)
                        ->where('product_id', $app->product_id)
                        ->first();

                    if (!$bankProduct || $bankProduct->percent === null) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Payout percentage is not configured for Bank/Product of case ' . $app->app_id . ' (' . $app->customer_name . ').');
                    }

                    $disburseAmount = (float) ($app->disburse_amount ?? 0);
                    $percent = (float) $bankProduct->percent;

                    $caseAmount = round($disburseAmount * $percent / 100, 2);

                    if ($caseAmount <= 0) {
                        return redirect()->back()
                            ->withInput()
                            ->with('error', 'Calculated advance amount is zero for case ' . $app->app_id . '. Please check disbursement amount and percentage.');
                    }

                    $perCaseAmounts[$app->id] = $caseAmount;
                    $totalAdvanceAmount += $caseAmount;
                }

                if ($totalAdvanceAmount <= 0) {
                    return redirect()->back()
                        ->withInput()
                        ->with('error', 'Calculated total advance amount is zero. Please verify selected cases and configuration.');
                }

                $validated['advance_amount'] = $totalAdvanceAmount;
            }

            \DB::transaction(function () use ($advance, $validated, $perCaseAmounts) {
                // Update base advance details
                $advance->user_id = $validated['user_id'];
                $advance->advance_amount = $validated['advance_amount'];
                $advance->advance_remark = $validated['advance_remark'] ?? null;
                $advance->save();

                // Latest log
                $latestLog = $advance->advanceAmountLogs()->latest('created_at')->first();
                if (!$latestLog) {
                    // If no log exists (edge case), create one
                    $latestLog = AdvanceAmountLog::createAdvanceAmountLog([
                        'advance_id' => $advance->id,
                        'advance_amount' => $validated['advance_amount'],
                        'advance_date' => now()->toDateString(),
                        'type' => 'add',
                        'remark' => $validated['advance_remark'] ?? null,
                        'created_by' => auth()->user()->id,
                    ]);
                } else {
                    // Update existing latest log
                    $latestLog->advance_amount = $validated['advance_amount'];
                    $latestLog->advance_date = now()->toDateString();
                    $latestLog->type = 'add';
                    $latestLog->remark = $validated['advance_remark'] ?? null;
                    $latestLog->created_by = auth()->user()->id;
                    $latestLog->save();
                }

                // Sync cases in advance_payment_cases for this log
                \App\Models\AdvancePaymentCase::where('advance_amount_log_id', $latestLog->id)->delete();

                if ($validated['case_type'] === 'case' && !empty($validated['application_ids'])) {
                    // Load applications with product relationship to get product names
                    $applicationsWithDetails = Application::with('product')
                        ->whereIn('id', $validated['application_ids'])
                        ->get()
                        ->keyBy('id');

                    foreach ($validated['application_ids'] as $applicationId) {
                        $caseAmount = $perCaseAmounts[$applicationId] ?? null;
                        if ($caseAmount === null) {
                            continue;
                        }

                        $application = $applicationsWithDetails->get($applicationId);
                        $productName = $application && $application->product ? $application->product->name : null;
                        
                        // Get product percent from BankProduct
                        $productPercent = null;
                        if ($application) {
                            $bankProduct = BankProduct::where('bank_id', $application->bank_id)
                                ->where('product_id', $application->product_id)
                                ->first();
                            $productPercent = $bankProduct ? $bankProduct->percent : null;
                        }

                        \App\Models\AdvancePaymentCase::create([
                            'advance_amount_log_id' => $latestLog->id,
                            'application_id' => $applicationId,
                            'product' => $productName,
                            'product_percent' => $productPercent,
                            'advance_payment_amount' => $caseAmount,
                            'status' => 'active',
                        ]);
                    }
                }
            });

            return redirect()->route('advance.index')->with('success', 'Advance updated successfully.');
        } catch (\Throwable $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * Get application IDs (app_ids) for a specific advance amount log.
     *
     * @param  int  $logId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogApplicationIds($logId)
    {
        try {
            $log = AdvanceAmountLog::with(['paymentCases.application.bank'])->findOrFail($logId);
            
            $applications = $log->paymentCases->map(function ($paymentCase) {
                if ($paymentCase->application && $paymentCase->application->app_id) {
                    $disburseAmount = $paymentCase->application->disburse_amount ?? 0;
                    return [
                        'app_id' => $paymentCase->application->app_id,
                        'bank_name' => $paymentCase->application->bank ? $paymentCase->application->bank->name : '-',
                        'product_name' => $paymentCase->product ? $paymentCase->product : '-',
                        'product_percent' => $paymentCase->product_percent ? number_format($paymentCase->product_percent, 2) . '%' : '-',
                        'disburse_amount' => number_format($disburseAmount, 2),
                        'advance_payment_amount' => $paymentCase->advance_payment_amount ? number_format($paymentCase->advance_payment_amount, 2) : '-',
                        'disbursement_date' => $paymentCase->application->disbursement_date ? date('d-m-Y', strtotime($paymentCase->application->disbursement_date)) : '-',
                    ];
                }
                return null;
            })->filter(function ($item) {
                // Filter out null entries
                return $item !== null && !empty($item['app_id']);
            })->values();

            return response()->json([
                'success' => true,
                'applications' => $applications,
                'count' => $applications->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch application IDs: ' . $e->getMessage(),
            ], 500);
        }
    }
}
