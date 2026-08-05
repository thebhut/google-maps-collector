@extends('layouts.app')

@section('title', 'Business Details - ' . $business->name)

@section('content')
<div class="space-y-6">

    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.businesses.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Businesses</span>
        </a>

        <form action="{{ route('admin.businesses.destroy', $business->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this business record?')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-3.5 py-2 bg-rose-600/10 hover:bg-rose-600/20 text-rose-400 border border-rose-500/30 rounded-xl font-semibold text-xs transition-colors">
                Delete Record
            </button>
        </form>
    </div>

    <!-- Details Card (Section 25) -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-800/80 pb-6 gap-4">
            <div class="space-y-1">
                <div class="flex items-center space-x-3">
                    <h1 class="text-2xl font-bold text-white tracking-tight">{{ $business->name }}</h1>
                    @if($business->rating)
                        <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            ★ {{ $business->rating }} ({{ $business->review_count ?? 0 }} reviews)
                        </span>
                    @endif
                </div>
                <p class="text-xs text-indigo-400 font-medium">{{ $business->category ?? 'Uncategorized' }}</p>
            </div>

            @if($business->maps_url)
                <a href="{{ $business->maps_url }}" target="_blank" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold rounded-xl shadow-lg shadow-indigo-600/20 inline-flex items-center space-x-2 shrink-0">
                    <span>Open on Google Maps</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                </a>
            @endif
        </div>

        <!-- Grid of Details -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-xs">
            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Phone Number</p>
                <p class="font-mono text-sm text-white font-semibold">{{ $business->phone ?? 'Not available' }}</p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Email Address</p>
                <p class="text-sm text-white font-semibold">{{ $business->email ?? 'Not available' }}</p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Website</p>
                @if($business->website)
                    <a href="{{ $business->website }}" target="_blank" class="text-sm text-indigo-400 hover:underline font-semibold truncate block">{{ $business->website }}</a>
                @else
                    <p class="text-sm text-slate-500">Not available</p>
                @endif
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1 md:col-span-2">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Full Address</p>
                <p class="text-sm text-white font-medium">{{ $business->address ?? 'Not available' }}</p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Location Breakdown</p>
                <p class="text-sm text-white font-medium">{{ implode(', ', array_filter([$business->city, $business->state, $business->country, $business->postal_code])) ?: 'Not available' }}</p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Google Place ID</p>
                <p class="font-mono text-xs text-indigo-300 select-all truncate">{{ $business->place_id ?? 'Not available' }}</p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Coordinates (Lat, Long)</p>
                <p class="font-mono text-xs text-white">
                    @if($business->latitude && $business->longitude)
                        {{ $business->latitude }}, {{ $business->longitude }}
                    @else
                        Not available
                    @endif
                </p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Source</p>
                <p class="text-xs text-slate-300 font-semibold uppercase">{{ $business->source }}</p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">First Collected At</p>
                <p class="text-xs text-slate-300">{{ $business->first_collected_at?->format('M d, Y H:i:s') ?? '-' }}</p>
            </div>

            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="font-semibold text-slate-400 uppercase tracking-wider text-[10px]">Last Updated / Collected</p>
                <p class="text-xs text-slate-300">{{ $business->last_collected_at?->format('M d, Y H:i:s') ?? '-' }}</p>
            </div>
        </div>
    </div>

</div>
@endsection
