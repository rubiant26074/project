@extends('layouts.app')

@section('content')
    <main class="page">
        <section class="hero-banner">
            <div>
                <p class="eyebrow">Daftar Checklist</p>
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

            <form method="GET" action="{{ route('my-tasks.index') }}" class="filter-panel">
                <div class="filter-field">
                    <label for="filter-wo">Nomor WO</label>
                    <select id="filter-wo" name="wo" onchange="this.form.submit()">
                        <option value="">Semua WO</option>
                        @foreach ($filterOptions['wo'] as $wo)
                            <option value="{{ $wo }}" @selected($filters['wo'] === $wo)>{{ $wo }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="filter-project">Project</label>
                    <select id="filter-project" name="project" onchange="this.form.submit()">
                        <option value="">Semua Project</option>
                        @foreach ($filterOptions['project'] as $projectName)
                            <option value="{{ $projectName }}" @selected($filters['project'] === $projectName)>{{ $projectName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-field">
                    <label for="filter-client">Client</label>
                    <select id="filter-client" name="client" onchange="this.form.submit()">
                        <option value="">Semua Client</option>
                        @foreach ($filterOptions['client'] as $clientName)
                            <option value="{{ $clientName }}" @selected($filters['client'] === $clientName)>{{ $clientName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-actions">
                    <a class="toolbar-button toolbar-button-small" href="{{ route('my-tasks.index') }}">Reset</a>
                </div>
            </form>

            <div class="table-shell">
                <table class="admin-table my-task-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th>Checklist</th>
                            <th>Project</th>
                            <th>Proses</th>
                            <th>Target</th>
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
                                    @if (auth()->user()->canUpdateProcess($process))
                                        <form method="POST" action="{{ route('projects.processes.checklists.update', [$project, $process, $task]) }}" class="task-target-form">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="label" value="{{ $task->label }}">
                                            <input type="hidden" name="sort_order" value="{{ $task->sort_order }}">
                                            <input type="hidden" name="document_link" value="{{ $task->document_link }}">
                                            <input type="hidden" name="is_done" value="{{ $task->is_done ? 1 : 0 }}">
                                            <label>
                                                <span>Mulai</span>
                                                <input type="date" name="target_start" value="{{ $task->target_start?->format('Y-m-d') }}">
                                            </label>
                                            <label>
                                                <span>Selesai</span>
                                                <input type="date" name="target_finish" value="{{ $task->target_finish?->format('Y-m-d') }}">
                                            </label>
                                            <button class="toolbar-button toolbar-button-small" type="submit">Simpan</button>
                                        </form>
                                    @else
                                        <div class="inline-meta inline-meta-compact">
                                            <span>Mulai {{ $task->target_start?->format('d M Y') ?? '-' }}</span>
                                            <span>Selesai {{ $task->target_finish?->format('d M Y') ?? '-' }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a class="toolbar-button toolbar-button-small" href="{{ route('projects.processes.show', [$project, $process]) }}">Buka Proses</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
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
