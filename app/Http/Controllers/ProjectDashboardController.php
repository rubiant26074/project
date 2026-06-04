<?php

namespace App\Http\Controllers;

use App\Models\MasterFlow;
use App\Models\Project;
use App\Models\ProjectProcess;
use App\Support\ProjectFlowBuilder;
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
        abort_unless($process->project_id === $project->id, 404);
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
}
