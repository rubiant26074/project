<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
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
            'roles' => Role::query()->withCount('users')->orderByDesc('is_system')->orderBy('name')->get(),
            'roleMatrix' => User::roleMatrix(),
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

        Role::create([
            ...$validated,
            'code' => strtolower($validated['code']),
            'is_active' => (bool) ($validated['is_active'] ?? false),
            'is_system' => false,
        ]);

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
}
