<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterFlowConnection extends Model
{
    protected $fillable = [
        'master_flow_id',
        'from_step_id',
        'to_step_id',
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

    public function masterFlow(): BelongsTo
    {
        return $this->belongsTo(MasterFlow::class);
    }

    public function fromStep(): BelongsTo
    {
        return $this->belongsTo(MasterFlowStep::class, 'from_step_id');
    }

    public function toStep(): BelongsTo
    {
        return $this->belongsTo(MasterFlowStep::class, 'to_step_id');
    }
}
