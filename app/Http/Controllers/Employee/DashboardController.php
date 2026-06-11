<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Event;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user  = $request->user();
        $today = Carbon::today();

        $todayAttendance = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        $usedPaid = (float) Leave::where('user_id', $user->id)
            ->where('status', 'approved')->where('pay_type', 'paid')
            ->sum('total_days');

        $usedUnpaid = (float) Leave::where('user_id', $user->id)
            ->where('status', 'approved')->where('pay_type', 'unpaid')
            ->sum('total_days');

        $pending = (float) Leave::where('user_id', $user->id)
            ->where('status', 'pending')->sum('total_days');

        $upcomingEvents = Event::where('starts_at', '>=', Carbon::now())
            ->orderBy('starts_at')->limit(3)->get();

        return Inertia::render('Employee/Dashboard', [
            'today'          => $today->toDateString(),
            'todayAttendance'=> $this->serializeAttendance($todayAttendance),
            'leaveSummary'   => [
                'quota'        => $user->annual_leave_quota,
                'used_paid'    => $usedPaid,
                'used_unpaid'  => $usedUnpaid,
                'pending'      => $pending,
                'remaining'    => max(0, $user->annual_leave_quota - $usedPaid),
            ],
            'upcomingEvents' => $upcomingEvents,
        ]);
    }

    public static function serializeAttendance(?Attendance $a): ?array
    {
        if (! $a) return null;

        $open = $a->breaks->whereNull('break_out_at')->first();
        $accumulated = $a->total_break_seconds;
        if ($open) {
            $accumulated += $open->break_in_at->diffInSeconds(Carbon::now());
        }
        $checkOut = $a->check_out_at;
        $endRef   = $checkOut ?: Carbon::now();
        $staffing = $a->check_in_at ? max(0, $a->check_in_at->diffInSeconds($endRef) - $accumulated) : 0;

        return [
            'id'                  => $a->id,
            'check_in_at'         => $a->check_in_at?->toIso8601String(),
            'check_out_at'        => $a->check_out_at?->toIso8601String(),
            'on_break'            => (bool) $open,
            'open_break_at'       => $open?->break_in_at?->toIso8601String(),
            'total_break_seconds' => $accumulated,
            'staffing_seconds'    => $staffing,
        ];
    }
}
