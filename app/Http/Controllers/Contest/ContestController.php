<?php

namespace App\Http\Controllers\Contest;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Bank;
use App\Models\ChannelUser;
use App\Models\ContestMis;
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

    public function index(Request $request)
    {
        $this->ensureViewPermission();

        if ($request->ajax()) {
            $query = ContestMis::with('bank');
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
                ->editColumn('application_no', fn($row) => $row->application_no ?? '-')
                ->editColumn('bank_name', function ($row) {
                    return $row->bank->name ?? '-';
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
                    $class = strtolower($related['status_text']) === 'payout completed'
                        ? 'status-buttons completed'
                        : 'status-buttons pending';
                    return '<button class="' . $class . '">' . e($related['status_text']) . '</button>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    if (auth()->user()->hasPermission('contest', 'view')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/contest/view/' . $row->id) . "'\" src='" . asset('assets/images/eye-icon.svg') . "'>";
                    }

                    if (auth()->user()->hasPermission('contest', 'update')) {
                        $btn .= "<img onclick=\"window.location.href='" . url('/contest/edit/' . $row->id) . "'\" src='" . asset('assets/images/Edit.svg') . "'>";
                    }

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
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

                if (ContestMis::where('application_no', $applicationNo)->exists()) {
                    $duplicateAppNos[] = $applicationNo;
                    continue;
                }

                $contestMis = new ContestMis();
                $contestMis->bank_id = (int) $request->bank_id;
                $contestMis->application_no = $applicationNo;
                $contestMis->location = $this->nullableString($row[$headerLookup['LOCATION']] ?? null);
                $contestMis->disbursement_date = $this->parseExcelDate($row[$headerLookup['DISBURSEMENT DATE']] ?? null);
                $contestMis->customer_name = $this->nullableString($row[$headerLookup['CUSTOMER NAME']] ?? null);
                $contestMis->loan_amt = $this->parseDecimal($row[$headerLookup['LOAN AMT']] ?? null);
                $contestMis->contest_rate = $this->parseDecimal($row[$headerLookup['CONTEST RATE']] ?? null);
                $contestMis->contest_amt = $this->parseDecimal($row[$headerLookup['CONTEST AMT']] ?? null);
                $contestMis->payment_status = 'pending';
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
            'payment_status' => 'nullable|in:pending,completed',
        ]);

        $contestMis = ContestMis::findOrFail($id);
        $contestMis->application_no = trim($request->application_no);
        $contestMis->location = $request->location;
        $contestMis->disbursement_date = $request->disbursement_date;
        $contestMis->customer_name = $request->customer_name;
        $contestMis->loan_amt = $request->loan_amt;
        $contestMis->contest_rate = $request->contest_rate;
        $contestMis->contest_amt = $request->contest_amt;
        $contestMis->payment_status = $request->payment_status ?? 'pending';
        $contestMis->save();

        return redirect()->to('/contest')->with('success', 'Contest MIS updated successfully.');
    }

    private function ensureViewPermission(): void
    {
        if (!auth()->user()->hasPermission('contest', 'view')) {
            abort(403);
        }
    }

    private function ensureUpdatePermission(): void
    {
        if (!auth()->user()->hasPermission('contest', 'update')) {
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
                'status_text' => $manualCompleted ? 'Payout Completed' : 'Payout Pending',
            ];
        }

        $application = Application::with(['user', 'parentChannel', 'bank'])
            ->where('app_id', $applicationNo)
            ->first();

        if (!$application) {
            return $this->applicationCache[$cacheKey] = [
                'channel_name' => '-',
                'parent_name' => '-',
                'status_text' => $manualCompleted ? 'Payout Completed' : 'Payout Pending',
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

        $isApplicationCompleted = strtolower((string) $application->status) === 'completed';
        $statusText = ($manualCompleted || $isApplicationCompleted)
            ? 'Payout Completed'
            : 'Payout Pending';

        return $this->applicationCache[$cacheKey] = [
            'channel_name' => $channelName,
            'parent_name' => $parentName,
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
}
