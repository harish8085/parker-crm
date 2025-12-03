<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Announcement';

        if ($request->ajax()) {
            $query = Announcement::query();

            if ($request->date) {
                $now = now();
                if ($request->date == 'today') {
                    $today = $now->toDateString();
                    $query->whereDate('created_at', $today);
                } elseif ($request->date == 'yesterday') {
                    $yesterday = $now->copy()->subDay()->toDateString();
                    $query->whereDate('created_at', $yesterday);
                } elseif ($request->date == 'this_week') {
                    $query->whereBetween('created_at', [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()]);
                } elseif ($request->date == 'last_week') {
                    $lastWeekStart = $now->copy()->subWeek()->startOfWeek();
                    $lastWeekEnd = $now->copy()->subWeek()->endOfWeek();
                    $query->whereBetween('created_at', [$lastWeekStart, $lastWeekEnd]);
                } elseif ($request->date == 'this_month') {
                    $query->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()]);
                } elseif ($request->date == 'last_month') {
                    $lastMonth = $now->copy()->subMonth();
                    $query->whereBetween('created_at', [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()]);
                } elseif ($request->date == 'last_3months') {
                    $start = $now->copy()->subMonths(2)->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                    $query->whereBetween('created_at', [$start, $end]);
                } elseif ($request->date == 'last_6months') {
                    $start = $now->copy()->subMonths(5)->startOfMonth();
                    $end = $now->copy()->endOfMonth();
                    $query->whereBetween('created_at', [$start, $end]);
                } elseif ($request->date == 'this_year') {
                    $query->whereBetween('created_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()]);
                } elseif ($request->date == 'last_year') {
                    $lastYear = $now->copy()->subYear();
                    $query->whereBetween('created_at', [$lastYear->copy()->startOfYear(), $lastYear->copy()->endOfYear()]);
                } elseif ($request->date == 'custom' && $request->date_range) {
                    if (strpos($request->date_range, 'to') !== false) {
                        [$startDate, $endDate] = array_map('trim', explode('to', $request->date_range));
                        $query->whereDate('created_at', '>=', $startDate)
                            ->whereDate('created_at', '<=', $endDate);
                    }
                }
            }

            if ($request->title) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            $total = $query->count();

            $start = $request->input('start', 0);
            $length = $request->input('length', 10);

            $data = $query->skip($start)->take($length)->get();

            $rows = [];
            foreach ($data as $announcement) {
                $rows[] = [
                    'title' => $announcement->title,
                    'message' => \Illuminate\Support\Str::limit($announcement->message, 80),
                    'starts_at' => optional($announcement->starts_at)->format('d-m-Y H:i'),
                    'expires_at' => optional($announcement->expires_at)->format('d-m-Y H:i'),
                    'is_active' => $announcement->is_active ? 'Yes' : 'No',
                    'views_count' => $announcement->views()->count(),
                    'action' => view('Frontend.Announcement.component.actions', compact('announcement'))->render(),
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $total,
                'recordsFiltered' => $total,
                'data' => $rows,
            ]);
        }

        return view('Frontend.Announcement.index', compact('Route'));
    }

    public function create()
    {
        $Route = 'Announcement';

        return view('Frontend.Announcement.create', compact('Route'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'required|date|after_or_equal:starts_at',
        ]);

        Announcement::create([
            'title' => $request->title,
            'message' => $request->message,
            'created_by' => Auth::id(),
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        $Route = 'Announcement';

        return view('Frontend.Announcement.edit', compact('Route', 'announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'starts_at' => 'nullable|date',
            'expires_at' => 'required|date|after_or_equal:starts_at',
        ]);

        $announcement->update([
            'title' => $request->title,
            'message' => $request->message,
            'starts_at' => $request->starts_at,
            'expires_at' => $request->expires_at,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }
}


