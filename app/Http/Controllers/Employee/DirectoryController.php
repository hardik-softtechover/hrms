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

        $employees = User::query()
            ->where('role', 'employee')
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
            'employees' => $employees->map(fn ($u) => [
                'id'          => $u->id,
                'name'        => $u->name,
                'username'    => $u->username,
                'email'       => $u->email,
                'phone'       => $u->phone,
                'designation' => $u->designation,
                'department'  => $u->department,
                'avatar_url'  => $u->avatar_url,
                'joined_on'   => $u->joined_on?->toDateString(),
            ]),
            'filters'   => ['q' => $search],
        ]);
    }
}
