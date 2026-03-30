<?php

namespace App\Http\Controllers\Contest;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Bank;
use App\Models\BankData;
use App\Models\ChannelUser;
use App\Models\ContestMis;
use App\Models\Settlement;
use App\Models\SettlementDistribution;
use App\Models\Settings;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;
use Yajra\DataTables\Facades\DataTables;

class ContestController extends Controller
{
    private array $applicationCache = [];
    private array $contestPayoutCache = [];

    public function index(Request $request)
    {
        $this->ensureViewPermission();

        if ($request->ajax()) {
            $query = ContestMis::with('bank');
            $user = auth()->user();
            $roleId = (int) ($user->roles[0]->id ?? 0);

            if ($roleId === 35) {
                $query->where(function ($q) {
                    $q->whereNull('status')->orWhere('status', 'pending');
                });
            } elseif ($roleId === 36) {
                $query->where('status', 'approved');
            }

            $bankId = $request->bank_id;
            $channelId = $request->channel_id;
            $fromDate = $request->from_date;
            $toDate = $request->to_date;

            if (!empty($bankId)) {
                $query->where('bank_id', $bankId);
            }

            if (!empty($channelId)) {
                $associateIds = ChannelUser::where('channel_id', $channelId)->pluck('associate_channel_id')->toArray();
                $query->whereExists(function ($q) use ($channelId, $associateIds) {
                    $q->select(DB::raw(1))
                        ->from('applications')
                        ->whereColumn('applications.app_id', 'contest_mis.application_no')
                        ->where(function ($inner) use ($channelId, $associateIds) {
                            $inner->where('applications.user_id', $channelId)
                                ->orWhere('applications.parent_channel_id', $channelId);
                            if (!empty($associateIds)) {
                                $inner->orWhereIn('applications.user_id', $associateIds);
                            }
                        });
                });
            }

            if (!empty($fromDate)) {
                $query->whereDate('disbursement_date', '>=', $fromDate);
            }

            if (!empty($toDate)) {
                $query->whereDate('disbursement_date', '<=', $toDate);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="contest-row-checkbox" value="' . e((string) $row->id) . '">';
                })
                ->editColumn('application_no', fn($row) => $row->application_no ?? '-')
                ->editColumn('company_name', fn($row) => $row->company_name ?? '-')
                ->editColumn('bank_name', function ($row) {
                    return $row->bank->name ?? '-';
                })
                ->editColumn('product_name', function ($row) {
                    $related = $this->getRelatedApplication($row->application_no, $row);
                    return $related['product_name'];
                })
                ->editColumn('channel_name', function ($row) {
                    $related = $this->getRelatedApplication($row->application_no, $row);
                    return $related['channel_name'];
                })
                ->editColumn('parent_name', function ($row) {
                    $related = $this->getRelatedApplication($row->application_no, $row);
                    return $related['parent_name'];
                })
                ->editColumn('location', fn($row) => $row->location ?? '-')
                ->editColumn('disbursement_date', function ($row) {
                    return $row->disbursement_date
                        ? Carbon::parse($row->disbursement_date)->format('d/m/Y')
                        : '-';
                })
                ->editColumn('customer_name', fn($row) => $row->customer_name ?? '-')
                ->editColumn('loan_amt', fn($row) => is_null($row->loan_amt) ? '-' : rtrim(rtrim(number_format((float) $row->loan_amt, 2, '.', ''), '0'), '.'))
                ->editColumn('contest_rate', fn($row) => is_null($row->contest_rate) ? '-' : rtrim(rtrim(number_format((float) $row->contest_rate, 4, '.', ''), '0'), '.'))
                ->editColumn('contest_amt', fn($row) => is_null($row->contest_amt) ? '-' : rtrim(rtrim(number_format((float) $row->contest_amt, 2, '.', ''), '0'), '.'))
                ->addColumn('status', function ($row) {
                    $related = $this->getRelatedApplication($row->application_no, $row);
                    $class = strtolower((string) $related['status_text']) === 'commission paid'
                        ? 'status-buttons completed'
                        : 'status-buttons pending';
                    return '<button class="' . $class . '">' . e($related['status_text']) . '</button>';
                })
                ->addColumn('contest_payout_status', function ($row) {
                    return $this->getContestPayoutStatusBadge((int) $row->id);
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $currentRoleId = (int) (auth()->user()->roles[0]->id ?? 0);

                    if (auth()->user()->hasPermission('contest', 'view') || in_array($currentRoleId, [1, 35, 36], true)) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/contest/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    $status = strtolower(trim((string) ($row->status ?? 'pending')));
                    $canEdit = $currentRoleId === 1
                        || ($currentRoleId === 35 && $status === 'pending')
                        || ($currentRoleId === 36 && $status === 'approved');

                    if ((auth()->user()->hasPermission('contest', 'update') || in_array($currentRoleId, [1, 35, 36], true)) && $canEdit) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/contest/edit/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    }

                    return $btn;
                })
                ->rawColumns(['checkbox', 'status', 'contest_payout_status', 'action'])
                ->make(true);
        }

        $banks = Bank::orderBy('name')->get();
        $channelRoleId = 2;
        $channels = User::whereHas('roles', function ($query) use ($channelRoleId) {
            $query->where('id', $channelRoleId);
        })->orderBy('first_name')->get();

        return view('Frontend.Contest.index', compact('banks', 'channels'));
    }

    public function uploadView()
    {
        $this->ensureUploadPermission();
        $banks = Bank::orderBy('name')->get();
        return view('Frontend.Contest.upload', compact('banks'));
    }

    public function upload(Request $request)
    {
        $this->ensureUploadPermission();

        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx',
            'bank_id' => 'required|exists:banks,id',
            'company_name' => 'required|string',
        ]);

        try {
            $file = $request->file('xlsx_file');
            $tempFilePath = $file->storeAs('tmp', 'contest-uploaded.xlsx');
            $rows = SimpleExcelReader::create(storage_path('app/' . $tempFilePath))->getRows()->toArray();

            if (empty($rows)) {
                return redirect()->back()->withErrors(['error' => 'Uploaded file is empty.']);
            }

            $headers = array_keys($rows[0]);
            $requiredHeaders = [
                'APPLICATION NO.',
                'LOCATION',
                'DISBURSEMENT DATE',
                'CUSTOMER NAME',
                'LOAN AMT',
                'CONTEST RATE',
                'CONTEST AMT',
            ];

            $headerLookup = [];
            foreach ($headers as $header) {
                $headerLookup[strtoupper(trim($header))] = $header;
            }

            foreach ($requiredHeaders as $required) {
                if (!array_key_exists($required, $headerLookup)) {
                    return redirect()->back()->withErrors(['error' => "Missing required header: {$required}"]);
                }
            }

            $uploadedBy = Auth::id();
            $successCount = 0;
            $duplicateAppNos = [];

            foreach ($rows as $row) {
                $applicationNo = trim((string) ($row[$headerLookup['APPLICATION NO.']] ?? ''));
                if ($applicationNo === '') {
                    continue;
                }

                $resolved = $this->resolveSecuredApplicationNo($applicationNo, ContestMis::class);
                $resolvedAppNo = $resolved['application_no'];
                if (!$resolved['is_secured'] && ContestMis::where('application_no', $resolvedAppNo)->exists()) {
                    $duplicateAppNos[] = $applicationNo;
                    continue;
                }

                $contestMis = new ContestMis();
                $contestMis->bank_id = (int) $request->bank_id;
                $contestMis->company_name = $request->company_name;
                $contestMis->application_no = $resolvedAppNo;
                $contestMis->location = $this->nullableString($row[$headerLookup['LOCATION']] ?? null);
                $contestMis->disbursement_date = $this->parseExcelDate($row[$headerLookup['DISBURSEMENT DATE']] ?? null);
                $contestMis->customer_name = $this->nullableString($row[$headerLookup['CUSTOMER NAME']] ?? null);
                $contestMis->loan_amt = $this->parseDecimal($row[$headerLookup['LOAN AMT']] ?? null);
                $contestMis->contest_rate = $this->parseDecimal($row[$headerLookup['CONTEST RATE']] ?? null);
                $contestMis->contest_amt = $this->parseDecimal($row[$headerLookup['CONTEST AMT']] ?? null);
                $contestMis->payment_status = 'pending';
                $contestMis->status = 'pending';
                $contestMis->sharing_contest_commission = 50.00;
                $contestMis->uploaded_by = $uploadedBy;
                $contestMis->save();
                $successCount++;
            }

            $message = $successCount . ' records uploaded successfully.';
            if (!empty($duplicateAppNos)) {
                $message .= ' Skipped existing application no(s): ' . implode(', ', array_unique($duplicateAppNos));
            }

            return redirect()->to('/contest')->with('success', $message);
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong while uploading Contest MIS.'])->withInput();
        }
    }

    public function show($id)
    {
        $this->ensureViewPermission();
        $contestMis = ContestMis::findOrFail($id);
        $related = $this->getRelatedApplication($contestMis->application_no, $contestMis);

        return view('Frontend.Contest.show', compact('contestMis', 'related'));
    }

    public function edit($id)
    {
        $this->ensureUpdatePermission();
        $contestMis = ContestMis::findOrFail($id);
        $related = $this->getRelatedApplication($contestMis->application_no, $contestMis);

        return view('Frontend.Contest.edit', compact('contestMis', 'related'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureUpdatePermission();

        $request->validate([
            'application_no' => 'required|string|max:255|unique:contest_mis,application_no,' . $id,
            'location' => 'nullable|string|max:255',
            'disbursement_date' => 'nullable|date',
            'customer_name' => 'nullable|string|max:255',
            'loan_amt' => 'nullable|numeric',
            'contest_rate' => 'nullable|numeric',
            'contest_amt' => 'nullable|numeric',
            'sharing_contest_commission' => 'nullable|numeric|min:0|max:100',
            'payment_status' => 'nullable|in:pending,completed',
            'status' => 'nullable|in:pending,approved,completed,rejected',
        ]);

        $contestMis = ContestMis::findOrFail($id);
        $oldWorkflowStatus = strtolower(trim((string) ($contestMis->status ?? 'pending')));
        $related = $this->getRelatedApplication($contestMis->application_no, $contestMis);
        $isCommissionCompleted = $this->isCommissionPayoutCompleted($related['status_text'] ?? null);

        $nextStatus = strtolower(trim((string) ($request->status ?? ($contestMis->status ?: 'pending'))));
        $currentRoleId = (int) (auth()->user()->roles[0]->id ?? 0);
        if ($nextStatus === 'approved' && in_array($currentRoleId, [1, 35], true) && !$isCommissionCompleted) {
            return redirect()->back()->withErrors([
                'status' => 'Approve is allowed only when Commission Payout Status is completed.'
            ])->withInput();
        }

        $contestMis->application_no = trim($request->application_no);
        $contestMis->location = $request->location;
        $contestMis->disbursement_date = $request->disbursement_date;
        $contestMis->customer_name = $request->customer_name;
        $contestMis->loan_amt = $request->loan_amt;
        $contestMis->contest_rate = $request->contest_rate;
        $contestMis->contest_amt = $request->contest_amt;
        $contestMis->payment_status = $request->payment_status ?? 'pending';
        $contestMis->sharing_contest_commission = $request->sharing_contest_commission ?? 50.00;
        if ($nextStatus === 'rejected') {
            $nextStatus = 'pending';
        }
        $contestMis->status = $nextStatus;
        $contestMis->save();

        if ($oldWorkflowStatus !== 'completed' && $nextStatus === 'completed') {
            $this->syncContestToSettlement($contestMis);
        }

        return redirect()->to('/contest')->with('success', 'Contest MIS updated successfully.');
    }

    private function ensureViewPermission(): void
    {
        $user = auth()->user();
        $roleId = (int) ($user->roles[0]->id ?? 0);
        if (!$user->hasPermission('contest', 'view') && !in_array($roleId, [1, 35, 36], true)) {
            abort(403);
        }
    }

    private function ensureUpdatePermission(): void
    {
        $user = auth()->user();
        $roleId = (int) ($user->roles[0]->id ?? 0);
        if (!$user->hasPermission('contest', 'update') && !in_array($roleId, [1, 35, 36], true)) {
            abort(403);
        }
    }

    private function ensureUploadPermission(): void
    {
        $user = auth()->user();
        $roleId = $user->roles[0]->id ?? null;

        $isAllowedRole = in_array($roleId, [1, 35, 36], true);
        $hasUploadPermission = $user->hasPermission('upload-mis', 'create') || $user->hasPermission('contest', 'create');

        if (!$isAllowedRole && !$hasUploadPermission) {
            abort(403);
        }
    }

    private function resolveSecuredApplicationNo(string $applicationNo, string $misModelClass): array
    {
        $trimmed = trim($applicationNo);
        if ($trimmed === '') {
            return ['application_no' => $applicationNo, 'is_secured' => false];
        }

        $base = preg_replace('/-\d{3}$/', '', $trimmed);
        $candidates = Application::where('group', 'Secured')
            ->where(function ($q) use ($base) {
                $q->where('app_id', $base)
                    ->orWhere('app_id', 'LIKE', $base . '-%');
            })
            ->orderBy('app_id')
            ->pluck('app_id')
            ->toArray();

        if (empty($candidates)) {
            return ['application_no' => $applicationNo, 'is_secured' => false];
        }

        $used = $misModelClass::whereIn('application_no', $candidates)
            ->pluck('application_no')
            ->toArray();

        if (in_array($trimmed, $candidates, true) && !in_array($trimmed, $used, true)) {
            return ['application_no' => $trimmed, 'is_secured' => true];
        }

        foreach ($candidates as $candidate) {
            if (!in_array($candidate, $used, true)) {
                return ['application_no' => $candidate, 'is_secured' => true];
            }
        }

        return [
            'application_no' => generateUniqueAppId($base, $misModelClass, [], null, 'application_no'),
            'is_secured' => true,
        ];
    }

    private function getRelatedApplication(?string $applicationNo, $contestMis = null): array
    {
        $cacheKey = ($applicationNo ?? '') . '|' . (($contestMis && isset($contestMis->payment_status)) ? (string) $contestMis->payment_status : '');
        if (isset($this->applicationCache[$cacheKey])) {
            return $this->applicationCache[$cacheKey];
        }

        $manualCompleted = false;
        if ($contestMis && !empty($contestMis->payment_status)) {
            $manualCompleted = strtolower((string) $contestMis->payment_status) === 'completed';
        }

        if (empty($applicationNo)) {
            return $this->applicationCache[$cacheKey] = [
                'channel_name' => '-',
                'parent_name' => '-',
                'product_name' => '-',
                'status_text' => $manualCompleted ? 'Commission Paid' : 'Commission Pending',
            ];
        }

        $application = Application::with(['user', 'parentChannel', 'bank', 'product'])
            ->where('app_id', $applicationNo)
            ->first();

        if (!$application) {
            return $this->applicationCache[$cacheKey] = [
                'channel_name' => '-',
                'parent_name' => '-',
                'product_name' => '-',
                'status_text' => $manualCompleted ? 'Commission Paid' : 'Commission Pending',
            ];
        }

        $channelName = $application->user
            ? trim(($application->user->first_name ?? '') . ' ' . ($application->user->last_name ?? ''))
            : '-';

        if ($channelName === '') {
            $channelName = '-';
        }

        $parentName = '-';
        if (!empty($application->parent_channel_id) && $application->parentChannel) {
            $parentName = trim(($application->parentChannel->first_name ?? '') . ' ' . ($application->parentChannel->last_name ?? ''));
            $parentName = $parentName !== '' ? $parentName : '-';
        }

        $productName = !empty($application->product?->name)
            ? (string) $application->product->name
            : '-';

        $isApplicationCompleted = strtolower((string) $application->status) === 'completed';
        $statusText = ($manualCompleted || $isApplicationCompleted)
            ? 'Commission Paid'
            : 'Commission Pending';

        return $this->applicationCache[$cacheKey] = [
            'channel_name' => $channelName,
            'parent_name' => $parentName,
            'product_name' => $productName,
            'status_text' => $statusText,
        ];
    }

    private function parseDecimal($value): ?float
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $value = str_replace([',', ' '], '', $value);
        $value = rtrim($value, '%');

        if (!is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function parseExcelDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->format('Y-m-d');
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return null;
        }

        if (is_numeric($raw)) {
            $date = Carbon::create(1899, 12, 30)->addDays((int) $raw);
            return $date->format('Y-m-d');
        }

        $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'm/d/Y', 'm-d-Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $raw)->format('Y-m-d');
            } catch (\Throwable $th) {
            }
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $th) {
            return null;
        }
    }

    private function nullableString($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function isCommissionPayoutCompleted(?string $statusText): bool
    {
        $normalized = strtolower(trim((string) $statusText));
        return in_array($normalized, ['commission paid', 'completed'], true);
    }

    private function syncContestToSettlement(ContestMis $contestMis): void
    {
        $application = Application::where('app_id', $contestMis->application_no)->first();
        if (!$application) {
            return;
        }

        $exists = SettlementDistribution::where('contest_mis_id', $contestMis->id)
            ->where('settlement_type', 'contest')
            ->exists();
        if ($exists) {
            return;
        }

        $parentChannelId = $application->parent_channel_id ?? $application->user_id;
        if (!$parentChannelId) {
            return;
        }

        $companyReceivingRate = (float) ($contestMis->contest_rate ?? 0); // e.g. 0.10 (%)
        $sharingRate = (float) ($contestMis->sharing_contest_commission ?? 50); // e.g. 50 (%)
        $disbursementAmount = (float) ($contestMis->loan_amt ?? 0);

        // Contest Amount = Disbursement Amount * Company Receiving (%)
        $contestAmount = round($disbursementAmount * ($companyReceivingRate / 100), 2);
        if ($contestAmount <= 0 && !empty($contestMis->contest_amt)) {
            $contestAmount = round((float) $contestMis->contest_amt, 2);
        }

        // Channel Contest Amount = Contest Amount * Sharing (%)
        $channelContestAmount = round($contestAmount * ($sharingRate / 100), 2);

        // Channel Contest Rate = Company Receiving * Sharing
        $channelContestRate = round($companyReceivingRate * ($sharingRate / 100), 4);

        $receivedRate = $sharingRate;
        $grossAmount = $channelContestAmount;
        $tdsPercentage = (float) (Settings::where('name', 'TDS')->value('value') ?? 2);
        $tdsAmount = round($grossAmount * $tdsPercentage / 100, 2);
        $netAmount = round($grossAmount - $tdsAmount, 2);
        $bankDataId = BankData::where('user_id', $parentChannelId)->value('id');

        $settlement = Settlement::where('user_id', $parentChannelId)
            ->where('settlement_type', 'contest')
            ->where('status', '!=', 'completed')
            ->first();

        if (!$settlement) {
            $settlement = Settlement::create([
                'user_id' => $parentChannelId,
                'application_id' => (string) ($application->id ?? ''),
                'settlement_type' => 'contest',
                'received_rate' => $channelContestRate,
                'amount' => $grossAmount,
                'gross_amount' => $grossAmount,
                'status' => 'checker',
            ]);
        } else {
            $settlement->amount = round((float) $settlement->amount + $grossAmount, 2);
            $settlement->gross_amount = round((float) $settlement->gross_amount + $grossAmount, 2);
            $settlement->save();
        }

        SettlementDistribution::create([
            'settlement_id' => $settlement->id,
            'user_id' => $application->user_id,
            'application_id' => $application->id,
            'contest_mis_id' => $contestMis->id,
            'settlement_type' => 'contest',
            'rc_commission' => $contestAmount,
            'received_rate' => $receivedRate,
            'gross_amount' => $grossAmount,
            'tds' => $tdsAmount,
            'tds_percentage' => $tdsPercentage,
            'bank_account_id' => $bankDataId,
            'amount' => $netAmount,
        ]);
    }

    private function getContestPayoutStatusBadge(int $contestMisId): string
    {
        if (isset($this->contestPayoutCache[$contestMisId])) {
            return $this->contestPayoutCache[$contestMisId];
        }

        $distribution = SettlementDistribution::query()
            ->leftJoin('settlements', 'settlements.id', '=', 'settlement_distributions.settlement_id')
            ->where('settlement_distributions.settlement_type', 'contest')
            ->where('settlement_distributions.contest_mis_id', $contestMisId)
            ->orderByDesc('settlement_distributions.id')
            ->select([
                'settlement_distributions.payment_status',
                'settlements.status as settlement_status',
            ])
            ->first();

        if (!$distribution) {
            return $this->contestPayoutCache[$contestMisId] = '<button class="status-buttons pending">Contest Payout Pending</button>';
        }

        $settlementStatus = strtolower(trim((string) ($distribution->settlement_status ?? '')));
        $paymentStatus = strtolower(trim((string) ($distribution->payment_status ?? '')));

        if ($settlementStatus === 'completed' || $paymentStatus === 'success') {
            return $this->contestPayoutCache[$contestMisId] = '<button class="status-buttons completed">Payout Completed</button>';
        }

        return $this->contestPayoutCache[$contestMisId] = '<button class="status-buttons pending">Payment Pending</button>';
    }
}
