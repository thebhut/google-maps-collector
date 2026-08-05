@extends('layouts.app')

@section('title', 'Error Logs')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Error Logs</h1>
            <p class="text-xs text-slate-400 mt-1">Audit log of system & Chrome extension parsing exceptions</p>
        </div>
    </div>

    <!-- Error Filter bar -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-4 shadow-lg">
        <form action="{{ route('admin.errors.index') }}" method="GET" class="flex flex-wrap items-center gap-3 text-xs">
            <select name="type" class="px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-white">
                <option value="">All Error Types</option>
                @foreach($errorTypes as $t)
                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition-colors">Filter</button>
            <a href="{{ route('admin.errors.index') }}" class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl">Reset</a>
        </form>
    </div>

    <!-- Error Logs Table -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-800/80 text-slate-400 uppercase text-[10px] tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-4">ID</th>
                        <th class="p-4">Error Type</th>
                        <th class="p-4">Message</th>
                        <th class="p-4">Session ID</th>
                        <th class="p-4">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-mono text-xs">
                    @forelse($errors as $err)
                        <tr class="hover:bg-slate-800/40 transition-colors">
                            <td class="p-4 text-slate-500">#{{ $err->id }}</td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20 font-sans">
                                    {{ $err->error_type }}
                                </span>
                            </td>
                            <td class="p-4 text-slate-200">{{ $err->message }}</td>
                            <td class="p-4 font-sans">
                                @if($err->collection_session_id)
                                    <a href="{{ route('admin.sessions.show', $err->collection_session_id) }}" class="text-indigo-400 hover:underline">Session #{{ $err->collection_session_id }}</a>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-400 font-sans text-[11px]">{{ $err->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-8 text-center text-slate-500 font-sans">No error logs recorded. system running cleanly!</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-800 bg-slate-900/60">
            {{ $errors->links() }}
        </div>
    </div>

</div>
@endsection
