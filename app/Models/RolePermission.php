<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class RolePermission extends Model
{
    protected $fillable = [
        'role_code',
        'permission_key',
        'is_allowed',
    ];

    protected $casts = [
        'is_allowed' => 'boolean',
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_code', 'code');
    }

    public static function isAllowed(string $roleCode, string $permissionKey): bool
    {
        if ($roleCode === 'admin') {
            return true;
        }

        if (! Schema::hasTable('role_permissions')) {
            $defaultRoles = config("access_matrix.permissions.{$permissionKey}.roles", []);

            return in_array($roleCode, $defaultRoles, true);
        }

        $permission = self::query()
            ->where('role_code', $roleCode)
            ->where('permission_key', $permissionKey)
            ->first();

        if ($permission) {
            return $permission->is_allowed;
        }

        $defaultRoles = config("access_matrix.permissions.{$permissionKey}.roles", []);

        return in_array($roleCode, $defaultRoles, true);
    }
}
