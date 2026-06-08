@extends('layouts.app')

@section('content')
    @php
        $donutOnTrack = $totalProjects > 0 ? round(($onTrackProjects / $totalProjects) * 100) : 0;
        $donutAtRisk = $totalProjects > 0 ? round(($atRiskProjects / $totalProjects) * 100) : 0;
        $donutDelay = max(0, 100 - $donutOnTrack - $donutAtRisk);
    @endphp

    <main class="page tv-dashboard-page">
        <section class="tv-board">
            <header class="tv-board-header">
                <div>
                    <h1>{{ config('app.name') }}</h1>
                    <p>ORDER TO DELIVERY DASHBOARD</p>
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
                                    <tr>
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
