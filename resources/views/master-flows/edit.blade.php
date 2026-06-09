@extends('layouts.app')

@section('content')
    @php
        $layoutSteps = $flow->steps->map(function ($step) {
            return [
                'id' => $step->id,
                'name' => $step->name,
                'status' => 'open',
                'position_x' => $step->position_x,
                'position_y' => $step->position_y,
            ];
        })->values();

        $layoutConnections = $flow->connections->map(function ($connection) {
            return [
                'id' => $connection->id,
                'from_id' => $connection->from_step_id,
                'to_id' => $connection->to_step_id,
                'start_x' => $connection->start_x,
                'start_y' => $connection->start_y,
                'bend_x' => $connection->bend_x,
                'bend_y' => $connection->bend_y,
                'mid2_x' => $connection->mid2_x,
                'mid2_y' => $connection->mid2_y,
                'end_x' => $connection->end_x,
                'end_y' => $connection->end_y,
            ];
        })->values();
    @endphp

    <main class="page" data-preserve-scroll-page>
        <div class="page-actions">
            <a class="back-link" href="{{ route('master-flows.index') }}">Kembali ke Master Flow</a>
        </div>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Master Flow</p>
                    <h1>{{ $flow->name }}</h1>
                </div>
            </div>

            <form method="POST" action="{{ route('master-flows.update', $flow) }}" class="form-stack">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-field">
                        <label for="name">Nama Flow</label>
                        <input id="name" name="name" type="text" value="{{ old('name', $flow->name) }}" required>
                    </div>
                    <div class="form-field">
                        <label>Status Flow</label>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $flow->is_active))>
                            <span>Bisa dipakai saat buat project baru</span>
                        </label>
                    </div>
                </div>
                <div class="form-field">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description" rows="3">{{ old('description', $flow->description) }}</textarea>
                </div>
                <button class="toolbar-button toolbar-button-primary" type="submit">Update Master Flow</button>
            </form>
        </section>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Visual Editor</p>
                    <h2>Susun Posisi Step Master Flow</h2>
                    <p class="hero-copy">Geser kotak step untuk merapikan layout. Tarik 4 titik garis: pangkal, titik 2, titik 3, dan ujung. Setelah sesuai, klik simpan layout.</p>
                </div>
                <div class="toolbar-group">
                    <button class="toolbar-button" type="button" data-layout-reset>Reset Posisi</button>
                    <button class="toolbar-button toolbar-button-primary" type="button" data-layout-save>Simpan Layout</button>
                </div>
            </div>

            <div
                class="flow-wrapper flow-wrapper-editor"
                data-preserve-scroll-container="master-flow-canvas"
                data-flow-layout-editor
                data-save-url="{{ route('master-flows.layout.update', $flow) }}"
                data-steps='@json($layoutSteps)'
                data-connections='@json($layoutConnections)'
            >
                <div class="flow-chart flow-chart-editor" data-layout-stage>
                    <svg class="flow-lines" viewBox="0 0 1200 760" preserveAspectRatio="none" data-layout-lines>
                        <defs>
                            <marker id="editor-arrow" viewBox="0 0 12 12" markerWidth="8" markerHeight="8" refX="13.2" refY="6" orient="auto" markerUnits="userSpaceOnUse">
                                <path d="M 0 0 L 12 6 L 0 12 z" fill="#445463"></path>
                            </marker>
                        </defs>
                    </svg>
                </div>
            </div>
        </section>

        <section class="two-column-layout two-column-layout-wide">
            <article class="panel-card">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Step Flow</p>
                        <h2>Tambah Step Baru</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('master-flows.steps.store', $flow) }}" class="form-stack">
                    @csrf
                    <div class="form-grid form-grid-3">
                        <div class="form-field">
                            <label>Kode</label>
                            <input name="code" type="text" placeholder="engineering" required>
                        </div>
                        <div class="form-field">
                            <label>Nama Step</label>
                            <input name="name" type="text" placeholder="Engineering" required>
                        </div>
                        <div class="form-field">
                            <label>Urutan</label>
                            <input name="sort_order" type="number" min="0" value="{{ $flow->steps->count() + 1 }}" required>
                        </div>
                    </div>
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Posisi X</label>
                            <input name="position_x" type="number" min="0" max="100" step="0.1" value="12" required>
                        </div>
                        <div class="form-field">
                            <label>Posisi Y</label>
                            <input name="position_y" type="number" min="0" max="100" step="0.1" value="12" required>
                        </div>
                    </div>
                    <div class="form-field">
                        <label>Role Yang Boleh Update Proses Ini</label>
                        <div class="role-permission-list">
                            @foreach ($roles as $role)
                                <label class="role-permission-inline">
                                    <input type="checkbox" name="allowed_role_codes[]" value="{{ $role->code }}">
                                    <span class="role-permission-inline-copy">
                                        <strong>{{ $role->name }}</strong>
                                        <small>{{ strtoupper($role->code) }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        <div class="info-box">Jika tidak dipilih, semua role yang memang punya hak update proses tetap bisa update.</div>
                    </div>
                    <button class="toolbar-button toolbar-button-primary" type="submit">Tambah Step</button>
                </form>

                <div class="section-head section-head-tight">
                    <div>
                        <p class="eyebrow">Garis Proses</p>
                        <h2>Atur Koneksi Antar Step</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('master-flows.connections.store', $flow) }}" class="form-stack">
                    @csrf
                    <div class="form-grid">
                        <div class="form-field">
                            <label>Dari Step</label>
                            <select name="from_step_id" required>
                                <option value="">Pilih</option>
                                @foreach ($flow->steps as $step)
                                    <option value="{{ $step->id }}">{{ $step->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-field">
                            <label>Ke Step</label>
                            <select name="to_step_id" required>
                                <option value="">Pilih</option>
                                @foreach ($flow->steps as $step)
                                    <option value="{{ $step->id }}">{{ $step->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button class="toolbar-button" type="submit">Tambah Garis</button>
                </form>

                <div class="connection-list">
                    @foreach ($flow->connections as $connection)
                        <div class="connection-row">
                            <div class="connection-row-name">
                                <strong>{{ $connection->fromStep->name }} → {{ $connection->toStep->name }}</strong>
                            </div>
                            <form method="POST" action="{{ route('master-flows.connections.destroy', [$flow, $connection]) }}" class="connection-row-action">
                                @csrf
                                @method('DELETE')
                                <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus Garis</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="panel-card">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Step Flow</p>
                        <h2>Daftar Step dan Template Checklist</h2>
                    </div>
                </div>

                <div class="list-stack">
                    @foreach ($flow->steps as $step)
                        <div class="step-card">
                            <form method="POST" action="{{ route('master-flows.steps.update', [$flow, $step]) }}" class="form-stack">
                                @csrf
                                @method('PUT')
                                <div class="form-grid form-grid-3">
                                    <div class="form-field">
                                        <label>Kode</label>
                                        <input name="code" type="text" value="{{ $step->code }}" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Nama Step</label>
                                        <input name="name" type="text" value="{{ $step->name }}" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Urutan</label>
                                        <input name="sort_order" type="number" min="0" value="{{ $step->sort_order }}" required>
                                    </div>
                                </div>
                                <div class="form-grid">
                                    <div class="form-field">
                                        <label>X</label>
                                        <input name="position_x" type="number" min="0" max="100" step="0.1" value="{{ $step->position_x }}" required>
                                    </div>
                                    <div class="form-field">
                                        <label>Y</label>
                                        <input name="position_y" type="number" min="0" max="100" step="0.1" value="{{ $step->position_y }}" required>
                                    </div>
                                </div>
                                <div class="form-field">
                                    <label>Role Yang Boleh Update Proses Ini</label>
                                    <div class="role-permission-list">
                                        @foreach ($roles as $role)
                                            <label class="role-permission-inline">
                                                <input type="checkbox" name="allowed_role_codes[]" value="{{ $role->code }}" @checked(in_array($role->code, $step->allowed_role_codes ?? [], true))>
                                                <span class="role-permission-inline-copy">
                                                    <strong>{{ $role->name }}</strong>
                                                    <small>{{ strtoupper($role->code) }}</small>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="info-box">Jika kosong, proses ini bisa diupdate semua role yang memang memiliki izin update proses.</div>
                                </div>
                                <button class="toolbar-button" type="submit">Update Step</button>
                            </form>
                            <form method="POST" action="{{ route('master-flows.steps.destroy', [$flow, $step]) }}" onsubmit="return confirm('Hapus step ini?')" class="toolbar-group toolbar-group-top">
                                @csrf
                                @method('DELETE')
                                <button class="toolbar-button toolbar-button-danger" type="submit">Hapus Step</button>
                            </form>

                            <div class="subsection">
                                <h3>Template Checklist</h3>
                                <div class="list-stack list-stack-compact">
                                    @foreach ($step->checklistTemplates as $template)
                                        <form method="POST" action="{{ route('master-flows.steps.checklists.update', [$flow, $step, $template]) }}" class="list-card list-card-form">
                                            @csrf
                                            @method('PUT')
                                            <input name="label" type="text" value="{{ $template->label }}" required>
                                            <input name="sort_order" type="number" min="0" value="{{ $template->sort_order }}" required>
                                            <button class="toolbar-button toolbar-button-small" type="submit">Update</button>
                                        </form>
                                        <form method="POST" action="{{ route('master-flows.steps.checklists.destroy', [$flow, $step, $template]) }}" class="toolbar-group toolbar-group-top">
                                            @csrf
                                            @method('DELETE')
                                            <button class="toolbar-button toolbar-button-danger toolbar-button-small" type="submit">Hapus</button>
                                        </form>
                                    @endforeach
                                </div>

                                <form method="POST" action="{{ route('master-flows.steps.checklists.store', [$flow, $step]) }}" class="form-inline">
                                    @csrf
                                    <input name="label" type="text" placeholder="Tambah checklist baru" required>
                                    <input name="sort_order" type="number" min="0" value="{{ $step->checklistTemplates->count() + 1 }}" required>
                                    <button class="toolbar-button toolbar-button-primary toolbar-button-small" type="submit">Tambah</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>
    </main>
@endsection
