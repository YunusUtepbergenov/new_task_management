<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Role;
use App\Models\Scores;
use App\Models\Sector;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TaskService
{
    /** Cache TTL: 24 hours for static lookup data */
    private const STATIC_TTL = 86400;

    /** Cache TTL: 1 hour for user-related data */
    private const USER_TTL = 3600;

    // ──────────────────────────────────────────
    // Cached static data
    // ──────────────────────────────────────────

    public static function cachedScores(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('scores:all', self::STATIC_TTL, function () {
            return Scores::all();
        });
    }

    public static function cachedSectors(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('sectors:all', self::STATIC_TTL, function () {
            return Sector::all();
        });
    }

    public static function cachedSectorsWithUsers(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('sectors:with_users', self::USER_TTL, function () {
            return Sector::with(['users' => function ($query) {
                $query->select(['id', 'name', 'sector_id', 'role_id', 'leave', 'avatar'])
                      ->where('leave', 0)
                      ->orderBy('role_id');
            }])->get();
        });
    }

    public static function cachedRoles(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('roles:all', self::STATIC_TTL, function () {
            return Role::all();
        });
    }

    // ──────────────────────────────────────────
    // Cache invalidation
    // ──────────────────────────────────────────

    public static function clearScoresCache(): void
    {
        Cache::forget('scores:all');
    }

    public static function clearSectorsCache(): void
    {
        Cache::forget('sectors:all');
        Cache::forget('sectors:with_users');
    }

    public static function clearRolesCache(): void
    {
        Cache::forget('roles:all');
    }

    public static function clearUsersCache(): void
    {
        Cache::forget('sectors:with_users');
    }

    // ──────────────────────────────────────────
    // Score lists (cached)
    // ──────────────────────────────────────────

    public function scoresList(): \Illuminate\Database\Eloquent\Collection
    {
        return self::cachedScores()->whereBetween('id', [1, 48])->values();
    }

    public function hrList(): \Illuminate\Database\Eloquent\Collection
    {
        return self::cachedScores()->whereBetween('id', [49, 57])->values();
    }

    public function accountantList(): \Illuminate\Database\Eloquent\Collection
    {
        return self::cachedScores()->whereBetween('id', [58, 64])->values();
    }

    public function lawyerList(): \Illuminate\Database\Eloquent\Collection
    {
        return self::cachedScores()->whereBetween('id', [65, 71])->values();
    }

    public function maintainerList(): \Illuminate\Database\Eloquent\Collection
    {
        return self::cachedScores()->whereBetween('id', [72, 75])->values();
    }

    public function ictList(): \Illuminate\Database\Eloquent\Collection
    {
        return self::cachedScores()->whereBetween('id', [76, 80])->values();
    }

    // ──────────────────────────────────────────
    // Sector list (role-filtered, cached base)
    // ──────────────────────────────────────────

    public function sectorList(): ?\Illuminate\Database\Eloquent\Collection
    {
        $user = Auth::user();

        if ($user->isDirector() || $user->isMailer() || $user->isDeputy() || $user->isHead()) {
            return self::cachedSectorsWithUsers();
        }

        return null;
    }

    // ──────────────────────────────────────────
    // Non-cached (dynamic data)
    // ──────────────────────────────────────────

    public function projectList()
    {
        $user = Auth::user();

        if ($user->isDirector() || $user->isMailer() || $user->isDeputy() || $user->isHead() || $user->isHR()) {
            return Project::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
        }

        return [];
    }

    public function typeList()
    {
        $user = Auth::user();

        if ($user->isDirector() || $user->isMailer() || $user->isDeputy() || $user->isHead()) {
            return Type::all();
        }

        return null;
    }
}
