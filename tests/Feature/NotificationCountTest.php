<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\ProjectProcess;
use App\Models\ProjectProcessComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCountTest extends TestCase
{
    use RefreshDatabase;

    public function test_unread_notification_count_only_counts_comments_newer_than_last_seen(): void
    {
        $user = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'last_notification_seen_at' => now()->subDay(),
        ]);

        $project = Project::create([
            'wo_number' => 'WO-TEST-1',
            'client_name' => 'Client Test',
            'project_name' => 'Project Test',
            'status' => 'open',
            'progress' => 10,
        ]);

        $process = ProjectProcess::create([
            'project_id' => $project->id,
            'code' => 'PROC-1',
            'name' => 'Testing Process',
            'status' => 'open',
            'progress' => 10,
            'completed_checklists' => 0,
            'total_checklists' => 0,
            'sort_order' => 1,
        ]);

        ProjectProcessComment::create([
            'project_process_id' => $process->id,
            'user_id' => $user->id,
            'comment' => 'Old comment',
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        ProjectProcessComment::create([
            'project_process_id' => $process->id,
            'user_id' => $user->id,
            'comment' => 'New comment',
        ]);

        $this->actingAs($user);

        $this->assertSame(1, $user->fresh()->unreadNotificationCount());
    }
}
