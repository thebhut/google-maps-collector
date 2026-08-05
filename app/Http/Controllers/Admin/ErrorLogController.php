<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CollectionError;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ErrorLogController extends Controller
{
    /**
     * GET /admin/errors
     */
    public function index(Request $request): View
    {
        $query = CollectionError::where('user_id', $request->user()->id)
            ->with('collectionSession');

        if ($type = $request->input('type')) {
            $query->where('error_type', $type);
        }

        $errors = $query->latest('id')->paginate(30)->withQueryString();
        $errorTypes = CollectionError::where('user_id', $request->user()->id)->distinct()->pluck('error_type');

        return view('admin.errors.index', compact('errors', 'errorTypes'));
    }
}
