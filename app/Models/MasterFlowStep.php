<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterFlowStep extends Model
{
    protected $fillable = [
        'master_flow_id',
        'code',
        'name',
        'position_x',
        'position_y',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'position_x' => 'float',
            'position_y' => 'float',
        ];
    }

    public function masterFlow(): BelongsTo
    {
        return $this->belongsTo(MasterFlow::class);
    }

    public function checklistTemplates(): HasMany
    {
        return $this->hasMany(MasterFlowStepChecklist::class)->orderBy('sort_order')->orderBy('id');
    }

    public function outgoingConnections(): HasMany
    {
        return $this->hasMany(MasterFlowConnection::class, 'from_step_id');
    }

    public function incomingConnections(): HasMany
    {
        return $this->hasMany(MasterFlowConnection::class, 'to_step_id');
    }
}
