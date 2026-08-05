<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\CollectionError;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $userId = $request->user()->id;
        $now = Carbon::now();

        $totalBusinesses = Business::where('user_id', $userId)->count();
        $todayCount = Business::where('user_id', $userId)->whereDate('created_at', $now->toDateString())->count();
        $thisWeekCount = Business::where('user_id', $userId)->where('created_at', '>=', $now->copy()->startOfWeek())->count();
        $thisMonthCount = Business::where('user_id', $userId)->where('created_at', '>=', $now->copy()->startOfMonth())->count();

        $totalErrors = CollectionError::where('user_id', $userId)->count();

        // Top categories
        $topCategories = Business::where('user_id', $userId)
            ->whereNotNull('category')
            ->select('category', DB::raw('count(*) as total'))
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Top cities
        $topCities = Business::where('user_id', $userId)
            ->whereNotNull('city')
            ->select('city', DB::raw('count(*) as total'))
            ->groupBy('city')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Recent businesses
        $recentBusinesses = Business::where('user_id', $userId)->latest('id')->limit(5)->get();

        return view('admin.dashboard', compact(
            'totalBusinesses',
            'todayCount',
            'thisWeekCount',
            'thisMonthCount',
            'totalErrors',
            'topCategories',
            'topCities',
            'recentBusinesses'
        ));
    }
}
