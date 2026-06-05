@extends('layouts.app')

@section('content')
    <main class="page">
        <section class="hero-banner">
            <div>
                <p class="eyebrow">Tugas Saya</p>
                <h1>Daftar Checklist Proses</h1>
                <p class="hero-copy">Semua checklist yang masuk ke role {{ $roleLabel }} ditampilkan bersama project dan proses terkait.</p>
            </div>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <span>Checklist Pending</span>
                <strong>{{ $pendingCount }}</strong>
            </article>
            <article class="summary-card">
                <span>Checklist Selesai</span>
                <strong>{{ $doneCount }}</strong>
            </article>
            <article class="summary-card">
                <span>Total Proses</span>
                <strong>{{ $processCount }}</strong>
            </article>
            <article class="summary-card">
                <span>Total Project</span>
                <strong>{{ $projectCount }}</strong>
            </article>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Task List</p>
                    <h2>Tugas Berdasarkan Role</h2>
                </div>
                <span class="inline-meta">{{ $tasks->count() }} checklist ditemukan</span>
            </div>

            <div class="table-shell">
                <table class="admin-table my-task-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Checklist</th>
                            <th>Project</th>
                            <th>Proses</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tasks as $task)
                            @php
                                $process = $task->process;
                                $project = $process->project;
                            @endphp
                            <tr>
                                <td>
                                    <span class="table-chip {{ $task->is_done ? 'table-chip-done' : 'table-chip-pending' }}">
                                        {{ $task->is_done ? 'Selesai' : 'Pending' }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ $task->label }}</strong>
                                    @if ($task->document_link)
                                        <a class="document-link-badge" href="{{ $task->document_link }}" target="_blank" rel="noopener">Link Dokumen</a>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $project->wo_number }}</strong>
                                    <div class="inline-meta inline-meta-compact">
                                        <span>{{ $project->client_name }}</span>
                                        <span>{{ $project->project_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $process->name }}</strong>
                                    <div class="inline-meta inline-meta-compact">
                                        <span>{{ $process->completed_checklists }}/{{ $process->total_checklists }} checklist selesai</span>
                                        <span>Progress {{ $process->progress }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <a class="toolbar-button toolbar-button-small" href="{{ route('projects.processes.show', [$project, $process]) }}">Buka Proses</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">Belum ada checklist yang masuk ke role Anda.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endsection
