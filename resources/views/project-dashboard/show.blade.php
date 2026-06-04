@extends('layouts.app')

@section('content')
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
                <span>Total Proses</span>
                <strong>{{ $project->processes->count() }}</strong>
            </article>
        </section>

        <div class="toolbar-group">
            <a class="toolbar-button" href="{{ route('projects.edit', $project) }}">Edit Project</a>
            @if ($project->masterFlow)
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
            <div class="flow-chart">
                <svg class="flow-lines" viewBox="0 0 1200 760" preserveAspectRatio="none" aria-hidden="true">
                    <defs>
                        <marker id="arrow" viewBox="0 0 12 12" refX="10" refY="6" markerWidth="5.4" markerHeight="5.4" orient="auto" markerUnits="strokeWidth">
                            <path d="M 0 0 L 12 6 L 0 12 z" fill="#2f3640"></path>
                        </marker>
                    </defs>
                    @foreach ($project->connections as $connection)
                        @php
                            $fromCenterX = ($connection->fromProcess->position_x * 12);
                            $fromCenterY = ($connection->fromProcess->position_y * 7.6) + 43;
                            $fromLeft = $fromCenterX - 85;
                            $fromRight = $fromCenterX + 85;
                            $fromTop = ($connection->fromProcess->position_y * 7.6);
                            $fromBottom = $fromTop + 86;

                            $toCenterX = ($connection->toProcess->position_x * 12);
                            $toCenterY = ($connection->toProcess->position_y * 7.6) + 43;
                            $toLeft = $toCenterX - 85;
                            $toRight = $toCenterX + 85;
                            $toTop = ($connection->toProcess->position_y * 7.6);
                            $toBottom = $toTop + 86;

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
                                        $defaultStartY = $fromBottom;
                                        $defaultEndX = $toCenterX;
                                        $defaultEndY = $toTop;
                                    } else {
                                        $defaultStartX = $fromCenterX;
                                        $defaultStartY = $fromTop;
                                        $defaultEndX = $toCenterX;
                                        $defaultEndY = $toBottom;
                                    }

                                    $defaultMiddleX = $defaultStartX;
                                    $defaultMiddleY = $defaultStartY + (($defaultEndY - $defaultStartY) / 2);
                                } else {
                                    if ($toCenterX >= $fromCenterX) {
                                        $defaultStartX = $fromRight;
                                        $defaultStartY = $fromCenterY;
                                        $defaultEndX = $toLeft;
                                        $defaultEndY = $toCenterY;
                                    } else {
                                        $defaultStartX = $fromLeft;
                                        $defaultStartY = $fromCenterY;
                                        $defaultEndX = $toRight;
                                        $defaultEndY = $toCenterY;
                                    }

                                    $defaultMiddleX = $defaultStartX + (($defaultEndX - $defaultStartX) / 2);
                                    $defaultMiddleY = $defaultStartY;
                                }

                                $startX = $connection->start_x !== null ? $connection->start_x * 12 : $defaultStartX;
                                $startY = $connection->start_y !== null ? $connection->start_y * 7.6 : $defaultStartY;
                                $middle1X = $connection->bend_x !== null ? $connection->bend_x * 12 : $defaultMiddleX;
                                $middle1Y = $connection->bend_y !== null ? $connection->bend_y * 7.6 : $defaultMiddleY;
                                $defaultMiddle2X = $isVerticalPriority ? $defaultEndX : $defaultMiddleX;
                                $defaultMiddle2Y = $isVerticalPriority ? $defaultMiddleY : $defaultEndY;
                                $middle2X = $connection->mid2_x !== null ? $connection->mid2_x * 12 : $defaultMiddle2X;
                                $middle2Y = $connection->mid2_y !== null ? $connection->mid2_y * 7.6 : $defaultMiddle2Y;
                                $endX = $connection->end_x !== null ? $connection->end_x * 12 : $defaultEndX;
                                $endY = $connection->end_y !== null ? $connection->end_y * 7.6 : $defaultEndY;

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
                                        $startY = $fromBottom;
                                        $endX = $toCenterX;
                                        $endY = $toTop;
                                        $midY = $startY + (($endY - $startY) / 2);
                                    } else {
                                        $startX = $fromCenterX;
                                        $startY = $fromTop;
                                        $endX = $toCenterX;
                                        $endY = $toBottom;
                                        $midY = $endY + (($startY - $endY) / 2);
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
                                        $startX = $fromRight;
                                        $startY = $fromCenterY;
                                        $endX = $toLeft;
                                        $endY = $toCenterY;
                                    } else {
                                        $startX = $fromLeft;
                                        $startY = $fromCenterY;
                                        $endX = $toRight;
                                        $endY = $toCenterY;
                                    }

                                    $midX = $startX + (($endX - $startX) / 2);

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
                        class="flow-node flow-node-{{ $process->status }}"
                        href="{{ route('projects.processes.show', [$project, $process]) }}"
                        style="left: {{ $process->position_x }}%; top: {{ $process->position_y }}%;"
                    >
                        <span class="flow-node-badge">{{ $process->progress }}%</span>
                        <strong>{{ $process->name }}</strong>
                        <small>{{ $process->completed_checklists }}/{{ $process->total_checklists }} checklist</small>
                    </a>
                @endforeach
            </div>
        </section>
    </main>
@endsection
