<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProcessChecklist extends Model
{
    protected $fillable = [
        'project_process_id',
        'label',
        'document_link',
        'target_start',
        'target_finish',
        'is_done',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_done' => 'boolean',
            'target_start' => 'date',
            'target_finish' => 'date',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProjectProcess::class, 'project_process_id');
    }
}
