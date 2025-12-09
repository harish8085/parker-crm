<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Announcement';

        if ($request->ajax()) {
            $query = Announcement::query();

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

            if ($request->title) {
                $query->where('title', 'like', '%' . $request->title . '%');
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('title', function ($row) {
                    return $row->title ? $row->title : '-';
                })
                ->editColumn('message', function ($row) {
                    if ($row->message) {
                        $cleanMessage = strip_tags($row->message);
                        return \Illuminate\Support\Str::limit($cleanMessage, 80);
                    }
                    return '-';
                })
                ->editColumn('starts_at', function ($row) {
                    return $row->starts_at ? $row->starts_at->format('d-m-Y') : '-';
                })
                ->editColumn('expires_at', function ($row) {
                    return $row->expires_at ? $row->expires_at->format('d-m-Y') : '-';
                })
                ->editColumn('is_active', function ($row) {
                    return $row->is_active ? 'Yes' : 'No';
                })
                ->editColumn('views_count', function ($row) {
                    
                    return $row->views()->count();
                })
                ->addColumn('action', function ($row) {
                    $announcement = $row;
                    return view('Frontend.Announcement.component.actions', compact('announcement'))->render();
                })
                ->rawColumns(['action'])
                ->make(true);
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
        // Validate dates in m-d-yyyy format
        $startsAt = null;
        if ($request->starts_at) {
            try {
                $startsAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->starts_at);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['starts_at' => 'Invalid start date format. Please use MM-DD-YYYY format.'])->withInput();
            }
        }
        
        // Expires at is required
        if (!$request->expires_at) {
            return redirect()->back()->withErrors(['expires_at' => 'Expiry date is required.'])->withInput();
        }
        
        $expiresAt = null;
        try {
            $expiresAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->expires_at);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['expires_at' => 'Invalid expiry date format. Please use MM-DD-YYYY format.'])->withInput();
        }
        
        // Validate expiry date is after or equal to start date
        if ($startsAt && $expiresAt && $expiresAt->lt($startsAt)) {
            return redirect()->back()->withErrors(['expires_at' => 'Expiry date must be greater than or equal to start date.'])->withInput();
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:2048',
        ], [
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Each attachment must be one of the following types: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, csv.',
            'attachments.*.max' => 'Each attachment must not be larger than 2MB.',
        ]);

        // Create the announcement
        $announcement = Announcement::create([
            'title' => $request->title,
            'message' => $request->message,
            'created_by' => Auth::id(),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_active' => $request->has('is_active'),
        ]);

        // Handle file uploads - store in announcement_attachments table
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('attachments'), $attachmentName);
                \App\Models\AnnouncementAttachment::create([
                    'announcement_id' => $announcement->id,
                    'attachment' => 'attachments/' . $attachmentName,
                    'created_by' => Auth::id(),
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        $Route = 'Announcement';
        
        // Load attachments relationship
        $announcement->load('attachments');

        return view('Frontend.Announcement.edit', compact('Route', 'announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        // Validate dates in m-d-yyyy format
        $startsAt = null;
        if ($request->starts_at) {
            try {
                $startsAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->starts_at);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['starts_at' => 'Invalid start date format. Please use MM-DD-YYYY format.'])->withInput();
            }
        }
        
        // Expires at is required
        if (!$request->expires_at) {
            return redirect()->back()->withErrors(['expires_at' => 'Expiry date is required.'])->withInput();
        }
        
        $expiresAt = null;
        try {
            $expiresAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->expires_at);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['expires_at' => 'Invalid expiry date format. Please use MM-DD-YYYY format.'])->withInput();
        }
        
        // Validate expiry date is after or equal to start date
        if ($startsAt && $expiresAt && $expiresAt->lt($startsAt)) {
            return redirect()->back()->withErrors(['expires_at' => 'Expiry date must be greater than or equal to start date.'])->withInput();
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:2048',
        ], [
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Each attachment must be one of the following types: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, csv.',
            'attachments.*.max' => 'Each attachment must not be larger than 2MB.',
        ]);

        // Update the announcement
        $announcement->update([
            'title' => $request->title,
            'message' => $request->message,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_active' => $request->has('is_active'),
        ]);

        // Handle deletion of existing attachments
        if ($request->has('delete_attachments') && !empty(trim($request->delete_attachments))) {
            $deleteIds = array_filter(array_map('trim', explode(',', $request->delete_attachments)));
            
            foreach ($deleteIds as $id) {
                if (!empty($id) && is_numeric($id)) {
                    $id = (int)$id;
                    $attachment = \App\Models\AnnouncementAttachment::where('id', $id)
                        ->where('announcement_id', $announcement->id)
                        ->first();
                    
                    if ($attachment) {
                        // Delete physical file
                        $filePath = public_path($attachment->attachment);
                        if (file_exists($filePath)) {
                            @unlink($filePath);
                        }
                        // Delete database record
                        $attachment->delete();
                    }
                }
            }
        }

        // Handle new file uploads
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('attachments'), $attachmentName);
                \App\Models\AnnouncementAttachment::create([
                    'announcement_id' => $announcement->id,
                    'attachment' => 'attachments/' . $attachmentName,
                    'created_by' => Auth::id(),
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function show($id)
    {
        $Route = 'Announcement';
        
        try {
            // Find the announcement by ID
            $announcement = Announcement::with('attachments', 'creator')->findOrFail($id);
            
            return view('Frontend.Announcement.show', compact('Route', 'announcement'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('announcements.index')->with('error', 'Announcement not found.');
        }
    }

    public function logs(Request $request, $id)
    {
        $Route = 'Announcement Logs';
        
        try {
            $announcement = Announcement::findOrFail($id);
            
            if ($request->ajax()) {
                $query = AnnouncementView::with('user')
                    ->where('announcement_id', $id);               

                if ($request->user_name) {
                    $query->whereHas('user', function ($q) use ($request) {
                        $q->where(function($subQ) use ($request) {
                            $subQ->where('first_name', 'like', '%' . $request->user_name . '%')
                                 ->orWhere('last_name', 'like', '%' . $request->user_name . '%')
                                 ->orWhere('email', 'like', '%' . $request->user_name . '%');
                        });
                    });
                }

                return DataTables::of($query)
                    ->addIndexColumn()
                    ->editColumn('user_name', function ($row) {
                        if ($row->user) {
                            $fullName = trim(($row->user->first_name ?? '') . ' ' . ($row->user->last_name ?? ''));
                            return $fullName ?: ($row->user->email ?? '-');
                        }
                        return '-';
                    })
                    ->editColumn('user_email', function ($row) {
                        return $row->user ? ($row->user->email ?? '-') : '-';
                    })
                    ->editColumn('viewed_at', function ($row) {
                        return $row->viewed_at ? $row->viewed_at->format('d-m-Y H:i:s') : '-';
                    })
                    ->rawColumns([])
                    ->make(true);
            }

            return view('Frontend.Announcement.logs', compact('Route', 'announcement'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('announcements.index')->with('error', 'Announcement not found.');
        }
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }
}


