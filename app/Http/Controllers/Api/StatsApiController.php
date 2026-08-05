<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CollectionError;
use App\Models\CollectionSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class StatsApiController extends Controller
{
    /**
     * GET /api/v1/stats
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id ?? User::first()?->id ?? 1;
        $now = Carbon::now();

        $totalBusinesses = Business::where('user_id', $userId)->count();
        $collectedToday = Business::where('user_id', $userId)->whereDate('created_at', $now->toDateString())->count();
        $collectedThisWeek = Business::where('user_id', $userId)->where('created_at', '>=', $now->copy()->startOfWeek())->count();
        $collectedThisMonth = Business::where('user_id', $userId)->where('created_at', '>=', $now->copy()->startOfMonth())->count();

        $totalSessions = CollectionSession::where('user_id', $userId)->count();
        $totalErrors = CollectionError::where('user_id', $userId)->count();
        $totalDuplicatesDetected = CollectionSession::where('user_id', $userId)->sum('duplicates_count');

        return response()->json([
            'success' => true,
            'stats' => [
                'total_businesses' => $totalBusinesses,
                'collected_today' => $collectedToday,
                'collected_this_week' => $collectedThisWeek,
                'collected_this_month' => $collectedThisMonth,
                'total_sessions' => $totalSessions,
                'total_errors' => $totalErrors,
                'total_duplicates_detected' => $totalDuplicatesDetected,
            ],
        ]);
    }
}
