<?php

namespace App\Http\Controllers\Advance;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\AdvancePaymentCase;
use App\Models\AdvanceRequest;
use App\Models\User;
use App\Notifications\AdvanceRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdvanceRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->assertAdminOrChecker();

        $Route = 'Advance Requests';
        $tab = $request->get('tab', 'pending');
        $isAdmin = $this->isAdminUser();

        if ($request->ajax()) {
            $query = AdvanceRequest::with(['channelUser', 'requestedByUser', 'adminActionByUser'])
                ->withCount('requestCases')
                ->orderByDesc('id');

            $status = $this->resolveStatusFromTab($tab);
            $query->where('status', $status);

            if (!$isAdmin) {
                // Checker can only see their own requests.
                $query->where('requested_by', auth()->id());
            }

            if ($request->filled('user_id')) {
                $query->where('user_id', $request->input('user_id'));
            }

            if ($isAdmin && $request->filled('requested_by')) {
                $query->where('requested_by', $request->input('requested_by'));
            }

            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('channel_partner', function (AdvanceRequest $row) {
                    if (!$row->channelUser) {
                        return '-';
                    }
                    return trim($row->channelUser->first_name . ' ' . $row->channelUser->last_name);
                })
                ->addColumn('requested_by_name', function (AdvanceRequest $row) {
                    if (!$row->requestedByUser) {
                        return '-';
                    }
                    return trim($row->requestedByUser->first_name . ' ' . $row->requestedByUser->last_name);
                })
                ->editColumn('case_type', function (AdvanceRequest $row) {
                    return $row->case_type === 'case' ? 'Case' : 'No Case';
                })
                ->editColumn('requested_amount', function (AdvanceRequest $row) {
                    return '₹' . number_format((float) $row->requested_amount, 2);
                })
                ->addColumn('case_count', function (AdvanceRequest $row) {
                    return (int) ($row->request_cases_count ?? 0);
                })
                ->editColumn('status', function (AdvanceRequest $row) {
                    if ($row->status === 'approved') {
                        return '<span class="badge bg-success">Completed</span>';
                    }
                    if ($row->status === 'rejected') {
                        return '<span class="badge bg-danger">Rejected</span>';
                    }
                    return '<span class="badge bg-warning text-dark">Pending</span>';
                })
                ->addColumn('actioned_by', function (AdvanceRequest $row) {
                    if (!$row->adminActionByUser) {
                        return '-';
                    }
                    return trim($row->adminActionByUser->first_name . ' ' . $row->adminActionByUser->last_name);
                })
                ->editColumn('created_at', function (AdvanceRequest $row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                })
                ->addColumn('actions', function (AdvanceRequest $row) {
                    $btn = '<button type="button" class="btn btn-sm btn-outline-info me-1 view-request-cases-btn" data-request-id="' . $row->id . '">Cases</button>';
                    if ($this->isAdminUser() && $row->status === 'pending') {
                        $btn .= '<button type="button" class="btn btn-sm btn-success me-1 approve-request-btn" data-request-id="' . $row->id . '">Approve</button>';
                        $btn .= '<button type="button" class="btn btn-sm btn-danger reject-request-btn" data-request-id="' . $row->id . '">Reject</button>';
                    }
                    return $btn;
                })
                ->rawColumns(['status', 'actions'])
                ->make(true);
        }

        return view('Frontend.AdvanceRequest.index', compact('Route', 'tab', 'isAdmin'));
    }

    public function approve(Request $request, $id)
    {
        $this->assertAdmin();

        $validated = $request->validate([
            'admin_remark' => 'nullable|string|max:1000',
        ]);

        $advanceRequest = AdvanceRequest::with(['requestCases.application.product'])->findOrFail($id);
        if ($advanceRequest->status !== 'pending') {
            return response()->json(['message' => 'This request is already actioned.'], 422);
        }

        DB::transaction(function () use ($advanceRequest, $validated) {
            $log = Advance::createAdvance([
                'user_id' => $advanceRequest->user_id,
                'advance_amount' => $advanceRequest->requested_amount,
                'advance_type' => 'add',
                'advance_date' => now()->toDateString(),
                'advance_status' => 1,
                'advance_remark' => $advanceRequest->advance_remark,
                'created_by' => auth()->id(),
            ]);

            foreach ($advanceRequest->requestCases as $case) {
                AdvancePaymentCase::create([
                    'advance_amount_log_id' => $log->id,
                    'application_id' => $case->application_id,
                    'product' => $case->product,
                    'product_percent' => $case->product_percent,
                    'advance_payment_amount' => $case->advance_payment_amount,
                    'status' => 'active',
                ]);
            }

            $advanceRequest->status = 'approved';
            $advanceRequest->admin_action_by = auth()->id();
            $advanceRequest->admin_action_at = now();
            $advanceRequest->admin_remark = $validated['admin_remark'] ?? null;
            $advanceRequest->approved_log_id = $log->id;
            $advanceRequest->save();
        });

        $advanceRequest->refresh();
        if ($advanceRequest->requestedByUser) {
            $advanceRequest->requestedByUser->notify(new AdvanceRequestNotification(
                'Advance request #' . $advanceRequest->id . ' has been approved.',
                url('/advance')
            ));
        }

        return response()->json(['message' => 'Advance request approved and added to advance balance.']);
    }

    public function reject(Request $request, $id)
    {
        $this->assertAdmin();

        $validated = $request->validate([
            'admin_remark' => 'required|string|max:1000',
        ], [
            'admin_remark.required' => 'Rejection reason is required.',
        ]);

        $advanceRequest = AdvanceRequest::with('requestedByUser')->findOrFail($id);
        if ($advanceRequest->status !== 'pending') {
            return response()->json(['message' => 'This request is already actioned.'], 422);
        }

        $advanceRequest->status = 'rejected';
        $advanceRequest->admin_action_by = auth()->id();
        $advanceRequest->admin_action_at = now();
        $advanceRequest->admin_remark = $validated['admin_remark'];
        $advanceRequest->save();

        if ($advanceRequest->requestedByUser) {
            $advanceRequest->requestedByUser->notify(new AdvanceRequestNotification(
                'Advance request #' . $advanceRequest->id . ' has been rejected. Reason: ' . $validated['admin_remark'],
                url('/advance')
            ));
        }

        return response()->json(['message' => 'Advance request rejected successfully.']);
    }

    public function cases($id)
    {
        $this->assertAdminOrChecker();

        $advanceRequest = AdvanceRequest::with(['requestCases.application.bank'])->findOrFail($id);
        if (!$this->isAdminUser() && (int) $advanceRequest->requested_by !== (int) auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $cases = $advanceRequest->requestCases->map(function ($row) {
            return [
                'app_id' => optional($row->application)->app_id ?: '-',
                'customer_name' => optional($row->application)->customer_name ?: '-',
                'bank_name' => optional(optional($row->application)->bank)->name ?: '-',
                'product' => $row->product ?: '-',
                'product_percent' => $row->product_percent ? $row->product_percent . '%' : '-',
                'advance_payment_amount' => number_format((float) $row->advance_payment_amount, 2),
            ];
        })->values();

        return response()->json([
            'request_id' => $advanceRequest->id,
            'cases' => $cases,
        ]);
    }

    public function searchCheckers(Request $request)
    {
        $this->assertAdmin();

        $term = $request->get('q');

        $users = User::query()
            ->select('id', 'first_name', 'last_name', 'email')
            ->where('user_type', 'checker')
            ->when($term, function ($query) use ($term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('first_name', 'like', '%' . $term . '%')
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

    private function assertAdmin(): void
    {
        $user = Auth::user();
        if (!$user || !$this->isAdminUser()) {
            abort(403, 'Unauthorized action.');
        }
    }

    private function assertAdminOrChecker(): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        if ($this->isAdminUser() || $user->user_type === 'checker') {
            return;
        }

        abort(403, 'Unauthorized action.');
    }

    private function isAdminUser(): bool
    {
        $user = Auth::user();
        if (!$user) {
            return false;
        }

        return $user->user_type === 'admin' || $user->roles()->where('roles.id', 1)->exists();
    }

    private function resolveStatusFromTab(string $tab): string
    {
        if ($tab === 'completed') {
            return 'approved';
        }
        if ($tab === 'rejected') {
            return 'rejected';
        }

        return 'pending';
    }
}
