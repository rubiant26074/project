<?php

return [
    'roles' => [
        'admin' => [
            'label' => 'Administrator',
            'description' => 'Akses penuh ke seluruh modul aplikasi.',
        ],
        'manager' => [
            'label' => 'Manager',
            'description' => 'Akses kelola project dan monitor progress tanpa hak kelola role dan user.',
        ],
        'user' => [
            'label' => 'User',
            'description' => 'Akses operasional untuk mengupdate proses project sesuai tugasnya.',
        ],
        'viewer' => [
            'label' => 'Viewer',
            'description' => 'Akses baca dashboard, flow process, dan histori tanpa hak edit.',
        ],
    ],
    'permissions' => [
        'dashboard_view' => [
            'label' => 'Lihat dashboard project',
            'group' => 'Dashboard',
            'roles' => ['admin', 'manager', 'user', 'viewer'],
        ],
        'project_create' => [
            'label' => 'Tambah project baru',
            'group' => 'Project',
            'roles' => ['admin', 'manager'],
        ],
        'project_update' => [
            'label' => 'Edit data project',
            'group' => 'Project',
            'roles' => ['admin', 'manager'],
        ],
        'project_delete' => [
            'label' => 'Hapus project',
            'group' => 'Project',
            'roles' => ['admin', 'manager'],
        ],
        'project_view' => [
            'label' => 'Lihat detail project',
            'group' => 'Project',
            'roles' => ['admin', 'manager', 'user', 'viewer'],
        ],
        'master_flow_manage' => [
            'label' => 'Kelola master flow',
            'group' => 'Master Flow',
            'roles' => ['admin'],
        ],
        'role_manage' => [
            'label' => 'Kelola role',
            'group' => 'Role Management',
            'roles' => ['admin'],
        ],
        'user_manage' => [
            'label' => 'Kelola user dan approval',
            'group' => 'User Management',
            'roles' => ['admin'],
        ],
        'process_view' => [
            'label' => 'Lihat flow project proses',
            'group' => 'Flow Project Proses',
            'roles' => ['admin', 'manager', 'user', 'viewer'],
        ],
        'process_checklist_manage' => [
            'label' => 'Tambah, ubah, hapus checklist proses',
            'group' => 'Flow Project Proses',
            'roles' => ['admin', 'manager', 'user'],
        ],
        'process_comment_add' => [
            'label' => 'Tambah komentar proses',
            'group' => 'Flow Project Proses',
            'roles' => ['admin', 'manager', 'user'],
        ],
        'process_comment_delete_any' => [
            'label' => 'Hapus komentar user lain',
            'group' => 'Flow Project Proses',
            'roles' => ['admin'],
        ],
        'process_comment_delete_own' => [
            'label' => 'Hapus komentar milik sendiri',
            'group' => 'Flow Project Proses',
            'roles' => ['admin', 'manager', 'user'],
        ],
        'process_activity_view' => [
            'label' => 'Lihat timeline dan histori proses',
            'group' => 'Flow Project Proses',
            'roles' => ['admin', 'manager', 'user', 'viewer'],
        ],
    ],
];
