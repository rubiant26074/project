@extends('layouts.app')

@section('content')
    @php
        $statusLabel = ['open' => 'Open', 'proses' => 'In Progress', 'close' => 'Completed'];
        $statusClass = ['open' => 'pending', 'proses' => 'progress', 'close' => 'done'];
        $varianceClass = $scheduleVariance >= 0 ? 'positive' : 'negative';
    @endphp

    <main class="tv-project-page">
        <header class="tv-project-header">
            <a class="tv-project-back" href="{{ route('dashboard') }}" aria-label="Kembali ke dashboard">&lsaquo;</a>
            <div>
                <h1>{{ config('app.name') }} - DETAIL PROJECT</h1>
                <p>End to End Project Monitoring</p>
            </div>
            <div class="tv-project-header-actions">
                <span>Last Update : {{ now()->format('d M Y H:i') }}</span>
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
                        <article class="tv-project-step tv-project-step-{{ $statusClass[$process->status] ?? 'pending' }} @if ($activeProcess && $activeProcess->is($process)) is-current @endif">
                            <div class="tv-project-step-icon">
                                @switch(($index % 6) + 1)
                                    @case(1)
                                        <svg viewBox="0 0 64 64" aria-hidden="true"><rect x="18" y="12" width="28" height="40" rx="3"></rect><path d="M25 25h14M25 34h14M25 43h9"></path><path d="m20 52-7-7 7-7"></path></svg>
                                        @break
                                    @case(2)
                                        <svg viewBox="0 0 64 64" aria-hidden="true"><rect x="17" y="14" width="30" height="38" rx="3"></rect><path d="m24 42 17-17 5 5-17 17h-5z"></path><path d="M24 24h10"></path></svg>
                                        @break
                                    @case(3)
                                        <svg viewBox="0 0 64 64" aria-hidden="true"><path d="M20 10h20l8 8v42H20z"></path><path d="M40 10v8h8"></path><path d="M26 28h15M26 38h15M26 48h10"></path></svg>
                                        @break
                                    @case(4)
                                        <svg viewBox="0 0 64 64" aria-hidden="true"><path d="M14 18h7l5 24h25l5-18H25"></path><circle cx="31" cy="50" r="4"></circle><circle cx="48" cy="50" r="4"></circle></svg>
                                        @break
                                    @case(5)
                                        <svg viewBox="0 0 64 64" aria-hidden="true"><path d="M18 24v-8h18v12"></path><rect x="10" y="28" width="30" height="16" rx="2"></rect><path d="M40 32h8l6 7v5H40z"></path><circle cx="20" cy="48" r="4"></circle><circle cx="48" cy="48" r="4"></circle></svg>
                                        @break
                                    @default
                                        <svg viewBox="0 0 64 64" aria-hidden="true"><path d="M29 10h8l1.5 7a18 18 0 0 1 5 2l6-4 5 7-5 5a18 18 0 0 1 1 6l7 3-3 8-7-1a18 18 0 0 1-4 5l2 7-8 3-4-6a18 18 0 0 1-6 0l-4 6-8-3 2-7a18 18 0 0 1-4-5l-7 1-3-8 7-3a18 18 0 0 1 1-6l-5-5 5-7 6 4a18 18 0 0 1 5-2z"></path><circle cx="33" cy="33" r="9"></circle></svg>
                                @endswitch
                            </div>
                            <strong>{{ $index + 1 }}. {{ $process->name }}</strong>
                            <small>{{ $process->target_finish?->format('d M Y') ?? '-' }}</small>
                            <em>{{ $statusLabel[$process->status] ?? ucfirst($process->status) }}</em>
                        </article>
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
                                    <td><span class="tv-project-mini-progress"><i style="width: {{ $process->progress }}%"></i><b>{{ $process->progress }}%</b></span></td>
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

