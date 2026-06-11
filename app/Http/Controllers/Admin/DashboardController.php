<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Leave;
use App\Models\User;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $today = Carbon::today();

        $stats = [
            'employees'       => User::where('role', 'employee')->count(),
            'pendingLeaves'   => Leave::where('status', 'pending')->count(),
            'presentToday'    => Attendance::whereDate('work_date', $today)->whereNotNull('check_in_at')->count(),
            'upcomingEvents'  => Event::where('starts_at', '>=', Carbon::now())->count(),
        ];

        $recentLeaves = Leave::with('user:id,name,username,avatar_path')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get()
            ->map(fn (Leave $l) => [
                'id'         => $l->id,
                'employee'   => [
                    'name'       => $l->user->name,
                    'username'   => $l->user->username,
                    'avatar_url' => $l->user->avatar_url,
                ],
                'from_date'  => $l->from_date->toDateString(),
                'to_date'    => $l->to_date->toDateString(),
                'total_days' => (float) $l->total_days,
                'reason'     => $l->reason,
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats'        => $stats,
            'recentLeaves' => $recentLeaves,
        ]);
    }
}
