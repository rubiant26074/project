<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'master_flow_id',
        'wo_number',
        'client_name',
        'project_name',
        'start_project',
        'target_finish',
        'description',
        'status',
        'progress',
    ];

    protected $casts = [
        'start_project' => 'date',
        'target_finish' => 'date',
    ];

    public function masterFlow(): BelongsTo
    {
        return $this->belongsTo(MasterFlow::class);
    }

    public function processes(): HasMany
    {
        return $this->hasMany(ProjectProcess::class)->orderBy('sort_order')->orderBy('id');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(ProjectProcessConnection::class);
    }
}
