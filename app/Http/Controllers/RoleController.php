<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\RolePermission;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('users')
            ->with('permissions')
            ->orderByDesc('is_system')
            ->orderBy('name')
            ->get();

        $this->ensurePermissionRows($roles);
        $roles->load('permissions');
        $matrixRoles = $roles->where('is_active', true)->values();
        $selectedRoleCode = request()->string('matrix_role')->toString() ?: $matrixRoles->first()?->code;
        $selectedRole = $matrixRoles->firstWhere('code', $selectedRoleCode) ?? $matrixRoles->first();

        $permissions = collect(User::permissionMatrix())
            ->groupBy('group')
            ->map(function (Collection $groupPermissions) {
                return $groupPermissions->map(function (array $permission, string $permissionKey) {
                    return [
                        'key' => $permissionKey,
                        ...$permission,
                    ];
                })->values();
            });

        return view('roles.index', [
            'roles' => $roles,
            'matrixRoles' => $matrixRoles,
            'selectedMatrixRole' => $selectedRole,
            'permissionGroups' => $permissions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $role = Role::create([
            ...$validated,
            'code' => strtolower($validated['code']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_system' => false,
        ]);

        $this->ensurePermissionRows(collect([$role]));

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role baru berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:roles,code,' . $role->id],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($role->is_system) {
            $validated['code'] = $role->code;
        } else {
            $validated['code'] = strtolower($validated['code']);
        }

        $oldCode = $role->code;

        $role->update([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        if ($oldCode !== $role->code) {
            User::query()->where('role', $oldCode)->update(['role' => $role->code]);
        }

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role berhasil diperbarui.');
    }

    public function updatePermissions(Request $request): RedirectResponse
    {
        $permissionKeys = array_keys(User::permissionMatrix());

        $validated = $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['required', 'boolean'],
            'matrix_role' => ['required', 'exists:roles,code'],
        ]);

        DB::transaction(function () use ($validated, $permissionKeys): void {
            foreach ($validated['permissions'] as $permissionKey => $isAllowed) {
                if (! in_array($permissionKey, $permissionKeys, true)) {
                    continue;
                }

                RolePermission::query()->updateOrCreate(
                    [
                        'role_code' => $validated['matrix_role'],
                        'permission_key' => $permissionKey,
                    ],
                    [
                        'is_allowed' => filter_var($isAllowed, FILTER_VALIDATE_BOOLEAN),
                    ],
                );
            }
        });

        return redirect()
            ->route('roles.index', ['matrix_role' => $validated['matrix_role'] ?? null])
            ->with('status', 'Matrix hak akses berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()
                ->route('roles.index')
                ->with('status', 'Role sistem tidak bisa dihapus.');
        }

        if ($role->users()->exists()) {
            return redirect()
                ->route('roles.index')
                ->with('status', 'Role tidak bisa dihapus karena masih dipakai user.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('status', 'Role berhasil dihapus.');
    }

    private function ensurePermissionRows(Collection $roles): void
    {
        $permissions = User::permissionMatrix();
        $now = now();

        $rows = $roles
            ->flatMap(function (Role $role) use ($permissions, $now) {
                $existingKeys = $role->permissions->pluck('permission_key')->all();

                return collect($permissions)
                    ->reject(fn (array $permission, string $permissionKey): bool => in_array($permissionKey, $existingKeys, true))
                    ->map(function (array $permission, string $permissionKey) use ($role, $now) {
                        return [
                            'role_code' => $role->code,
                            'permission_key' => $permissionKey,
                            'is_allowed' => in_array($role->code, $permission['roles'] ?? [], true),
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    })
                    ->values();
            })
            ->values()
            ->all();

        if (! empty($rows)) {
            RolePermission::query()->insert($rows);
        }
    }
}
