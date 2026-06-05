<?php

namespace App\Support;

use App\Models\Project;
use App\Models\ProjectProcess;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProjectFlowBuilder
{
    public function __construct(private readonly ProjectProgressService $progressService)
    {
    }

    public function build(Project $project): void
    {
        $masterFlow = $project->masterFlow()->with([
            'steps.checklistTemplates',
            'connections',
        ])->first();

        if (! $masterFlow) {
            return;
        }

        DB::transaction(function () use ($project, $masterFlow): void {
            $project->processes()->delete();
            $project->connections()->delete();

            $processMap = [];

            foreach ($masterFlow->steps as $step) {
                $process = $project->processes()->create([
                    'master_flow_step_id' => $step->id,
                    'code' => $step->code ?: Str::slug($step->name),
                    'name' => $step->name,
                    'status' => 'open',
                    'progress' => 0,
                    'completed_checklists' => 0,
                    'total_checklists' => $step->checklistTemplates->count(),
                    'position_x' => $step->position_x,
                    'position_y' => $step->position_y,
                    'sort_order' => $step->sort_order,
                    'allowed_role_codes' => $step->allowed_role_codes,
                ]);

                foreach ($step->checklistTemplates as $template) {
                    $process->checklists()->create([
                        'label' => $template->label,
                        'is_done' => false,
                        'sort_order' => $template->sort_order,
                    ]);
                }

                $processMap[$step->id] = $process;
            }

            foreach ($masterFlow->connections as $connection) {
                if (! isset($processMap[$connection->from_step_id], $processMap[$connection->to_step_id])) {
                    continue;
                }

                $project->connections()->create([
                    'from_process_id' => $processMap[$connection->from_step_id]->id,
                    'to_process_id' => $processMap[$connection->to_step_id]->id,
                    'start_x' => $connection->start_x,
                    'start_y' => $connection->start_y,
                    'bend_x' => $connection->bend_x,
                    'bend_y' => $connection->bend_y,
                    'mid2_x' => $connection->mid2_x,
                    'mid2_y' => $connection->mid2_y,
                    'end_x' => $connection->end_x,
                    'end_y' => $connection->end_y,
                ]);
            }
        });

        $this->progressService->syncProject($project->fresh('processes.checklists'));
    }

    public function syncLayout(Project $project): void
    {
        $masterFlow = $project->masterFlow()->with([
            'steps.checklistTemplates',
            'connections',
        ])->first();

        if (! $masterFlow) {
            return;
        }

        $project->load('processes.checklists', 'connections');
        $processMap = $project->processes->keyBy('master_flow_step_id');

        DB::transaction(function () use ($project, $masterFlow, $processMap): void {
            foreach ($masterFlow->steps as $step) {
                $process = $processMap->get($step->id);

                if (! $process) {
                    $process = $project->processes()->create([
                        'master_flow_step_id' => $step->id,
                        'code' => $step->code ?: Str::slug($step->name),
                        'name' => $step->name,
                        'status' => 'open',
                        'progress' => 0,
                        'completed_checklists' => 0,
                        'total_checklists' => $step->checklistTemplates->count(),
                        'position_x' => $step->position_x,
                        'position_y' => $step->position_y,
                        'sort_order' => $step->sort_order,
                        'allowed_role_codes' => $step->allowed_role_codes,
                    ]);

                    foreach ($step->checklistTemplates as $template) {
                        $process->checklists()->create([
                            'label' => $template->label,
                            'is_done' => false,
                            'sort_order' => $template->sort_order,
                        ]);
                    }
                } else {
                    $process->update([
                        'code' => $step->code ?: Str::slug($step->name),
                        'name' => $step->name,
                        'position_x' => $step->position_x,
                        'position_y' => $step->position_y,
                        'sort_order' => $step->sort_order,
                        'allowed_role_codes' => $step->allowed_role_codes,
                    ]);
                }

                $processMap[$step->id] = $process->fresh('checklists');
            }

            $project->connections()->delete();

            foreach ($masterFlow->connections as $connection) {
                if (! isset($processMap[$connection->from_step_id], $processMap[$connection->to_step_id])) {
                    continue;
                }

                $project->connections()->create([
                    'from_process_id' => $processMap[$connection->from_step_id]->id,
                    'to_process_id' => $processMap[$connection->to_step_id]->id,
                    'start_x' => $connection->start_x,
                    'start_y' => $connection->start_y,
                    'bend_x' => $connection->bend_x,
                    'bend_y' => $connection->bend_y,
                    'mid2_x' => $connection->mid2_x,
                    'mid2_y' => $connection->mid2_y,
                    'end_x' => $connection->end_x,
                    'end_y' => $connection->end_y,
                ]);
            }
        });

        $this->progressService->syncProject($project->fresh('processes.checklists'));
    }
}
