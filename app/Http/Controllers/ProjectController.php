<?php

namespace App\Http\Controllers;

use App\Models\MasterFlow;
use App\Models\Project;
use App\Support\ProjectFlowBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function create(): View
    {
        return view('projects.create', [
            'masterFlows' => MasterFlow::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request, ProjectFlowBuilder $flowBuilder): RedirectResponse
    {
        $validated = $request->validate([
            'wo_number' => ['required', 'string', 'max:255', 'unique:projects,wo_number'],
            'client_name' => ['required', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'master_flow_id' => ['required', 'exists:master_flows,id'],
        ]);

        $project = Project::create($validated);
        $flowBuilder->build($project);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Project baru berhasil dibuat dari master flow.');
    }

    public function edit(Project $project): View
    {
        $project->load('masterFlow');

        return view('projects.edit', [
            'project' => $project,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'wo_number' => ['required', 'string', 'max:255', 'unique:projects,wo_number,' . $project->id],
            'client_name' => ['required', 'string', 'max:255'],
            'project_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $project->update($validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('status', 'Data project berhasil diperbarui.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('dashboard')
            ->with('status', 'Project berhasil dihapus.');
    }
}
