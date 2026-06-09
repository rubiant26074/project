@extends('layouts.app')

@section('content')
    @php
        $statusIcons = [
            'close' => '<svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="29"></circle><rect x="18" y="18" width="24" height="28" rx="4"></rect><path d="M24 33l6 6 12-14"></path></svg>',
            'proses' => '<svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="29"></circle><path d="M28 13h8l1 5a17 17 0 0 1 5 2l4-3 6 6-3 4a17 17 0 0 1 2 5l5 1v8l-5 1a17 17 0 0 1-2 5l3 4-6 6-4-3a17 17 0 0 1-5 2l-1 5h-8l-1-5a17 17 0 0 1-5-2l-4 3-6-6 3-4a17 17 0 0 1-2-5l-5-1v-8l5-1a17 17 0 0 1 2-5l-3-4 6-6 4 3a17 17 0 0 1 5-2z"></path><circle cx="32" cy="37" r="7"></circle><path d="M46 39h3l1 3 3 1v3l-3 1-1 3h-3l-1-3-3-1v-3l3-1z"></path><circle cx="47.5" cy="44.5" r="2.5"></circle></svg>',
            'open' => '<svg viewBox="0 0 64 64" aria-hidden="true"><circle cx="32" cy="32" r="29"></circle><path d="M22 22l20 20"></path><path d="M42 22L22 42"></path></svg>',
        ];

        $nodeWidth = 160;
        $nodeHeight = 88;
        $halfNodeWidth = $nodeWidth / 2;
        $nodeHeaderOffset = 34;
        $nodeTopInset = 6;
        $nodeBottomInset = $nodeHeight - 6;
        $verticalArrowGap = 0;
        $chartWidth = 1200;
        $chartHeight = 760;
        $xScale = $chartWidth / 100;
        $yScale = $chartHeight / 100;
        $gridSnapPx = 20;
        $snapToGrid = static fn (float $value): float => round($value / $gridSnapPx) * $gridSnapPx;
    @endphp

    <main class="page">
        <div class="page-actions">
            <a class="back-link" href="{{ route('dashboard') }}">Kembali ke Semua Project</a>
        </div>

        <section class="hero-banner hero-banner-detail">
            <div>
                <p class="eyebrow">Project Manager</p>
                <h1>{{ $project->wo_number }} {{ $project->client_name }} - {{ $project->project_name }}</h1>
                <p class="hero-copy">{{ $project->description }}</p>
            </div>
            <div class="hero-status hero-status-{{ $project->status }}">
                <span>Status</span>
                <strong>{{ ucfirst($project->status) }}</strong>
                <small>Progress total {{ $project->progress }}%</small>
            </div>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <span>Nomor WO</span>
                <strong>{{ $project->wo_number }}</strong>
            </article>
            <article class="summary-card">
                <span>Nama Client</span>
                <strong>{{ $project->client_name }}</strong>
            </article>
            <article class="summary-card">
                <span>Nama Project</span>
                <strong>{{ $project->project_name }}</strong>
            </article>
            <article class="summary-card">
                <span>Start Project</span>
                <strong>{{ $project->start_project?->format('d M Y') ?? '-' }}</strong>
            </article>
            <article class="summary-card">
                <span>Target Finish</span>
                <strong>{{ $project->target_finish?->format('d M Y') ?? '-' }}</strong>
            </article>
            <article class="summary-card">
                <span>Total Proses</span>
                <strong>{{ $project->processes->count() }}</strong>
            </article>
        </section>

        <div class="toolbar-group">
            @if (auth()->user()->canAccess('project_update'))
                <a class="toolbar-button" href="{{ route('projects.edit', $project) }}">Edit Project</a>
            @endif
            @if ($project->masterFlow && auth()->user()->canManageMasterFlows())
                <a class="toolbar-button" href="{{ route('master-flows.edit', $project->masterFlow) }}">Lihat Master Flow</a>
            @endif
        </div>

        <section class="flow-legend">
            <span class="legend-chip legend-open">Open</span>
            <span class="legend-chip legend-proses">Proses</span>
            <span class="legend-chip legend-close">Close</span>
            <p>Klik kotak proses untuk membuka checklist dan progress per proses.</p>
        </section>

        <section class="flow-wrapper">
            <div class="flow-chart flow-chart-project" style="--flow-chart-width: {{ $chartWidth }}px; --flow-chart-height: {{ $chartHeight }}px;">
                <svg class="flow-lines" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <marker id="arrow" viewBox="0 0 12 12" refX="13.6" refY="6" markerWidth="14" markerHeight="14" orient="auto" markerUnits="userSpaceOnUse">
                            <path d="M 0 0 L 12 6 L 0 12 z" fill="#2f3640"></path>
                        </marker>
                    </defs>
                    @foreach ($project->connections as $connection)
                        @php
                            $edgeOverlap = 4;
                            $fromCenterX = ($connection->fromProcess->position_x * $xScale);
                            $fromTop = ($connection->fromProcess->position_y * $yScale);
                            $fromCenterY = $fromTop + $nodeHeaderOffset;
                            $fromLeft = $fromCenterX - $halfNodeWidth;
                            $fromRight = $fromCenterX + $halfNodeWidth;
                            $fromBottom = $fromTop + $nodeBottomInset;

                            $toCenterX = ($connection->toProcess->position_x * $xScale);
                            $toTop = ($connection->toProcess->position_y * $yScale);
                            $toCenterY = $toTop + $nodeHeaderOffset;
                            $toLeft = $toCenterX - $halfNodeWidth;
                            $toRight = $toCenterX + $halfNodeWidth;
                            $toBottom = $toTop + $nodeBottomInset;

                            $hasManualPoints =
                                ($connection->start_x !== null && $connection->start_y !== null) ||
                                ($connection->bend_x !== null && $connection->bend_y !== null) ||
                                ($connection->mid2_x !== null && $connection->mid2_y !== null) ||
                                ($connection->end_x !== null && $connection->end_y !== null);

                            if ($hasManualPoints) {
                                $isVerticalPriority = abs($toCenterY - $fromCenterY) > abs($toCenterX - $fromCenterX) || abs($toCenterX - $fromCenterX) <= 110;

                                if ($isVerticalPriority) {
                                    if ($toCenterY >= $fromCenterY) {
                                        $defaultStartX = $fromCenterX;
                                        $defaultStartY = $fromBottom - $edgeOverlap;
                                        $defaultEndX = $toCenterX;
                                        $defaultEndY = $toTop - $verticalArrowGap;
                                    } else {
                                        $defaultStartX = $fromCenterX;
                                        $defaultStartY = $fromTop + $nodeTopInset + $edgeOverlap;
                                        $defaultEndX = $toCenterX;
                                        $defaultEndY = $toBottom + $verticalArrowGap;
                                    }

                                    $defaultMiddleX = $defaultStartX;
                                    $defaultMiddleY = $snapToGrid($defaultStartY + (($defaultEndY - $defaultStartY) / 2));
                                } else {
                                    if ($toCenterX >= $fromCenterX) {
                                        $defaultStartX = $fromRight - $edgeOverlap;
                                        $defaultStartY = $fromCenterY;
                                        $defaultEndX = $toLeft + $edgeOverlap;
                                        $defaultEndY = $toCenterY;
                                    } else {
                                        $defaultStartX = $fromLeft + $edgeOverlap;
                                        $defaultStartY = $fromCenterY;
                                        $defaultEndX = $toRight - $edgeOverlap;
                                        $defaultEndY = $toCenterY;
                                    }

                                    $defaultMiddleX = $snapToGrid($defaultStartX + (($defaultEndX - $defaultStartX) / 2));
                                    $defaultMiddleY = $defaultStartY;
                                }

                                $startX = $connection->start_x !== null ? $connection->start_x * $xScale : $defaultStartX;
                                $startY = $connection->start_y !== null ? $connection->start_y * $yScale : $defaultStartY;
                                $middle1X = $connection->bend_x !== null ? $connection->bend_x * $xScale : $defaultMiddleX;
                                $middle1Y = $connection->bend_y !== null ? $connection->bend_y * $yScale : $defaultMiddleY;
                                $defaultMiddle2X = $isVerticalPriority ? $defaultEndX : $defaultMiddleX;
                                $defaultMiddle2Y = $isVerticalPriority ? $defaultMiddleY : $defaultEndY;
                                $middle2X = $connection->mid2_x !== null ? $connection->mid2_x * $xScale : $defaultMiddle2X;
                                $middle2Y = $connection->mid2_y !== null ? $connection->mid2_y * $yScale : $defaultMiddle2Y;
                                $endX = $connection->end_x !== null ? $connection->end_x * $xScale : $defaultEndX;
                                $endY = $connection->end_y !== null ? $connection->end_y * $yScale : $defaultEndY;

                                $path = sprintf(
                                    'M %s %s L %s %s L %s %s L %s %s',
                                    $startX,
                                    $startY,
                                    $middle1X,
                                    $middle1Y,
                                    $middle2X,
                                    $middle2Y,
                                    $endX,
                                    $endY,
                                );
                            } else {
                                $dx = $toCenterX - $fromCenterX;
                                $dy = $toCenterY - $fromCenterY;
                                $isVerticalPriority = abs($dx) <= 110 || abs($dy) > abs($dx);

                                if ($isVerticalPriority) {
                                    if ($dy >= 0) {
                                        $startX = $fromCenterX;
                                        $startY = $fromBottom - $edgeOverlap;
                                        $endX = $toCenterX;
                                        $endY = $toTop - $verticalArrowGap;
                                        $midY = $snapToGrid($startY + (($endY - $startY) / 2));
                                    } else {
                                        $startX = $fromCenterX;
                                        $startY = $fromTop + $nodeTopInset + $edgeOverlap;
                                        $endX = $toCenterX;
                                        $endY = $toBottom + $verticalArrowGap;
                                        $midY = $snapToGrid($endY + (($startY - $endY) / 2));
                                    }

                                    $path = sprintf(
                                        'M %s %s L %s %s L %s %s L %s %s',
                                        $startX,
                                        $startY,
                                        $startX,
                                        $midY,
                                        $endX,
                                        $midY,
                                        $endX,
                                        $endY,
                                    );
                                } else {
                                    if ($dx >= 0) {
                                        $startX = $fromRight - $edgeOverlap;
                                        $startY = $fromCenterY;
                                        $endX = $toLeft + $edgeOverlap;
                                        $endY = $toCenterY;
                                    } else {
                                        $startX = $fromLeft + $edgeOverlap;
                                        $startY = $fromCenterY;
                                        $endX = $toRight - $edgeOverlap;
                                        $endY = $toCenterY;
                                    }

                                    $midX = $snapToGrid($startX + (($endX - $startX) / 2));

                                    $path = sprintf(
                                        'M %s %s L %s %s L %s %s L %s %s',
                                        $startX,
                                        $startY,
                                        $midX,
                                        $startY,
                                        $midX,
                                        $endY,
                                        $endX,
                                        $endY,
                                    );
                                }
                            }
                        @endphp
                        <path d="{{ $path }}" marker-end="url(#arrow)" fill="none" class="flow-line"></path>
                    @endforeach
                </svg>

                @foreach ($project->processes as $process)
                    <a
                        class="flow-node flow-node-project flow-node-{{ $process->status }}"
                        href="{{ route('projects.processes.show', [$project, $process]) }}"
                        style="left: {{ $process->position_x }}%; top: {{ $process->position_y }}%;"
                    >
                        <span class="flow-node-badge">{{ $process->progress }}%</span>
                        <div class="flow-node-body">
                            <strong>{{ $process->name }}</strong>
                            <small class="flow-node-meta">{{ $process->completed_checklists }}/{{ $process->total_checklists }} checklist</small>
                        </div>
                        <div class="flow-node-footer">
                            <small class="flow-node-target">
                                {{ $process->target_start?->format('d M Y') ?? 'Belum diatur' }}
                                @if ($process->target_finish)
                                    - {{ $process->target_finish->format('d M Y') }}
                                @endif
                            </small>
                            <span class="flow-node-status-icon flow-node-status-icon-{{ $process->status }}">{!! $statusIcons[$process->status] ?? $statusIcons['open'] !!}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    </main>
@endsection
