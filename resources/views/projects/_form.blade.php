@csrf

<div class="form-stack">
    <div class="form-grid">
        <div class="form-field">
            <label for="wo_number">Nomor WO</label>
            <input id="wo_number" name="wo_number" type="text" value="{{ old('wo_number', $project->wo_number ?? '') }}" required>
        </div>
        <div class="form-field">
            <label for="client_name">Nama Client</label>
            <input id="client_name" name="client_name" type="text" value="{{ old('client_name', $project->client_name ?? '') }}" required>
        </div>
    </div>

    <div class="form-grid">
        <div class="form-field">
            <label for="project_name">Nama Project</label>
            <input id="project_name" name="project_name" type="text" value="{{ old('project_name', $project->project_name ?? '') }}" required>
        </div>

        @if (! isset($project))
            <div class="form-field">
                <label for="master_flow_id">Master Flow</label>
                <select id="master_flow_id" name="master_flow_id" required>
                    <option value="">Pilih flow</option>
                    @foreach ($masterFlows as $masterFlow)
                        <option value="{{ $masterFlow->id }}" @selected(old('master_flow_id') == $masterFlow->id)>{{ $masterFlow->name }}</option>
                    @endforeach
                </select>
            </div>
        @else
            <div class="form-field">
                <label>Master Flow</label>
                <div class="info-box">{{ $project->masterFlow?->name ?? 'Tidak ada master flow' }}</div>
            </div>
        @endif
    </div>

    <div class="form-field">
        <label for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="4">{{ old('description', $project->description ?? '') }}</textarea>
    </div>

    <div class="toolbar-group">
        <button class="toolbar-button toolbar-button-primary" type="submit">{{ $submitLabel }}</button>
        <a class="toolbar-button" href="{{ $cancelUrl }}">Batal</a>
    </div>
</div>
