<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $leaves = Leave::where('user_id', $user->id)
            ->orderByDesc('from_date')
            ->get()
            ->map(fn (Leave $l) => [
                'id'         => $l->id,
                'from_date'  => $l->from_date->toDateString(),
                'to_date'    => $l->to_date->toDateString(),
                'half_day'   => $l->half_day,
                'total_days' => (float) $l->total_days,
                'reason'     => $l->reason,
                'status'     => $l->status,
                'pay_type'   => $l->pay_type,
                'admin_note' => $l->admin_note,
                'month'      => $l->from_date->format('Y-m'),
            ]);

        $usedPaid   = (float) $user->leaves()->where('status', 'approved')->where('pay_type', 'paid')->sum('total_days');
        $usedUnpaid = (float) $user->leaves()->where('status', 'approved')->where('pay_type', 'unpaid')->sum('total_days');
        $pending    = (float) $user->leaves()->where('status', 'pending')->sum('total_days');

        return Inertia::render('Employee/Leaves', [
            'leaves'  => $leaves,
            'summary' => [
                'quota'       => $user->annual_leave_quota,
                'used_paid'   => $usedPaid,
                'used_unpaid' => $usedUnpaid,
                'pending'     => $pending,
                'remaining'   => max(0, $user->annual_leave_quota - $usedPaid),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_date' => ['required', 'date'],
            'to_date'   => ['required', 'date', 'after_or_equal:from_date'],
            'half_day'  => ['boolean'],
            'reason'    => ['required', 'string', 'max:500'],
        ]);

        $from = Carbon::parse($data['from_date']);
        $to   = Carbon::parse($data['to_date']);
        $halfDay = (bool) ($data['half_day'] ?? false);

        if ($halfDay) {
            if (! $from->isSameDay($to)) {
                return back()->withErrors(['half_day' => 'Half-day leave must be on a single date.'])->withInput();
            }
            $totalDays = 0.5;
        } else {
            $totalDays = $from->diffInDays($to) + 1;
        }

        Leave::create([
            'user_id'    => $request->user()->id,
            'from_date'  => $from,
            'to_date'    => $to,
            'half_day'   => $halfDay,
            'total_days' => $totalDays,
            'reason'     => $data['reason'],
            'status'     => 'pending',
        ]);

        return back()->with('success', 'Leave request submitted.');
    }
}
