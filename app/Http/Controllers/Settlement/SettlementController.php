<?php

namespace App\Http\Controllers\Settlement;

use App\Exports\SettlementExport;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\BankData;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\StaffAssign;
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
        if ((Auth::user()->roles[0]->id == 2 || Auth::user()->roles[0]->id == 3) || $p) {
            $settlements = $this->getSettlementData($user, $p);
            if ($request->ajax()) {
                $settlement = $this->getSettlementData($user, $p);
                $query = $settlement->toQuery();

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

                if ($request->status) {
                    $query->where('status', $request->status);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('app_id', function ($row) {
                        $application = DB::table('applications')->where('id', $row->application_id)->first();
                        return $application ? $application->app_id : 'N/A';
                    })
                    ->addColumn('customer_name', function ($row) {
                        $application = DB::table('applications')->where('id', $row->application_id)->first();
                        return $application->customer_name ?? '-';
                    })
                    ->editColumn('received_rate', function ($row) {
                        return $row->received_rate;
                    })
                    ->addColumn('tds_amount', function ($row) {
                        return '₹ ' . indianNumberFormat(DB::table('settlement_distributions')->where('settlement_id', $row->id)->sum('tds'));
                    })
                    ->editColumn('amount', function ($row) {
                        return $row->amount;
                    })
                    ->editColumn('status', function ($row) {
                        $status = $row->status ?? '-';
                        $statusClass = strtolower($status);
                        $statusText = ucwords($status);
                        return '<button class="status-buttons ' . $statusClass . '">' . $statusText . '</button>';
                    })
                    ->addColumn('action', function ($row) {
                        $buttons = '';
                        if (auth()->user()->hasPermission('settlement', 'view')) {
                            $buttons .= '<img onclick="window.location.href=\'' . url('/settlement/view/' . $row->id) . '\'" src="' . asset('assets/images/eye-icon.svg') . '">';
                        }
                        if (auth()->user()->hasPermission('settlement', 'update')) {
                            $buttons .= '<img onclick="window.location.href=\'' . url('/settlement/update/' . $row->id) . '\'" src="' . asset('assets/images/Edit.svg') . '">';
                        }
                        return $buttons;
                    })
                    ->rawColumns(['status', 'action'])
                    ->make(true);
            }
            return view('Frontend.Settlement.userView', compact('Route', 'settlements', 'p'));
        } else {
            if ($request->ajax()) {

                $settlement = $this->getSettlementData($user, $p);
                $query = $settlement->toQuery();

                if ($request->first_name) {
                    $query->where('first_name', $request->first_name);
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('first_name', function ($row) {
                        return $row->first_name . '' . $row->last_name;
                    })
                    ->addColumn('net_amount', function ($row) {
                        $amount = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->sum('amount');
                        return '₹ ' . indianNumberFormat($amount);
                    })
                    ->addColumn('tds_amount', function ($row) {
                        $settlementIds = DB::table('settlements')->where('user_id', $row->id)
                            ->pluck('id');
                        $tdsAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->sum('tds');

                        return '₹ ' . indianNumberFormat($tdsAmount);
                    })
                    ->addColumn('payout_amount', function ($row) {
                        $settlementIds = DB::table('settlements')->where('user_id', $row->id)
                            ->pluck('id');
                        $tdsAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->sum('tds');
                        $amount = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->sum('amount');
                        return '₹ ' . indianNumberFormat($amount - $tdsAmount);
                    })
                    ->addColumn('remaining_amount', function ($row) {
                        $settlementIds = DB::table('settlements')->where('user_id', $row->id)
                            ->pluck('id');
                        $tdsAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->sum('tds');
                        $amount = DB::table('settlements')
                            ->where('user_id', $row->id)
                            ->sum('amount');
                        $paidAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->where('payment_status', 'Success')
                            ->sum('amount');
                        $totalAmount = round($amount - $tdsAmount - $paidAmount, 2);
                        return '₹ ' . indianNumberFormat($totalAmount < 0 ? 0 : $totalAmount);
                    })
                    ->addColumn('paid_amount', function ($row) {
                        $settlementIds = DB::table('settlements')->where('user_id', $row->id)
                            ->pluck('id');
                        $paidAmount = DB::table('settlement_distributions')
                            ->whereIn('settlement_id', $settlementIds)
                            ->where('payment_status', 'Success')
                            ->sum('amount');
                        return '₹ ' . indianNumberFormat($paidAmount);
                    })
                    ->addColumn('action', function ($row) {
                        $btn = '';

                        if (auth()->user()->hasPermission('application', 'view')) {
                            $btn = "<img onclick=\"window.location.href='" . url('/settlement?p=' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                        }

                        return $btn;
                    })
                    ->rawColumns(['action'])
                    ->make(true);
            }
            return view('Frontend.Settlement.index', compact('Route', 'settlements', 'p'));
        }



        // Assuming you want to pass all settlements to the view
        // $settlements = Settlement::all();

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

        // Execute the query and fetch results
        $settlements = $query->get();
        return view('Frontend.Settlement.Table.settlement_table', compact('Route', 'settlements'));
    }

    public function edit($id)
    {
        $Route = 'Edit Settlement';
        // Retrieve the staff member by ID
        $settlement = Settlement::findOrFail($id);

        $application = Application::findOrFail($settlement->application_id);
        $settlement_distributions = SettlementDistribution::where('settlement_id', $id)->where('user_id', $settlement->user_id)->get();

        $banks = BankData::where('user_id', $settlement->user_id)->where('status', 1)->get();
        // Pass the staff member data to the edit view
        return view('Frontend.Settlement.edit', compact('Route', 'application', 'settlement', 'banks', 'settlement_distributions'));
    }

    public function update(Request $request, $id)
    {
        // Validate the form data
        $validatedData = $request->validate([
            'received_rate' => 'required',
            'amount' => 'required',
            'status' => 'required',
        ]);

        // Find the application by ID
        $settlement = Settlement::findOrFail($id);

        // Update settlement data
        $settlement->received_rate = $request->received_rate;
        $settlement->amount = $request->amount;
        $settlement->status = $request->status;

        // If status is completed, validate and save settlement date
        if ($request->status == 'completed') {
            $request->validate([
                'settlement_date' => 'required|date',
            ]);
            $settlement->settlement_date = $request->settlement_date;
        }

        // Save updated settlement
        $settlement->save();

        // Recalculate or recreate distribution if status is 'pending' or 'bankPending'
        if (in_array($request->status, ['pending', 'bankPending'])) {
            // Prefer request->bank if present, otherwise use existing distribution banks
            $bankAccounts = [];

            if ($request->has('bank') && is_array($request->bank)) {
                $bankAccounts = $request->bank;
            } else {
                // Get existing bank account IDs from previous distribution
                $existingDistributions = SettlementDistribution::where('settlement_id', $settlement->id)->get();
                if ($existingDistributions->isNotEmpty()) {
                    $bankAccounts = $existingDistributions->pluck('bank_account_id')->toArray();
                }
            }

            // Proceed if bank accounts exist
            if (!empty($bankAccounts)) {
                // Delete previous distributions
                SettlementDistribution::where('settlement_id', $settlement->id)->delete();

                $bankCount = count($bankAccounts);
                $totalAmount = $settlement->amount;

                foreach ($bankAccounts as $index => $bankAccountId) {
                    if ($bankAccountId) {
                        // Example logic: equally divide amount
                        $shareAmount = round($totalAmount / $bankCount, 2);
                        $tds = round($shareAmount * 0.02, 2); // 10% TDS example
                        $receiveAmount = $shareAmount - $tds;

                        SettlementDistribution::create([
                            'settlement_id' => $settlement->id,
                            'user_id' => $settlement->user_id,
                            'bank_account_id' => $bankAccountId,
                            'amount' => $receiveAmount,
                            'tds' => $tds,
                            'utr_number' => $request->utr_number[$index] ?? null,
                        ]);
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
        $Route = 'Edit Settlement';
        // Retrieve the staff member by ID
        $settlement = Settlement::findOrFail($id);

        $application = Application::findOrFail($settlement->application_id);
        $settlement_distributions = SettlementDistribution::where('settlement_id', $id)->where('user_id', $settlement->user_id)->get();

        $banks = BankData::where('user_id', $settlement->user_id)->where('status', 1)->get();
        // Pass the staff member data to the edit view
        return view('Frontend.Settlement.show', compact('Route', 'application', 'settlement', 'banks', 'settlement_distributions'));
    }


    private function getSettlementData($user, $p)
    {
        $settlementsData = [];

        if ($user->roles[0]->id == 1) {
            if ($p) {
                $data = Settlement::where('user_id', $p)->paginate(25);
                $data->appends(['p' => $p]);
            } else {
                $data = User::whereIn('user_type', ['channel', 'sales'])->paginate(25);
            }
        } elseif ($user->roles[0]->id == 2 || $user->roles[0]->id == 3) {
            $data = Settlement::where('user_id', $user->id)->where('status', '!=', 'checker')->paginate(25);
        } else {
            $channelAssign = StaffAssign::where('user_id', $user->id)->value('channel_sales_id');
            $channelAssign = json_decode($channelAssign, true);
            if (!is_array($channelAssign) || empty($channelAssign)) {
                $data = collect([]); // return empty result if nothing is assigned
            } else {
                if ($p) {
                    $data = Settlement::where('user_id', $p)->paginate(25);
                    $data->appends(['p' => $p]);
                } else {
                    $data = User::whereIn('id', $channelAssign)->paginate(25);
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
                // Skip this row or handle the error
                continue;
            }

            foreach ($reference_nos as $reference_no) {
                $settlement = SettlementDistribution::where('id', $reference_no)->first();
                if (!is_null($settlement)) {

                    $originalDate = $row['Pymt_Date'];
                    $date = DateTime::createFromFormat('d-m-Y', $originalDate);
                    $formattedDate = $date ? $date->format('Y-m-d') : null;


                    $settlement->utr_number = $row['UTR NO'];
                    $settlement->payment_status = $row['STATUS'];
                    $settlement->rejection_reason = $row['Rejection Reason'];
                    $settlement->file_name = $row['File name'];
                    $settlement->save();


                    $settlement = Settlement::where('id', $settlement->settlement_id)->update([
                        'status' => ($row['STATUS'] == 'Success') ? 'completed' : 'pending',
                        'settlement_date' => $formattedDate
                    ]);
                }
            }
        }

        // Clean up the temporary file
        // Storage::delete('app/' . $tempFilePath);

        return redirect()->to('/settlement')->with('success', 'settlement status updated successfully');
    }
}
