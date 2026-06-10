@extends('layouts.app')

@section('title', 'Project Detail - ' . ($project['name'] ?? 'Project'))

@section('content')
<div class="project-detail-container">
    <!-- Header with Back Button -->
    <div class="detail-header">
        <a href="{{ route('projects.overview') }}" class="back-button">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Back to Overview
        </a>
        <div class="header-content">
            <h1>{{ $project['name'] ?? 'Project Name' }}</h1>
            <div class="header-meta">
                <span class="wo-number">WO: {{ $project['wo_number'] ?? 'N/A' }}</span>
                <span class="customer">{{ $project['customer'] ?? 'Customer' }}</span>
                <span class="status" :class="'status-' . strtolower($project['status'] ?? 'at-risk')">
                    {{ $project['status'] ?? 'AT RISK' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Overall Progress -->
    <div class="progress-section">
        <div class="progress-card">
            <h3>Overall Progress</h3>
            <div class="large-progress-bar">
                <div class="progress-fill" style="width: {{ $project['overall_progress'] ?? 0 }}%"></div>
            </div>
            <span class="progress-value">{{ $project['overall_progress'] ?? 0 }}%</span>
        </div>

        <div class="info-card">
            <h3>Project Information</h3>
            <div class="info-items">
                <div class="info-item">
                    <label>Delivery Date</label>
                    <span>{{ $project['delivery_date'] ?? '-' }}</span>
                </div>
                <div class="info-item">
                    <label>Current Status</label>
                    <span class="status" :class="'status-' . strtolower($project['status'] ?? 'at-risk')">
                        {{ $project['status'] ?? 'AT RISK' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Breakdown -->
    <div class="breakdown-grid">
        <div class="breakdown-card">
            <h3>Engineering</h3>
            <div class="breakdown-items">
                <div class="breakdown-item">
                    <span>Drawing Approval</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 25%"></div>
                    </div>
                    <span class="percent">25%</span>
                </div>
                <div class="breakdown-item">
                    <span>CTP / Dokumen</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="percent">0%</span>
                </div>
                <div class="breakdown-item">
                    <span>BOM Release</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 15%"></div>
                    </div>
                    <span class="percent">15%</span>
                </div>
            </div>
        </div>

        <div class="breakdown-card">
            <h3>Procurement</h3>
            <div class="breakdown-items">
                <div class="breakdown-item">
                    <span>Material Progress</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 10%"></div>
                    </div>
                    <span class="percent">10%</span>
                </div>
                <div class="breakdown-item">
                    <span>SCC / PO Follow Up</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 30%"></div>
                    </div>
                    <span class="percent">30%</span>
                </div>
                <div class="breakdown-item">
                    <span>Outstanding PO</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 5%"></div>
                    </div>
                    <span class="percent">5%</span>
                </div>
            </div>
        </div>

        <div class="breakdown-card">
            <h3>Production</h3>
            <div class="breakdown-items">
                <div class="breakdown-item">
                    <span>Fabrication</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="percent">0%</span>
                </div>
                <div class="breakdown-item">
                    <span>Assembly</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="percent">0%</span>
                </div>
                <div class="breakdown-item">
                    <span>Wiring</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="percent">0%</span>
                </div>
            </div>
        </div>

        <div class="breakdown-card">
            <h3>Testing & Delivery</h3>
            <div class="breakdown-items">
                <div class="breakdown-item">
                    <span>Testing / FAT</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="percent">0%</span>
                </div>
                <div class="breakdown-item">
                    <span>Packing</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="percent">0%</span>
                </div>
                <div class="breakdown-item">
                    <span>Delivery</span>
                    <div class="mini-progress">
                        <div class="progress-fill" style="width: 0%"></div>
                    </div>
                    <span class="percent">0%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stage Progress Chart -->
    <div class="stage-chart-container">
        <h3>Progress by Stage</h3>
        <canvas id="stageDetailChart" height="100"></canvas>
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

    <!-- Issues & Risks -->
    <div class="issues-section">
        <h3>Issues / Risk</h3>
        <div class="issues-table">
            <table>
                <thead>
                    <tr>
                        <th>Issue / Risk</th>
                        <th>Impact</th>
                        <th>Action Plan</th>
                        <th>PIC</th>
                        <th>Target Date</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Delayed Material Arrival</td>
                        <td>High</td>
                        <td>Expedite supplier order</td>
                        <td>PT Rekayas</td>
                        <td>15 Jun 2026</td>
                    </tr>
                    <tr>
                        <td>Design Revision Pending</td>
                        <td>High</td>
                        <td>Follow up with customer</td>
                        <td>Eng Team</td>
                        <td>10 Jun 2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .project-detail-container {
            padding: 20px;
            background: #f5f5f5;
            min-height: 100vh;
        }

        /* Detail Header */
        .detail-header {
            background: linear-gradient(135deg, #001a4d 0%, #003380 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            position: relative;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: white;
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 15px;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background 0.3s ease;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .header-content h1 {
            margin: 0 0 10px 0;
            font-size: 32px;
            font-weight: bold;
        }

        .header-meta {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            font-size: 14px;
            opacity: 0.95;
        }

        .header-meta .wo-number,
        .header-meta .customer {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Progress Section */
        .progress-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .progress-card,
        .info-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .progress-card h3,
        .info-card h3 {
            margin: 0 0 20px 0;
            font-size: 16px;
            font-weight: bold;
            color: #001a4d;
        }

        .large-progress-bar {
            width: 100%;
            height: 20px;
            background: #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 15px;
        }

        .large-progress-bar .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%);
            transition: width 0.3s ease;
        }

        .progress-value {
            display: block;
            font-size: 24px;
            font-weight: bold;
            color: #001a4d;
            text-align: center;
        }

        .info-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: #f9fafb;
            border-radius: 4px;
        }

        .info-item label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: 600;
        }

        .info-item span {
            font-size: 14px;
            font-weight: bold;
            color: #001a4d;
        }

        /* Breakdown Grid */
        .breakdown-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .breakdown-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .breakdown-card h3 {
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: bold;
            color: #001a4d;
            text-transform: uppercase;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }

        .breakdown-items {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .breakdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 12px;
        }

        .breakdown-item span:first-child {
            flex: 1;
            color: #6b7280;
        }

        .mini-progress {
            flex: 1;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .mini-progress .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%);
        }

        .breakdown-item .percent {
            color: #001a4d;
            font-weight: bold;
            min-width: 30px;
            text-align: right;
        }

        /* Stage Chart */
        .stage-chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .stage-chart-container h3 {
            margin: 0 0 20px 0;
            font-size: 16px;
            font-weight: bold;
            color: #001a4d;
            text-transform: uppercase;
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

        /* Issues Section */
        .issues-section {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .issues-section h3 {
            margin: 0 0 20px 0;
            font-size: 16px;
            font-weight: bold;
            color: #001a4d;
            text-transform: uppercase;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }

        .issues-table {
            overflow-x: auto;
        }

        .issues-table table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .issues-table thead {
            background: #f3f4f6;
        }

        .issues-table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            color: #001a4d;
            border-bottom: 2px solid #e5e7eb;
        }

        .issues-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
        }

        .issues-table tr:hover {
            background: #f9fafb;
        }

        /* Status Badge */
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 12px;
        }

        .status-at-risk {
            background: #fef3c7;
            color: #d97706;
        }

        .status-on-track {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-delay {
            background: #fee2e2;
            color: #dc2626;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .progress-section {
                grid-template-columns: 1fr;
            }

            .breakdown-grid {
                grid-template-columns: 1fr;
            }

            .header-content h1 {
                font-size: 24px;
            }

            .header-meta {
                flex-direction: column;
                gap: 8px;
            }

            .issues-table table {
                font-size: 11px;
            }

            .issues-table th,
            .issues-table td {
                padding: 8px;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeDetailCharts();
        });

        function initializeDetailCharts() {
            const stageCtx = document.getElementById('stageDetailChart');

            if (stageCtx && typeof Chart !== 'undefined') {
                new Chart(stageCtx, {
                    type: 'bar',
                    data: {
                        labels: ['PO', 'ENG', 'BOM', 'PUR', 'MAT IN', 'FAB', 'ASSY', 'WIRING', 'TEST', 'PACK', 'DO'],
                        datasets: [{
                            label: 'Progress %',
                            data: [5, 10, 8, 5, 2, 0, 0, 0, 0, 0, 0],
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
                                max: 15
                            }
                        }
                    }
                });
            }
        }
    </script>
@endpush
@endsection