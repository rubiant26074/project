<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectProcess;
use App\Models\ProjectProcessChecklist;
use App\Support\ProjectProcessActivityService;
use App\Support\ProjectProgressService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectProcessChecklistController extends Controller
{
    public function store(Request $request, Project $project, ProjectProcess $process, ProjectProgressService $progressService, ProjectProcessActivityService $activityService): RedirectResponse
    {
        abort_unless($process->project_id === $project->id, 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $checklist = $process->checklists()->create([
            ...$validated,
            'is_done' => false,
        ]);

        $activityService->log($process, $request->user(), 'checklist_created', 'Checklist proses baru ditambahkan.', [
            'checklist_id' => $checklist->id,
            'label' => $checklist->label,
        ]);

        $progressService->syncFromChecklist($process->fresh('checklists'), $request->user());

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Checklist proses berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectProcess $process, ProjectProcessChecklist $checklist, ProjectProgressService $progressService): RedirectResponse
    {
        abort_unless($process->project_id === $project->id && $checklist->project_process_id === $process->id, 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_done' => ['nullable', 'boolean'],
        ]);

        $previousDone = $checklist->is_done;

        $checklist->update([
            'label' => $validated['label'],
            'sort_order' => $validated['sort_order'],
            'is_done' => (bool) ($validated['is_done'] ?? false),
        ]);

        if ($previousDone !== $checklist->is_done) {
            app(ProjectProcessActivityService::class)->log(
                $process,
                $request->user(),
                'checklist_toggled',
                $checklist->is_done
                    ? sprintf('Checklist "%s" ditandai selesai.', $checklist->label)
                    : sprintf('Checklist "%s" dibuka kembali.', $checklist->label),
                [
                    'checklist_id' => $checklist->id,
                    'is_done' => $checklist->is_done,
                ],
            );
        }

        $progressService->syncFromChecklist($process->fresh('checklists', 'project.processes'), $request->user());

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Checklist proses berhasil diperbarui.');
    }

    public function destroy(Project $project, ProjectProcess $process, ProjectProcessChecklist $checklist, ProjectProgressService $progressService, ProjectProcessActivityService $activityService): RedirectResponse
    {
        abort_unless($process->project_id === $project->id && $checklist->project_process_id === $process->id, 404);
        $activityService->log($process, request()->user(), 'checklist_deleted', 'Checklist proses dihapus.', [
            'checklist_id' => $checklist->id,
            'label' => $checklist->label,
        ]);
        $checklist->delete();

        $progressService->syncFromChecklist($process->fresh('checklists', 'project.processes'), request()->user());

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Checklist proses berhasil dihapus.');
    }
}
