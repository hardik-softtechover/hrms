<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leave extends Model
{
    protected $guarded = [];

    protected $casts = [
        'from_date'   => 'date',
        'to_date'     => 'date',
        'half_day'    => 'boolean',
        'total_days'  => 'decimal:1',
        'decided_at'  => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
