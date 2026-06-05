<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\ProjectProcess;

#[Fillable(['name', 'email', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'approved_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isApproved(): bool
    {
        return $this->is_active && filled($this->role);
    }

    public function canManageProjects(): bool
    {
        return $this->canAccess('project_create') || $this->canAccess('project_update') || $this->canAccess('project_delete');
    }

    public function canManageMasterFlows(): bool
    {
        return $this->canAccess('master_flow_manage');
    }

    public function canManageRoles(): bool
    {
        return $this->canAccess('role_manage');
    }

    public function canManageUsers(): bool
    {
        return $this->canAccess('user_manage');
    }

    public function canUpdateProcesses(): bool
    {
        return $this->canAccess('process_checklist_manage') || $this->canAccess('process_comment_add');
    }

    public function canUpdateProcess(ProjectProcess $process): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $allowedRoles = $process->allowed_role_codes ?? [];

        if (! empty($allowedRoles)) {
            return in_array(strtolower((string) $this->role), array_map(
                fn ($role): string => strtolower((string) $role),
                $allowedRoles,
            ), true);
        }

        return $this->canUpdateProcesses();
    }

    public function canAccess(string $permission): bool
    {
        $roles = config("access_matrix.permissions.{$permission}.roles", []);

        return in_array($this->role, $roles, true);
    }

    public function canDeleteProcessComment(?int $commentUserId): bool
    {
        if ($this->canAccess('process_comment_delete_any')) {
            return true;
        }

        return $commentUserId === $this->id;
    }

    public static function roleMatrix(): array
    {
        return config('access_matrix.roles', []);
    }

    public static function permissionMatrix(): array
    {
        return config('access_matrix.permissions', []);
    }

    public function roleDefinition(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role', 'code');
    }
}
