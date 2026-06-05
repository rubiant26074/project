<?php

namespace App\Http\Controllers;

use App\Models\MasterFlow;
use App\Models\MasterFlowConnection;
use App\Models\MasterFlowStep;
use App\Models\MasterFlowStepChecklist;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterFlowController extends Controller
{
    public function index(): View
    {
        return view('master-flows.index', [
            'flows' => MasterFlow::query()
                ->withCount('steps', 'projects')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $flow = MasterFlow::create([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master-flows.edit', $flow)
            ->with('status', 'Master flow baru berhasil dibuat.');
    }

    public function edit(MasterFlow $masterFlow): View
    {
        $masterFlow->load([
            'steps.checklistTemplates',
            'connections.fromStep',
            'connections.toStep',
            'projects',
        ]);

        return view('master-flows.edit', [
            'flow' => $masterFlow,
            'roles' => Role::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, MasterFlow $masterFlow): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $masterFlow->update([
            ...$validated,
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Master flow berhasil diperbarui.');
    }

    public function destroy(MasterFlow $masterFlow): RedirectResponse
    {
        $masterFlow->delete();

        return redirect()
            ->route('master-flows.index')
            ->with('status', 'Master flow berhasil dihapus.');
    }

    public function updateLayout(Request $request, MasterFlow $masterFlow): JsonResponse
    {
        $validated = $request->validate([
            'steps' => ['required', 'array'],
            'steps.*.id' => ['required', Rule::exists('master_flow_steps', 'id')->where(fn ($query) => $query->where('master_flow_id', $masterFlow->id))],
            'steps.*.position_x' => ['required', 'numeric', 'min:2', 'max:96'],
            'steps.*.position_y' => ['required', 'numeric', 'min:4', 'max:96'],
            'connections' => ['nullable', 'array'],
            'connections.*.id' => ['required', Rule::exists('master_flow_connections', 'id')->where(fn ($query) => $query->where('master_flow_id', $masterFlow->id))],
            'connections.*.start_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'connections.*.start_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'connections.*.bend_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'connections.*.bend_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'connections.*.mid2_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'connections.*.mid2_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'connections.*.end_x' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'connections.*.end_y' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        foreach ($validated['steps'] as $stepData) {
            $masterFlow->steps()
                ->whereKey($stepData['id'])
                ->update([
                    'position_x' => $stepData['position_x'],
                    'position_y' => $stepData['position_y'],
                ]);
        }

        foreach ($validated['connections'] ?? [] as $connectionData) {
            $masterFlow->connections()
                ->whereKey($connectionData['id'])
                ->update([
                    'start_x' => $connectionData['start_x'] ?? null,
                    'start_y' => $connectionData['start_y'] ?? null,
                    'bend_x' => $connectionData['bend_x'] ?? null,
                    'bend_y' => $connectionData['bend_y'] ?? null,
                    'mid2_x' => $connectionData['mid2_x'] ?? null,
                    'mid2_y' => $connectionData['mid2_y'] ?? null,
                    'end_x' => $connectionData['end_x'] ?? null,
                    'end_y' => $connectionData['end_y'] ?? null,
                ]);
        }

        return response()->json(['message' => 'Layout master flow berhasil disimpan.']);
    }

    public function storeStep(Request $request, MasterFlow $masterFlow): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'alpha_dash', Rule::unique('master_flow_steps', 'code')->where(fn ($query) => $query->where('master_flow_id', $masterFlow->id))],
            'name' => ['required', 'string', 'max:255'],
            'position_x' => ['required', 'numeric', 'min:2', 'max:96'],
            'position_y' => ['required', 'numeric', 'min:4', 'max:96'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'allowed_role_codes' => ['nullable', 'array'],
            'allowed_role_codes.*' => ['string', Rule::exists('roles', 'code')->where(fn ($query) => $query->where('is_active', true))],
        ]);

        $masterFlow->steps()->create([
            ...$validated,
            'allowed_role_codes' => array_values($validated['allowed_role_codes'] ?? []),
        ]);

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Step flow berhasil ditambahkan.');
    }

    public function updateStep(Request $request, MasterFlow $masterFlow, MasterFlowStep $step): RedirectResponse
    {
        abort_unless((int) $step->master_flow_id === (int) $masterFlow->getKey(), 404);

        $validated = $request->validate([
            'code' => ['required', 'alpha_dash', Rule::unique('master_flow_steps', 'code')->where(fn ($query) => $query->where('master_flow_id', $masterFlow->id))->ignore($step->id)],
            'name' => ['required', 'string', 'max:255'],
            'position_x' => ['required', 'numeric', 'min:2', 'max:96'],
            'position_y' => ['required', 'numeric', 'min:4', 'max:96'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'allowed_role_codes' => ['nullable', 'array'],
            'allowed_role_codes.*' => ['string', Rule::exists('roles', 'code')->where(fn ($query) => $query->where('is_active', true))],
        ]);

        $step->update([
            ...$validated,
            'allowed_role_codes' => array_values($validated['allowed_role_codes'] ?? []),
        ]);

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Step flow berhasil diperbarui.');
    }

    public function destroyStep(MasterFlow $masterFlow, MasterFlowStep $step): RedirectResponse
    {
        abort_unless((int) $step->master_flow_id === (int) $masterFlow->getKey(), 404);
        $step->delete();

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Step flow berhasil dihapus.');
    }

    public function storeConnection(Request $request, MasterFlow $masterFlow): RedirectResponse
    {
        $validated = $request->validate([
            'from_step_id' => ['required', Rule::exists('master_flow_steps', 'id')->where(fn ($query) => $query->where('master_flow_id', $masterFlow->id))],
            'to_step_id' => ['required', Rule::exists('master_flow_steps', 'id')->where(fn ($query) => $query->where('master_flow_id', $masterFlow->id)), 'different:from_step_id'],
        ]);

        MasterFlowConnection::firstOrCreate([
            'master_flow_id' => $masterFlow->id,
            'from_step_id' => $validated['from_step_id'],
            'to_step_id' => $validated['to_step_id'],
        ]);

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Garis flow berhasil ditambahkan.');
    }

    public function destroyConnection(MasterFlow $masterFlow, MasterFlowConnection $connection): RedirectResponse
    {
        abort_unless((int) $connection->master_flow_id === (int) $masterFlow->getKey(), 404);
        $connection->delete();

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Garis flow berhasil dihapus.');
    }

    public function storeChecklist(Request $request, MasterFlow $masterFlow, MasterFlowStep $step): RedirectResponse
    {
        abort_unless((int) $step->master_flow_id === (int) $masterFlow->getKey(), 404);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $step->checklistTemplates()->create($validated);

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Template checklist berhasil ditambahkan.');
    }

    public function updateChecklist(Request $request, MasterFlow $masterFlow, MasterFlowStep $step, MasterFlowStepChecklist $checklist): RedirectResponse
    {
        abort_unless(
            (int) $step->master_flow_id === (int) $masterFlow->getKey()
            && (int) $checklist->master_flow_step_id === (int) $step->getKey(),
            404,
        );

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $checklist->update($validated);

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Template checklist berhasil diperbarui.');
    }

    public function destroyChecklist(MasterFlow $masterFlow, MasterFlowStep $step, MasterFlowStepChecklist $checklist): RedirectResponse
    {
        abort_unless(
            (int) $step->master_flow_id === (int) $masterFlow->getKey()
            && (int) $checklist->master_flow_step_id === (int) $step->getKey(),
            404,
        );
        $checklist->delete();

        return redirect()
            ->route('master-flows.edit', $masterFlow)
            ->with('status', 'Template checklist berhasil dihapus.');
    }
}
