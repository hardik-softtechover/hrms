<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    protected $guarded = [];

    protected $casts = [
        'work_date'    => 'date',
        'check_in_at'  => 'datetime',
        'check_out_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(AttendanceBreak::class);
    }

    public function openBreak(): ?AttendanceBreak
    {
        return $this->breaks()->whereNull('break_out_at')->latest()->first();
    }
}
