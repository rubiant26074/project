<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectProcess;
use App\Models\ProjectProcessChecklist;
use App\Support\ProjectProcessActivityService;
use App\Support\ProjectProgressService;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class ProjectProcessChecklistController extends Controller
{
    private function ensureChecklistBelongsToProcess(Project $project, ProjectProcess $process, ProjectProcessChecklist $checklist): void
    {
        abort_unless(
            (int) $process->project_id === (int) $project->getKey()
            && (int) $checklist->project_process_id === (int) $process->getKey(),
            404,
        );
    }

    private function configureChecklistValidation(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $documentLink = trim((string) $validator->getData()['document_link'] ?? '');

            if ($documentLink === '') {
                return;
            }

            if (
                filter_var($documentLink, FILTER_VALIDATE_URL)
                || preg_match('/^(file:\/\/|\\\\\\\\|[a-zA-Z]:[\\\\\\/]).+$/', $documentLink)
            ) {
                return;
            }

            $validator->errors()->add('document_link', 'Link dokumen bisa berupa URL, file://, path server lokal \\\\server\\folder, atau path drive lokal.');
        });
    }

    private function isRemoteDocumentUrl(string $documentLink): bool
    {
        return (bool) preg_match('/^https?:\/\//i', $documentLink);
    }

    private function normalizeDocumentPath(string $documentLink): string
    {
        $documentLink = trim($documentLink);

        if (preg_match('/^file:\/\//i', $documentLink)) {
            $parsedUrl = parse_url($documentLink);
            $path = rawurldecode((string) ($parsedUrl['path'] ?? ''));
            $host = (string) ($parsedUrl['host'] ?? '');

            if ($host !== '' && $host !== 'localhost') {
                if (DIRECTORY_SEPARATOR === '\\') {
                    return '\\\\' . $host . '\\' . ltrim(str_replace('/', '\\', $path), '\\');
                }

                return '//' . $host . '/' . ltrim($path, '/');
            }

            if (DIRECTORY_SEPARATOR === '\\') {
                return ltrim(str_replace('/', '\\', $path), '\\');
            }

            return $path;
        }

        if (preg_match('/^\\\\\\\\/', $documentLink)) {
            return DIRECTORY_SEPARATOR === '\\'
                ? $documentLink
                : str_replace('\\', '/', $documentLink);
        }

        if (preg_match('/^[a-zA-Z]:[\\\\\\/]/', $documentLink)) {
            return DIRECTORY_SEPARATOR === '\\'
                ? str_replace('/', '\\', $documentLink)
                : $documentLink;
        }

        return $documentLink;
    }

    public function store(Request $request, Project $project, ProjectProcess $process, ProjectProgressService $progressService, ProjectProcessActivityService $activityService): RedirectResponse
    {
        abort_unless((int) $process->project_id === (int) $project->getKey(), 404);
        abort_unless($request->user()?->canUpdateProcess($process), 403);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $checklist = $process->checklists()->create([
            ...$validated,
            'document_link' => null,
            'is_done' => false,
        ]);

        $activityService->log($process, $request->user(), 'checklist_created', 'Checklist proses baru ditambahkan.', [
            'checklist_id' => $checklist->id,
            'label' => $checklist->label,
        ]);

        $progressService->syncFromChecklist($process->fresh('checklists'), $request->user());

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Checklist proses berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project, ProjectProcess $process, ProjectProcessChecklist $checklist, ProjectProgressService $progressService): RedirectResponse
    {
        $this->ensureChecklistBelongsToProcess($project, $process, $checklist);
        abort_unless($request->user()?->canUpdateProcess($process), 403);

        $validator = validator($request->all(), [
            'label' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_done' => ['nullable', 'boolean'],
            'document_link' => ['nullable', 'string', 'max:2048'],
            'target_start' => ['nullable', 'date'],
            'target_finish' => ['nullable', 'date', 'after_or_equal:target_start'],
        ]);
        $this->configureChecklistValidation($validator);
        $validated = $validator->validate();

        $isDone = (bool) ($validated['is_done'] ?? false);

        if ($isDone && blank($validated['document_link'] ?? null)) {
            return redirect()
                ->route('projects.processes.show', [$project, $process])
                ->withErrors(['document_link' => 'Link dokumen wajib diisi saat checklist ditandai selesai.']);
        }

        $previousDone = $checklist->is_done;
        $previousDocumentLink = $checklist->document_link;
        $previousTargetStart = $checklist->target_start?->toDateString();
        $previousTargetFinish = $checklist->target_finish?->toDateString();

        $checklist->update([
            'label' => $validated['label'],
            'document_link' => $validated['document_link'] ?? null,
            'target_start' => $validated['target_start'] ?? null,
            'target_finish' => $validated['target_finish'] ?? null,
            'sort_order' => $validated['sort_order'],
            'is_done' => $isDone,
        ]);

        if ($previousDone !== $checklist->is_done) {
            app(ProjectProcessActivityService::class)->log(
                $process,
                $request->user(),
                'checklist_toggled',
                $checklist->is_done
                    ? sprintf('Checklist "%s" ditandai selesai.', $checklist->label)
                    : sprintf('Checklist "%s" dibuka kembali.', $checklist->label),
                [
                    'checklist_id' => $checklist->id,
                    'is_done' => $checklist->is_done,
                ],
            );
        }

        if (
            $previousTargetStart !== $checklist->target_start?->toDateString()
            || $previousTargetFinish !== $checklist->target_finish?->toDateString()
        ) {
            app(ProjectProcessActivityService::class)->log(
                $process,
                $request->user(),
                'checklist_target_updated',
                sprintf('Target tanggal checklist "%s" diperbarui.', $checklist->label),
                [
                    'checklist_id' => $checklist->id,
                    'target_start' => $checklist->target_start?->toDateString(),
                    'target_finish' => $checklist->target_finish?->toDateString(),
                ],
            );
        }

        if ($previousDocumentLink !== $checklist->document_link && filled($checklist->document_link)) {
            app(ProjectProcessActivityService::class)->log(
                $process,
                $request->user(),
                'checklist_document_linked',
                sprintf('Link dokumen untuk checklist "%s" diperbarui.', $checklist->label),
                [
                    'checklist_id' => $checklist->id,
                    'document_link' => $checklist->document_link,
                ],
            );
        }

        $progressService->syncFromChecklist($process->fresh('checklists', 'project.processes'), $request->user());

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Checklist proses berhasil diperbarui.');
    }

    private function canOpenDocumentPath(string $documentPath): bool
    {
        if (is_file($documentPath) && is_readable($documentPath)) {
            return true;
        }

        if ($documentPath === '') {
            return false;
        }

        $handle = @fopen($documentPath, 'rb');

        if (is_resource($handle)) {
            fclose($handle);

            return true;
        }

        return false;
    }

    public function open(Request $request, Project $project, ProjectProcess $process, ProjectProcessChecklist $checklist): Response|RedirectResponse
    {
        $this->ensureChecklistBelongsToProcess($project, $process, $checklist);

        $documentLink = trim((string) $checklist->document_link);

        if ($documentLink === '') {
            return redirect()
                ->route('projects.processes.show', [$project, $process])
                ->withErrors(['document_link' => 'Link dokumen belum diisi untuk checklist ini.']);
        }

        if ($this->isRemoteDocumentUrl($documentLink)) {
            return redirect()->away($documentLink);
        }

        $documentPath = $this->normalizeDocumentPath($documentLink);

        if (! $this->canOpenDocumentPath($documentPath)) {
            return redirect()
                ->route('projects.processes.show', [$project, $process])
                ->withErrors([
                    'document_link' => 'Dokumen tidak bisa dibuka dari server aplikasi. Pastikan path benar dan server aplikasi memiliki akses ke folder/share tersebut.',
                ]);
        }

        $headers = [];
        $mimeType = @mime_content_type($documentPath);

        if (is_string($mimeType) && $mimeType !== '') {
            $headers['Content-Type'] = $mimeType;
        }

        return response()->file($documentPath, $headers);
    }

    public function destroy(Project $project, ProjectProcess $process, ProjectProcessChecklist $checklist, ProjectProgressService $progressService, ProjectProcessActivityService $activityService): RedirectResponse
    {
        $this->ensureChecklistBelongsToProcess($project, $process, $checklist);
        abort_unless(request()->user()?->canUpdateProcess($process), 403);
        $activityService->log($process, request()->user(), 'checklist_deleted', 'Checklist proses dihapus.', [
            'checklist_id' => $checklist->id,
            'label' => $checklist->label,
        ]);
        $checklist->delete();

        $progressService->syncFromChecklist($process->fresh('checklists', 'project.processes'), request()->user());

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Checklist proses berhasil dihapus.');
    }
}
