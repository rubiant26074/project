<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MasterFlowStepChecklist extends Model
{
    protected $fillable = [
        'master_flow_step_id',
        'label',
        'sort_order',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(MasterFlowStep::class, 'master_flow_step_id');
    }
}
