<?php

namespace App\Support;

use App\Models\ProjectProcess;
use App\Models\ProjectProcessHistory;
use App\Models\User;

class ProjectProcessActivityService
{
    public function log(ProjectProcess $process, ?User $user, string $eventType, string $description, ?array $meta = null): void
    {
        $process->histories()->create([
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'description' => $description,
            'meta' => $meta,
        ]);
    }

    public function logProgressChange(ProjectProcess $process, ?User $user, array $before, array $after): void
    {
        if (
            ($before['progress'] ?? null) === ($after['progress'] ?? null) &&
            ($before['status'] ?? null) === ($after['status'] ?? null)
        ) {
            return;
        }

        $this->log(
            $process,
            $user,
            'progress_updated',
            sprintf(
                'Progress proses berubah dari %d%% (%s) menjadi %d%% (%s).',
                $before['progress'] ?? 0,
                $before['status'] ?? 'open',
                $after['progress'] ?? 0,
                $after['status'] ?? 'open',
            ),
            [
                'before' => $before,
                'after' => $after,
            ],
        );
    }
}
