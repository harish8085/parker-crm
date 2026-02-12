<?php

namespace App\Http\Controllers\Settlement;

use App\Exports\SettlementExport;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\BankData;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\StaffAssign;
use App\Models\Settings;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\SimpleExcel\SimpleExcelReader;
use Yajra\DataTables\Facades\DataTables;

class SettlementController extends Controller
{
    public function index(Request $request)
    {
        $p = request('p');

        $Route = 'Settlement';
        $user = Auth::user();

        $settlements = $this->getSettlementData($user, $p);

        Session::put('channel_url', $p);

        // Channel/Sales users or detail view (when ?p= is set)
        // Makers (35) and Checkers (36) see the admin-like parent channel list, not the user view
        if ((Auth::user()->roles[0]->id == 2 || Auth::user()->roles[0]->id == 3) || $p) {
            $settlements = $this->getSettlementData($user, $p);

            if ($request->ajax()) {
                // For detail view: show settlement_distributions (application-level breakdown)
                $query = SettlementDistribution::query();

                if ($p) {
                    // Admin viewing a specific channel's distributions
                    $settlementIds = Settlement::where('user_id', $p)->pluck('id');
                    $query->whereIn('settlement_id', $settlementIds);
                } else {
                    // Channel/Sales user viewing their own distributions
                    $settlementIds = Settlement::where('user_id', $user->id)
                        ->where('status', '!=', 'checker')
                        ->pluck('id');
                    $query->whereIn('settlement_id', $settlementIds);
                }

                // Hide completed/paid distributions
                $query->where(function ($q) {
                    $q->whereNull('payment_status')->orWhere('payment_status', '!=', 'Success');
                });

                // Date filter
                if ($request->date) {
                    $now = Carbon::now();
                    if ($request->date == 'today') {
                        $query->whereDate('settlement_distributions.created_at', Carbon::today()->toDateString());
                    } elseif ($request->date == 'yesterday') {
                        $query->whereDate('settlement_distributions.created_at', Carbon::yesterday()->toDateString());
                    } elseif ($request->date == 'this_week') {
                        $query->whereDate('settlement_distributions.created_at', '>=', $now->startOfWeek()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $now->endOfWeek()->toDateString());
                    } elseif ($request->date == 'last_week') {
                        $subWeek = $now->subWeek();
                        $query->whereDate('settlement_distributions.created_at', '>=', $subWeek->startOfWeek()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $subWeek->endOfWeek()->toDateString());
                    } elseif ($request->date == 'this_month') {
                        $query->whereDate('settlement_distributions.created_at', '>=', $now->startOfMonth()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $now->endOfMonth()->toDateString());
                    } elseif ($request->date == 'last_month') {
                        $subMonth = $now->subMonth();
                        $query->whereDate('settlement_distributions.created_at', '>=', $subMonth->startOfMonth()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $subMonth->endOfMonth()->toDateString());
                    } elseif ($request->date == 'last_3_months') {
                        $query->whereDate('settlement_distributions.created_at', '>=', $now->subMonths(2)->startOfMonth()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $now->endOfMonth()->toDateString());
                    } elseif ($request->date == 'last_6_months') {
                        $query->whereDate('settlement_distributions.created_at', '>=', $now->subMonths(5)->startOfMonth()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $now->endOfMonth()->toDateString());
                    } elseif ($request->date == 'this_year') {
                        $query->whereDate('settlement_distributions.created_at', '>=', $now->startOfYear()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $now->endOfYear()->toDateString());
                    } elseif ($request->date == 'last_year') {
                        $lastYear = $now->subYear();
                        $query->whereDate('settlement_distributions.created_at', '>=', $lastYear->startOfYear()->toDateString())
                            ->whereDate('settlement_distributions.created_at', '<=', $lastYear->endOfYear()->toDateString());
                    } elseif ($request->date == 'custom' && isset($request->date_range)) {
                        if (strpos($request->date_range, 'to') !== false) {
                            $dates = explode('to', $request->date_range);
                            $query->whereDate('settlement_distributions.created_at', '>=', trim($dates[0]))
                                ->whereDate('settlement_distributions.created_at', '<=', trim($dates[1]));
                        }
                    }
                }

                if ($request->status) {
                    $filteredSettlementIds = Settlement::where('user_id', $p ?? $user->id)
                        ->where('status', $request->status)
                        ->pluck('id');
                    $query->whereIn('settlement_id', $filteredSettlementIds);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('checkbox', function ($row) use ($p) {
                        $roleId = auth()->user()->roles[0]->id;
                        // Only show checkboxes for admin/maker/checker and only if distribution is not yet in a transaction
                        if ($p && in_array($roleId, [1, 35, 36]) && is_null($row->transaction_id)) {
                            return '<input type="checkbox" class="dist-checkbox" value="' . $row->id . '" data-gross="' . ($row->gross_amount ?? 0) . '" data-tds="' . ($row->tds ?? 0) . '" data-net="' . ($row->amount ?? 0) . '">';
                        }
                        if ($p && in_array($roleId, [1, 35, 36]) && !is_null($row->transaction_id)) {
                            return '<span class="badge bg-secondary">Processed</span>';
                        }
                        return '';
                    })
                    ->addColumn('app_id', function ($row) {
                        $application = DB::table('applications')->where('id', $row->application_id)->first();
                        return $application ? $application->app_id : 'N/A';
                    })
                    ->addColumn('customer_name', function ($row) {
                        $application = DB::table('applications')->where('id', $row->application_id)->first();
                        return $application->customer_name ?? '-';
                    })
                    ->addColumn('disbursement_amount', function ($row) {
                        $application = DB::table('applications')->where('id', $row->application_id)->first();
                        return $application ? '₹ ' . indianNumberFormat($application->disburse_amount ?? 0) : '-';
                    })
                    ->addColumn('submitted_by', function ($row) {
                        $application = DB::table('applications')->where('id', $row->application_id)->first();
                        if ($application && $application->user_id) {
                            $user = DB::table('users')->where('id', $application->user_id)->first();
                            return $user ? $user->first_name . ' ' . ($user->last_name ?? '') : '-';
                        }
                        return '-';
                    })
                    ->addColumn('company_receiving', function ($row) {
                        $application = DB::table('applications')->where('id', $row->application_id)->first();
                        return $application && $application->commission_rate ? $application->commission_rate . '%' : '-';
                    })
                    ->addColumn('received_rate', function ($row) {
                        return $row->received_rate ? $row->received_rate . '%' : '-';
                    })
                    ->addColumn('gross_amount', function ($row) {
                        return '₹ ' . indianNumberFormat($row->gross_amount ?? 0);
                    })
                    ->addColumn('tds_amount', function ($row) {
                        return '₹ ' . indianNumberFormat($row->tds ?? 0);
                    })
                    ->addColumn('net_amount', function ($row) {
                        return '₹ ' . indianNumberFormat($row->amount ?? 0);
                    })
                    ->addColumn('advance_flag', function ($row) {
                        $hasAdvance = DB::table('advance_payment_cases')
                            ->where('application_id', $row->application_id)
                            ->exists();
                        return $hasAdvance
                            ? '<span class="badge bg-warning text-dark">Advance</span>'
                            : '-';
                    })
                    ->addColumn('status', function ($row) {
                        $settlement = Settlement::find($row->settlement_id);
                        $status = $settlement->status ?? '-';
                        $statusClass = strtolower($status);
                        $statusText = ucwords($status);
                        return '<button class="status-buttons ' . $statusClass . '">' . $statusText . '</button>';
                    })
                    ->addColumn('action', function ($row) {
                        $buttons = '';
                        if (auth()->user()->hasPermission('settlement', 'view')) {
                            $buttons .= '<img onclick="window.location.href=\'' . url('/settlement/view/' . $row->settlement_id) . '\'" src="' . asset('assets/images/eye-icon.svg') . '">';
                        }
                        if (auth()->user()->hasPermission('settlement', 'update')) {
                            $buttons .= '<img onclick="window.location.href=\'' . url('/settlement/distribution/edit/' . $row->id) . '\'" src="' . asset('assets/images/Edit.svg') . '">';
                        }
                        return $buttons;
                    })
                    ->rawColumns(['checkbox', 'advance_flag', 'status', 'action'])
                    ->make(true);
            }
            // Fetch channel advance balance for display
            $channelAdvance = 0;
            if ($p) {
                $channelAdvance = \App\Models\Advance::where('user_id', $p)->value('advance_amount') ?? 0;
            }
            $tdsPercentage = Settings::where('name', 'TDS')->first()->value ?? 2;
            return view('Frontend.Settlement.userView', compact('Route', 'settlements', 'p', 'channelAdvance', 'tdsPercentage'));
        } else {
            // Admin/Staff view: Show parent channels list
            if ($request->ajax()) {
                $tab = $request->input('tab', 'pending');
                // Only show parent channels that have settlements matching the tab filter
                $query = $this->getParentChannelQuery($user, $tab);

                if ($request->first_name) {
                    $query->where('first_name', $request->first_name);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('first_name', function ($row) {
                        return $row->first_name . ' ' . $row->last_name;
                    })
                    ->addColumn('net_amount', function ($row) use ($tab) {
                        $amount = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->when($tab === 'completed', fn($q) => $q->where('status', 'completed'))
                            ->when($tab === 'pending', fn($q) => $q->where('status', '!=', 'completed'))
                            ->sum('amount');
                        return '₹ ' . indianNumberFormat($amount);
                    })
                    ->addColumn('tds_amount', function ($row) use ($tab) {
                        $settlementIds = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->when($tab === 'completed', fn($q) => $q->where('status', 'completed'))
                            ->when($tab === 'pending', fn($q) => $q->where('status', '!=', 'completed'))
                            ->pluck('id');
                        $tdsAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->sum('tds');
                        return '₹ ' . indianNumberFormat($tdsAmount);
                    })
                    ->addColumn('payout_amount', function ($row) use ($tab) {
                        $settlementIds = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->when($tab === 'completed', fn($q) => $q->where('status', 'completed'))
                            ->when($tab === 'pending', fn($q) => $q->where('status', '!=', 'completed'))
                            ->pluck('id');
                        $tdsAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->sum('tds');
                        $amount = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->when($tab === 'completed', fn($q) => $q->where('status', 'completed'))
                            ->when($tab === 'pending', fn($q) => $q->where('status', '!=', 'completed'))
                            ->sum('amount');
                        return '₹ ' . indianNumberFormat($amount - $tdsAmount);
                    })
                    ->addColumn('remaining_amount', function ($row) use ($tab) {
                        $settlementIds = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->when($tab === 'completed', fn($q) => $q->where('status', 'completed'))
                            ->when($tab === 'pending', fn($q) => $q->where('status', '!=', 'completed'))
                            ->pluck('id');
                        $tdsAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->sum('tds');
                        $amount = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->when($tab === 'completed', fn($q) => $q->where('status', 'completed'))
                            ->when($tab === 'pending', fn($q) => $q->where('status', '!=', 'completed'))
                            ->sum('amount');
                        $paidAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->where('payment_status', 'Success')
                            ->sum('amount');
                        $totalAmount = round($amount - $tdsAmount - $paidAmount, 2);
                        return '₹ ' . indianNumberFormat($totalAmount < 0 ? 0 : $totalAmount);
                    })
                    ->addColumn('advance_amount', function ($row) {
                        $advance = DB::table('advances')->where('user_id', $row->id)->first();
                        return '₹ ' . indianNumberFormat($advance->advance_amount ?? 0);
                    })
                    ->addColumn('paid_amount', function ($row) use ($tab) {
                        $settlementIds = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->when($tab === 'completed', fn($q) => $q->where('status', 'completed'))
                            ->when($tab === 'pending', fn($q) => $q->where('status', '!=', 'completed'))
                            ->pluck('id');
                        $paidAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->where('payment_status', 'Success')
                            ->sum('amount');
                        return '₹ ' . indianNumberFormat($paidAmount);
                    })
                    ->addColumn('action', function ($row) use ($tab) {
                        $btn = '';
                        if (auth()->user()->hasPermission('application', 'view')) {
                            if ($tab === 'completed') {
                                $btn = "<img onclick=\"window.location.href='" . url('/settlement/summary/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "' style='cursor:pointer;' title='View Summary'>";
                            } else {
                                $btn = "<img onclick=\"window.location.href='" . url('/settlement?p=' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "' style='cursor:pointer;' title='View Details'>";
                            }
                        }
                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            $tdsPercentage = Settings::where('name', 'TDS')->first()->value ?? 2;
            return view('Frontend.Settlement.index', compact('Route', 'settlements', 'p', 'tdsPercentage'));
        }
    }

    public function filter(Request $request)
    {
        $Route = 'Application';
        $user = Auth::user();

        $query = Settlement::query();

        if ($user->roles[0]->id == 1) {
        } else if ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $query->where('user_id', Auth::id());
        } else {
            $channel_assign = StaffAssign::where('user_id', Auth::id())->value('channel_sales_id');
            $channel_assign = json_decode($channel_assign, true);
            $query->whereIn('user_id', $channel_assign);
        }

        if ($request->status !== null) {
            $query->where('status', $request->status);
        }
        if ($request->from_date !== null) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date !== null) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $settlements = $query->get();
        return view('Frontend.Settlement.Table.settlement_table', compact('Route', 'settlements'));
    }

    public function edit($id)
    {
        $Route = 'Edit Settlement';
        $settlement = Settlement::findOrFail($id);

        // Get all distributions (application-level breakdown) for this settlement
        $settlement_distributions = SettlementDistribution::where('settlement_id', $id)->get();

        $banks = BankData::where('user_id', $settlement->user_id)->where('status', 1)->get();

        // Get the channel user info
        $channelUser = User::find($settlement->user_id);

        $tdsPercentage = Settings::where('name', 'TDS')->first()->value ?? 2;
        return view('Frontend.Settlement.edit', compact('Route', 'settlement', 'banks', 'settlement_distributions', 'channelUser', 'tdsPercentage'));
    }

    public function update(Request $request, $id)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'amount' => 'required',
            'status' => 'required',
        ]);

        $settlement = Settlement::findOrFail($id);

        // Update settlement data
        $settlement->amount = $request->amount;
        $settlement->status = $request->status;

        // If status is completed, validate and save settlement date
        if ($request->status == 'completed') {
            $request->validate([
                'settlement_date' => 'required|date',
            ]);
            $settlement->settlement_date = $request->settlement_date;
        }

        $settlement->save();

        // Recalculate or recreate bank distributions if status is 'pending' or 'bankPending'
        if (in_array($request->status, ['pending', 'bankPending'])) {
            $bankAccounts = [];

            if ($request->has('bank') && is_array($request->bank)) {
                $bankAccounts = $request->bank;
            } else {
                $existingDistributions = SettlementDistribution::where('settlement_id', $settlement->id)->get();
                if ($existingDistributions->isNotEmpty()) {
                    $bankAccounts = $existingDistributions->pluck('bank_account_id')->unique()->filter()->toArray();
                }
            }

            // Update bank_account_id on existing distributions if bank accounts provided
            if (!empty($bankAccounts) && $request->has('bank') && is_array($request->bank)) {
                $distributions = SettlementDistribution::where('settlement_id', $settlement->id)->get();
                foreach ($distributions as $index => $dist) {
                    if (isset($bankAccounts[$index % count($bankAccounts)])) {
                        $dist->bank_account_id = $bankAccounts[$index % count($bankAccounts)];
                        $dist->save();
                    }
                }
            }
        }

        // Redirect with success message
        $p = Session::get('channel_url');
        if ($p) {
            return redirect()->to('/settlement?p=' . $p)->with('success', 'Settlement updated successfully');
        } else {
            return redirect()->to('/settlement')->with('success', 'Settlement updated successfully');
        }
    }

    public function exportSettlement()
    {
        return Excel::download(new SettlementExport(), 'settlement.xlsx');
    }

    public function show($id)
    {
        $Route = 'View Settlement';
        $settlement = Settlement::findOrFail($id);

        // Get all distributions (application-level breakdown) for this settlement
        $settlement_distributions = SettlementDistribution::where('settlement_id', $id)->get();

        $banks = BankData::where('user_id', $settlement->user_id)->where('status', 1)->get();

        // Get the channel user info
        $channelUser = User::find($settlement->user_id);

        $tdsPercentage = Settings::where('name', 'TDS')->first()->value ?? 2;
        return view('Frontend.Settlement.show', compact('Route', 'settlement', 'banks', 'settlement_distributions', 'channelUser', 'tdsPercentage'));
    }


    /**
     * Get parent channels that have settlements (for admin list view).
     * @param string $tab 'pending' or 'completed' - filters by settlement status
     */
    private function getParentChannelQuery($user, $tab = 'pending')
    {
        // Get user IDs filtered by settlement status
        if ($tab === 'completed') {
            $userIdsWithSettlements = Settlement::where('status', 'completed')->pluck('user_id')->unique()->toArray();
        } else {
            $userIdsWithSettlements = Settlement::where('status', '!=', 'completed')->pluck('user_id')->unique()->toArray();
        }

        $roleId = $user->roles[0]->id;

        if (in_array($roleId, [1, 35, 36])) {
            // Admin, Maker, Checker: show all parent channels with settlements
            return User::whereIn('id', $userIdsWithSettlements);
        } else {
            // Staff: show only assigned channels with settlements
            $channelAssign = StaffAssign::where('user_id', $user->id)->value('channel_sales_id');
            $channelAssign = json_decode($channelAssign, true);
            if (!is_array($channelAssign) || empty($channelAssign)) {
                return User::whereRaw('1 = 0'); // empty query
            }
            return User::whereIn('id', $channelAssign)->whereIn('id', $userIdsWithSettlements);
        }
    }

    private function getSettlementData($user, $p)
    {
        $roleId = $user->roles[0]->id;

        if (in_array($roleId, [1, 35, 36])) {
            // Admin, Maker, Checker: see all parent channels with settlements
            if ($p) {
                $data = Settlement::where('user_id', $p)->paginate(25);
                $data->appends(['p' => $p]);
            } else {
                // Only parent channels that have settlements
                $userIdsWithSettlements = Settlement::pluck('user_id')->unique()->toArray();
                $data = User::whereIn('id', $userIdsWithSettlements)->paginate(25);
            }
        } elseif (in_array($roleId, [2, 3])) {
            $data = Settlement::where('user_id', $user->id)->where('status', '!=', 'checker')->paginate(25);
        } else {
            $channelAssign = StaffAssign::where('user_id', $user->id)->value('channel_sales_id');
            $channelAssign = json_decode($channelAssign, true);
            if (!is_array($channelAssign) || empty($channelAssign)) {
                $data = collect([]);
            } else {
                if ($p) {
                    $data = Settlement::where('user_id', $p)->paginate(25);
                    $data->appends(['p' => $p]);
                } else {
                    $userIdsWithSettlements = Settlement::pluck('user_id')->unique()->toArray();
                    $data = User::whereIn('id', $channelAssign)->whereIn('id', $userIdsWithSettlements)->paginate(25);
                }
            }
        }
        return $data;
    }

    public function uploadView()
    {
        $Route = 'Settlement';
        return view('Frontend.Settlement.upload', compact('Route'));
    }

    public function storeExcel(Request $request)
    {
        // Validate that the file is XLSX format
        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx'
        ]);

        $expectedHeaders = [
            "File_Sequence_Num",
            "Pymt_Prod_Type_Code",
            "Pymt_Mode",
            "Debit_Acct_no",
            "Beneficiary Name",
            "Beneficiary Account No",
            "Bene_IFSC_Code",
            "Amount",
            "Debit narration",
            "Credit narration",
            "Mobile Number",
            "Email id",
            "Remark",
            "Pymt_Date",
            "Reference_no",
            "Addl_Info1",
            "Addl_Info2",
            "Addl_Info3",
            "Addl_Info4",
            "Addl_Info5",
            "Beneficiary LEI",
            "STATUS",
            "Current Step",
            "File name",
            "Rejected by",
            "Rejection Reason",
            "Acct_Debit_date",
            "Customer Ref No",
            "UTR NO"
        ];
        $userId = Auth::id();
        $file = $request->file('xlsx_file');
        $tempFilePath = $file->storeAs('tmp', 'uploaded.xlsx');

        $excel = SimpleExcelReader::create(storage_path('app/' . $tempFilePath));
        $rows = $excel->getRows()->toArray();

        $fileHeaders = array_keys($rows[0]);

        if ($fileHeaders !== $expectedHeaders) {
            return redirect()->back()->withErrors(['xlsx_file' => 'The file headers do not match the expected headers.']);
        }

        foreach ($rows as $row) {

            $reference_nos = json_decode($row['Reference_no'], true);

            if (!is_array($reference_nos)) {
                continue;
            }

            foreach ($reference_nos as $reference_no) {
                $distribution = SettlementDistribution::where('id', $reference_no)->first();
                if (!is_null($distribution)) {

                    $originalDate = $row['Pymt_Date'];
                    $date = DateTime::createFromFormat('d-m-Y', $originalDate);
                    $formattedDate = $date ? $date->format('Y-m-d') : null;

                    $distribution->utr_number = $row['UTR NO'];
                    $distribution->payment_status = $row['STATUS'];
                    $distribution->rejection_reason = $row['Rejection Reason'];
                    $distribution->file_name = $row['File name'];
                    $distribution->save();

                    // Check if all distributions for this settlement are completed
                    $settlement = Settlement::find($distribution->settlement_id);
                    if ($settlement) {
                        $allDistributions = SettlementDistribution::where('settlement_id', $settlement->id)->get();
                        $allSuccess = $allDistributions->every(fn($d) => $d->payment_status == 'Success');

                        $settlement->update([
                            'status' => $allSuccess ? 'completed' : 'pending',
                            'settlement_date' => $formattedDate
                        ]);
                    }
                }
            }
        }

        return redirect()->to('/settlement')->with('success', 'Settlement status updated successfully');
    }

