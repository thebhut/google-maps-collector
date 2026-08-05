<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CollectionSessionController extends Controller
{
    /**
     * GET /admin/collection-sessions
     */
    public function index(Request $request): View
    {
        $sessions = CollectionSession::where('user_id', $request->user()->id)
            ->withCount('errors')
            ->latest('id')
            ->paginate(25);

        return view('admin.sessions.index', compact('sessions'));
    }

    /**
     * GET /admin/collection-sessions/{id}
     */
    public function show(Request $request, int $id): View
    {
        $session = CollectionSession::where('user_id', $request->user()->id)
            ->with(['errors' => fn ($q) => $q->latest('id')])
            ->findOrFail($id);

        return view('admin.sessions.show', compact('session'));
    }
}
