@extends('layouts.app')

@section('content')
    <main class="page">
        <section class="hero-banner">
            <div>
                <p class="eyebrow">Control Project Manager</p>
                <h1>Status Semua Proyek</h1>
                <p class="hero-copy">Pantau seluruh project dalam bentuk kartu kanban. Klik salah satu kartu untuk membuka flow proses per project.</p>
            </div>
            <div class="hero-actions">
                <a class="toolbar-button toolbar-button-primary" href="{{ route('projects.create') }}">Tambah Project</a>
                <a class="toolbar-button" href="{{ route('master-flows.index') }}">Atur Master Flow</a>
            </div>
        </section>

        <section class="summary-grid">
            <article class="summary-card">
                <span>Total Project</span>
                <strong>{{ count($projects) }}</strong>
            </article>
            <article class="summary-card summary-card-open">
                <span>Masih Open</span>
                <strong>{{ count($groupedProjects['open']) }}</strong>
            </article>
            <article class="summary-card summary-card-proses">
                <span>Sedang Proses</span>
                <strong>{{ count($groupedProjects['proses']) }}</strong>
            </article>
            <article class="summary-card summary-card-close">
                <span>Sudah Close</span>
                <strong>{{ count($groupedProjects['close']) }}</strong>
            </article>
        </section>

        <section class="summary-grid">
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

        <section class="kanban-board">
            <article class="kanban-column kanban-open">
                <header>
                    <h2>Open</h2>
                    <span>{{ count($groupedProjects['open']) }} Project</span>
                </header>
                <div class="kanban-cards">
                    @foreach ($groupedProjects['open'] as $project)
                        <div class="project-card status-open">
                            <a class="project-card-main" href="{{ route('projects.show', $project) }}">
                                <p class="project-card-label">{{ ucfirst($project->status) }}</p>
                                <strong>{{ $project->wo_number }}</strong>
                                <h3>{{ $project->client_name }}</h3>
                                <p>{{ $project->project_name }}</p>
                                <div class="project-card-meta">
                                    <span>Progress {{ $project->progress }}%</span>
                                    <span>{{ $project->processes->where('status', 'close')->count() }}/{{ $project->processes->count() }} proses close</span>
                                </div>
                            </a>
                            <div class="project-card-actions">
                                <a class="toolbar-button toolbar-button-small" href="{{ route('projects.edit', $project) }}">Edit</a>
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus project ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="kanban-column kanban-proses">
                <header>
                    <h2>Proses</h2>
                    <span>{{ count($groupedProjects['proses']) }} Project</span>
                </header>
                <div class="kanban-cards">
                    @foreach ($groupedProjects['proses'] as $project)
                        <div class="project-card status-proses">
                            <a class="project-card-main" href="{{ route('projects.show', $project) }}">
                                <p class="project-card-label">{{ ucfirst($project->status) }}</p>
                                <strong>{{ $project->wo_number }}</strong>
                                <h3>{{ $project->client_name }}</h3>
                                <p>{{ $project->project_name }}</p>
                                <div class="project-card-meta">
                                    <span>Progress {{ $project->progress }}%</span>
                                    <span>{{ $project->processes->where('status', 'proses')->count() }} proses berjalan</span>
                                </div>
                            </a>
                            <div class="project-card-actions">
                                <a class="toolbar-button toolbar-button-small" href="{{ route('projects.edit', $project) }}">Edit</a>
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus project ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="kanban-column kanban-close">
                <header>
                    <h2>Close</h2>
                    <span>{{ count($groupedProjects['close']) }} Project</span>
                </header>
                <div class="kanban-cards">
                    @foreach ($groupedProjects['close'] as $project)
                        <div class="project-card status-close">
                            <a class="project-card-main" href="{{ route('projects.show', $project) }}">
                                <p class="project-card-label">{{ ucfirst($project->status) }}</p>
                                <strong>{{ $project->wo_number }}</strong>
                                <h3>{{ $project->client_name }}</h3>
                                <p>{{ $project->project_name }}</p>
                                <div class="project-card-meta">
                                    <span>Progress {{ $project->progress }}%</span>
                                    <span>{{ $project->processes->count() }} proses selesai</span>
                                </div>
                            </a>
                            <div class="project-card-actions">
                                <a class="toolbar-button toolbar-button-small" href="{{ route('projects.edit', $project) }}">Edit</a>
                                <form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm('Hapus project ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </main>
@endsection
