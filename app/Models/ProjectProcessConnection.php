<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProcessConnection extends Model
{
    protected $fillable = [
        'project_id',
        'from_process_id',
        'to_process_id',
        'start_x',
        'start_y',
        'bend_x',
        'bend_y',
        'mid2_x',
        'mid2_y',
        'end_x',
        'end_y',
    ];

    protected function casts(): array
    {
        return [
            'start_x' => 'float',
            'start_y' => 'float',
            'bend_x' => 'float',
            'bend_y' => 'float',
            'mid2_x' => 'float',
            'mid2_y' => 'float',
            'end_x' => 'float',
            'end_y' => 'float',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fromProcess(): BelongsTo
    {
        return $this->belongsTo(ProjectProcess::class, 'from_process_id');
    }

    public function toProcess(): BelongsTo
    {
        return $this->belongsTo(ProjectProcess::class, 'to_process_id');
    }
}
