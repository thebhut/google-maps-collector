<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CollectionError;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ErrorLogApiController extends Controller
{
    /**
     * POST /api/v1/errors
     * Report an error from the Chrome Extension.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'collection_session_id' => 'nullable|integer|exists:collection_sessions,id',
            'error_type' => 'required|string',
            'message' => 'required|string',
            'payload' => 'nullable|array',
        ]);

        $userId = $request->user()?->id ?? User::first()?->id ?? 1;

        $error = CollectionError::create([
            'user_id' => $userId,
            'collection_session_id' => $validated['collection_session_id'] ?? null,
            'error_type' => $validated['error_type'],
            'message' => $validated['message'],
            'payload' => $validated['payload'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'data' => $error,
        ], 201);
    }
}
