<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementView;
use App\Models\AnnouncementCategory;
use App\Models\Bank;
use App\Models\BankProduct;
use App\Models\Product;
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
            $query = Announcement::with(['bank', 'product', 'category'])->orderBy('id', 'desc');

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

            if ($request->announcement_category_id) {
                $query->where('announcement_category_id', $request->announcement_category_id);
            }

            if ($request->bank_id) {
                $query->where('bank_id', $request->bank_id);
            }

            if ($request->product_id) {
                $query->where('product_id', $request->product_id);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('title', function ($row) {
                    return $row->title ? $row->title : '-';
                })
                ->editColumn('category', function ($row) {
                    return $row->category ? $row->category->name : '-';
                })
                ->editColumn('bank', function ($row) {
                    return $row->bank ? $row->bank->name : '-';
                })
                ->editColumn('product', function ($row) {
                    if ($row->product) {
                        return $row->product->name . ($row->product->group ? ' (' . $row->product->group . ')' : '');
                    }
                    return '-';
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

        $banks = Bank::orderBy('name')->get();
        $categories = AnnouncementCategory::where('is_active', true)->orderBy('name')->get();
        $products = \App\Models\Product::orderBy('name')->get();

        return view('Frontend.Announcement.index', compact('Route', 'banks', 'categories', 'products'));
    }

    public function create()
    {
        $Route = 'Announcement';
        $banks = Bank::orderBy('name')->get();
        $categories = AnnouncementCategory::where('is_active', true)->orderBy('name')->get();

        return view('Frontend.Announcement.create', compact('Route', 'banks', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'message' => 'nullable|string',
            'bank_id' => 'required|exists:banks,id',
            'product_id' => 'required|exists:products,id',
            'announcement_category_id' => 'required|exists:announcement_categories,id',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:2048',
            'message_attachment' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'starts_at' => 'required',
            'expires_at' => 'nullable',
        ], [
            'title.required' => 'Title is required.',
            'title.max' => 'Title must be less than 50 characters.',
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Each attachment must be one of the following types: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, csv.',
            'attachments.*.max' => 'Each attachment must not be larger than 2MB.',
            'message_attachment.file' => 'Message attachment must be a valid file.',
            'message_attachment.mimes' => 'Message attachment must be one of the following types: jpg, jpeg, png.',
            'message_attachment.max' => 'Message attachment must not be larger than 2MB.',
            'bank_id.required' => 'Please select a bank.',
            'bank_id.exists' => 'Selected bank is invalid.',
            'product_id.required' => 'Please select a bank product.',
            'product_id.exists' => 'Selected bank product is invalid.',
            'announcement_category_id.required' => 'Please select an announcement category.',
            'announcement_category_id.exists' => 'Selected announcement category is invalid.',
            'starts_at.required' => 'Start date is required.',
            'starts_at.date' => 'Start date must be a valid date.',            
            'expires_at.date' => 'Expiry date must be a valid date.',
            
        ]);
        // Validate dates in m-d-yyyy format
        $startsAt = null;
        if ($request->starts_at) {
            try {
                $startsAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->starts_at);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['starts_at' => 'Invalid start date format. Please use MM-DD-YYYY format.'])->withInput();
            }
        }
        
       
         
        
        $expiresAt = null;
        if ($request->expires_at) {
        try {
                $expiresAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->expires_at);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['expires_at' => 'Invalid expiry date format. Please use MM-DD-YYYY format.'])->withInput();
            }
        }
        
        // Validate expiry date is after or equal to start date
        if ($startsAt && $expiresAt && $expiresAt->lt($startsAt)) {
            return redirect()->back()->withErrors(['expires_at' => 'Expiry date must be greater than or equal to start date.'])->withInput();
        }

        

        // Handle message_attachment upload
        $messageAttachmentPath = null;
        if ($request->hasFile('message_attachment')) {
            $messageAttachment = $request->file('message_attachment');
            $messageAttachmentName = time() . '_' . uniqid() . '.' . $messageAttachment->getClientOriginalExtension();
            $messageAttachment->move(public_path('uploads/attachments'), $messageAttachmentName);
            $messageAttachmentPath = 'uploads/attachments/' . $messageAttachmentName;
        }

        // Create the announcement
        $announcement = Announcement::create([
            'title' => $request->title,
            'message' => $request->message ?? null,
            'created_by' => Auth::id(),
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_active' => $request->has('is_active'),
            'message_attachment' => $messageAttachmentPath,
            'bank_id' => $request->bank_id,
            'product_id' => $request->product_id,
            'announcement_category_id' => $request->announcement_category_id,
        ]);

        // Handle file uploads - store in announcement_attachments table
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $attachmentName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/attachments'), $attachmentName);
                \App\Models\AnnouncementAttachment::create([
                    'announcement_id' => $announcement->id,
                    'attachment' => 'uploads/attachments/' . $attachmentName,
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
        $banks = Bank::orderBy('name')->get();
        $categories = AnnouncementCategory::where('is_active', true)->orderBy('name')->get();
        $bankProductIds = BankProduct::where('bank_id', $announcement->bank_id)->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $bankProductIds)->orderBy('name')->get();

        return view('Frontend.Announcement.edit', compact('Route', 'announcement', 'banks', 'categories', 'products'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $request->validate([
            'title' => 'required|string|max:50',
            'title.max' => 'Title must be less than 50 characters.',
            'message' => 'nullable|string',
            'bank_id' => 'required|exists:banks,id',
            'product_id' => 'required|exists:products,id',
            'announcement_category_id' => 'required|exists:announcement_categories,id',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv|max:2048',
            'message_attachment' => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
            'starts_at' => 'required',
            'expires_at' => 'nullable',
        ], [
            'attachments.*.file' => 'Each attachment must be a valid file.',
            'attachments.*.mimes' => 'Each attachment must be one of the following types: jpg, jpeg, png, gif, pdf, doc, docx, xls, xlsx, ppt, pptx, txt, csv.',
            'attachments.*.max' => 'Each attachment must not be larger than 2MB.',
            'message_attachment.file' => 'Message attachment must be a valid file.',
            'message_attachment.mimes' => 'Message attachment must be one of the following types: jpg, jpeg, png.',
            'message_attachment.max' => 'Message attachment must not be larger than 2MB.',
            'bank_id.required' => 'Please select a bank.',
            'bank_id.exists' => 'Selected bank is invalid.',
            'product_id.required' => 'Please select a bank product.',
            'product_id.exists' => 'Selected bank product is invalid.',
            'announcement_category_id.required' => 'Please select an announcement category.',
            'announcement_category_id.exists' => 'Selected announcement category is invalid.',
            'starts_at.required' => 'Start date is required.',
            'starts_at.date' => 'Start date must be a valid date.',
            'expires_at.date' => 'Expiry date must be a valid date.',
        ]);
        // Validate dates in m-d-yyyy format
        $startsAt = null;
        if ($request->starts_at) {
            try {
                $startsAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->starts_at);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['starts_at' => 'Invalid start date format. Please use MM-DD-YYYY format.'])->withInput();
            }
        }
        
         
        
        $expiresAt = null;
        if ($request->expires_at) {
        try {
                $expiresAt = \Carbon\Carbon::createFromFormat('m-d-Y', $request->expires_at);
            } catch (\Exception $e) {
                return redirect()->back()->withErrors(['expires_at' => 'Invalid expiry date format. Please use MM-DD-YYYY format.'])->withInput();
            }
        }
        
        // Validate expiry date is after or equal to start date
        if ($startsAt && $expiresAt && $expiresAt->lt($startsAt)) {
            return redirect()->back()->withErrors(['expires_at' => 'Expiry date must be greater than or equal to start date.'])->withInput();
        }

        

        // Handle message_attachment upload/update
        $updateData = [
            'title' => $request->title,
            'message' => $request->message ?? null,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'is_active' => $request->has('is_active'),
            'bank_id' => $request->bank_id,
            'product_id' => $request->product_id,
            'announcement_category_id' => $request->announcement_category_id,
        ];

        // If new message_attachment is uploaded
        if ($request->hasFile('message_attachment')) {
            // Delete old message_attachment if exists
            if ($announcement->message_attachment) {
                $oldFilePath = public_path('uploads/attachments/' . $announcement->message_attachment);
                if (file_exists($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
            
            // Upload new message_attachment
            $messageAttachment = $request->file('message_attachment');
            $messageAttachmentName = time() . '_' . uniqid() . '.' . $messageAttachment->getClientOriginalExtension();
            $messageAttachment->move(public_path('uploads/attachments'), $messageAttachmentName);
            $updateData['message_attachment'] = 'attachments/' . $messageAttachmentName;
        } elseif ($request->has('delete_message_attachment') && $request->delete_message_attachment == '1') {
            // Delete message_attachment if requested
            if ($announcement->message_attachment) {
                $oldFilePath = public_path($announcement->message_attachment);
                if (file_exists($oldFilePath)) {
                    @unlink($oldFilePath);
                }
            }
            $updateData['message_attachment'] = null;
        }

        // Update the announcement
        $announcement->update($updateData);

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
            $announcement = Announcement::with('attachments', 'creator', 'bank', 'product', 'category')->findOrFail($id);
            
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

    public function destroy(Request $request, Announcement $announcement)
    {
        // Delete message_attachment file if exists
        if ($announcement->message_attachment) {
            $filePath = public_path($announcement->message_attachment);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }

        // Delete associated attachments
        foreach ($announcement->attachments as $attachment) {
            $attachmentPath = public_path($attachment->attachment);
            if (file_exists($attachmentPath)) {
                @unlink($attachmentPath);
            }
            $attachment->delete();
        }

        $announcement->delete();

        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Announcement deleted successfully.'
            ]);
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement deleted successfully.');
    }
}


