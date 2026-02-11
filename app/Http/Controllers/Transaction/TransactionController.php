<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\BankData;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\Transaction;
use App\Models\TransactionBankAllocation;
use App\Models\TransactionItem;
use App\Models\User;
use App\Notifications\TransactionCreatedNotification;
use App\Notifications\TransactionApprovedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    /**
     * Transaction list page.
     * Admin/Maker/Checker see all transactions; Channel/Sales see only their own.
     */
    public function index(Request $request)
    {
        $Route = 'Transactions';
        $user = Auth::user();
        $roleId = $user->roles[0]->id;

        if ($request->ajax()) {
            $query = Transaction::query();

            // Channel/Sales users see only their own transactions
            if (in_array($roleId, [2, 3])) {
                $query->where('user_id', $user->id);
            }

            // Filters
            if ($request->status) {
                $query->where('status', $request->status);
            }
            if ($request->channel_id) {
                $query->where('user_id', $request->channel_id);
            }
            if ($request->date_range) {
                if (strpos($request->date_range, 'to') !== false) {
                    $dates = explode('to', $request->date_range);
                    $query->whereDate('created_at', '>=', trim($dates[0]))
                        ->whereDate('created_at', '<=', trim($dates[1]));
                }
            }

            $query->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('channel_name', function ($row) {
                    $user = User::find($row->user_id);
                    return $user ? $user->first_name . ' ' . $user->last_name : 'N/A';
                })
                ->addColumn('gross_amount_display', function ($row) {
                    return '₹ ' . indianNumberFormat($row->gross_amount);
                })
                ->addColumn('tds_display', function ($row) {
                    return '₹ ' . indianNumberFormat($row->tds_amount);
                })
                ->addColumn('advance_display', function ($row) {
                    return '₹ ' . indianNumberFormat($row->advance_amount);
                })
                ->addColumn('net_payable_display', function ($row) {
                    return '₹ ' . indianNumberFormat($row->net_payable);
                })
                ->addColumn('status_display', function ($row) {
                    $statusClass = strtolower($row->status);
                    $statusText = ucwords($row->status);
                    return '<button class="status-buttons ' . $statusClass . '">' . $statusText . '</button>';
                })
                ->addColumn('created_date', function ($row) {
                    return $row->created_at ? $row->created_at->format('d M Y') : '-';
                })
                ->addColumn('action', function ($row) {
                    $roleId = auth()->user()->roles[0]->id;
                    $buttons = '';

                    // View button for all roles
                    $buttons .= '<img onclick="window.location.href=\'' . url('/transactions/view/' . $row->id) . '\'" src="' . asset('assets/images/eye-icon.svg') . '" style="cursor:pointer;" title="View">';

                    // Approve button for channel users when status is pending
                    if (in_array($roleId, [2, 3]) && $row->status === 'pending') {
                        $buttons .= ' <img onclick="window.location.href=\'' . url('/transactions/approve/' . $row->id) . '\'" src="' . asset('assets/images/Edit.svg') . '" style="cursor:pointer;" title="Approve">';
                    }

                    // Complete button for checker when status is approved
                    if ($roleId == 36 && $row->status === 'approved') {
                        $buttons .= ' <button class="btn btn-sm btn-success complete-btn" data-id="' . $row->id . '" title="Complete"><i class="fas fa-check"></i></button>';
                    }

                    return $buttons;
                })
                ->rawColumns(['status_display', 'action'])
                ->make(true);
        }

        // Get channels for filter dropdown (admin/maker/checker only)
        $channels = [];
        if (in_array($roleId, [1, 35, 36])) {
            $channelIds = Transaction::pluck('user_id')->unique()->toArray();
            $channels = User::whereIn('id', $channelIds)->get();
        }

        return view('Frontend.Transaction.index', compact('Route', 'channels'));
    }

    /**
     * Process selected distributions into a transaction.
     */
    public function process(Request $request)
    {
        $request->validate([
            'distribution_ids' => 'required|array|min:1',
            'distribution_ids.*' => 'exists:settlement_distributions,id',
        ]);

        $distributionIds = $request->distribution_ids;

        // Fetch the distributions
        $distributions = SettlementDistribution::whereIn('id', $distributionIds)
            ->whereNull('transaction_id')
            ->get();

        if ($distributions->isEmpty()) {
            return redirect()->back()->with('error', 'No valid distributions selected or already processed.');
        }

        // Ensure all distributions belong to the same settlement/channel
        $settlementIds = $distributions->pluck('settlement_id')->unique();
        if ($settlementIds->count() > 1) {
            return redirect()->back()->with('error', 'Selected distributions must belong to the same settlement.');
        }

        $settlement = Settlement::find($settlementIds->first());
        if (!$settlement) {
            return redirect()->back()->with('error', 'Settlement not found.');
        }

        $userId = $settlement->user_id; // parent channel

        DB::beginTransaction();
        try {
            $totalGross = 0;
            $totalTds = 0;
            $totalNet = 0;
            $totalAdvance = 0;

            $itemsData = [];

            foreach ($distributions as $dist) {
                $gross = $dist->gross_amount ?? 0;
                $tds = $dist->tds ?? 0;
                $net = $dist->amount ?? 0;

                // Check for case-wise advance
                $advanceAmount = DB::table('advance_payment_cases')
                    ->where('application_id', $dist->application_id)
                    ->sum('advance_payment_amount');

                $totalGross += $gross;
                $totalTds += $tds;
                $totalNet += $net;
                $totalAdvance += $advanceAmount;

                $itemsData[] = [
                    'settlement_distribution_id' => $dist->id,
                    'gross_amount' => $gross,
                    'tds' => $tds,
                    'net_amount' => $net,
                    'advance_amount' => $advanceAmount,
                ];
            }

            $netPayable = $totalNet - $totalAdvance;
            if ($netPayable < 0) {
                $netPayable = 0;
            }

            // Create transaction
            $transaction = Transaction::create([
                'settlement_id' => $settlement->id,
                'user_id' => $userId,
                'gross_amount' => round($totalGross, 2),
                'tds_amount' => round($totalTds, 2),
                'advance_amount' => round($totalAdvance, 2),
                'net_payable' => round($netPayable, 2),
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            // Create transaction items
            foreach ($itemsData as $item) {
                TransactionItem::create(array_merge($item, [
                    'transaction_id' => $transaction->id,
                ]));
            }

            // Mark distributions as linked to this transaction
            SettlementDistribution::whereIn('id', $distributionIds)
                ->update(['transaction_id' => $transaction->id]);

            DB::commit();

            // Notify the channel user about the new transaction
            $channelUser = User::find($userId);
            if ($channelUser) {
                $channelUser->notify(new TransactionCreatedNotification($transaction));
            }

            return redirect('/transactions')->with('success', 'Transaction created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating transaction: ' . $e->getMessage());
        }
    }

    /**
     * Invoice-style detail view.
     */
    public function show($id)
    {
        $Route = 'Transaction Detail';
        $transaction = Transaction::with(['items.settlementDistribution', 'bankAllocations.bankAccount', 'user', 'settlement'])->findOrFail($id);

        $channelUser = User::find($transaction->user_id);

        return view('Frontend.Transaction.show', compact('Route', 'transaction', 'channelUser'));
    }

    /**
     * Channel bank allocation form (approve page).
     */
    public function approveForm($id)
    {
        $Route = 'Approve Transaction';
        $transaction = Transaction::with(['items.settlementDistribution', 'user'])->findOrFail($id);

        $user = Auth::user();
        $roleId = $user->roles[0]->id;

        // Only channel users can approve, and only pending transactions
        if (!in_array($roleId, [2, 3]) || $transaction->status !== 'pending') {
            return redirect('/transactions')->with('error', 'Unauthorized or transaction is not pending.');
        }

        // Get channel's bank accounts
        $bankAccounts = BankData::where('user_id', $transaction->user_id)
            ->where('status', 1)
            ->get();

        return view('Frontend.Transaction.approve', compact('Route', 'transaction', 'bankAccounts'));
    }

    /**
     * Channel submits bank allocation (approves the transaction).
     */
    public function approve(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return redirect('/transactions')->with('error', 'Transaction is not pending.');
        }

        $request->validate([
            'bank_accounts' => 'required|array|min:1',
            'bank_accounts.*.bank_account_id' => 'required|exists:bank_data,id',
            'bank_accounts.*.amount' => 'required|numeric|min:0.01',
        ]);

        $allocations = $request->bank_accounts;
        $totalAllocated = array_sum(array_column($allocations, 'amount'));

        // Validate: total must exactly equal net_payable
        if (round($totalAllocated, 2) != round($transaction->net_payable, 2)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Total allocated amount (₹' . number_format($totalAllocated, 2) . ') must exactly match net payable amount (₹' . number_format($transaction->net_payable, 2) . ').');
        }

        DB::beginTransaction();
        try {
            foreach ($allocations as $allocation) {
                TransactionBankAllocation::create([
                    'transaction_id' => $transaction->id,
                    'bank_account_id' => $allocation['bank_account_id'],
                    'amount' => $allocation['amount'],
                ]);
            }

            $transaction->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);

            DB::commit();

            // Notify all checkers about the approved transaction
            $channelUser = User::find($transaction->user_id);
            $checkers = User::whereHas('roles', function ($q) {
                $q->where('roles.id', 36);
            })->get();

            foreach ($checkers as $checker) {
                $checker->notify(new TransactionApprovedNotification($transaction, $channelUser));
            }

            return redirect('/transactions')->with('success', 'Transaction approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error approving transaction: ' . $e->getMessage());
        }
    }

    /**
     * Checker marks transaction as completed.
     */
    public function complete(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);
        $user = Auth::user();

        // Only checker (role 36) can complete, and only approved transactions
        if ($user->roles[0]->id != 36 || $transaction->status !== 'approved') {
            return response()->json(['error' => 'Unauthorized or transaction is not approved.'], 403);
        }

        $transaction->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => $user->id,
        ]);

        // Mark linked settlement distributions as paid
        SettlementDistribution::where('transaction_id', $transaction->id)
            ->update(['payment_status' => 'Success']);

        // Check if all distributions for this settlement are now paid
        $settlement = Settlement::find($transaction->settlement_id);
        if ($settlement) {
            $totalDist = SettlementDistribution::where('settlement_id', $settlement->id)->count();
            $paidDist = SettlementDistribution::where('settlement_id', $settlement->id)
                ->where('payment_status', 'Success')->count();
            if ($totalDist === $paidDist) {
                $settlement->update(['status' => 'completed', 'settlement_date' => now()]);
            }
        }

        return response()->json(['success' => 'Transaction marked as completed.']);
    }

    /**
     * AJAX endpoint to calculate totals for selected distributions.
     */
    public function calculateTotals(Request $request)
    {
        $request->validate([
            'distribution_ids' => 'required|array|min:1',
        ]);

        $distributions = SettlementDistribution::whereIn('id', $request->distribution_ids)->get();

        $totalGross = 0;
        $totalTds = 0;
        $totalNet = 0;
        $totalAdvance = 0;

        foreach ($distributions as $dist) {
            $totalGross += $dist->gross_amount ?? 0;
            $totalTds += $dist->tds ?? 0;
            $totalNet += $dist->amount ?? 0;

            // Check for case-wise advance
            $advanceAmount = DB::table('advance_payment_cases')
                ->where('application_id', $dist->application_id)
                ->sum('advance_payment_amount');
            $totalAdvance += $advanceAmount;
        }

        $netPayable = $totalNet - $totalAdvance;
        if ($netPayable < 0) {
            $netPayable = 0;
        }

        return response()->json([
            'gross' => round($totalGross, 2),
            'tds' => round($totalTds, 2),
            'advance' => round($totalAdvance, 2),
            'net_payable' => round($netPayable, 2),
            'gross_formatted' => '₹ ' . indianNumberFormat(round($totalGross, 2)),
            'tds_formatted' => '₹ ' . indianNumberFormat(round($totalTds, 2)),
            'advance_formatted' => '₹ ' . indianNumberFormat(round($totalAdvance, 2)),
            'net_payable_formatted' => '₹ ' . indianNumberFormat(round($netPayable, 2)),
        ]);
    }
}
