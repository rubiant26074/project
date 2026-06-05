<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectProcess;
use App\Models\ProjectProcessComment;
use App\Support\ProjectProcessActivityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectProcessCommentController extends Controller
{
    public function store(Request $request, Project $project, ProjectProcess $process, ProjectProcessActivityService $activityService): RedirectResponse
    {
        abort_unless((int) $process->project_id === (int) $project->getKey(), 404);

        $validated = $request->validate([
            'comment' => ['required', 'string', 'max:4000'],
        ]);

        $comment = $process->comments()->create([
            'user_id' => $request->user()->id,
            'comment' => $validated['comment'],
        ]);

        $activityService->log($process, $request->user(), 'comment_added', 'Komentar baru ditambahkan pada proses.', [
            'comment_id' => $comment->id,
        ]);

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Komentar proses berhasil ditambahkan.');
    }

    public function destroy(Request $request, Project $project, ProjectProcess $process, ProjectProcessComment $comment, ProjectProcessActivityService $activityService): RedirectResponse
    {
        abort_unless(
            (int) $process->project_id === (int) $project->getKey()
            && (int) $comment->project_process_id === (int) $process->getKey(),
            404,
        );
        abort_unless($request->user()?->canDeleteProcessComment($comment->user_id), 403);

        $activityService->log($process, $request->user(), 'comment_deleted', 'Komentar proses dihapus.', [
            'comment_id' => $comment->id,
        ]);

        $comment->delete();

        return redirect()
            ->route('projects.processes.show', [$project, $process])
            ->with('status', 'Komentar proses berhasil dihapus.');
    }
}
