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

    public function test_mark_notifications_as_read_hides_unread_counts(): void
    {
        $user = User::factory()->create([
            'role' => 'pm',
            'is_active' => true,
            'last_notification_seen_at' => now()->subDay(),
        ]);

        $project = Project::create([
            'wo_number' => 'WO-TEST-2',
            'client_name' => 'Client Test 2',
            'project_name' => 'Project Test 2',
            'status' => 'open',
            'progress' => 20,
        ]);

        $process = ProjectProcess::create([
            'project_id' => $project->id,
            'code' => 'PROC-2',
            'name' => 'Process Read Test',
            'status' => 'open',
            'progress' => 20,
            'completed_checklists' => 0,
            'total_checklists' => 0,
            'sort_order' => 1,
        ]);

        ProjectProcessComment::create([
            'project_process_id' => $process->id,
            'user_id' => $user->id,
            'comment' => 'Unread comment for read test',
        ]);

        $this->actingAs($user);

        $this->assertSame(1, $user->fresh()->unreadNotificationCount());
        $this->assertSame(1, $user->fresh()->unreadProjectNotificationCount($project));
        $this->assertSame(1, $user->fresh()->unreadProcessNotificationCount($process));

        $user->markNotificationsAsRead();

        $this->assertNotNull($user->fresh()->last_notification_seen_at);
        $this->assertSame(0, $user->fresh()->unreadNotificationCount());
        $this->assertSame(0, $user->fresh()->unreadProjectNotificationCount($project));
        $this->assertSame(0, $user->fresh()->unreadProcessNotificationCount($process));
    }
}
