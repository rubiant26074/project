<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectProcess;
use App\Models\User;

class ProjectProgressService
{
    public function __construct(private readonly ProjectProcessActivityService $activityService)
    {
    }

    public function syncProcess(ProjectProcess $process): void
    {
        $process->loadMissing('checklists', 'project.processes');

        $total = $process->checklists->count();
        $completed = $process->checklists->where('is_done', true)->count();
        $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

        $process->forceFill([
            'completed_checklists' => $completed,
            'total_checklists' => $total,
            'progress' => $progress,
            'status' => $completed === 0 ? 'open' : ($completed === $total ? 'close' : 'proses'),
        ])->save();
    }

    public function syncProject(Project $project): void
    {
        $project->loadMissing('processes.checklists');

        foreach ($project->processes as $process) {
            $total = $process->checklists->count();
            $completed = $process->checklists->where('is_done', true)->count();
            $progress = $total > 0 ? (int) round(($completed / $total) * 100) : 0;

            if (
                $process->total_checklists !== $total ||
                $process->completed_checklists !== $completed ||
                $process->progress !== $progress
            ) {
                $process->forceFill([
                    'total_checklists' => $total,
                    'completed_checklists' => $completed,
                    'progress' => $progress,
                    'status' => $completed === 0 ? 'open' : ($completed === $total ? 'close' : 'proses'),
                ])->save();
            }
        }

        $project->refresh()->load('processes');

        $totalItems = $project->processes->sum('total_checklists');
        $completedItems = $project->processes->sum('completed_checklists');
        $progress = $totalItems > 0 ? (int) round(($completedItems / $totalItems) * 100) : 0;

        $status = 'open';
        if ($project->processes->isNotEmpty()) {
            if ($project->processes->every(fn (ProjectProcess $process) => $process->status === 'close')) {
                $status = 'close';
            } elseif ($project->processes->contains(fn (ProjectProcess $process) => in_array($process->status, ['proses', 'close'], true))) {
                $status = 'proses';
            }
        }

        $project->forceFill([
            'progress' => $progress,
            'status' => $status,
        ])->save();
    }

    public function syncFromChecklist(ProjectProcess $process, ?User $user = null): void
    {
        $before = [
            'progress' => $process->progress,
            'status' => $process->status,
        ];

        $this->syncProcess($process);
        $process->refresh();
        $this->activityService->logProgressChange($process, $user, $before, [
            'progress' => $process->progress,
            'status' => $process->status,
        ]);
        $this->syncProject($process->project()->firstOrFail());
    }
}
