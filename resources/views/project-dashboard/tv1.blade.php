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
                            @switch($index + 1)
                                @case(1)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="18" y="12" width="28" height="40" rx="3"></rect>
                                        <path d="M25 12h14l-2-4H27z"></path>
                                        <path d="M24 25h16M24 34h16M24 43h10"></path>
                                        <path d="m18 27-5 5 5 5"></path>
                                    </svg>
                                    @break
                                @case(2)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <path d="M14 48h36"></path>
                                        <rect x="18" y="16" width="28" height="30" rx="2"></rect>
                                        <path d="m23 40 17-17 5 5-17 17h-5z"></path>
                                        <path d="M24 26h10M24 32h5"></path>
                                    </svg>
                                    @break
                                @case(3)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <path d="M20 10h20l8 8v42H20z"></path>
                                        <path d="M40 10v8h8"></path>
                                        <rect x="26" y="25" width="5" height="5"></rect>
                                        <rect x="36" y="25" width="5" height="5"></rect>
                                        <rect x="26" y="36" width="5" height="5"></rect>
                                        <rect x="36" y="36" width="5" height="5"></rect>
                                        <path d="M26 50h15"></path>
                                    </svg>
                                    @break
                                @case(4)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <path d="M14 18h7l5 24h25l5-18H25"></path>
                                        <circle cx="31" cy="50" r="4"></circle>
                                        <circle cx="48" cy="50" r="4"></circle>
                                        <path d="M31 30h16M34 36h10"></path>
                                    </svg>
                                    @break
                                @case(5)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="10" y="28" width="30" height="16" rx="2"></rect>
                                        <path d="M40 32h8l6 7v5H40z"></path>
                                        <path d="M18 24v-8h18v12"></path>
                                        <circle cx="20" cy="48" r="4"></circle>
                                        <circle cx="48" cy="48" r="4"></circle>
                                    </svg>
                                    @break
                                @case(6)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <path d="M29 10h8l1.5 7a18 18 0 0 1 5 2l6-4 5 7-5 5a18 18 0 0 1 1 6l7 3-3 8-7-1a18 18 0 0 1-4 5l2 7-8 3-4-6a18 18 0 0 1-6 0l-4 6-8-3 2-7a18 18 0 0 1-4-5l-7 1-3-8 7-3a18 18 0 0 1 1-6l-5-5 5-7 6 4a18 18 0 0 1 5-2z"></path>
                                        <circle cx="33" cy="33" r="9"></circle>
                                    </svg>
                                    @break
                                @case(7)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <circle cx="23" cy="20" r="6"></circle>
                                        <circle cx="43" cy="20" r="6"></circle>
                                        <path d="M13 48v-8a10 10 0 0 1 20 0v8"></path>
                                        <path d="M33 48v-8a10 10 0 0 1 18-6"></path>
                                        <path d="M25 34h14"></path>
                                    </svg>
                                    @break
                                @case(8)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="17" y="12" width="30" height="44" rx="3"></rect>
                                        <path d="M25 20v36M35 20v36"></path>
                                        <path d="M25 28h10M25 38h10M25 48h10"></path>
                                        <circle cx="42" cy="28" r="2"></circle>
                                        <circle cx="42" cy="44" r="2"></circle>
                                    </svg>
                                    @break
                                @case(9)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="18" y="12" width="28" height="40" rx="3"></rect>
                                        <path d="M25 12h14l-2-4H27z"></path>
                                        <path d="m24 32 6 6 14-16"></path>
                                        <circle cx="45" cy="45" r="9"></circle>
                                        <path d="M45 40v5l4 3"></path>
                                    </svg>
                                    @break
                                @case(10)
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <path d="M14 23 32 13l18 10-18 10z"></path>
                                        <path d="M14 23v20l18 10 18-10V23"></path>
                                        <path d="M32 33v20"></path>
                                        <path d="m23 18 18 10"></path>
                                    </svg>
                                    @break
                                @default
                                    <svg viewBox="0 0 64 64" aria-hidden="true">
                                        <rect x="8" y="28" width="32" height="18" rx="2"></rect>
                                        <path d="M40 32h10l6 8v6H40z"></path>
                                        <path d="M14 22h20v6"></path>
                                        <circle cx="18" cy="50" r="4"></circle>
                                        <circle cx="48" cy="50" r="4"></circle>
                                        <path d="M12 34h12M12 40h8"></path>
                                    </svg>
                            @endswitch
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
