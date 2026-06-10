@extends('layouts.app')

@section('content')
    <main class="page" data-preserve-scroll-page>
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

        @php
            $allowedRoles = $process->allowed_role_codes ?? [];
            $canUpdateThisProcess = auth()->user()->canUpdateProcess($process);
            $canUpdateProcessTargets = auth()->user()->canUpdateProcessTargets();
        @endphp

        <section class="panel-card process-target-panel">
            <div class="section-head section-head-tight">
                <div>
                    <p class="eyebrow">Target Proses</p>
                    <h2>Target Mulai dan Target Selesai</h2>
                </div>
                <span class="inline-meta">Diisi oleh PM</span>
            </div>
            @if ($canUpdateProcessTargets)
                <form method="POST" action="{{ route('projects.processes.target.update', [$project, $process]) }}" class="process-target-form">
                    @csrf
                    @method('PUT')
                    <label>
                        <span>Target Mulai</span>
                        <input type="date" name="target_start" value="{{ $process->target_start?->format('Y-m-d') }}">
                    </label>
                    <label>
                        <span>Target Selesai</span>
                        <input type="date" name="target_finish" value="{{ $process->target_finish?->format('Y-m-d') }}">
                    </label>
                    <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit">Simpan Target Proses</button>
                </form>
            @else
                <div class="process-target-readonly">
                    <span>Target mulai: <strong>{{ $process->target_start?->format('d M Y') ?? '-' }}</strong></span>
                    <span>Target selesai: <strong>{{ $process->target_finish?->format('d M Y') ?? '-' }}</strong></span>
                </div>
            @endif
        </section>

        @if (! $canUpdateThisProcess)
            <div class="flash-message flash-message-info">
                Proses ini hanya bisa diupdate oleh role:
                <strong>{{ empty($allowedRoles) ? 'role updater standar' : strtoupper(implode(', ', $allowedRoles)) }}</strong>.
                Akun Anda saat ini hanya memiliki akses lihat.
            </div>
        @endif

        <section class="process-layout process-layout-checklist">
            <div class="process-side-column">
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
            </div>

            <article class="process-card process-card-checklist">
                <p class="eyebrow">Daftar Checklist</p>
                @php
                    $resolveDocumentHref = function (?string $link): ?string {
                        if (blank($link)) {
                            return null;
                        }

                        $link = trim($link);

                        if (preg_match('/^https?:\/\//i', $link) || preg_match('/^file:\/\//i', $link)) {
                            return $link;
                        }

                        if (preg_match('/^\\\\\\\\/', $link)) {
                            return 'file:' . str_replace('\\', '/', $link);
                        }

                        if (preg_match('/^[a-zA-Z]:[\\\\\\/]/', $link)) {
                            return 'file:///' . str_replace(['\\', ' '], ['/', '%20'], $link);
                        }

                        return $link;
                    };
                @endphp

                @if ($canUpdateThisProcess)
                    <div class="checklist-sheet-toolbar">
                        <span>Paste dari Excel didukung untuk kolom Link Dokumen, Target Mulai, dan Target Selesai.</span>
                    </div>
                    <form id="checklist-bulk-update" method="POST" action="{{ route('projects.processes.checklists.bulk-update', [$project, $process]) }}">
                        @csrf
                        @method('PUT')
                    </form>
                @endif

                <div class="table-shell checklist-table-shell">
                    <table class="admin-table checklist-table">
                        <colgroup>
                            <col style="width: 54px;">
                            <col style="width: 28%;">
                            <col style="width: 30%;">
                            <col style="width: 15%;">
                            <col style="width: 15%;">
                            <col style="width: 108px;">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Checklist</th>
                                <th>Link Dokumen</th>
                                <th>Target Mulai</th>
                                <th>Target Selesai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($process->checklists as $item)
                                @php
                                    $documentHref = $resolveDocumentHref($item->document_link);
                                    $deleteFormId = 'checklist-delete-' . $item->id;
                                @endphp
                                <tr class="{{ $item->is_done ? 'checklist-row-done' : 'checklist-row-pending' }}">
                                    @if ($canUpdateThisProcess)
                                        <td class="checklist-status-cell">
                                            <input type="hidden" name="checklists[{{ $item->id }}][is_done]" value="0" form="checklist-bulk-update">
                                            <input type="checkbox" name="checklists[{{ $item->id }}][is_done]" value="1" form="checklist-bulk-update" @checked($item->is_done)>
                                        </td>
                                        <td class="checklist-label-cell">
                                            <strong>{{ $item->label }}</strong>
                                        </td>
                                        <td>
                                            <div class="checklist-link-cell">
                                                <input
                                                    name="checklists[{{ $item->id }}][document_link]"
                                                    type="text"
                                                    form="checklist-bulk-update"
                                                    value="{{ $item->document_link }}"
                                                    placeholder="Paste link"
                                                    data-checklist-field="document_link"
                                                >
                                                @if ($item->document_link && $documentHref)
                                                    <a class="table-icon-button table-icon-button-link checklist-cell-action" href="{{ $documentHref }}" target="_blank" rel="noopener noreferrer" title="Buka link dokumen" aria-label="Buka link dokumen">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M14 5h5v5"></path>
                                                            <path d="M10 14 19 5"></path>
                                                            <path d="M19 14v4a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h4"></path>
                                                        </svg>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <input name="checklists[{{ $item->id }}][target_start]" type="date" form="checklist-bulk-update" value="{{ $item->target_start?->format('Y-m-d') }}" data-checklist-field="target_start">
                                        </td>
                                        <td>
                                            <input name="checklists[{{ $item->id }}][target_finish]" type="date" form="checklist-bulk-update" value="{{ $item->target_finish?->format('Y-m-d') }}" data-checklist-field="target_finish">
                                        </td>
                                        <td>
                                            <div class="table-action-group">
                                                <button class="table-icon-button table-icon-button-danger checklist-cell-action" type="submit" form="{{ $deleteFormId }}" title="Hapus checklist" aria-label="Hapus checklist">
                                                    <svg viewBox="0 0 24 24" aria-hidden="true">
                                                        <path d="M3 6h18"></path>
                                                        <path d="M8 6V4h8v2"></path>
                                                        <path d="M19 6l-1 14H6L5 6"></path>
                                                        <path d="M10 10v6"></path>
                                                        <path d="M14 10v6"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    @else
                                        <td class="checklist-status-cell">
                                            <input type="checkbox" @checked($item->is_done) disabled>
                                        </td>
                                        <td class="checklist-label-cell"><strong>{{ $item->label }}</strong></td>
                                        <td>
                                                @if ($item->document_link && $documentHref)
                                                    <a class="table-icon-button table-icon-button-link" href="{{ $documentHref }}" target="_blank" rel="noopener noreferrer" title="Buka link dokumen" aria-label="Buka link dokumen">
                                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M14 5h5v5"></path>
                                                            <path d="M10 14 19 5"></path>
                                                            <path d="M19 14v4a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h4"></path>
                                                        </svg>
                                                    </a>
                                                @else
                                                    <span class="inline-meta">-</span>
                                                @endif
                                            </td>
                                            <td>{{ $item->target_start?->format('d M Y') ?? '-' }}</td>
                                            <td>{{ $item->target_finish?->format('d M Y') ?? '-' }}</td>
                                            <td><span class="inline-meta">Lihat saja</span></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($canUpdateThisProcess)
                    @foreach ($process->checklists as $item)
                        <form id="checklist-delete-{{ $item->id }}" method="POST" action="{{ route('projects.processes.checklists.destroy', [$project, $process, $item]) }}">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach
                    <div class="checklist-save-row">
                        <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit" form="checklist-bulk-update">Simpan Perubahan</button>
                    </div>
                @endif

                @if ($canUpdateThisProcess)
                    <form method="POST" action="{{ route('projects.processes.checklists.store', [$project, $process]) }}" class="form-inline checklist-create-form">
                        @csrf
                        <input name="label" type="text" placeholder="Tambah checklist baru" required>
                        <input name="sort_order" type="number" min="0" value="{{ $process->checklists->count() + 1 }}" required>
                        <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit">Tambah</button>
                    </form>
                @endif
            </article>
        </section>

        <button
            class="comment-fab"
            type="button"
            data-comment-modal-open
            aria-label="Buka komentar tim"
            title="Buka komentar tim"
        >
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M21 15a2 2 0 0 1-2 2H8l-5 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                <path d="M8 9h8"></path>
                <path d="M8 13h5"></path>
            </svg>
        </button>

        <div class="comment-modal" data-comment-modal hidden>
            <div class="comment-modal-backdrop" data-comment-modal-close></div>
            <div class="comment-modal-panel" role="dialog" aria-modal="true" aria-labelledby="comment-modal-title">
                <div class="process-card-head">
                    <div>
                        <p class="eyebrow">Komentar Tim</p>
                        <h2 id="comment-modal-title">Diskusi Proses</h2>
                    </div>
                    <button class="comment-modal-close" type="button" data-comment-modal-close aria-label="Tutup komentar">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M6 6l12 12"></path>
                            <path d="M18 6 6 18"></path>
                        </svg>
                    </button>
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
                                @if (auth()->user()->canDeleteProcessComment($comment->user_id))
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
            </div>
        </div>
    </main>
@endsection
