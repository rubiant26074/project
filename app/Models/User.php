<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use App\Models\ProjectProcess;
use App\Models\ProjectProcessComment;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'is_active',
        'approved_at',
        'approved_by',
        'last_notification_seen_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'last_notification_seen_at' => 'datetime',
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

    public function canUpdateProcessTargets(): bool
    {
        return $this->isAdmin() || strtolower((string) $this->role) === 'pm';
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
        return RolePermission::isAllowed((string) $this->role, $permission);
    }

    public function canDeleteProcessComment(?int $commentUserId): bool
    {
        if ($this->canAccess('process_comment_delete_any')) {
            return true;
        }

        return $commentUserId === $this->id;
    }

    public function unreadNotificationCount(): int
    {
        if (! Schema::hasColumn('users', 'last_notification_seen_at')) {
            return 0;
        }

        $lastSeenAt = $this->last_notification_seen_at;

        return ProjectProcessComment::query()
            ->when($lastSeenAt, fn ($query) => $query->where('created_at', '>', $lastSeenAt))
            ->count();
    }

    public function markNotificationsAsRead(): void
    {
        if (! Schema::hasColumn('users', 'last_notification_seen_at')) {
            return;
        }

        $this->forceFill([
            'last_notification_seen_at' => now(),
        ])->save();
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
