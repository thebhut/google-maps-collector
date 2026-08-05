@extends('layouts.app')

@section('title', 'Dashboard Overview')

@section('content')
<div class="space-y-6">

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Dashboard Overview</h1>
            <p class="text-xs text-slate-400 mt-1">Real-time business leads & collection analytics</p>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Businesses</p>
                <div class="p-2 rounded-xl bg-indigo-500/10 text-indigo-400">🏢</div>
            </div>
            <p class="text-3xl font-extrabold text-white mt-3">{{ number_format($totalBusinesses) }}</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Collected Today</p>
                <div class="p-2 rounded-xl bg-emerald-500/10 text-emerald-400">📅</div>
            </div>
            <p class="text-3xl font-extrabold text-white mt-3">{{ number_format($todayCount) }}</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-teal-500"></div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">This Week</p>
                <div class="p-2 rounded-xl bg-amber-500/10 text-amber-400">📈</div>
            </div>
            <p class="text-3xl font-extrabold text-white mt-3">{{ number_format($thisWeekCount) }}</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-orange-500"></div>
        </div>

        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg relative overflow-hidden">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">This Month</p>
                <div class="p-2 rounded-xl bg-purple-500/10 text-purple-400">📊</div>
            </div>
            <p class="text-3xl font-extrabold text-white mt-3">{{ number_format($thisMonthCount) }}</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500"></div>
        </div>
    </div>

    <!-- Top Categories & Top Cities breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Categories -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-lg">
            <h2 class="text-base font-bold text-white mb-4 flex items-center justify-between">
                <span>Top Business Categories</span>
                <span class="text-xs text-slate-400 font-normal">By count</span>
            </h2>
            <div class="space-y-3">
                @forelse($topCategories as $cat)
                    <div>
                        <div class="flex justify-between text-xs font-medium text-slate-300 mb-1">
                            <span>{{ $cat->category }}</span>
                            <span class="text-slate-400">{{ number_format($cat->total) }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-indigo-500 rounded-full" style="width: {{ $totalBusinesses > 0 ? min(100, round(($cat->total / $totalBusinesses) * 100)) : 0 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No business category data available yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Top Cities -->
        <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-lg">
            <h2 class="text-base font-bold text-white mb-4 flex items-center justify-between">
                <span>Top Locations / Cities</span>
                <span class="text-xs text-slate-400 font-normal">By count</span>
            </h2>
            <div class="space-y-3">
                @forelse($topCities as $city)
                    <div>
                        <div class="flex justify-between text-xs font-medium text-slate-300 mb-1">
                            <span>{{ $city->city }}</span>
                            <span class="text-slate-400">{{ number_format($city->total) }}</span>
                        </div>
                        <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500 rounded-full" style="width: {{ $totalBusinesses > 0 ? min(100, round(($city->total / $totalBusinesses) * 100)) : 0 }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No city data collected yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Recent Records Overview -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-lg">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-white">Recently Collected Businesses</h2>
            <a href="{{ route('admin.businesses.index') }}" class="text-xs font-semibold text-indigo-400 hover:text-indigo-300">View All Businesses &rarr;</a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px] tracking-wider font-semibold">
                    <tr>
                        <th class="px-4 py-3 rounded-l-lg">Name</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">City</th>
                        <th class="px-4 py-3">Phone</th>
                        <th class="px-4 py-3">Rating</th>
                        <th class="px-4 py-3 rounded-r-lg">Collected</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($recentBusinesses as $b)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="px-4 py-3 font-semibold text-white">
                                <a href="{{ route('admin.businesses.show', $b->id) }}" class="hover:text-indigo-400">{{ $b->name }}</a>
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $b->category ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-400">{{ $b->city ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-slate-300">{{ $b->phone ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($b->rating)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        ★ {{ $b->rating }} ({{ $b->review_count ?? 0 }})
                                    </span>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-400">{{ $b->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6 text-slate-500">No businesses collected yet. Install the Chrome extension to start collecting!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
