<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Report\Concerns\InteractsWithReportData;
use App\Models\Application;
use App\Models\Bank;
use App\Models\Product;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BankMisPendingReportController extends Controller
{
    use InteractsWithReportData;

    public function index(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = Application::query()->with(['user', 'bank', 'product'])
            ->where(function ($q) {
                $q->whereNull('bank_mis_id')
                    ->orWhere('status', 'pending')
                    ->orWhere('app_id_is_matched', 0)
                    ->orWhere('disburse_amount_is_matched', 0);
            });

        $this->applyUserScope($query, 'user_id', $visibleUserIds);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $this->applyDateRangeFilter($query, 'created_at', $request->from_date, $request->to_date);

        $query->latest();

        if ($request->ajax()) {
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('channel_partner_name', function ($row) {
                    return $row->user ? trim(($row->user->first_name ?? '') . ' ' . ($row->user->last_name ?? '')) : '-';
                })
                ->addColumn('bank_name', function ($row) {
                    return $row->bank->name ?? '-';
                })
                ->addColumn('product_name', function ($row) {
                    return $row->product->name ?? '-';
                })
                ->make(true);
        }

        $users = $this->reportUsersForFilter($visibleUserIds);
        $banks = Bank::query()->orderBy('name')->get();
        $products = Product::query()->orderBy('name')->get();

        return view('Frontend.Report.bank-mis-pending.index', compact('users', 'banks', 'products'));
    }

    public function filter(Request $request)
    {
        return $this->index($request);
    }

    public function export(Request $request)
    {
        $user = $this->authenticatedUser();
        $visibleUserIds = $this->visibleUserIds($user);

        $query = Application::query()->with(['user', 'bank', 'product'])
            ->where(function ($q) {
                $q->whereNull('bank_mis_id')
                    ->orWhere('status', 'pending')
                    ->orWhere('app_id_is_matched', 0)
                    ->orWhere('disburse_amount_is_matched', 0);
            });

        $this->applyUserScope($query, 'user_id', $visibleUserIds);

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('bank_id')) {
            $query->where('bank_id', $request->bank_id);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $this->applyDateRangeFilter($query, 'created_at', $request->from_date, $request->to_date);

        $rows = $query->latest()->get()->values()->map(function ($record, $index) {
            return [
                $index + 1,
                $record->user ? trim(($record->user->first_name ?? '') . ' ' . ($record->user->last_name ?? '')) : '-',
                $record->bank->name ?? '-',
                $record->product->name ?? '-',
            ];
        });

        return $this->csvDownloadResponse('bank-mis-pending-report.csv', [
            'S.NO',
            'CHANNEL PARTNER NAME',
            'BANK NAME',
            'PRODUCT',
        ], $rows);
    }
}
