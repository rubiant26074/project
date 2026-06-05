<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectProcess extends Model
{
    protected $fillable = [
        'project_id',
        'master_flow_step_id',
        'code',
        'name',
        'status',
        'progress',
        'completed_checklists',
        'total_checklists',
        'position_x',
        'position_y',
        'sort_order',
        'allowed_role_codes',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'float',
            'position_y' => 'float',
            'allowed_role_codes' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function masterFlowStep(): BelongsTo
    {
        return $this->belongsTo(MasterFlowStep::class);
    }

    public function checklists(): HasMany
    {
        return $this->hasMany(ProjectProcessChecklist::class)->orderBy('sort_order')->orderBy('id');
    }

    public function outgoingConnections(): HasMany
    {
        return $this->hasMany(ProjectProcessConnection::class, 'from_process_id');
    }

    public function incomingConnections(): HasMany
    {
        return $this->hasMany(ProjectProcessConnection::class, 'to_process_id');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(ProjectProcessHistory::class)->latest();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ProjectProcessComment::class)->latest();
    }
}
