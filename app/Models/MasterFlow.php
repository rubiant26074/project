<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MasterFlow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(MasterFlowStep::class)->orderBy('sort_order')->orderBy('id');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(MasterFlowConnection::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
