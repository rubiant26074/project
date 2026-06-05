@extends('layouts.app')

@section('content')
    <main class="page page-narrow">
        <div class="page-actions">
            <a class="back-link" href="{{ route('users.index') }}">Kembali ke User Management</a>
        </div>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">Admin Panel</p>
                    <h1>Tambah User Baru</h1>
                </div>
            </div>

            <form method="POST" action="{{ route('users.store') }}">
                @include('users._form', [
                    'userModel' => null,
                    'submitLabel' => 'Simpan User',
                    'cancelUrl' => route('users.index'),
                ])
            </form>
        </section>
    </main>
@endsection
