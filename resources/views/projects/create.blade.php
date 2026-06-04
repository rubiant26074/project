@extends('layouts.app')

@section('content')
    <main class="page page-narrow">
        <div class="page-actions">
            <a class="back-link" href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
        </div>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">CRUD Project</p>
                    <h1>Buat Project Baru</h1>
                </div>
            </div>

            <form method="POST" action="{{ route('projects.store') }}">
                @include('projects._form', [
                    'submitLabel' => 'Simpan Project',
                    'cancelUrl' => route('dashboard'),
                ])
            </form>
        </section>
    </main>
@endsection
