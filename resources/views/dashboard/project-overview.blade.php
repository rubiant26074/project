@extends('layouts.app')

@section('title', 'Project Overview')

@section('content')
<div class="project-overview-container">
    <!-- Header Section -->
    <div class="overview-header">
        <h1>PROJECT OVERVIEW</h1>
        <div class="header-stats">
            <div class="stat-box">
                <label>Last Updated</label>
                <span>{{ now()->format('d M Y H:i') }}</span>
            </div>
            <div class="stat-box">
                <label>Overall Progress</label>
                <span class="progress-value">{{ $overall_progress ?? '0.8' }}%</span>
            </div>
            <div class="stat-box">
                <label>Total Project</label>
                <span class="total-count">{{ $total_projects ?? 43 }}</span>
            </div>
            <div class="stat-box on-track">
                <label>On Track</label>
                <span>{{ $on_track ?? 0 }}</span>
            </div>
            <div class="stat-box at-risk">
                <label>At Risk</label>
                <span>{{ $at_risk ?? 42 }}</span>
            </div>
            <div class="stat-box delay">
                <label>Delay</label>
                <span>{{ $delay ?? 1 }}</span>
            </div>
        </div>
    </div>

    <!-- Auto-scrolling Project List -->
    <div class="projects-scroll-container" id="projectsScrollContainer">
        <div class="projects-list" id="projectsList">
            @forelse($projects as $project)
                <a href="{{ route('projects.detail', $project->id) }}" class="project-card" data-project-id="{{ $project->id }}">
                    <div class="project-number">{{ $project->wo_number ?? 'N/A' }}</div>
                    <div class="project-name">{{ $project->name ?? 'Project Name' }}</div>
                    <div class="project-meta">
                        <span class="customer">{{ $project->customer ?? 'Customer' }}</span>
                        <span class="status" :class="'status-' . strtolower($project->status ?? 'at-risk')">
                            {{ $project->status ?? 'AT RISK' }}
                        </span>
                    </div>
                    <div class="project-progress">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: {{ $project->progress ?? 0 }}%"></div>
                        </div>
                        <span class="progress-text">{{ $project->progress ?? 0 }}%</span>
                    </div>
                </a>
            @empty
                <div class="no-projects">
                    <p>Tidak ada data project tersedia</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Statistics Grid -->
    <div class="stats-grid">
        <div class="stats-card">
            <h3>PROGRESS SUMMARY</h3>
            <div class="pie-chart-container">
                <canvas id="progressChart"></canvas>
            </div>
            <div class="chart-legend">
                <div class="legend-item">
                    <span class="legend-color on-track"></span>
                    <span>On Track ({{ $on_track ?? 0 }})</span>
                    <span class="legend-percent">0%</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color at-risk"></span>
                    <span>At Risk ({{ $at_risk ?? 42 }})</span>
                    <span class="legend-percent">98%</span>
                </div>
                <div class="legend-item">
                    <span class="legend-color delay"></span>
                    <span>Delay ({{ $delay ?? 1 }})</span>
                    <span class="legend-percent">2%</span>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <h3>PROGRESS BY STAGE (ALL PROJECT)</h3>
            <canvas id="stageChart"></canvas>
            <div class="stage-labels">
                <span>PO</span>
                <span>ENG</span>
                <span>BOM</span>
                <span>PUR</span>
                <span>MAT IN</span>
                <span>FAB</span>
                <span>ASSY</span>
                <span>WIRING</span>
                <span>TEST</span>
                <span>PACK</span>
                <span>DO</span>
            </div>
        </div>

        <div class="stats-card">
            <h3>ENGINEERING</h3>
            <div class="department-stats">
                <div class="stat-item">
                    <span class="stat-label">Drawing Approval</span>
                    <span class="stat-value">0%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">CTP / Dokumen</span>
                    <span class="stat-value">1%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">BOM Release</span>
                    <span class="stat-value">2%</span>
                </div>
            </div>
        </div>

        <div class="stats-card">
            <h3>PROCUREMENT</h3>
            <div class="department-stats">
                <div class="stat-item">
                    <span class="stat-label">Material Progress</span>
                    <span class="stat-value">0%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">SCC / PO Follow Up</span>
                    <span class="stat-value">1%</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Outstanding PO</span>
                    <span class="stat-value">98%</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .project-overview-container {
            padding: 20px;
            background: #f5f5f5;
            min-height: 100vh;
        }

        /* Header Styles */
        .overview-header {
            background: linear-gradient(135deg, #001a4d 0%, #003380 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .overview-header h1 {
            margin: 0 0 20px 0;
            font-size: 28px;
            font-weight: bold;
        }

        .header-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .stat-box {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-box label {
            display: block;
            font-size: 12px;
            opacity: 0.8;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .stat-box span {
            display: block;
            font-size: 24px;
            font-weight: bold;
        }

        .stat-box.on-track {
            background: linear-gradient(135deg, #28a745 20%, rgba(40, 167, 69, 0.3) 100%);
        }

        .stat-box.at-risk {
            background: linear-gradient(135deg, #ffc107 20%, rgba(255, 193, 7, 0.3) 100%);
        }

        .stat-box.delay {
            background: linear-gradient(135deg, #dc3545 20%, rgba(220, 53, 69, 0.3) 100%);
        }

        /* Project Scroll Container */
        .projects-scroll-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .projects-scroll-container::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 30px;
            background: linear-gradient(to right, white, transparent);
            z-index: 10;
            pointer-events: none;
        }

        .projects-scroll-container::after {
            content: '';
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 30px;
            background: linear-gradient(to left, white, transparent);
            z-index: 10;
            pointer-events: none;
        }

        .projects-list {
            display: flex;
            gap: 15px;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 10px 0;
            scrollbar-width: thin;
            scrollbar-color: #ccc #f1f1f1;
        }

        .projects-list::-webkit-scrollbar {
            height: 6px;
        }

        .projects-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .projects-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        .projects-list::-webkit-scrollbar-thumb:hover {
            background: #999;
        }

        .project-card {
            flex: 0 0 280px;
            padding: 15px;
            background: linear-gradient(135deg, #f8f9ff 0%, #f0f4ff 100%);
            border: 2px solid #e0e7ff;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .project-card:hover {
            border-color: #3b82f6;
            box-shadow: 0 8px 16px rgba(59, 130, 246, 0.2);
            transform: translateY(-4px);
        }

        .project-number {
            font-size: 12px;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
        }

        .project-name {
            font-size: 14px;
            font-weight: bold;
            color: #001a4d;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .project-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }

        .project-meta .customer {
            color: #6b7280;
            flex: 1;
        }

        .project-meta .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            white-space: nowrap;
            margin-left: 5px;
        }

        .project-meta .status-at-risk {
            background: #fef3c7;
            color: #d97706;
        }

        .project-meta .status-on-track {
            background: #dcfce7;
            color: #16a34a;
        }

        .project-meta .status-delay {
            background: #fee2e2;
            color: #dc2626;
        }

        .project-progress {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .progress-bar {
            flex: 1;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .progress-text {
            font-size: 12px;
            font-weight: bold;
            color: #001a4d;
            min-width: 35px;
            text-align: right;
        }

        .no-projects {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }

        /* Statistics Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .stats-card h3 {
            margin: 0 0 20px 0;
            font-size: 16px;
            font-weight: bold;
            color: #001a4d;
            text-transform: uppercase;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }

        .pie-chart-container {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
            height: 200px;
        }

        .chart-legend {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
            flex-shrink: 0;
        }

        .legend-color.on-track {
            background: #28a745;
        }

        .legend-color.at-risk {
            background: #ffc107;
        }

        .legend-color.delay {
            background: #dc3545;
        }

        .legend-percent {
            margin-left: auto;
            font-weight: bold;
            color: #6b7280;
        }

        .stage-labels {
            display: grid;
            grid-template-columns: repeat(11, 1fr);
            gap: 5px;
            margin-top: 15px;
            text-align: center;
            font-size: 11px;
            font-weight: bold;
            color: #6b7280;
        }

        .department-stats {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f9fafb;
            border-radius: 4px;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
        }

        .stat-value {
            font-size: 14px;
            font-weight: bold;
            color: #001a4d;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-stats {
                grid-template-columns: repeat(2, 1fr);
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .project-card {
                flex: 0 0 240px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const scrollContainer = document.getElementById('projectsScrollContainer');
            const projectsList = document.getElementById('projectsList');

            if (!scrollContainer || !projectsList) return;

            let autoScrollInterval;
            let isScrolling = false;

            // Auto-scroll function
            function startAutoScroll() {
                autoScrollInterval = setInterval(() => {
                    if (!isScrolling && projectsList.scrollLeft + projectsList.clientWidth < projectsList.scrollWidth) {
                        projectsList.scrollBy({
                            left: 4,
                            behavior: 'auto'
                        });
                    } else if (projectsList.scrollLeft + projectsList.clientWidth >= projectsList.scrollWidth) {
                        // Reset to beginning
                        projectsList.scrollTo({
                            left: 0,
                            behavior: 'smooth'
                        });
                    }
                }, 50);
            }

            // Stop auto-scroll when mouse approaches
            scrollContainer.addEventListener('mouseenter', () => {
                isScrolling = true;
                clearInterval(autoScrollInterval);
            });

            // Resume auto-scroll when mouse leaves
            scrollContainer.addEventListener('mouseleave', () => {
                isScrolling = false;
                startAutoScroll();
            });

            // Manual scroll detection
            projectsList.addEventListener('scroll', () => {
                isScrolling = true;
                clearInterval(autoScrollInterval);

                // Resume auto-scroll after user stops scrolling
                setTimeout(() => {
                    isScrolling = false;
                    startAutoScroll();
                }, 3000);
            });

            // Start auto-scroll on load
            startAutoScroll();

            // Click handler for project cards
            document.querySelectorAll('.project-card').forEach(card => {
                card.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    if (href) {
                        window.location.href = href;
                    }
                });
            });

            // Chart initialization (if using Chart.js)
            initializeCharts();
        });

        function initializeCharts() {
            const progressCtx = document.getElementById('progressChart');
            const stageCtx = document.getElementById('stageChart');

            if (progressCtx && typeof Chart !== 'undefined') {
                new Chart(progressCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['On Track', 'At Risk', 'Delay'],
                        datasets: [{
                            data: [0, 98, 2],
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                            borderColor: ['#28a745', '#ffc107', '#dc3545'],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }

            if (stageCtx && typeof Chart !== 'undefined') {
                new Chart(stageCtx, {
                    type: 'bar',
                    data: {
                        labels: ['PO', 'ENG', 'BOM', 'PUR', 'MAT IN', 'FAB', 'ASSY', 'WIRING', 'TEST', 'PACK', 'DO'],
                        datasets: [{
                            label: 'Progress %',
                            data: [5, 2, 2, 2, 0, 2, 1, 0, 0, 0, 0],
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                max: 10
                            }
                        }
                    }
                });
            }
        }
    </script>
@endpush
@endsection
