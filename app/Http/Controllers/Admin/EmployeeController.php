<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));

        $employees = User::query()
            ->where('role', 'employee')
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")->orWhere('username', 'like', "%{$search}%");
            }))
            ->orderByDesc('created_at')
            ->get(['id', 'name', 'username', 'email', 'designation', 'department', 'annual_leave_quota', 'avatar_path', 'created_at']);

        return Inertia::render('Admin/Employees', [
            'employees' => $employees->map(fn ($u) => [
                'id'                 => $u->id,
                'name'               => $u->name,
                'username'           => $u->username,
                'email'              => $u->email,
                'designation'        => $u->designation,
                'department'         => $u->department,
                'annual_leave_quota' => $u->annual_leave_quota,
                'avatar_url'         => $u->avatar_url,
                'created_at'         => $u->created_at?->toDateString(),
            ]),
            'filters'   => ['q' => $search],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:120'],
            'username'           => ['required', 'string', 'max:50', 'alpha_dash', 'unique:users,username'],
            'password'           => ['required', 'string', 'min:6'],
            'annual_leave_quota' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        User::create([
            'name'               => $data['name'],
            'username'           => $data['username'],
            'password'           => Hash::make($data['password']),
            'role'               => 'employee',
            'annual_leave_quota' => $data['annual_leave_quota'] ?? 12,
        ]);

        return redirect()->route('admin.employees.index')->with('success', "Employee created. Share these creds: {$data['username']} / {$data['password']}");
    }

    public function destroy(User $employee): RedirectResponse
    {
        abort_if($employee->isAdmin(), 403);
        $employee->delete();
        return back()->with('success', 'Employee removed.');
    }
}
