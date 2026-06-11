<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectProcessChecklist extends Model
{
    protected $fillable = [
        'project_process_id',
        'parent_id',
        'depth',
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
            'depth' => 'integer',
        ];
    }

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProjectProcess::class, 'project_process_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
    }
}
