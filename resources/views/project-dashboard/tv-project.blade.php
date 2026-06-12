@extends('layouts.app')

@section('content')
    @php
        $statusLabel = ['open' => 'Open', 'proses' => 'In Progress', 'close' => 'Completed'];
        $statusClass = ['open' => 'pending', 'proses' => 'progress', 'close' => 'done'];
        $varianceClass = $scheduleVariance >= 0 ? 'positive' : 'negative';

        $iconDirectory = public_path('icon');
        $iconFiles = is_dir($iconDirectory)
            ? array_values(array_filter(scandir($iconDirectory), static fn ($file) => is_file($iconDirectory . DIRECTORY_SEPARATOR . $file) && preg_match('/\.(png|jpg|jpeg|svg)$/i', $file)))
            : [];

        $iconLookup = [];
        foreach ($iconFiles as $file) {
            $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '', pathinfo($file, PATHINFO_FILENAME)));
            $iconLookup[$slug] = $file;
        }
    @endphp

    <main class="tv-project-page">
        <header class="tv-project-header">
            <a class="tv-project-back" href="{{ route('dashboard.tv1') }}" aria-label="Kembali ke Dashboard TV1">&lsaquo;</a>
            <div>
                <h1>{{ config('app.name') }} - DETAIL PROJECT</h1>
                <p>End to End Project Monitoring</p>
            </div>
            <div class="tv-project-header-actions">
                <span>Last Update : {{ now()->format('d M Y H:i') }}</span>
                <a href="{{ route('dashboard.tv1') }}">Dashboard TV1</a>
                <a href="{{ route('dashboard') }}">Home Dashboard</a>
                @if (auth()->user()->canAccess('project_update'))
                    <a href="{{ route('projects.edit', $project) }}">Edit Project</a>
                @endif
            </div>
        </header>

        <section class="tv-project-info-strip">
            <article>
                <span>Project</span>
                <strong>{{ $project->wo_number }}</strong>
                <em class="tv-project-pill tv-project-pill-{{ $project->status }}">{{ strtoupper($statusLabel[$project->status] ?? $project->status) }}</em>
            </article>
            <article>
                <span>Project Name</span>
                <strong>{{ $project->project_name }}</strong>
                <small>{{ $project->masterFlow?->name ?? 'Master flow belum dipilih' }}</small>
            </article>
            <article>
                <span>Customer</span>
                <strong>{{ $project->client_name }}</strong>
            </article>
            <article>
                <span>WO Number</span>
                <strong>{{ $project->wo_number }}</strong>
            </article>
            <article>
                <span>Start Project</span>
                <strong>{{ $project->start_project?->format('d M Y') ?? '-' }}</strong>
            </article>
            <article>
                <span>Required Delivery</span>
                <strong>{{ $project->target_finish?->format('d M Y') ?? '-' }}</strong>
            </article>
        </section>

        <section class="tv-project-flow" aria-label="Project process flow">
            @forelse ([0, 1] as $loopIndex)
                <div class="tv-project-flow-track" @if ($loopIndex === 1) aria-hidden="true" @endif>
                    @foreach ($processes as $index => $process)
                        <a
                            class="tv-project-step tv-project-step-{{ $statusClass[$process->status] ?? 'pending' }} @if ($activeProcess && $activeProcess->is($process)) is-current @endif"
                            href="{{ route('projects.processes.show', [$project, $process]) }}"
                            @if ($loopIndex === 1) tabindex="-1" @endif
                        >
                            @php
                                $processSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '', $process->name));
                                $processName = strtolower($process->name);

                                $mappedIcon = match (true) {
                                    str_contains($processName, 'packing') || str_contains($processName, 'shipment') => 'PACKING - SHIPMEN.PNG',
                                    str_contains($processName, 'purchasing') => 'PRUSCHASING - BINA.png',
                                    str_contains($processName, 'kom') && str_contains($processName, 'internal') => 'KOM INTERNAL.PNG',
                                    default => $iconLookup[$processSlug] ?? null,
                                };

                                if (!$mappedIcon) {
                                    $fallbackSlug = strtolower(preg_replace('/[^a-z0-9]+/i', '', str_replace([' - ', ' -', '- '], ' ', $process->name)));
                                    $mappedIcon = $iconLookup[$fallbackSlug] ?? null;
                                }
                            @endphp
                            <div class="tv-project-step-icon">
                                @if ($mappedIcon)
                                    <img
                                        class="tv-project-step-icon-image"
                                        src="{{ asset('icon/' . $mappedIcon) }}"
                                        alt=""
                                        aria-hidden="true"
                                    >
                                @else
                                    <span class="tv-project-step-icon-fallback">{{ strtoupper(substr($process->name, 0, 2)) }}</span>
                                @endif
                            </div>
                            <strong>{{ $index + 1 }}. {{ $process->name }}</strong>
                            <small>{{ $process->target_finish?->format('d M Y') ?? '-' }}</small>
                            <em>{{ $statusLabel[$process->status] ?? ucfirst($process->status) }}</em>
                        </a>
                    @endforeach
                </div>
            @empty
                <article class="tv-project-empty">Belum ada stage project.</article>
            @endforelse
        </section>

        <section class="tv-project-progress-strip">
            <span>Overall Progress</span>
            <div class="tv-project-progress"><i style="width: {{ $project->progress }}%"></i><b>{{ $project->progress }}%</b></div>
            <span>Plan Progress</span>
            <strong>{{ $plannedProgress }}%</strong>
            <span>Schedule Variance</span>
            <strong class="tv-project-variance-{{ $varianceClass }}">{{ $scheduleVariance >= 0 ? '+' : '' }}{{ $scheduleVariance }}%</strong>
            <span>Est. Completion</span>
            <strong>{{ $project->target_finish?->format('d M Y') ?? '-' }}</strong>
        </section>

        <section class="tv-project-grid">
            <article class="tv-project-panel tv-project-info-panel">
                <h2>Project Information</h2>
                <dl>
                    <dt>Customer</dt><dd>{{ $project->client_name }}</dd>
                    <dt>Project Name</dt><dd>{{ $project->project_name }}</dd>
                    <dt>WO Number</dt><dd>{{ $project->wo_number }}</dd>
                    <dt>Start Project</dt><dd>{{ $project->start_project?->format('d M Y') ?? '-' }}</dd>
                    <dt>Required Delivery</dt><dd>{{ $project->target_finish?->format('d M Y') ?? '-' }}</dd>
                    <dt>Current Stage</dt><dd>{{ $activeProcess?->name ?? '-' }}</dd>
                    <dt>Remark</dt><dd>{{ $project->description ?: '-' }}</dd>
                </dl>
            </article>

            <article class="tv-project-panel tv-project-stage-panel">
                <h2>Stage Detail</h2>
                <table class="tv-project-table tv-project-stage-head">
                    <thead>
                    <tr>
                        <th>No.</th>
                        <th>Stage</th>
                        <th>Plan Start</th>
                        <th>Plan Finish</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Checklist</th>
                    </tr>
                    </thead>
                </table>
                <div class="tv-project-stage-scroll">
                    @foreach ([0, 1] as $loopIndex)
                        <table class="tv-project-table tv-project-stage-track" @if ($loopIndex === 1) aria-hidden="true" @endif>
                            <tbody>
                            @foreach ($processes as $index => $process)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $process->name }}</td>
                                    <td>{{ $process->target_start?->format('d M Y') ?? '-' }}</td>
                                    <td>{{ $process->target_finish?->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        @php
                                            $stageProgress = is_numeric($process->progress) ? (int) round($process->progress) : null;
                                        @endphp

                                        @if ($stageProgress !== null && $stageProgress >= 0)
                                            <span class="tv-project-stage-progress" aria-label="Progress {{ $stageProgress }}%">
                                                <i style="width: {{ min(max($stageProgress, 0), 100) }}%"></i>
                                                <b>{{ $stageProgress }}%</b>
                                            </span>
                                        @else
                                            <span class="tv-project-stage-progress-empty">-</span>
                                        @endif
                                    </td>
                                    <td><em class="tv-project-status tv-project-status-{{ $statusClass[$process->status] ?? 'pending' }}">{{ $statusLabel[$process->status] ?? ucfirst($process->status) }}</em></td>
                                    <td>{{ $process->completed_checklists }}/{{ $process->total_checklists }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endforeach
                    @if ($processes->isEmpty())
                        <table class="tv-project-table">
                            <tbody>
                            <tr>
                                <td colspan="7">Belum ada stage detail.</td>
                            </tr>
                            </tbody>
                        </table>
                    @endif
                </div>
            </article>

            <aside class="tv-project-side">
                <article class="tv-project-panel">
                    <h2>Key Dates</h2>
                    <dl>
                        <dt>Start Project</dt><dd>{{ $project->start_project?->format('d M Y') ?? '-' }}</dd>
                        <dt>Plan Delivery</dt><dd>{{ $project->target_finish?->format('d M Y') ?? '-' }}</dd>
                        <dt>Active Stage</dt><dd>{{ $activeProcess?->name ?? '-' }}</dd>
                        <dt>Active Target</dt><dd>{{ $activeProcess?->target_finish?->format('d M Y') ?? '-' }}</dd>
                    </dl>
                </article>
                <article class="tv-project-panel">
                    <h2>Checklist Status</h2>
                    <div class="tv-project-donut" style="--done: {{ $totalChecklist > 0 ? round(($completedChecklist / $totalChecklist) * 100) : 0 }};">
                        <strong>{{ $totalChecklist > 0 ? round(($completedChecklist / $totalChecklist) * 100) : 0 }}%</strong>
                        <span>Done</span>
                    </div>
                    <dl>
                        <dt>Total Checklist</dt><dd>{{ $totalChecklist }}</dd>
                        <dt>Completed</dt><dd>{{ $completedChecklist }}</dd>
                        <dt>Open</dt><dd>{{ $openChecklist }}</dd>
                    </dl>
                </article>
            </aside>
        </section>

        <section class="tv-project-bottom-grid">
            <article class="tv-project-panel">
                <h2>Top Critical / Long Lead Item</h2>
                <table class="tv-project-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Stage</th>
                            <th>Required Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($criticalItems as $item)
                            <tr>
                                <td>{{ $item['checklist']->label }}</td>
                                <td>{{ $item['process']->name }}</td>
                                <td>{{ $item['checklist']->target_finish?->format('d M Y') ?? '-' }}</td>
                                <td><em class="tv-project-status tv-project-status-pending">Open</em></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">Tidak ada critical item terbuka.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </article>

            <article class="tv-project-panel">
                <h2>Issue / Risk</h2>
                <table class="tv-project-table">
                    <thead>
                        <tr>
                            <th>Issue / Risk</th>
                            <th>Impact</th>
                            <th>Action Plan</th>
                            <th>PIC</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($criticalItems->take(4) as $item)
                            <tr>
                                <td>{{ $item['checklist']->label }} belum selesai</td>
                                <td><span class="tv-project-impact">Medium</span></td>
                                <td>Follow up {{ $item['process']->name }} dan update checklist.</td>
                                <td>{{ $item['process']->code ?: 'PM' }}</td>
                                <td><em class="tv-project-status tv-project-status-progress">In Progress</em></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Tidak ada issue/risk aktif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </article>
        </section>

        <section class="tv-project-doc-grid">
            <article class="tv-project-panel">
                <h2>Documents</h2>
                <div class="tv-project-doc-list">
                    @forelse ($documents as $item)
                        <a href="{{ $item['checklist']->document_link }}" target="_blank" rel="noopener">
                            <span>📄</span>
                            <strong>{{ $item['checklist']->label }}</strong>
                            <small>{{ $item['process']->name }}</small>
                        </a>
                    @empty
                        <p>Belum ada dokumen/link checklist.</p>
                    @endforelse
                </div>
            </article>
            <article class="tv-project-panel">
                <h2>Notes</h2>
                <ul class="tv-project-notes">
                    <li>Progress aktual {{ $project->progress }}% dari rencana {{ $plannedProgress }}%.</li>
                    <li>{{ $completedProcesses }} dari {{ $processes->count() }} stage sudah completed.</li>
                    <li>{{ $openChecklist }} checklist masih open dan perlu follow up.</li>
                </ul>
            </article>
        </section>
    </main>
@endsection

