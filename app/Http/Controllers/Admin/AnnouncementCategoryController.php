<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnnouncementCategory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AnnouncementCategoryController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Announcement Categories';

        if ($request->ajax()) {
            $query = AnnouncementCategory::query()->orderBy('id', 'desc');

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
                } elseif ($request->date == 'last_3months') {
                    $thirdLastMonthStart = $now->subMonths(2)->startOfMonth()->toDateString();
                    $lastOneMonthEnd = $now->endOfMonth()->toDateString();
                    $query = $query->whereDate('created_at', '>=', $thirdLastMonthStart)
                        ->whereDate('created_at', '<=', $lastOneMonthEnd);
                } elseif ($request->date == 'last_6months') {
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

            if ($request->name) {
                $query->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->is_active !== null && $request->is_active !== '') {
                $query->where('is_active', $request->is_active);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('name', function ($row) {
                    return $row->name ? $row->name : '-';
                })
                ->editColumn('is_active', function ($row) {
                    $checked = $row->is_active ? 'checked' : '';
                    $status = '<label class="toggle-switch">
                        <input type="checkbox" class="status-toggle" data-category-id="' . $row->id . '" ' . $checked . '>
                        <span class="toggle-slider"></span>
                    </label>';
                    return $status;
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y') : '-';
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= "<img class='edit-category-btn' data-category-id='" . $row->id . "' src='" . asset('assets/images/Edit.svg') . "' alt='edit' style='cursor: pointer; margin-right: 10px;'>";
                    $btn .= "<img class='delete-category-btn' data-category-id='" . $row->id . "' src='" . asset('assets/images/delete-icon.svg') . "' alt='delete' style='cursor: pointer;'>";
                    return $btn;
                })
                ->rawColumns(['action', 'is_active'])
                ->make(true);
        }

        return view('Frontend.AnnouncementCategory.index', compact('Route'));
    }

    public function create()
    {
        $Route = 'Announcement Categories';
        return view('Frontend.AnnouncementCategory.create', compact('Route'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:announcement_categories,name',
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'Category name is required.',
                'name.unique' => 'Category name already exists.',
            ]);

            AnnouncementCategory::create([
                'name' => $request->name,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // Return JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Announcement category created successfully.'
                ]);
            }

            return redirect()->route('announcement-categories.index')->with('success', 'Announcement category created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON validation errors for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    public function edit(AnnouncementCategory $announcementCategory)
    {
        // Return JSON for AJAX requests (modal)
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'id' => $announcementCategory->id,
                'name' => $announcementCategory->name,
                'is_active' => $announcementCategory->is_active ? 1 : 0,
            ]);
        }
        
        // Return view for regular requests
        $Route = 'Announcement Categories';
        return view('Frontend.AnnouncementCategory.edit', compact('Route', 'announcementCategory'));
    }

    public function update(Request $request, AnnouncementCategory $announcementCategory)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:announcement_categories,name,' . $announcementCategory->id,
                'is_active' => 'nullable|boolean',
            ], [
                'name.required' => 'Category name is required.',
                'name.unique' => 'Category name already exists.',
            ]);

            $announcementCategory->update([
                'name' => $request->name,
                'is_active' => $request->has('is_active') ? true : false,
            ]);

            // Return JSON for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Announcement category updated successfully.'
                ]);
            }

            return redirect()->route('announcement-categories.index')->with('success', 'Announcement category updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON validation errors for AJAX requests
            if ($request->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, AnnouncementCategory $announcementCategory)
    {
        $announcementCategory->delete();

        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Announcement category deleted successfully.'
            ]);
        }

        return redirect()->route('announcement-categories.index')->with('success', 'Announcement category deleted successfully.');
    }

    public function toggleStatus(Request $request, AnnouncementCategory $announcementCategory)
    {
        $announcementCategory->is_active = !$announcementCategory->is_active;
        $announcementCategory->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Category status updated successfully.',
            'is_active' => $announcementCategory->is_active
        ]);
    }
}

