<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProcessHistory extends Model
{
    protected $fillable = [
        'project_process_id',
        'user_id',
        'event_type',
        'description',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProjectProcess::class, 'project_process_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
