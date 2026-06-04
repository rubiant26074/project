<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProcessComment extends Model
{
    protected $fillable = [
        'project_process_id',
        'user_id',
        'comment',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProjectProcess::class, 'project_process_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
