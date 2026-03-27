<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Jobs\MakerCheckerJob;
use App\Jobs\WelComeEmailJob;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class MakerCheckerController extends Controller
{
    public function index(Request $request)
    {
        $Route = 'Maker / Checker';

        if ($request->ajax()) {
            $query = User::query()
                ->whereIn('user_type', ['maker', 'checker'])
                ->orderByDesc('id');

            return DataTables::of($query)
                ->addIndexColumn()
                ->editColumn('Emp_Id', function (User $user) {
                    return $user->Emp_Id ?: '-';
                })
                ->addColumn('name', function (User $user) {
                    return trim($user->first_name . ' ' . $user->last_name) ?: '-';
                })
                ->editColumn('email', function (User $user) {
                    return $user->email ?: '-';
                })
                ->editColumn('phone', function (User $user) {
                    return $user->phone ?: '-';
                })
                ->addColumn('role_label', function (User $user) {
                    return $user->user_type === 'checker' ? 'Checker' : 'Maker';
                })
                ->addColumn('status', function (User $user) {
                    $isActive = (int) ($user->status ?? 0) === 1;
                    $checked = $isActive ? 'checked' : '';
                    $status = '<label class="toggle-switch">
                        <input type="checkbox" class="status-toggle" data-user-id="' . $user->id . '" ' . $checked . '>
                        <span class="toggle-slider"></span>
                    </label>';
                    return $status;
                })
                ->addColumn('action', function (User $user) {
                    $viewUrl = url('/maker-checker/view/' . $user->id);
                    $editUrl = url('/maker-checker/update/' . $user->id);

                    $btn = "<a href=\"{$viewUrl}\"><img src=\"" . asset('assets/images/eye-icon.svg') . "\" alt=\"View\"></a>";
                    $btn .= "<a href=\"{$editUrl}\"><img src=\"" . asset('assets/images/Edit.svg') . "\" alt=\"Edit\"></a>";

                    return $btn;
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('Frontend.Users.maker-checker.index', compact('Route'));
    }

    public function create()
    {
        $Route = 'Add Maker / Checker';

        $roles = [
            'maker' => 'Maker',
            'checker' => 'Checker',
        ];

        $states = getState();

        return view('Frontend.Users.maker-checker.create', compact('Route', 'roles', 'states'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'role'  => ['required', Rule::in(['maker', 'checker'])],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:10', 'min:10', 'unique:users,phone'],
            'address_1' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'digits:6'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name']);
        $password = !empty($validated['password']) ? $validated['password'] : $this->generateRandomPassword();

        $user = new User();
        $user->Emp_Id = generateEmployeeID($validated['state'], $validated['city'], $firstName);
        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->address_1 = $validated['address_1'];
        $user->address_2 = $validated['country'] ?? null;
        $user->state = $validated['state'];
        $user->district = $validated['city'];
        $user->pincode = $validated['pincode'];
        $user->user_type = strtolower($validated['role']);
        $user->status = 1;
        $user->password = Hash::make($password);
        $user->save();
        $roles = Role::where('name', ucfirst($validated['role']))->get();

        $userRole = Role::where('name', ucfirst($validated['role']))->first();
        $user->roles()->attach($roles);
        $user->roles()->sync([$userRole->id]);

        $data = [
            'user' => $firstName . ' ' . $lastName,
            'password' => $password,
            'email' => $validated['email'],            
            'user_type' => $validated['role'],
        ];

        dispatch(new MakerCheckerJob($user, $password));
        

        return redirect()
            ->to('/maker-checker')
            ->with('success', 'Maker/Checker user created successfully.');
    }

    public function show($id)
    {
        $Route = 'View Maker / Checker';
        $user = User::whereIn('user_type', ['maker', 'checker'])->findOrFail($id);

        $roles = [
            'maker' => 'Maker',
            'checker' => 'Checker',
        ];

        return view('Frontend.Users.maker-checker.show', compact('Route', 'user', 'roles'));
    }

    public function edit($id)
    {
        $Route = 'Edit Maker / Checker';
        $user = User::whereIn('user_type', ['maker', 'checker'])->findOrFail($id);

        $roles = [
            'maker' => 'Maker',
            'checker' => 'Checker',
        ];

        $states = getState();

        return view('Frontend.Users.maker-checker.edit', compact('Route', 'user', 'roles', 'states'));
    }

    public function update(Request $request, $id)
    {
        $user = User::whereIn('user_type', ['maker', 'checker'])->findOrFail($id);

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'role'  => ['required', Rule::in(['maker', 'checker'])],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['required', 'string', 'max:10', 'min:10', Rule::unique('users', 'phone')->ignore($user->id)],
            'address_1' => ['required', 'string', 'max:255'],
            'state' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'pincode' => ['required', 'digits:6'],
            'country' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['name']);

        $user->first_name = $firstName;
        $user->last_name = $lastName;
        $user->email = $validated['email'];
        $user->phone = $validated['phone'];
        $user->address_1 = $validated['address_1'];
        $user->address_2 = $validated['country'] ?? null;
        $user->state = $validated['state'];
        $user->district = $validated['city'];
        $user->pincode = $validated['pincode'];
        $user->user_type = $validated['role'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->to('/maker-checker')
            ->with('success', 'Maker/Checker user updated successfully.');
    }

    public function destroy(User $user)
    {
        if (!in_array($user->user_type, ['maker', 'checker'], true)) {
            abort(404);
        }

        $user->roles()->detach();
        $user->delete();

        return response()->json(['success' => true]);
    }

    public function toggleStatus(Request $request, $id)
    {
        $user = User::whereIn('user_type', ['maker', 'checker'])->findOrFail($id);
        
        $user->status = (int) ($user->status ?? 0) === 1 ? 0 : 1;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'User status updated successfully.',
            'is_active' => (int) $user->status === 1
        ]);
    }

    private function splitName(string $name): array
    {
        $parts = preg_split('/\s+/', trim($name), 2);

        $first = $parts[0] ?? '';
        $last = $parts[1] ?? '';

        return [$first, $last];
    }

    private function generateRandomPassword(int $length = 12): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789@$!%*?&';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }

        return $password;
    }
}


