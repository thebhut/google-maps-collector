<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Services\CsvExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BusinessController extends Controller
{
    public function __construct(protected CsvExportService $csvService)
    {
    }

    /**
     * GET /admin/businesses
     */
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $query = Business::where('user_id', $userId);

        // Search across fields
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%")
                    ->orWhere('place_id', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        if ($state = $request->input('state')) {
            $query->where('state', $state);
        }

        if ($country = $request->input('country')) {
            $query->where('country', $country);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($rating = $request->input('rating')) {
            $query->where('rating', '>=', (float) $rating);
        }

        if ($source = $request->input('source')) {
            $query->where('source', $source);
        }

        if ($request->input('has_phone') === 'yes') {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        }

        if ($request->input('has_website') === 'yes') {
            $query->whereNotNull('website')->where('website', '!=', '');
        }

        if ($request->input('has_email') === 'yes') {
            $query->whereNotNull('email')->where('email', '!=', '');
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sorting
        $sortColumn = $request->input('sort', 'id');
        $sortDir = strtolower($request->input('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'name', 'city', 'category', 'rating', 'review_count', 'created_at'];

        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDir);
        } else {
            $query->latest('id');
        }

        $perPage = (int) $request->input('per_page', 50);
        $businesses = $query->paginate(min(max($perPage, 10), 250))->withQueryString();

        // Dropdown options for filters
        $cities = Business::where('user_id', $userId)->whereNotNull('city')->distinct()->pluck('city')->sort();
        $categories = Business::where('user_id', $userId)->whereNotNull('category')->distinct()->pluck('category')->sort();
        $sources = Business::where('user_id', $userId)->whereNotNull('source')->distinct()->pluck('source')->sort();

        return view('admin.businesses.index', compact('businesses', 'cities', 'categories', 'sources'));
    }

    /**
     * GET /admin/businesses/{id}
     */
    public function show(Request $request, int $id): View
    {
        $business = Business::where('user_id', $request->user()->id)->findOrFail($id);
        return view('admin.businesses.show', compact('business'));
    }

    /**
     * DELETE /admin/businesses/{id}
     */
    public function destroy(Request $request, int $id): RedirectResponse
    {
        $business = Business::where('user_id', $request->user()->id)->findOrFail($id);
        $business->delete();

        return redirect()->route('admin.businesses.index')->with('success', 'Business record deleted successfully.');
    }

    /**
     * POST /admin/businesses/bulk-delete
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $ids = $request->input('ids');
        $deleted = Business::where('user_id', $request->user()->id)
            ->whereIn('id', $ids)
            ->delete();

        return redirect()->route('admin.businesses.index')->with('success', "{$deleted} business record(s) deleted successfully.");
    }

    /**
     * Stream CSV Export All
     */
    public function exportAll(Request $request)
    {
        $query = Business::where('user_id', $request->user()->id)->latest('id');
        return $this->csvService->export($query, 'all_businesses_' . date('Y-m-d') . '.csv');
    }

    /**
     * Stream CSV Export Filtered
     */
    public function exportFiltered(Request $request)
    {
        $userId = $request->user()->id;
        $query = Business::where('user_id', $userId);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('website', 'like', "%{$search}%")
                    ->orWhere('place_id', 'like', "%{$search}%");
            });
        }

        if ($city = $request->input('city')) {
            $query->where('city', $city);
        }

        if ($category = $request->input('category')) {
            $query->where('category', $category);
        }

        if ($rating = $request->input('rating')) {
            $query->where('rating', '>=', (float) $rating);
        }

        if ($request->input('has_phone') === 'yes') {
            $query->whereNotNull('phone')->where('phone', '!=', '');
        }

        if ($request->input('has_website') === 'yes') {
            $query->whereNotNull('website')->where('website', '!=', '');
        }

        return $this->csvService->export($query, 'filtered_businesses_' . date('Y-m-d') . '.csv');
    }

    /**
     * Stream CSV Export Selected IDs
     */
    public function exportSelected(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $ids = $request->input('ids');
        $query = Business::where('user_id', $request->user()->id)->whereIn('id', $ids)->latest('id');

        return $this->csvService->export($query, 'selected_businesses_' . date('Y-m-d') . '.csv');
    }
}