    /**
     * Show completed settlement summary with all transactions for a channel.
     */
    public function settlementSummary($userId)
    {
        $Route = 'Settlement Summary';
        $channelUser = User::findOrFail($userId);

        // Get all completed settlements for this channel
        $settlements = Settlement::where('user_id', $userId)
            ->where('status', 'completed')
            ->get();

        $settlementIds = $settlements->pluck('id');

        // Calculate totals from distributions
        $totalCommission = DB::table('settlement_distributions')
            ->whereIn('settlement_id', $settlementIds)
            ->sum('gross_amount');

        $totalTds = DB::table('settlement_distributions')
            ->whereIn('settlement_id', $settlementIds)
            ->sum('tds');

        $totalNetPayable = DB::table('settlement_distributions')
            ->whereIn('settlement_id', $settlementIds)
            ->sum('amount');

        // Get all transactions linked to these settlements
        $transactions = Transaction::whereIn('settlement_id', $settlementIds)
            ->orderBy('created_at', 'desc')
            ->get();

        $tdsPercentage = Settings::where('name', 'TDS')->first()->value ?? 2;

        return view('Frontend.Settlement.completed_detail', compact(
            'Route', 'channelUser', 'settlements', 'transactions',
            'totalCommission', 'totalTds', 'totalNetPayable', 'tdsPercentage'
        ));
    }

