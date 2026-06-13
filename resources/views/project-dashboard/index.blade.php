@extends('layouts.app')

@section('content')
    <main class="page">
        <section class="hero-banner">
            <div class="hero-banner-copy">
                <p class="eyebrow">Control Project Manager</p>
                <h1>Status Semua Proyek</h1>
                <p class="hero-copy">Pantau seluruh project dalam bentuk kartu kanban. Klik salah satu kartu untuk membuka flow proses per project.</p>
            </div>

            <div class="hero-banner-side">
                <div class="hero-actions">
                    @if (auth()->user()->canAccess('project_create'))
                        <a class="toolbar-button toolbar-button-primary" href="{{ route('projects.create') }}">Tambah Project</a>
                    @endif
                    @if (auth()->user()->canManageMasterFlows())
                        <a class="toolbar-button" href="{{ route('master-flows.index') }}">Atur Master Flow</a>
                    @endif
                </div>
            </div>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <span>Total Project</span>
                <strong>{{ count($projects) }}</strong>
            </article>
            <article class="summary-card">
                <span>Master Flow Aktif</span>
                <strong>{{ $masterFlows->where('is_active', true)->count() }}</strong>
            </article>
            <article class="summary-card">
                <span>Total Master Flow</span>
                <strong>{{ $masterFlows->count() }}</strong>
            </article>
            <article class="summary-card">
                <span>Template Step</span>
                <strong>{{ $masterFlows->sum('steps_count') }}</strong>
            </article>
            <article class="summary-card">
                <span>Project Database</span>
                <strong>{{ $projects->count() }}</strong>
            </article>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Kartu Project</p>
                    <h2>Daftar Semua Project</h2>
                </div>
                <span class="inline-meta">{{ $projects->count() }} project aktif di dashboard</span>
            </div>

            <div class="project-card-grid">
                @forelse ($projects as $project)
                    @php
                        $projectNotificationCount = auth()->user()->unreadProjectNotificationCount($project);
                    @endphp
                    <div class="project-card status-{{ $project->status }}">
                        <a class="project-card-main" href="{{ route('projects.show', $project) }}">
                            <div class="project-card-head">
                                <p class="project-card-label">{{ strtoupper($project->status) }}</p>
                                <div class="project-card-head-right">
                                    @if ($projectNotificationCount > 0)
                                        <span class="notification-chip" aria-label="Ada {{ $projectNotificationCount }} pesan baru di project ini">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M15 17H5l1.5-2.2c.5-.8.8-1.8.8-2.8V9a4.5 4.5 0 1 1 9 0v3c0 1 .3 2 .8 2.8L19 17h-4Z"></path>
                                                <path d="M10 18a2 2 0 0 0 4 0"></path>
                                            </svg>
                                            <strong>{{ $projectNotificationCount }}</strong>
                                        </span>
                                    @endif
                                    <span class="project-status-chip project-status-chip-{{ $project->status }}">
                                    {{ $project->progress }}%
                                </span>
                                </div>
                            </div>
                            <strong>{{ $project->wo_number }}</strong>
                            <h3>{{ $project->client_name }}</h3>
                            <p>{{ $project->project_name }}</p>
                            <div class="project-card-meta">
                                <span>Start {{ $project->start_project?->format('d M Y') ?? '-' }}</span>
                                <span>Finish {{ $project->target_finish?->format('d M Y') ?? '-' }}</span>
                            </div>
                            <div class="project-card-meta">
                                <span>{{ $project->processes->where('status', 'close')->count() }}/{{ $project->processes->count() }} proses close</span>
                                <span>{{ ucfirst($project->status) }}</span>
                            </div>
                        </a>
                        @if (auth()->user()->canAccess('project_view') || auth()->user()->canAccess('project_update') || auth()->user()->canAccess('project_delete'))
                            <div class="project-card-actions">
                                @if (auth()->user()->canAccess('project_update'))
                                    <a class="toolbar-button toolbar-button-small" href="{{ route('projects.edit', $project) }}">Edit</a>
                                @endif
                                @if (auth()->user()->canAccess('project_delete'))
                                    <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus project ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                                    </form>
                                @endif
                                @if (auth()->user()->canAccess('project_view') && Route::has('projects.tv'))
                                    <a class="toolbar-button toolbar-button-small toolbar-button-tv" href="{{ route('projects.tv', $project) }}">TV Dashboard</a>
                                @endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="empty-state">
                        Belum ada project. Klik `Tambah Project` untuk mulai membuat kartu kanban pertama.
                    </div>
                @endforelse
            </div>
        </section>
    </main>
@endsection
