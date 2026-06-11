<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Notifications\LeaveApproved;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function index(Request $request): Response
    {
        $status = $request->query('status', 'all');

        $leaves = Leave::with('user:id,name,username,annual_leave_quota,avatar_path')
            ->when(in_array($status, ['pending', 'approved', 'rejected']), fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->get()
            ->map(function (Leave $l) {
                $employee = $l->user;
                $usedPaid = (float) Leave::where('user_id', $employee->id)
                    ->where('status', 'approved')->where('pay_type', 'paid')
                    ->where('id', '!=', $l->id)->sum('total_days');

                return [
                    'id'         => $l->id,
                    'employee'   => [
                        'id'         => $employee->id,
                        'name'       => $employee->name,
                        'username'   => $employee->username,
                        'avatar_url' => $employee->avatar_url,
                        'quota'      => $employee->annual_leave_quota,
                        'used_paid'  => $usedPaid,
                        'remaining'  => max(0, $employee->annual_leave_quota - $usedPaid),
                    ],
                    'from_date'  => $l->from_date->toDateString(),
                    'to_date'    => $l->to_date->toDateString(),
                    'half_day'   => $l->half_day,
                    'total_days' => (float) $l->total_days,
                    'reason'     => $l->reason,
                    'status'     => $l->status,
                    'pay_type'   => $l->pay_type,
                    'admin_note' => $l->admin_note,
                    'created_at' => $l->created_at->toDateString(),
                ];
            });

        return Inertia::render('Admin/Leaves', [
            'leaves'  => $leaves,
            'filters' => ['status' => $status],
        ]);
    }

    public function approve(Request $request, Leave $leave): RedirectResponse
    {
        abort_unless($leave->status === 'pending', 422);

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:255'],
        ]);

        $employee = $leave->user;
        $usedPaid = (float) Leave::where('user_id', $employee->id)
            ->where('status', 'approved')->where('pay_type', 'paid')->sum('total_days');
        $remaining = max(0, $employee->annual_leave_quota - $usedPaid);

        $payType = $remaining >= (float) $leave->total_days ? 'paid' : 'unpaid';

        $leave->update([
            'status'      => 'approved',
            'pay_type'    => $payType,
            'decided_by'  => $request->user()->id,
            'decided_at'  => Carbon::now(),
            'admin_note'  => $data['admin_note'] ?? null,
        ]);

        $employee->notify(new LeaveApproved($leave->fresh()));

        return back()->with('success', "Leave approved as {$payType}.");
    }

    public function reject(Request $request, Leave $leave): RedirectResponse
    {
        abort_unless($leave->status === 'pending', 422);

        $data = $request->validate([
            'admin_note' => ['nullable', 'string', 'max:255'],
        ]);

        $leave->update([
            'status'     => 'rejected',
            'decided_by' => $request->user()->id,
            'decided_at' => Carbon::now(),
            'admin_note' => $data['admin_note'] ?? null,
        ]);

        return back()->with('success', 'Leave rejected.');
    }
}