    /**
     * Show the edit page for a single settlement distribution's sharing commission.
     */
    public function editDistribution($id)
    {
        $Route = 'Edit Distribution';
        $distribution = SettlementDistribution::findOrFail($id);
        $app = Application::find($distribution->application_id);
        $settlement = Settlement::find($distribution->settlement_id);
        $channelUser = User::find($settlement->user_id);
        $tdsPercentage = Settings::where('name', 'TDS')->first()->value ?? 2;

        // Get the bank payout amount (base for commission calculation)
        $payoutAmount = 0;
        if ($app && $app->bank_mis_id) {
            $bankMis = DB::table('bank_mis')->where('id', $app->bank_mis_id)->first();
            $payoutAmount = $bankMis ? round(floatval($bankMis->payout_amount), 2) : 0;
        }

        // Get the submitter name
        $submitter = null;
        if ($app && $app->user_id) {
            $submitter = User::find($app->user_id);
        }

        return view('Frontend.Settlement.edit_distribution', compact(
            'Route', 'distribution', 'app', 'settlement', 'channelUser', 'tdsPercentage', 'submitter', 'payoutAmount'
        ));
    }

    /**
     * Update a single settlement distribution's sharing commission and recalculate amounts.
     */
    public function updateDistribution(Request $request, $id)
    {
        $request->validate([
            'received_rate' => 'required|numeric|min:0|max:100',
        ]);

        $distribution = SettlementDistribution::findOrFail($id);
        $app = Application::find($distribution->application_id);
        $settlement = Settlement::find($distribution->settlement_id);

        if (!$app || !$settlement) {
            return redirect()->back()->with('error', 'Application or settlement not found.');
        }

        $tdsPercentage = Settings::where('name', 'TDS')->first()->value ?? 2;
        $newRate = round(floatval($request->received_rate), 2);

        // Get the bank payout amount (base for commission calculation)
        $payoutAmount = 0;
        if ($app->bank_mis_id) {
            $bankMis = DB::table('bank_mis')->where('id', $app->bank_mis_id)->first();
            $payoutAmount = $bankMis ? round(floatval($bankMis->payout_amount), 2) : 0;
        }

        // Recalculate amounts: commission = payout_amount * sharing_commission% / 100
        $newCommission = round($payoutAmount * $newRate / 100, 2);
        $newTds = round($newCommission * $tdsPercentage / 100, 2);
        $newNet = round($newCommission - $newTds, 2);

        DB::beginTransaction();
        try {
            // Update distribution
            $distribution->update([
                'received_rate' => $newRate,
                'gross_amount' => $newCommission,
                'tds' => $newTds,
                'amount' => $newNet,
            ]);

            // Update application sharing_commission
            $app->sharing_commission = $newRate;
            $app->save();

            // Recalculate settlement totals
            $allDistributions = SettlementDistribution::where('settlement_id', $settlement->id)->get();
            $settlement->amount = $allDistributions->sum('gross_amount'); // total commission
            // settlement.gross_amount (bank payout total) stays unchanged
            $settlement->save();

            DB::commit();

            return redirect('/settlement?p=' . $settlement->user_id)
                ->with('success', 'Distribution updated successfully. Sharing commission changed to ' . $newRate . '%.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating distribution: ' . $e->getMessage());
        }
    }
}
