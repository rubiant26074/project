@extends('layouts.app')

@section('content')
    @php
        $donutOnTrack = $totalProjects > 0 ? round(($onTrackProjects / $totalProjects) * 100) : 0;
        $donutAtRisk = $totalProjects > 0 ? round(($atRiskProjects / $totalProjects) * 100) : 0;
        $donutDelay = max(0, 100 - $donutOnTrack - $donutAtRisk);

        $plannedCurve = collect([6, 14, 24, 36, 48, 58, 68, 77, 85, 93, 100]);
        $actualCurve = collect($stageProgress)->pluck('progress');
        $curveWidth = 640;
        $curveHeight = 180;
        $curvePaddingX = 22;
        $curvePaddingY = 16;

        $curvePoint = function (int $index, int $value) use ($plannedCurve, $curveWidth, $curveHeight, $curvePaddingX, $curvePaddingY) {
            $maxIndex = max(1, $plannedCurve->count() - 1);
            $x = $curvePaddingX + (($index / $maxIndex) * ($curveWidth - ($curvePaddingX * 2)));
            $y = $curveHeight - $curvePaddingY - (($value / 100) * ($curveHeight - ($curvePaddingY * 2)));

            return [$x, $y];
        };

        $plannedPoints = $plannedCurve->map(fn (int $value, int $index) => $curvePoint($index, $value))->map(fn (array $point) => $point[0].','.$point[1])->implode(' ');
        $actualPoints = $actualCurve->map(fn (int $value, int $index) => $curvePoint($index, $value))->map(fn (array $point) => $point[0].','.$point[1])->implode(' ');
        $curvePointsFor = function ($stageProgress) use ($curvePoint) {
            return collect($stageProgress)
                ->map(fn (array $stage, int $index) => $curvePoint($index, (int) ($stage['progress'] ?? 0)))
                ->map(fn (array $point) => $point[0].','.$point[1])
                ->implode(' ');
        };
    @endphp

    <main class="page tv-dashboard-page">
        <section class="tv-board">
            <header class="tv-board-header">
                <div>
                    <h1>{{ config('app.name') }}</h1>
                    <p>TV 50" EXECUTIVE DASHBOARD</p>
                    <small>Monitor eksekutif untuk project manager &amp; management: progress, risiko, delivery, dan status kesiapan project.</small>
                </div>
                <div class="tv-header-meta">
                    <span>Last Update: {{ now()->format('d M Y H:i') }}</span>
                    <article>
                        <small>Overall Progress</small>
                        <strong>{{ $avgProgress }}%</strong>
                    </article>
                    <article class="tv-kpi-blue">
                        <small>Total Project</small>
                        <strong>{{ $totalProjects }}</strong>
                    </article>
                    <article class="tv-kpi-green">
                        <small>On Track</small>
                        <strong>{{ $onTrackProjects }}</strong>
                    </article>
                    <article class="tv-kpi-orange">
                        <small>At Risk</small>
                        <strong>{{ $atRiskProjects }}</strong>
                    </article>
                    <article class="tv-kpi-red">
                        <small>Delay</small>
                        <strong>{{ $delayProjects }}</strong>
                    </article>
                </div>
            </header>

            <section class="tv-exec-strip">
                <article class="tv-kpi-card tv-kpi-blue">
                    <span>Overall Progress</span>
                    <strong>{{ $avgProgress }}%</strong>
                    <small>Rata-rata progress aktif</small>
                </article>
                <article class="tv-kpi-card tv-kpi-green">
                    <span>On Track</span>
                    <strong>{{ $onTrackProjects }}</strong>
                    <small>Project dalam jalur aman</small>
                </article>
                <article class="tv-kpi-card tv-kpi-orange">
                    <span>At Risk</span>
                    <strong>{{ $atRiskProjects }}</strong>
                    <small>Perlu perhatian eksekutif</small>
                </article>
                <article class="tv-kpi-card tv-kpi-red">
                    <span>Delay</span>
                    <strong>{{ $delayProjects }}</strong>
                    <small>Project yang tertinggal</small>
                </article>
                <article class="tv-kpi-card tv-kpi-neutral">
                    <span>Delivery 30 Hari</span>
                    <strong>{{ $upcomingProjects->count() }}</strong>
                    <small>Project mendekati delivery</small>
                </article>
            </section>

            <section class="tv-analytics-grid">
                <article class="tv-panel tv-analytics-panel">
                    <h2>BAR CHART — PROGRESS BY STAGE</h2>
                    <div class="tv-bar-grid">
                        @foreach ($stageProgress as $stage)
                            <div class="tv-bar-row">
                                <div class="tv-bar-label">
                                    <strong>{{ $stage['short'] }}</strong>
                                    <span>{{ $stage['progress'] }}%</span>
                                </div>
                                <div class="tv-bar-track"><i style="width: {{ max(4, $stage['progress']) }}%"></i></div>
                            </div>
                        @endforeach
                    </div>
                </article>

                <article class="tv-panel tv-analytics-panel">
                    <h2>DONUT CHART — STATUS DISTRIBUTION</h2>
                    <div class="tv-donut-shell">
                        <div class="tv-donut tv-donut-large" style="--on-track: {{ $donutOnTrack }}; --at-risk: {{ $donutAtRisk }}; --delay: {{ $donutDelay }};">
                            <strong>{{ $totalProjects }}</strong>
                            <span>PROJECT</span>
                        </div>
                        <ul class="tv-donut-list">
                            <li><i class="legend-green"></i> On Track <b>{{ $onTrackProjects }} ({{ $donutOnTrack }}%)</b></li>
                            <li><i class="legend-orange"></i> At Risk <b>{{ $atRiskProjects }} ({{ $donutAtRisk }}%)</b></li>
                            <li><i class="legend-red"></i> Delay <b>{{ $delayProjects }} ({{ $donutDelay }}%)</b></li>
                        </ul>
                    </div>
                </article>

                <article class="tv-panel tv-analytics-panel">
                    <div class="tv-scurve-header">
                        <h2>S-CURVE — PROJECT ROTATION</h2>
                        <span>Auto-slide every 3 seconds</span>
                    </div>
                    <div class="tv-scurve-shell tv-scurve-rotator" data-tv-scurve-rotator>
                        @forelse ($projectStageProgress as $entry)
                            @php
                                $project = $entry['project'];
                                $actualPoints = $curvePointsFor($entry['stageProgress']);
                            @endphp
                            <article class="tv-scurve-slide {{ $loop->first ? 'is-active' : '' }}" data-tv-scurve-slide>
                                <header class="tv-scurve-slide-header">
                                    <div>
                                        <strong>{{ $project->wo_number }}</strong>
                                        <p>{{ $project->project_name }}</p>
                                        <small>{{ $project->client_name }}</small>
                                    </div>
                                    <div class="tv-scurve-chip-row">
                                        <span class="tv-scurve-chip tv-scurve-chip-green">{{ $entry['avgProgress'] }}% progress</span>
                                        <span class="tv-scurve-chip tv-scurve-chip-{{ $project->delivery_status }}">{{ $project->delivery_label }}</span>
                                    </div>
                                </header>
                                <svg class="tv-scurve-chart" viewBox="0 0 {{ $curveWidth }} {{ $curveHeight }}" aria-label="S-curve progress chart for {{ $project->project_name }}">
                                    <line x1="22" y1="164" x2="618" y2="164" class="tv-scurve-axis" />
                                    <line x1="22" y1="20" x2="22" y2="164" class="tv-scurve-axis" />
                                    <polyline points="{{ $plannedPoints }}" class="tv-scurve-planned" />
                                    <polyline points="{{ $actualPoints }}" class="tv-scurve-actual" />
                                    @foreach ($entry['stageProgress'] as $index => $stage)
                                        @php [$x, $y] = $curvePoint($index, (int) ($stage['progress'] ?? 0)); @endphp
                                        <circle cx="{{ $x }}" cy="{{ $y }}" r="4.2" class="tv-scurve-dot" />
                                    @endforeach
                                </svg>
                                <div class="tv-scurve-stage-row">
                                    @foreach ($entry['stageProgress'] as $stage)
                                        <span class="tv-scurve-stage-pill">{{ $stage['short'] }} {{ $stage['progress'] }}%</span>
                                    @endforeach
                                </div>
                            </article>
                        @empty
                            <article class="tv-scurve-slide is-active">
                                <div class="tv-scurve-empty">Belum ada data project untuk ditampilkan.</div>
                            </article>
                        @endforelse
                        @if ($projectStageProgress->count() > 1)
                            <div class="tv-scurve-indicators" aria-label="Project S-curve selector">
                                @foreach ($projectStageProgress as $index => $entry)
                                    <button type="button" class="tv-scurve-indicator {{ $index === 0 ? 'is-active' : '' }}" data-tv-scurve-indicator="{{ $index }}" aria-label="Tampilkan project {{ $entry['project']->wo_number }}"></button>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            </section>

            <section class="tv-stage-strip">
                @foreach ($stageProgress as $index => $stage)
                    <article class="tv-stage-card">
                        <div class="tv-stage-icon">
                            @include('project-dashboard.partials.stage-icon', ['stageName' => $stage['name']])
                        </div>
                        <strong>{{ $index + 1 }}. {{ $stage['name'] }}</strong>
                        <small>{{ $stage['progress'] }}%</small>
                        <div class="tv-stage-bar">
                            <span class="{{ $stage['progress'] < 35 ? 'is-red' : ($stage['progress'] < 70 ? 'is-orange' : 'is-green') }}" style="width: {{ $stage['progress'] }}%"></span>
                        </div>
                    </article>
                @endforeach
            </section>

            <section class="tv-main-grid">
                <article class="tv-panel tv-overview-panel">
                    <h2>PROJECT OVERVIEW</h2>
                    <div class="tv-overview-scroll" data-tv-auto-scroll>
                        <table class="tv-compact-table">
                            <thead>
                                <tr>
                                    <th>No.</th>
                                    <th>Wo Number</th>
                                    <th>Project</th>
                                    <th>Customer</th>
                                    <th>PO Date</th>
                                    <th>Delivery Date</th>
                                    <th>Overall Progress</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overviewProjects as $index => $project)
                                    <tr class="tv-project-row" data-tv-project-url="{{ route('projects.tv', $project) }}" tabindex="0">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $project->wo_number }}</td>
                                        <td>{{ $project->project_name }}</td>
                                        <td>{{ $project->client_name }}</td>
                                        <td>{{ $project->start_project?->format('d M Y') ?? '-' }}</td>
                                        <td>{{ $project->target_finish?->format('d M Y') ?? '-' }}</td>
                                        <td>
                                            <div class="tv-table-progress">
                                                <span style="width: {{ $project->progress }}%"></span>
                                                <em>{{ $project->progress }}%</em>
                                            </div>
                                        </td>
                                        <td><span class="tv-status tv-status-{{ $project->delivery_status }}">{{ $project->delivery_label }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">Belum ada project.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <aside class="tv-panel tv-summary-panel">
                    <h2>PROGRESS SUMMARY</h2>
                    <div class="tv-donut-row">
                        <div class="tv-donut" style="--on-track: {{ $donutOnTrack }}; --at-risk: {{ $donutAtRisk }};">
                            <strong>{{ $totalProjects }}</strong>
                            <span>TOTAL</span>
                        </div>
                        <div class="tv-donut-legend">
                            <span><i class="legend-green"></i> On Track ({{ $onTrackProjects }}) <b>{{ $donutOnTrack }}%</b></span>
                            <span><i class="legend-orange"></i> At Risk ({{ $atRiskProjects }}) <b>{{ $donutAtRisk }}%</b></span>
                            <span><i class="legend-red"></i> Delay ({{ $delayProjects }}) <b>{{ $donutDelay }}%</b></span>
                        </div>
                    </div>
                    <h3>PROGRESS BY STAGE (ALL PROJECT)</h3>
                    <div class="tv-stage-chart">
                        @foreach ($stageProgress as $stage)
                            <div>
                                <span>{{ $stage['progress'] }}%</span>
                                <i style="height: {{ max(6, $stage['progress']) }}%"></i>
                                <small>{{ $stage['short'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </aside>
            </section>

            <section class="tv-department-grid">
                @foreach ($departmentCards as $card)
                    <article class="tv-panel tv-mini-panel">
                        <h2>{{ $card['title'] }}</h2>
                        @foreach ($card['items'] as $item)
                            <div class="tv-mini-row">
                                <span>{{ $item['label'] }}</span>
                                <div class="tv-mini-bar"><i style="width: {{ $item['progress'] }}%"></i></div>
                                <b>{{ $item['progress'] }}%</b>
                            </div>
                        @endforeach
                    </article>
                @endforeach
            </section>

            <section class="tv-bottom-grid">
                <article class="tv-panel">
                    <h2>CRITICAL ISSUE / RISK</h2>
                    <div class="tv-risk-scroll" data-tv-auto-scroll>
                        <table class="tv-compact-table">
                            <thead>
                                <tr>
                                    <th>Project</th>
                                    <th>Issue / Risk</th>
                                    <th>Impact</th>
                                    <th>Action Plan</th>
                                    <th>PIC</th>
                                    <th>Target Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($riskProjects as $project)
                                    <tr>
                                        <td>{{ $project->wo_number }}</td>
                                        <td>{{ $project->project_name }} progress perlu follow up</td>
                                        <td><span class="tv-impact tv-impact-{{ $project->delivery_status }}">{{ $project->delivery_label }}</span></td>
                                        <td>Review schedule, koordinasi material, dan percepatan proses.</td>
                                        <td>PM</td>
                                        <td>{{ $project->target_finish?->format('d M Y') ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">Tidak ada issue kritikal.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </article>

                <article class="tv-panel">
                    <h2>UPCOMING DELIVERY (NEXT 30 DAYS)</h2>
                    <table class="tv-compact-table">
                        <thead>
                            <tr>
                                <th>Project</th>
                                <th>Customer</th>
                                <th>Delivery Date</th>
                                <th>Progress</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($upcomingProjects as $project)
                                <tr>
                                    <td>{{ $project->wo_number }}</td>
                                    <td>{{ $project->client_name }}</td>
                                    <td>{{ $project->target_finish?->format('d M Y') ?? '-' }}</td>
                                    <td>
                                        <div class="tv-table-progress">
                                            <span style="width: {{ $project->progress }}%"></span>
                                            <em>{{ $project->progress }}%</em>
                                        </div>
                                    </td>
                                    <td><span class="tv-status tv-status-{{ $project->delivery_status }}">{{ $project->delivery_label }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">Belum ada delivery 30 hari ke depan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </article>
            </section>
        </section>
    </main>
@endsection
