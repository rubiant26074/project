<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProcessChecklist extends Model
{
    protected $fillable = [
        'project_process_id',
        'label',
        'is_done',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProjectProcess::class, 'project_process_id');
    }
}
