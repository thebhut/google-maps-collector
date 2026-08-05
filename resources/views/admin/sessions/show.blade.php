@extends('layouts.app')

@section('title', 'Session Details')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <a href="{{ route('admin.sessions.index') }}" class="text-xs font-semibold text-slate-400 hover:text-white flex items-center space-x-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Sessions</span>
        </a>
    </div>

    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 shadow-xl space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-slate-800 pb-6 gap-4">
            <div>
                <h1 class="text-xl font-bold text-white font-mono">Session #{{ $session->id }}</h1>
                <p class="text-xs text-slate-400 mt-1 font-mono select-all">UUID: {{ $session->session_uuid }}</p>
            </div>
            <div>
                <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase tracking-wider
                    {{ $session->status === 'completed' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                    {{ $session->status === 'running' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                    {{ $session->status === 'stopped' ? 'bg-slate-800 text-slate-400' : '' }}
                    {{ $session->status === 'failed' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : '' }}">
                    Status: {{ $session->status }}
                </span>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
            <div class="bg-slate-800/40 p-4 rounded-xl">
                <p class="text-slate-400 uppercase tracking-wider text-[10px]">Detected</p>
                <p class="text-xl font-bold text-white mt-1">{{ number_format($session->businesses_detected) }}</p>
            </div>
            <div class="bg-slate-800/40 p-4 rounded-xl">
                <p class="text-slate-400 uppercase tracking-wider text-[10px]">Uploaded</p>
                <p class="text-xl font-bold text-emerald-400 mt-1">{{ number_format($session->businesses_uploaded) }}</p>
            </div>
            <div class="bg-slate-800/40 p-4 rounded-xl">
                <p class="text-slate-400 uppercase tracking-wider text-[10px]">Duplicates</p>
                <p class="text-xl font-bold text-amber-400 mt-1">{{ number_format($session->duplicates_count) }}</p>
            </div>
            <div class="bg-slate-800/40 p-4 rounded-xl">
                <p class="text-slate-400 uppercase tracking-wider text-[10px]">Errors Logged</p>
                <p class="text-xl font-bold text-rose-400 mt-1">{{ number_format($session->errors_count) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="text-slate-400 font-semibold uppercase text-[10px]">Search Query</p>
                <p class="text-slate-200 font-medium">{{ $session->search_query ?? 'Not specified' }}</p>
            </div>
            <div class="bg-slate-800/40 p-4 rounded-xl space-y-1">
                <p class="text-slate-400 font-semibold uppercase text-[10px]">Google Maps URL</p>
                <p class="text-indigo-400 font-mono truncate select-all">{{ $session->google_maps_url ?? 'Not specified' }}</p>
            </div>
        </div>

        <!-- Session Error Logs -->
        <div class="pt-4 border-t border-slate-800">
            <h2 class="text-base font-bold text-white mb-4">Logged Errors in Session</h2>
            <div class="space-y-2">
                @forelse($session->errors as $err)
                    <div class="p-3 bg-rose-500/5 border border-rose-500/20 rounded-xl text-xs space-y-1">
                        <div class="flex items-center justify-between text-rose-400 font-semibold">
                            <span>Type: {{ $err->error_type }}</span>
                            <span class="text-slate-500 text-[10px]">{{ $err->created_at->format('Y-m-d H:i:s') }}</span>
                        </div>
                        <p class="text-slate-300 font-mono">{{ $err->message }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-4 text-center">No errors recorded during this collection session.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection
