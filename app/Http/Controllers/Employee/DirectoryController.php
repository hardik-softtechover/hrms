<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DirectoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $search = trim((string) $request->query('q', ''));
        $today = \Carbon\Carbon::today();

        $employees = User::query()
            ->where('role', 'employee')
            ->with(['attendances' => function ($q) use ($today) {
                $q->whereDate('work_date', $today)->with('breaks');
            }])
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%")
                      ->orWhere('designation', 'like', "%{$search}%")
                      ->orWhere('department', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->get(['id', 'name', 'username', 'email', 'phone', 'designation', 'department', 'avatar_path', 'joined_on']);

        return Inertia::render('Employee/Directory', [
            'employees' => $employees->map(function ($u) {
                $todayAtt = $u->attendances->first();
                $status = 'Not In';
                $time = null;

                if ($todayAtt) {
                    if ($todayAtt->check_out_at) {
                        $status = 'Checked Out';
                        $time = $todayAtt->check_out_at->format('H:i');
                    } elseif ($todayAtt->openBreak()) {
                        $status = 'On Break';
                        $time = $todayAtt->openBreak()->break_in_at->format('H:i');
                    } elseif ($todayAtt->check_in_at) {
                        $status = 'Active';
                        $time = $todayAtt->check_in_at->format('H:i');
                    }
                }

                return [
                    'id'          => $u->id,
                    'name'        => $u->name,
                    'username'    => $u->username,
                    'email'       => $u->email,
                    'phone'       => $u->phone,
                    'designation' => $u->designation,
                    'department'  => $u->department,
                    'avatar_url'  => $u->avatar_url,
                    'joined_on'   => $u->joined_on?->toDateString(),
                    'status'      => $status,
                    'status_time' => $time,
                ];
            }),
            'filters'   => ['q' => $search],
            'isAdmin'   => $request->user()->isAdmin(),
        ]);
    }
}
