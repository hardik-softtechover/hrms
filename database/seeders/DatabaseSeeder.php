<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name'     => 'HR Admin',
                'email'    => 'admin@hrms.test',
                'password' => Hash::make('admin123'),
                'role'     => 'admin',
            ],
        );

        $employee = User::updateOrCreate(
            ['username' => 'employee'],
            [
                'name'               => 'Sample Employee',
                'email'              => 'employee@hrms.test',
                'password'           => Hash::make('employee123'),
                'role'               => 'employee',
                'designation'        => 'Software Engineer',
                'department'         => 'Engineering',
                'phone'              => '+91 9000000000',
                'joined_on'          => Carbon::now()->subMonths(8),
                'annual_leave_quota' => 12,
            ],
        );

        Event::firstOrCreate(
            ['title' => 'Monthly Town Hall'],
            [
                'description' => 'Company-wide monthly update from leadership.',
                'starts_at'   => Carbon::now()->addDays(5)->setTime(16, 0),
                'ends_at'     => Carbon::now()->addDays(5)->setTime(17, 0),
                'location'    => 'Main Conference Room',
                'created_by'  => $admin->id,
            ],
        );

        Event::firstOrCreate(
            ['title' => 'Team Outing'],
            [
                'description' => 'Quarterly team get-together.',
                'starts_at'   => Carbon::now()->addDays(20)->setTime(11, 0),
                'location'    => 'Riverside Park',
                'created_by'  => $admin->id,
            ],
        );
    }
}
