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
        'target_finish',
        'description',
        'status',
        'progress',
    ];

    protected $casts = [
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
