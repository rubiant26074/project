<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the project overview dashboard
     */
    public function projectOverview()
    {
        // Get projects from database or API
        // Example structure - adjust based on your actual data model
        $projects = [
            [
                'id' => 11,
                'wo_number' => '260050020',
                'name' => 'PEMPING ISLAND GAS PIPELINE - Distribution Board',
                'customer' => 'PT Timas Suplindo',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 12,
                'wo_number' => '260050028',
                'name' => 'Drawer LV SWGR MCC',
                'customer' => 'PT Cahaya Turanga Sakti',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 13,
                'wo_number' => '260050032',
                'name' => '(WNTS) to Pemping Island Gas Pipeline - COS Panel',
                'customer' => 'PT Timas Suplindo',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 14,
                'wo_number' => '260050033',
                'name' => 'SUPPLY VCB 24 KV',
                'customer' => 'PT FUKUDENYOU',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 15,
                'wo_number' => '260050034',
                'name' => 'SUPPLY COMPONENT',
                'customer' => 'PT SUMBER ANEKA GAS',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 16,
                'wo_number' => '260050035',
                'name' => 'PENGGANTIAN CT',
                'customer' => 'PT SUMBER ANEKA GAS',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 17,
                'wo_number' => '260050038',
                'name' => 'BUSDUCT',
                'customer' => 'PT JIANXI',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 18,
                'wo_number' => '260050039',
                'name' => 'Add Cost',
                'customer' => 'PT SUMBER ANEKA GAS',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
            [
                'id' => 19,
                'wo_number' => '260050042',
                'name' => 'PT Riau Andalan Pulp&Paper',
                'customer' => 'Intiguna Primatama',
                'status' => 'AT RISK',
                'progress' => 0,
                'po_date' => '-',
                'delivery_date' => '-'
            ],
        ];

        // Calculate statistics
        $stats = [
            'total_projects' => count($projects),
            'on_track' => count(array_filter($projects, fn($p) => $p['status'] === 'ON TRACK')),
            'at_risk' => count(array_filter($projects, fn($p) => $p['status'] === 'AT RISK')),
            'delay' => count(array_filter($projects, fn($p) => $p['status'] === 'DELAY')),
            'overall_progress' => 0.8
        ];

        return view('dashboard.project-overview', [
            'projects' => $projects,
            'total_projects' => $stats['total_projects'],
            'on_track' => $stats['on_track'],
            'at_risk' => $stats['at_risk'],
            'delay' => $stats['delay'],
            'overall_progress' => $stats['overall_progress'],
        ]);
    }

    /**
     * Display the project detail dashboard
     */
    public function projectDetail($id)
    {
        // Get specific project details
        $project = [
            'id' => $id,
            'wo_number' => '260050014',
            'name' => 'MY 3.15KV Project',
            'customer' => 'PT REKAYAS INDUSTRI',
            'status' => 'AT RISK',
            'progress' => 0,
            'delivery_date' => '30 Jun 2026',
            'overall_progress' => 15,
            // Add more details as needed
        ];

        return view('dashboard.project-detail', ['project' => $project]);
    }
}
