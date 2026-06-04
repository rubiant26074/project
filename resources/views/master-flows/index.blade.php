@extends('layouts.app')

@section('content')
    <main class="page">
        <div class="page-actions">
            <a class="back-link" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
        </div>

        <section class="two-column-layout">
            <article class="panel-card">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Master Flow</p>
                        <h1>Daftar Master Flow</h1>
                    </div>
                </div>

                <div class="list-stack">
                    @foreach ($flows as $flow)
                        <div class="list-card">
                            <div>
                                <strong>{{ $flow->name }}</strong>
                                <p>{{ $flow->description ?: 'Belum ada deskripsi.' }}</p>
                                <div class="inline-meta">
                                    <span>{{ $flow->steps_count }} step</span>
                                    <span>{{ $flow->projects_count }} project</span>
                                    <span>{{ $flow->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </div>
                            </div>
                            <div class="toolbar-group">
                                <a class="toolbar-button" href="{{ route('master-flows.edit', $flow) }}">Atur Flow</a>
                                <form method="POST" action="{{ route('master-flows.destroy', $flow) }}" onsubmit="return confirm('Hapus master flow ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="toolbar-button toolbar-button-danger" type="submit">Hapus</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="panel-card">
                <div class="section-head">
                    <div>
                        <p class="eyebrow">Master Flow</p>
                        <h2>Buat Master Flow Baru</h2>
                    </div>
                </div>

                <form method="POST" action="{{ route('master-flows.store') }}" class="form-stack">
                    @csrf
                    <div class="form-field">
                        <label for="flow-name">Nama Flow</label>
                        <input id="flow-name" name="name" type="text" required>
                    </div>
                    <div class="form-field">
                        <label for="flow-description">Deskripsi</label>
                        <textarea id="flow-description" name="description" rows="4"></textarea>
                    </div>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="is_active" value="1" checked>
                        <span>Flow aktif dan bisa dipilih saat buat project</span>
                    </label>
                    <button class="toolbar-button toolbar-button-primary" type="submit">Simpan Master Flow</button>
                </form>
            </article>
        </section>
    </main>
@endsection
