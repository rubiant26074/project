<?php

namespace App\Http\Controllers;

use App\Models\MasterFlow;
use App\Models\Project;
use App\Models\ProjectProcess;
use App\Support\ProjectProcessActivityService;
use App\Support\ProjectFlowBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProjectDashboardController extends Controller
{
    public function tv1(): View
    {
        $projects = Project::query()
            ->with(['masterFlow', 'processes.checklists'])
            ->orderBy('start_project')
            ->orderBy('target_finish')
            ->get();

        $today = now()->startOfDay();
        $stageNames = [
            'TERIMA PO',
            'ENGINEERING',
            'BOM RELEASE',
            'PURCHASING',
            'MATERIAL APPROVAL',
            'FABRICATION',
            'ASSEMBLY',
            'WIRING',
            'TESTING / FAT',
            'PACKING',
            'DELIVERY (DO)',
        ];
        $stageShortNames = ['PO', 'ENG', 'BOM', 'PUR', 'MAT IN', 'FAB', 'ASSY', 'WIRING', 'TEST', 'PACK', 'DO'];
        $decoratedProjects = $projects->map(function (Project $project) use ($today) {
            $targetFinish = $project->target_finish;
            $isDelay = $targetFinish && $targetFinish->lt($today) && $project->status !== 'close';
            $isAtRisk = ! $isDelay && $project->status !== 'close' && ($project->progress < 60 || ($targetFinish && $targetFinish->diffInDays($today, false) >= -14));
            $deliveryStatus = $isDelay ? 'delay' : ($isAtRisk ? 'at-risk' : 'on-track');

            $project->delivery_status = $deliveryStatus;
            $project->delivery_label = match ($deliveryStatus) {
                'delay' => 'DELAY',
                'at-risk' => 'AT RISK',
                default => 'ON TRACK',
            };

            return $project;
        });
        $stageProgress = collect($stageNames)->map(function (string $stageName, int $index) use ($projects, $stageShortNames) {
            $values = $projects
                ->map(fn (Project $project) => $project->processes->sortBy('sort_order')->values()->get($index)?->progress)
                ->filter(fn ($progress) => $progress !== null);

            return [
                'name' => $stageName,
                'short' => $stageShortNames[$index] ?? Str::substr($stageName, 0, 4),
                'progress' => $values->count() > 0 ? (int) round($values->avg()) : 0,
            ];
        });

        $procurementProgress = $this->averageProcessProgress($projects, ['purchasing', 'procurement', 'material']);
        $poFollowUpProgress = $this->averageProcessProgress($projects, ['scc', 'po']);

        $departmentCards = [
            [
                'title' => 'ENGINEERING',
                'items' => [
                    ['label' => 'Drawing Approval', 'progress' => $this->averageProcessProgress($projects, ['eng', 'drawing', 'design'])],
                    ['label' => 'CTP / Dokumen', 'progress' => $this->averageProcessProgress($projects, ['ctp', 'doc', 'client'])],
                    ['label' => 'BOM Release', 'progress' => $stageProgress->get(2)['progress']],
                ],
            ],
            [
                'title' => 'PROCUREMENT',
                'items' => [
                    ['label' => 'Material Progress', 'progress' => $procurementProgress],
                    ['label' => 'SCC / PO Follow Up', 'progress' => $poFollowUpProgress],
                    ['label' => 'Outstanding PO', 'progress' => max(0, 100 - $procurementProgress)],
                ],
            ],
            [
                'title' => 'PRODUCTION',
                'items' => [
                    ['label' => 'Fabrication', 'progress' => $this->averageProcessProgress($projects, ['fabrication', 'fabrikasi'])],
                    ['label' => 'Assembly', 'progress' => $this->averageProcessProgress($projects, ['assembly', 'assembling', 'assy'])],
                    ['label' => 'Wiring', 'progress' => $this->averageProcessProgress($projects, ['wiring', 'qc'])],
                ],
            ],
            [
                'title' => 'TESTING & DELIVERY',
                'items' => [
                    ['label' => 'FAT / Testing', 'progress' => $this->averageProcessProgress($projects, ['fat', 'test', 'testing'])],
                    ['label' => 'Packing', 'progress' => $this->averageProcessProgress($projects, ['packing', 'shipment'])],
                    ['label' => 'Delivery', 'progress' => $stageProgress->last()['progress']],
                ],
            ],
            [
                'title' => 'PM KPI',
                'items' => [
                    ['label' => 'Planned Progress', 'progress' => $projects->count() > 0 ? (int) round($projects->avg('progress')) : 0],
                    ['label' => 'Actual Progress', 'progress' => $projects->count() > 0 ? (int) round($projects->avg('progress')) : 0],
                    ['label' => 'On Time Delivery', 'progress' => $projects->count() > 0 ? (int) round(($decoratedProjects->where('delivery_status', 'on-track')->count() / $projects->count()) * 100) : 0],
                ],
            ],
        ];

        return view('project-dashboard.tv1', [
            'projects' => $decoratedProjects,
            'overviewProjects' => $decoratedProjects,
            'stageProgress' => $stageProgress,
            'departmentCards' => $departmentCards,
            'riskProjects' => $decoratedProjects->whereIn('delivery_status', ['delay', 'at-risk'])->values(),
            'upcomingProjects' => $decoratedProjects
                ->filter(fn (Project $project) => $project->target_finish && $project->target_finish->between($today, $today->copy()->addDays(30)))
                ->take(5)
                ->values(),
            'totalProjects' => $projects->count(),
            'onTrackProjects' => $decoratedProjects->where('delivery_status', 'on-track')->count(),
            'atRiskProjects' => $decoratedProjects->where('delivery_status', 'at-risk')->count(),
            'delayProjects' => $decoratedProjects->where('delivery_status', 'delay')->count(),
            'avgProgress' => $projects->count() > 0 ? round($projects->avg('progress'), 1) : 0,
        ]);
    }

    private function averageProcessProgress($projects, array $keywords): int
    {
        $values = $projects
            ->flatMap(fn (Project $project) => $project->processes)
            ->filter(function (ProjectProcess $process) use ($keywords): bool {
                $name = strtolower($process->name . ' ' . $process->code);

                foreach ($keywords as $keyword) {
                    if (str_contains($name, strtolower($keyword))) {
                        return true;
                    }
                }

                return false;
            })
            ->pluck('progress');

        return $values->count() > 0 ? (int) round($values->avg()) : 0;
    }

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

    public function tvProject(Project $project, ProjectFlowBuilder $flowBuilder): View
    {
        $flowBuilder->syncLayout($project);

        $project->load([
            'masterFlow',
            'processes.checklists',
        ]);

        $today = now()->startOfDay();
        $processes = $project->processes->sortBy('sort_order')->values();
        $completedProcesses = $processes->where('status', 'close')->count();
        $activeProcess = $processes->firstWhere('status', 'proses') ?? $processes->firstWhere('status', 'open') ?? $processes->last();
        $totalChecklist = $processes->sum('total_checklists');
        $completedChecklist = $processes->sum('completed_checklists');
        $openChecklist = $totalChecklist - $completedChecklist;
        $plannedProgress = $this->calculatePlannedProgress($project, $processes, $today);
        $scheduleVariance = $project->progress - $plannedProgress;
        $criticalItems = $processes
            ->flatMap(fn (ProjectProcess $process) => $process->checklists->map(fn ($checklist) => [
                'process' => $process,
                'checklist' => $checklist,
            ]))
            ->filter(fn (array $item) => ! $item['checklist']->is_done)
            ->sortBy(fn (array $item) => $item['checklist']->target_finish?->timestamp ?? PHP_INT_MAX)
            ->take(6)
            ->values();
        $documents = $processes
            ->flatMap(fn (ProjectProcess $process) => $process->checklists->filter(fn ($checklist) => filled($checklist->document_link))->map(fn ($checklist) => [
                'process' => $process,
                'checklist' => $checklist,
            ]))
            ->take(6)
            ->values();

        return view('project-dashboard.tv-project', [
            'project' => $project,
            'processes' => $processes,
            'completedProcesses' => $completedProcesses,
            'activeProcess' => $activeProcess,
            'totalChecklist' => $totalChecklist,
            'completedChecklist' => $completedChecklist,
            'openChecklist' => $openChecklist,
            'plannedProgress' => $plannedProgress,
            'scheduleVariance' => $scheduleVariance,
            'criticalItems' => $criticalItems,
            'documents' => $documents,
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

    private function calculatePlannedProgress(Project $project, $processes, $today): int
    {
        if (! $project->start_project || ! $project->target_finish || $project->target_finish->lte($project->start_project)) {
            return $processes->count() > 0 ? (int) round($processes->avg('progress')) : 0;
        }

        if ($today->lte($project->start_project)) {
            return 0;
        }

        if ($today->gte($project->target_finish)) {
            return 100;
        }

        $totalDays = max(1, $project->start_project->diffInDays($project->target_finish));
        $elapsedDays = max(0, $project->start_project->diffInDays($today));

        return min(100, (int) round(($elapsedDays / $totalDays) * 100));
    }
}
