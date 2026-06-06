<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $permissions = config('access_matrix.permissions', []);
        $existing = DB::table('role_permissions')
            ->get(['role_code', 'permission_key'])
            ->mapWithKeys(fn ($row): array => [$row->role_code . '|' . $row->permission_key => true]);

        $rows = DB::table('roles')
            ->pluck('code')
            ->flatMap(function (string $roleCode) use ($permissions, $existing, $now) {
                return collect($permissions)
                    ->reject(fn (array $permission, string $permissionKey): bool => $existing->has($roleCode . '|' . $permissionKey))
                    ->map(function (array $permission, string $permissionKey) use ($roleCode, $now) {
                        return [
                            'role_code' => $roleCode,
                            'permission_key' => $permissionKey,
                            'is_allowed' => in_array($roleCode, $permission['roles'] ?? [], true),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })
                    ->values();
            })
            ->values()
            ->all();

        if (! empty($rows)) {
            DB::table('role_permissions')->insert($rows);
        }
    }

    public function down(): void
    {
        //
    }
};
