<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DesktopApiController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau password tidak sesuai.',
            ], 401);
        }

        if (! $user->isApproved()) {
            return response()->json([
                'success' => false,
                'message' => 'Akun belum aktif.',
            ], 403);
        }

        $token = Str::random(80);
        $user->forceFill([
            'remember_token' => hash('sha256', $token),
        ])->save();

        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'user' => $this->formatUser($user),
            ],
            'message' => 'Login berhasil.',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    public function dashboardStats(): JsonResponse
    {
        $projects = Project::query()->get();
        $statuses = $projects->map(fn (Project $project): string => $this->deliveryStatus($project));

        return response()->json([
            'totalProjects' => $projects->count(),
            'onTrack' => $statuses->filter(fn (string $status): bool => $status === 'on track')->count(),
            'atRisk' => $statuses->filter(fn (string $status): bool => $status === 'at risk')->count(),
            'delayed' => $statuses->filter(fn (string $status): bool => $status === 'delayed')->count(),
        ]);
    }

    public function recentProjects(Request $request): JsonResponse
    {
        $limit = min(max((int) $request->query('limit', 10), 1), 50);

        $projects = Project::query()
            ->latest('updated_at')
            ->limit($limit)
            ->get()
            ->map(fn (Project $project): array => $this->formatProject($project))
            ->values();

        return response()->json($projects);
    }

    public function monthly(): JsonResponse
    {
        $months = collect(range(11, 0))
            ->map(fn (int $offset) => now()->startOfMonth()->subMonths($offset));

        $allProjects = Project::query()->get();

        return response()->json([
            'months' => $months->map(fn ($month): string => $month->format('M'))->values(),
            'progress' => $months->map(function ($month) use ($allProjects): float {
                $projects = $allProjects->filter(fn (Project $project): bool => $project->created_at?->isSameMonth($month) ?? false);

                return round((float) ($projects->avg('progress') ?? 0), 1);
            })->values(),
            'revenue' => $months->map(function ($month) use ($allProjects): int {
                return $allProjects
                    ->filter(fn (Project $project): bool => $project->created_at?->isSameMonth($month) ?? false)
                    ->count();
            })->values(),
            'completion' => $months->map(function ($month) use ($allProjects): int {
                return $allProjects
                    ->filter(fn (Project $project): bool => ($project->updated_at?->isSameMonth($month) ?? false) && $project->status === 'close')
                    ->count();
            })->values(),
        ]);
    }

    private function formatUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? '',
            'photoUrl' => null,
            'isActive' => (bool) $user->is_active,
            'isApproved' => $user->isApproved(),
        ];
    }

    private function formatProject(Project $project): array
    {
        return [
            'id' => $project->id,
            'projectNo' => $project->wo_number ?? '',
            'projectName' => $project->project_name ?? '',
            'customer' => $project->client_name ?? '',
            'pm' => '',
            'progress' => (float) $project->progress,
            'status' => $this->deliveryStatus($project),
            'customerPO' => null,
            'location' => null,
            'year' => $project->start_project?->format('Y'),
            'equipmentName' => null,
            'targetDeliveryDate' => $project->target_finish?->toIso8601String(),
            'actualDeliveryDate' => null,
            'createdAt' => $project->created_at?->toIso8601String(),
        ];
    }

    private function deliveryStatus(Project $project): string
    {
        $today = now()->startOfDay();
        $targetFinish = $project->target_finish;

        if ($project->status === 'close') {
            return 'close';
        }

        if ($targetFinish && $targetFinish->lt($today)) {
            return 'delayed';
        }

        if ($project->progress < 60 || ($targetFinish && $targetFinish->diffInDays($today, false) >= -14)) {
            return 'at risk';
        }

        return 'on track';
    }
}
