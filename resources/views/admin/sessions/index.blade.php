@extends('layouts.app')

@section('title', 'Collection Sessions')

@section('content')
<div class="space-y-6">

    <!-- Header (Section 28) -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Collection Sessions</h1>
            <p class="text-xs text-slate-400 mt-1">Audit log of extension scraping runs & session metrics</p>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-800/80 text-slate-400 uppercase text-[10px] tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-4">Session UUID</th>
                        <th class="p-4">Search Query / URL</th>
                        <th class="p-4">Started</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-center">Detected</th>
                        <th class="p-4 text-center">Uploaded</th>
                        <th class="p-4 text-center">Duplicates</th>
                        <th class="p-4 text-center">Errors</th>
                        <th class="p-4 text-right">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($sessions as $s)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-4 font-mono text-indigo-400 font-semibold select-all">
                                <a href="{{ route('admin.sessions.show', $s->id) }}" class="hover:underline">{{ substr($s->session_uuid, 0, 8) }}...</a>
                            </td>
                            <td class="p-4 text-slate-300 max-w-[200px] truncate" title="{{ $s->search_query ?? $s->google_maps_url }}">
                                {{ $s->search_query ?? ($s->google_maps_url ? parse_url($s->google_maps_url, PHP_URL_PATH) : 'Google Maps') }}
                            </td>
                            <td class="p-4 text-slate-400 text-[11px]">{{ $s->started_at?->format('M d, Y H:i') ?? '-' }}</td>
                            <td class="p-4">
                                @if($s->status === 'running')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Running</span>
                                @elseif($s->status === 'completed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Completed</span>
                                @elseif($s->status === 'stopped')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400">Stopped</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Failed</span>
                                @endif
                            </td>
                            <td class="p-4 text-center font-bold text-slate-300">{{ number_format($s->businesses_detected) }}</td>
                            <td class="p-4 text-center font-bold text-emerald-400">{{ number_format($s->businesses_uploaded) }}</td>
                            <td class="p-4 text-center font-bold text-amber-400">{{ number_format($s->duplicates_count) }}</td>
                            <td class="p-4 text-center font-bold text-rose-400">{{ number_format($s->errors_count) }}</td>
                            <td class="p-4 text-right">
                                <a href="{{ route('admin.sessions.show', $s->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded font-medium text-[11px] transition-colors">View Session</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500">No collection sessions recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-900/60">
            {{ $sessions->links() }}
        </div>
    </div>

</div>
@endsection
