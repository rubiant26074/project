<?php

namespace App\Http\Controllers;

use App\Models\MasterFlow;
use App\Models\Project;
use App\Models\ProjectProcess;
use App\Support\ProjectProcessActivityService;
use App\Support\ProjectFlowBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectDashboardController extends Controller
{
    public function index(): View
    {
        $projects = Project::query()
            ->with(['masterFlow', 'processes'])
            ->orderBy('wo_number')
            ->get();

        return view('project-dashboard.index', [
            'projects' => $projects,
            'masterFlows' => MasterFlow::query()->withCount('steps')->orderBy('name')->get(),
            'groupedProjects' => [
                'open' => $projects->where('status', 'open'),
                'proses' => $projects->where('status', 'proses'),
                'close' => $projects->where('status', 'close'),
            ],
        ]);
    }

    public function show(Project $project, ProjectFlowBuilder $flowBuilder): View
    {
        $flowBuilder->syncLayout($project);

        $project->load([
            'masterFlow',
            'processes.checklists',
            'connections.fromProcess',
            'connections.toProcess',
        ]);

        return view('project-dashboard.show', [
            'project' => $project,
        ]);
    }

    public function showProcess(Project $project, ProjectProcess $process): View
    {
        abort_unless((int) $process->project_id === (int) $project->getKey(), 404);
        $project->load('processes');
        $process->load([
            'checklists',
            'comments.user',
            'histories.user',
        ]);

        return view('project-dashboard.process', [
            'project' => $project,
            'process' => $process,
        ]);
    }

    public function updateProcessTarget(Request $request, Project $project, ProjectProcess $process, ProjectProcessActivityService $activityService): RedirectResponse
    {
        abort_unless((int) $process->project_id === (int) $project->getKey(), 404);
        abort_unless($request->user()?->canUpdateProcessTargets(), 403);

        $validated = $request->validate([
            'target_start' => ['nullable', 'date'],
            'target_finish' => ['nullable', 'date', 'after_or_equal:target_start'],
        ]);

        $before = [
            'target_start' => $process->target_start?->toDateString(),
            'target_finish' => $process->target_finish?->toDateString(),
        ];

        $process->update([
            'target_start' => $validated['target_start'] ?? null,
            'target_finish' => $validated['target_finish'] ?? null,
        ]);

        $after = [
            'target_start' => $process->target_start?->toDateString(),
            'target_finish' => $process->target_finish?->toDateString(),
        ];

        if ($before !== $after) {
            $activityService->log($process, $request->user(), 'process_target_updated', 'Target tanggal proses diperbarui.', [
                'before' => $before,
                'after' => $after,
            ]);
        }

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Target tanggal proses berhasil diperbarui.');
    }
}
