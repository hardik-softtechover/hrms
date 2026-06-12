<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
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

    public function update(Request $request, User $employee): RedirectResponse
    {
        abort_if($employee->isAdmin(), 403);

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:120'],
            'username'           => ['required', 'string', 'max:50', 'alpha_dash', "unique:users,username,{$employee->id}"],
            'password'           => ['nullable', 'string', 'min:6'],
            'annual_leave_quota' => ['nullable', 'integer', 'min:0', 'max:60'],
        ]);

        $employee->name = $data['name'];
        $employee->username = $data['username'];
        $employee->annual_leave_quota = $data['annual_leave_quota'] ?? 12;

        if (!empty($data['password'])) {
            $employee->password = Hash::make($data['password']);
        }

        $employee->save();

        return redirect()->route('admin.employees.index')->with('success', 'Employee updated successfully.');
    }

    public function attendance(User $employee): Response
    {
        abort_if($employee->isAdmin(), 403);
$rows = Attendance::where('user_id', $employee->id)
    ->with(['breaks', 'notes.user' => fn($q) => $q->select('id', 'name')])
    ->orderByDesc('work_date')
    ->limit(100)
    ->get();

$records = $rows->map(function ($a) {
    return [
        'id'                  => $a->id,
        'work_date'           => $a->work_date->toDateString(),
        'check_in_at'         => $a->check_in_at?->toIso8601String(),
        'check_out_at'        => $a->check_out_at?->toIso8601String(),
        'total_break_seconds' => $a->total_break_seconds,
        'staffing_seconds'    => $a->staffing_seconds,
        'notes'               => $a->notes->map(fn($n) => [
            'id'         => $n->id,
            'content'    => $n->content,
            'user_name'  => $n->user->name,
            'created_at' => $n->created_at->diffForHumans(),
        ]),
        'completed'           => $a->staffing_seconds >= EmployeeAttendanceController::TARGET_SECONDS,
        'breaks'              => $a->breaks->map(fn($b) => [
            'id' => $b->id,
            'break_in_at' => $b->break_in_at?->toIso8601String(),
            'break_out_at' => $b->break_out_at?->toIso8601String(),
        ]),
    ];
});

        return Inertia::render('Admin/EmployeeAttendance', [
            'employee'      => [
                'id'   => $employee->id,
                'name' => $employee->name,
            ],
            'records'       => $records,
            'targetSeconds' => EmployeeAttendanceController::TARGET_SECONDS,
        ]);
    }

    public function updateAttendance(Request $request, Attendance $attendance): RedirectResponse
    {
        $data = $request->validate([
            'check_in_at'  => ['nullable', 'string'], // Expecting HH:mm
            'check_out_at' => ['nullable', 'string'], // Expecting HH:mm
            'note'         => ['nullable', 'string', 'max:500'],
            'breaks'       => ['nullable', 'array'],
            'breaks.*.id'  => ['required', 'exists:attendance_breaks,id'],
            'breaks.*.break_in_at'  => ['nullable', 'string'], // Expecting HH:mm
            'breaks.*.break_out_at' => ['nullable', 'string'], // Expecting HH:mm
        ]);

        $workDate = $attendance->work_date->toDateString();

        $checkInAt = $data['check_in_at'] ? \Carbon\Carbon::parse("{$workDate} {$data['check_in_at']}") : null;
        $checkOutAt = $data['check_out_at'] ? \Carbon\Carbon::parse("{$workDate} {$data['check_out_at']}") : null;

        $attendance->update([
            'check_in_at'  => $checkInAt,
            'check_out_at' => $checkOutAt,
            'note'         => $data['note'],
        ]);

        $totalBreakSeconds = 0;
        if (!empty($data['breaks'])) {
            foreach ($data['breaks'] as $bData) {
                $b = \App\Models\AttendanceBreak::find($bData['id']);
                if ($b && $b->attendance_id === $attendance->id) {
                    $bIn  = $bData['break_in_at'] ? \Carbon\Carbon::parse("{$workDate} {$bData['break_in_at']}") : null;
                    $bOut = $bData['break_out_at'] ? \Carbon\Carbon::parse("{$workDate} {$bData['break_out_at']}") : null;

                    $b->update([
                        'break_in_at'  => $bIn,
                        'break_out_at' => $bOut,
                    ]);

                    if ($b->break_in_at && $b->break_out_at) {
                        $totalBreakSeconds += $b->break_in_at->diffInSeconds($b->break_out_at);
                    }
                }
            }
        }

        $attendance->update(['total_break_seconds' => $totalBreakSeconds]);

        // Recalculate staffing
        if ($attendance->check_in_at && $attendance->check_out_at) {
            $staffing = max(0, $attendance->check_in_at->diffInSeconds($attendance->check_out_at) - $totalBreakSeconds);
            $attendance->update(['staffing_seconds' => $staffing]);
        }

        return back()->with('success', 'Attendance record and breaks updated.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        abort_if($employee->isAdmin(), 403);
        $employee->delete();
        return back()->with('success', 'Employee removed.');
    }
}
