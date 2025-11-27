<?php

namespace App\Http\Controllers\Advance;

use App\Http\Controllers\Controller;
use App\Models\Advance;
use App\Models\AdvanceAmountLog;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdvanceController extends Controller
{
    protected $advance;    
    /**
     * Method __construct
     *
     * @param Advance $advance [explicite description]
     *
     * @return void
     */
    public function __construct(Advance $advance)
    {
        $this->advance = $advance;
    }
        
    /**
     * Method index
     *
     * @return void
     */
    public function index(Request $request)
    {
        $Route = 'Advances';
        
        if ($request->ajax()) {
            $query = Advance::with('user')->orderBy('id', 'desc');           

            if ($request->status !== null && $request->status !== '') {
                $query->where('advance_status', $request->status);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('user_id', function ($row) {
                    if($row->user) {
                        return $row->user->first_name.' '.$row->user->last_name;
                    }
                    return '-';
                })
                ->editColumn('advance_amount', function ($row) {
                    return $row->advance_amount ? '₹' . number_format($row->advance_amount, 2) : '-';
                })
                ->editColumn('advance_date', function ($row) {
                    return $row->advance_date ? date('d-m-Y', strtotime($row->advance_date)) : '-';
                })
                ->editColumn('advance_status', function ($row) {
                    $checked = $row->advance_status == 1 ? 'checked' : '';
                    $status = '<label class="toggle-switch">
                        <input type="checkbox" class="status-toggle" data-advance-id="' . $row->id . '" ' . $checked . '>
                        <span class="toggle-slider"></span>
                    </label>';
                    return $status;
                })
                ->addColumn('action', function ($row) {
                    $btn = '';
                    $btn .= "<a href='" . url('/advance/view/' . $row->id) . "' title='View Details' style='cursor: pointer; display: inline-block;'>";
                    $btn .= "<svg width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' style='color: #007bff;'>";
                    $btn .= "<path d='M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z'></path><circle cx='12' cy='12' r='3'></circle>";
                    $btn .= "</svg></a>";
                    return $btn;
                })
                ->rawColumns(['advance_status', 'action'])
                ->make(true);
        } 
        return view('Frontend.Advance.index', compact('Route'));
    }

    /**
     * Show the form for creating a new advance.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $Route = 'Add Advance';
        $users = User::select('id', 'first_name', 'last_name', 'email')
            ->whereHas('roles', function ($query) {
                $query->where('roles.id', 2);
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(10)
            ->get();

        return view('Frontend.Advance.create', compact('Route', 'users'));
    }

    /**
     * Store a newly created advance in storage.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            // Validation and advance creation is moved inside try block. 
            $messages = [
                'user_id.required' => 'Please select a channel partner.',
                'user_id.exists' => 'The selected channel partner does not exist.',
                'advance_amount.required' => 'Please enter an advance amount.',
                'advance_amount.numeric' => 'The advance amount must be a number.',
                'advance_amount.min' => 'The advance amount must be at least 1.',
                'advance_remark.max' => 'The advance remark must not exceed 500 characters.',
                'advance_type.required' => 'Please select an advance type.',
                'advance_type.in' => 'The advance type must be either add or deduct.',
            ];
    
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'advance_amount' => 'required|numeric|min:1',
                'advance_remark' => 'nullable|string|max:500',
                'advance_type' => 'required|in:add,deduct',
            ], $messages);
    
            Advance::createAdvance([
                'user_id' => $validated['user_id'],
                'advance_amount' => $validated['advance_amount'],
                'advance_remark' => $validated['advance_remark'] ?? null,
                'advance_type' => $validated['advance_type'],
                'created_by' => auth()->user()->id,
            ]);
    
            return redirect()->route('advance.index')->with('success', 'Advance created successfully.');
        } catch (\Throwable $e) {
            // You might further log the exception here
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        } 
       
    }

    /**
     * Search users with role id 2 for select picker.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request)
    {
        $term = $request->get('q');

        $users = User::select('id', 'first_name', 'last_name', 'email')
            ->whereHas('roles', function ($query) {
                $query->where('roles.id', 2);
            })
            ->when($term, function ($query) use ($term) {
                $query->where(function ($innerQuery) use ($term) {
                    $innerQuery->where('first_name', 'like', '%' . $term . '%')
                        ->orWhere('last_name', 'like', '%' . $term . '%')
                        ->orWhere('email', 'like', '%' . $term . '%');
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(10)
            ->get();

        $results = $users->map(function ($user) {
            $display = trim($user->first_name . ' ' . $user->last_name);
            if ($user->email) {
                $display .= " ({$user->email})";
            }

            return [
                'id' => $user->id,
                'text' => $display,
            ];
        });

        return response()->json($results);
    }

    /**
     * Display the specified advance details with logs.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $Route = 'View Advance Details';
        $advance = Advance::with('user')->findOrFail($id);
        
        // Handle AJAX request for DataTables
        if ($request->ajax()) {
            $query = AdvanceAmountLog::with('createdBy')
                ->where('advance_id', $id)
                ->orderBy('created_at', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('type', function ($row) {
                    $badgeClass = $row->type == 'add' ? 'log-type-add' : 'log-type-deduct';
                    return '<span class="log-type-badge ' . $badgeClass . '">' . ucfirst($row->type) . '</span>';
                })
                ->editColumn('advance_amount', function ($row) {
                    return '₹' . number_format($row->advance_amount, 2);
                })
                ->editColumn('advance_date', function ($row) {
                    return $row->advance_date ? date('d-m-Y', strtotime($row->advance_date)) : '-';
                })
                ->editColumn('created_at', function ($row) {
                    return $row->created_at ? $row->created_at->format('d-m-Y H:i') : '-';
                })
                ->editColumn('created_by', function ($row) {
                    if ($row->createdBy) {
                        return $row->createdBy->first_name . ' ' . $row->createdBy->last_name;
                    }
                    return '-';
                })
                ->editColumn('remark', function ($row) {
                    return $row->remark ? $row->remark : '-';
                })
                ->rawColumns(['type'])
                ->make(true);
        }

        return view('Frontend.Advance.show', compact('Route', 'advance'));
    }
}