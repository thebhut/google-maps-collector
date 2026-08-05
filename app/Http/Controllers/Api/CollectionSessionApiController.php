<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionSession;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionSessionApiController extends Controller
{
    /**
     * POST /api/v1/collection-sessions
     * Create a new collection session.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_uuid' => 'required|string',
            'source' => 'nullable|string',
            'search_query' => 'nullable|string',
            'google_maps_url' => 'nullable|string',
        ]);

        $userId = $request->user()?->id ?? User::first()?->id ?? 1;

        $session = CollectionSession::create([
            'user_id' => $userId,
            'session_uuid' => $validated['session_uuid'],
            'source' => $validated['source'] ?? 'google_maps',
            'search_query' => $validated['search_query'] ?? null,
            'google_maps_url' => $validated['google_maps_url'] ?? null,
            'started_at' => now(),
            'status' => 'running',
        ]);

        return response()->json([
            'success' => true,
            'data' => $session,
        ], 201);
    }

    /**
     * PATCH /api/v1/collection-sessions/{id}
     * Update session status or metrics.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $userId = $request->user()?->id ?? User::first()?->id ?? 1;
        $session = CollectionSession::where('user_id', $userId)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'nullable|string|in:running,completed,stopped,failed',
            'businesses_detected' => 'nullable|integer',
            'businesses_uploaded' => 'nullable|integer',
            'duplicates_count' => 'nullable|integer',
            'errors_count' => 'nullable|integer',
        ]);

        if (isset($validated['status']) && in_array($validated['status'], ['completed', 'stopped', 'failed'])) {
            $validated['finished_at'] = now();
        }

        $session->update(array_filter($validated, fn ($val) => !is_null($val)));

        return response()->json([
            'success' => true,
            'data' => $session,
        ]);
    }
}
