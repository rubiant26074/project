<?php

namespace Database\Seeders;

use App\Models\MasterFlow;
use App\Models\Project;
use App\Models\User;
use App\Support\ProjectFlowBuilder;
use App\Support\ProjectProgressService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@project-control.local'],
            [
                'name' => 'Administrator',
                'role' => 'admin',
                'password' => 'admin12345',
            ],
        );

        User::query()->updateOrCreate(
            ['email' => 'user@project-control.local'],
            [
                'name' => 'Project User',
                'role' => 'user',
                'password' => 'user12345',
            ],
        );

        $flow = MasterFlow::create([
            'name' => 'Standard Panel Project',
            'description' => 'Master flow utama untuk project panel dan pengadaan.',
            'is_active' => true,
        ]);

        $steps = collect([
            ['code' => 'client', 'name' => 'Client', 'x' => 4, 'y' => 10, 'sort' => 1, 'checklists' => ['WO diterima dari client', 'Dokumen kebutuhan awal lengkap', 'Target jadwal disepakati']],
            ['code' => 'sales', 'name' => 'Sales', 'x' => 4, 'y' => 34, 'sort' => 2, 'checklists' => ['Penawaran komersial disetujui', 'Purchase order diterima']],
            ['code' => 'pm', 'name' => 'PM', 'x' => 20, 'y' => 10, 'sort' => 3, 'checklists' => ['Kickoff internal dilakukan', 'PIC tiap departemen ditetapkan']],
            ['code' => 'ctp', 'name' => 'CTP', 'x' => 20, 'y' => 34, 'sort' => 4, 'checklists' => ['Schedule kerja dibuat', 'Baseline progress ditetapkan']],
            ['code' => 'engineering', 'name' => 'Engineering', 'x' => 36, 'y' => 18, 'sort' => 5, 'checklists' => ['Drawing approval client', 'BOM final diterbitkan', 'Single line diagram final']],
            ['code' => 'elektrikal', 'name' => 'Elektrikal', 'x' => 36, 'y' => 42, 'sort' => 6, 'checklists' => ['Layout panel selesai', 'Review wiring selesai', 'Finalisasi terminal list']],
            ['code' => 'mekanikal', 'name' => 'Mekanikal', 'x' => 52, 'y' => 18, 'sort' => 7, 'checklists' => ['Shop drawing mekanik final', 'Material enclosure disetujui']],
            ['code' => 'frm', 'name' => 'FRM', 'x' => 52, 'y' => 42, 'sort' => 8, 'checklists' => ['Material request dibuat', 'Request diverifikasi PM', 'Request dilepas ke procurement']],
            ['code' => 'gudang-prep', 'name' => 'Gudang', 'x' => 42, 'y' => 70, 'sort' => 9, 'checklists' => ['Stok internal dicek', 'Material existing dipisahkan']],
            ['code' => 'eng-support', 'name' => 'Engineering Support', 'x' => 42, 'y' => 88, 'sort' => 10, 'checklists' => ['Data material pengganti diberikan']],
            ['code' => 'procurement', 'name' => 'Procurement', 'x' => 60, 'y' => 70, 'sort' => 11, 'checklists' => ['RFQ dikirim ke vendor', 'Perbandingan penawaran selesai']],
            ['code' => 'client-approval', 'name' => 'Client Approval', 'x' => 60, 'y' => 88, 'sort' => 12, 'checklists' => ['Approval merek utama', 'Approval substitusi material']],
            ['code' => 'admin-kie', 'name' => 'Admin KIE', 'x' => 50, 'y' => 92, 'sort' => 13, 'checklists' => ['Dokumen release final', 'Arsip penutupan tahap awal']],
            ['code' => 'pfm', 'name' => 'PFM', 'x' => 68, 'y' => 32, 'sort' => 14, 'checklists' => ['Planning fabrikasi disusun', 'Urutan produksi disetujui']],
            ['code' => 'bina', 'name' => 'Bina', 'x' => 84, 'y' => 42, 'sort' => 15, 'checklists' => ['Jadwal manpower tersedia', 'Tooling area siap']],
            ['code' => 'fabrikasi', 'name' => 'Fabrikasi', 'x' => 84, 'y' => 62, 'sort' => 16, 'checklists' => ['Cutting material dimulai', 'Panel machining selesai']],
            ['code' => 'assy', 'name' => 'Assy', 'x' => 84, 'y' => 82, 'sort' => 17, 'checklists' => ['Komponen utama terpasang', 'Wiring internal selesai']],
            ['code' => 'test-internal', 'name' => 'Test Internal', 'x' => 96, 'y' => 96, 'sort' => 18, 'checklists' => ['Checklist FAT internal lengkap', 'Hasil pengujian terdokumentasi']],
        ])->mapWithKeys(function (array $stepData) use ($flow) {
            $step = $flow->steps()->create([
                'code' => $stepData['code'],
                'name' => $stepData['name'],
                'position_x' => $stepData['x'],
                'position_y' => $stepData['y'],
                'sort_order' => $stepData['sort'],
            ]);

            foreach ($stepData['checklists'] as $index => $label) {
                $step->checklistTemplates()->create([
                    'label' => $label,
                    'sort_order' => $index + 1,
                ]);
            }

            return [$stepData['code'] => $step];
        });

        foreach ([
            ['client', 'sales'],
            ['client', 'pm'],
            ['sales', 'ctp'],
            ['pm', 'engineering'],
            ['ctp', 'pm'],
            ['engineering', 'elektrikal'],
            ['engineering', 'mekanikal'],
            ['elektrikal', 'frm'],
            ['mekanikal', 'frm'],
            ['frm', 'pfm'],
            ['frm', 'gudang-prep'],
            ['frm', 'procurement'],
            ['gudang-prep', 'eng-support'],
            ['eng-support', 'admin-kie'],
            ['procurement', 'client-approval'],
            ['client-approval', 'admin-kie'],
            ['pfm', 'bina'],
            ['bina', 'fabrikasi'],
            ['fabrikasi', 'assy'],
            ['assy', 'test-internal'],
        ] as [$from, $to]) {
            $flow->connections()->create([
                'from_step_id' => $steps[$from]->id,
                'to_step_id' => $steps[$to]->id,
            ]);
        }

        $builder = app(ProjectFlowBuilder::class);
        $progressService = app(ProjectProgressService::class);

        $projectOne = Project::create([
            'master_flow_id' => $flow->id,
            'wo_number' => '260050033',
            'client_name' => 'PT Patra SK',
            'project_name' => 'Vacuum Contactor',
            'description' => 'Revamp panel vakum contactor untuk area produksi utama.',
        ]);
        $builder->build($projectOne);
        $this->markDone($projectOne, [
            'client' => [0, 1, 2],
            'sales' => [0, 1],
            'pm' => [0, 1],
            'ctp' => [0, 1],
            'elektrikal' => [0, 1],
            'frm' => [0, 1],
            'gudang-prep' => [0, 1],
            'eng-support' => [0],
            'procurement' => [0],
            'client-approval' => [0],
        ]);
        $progressService->syncProject($projectOne->fresh('processes.checklists'));

        $projectTwo = Project::create([
            'master_flow_id' => $flow->id,
            'wo_number' => '260050034',
            'client_name' => 'PT Fukudenryoku',
            'project_name' => 'Vacuum Circuit Breaker',
            'description' => 'Pembuatan panel VCB baru untuk area utility.',
        ]);
        $builder->build($projectTwo);
        $this->markDone($projectTwo, [
            'client' => [0, 1, 2],
            'sales' => [0],
        ]);
        $progressService->syncProject($projectTwo->fresh('processes.checklists'));

        $projectThree = Project::create([
            'master_flow_id' => $flow->id,
            'wo_number' => '260050035',
            'client_name' => 'PT Sumber Aneka Gas',
            'project_name' => 'Penggantian CT',
            'description' => 'Penggantian current transformer dan penyesuaian wiring lapangan.',
        ]);
        $builder->build($projectThree);
        foreach ($projectThree->processes as $process) {
            foreach ($process->checklists as $checklist) {
                $checklist->update(['is_done' => true]);
            }
        }
        $progressService->syncProject($projectThree->fresh('processes.checklists'));
    }

    private function markDone(Project $project, array $doneMap): void
    {
        $project->load('processes.checklists');

        foreach ($project->processes as $process) {
            $doneIndexes = $doneMap[$process->code] ?? [];

            foreach ($process->checklists as $index => $checklist) {
                $checklist->update([
                    'is_done' => in_array($index, $doneIndexes, true),
                ]);
            }
        }
    }
}
