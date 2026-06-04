@extends('layouts.app')

@section('content')
    <main class="page">
        <div class="page-actions">
            <a class="back-link" href="{{ route('projects.show', $project) }}">Kembali ke Dashboard Project</a>
        </div>

        <section class="hero-banner hero-banner-detail">
            <div>
                <p class="eyebrow">Checklist Proses</p>
                <h1>{{ $process->name }} - {{ $project->wo_number }}</h1>
                <p class="hero-copy">{{ $project->client_name }} | {{ $project->project_name }}</p>
            </div>
            <div class="hero-status hero-status-{{ $process->status }}">
                <span>Status Proses</span>
                <strong>{{ ucfirst($process->status) }}</strong>
                <small>{{ $process->completed_checklists }} dari {{ $process->total_checklists }} item selesai</small>
            </div>
        </section>

        <section class="process-layout process-layout-expanded">
            <article class="process-card">
                <div class="process-card-head">
                    <div>
                        <p class="eyebrow">Progress Proses</p>
                        <h2>{{ $process->progress }}%</h2>
                    </div>
                    <span class="legend-chip legend-{{ $process->status }}">{{ ucfirst($process->status) }}</span>
                </div>

                <div class="progress-bar">
                    <div class="progress-bar-fill" style="width: {{ $process->progress }}%;"></div>
                </div>

                <div class="process-stats">
                    <div>
                        <span>Checklist Selesai</span>
                        <strong>{{ $process->completed_checklists }}</strong>
                    </div>
                    <div>
                        <span>Checklist Pending</span>
                        <strong>{{ $process->total_checklists - $process->completed_checklists }}</strong>
                    </div>
                </div>
            </article>

            <article class="process-card">
                <p class="eyebrow">Daftar Checklist</p>
                <ul class="checklist">
                    @foreach ($process->checklists as $item)
                        <li class="checklist-item {{ $item->is_done ? 'is-done' : 'is-pending' }}">
                            <form method="POST" action="{{ route('projects.processes.checklists.update', [$project, $process, $item]) }}" class="checklist-form">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="label" value="{{ $item->label }}">
                                <input type="hidden" name="sort_order" value="{{ $item->sort_order }}">
                                <label class="checkbox-inline checkbox-inline-grow">
                                    <input type="checkbox" name="is_done" value="1" @checked($item->is_done) onchange="this.form.submit()">
                                    <span>{{ $item->label }}</span>
                                </label>
                            </form>
                            <form method="POST" action="{{ route('projects.processes.checklists.destroy', [$project, $process, $item]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                            </form>
                        </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('projects.processes.checklists.store', [$project, $process]) }}" class="form-inline">
                    @csrf
                    <input name="label" type="text" placeholder="Tambah checklist baru" required>
                    <input name="sort_order" type="number" min="0" value="{{ $process->checklists->count() + 1 }}" required>
                    <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit">Tambah</button>
                </form>
            </article>

            <article class="process-card">
                <div class="process-card-head">
                    <div>
                        <p class="eyebrow">Komentar Tim</p>
                        <h2>Diskusi Proses</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('projects.processes.comments.store', [$project, $process]) }}" class="form-stack">
                    @csrf
                    <div class="form-field">
                        <label for="comment">Komentar</label>
                        <textarea id="comment" name="comment" rows="4" placeholder="Tulis update, catatan kendala, atau arahan proses..." required></textarea>
                    </div>
                    <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit">Simpan Komentar</button>
                </form>

                <div class="activity-feed">
                    @forelse ($process->comments as $comment)
                        <div class="activity-card">
                            <div class="activity-card-head">
                                <div>
                                    <strong>{{ $comment->user?->name ?? 'System' }}</strong>
                                    <span>{{ $comment->created_at->format('d M Y H:i') }}</span>
                                </div>
                                @if (auth()->user()->isAdmin() || $comment->user_id === auth()->id())
                                    <form method="POST" action="{{ route('projects.processes.comments.destroy', [$project, $process, $comment]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                                    </form>
                                @endif
                            </div>
                            <p>{{ $comment->comment }}</p>
                        </div>
                    @empty
                        <div class="empty-state">
                            Belum ada komentar untuk proses ini.
                        </div>
                    @endforelse
                </div>
            </article>

            <article class="process-card">
                <div class="process-card-head">
                    <div>
                        <p class="eyebrow">Riwayat Aktivitas</p>
                        <h2>Timeline Progress</h2>
                    </div>
                </div>

                <div class="activity-timeline">
                    @forelse ($process->histories as $history)
                        <div class="timeline-item">
                            <div class="timeline-marker"></div>
                            <div class="timeline-content">
                                <div class="activity-card-head">
                                    <div>
                                        <strong>{{ $history->user?->name ?? 'System' }}</strong>
                                        <span>{{ $history->created_at->format('d M Y H:i') }}</span>
                                    </div>
                                    <span class="timeline-tag">{{ str_replace('_', ' ', $history->event_type) }}</span>
                                </div>
                                <p>{{ $history->description }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            Belum ada histori aktivitas pada proses ini.
                        </div>
                    @endforelse
                </div>
            </article>
        </section>
    </main>
@endsection
