<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role_code');
            $table->string('permission_key');
            $table->boolean('is_allowed')->default(false);
            $table->timestamps();

            $table->unique(['role_code', 'permission_key']);
            $table->foreign('role_code')->references('code')->on('roles')->cascadeOnUpdate()->cascadeOnDelete();
        });

        $now = now();
        $permissions = config('access_matrix.permissions', []);
        $rows = DB::table('roles')
            ->pluck('code')
            ->flatMap(function (string $roleCode) use ($permissions, $now) {
                return collect($permissions)
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
        Schema::dropIfExists('role_permissions');
    }
};
