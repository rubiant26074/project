<?php

namespace App\Http\Controllers;

use App\Models\ProjectProcessChecklist;
use Illuminate\View\View;

class MyTaskController extends Controller
{
    public function index(): View
    {
        $user = request()->user();
        $roleCode = strtolower((string) $user->role);
        $roleName = strtolower((string) ($user->roleDefinition?->name ?? $user->role));
        $filters = [
            'wo' => trim(request()->string('wo')->toString()),
            'project' => trim(request()->string('project')->toString()),
            'client' => trim(request()->string('client')->toString()),
        ];

        $tasks = ProjectProcessChecklist::query()
            ->with(['process.project'])
            ->get()
            ->filter(function (ProjectProcessChecklist $checklist) use ($roleCode, $roleName, $user): bool {
                $process = $checklist->process;

                if (! $process || ! $process->project) {
                    return false;
                }

                if ($user->isAdmin()) {
                    return true;
                }

                $allowedRoles = collect($process->allowed_role_codes ?? [])
                    ->map(fn ($role): string => strtolower((string) $role));

                if ($allowedRoles->contains($roleCode)) {
                    return true;
                }

                return $this->matchesProcessIdentity($process->code, $roleCode, $roleName)
                    || $this->matchesProcessIdentity($process->name, $roleCode, $roleName);
            })
            ->filter(function (ProjectProcessChecklist $checklist) use ($filters): bool {
                $project = $checklist->process->project;

                return $this->matchesFilter($project->wo_number, $filters['wo'])
                    && $this->matchesFilter($project->project_name, $filters['project'])
                    && $this->matchesFilter($project->client_name, $filters['client']);
            })
            ->sortBy([
                fn (ProjectProcessChecklist $task): int => $task->is_done ? 1 : 0,
                fn (ProjectProcessChecklist $task): string => optional($task->process->project->target_finish)->format('Y-m-d') ?? '9999-12-31',
                fn (ProjectProcessChecklist $task): string => $task->process->project->wo_number,
                fn (ProjectProcessChecklist $task): int => $task->process->sort_order,
                fn (ProjectProcessChecklist $task): int => $task->sort_order,
            ])
            ->values();

        return view('my-tasks.index', [
            'tasks' => $tasks,
            'roleLabel' => strtoupper($roleCode),
            'pendingCount' => $tasks->where('is_done', false)->count(),
            'doneCount' => $tasks->where('is_done', true)->count(),
            'projectCount' => $tasks->pluck('process.project_id')->unique()->count(),
            'processCount' => $tasks->pluck('project_process_id')->unique()->count(),
            'filters' => $filters,
        ]);
    }

    private function matchesProcessIdentity(?string $value, string $roleCode, string $roleName): bool
    {
        $normalizedValue = $this->normalize($value);

        if ($normalizedValue === '') {
            return false;
        }

        return $normalizedValue === $this->normalize($roleCode)
            || $normalizedValue === $this->normalize($roleName);
    }

    private function normalize(?string $value): string
    {
        return str((string) $value)->lower()->replace([' ', '-', '_'], '')->toString();
    }

    private function matchesFilter(?string $value, string $filter): bool
    {
        if ($filter === '') {
            return true;
        }

        return str_contains(strtolower((string) $value), strtolower($filter));
    }
}
