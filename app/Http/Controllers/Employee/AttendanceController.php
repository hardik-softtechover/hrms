<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public const TARGET_SECONDS = 8 * 3600 + 30 * 60; // 8h 30m

    public function index(Request $request): Response
    {
        $rows = Attendance::where('user_id', $request->user()->id)
            ->with(['notes.user' => fn($q) => $q->select('id', 'name')])
            ->orderByDesc('work_date')
            ->limit(60)
            ->get();

        $records = $rows->map(function (Attendance $a) {
            return [
                'id'                  => $a->id,
                'work_date'           => $a->work_date->toDateString(),
                'check_in_at'         => $a->check_in_at?->toIso8601String(),
                'check_out_at'        => $a->check_out_at?->toIso8601String(),
                'total_break_seconds' => $a->total_break_seconds,
                'staffing_seconds'    => $a->staffing_seconds,
                'completed'           => $a->staffing_seconds >= self::TARGET_SECONDS,
                'notes'               => $a->notes->map(fn($n) => [
                    'id'         => $n->id,
                    'content'    => $n->content,
                    'user_name'  => $n->user->name,
                    'created_at' => $n->created_at->diffForHumans(),
                ]),
            ];
        });

        return Inertia::render('Employee/Attendance', [
            'records'       => $records,
            'targetSeconds' => self::TARGET_SECONDS,
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        $user  = $request->user();
        $today = Carbon::today();

        $existing = Attendance::where('user_id', $user->id)->whereDate('work_date', $today)->first();
        if ($existing && $existing->check_in_at) {
            return back()->with('error', 'You have already checked in today.');
        }

        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['check_in_at' => Carbon::now()],
        );

        return back()->with('success', 'Checked in. Have a productive day!');
    }

    public function breakIn(Request $request): RedirectResponse
    {
        $attendance = $this->todayAttendance($request);
        if (! $attendance || ! $attendance->check_in_at) {
            return back()->with('error', 'Check in first.');
        }
        if ($attendance->check_out_at) {
            return back()->with('error', 'You have already checked out today.');
        }
        if ($attendance->openBreak()) {
            return back()->with('error', 'Break already running.');
        }

        $attendance->breaks()->create(['break_in_at' => Carbon::now()]);
        return back()->with('success', 'Break started.');
    }

    public function breakOut(Request $request): RedirectResponse
    {
        $attendance = $this->todayAttendance($request);
        $open = $attendance?->openBreak();
        if (! $open) {
            return back()->with('error', 'No active break.');
        }

        $end = Carbon::now();
        $open->update(['break_out_at' => $end]);
        $attendance->increment('total_break_seconds', $open->break_in_at->diffInSeconds($end));

        return back()->with('success', 'Break ended.');
    }

    public function checkOut(Request $request): RedirectResponse
    {
        $attendance = $this->todayAttendance($request);
        if (! $attendance || ! $attendance->check_in_at) {
            return back()->with('error', 'Check in first.');
        }
        if ($attendance->check_out_at) {
            return back()->with('error', 'Already checked out.');
        }

        if ($open = $attendance->openBreak()) {
            $end = Carbon::now();
            $open->update(['break_out_at' => $end]);
            $attendance->increment('total_break_seconds', $open->break_in_at->diffInSeconds($end));
            $attendance->refresh();
        }

        $now = Carbon::now();
        $staffing = max(0, $attendance->check_in_at->diffInSeconds($now) - $attendance->total_break_seconds);
        $attendance->update([
            'check_out_at'     => $now,
            'staffing_seconds' => $staffing,
        ]);

        return back()->with('success', 'Checked out. See you tomorrow!');
    }

    public function updateNote(Request $request, Attendance $attendance): RedirectResponse
    {
        abort_unless($attendance->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'note' => ['required', 'string', 'max:500'],
        ]);

        $attendance->notes()->create([
            'user_id' => $request->user()->id,
            'content' => $data['note'],
        ]);

        return back()->with('success', 'Note added.');
    }

    private function todayAttendance(Request $request): ?Attendance
    {
        return Attendance::with('breaks')
            ->where('user_id', $request->user()->id)
            ->whereDate('work_date', Carbon::today())
            ->first();
    }
}
