@extends('layouts.app')

@section('content')
    <main class="page page-narrow">
        <div class="page-actions">
            <a class="back-link" href="{{ route('projects.show', $project) }}">Kembali ke Detail Project</a>
        </div>

        <section class="panel-card">
            <div class="section-head">
                <div>
                    <p class="eyebrow">CRUD Project</p>
                    <h1>Edit Project</h1>
                </div>
            </div>

            <form method="POST" action="{{ route('projects.update', $project) }}">
                @method('PUT')
                @include('projects._form', [
                    'submitLabel' => 'Update Project',
                    'cancelUrl' => route('projects.show', $project),
                ])
            </form>
        </section>
    </main>
@endsection
