<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Bank;
use App\Models\BankData;
use App\Models\ChannelUser;
use App\Models\InsuranceMis;
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

class InsuranceController extends Controller
{
    private array $applicationCache = [];
    private array $insurancePayoutCache = [];

    public function index(Request $request)
    {
        $this->ensureViewPermission();

        if ($request->ajax()) {
            $query = InsuranceMis::with('bank');
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
                        ->whereColumn('applications.app_id', 'insurance_mis.application_no')
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
                    return '<input type="checkbox" class="insurance-row-checkbox" value="' . e((string) $row->id) . '">';
                })
                ->editColumn('application_no', fn($row) => $row->application_no ?? '-')
                ->editColumn('bank_name', fn($row) => $row->bank->name ?? '-')
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
                ->editColumn('insurance_rate', fn($row) => is_null($row->insurance_rate) ? '-' : rtrim(rtrim(number_format((float) $row->insurance_rate, 4, '.', ''), '0'), '.'))
                ->editColumn('insurance_amt', fn($row) => is_null($row->insurance_amt) ? '-' : rtrim(rtrim(number_format((float) $row->insurance_amt, 2, '.', ''), '0'), '.'))
                ->addColumn('status', function ($row) {
                    $related = $this->getRelatedApplication($row->application_no, $row);
                    $class = strtolower((string) $related['status_text']) === 'insurance paid'
                        ? 'status-buttons completed'
                        : 'status-buttons pending';
                    return '<button class="' . $class . '">' . e($related['status_text']) . '</button>';
                })
                ->addColumn('insurance_payout_status', function ($row) {
                    return $this->getInsurancePayoutStatusBadge((int) $row->id, (string) $row->application_no);
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $currentRoleId = (int) (auth()->user()->roles[0]->id ?? 0);

                    if (auth()->user()->hasPermission('insurance', 'view') || in_array($currentRoleId, [1, 35, 36], true)) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/insurance/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    $status = strtolower(trim((string) ($row->status ?? 'pending')));
                    $canEdit = $currentRoleId === 1
                        || ($currentRoleId === 35 && $status === 'pending')
                        || ($currentRoleId === 36 && $status === 'approved');

                    if ((auth()->user()->hasPermission('insurance', 'update') || in_array($currentRoleId, [1, 35, 36], true)) && $canEdit) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/insurance/edit/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    }

                    return $btn;
                })
                ->rawColumns(['checkbox', 'status', 'insurance_payout_status', 'action'])
                ->make(true);
        }

        $banks = Bank::orderBy('name')->get();
        $channelRoleId = 2;
        $channels = User::whereHas('roles', function ($query) use ($channelRoleId) {
            $query->where('id', $channelRoleId);
        })->orderBy('first_name')->get();

        return view('Frontend.Insurance.index', compact('banks', 'channels'));
    }

    public function uploadView()
    {
        $this->ensureUploadPermission();
        $banks = Bank::orderBy('name')->get();
        return view('Frontend.Insurance.upload', compact('banks'));
    }

    public function upload(Request $request)
    {
        $this->ensureUploadPermission();

        $request->validate([
            'xlsx_file' => 'required|file|mimes:xlsx',
            'bank_id' => 'required|exists:banks,id',
        ]);

        try {
            $file = $request->file('xlsx_file');
            $tempFilePath = $file->storeAs('tmp', 'insurance-uploaded.xlsx');
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
                'INSURANCE RATE',
                'INSURANCE AMT',
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

                if (InsuranceMis::where('application_no', $applicationNo)->exists()) {
                    $duplicateAppNos[] = $applicationNo;
                    continue;
                }

                $insuranceMis = new InsuranceMis();
                $insuranceMis->bank_id = (int) $request->bank_id;
                $insuranceMis->application_no = $applicationNo;
                $insuranceMis->location = $this->nullableString($row[$headerLookup['LOCATION']] ?? null);
                $insuranceMis->disbursement_date = $this->parseExcelDate($row[$headerLookup['DISBURSEMENT DATE']] ?? null);
                $insuranceMis->customer_name = $this->nullableString($row[$headerLookup['CUSTOMER NAME']] ?? null);
                $insuranceMis->loan_amt = $this->parseDecimal($row[$headerLookup['LOAN AMT']] ?? null);
                $insuranceMis->insurance_rate = $this->parseDecimal($row[$headerLookup['INSURANCE RATE']] ?? null);
                $insuranceMis->insurance_amt = $this->parseDecimal($row[$headerLookup['INSURANCE AMT']] ?? null);
                $insuranceMis->payment_status = 'pending';
                $insuranceMis->status = 'pending';
                $insuranceMis->sharing_insurance_commission = 50.00;
                $insuranceMis->uploaded_by = $uploadedBy;
                $insuranceMis->save();
                $successCount++;
            }

            $message = $successCount . ' records uploaded successfully.';
            if (!empty($duplicateAppNos)) {
                $message .= ' Skipped existing application no(s): ' . implode(', ', array_unique($duplicateAppNos));
            }

            return redirect()->to('/insurance')->with('success', $message);
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong while uploading Insurance MIS.'])->withInput();
        }
    }

    public function show($id)
    {
        $this->ensureViewPermission();
        $insuranceMis = InsuranceMis::findOrFail($id);
        $related = $this->getRelatedApplication($insuranceMis->application_no, $insuranceMis);

        return view('Frontend.Insurance.show', compact('insuranceMis', 'related'));
    }

    public function edit($id)
    {
        $this->ensureUpdatePermission();
        $insuranceMis = InsuranceMis::findOrFail($id);
        $related = $this->getRelatedApplication($insuranceMis->application_no, $insuranceMis);

        return view('Frontend.Insurance.edit', compact('insuranceMis', 'related'));
    }

    public function update(Request $request, $id)
    {
        $this->ensureUpdatePermission();

        $request->validate([
            'application_no' => 'required|string|max:255|unique:insurance_mis,application_no,' . $id,
            'location' => 'nullable|string|max:255',
            'disbursement_date' => 'nullable|date',
            'customer_name' => 'nullable|string|max:255',
            'loan_amt' => 'nullable|numeric',
            'insurance_rate' => 'nullable|numeric',
            'insurance_amt' => 'nullable|numeric',
            'sharing_insurance_commission' => 'nullable|numeric|min:0|max:100',
            'payment_status' => 'nullable|in:pending,completed',
            'status' => 'nullable|in:pending,approved,completed,rejected',
        ]);

        $insuranceMis = InsuranceMis::findOrFail($id);
        $oldWorkflowStatus = strtolower(trim((string) ($insuranceMis->status ?? 'pending')));
        $related = $this->getRelatedApplication($insuranceMis->application_no, $insuranceMis);
        $isInsuranceCompleted = $this->isInsurancePayoutCompleted($related['status_text'] ?? null);

        $nextStatus = strtolower(trim((string) ($request->status ?? ($insuranceMis->status ?: 'pending'))));
        $currentRoleId = (int) (auth()->user()->roles[0]->id ?? 0);
        if ($nextStatus === 'approved' && in_array($currentRoleId, [1, 35], true) && !$isInsuranceCompleted) {
            return redirect()->back()->withErrors([
                'status' => 'Approve is allowed only when Insurance Payout Status is completed.'
            ])->withInput();
        }

        $insuranceMis->application_no = trim($request->application_no);
        $insuranceMis->location = $request->location;
        $insuranceMis->disbursement_date = $request->disbursement_date;
        $insuranceMis->customer_name = $request->customer_name;
        $insuranceMis->loan_amt = $request->loan_amt;
        $insuranceMis->insurance_rate = $request->insurance_rate;
        $insuranceMis->insurance_amt = $request->insurance_amt;
        $insuranceMis->payment_status = $request->payment_status ?? 'pending';
        $insuranceMis->sharing_insurance_commission = $request->sharing_insurance_commission ?? 50.00;
        if ($nextStatus === 'rejected') {
            $nextStatus = 'pending';
        }
        $insuranceMis->status = $nextStatus;
        $insuranceMis->save();

        if ($oldWorkflowStatus !== 'completed' && $nextStatus === 'completed') {
            $this->syncInsuranceToSettlement($insuranceMis);
        }

        return redirect()->to('/insurance')->with('success', 'Insurance MIS updated successfully.');
    }

    private function ensureViewPermission(): void
    {
        $user = auth()->user();
        $roleId = (int) ($user->roles[0]->id ?? 0);
        if (!$user->hasPermission('insurance', 'view') && !in_array($roleId, [1, 35, 36], true)) {
            abort(403);
        }
    }

    private function ensureUpdatePermission(): void
    {
        $user = auth()->user();
        $roleId = (int) ($user->roles[0]->id ?? 0);
        if (!$user->hasPermission('insurance', 'update') && !in_array($roleId, [1, 35, 36], true)) {
            abort(403);
        }
    }

    private function ensureUploadPermission(): void
    {
        $user = auth()->user();
        $roleId = $user->roles[0]->id ?? null;

        $isAllowedRole = in_array($roleId, [1, 35, 36], true);
        $hasUploadPermission = $user->hasPermission('upload-mis', 'create') || $user->hasPermission('insurance', 'create');

        if (!$isAllowedRole && !$hasUploadPermission) {
            abort(403);
        }
    }

    private function getRelatedApplication(?string $applicationNo, $insuranceMis = null): array
    {
        $cacheKey = ($applicationNo ?? '') . '|' . (($insuranceMis && isset($insuranceMis->payment_status)) ? (string) $insuranceMis->payment_status : '');
        if (isset($this->applicationCache[$cacheKey])) {
            return $this->applicationCache[$cacheKey];
        }

        $manualCompleted = false;
        if ($insuranceMis && !empty($insuranceMis->payment_status)) {
            $manualCompleted = strtolower((string) $insuranceMis->payment_status) === 'completed';
        }

        if (empty($applicationNo)) {
            return $this->applicationCache[$cacheKey] = [
                'channel_name' => '-',
                'parent_name' => '-',
                'product_name' => '-',
                'status_text' => $manualCompleted ? 'Insurance Paid' : 'Insurance Pending',
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
                'status_text' => $manualCompleted ? 'Insurance Paid' : 'Insurance Pending',
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
            ? 'Insurance Paid'
            : 'Insurance Pending';

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

    private function isInsurancePayoutCompleted(?string $statusText): bool
    {
        $normalized = strtolower(trim((string) $statusText));
        return in_array($normalized, ['insurance paid', 'completed'], true);
    }

    private function syncInsuranceToSettlement(InsuranceMis $insuranceMis): void
    {
        $application = Application::where('app_id', $insuranceMis->application_no)->first();
        if (!$application) {
            return;
        }

        $exists = SettlementDistribution::where('settlement_type', 'insurance')
            ->where('application_id', $application->id)
            ->exists();
        if ($exists) {
            return;
        }

        $parentChannelId = $application->parent_channel_id ?? $application->user_id;
        if (!$parentChannelId) {
            return;
        }

        $companyReceivingRate = (float) ($insuranceMis->insurance_rate ?? 0);
        $sharingRate = (float) ($insuranceMis->sharing_insurance_commission ?? 50);
        $disbursementAmount = (float) ($insuranceMis->loan_amt ?? 0);

        $insuranceAmount = round($disbursementAmount * ($companyReceivingRate / 100), 2);
        if ($insuranceAmount <= 0 && !empty($insuranceMis->insurance_amt)) {
            $insuranceAmount = round((float) $insuranceMis->insurance_amt, 2);
        }

        $channelInsuranceAmount = round($insuranceAmount * ($sharingRate / 100), 2);
        $channelInsuranceRate = round($companyReceivingRate * ($sharingRate / 100), 4);

        $receivedRate = $sharingRate;
        $grossAmount = $channelInsuranceAmount;
        $tdsPercentage = (float) (Settings::where('name', 'TDS')->value('value') ?? 2);
        $tdsAmount = round($grossAmount * $tdsPercentage / 100, 2);
        $netAmount = round($grossAmount - $tdsAmount, 2);
        $bankDataId = BankData::where('user_id', $parentChannelId)->value('id');

        $settlement = Settlement::where('user_id', $parentChannelId)
            ->where('settlement_type', 'insurance')
            ->where('status', '!=', 'completed')
            ->first();

        if (!$settlement) {
            $settlement = Settlement::create([
                'user_id' => $parentChannelId,
                'application_id' => (string) ($application->id ?? ''),
                'settlement_type' => 'insurance',
                'received_rate' => $channelInsuranceRate,
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
            'contest_mis_id' => null,
            'settlement_type' => 'insurance',
            'rc_commission' => $insuranceAmount,
            'received_rate' => $receivedRate,
            'gross_amount' => $grossAmount,
            'tds' => $tdsAmount,
            'tds_percentage' => $tdsPercentage,
            'bank_account_id' => $bankDataId,
            'amount' => $netAmount,
        ]);
    }

    private function getInsurancePayoutStatusBadge(int $insuranceMisId, string $applicationNo): string
    {
        $cacheKey = $insuranceMisId . '|' . $applicationNo;
        if (isset($this->insurancePayoutCache[$cacheKey])) {
            return $this->insurancePayoutCache[$cacheKey];
        }

        $applicationId = Application::where('app_id', $applicationNo)->value('id');
        if (!$applicationId) {
            return $this->insurancePayoutCache[$cacheKey] = '<button class="status-buttons pending">Insurance Payout Pending</button>';
        }

        $distribution = SettlementDistribution::query()
            ->leftJoin('settlements', 'settlements.id', '=', 'settlement_distributions.settlement_id')
            ->where('settlement_distributions.settlement_type', 'insurance')
            ->where('settlement_distributions.application_id', $applicationId)
            ->orderByDesc('settlement_distributions.id')
            ->select([
                'settlement_distributions.payment_status',
                'settlements.status as settlement_status',
            ])
            ->first();

        if (!$distribution) {
            return $this->insurancePayoutCache[$cacheKey] = '<button class="status-buttons pending">Insurance Payout Pending</button>';
        }

        $settlementStatus = strtolower(trim((string) ($distribution->settlement_status ?? '')));
        $paymentStatus = strtolower(trim((string) ($distribution->payment_status ?? '')));

        if ($settlementStatus === 'completed' || $paymentStatus === 'success') {
            return $this->insurancePayoutCache[$cacheKey] = '<button class="status-buttons completed">Payout Completed</button>';
        }

        return $this->insurancePayoutCache[$cacheKey] = '<button class="status-buttons pending">Payment Pending</button>';
    }
}
