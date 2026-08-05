@extends('layouts.app')

@section('title', 'Manage Businesses')

@section('content')
<div x-data="{
    selectedIds: [],
    selectAll: false,
    showDeleteModal: false,
    toggleAll() {
        this.selectAll = !this.selectAll;
        if (this.selectAll) {
            this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => parseInt(cb.value));
        } else {
            this.selectedIds = [];
        }
    },
    toggleRow(id) {
        if (this.selectedIds.includes(id)) {
            this.selectedIds = this.selectedIds.filter(i => i !== id);
        } else {
            this.selectedIds.push(id);
        }
        this.selectAll = this.selectedIds.length === document.querySelectorAll('.row-checkbox').length;
    }
}" class="space-y-6">

    <!-- Header Actions & CSV Exports (Section 26) -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Business Directory</h1>
            <p class="text-xs text-slate-400 mt-1">View, search, filter, and export collected records</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- Export All -->
            <a href="{{ route('admin.businesses.export.all') }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs rounded-xl transition-all shadow-lg shadow-emerald-600/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <span>Export All CSV</span>
            </a>

            <!-- Export Filtered -->
            <a href="{{ route('admin.businesses.export.filtered', request()->query()) }}" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-medium text-xs rounded-xl transition-all shadow-lg shadow-indigo-600/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <span>Export Current Filter</span>
            </a>

            <!-- Export Selected -->
            <form action="{{ route('admin.businesses.export.selected') }}" method="POST" inline x-show="selectedIds.length > 0">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="submit" class="px-3.5 py-2 bg-purple-600 hover:bg-purple-500 text-white font-medium text-xs rounded-xl transition-all shadow-lg shadow-purple-600/20 flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    <span>Export Selected (<span x-text="selectedIds.length"></span>)</span>
                </button>
            </form>

            <!-- Bulk Delete Trigger (Section 27) -->
            <button type="button" @click="showDeleteModal = true" x-show="selectedIds.length > 0" class="px-3.5 py-2 bg-rose-600 hover:bg-rose-500 text-white font-medium text-xs rounded-xl transition-all shadow-lg shadow-rose-600/20 flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                <span>Delete Selected (<span x-text="selectedIds.length"></span>)</span>
            </button>
        </div>
    </div>

    <!-- Search & Filter Controls (Sections 23, 24) -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-5 shadow-lg space-y-4">
        <form action="{{ route('admin.businesses.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search bar -->
                <div class="lg:col-span-2">
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Search Keywords</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, city, address, category, place ID..."
                            class="w-full pl-10 pr-4 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">Category</label>
                    <select name="category" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- City Filter -->
                <div>
                    <label class="block text-[11px] font-semibold text-slate-400 uppercase tracking-wider mb-1">City</label>
                    <select name="city" class="w-full px-3 py-2 bg-slate-800/80 border border-slate-700 rounded-xl text-xs text-white focus:outline-none focus:border-indigo-500">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>{{ $city }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Secondary Filters Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3 pt-2 border-t border-slate-800/80 text-xs">
                <div>
                    <select name="rating" class="w-full px-2.5 py-1.5 bg-slate-800/80 border border-slate-700 rounded-lg text-slate-300">
                        <option value="">Min Rating</option>
                        <option value="4.5" {{ request('rating') == '4.5' ? 'selected' : '' }}>4.5+ Stars</option>
                        <option value="4.0" {{ request('rating') == '4.0' ? 'selected' : '' }}>4.0+ Stars</option>
                        <option value="3.5" {{ request('rating') == '3.5' ? 'selected' : '' }}>3.5+ Stars</option>
                        <option value="3.0" {{ request('rating') == '3.0' ? 'selected' : '' }}>3.0+ Stars</option>
                    </select>
                </div>

                <div>
                    <select name="has_phone" class="w-full px-2.5 py-1.5 bg-slate-800/80 border border-slate-700 rounded-lg text-slate-300">
                        <option value="">Has Phone?</option>
                        <option value="yes" {{ request('has_phone') == 'yes' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div>
                    <select name="has_website" class="w-full px-2.5 py-1.5 bg-slate-800/80 border border-slate-700 rounded-lg text-slate-300">
                        <option value="">Has Website?</option>
                        <option value="yes" {{ request('has_website') == 'yes' ? 'selected' : '' }}>Yes</option>
                    </select>
                </div>

                <div>
                    <select name="per_page" class="w-full px-2.5 py-1.5 bg-slate-800/80 border border-slate-700 rounded-lg text-slate-300">
                        <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25 / page</option>
                        <option value="50" {{ request('per_page', '50') == '50' ? 'selected' : '' }}>50 / page</option>
                        <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100 / page</option>
                        <option value="250" {{ request('per_page') == '250' ? 'selected' : '' }}>250 / page</option>
                    </select>
                </div>

                <div class="col-span-2 flex items-center justify-end space-x-2">
                    <a href="{{ route('admin.businesses.index') }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg font-medium transition-colors">Reset</a>
                    <button type="submit" class="px-4 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-semibold transition-colors">Apply Filters</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table (Section 22) -->
    <div class="bg-slate-900/80 border border-slate-800 rounded-2xl shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-300">
                <thead class="bg-slate-800/80 text-slate-400 uppercase text-[10px] tracking-wider font-semibold border-b border-slate-800">
                    <tr>
                        <th class="p-4 w-10">
                            <input type="checkbox" @click="toggleAll()" :checked="selectAll" class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        </th>
                        <th class="p-4">Business Name</th>
                        <th class="p-4">Phone</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">City</th>
                        <th class="p-4">Rating</th>
                        <th class="p-4">Website</th>
                        <th class="p-4">Collected</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60">
                    @forelse($businesses as $b)
                        <tr class="hover:bg-slate-800/40 transition-colors {{ in_array($b->id, old('ids', [])) ? 'bg-indigo-950/20' : '' }}">
                            <td class="p-4">
                                <input type="checkbox" value="{{ $b->id }}" @click="toggleRow({{ $b->id }})" :checked="selectedIds.includes({{ $b->id }})" class="row-checkbox rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                            </td>
                            <td class="p-4 font-semibold text-white">
                                <a href="{{ route('admin.businesses.show', $b->id) }}" class="hover:text-indigo-400 transition-colors">{{ $b->name }}</a>
                                @if($b->place_id)
                                    <span class="block text-[10px] text-slate-500 font-mono truncate max-w-[150px]" title="{{ $b->place_id }}">{{ $b->place_id }}</span>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-slate-300">{{ $b->phone ?? '-' }}</td>
                            <td class="p-4 text-slate-400">
                                @if($b->category)
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 font-medium">{{ $b->category }}</span>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-300">{{ $b->city ?? '-' }}</td>
                            <td class="p-4">
                                @if($b->rating)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        ★ {{ $b->rating }} ({{ $b->review_count ?? 0 }})
                                    </span>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($b->website)
                                    <a href="{{ $b->website }}" target="_blank" class="text-indigo-400 hover:underline inline-flex items-center space-x-1">
                                        <span>Visit</span>
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                    </a>
                                @else
                                    <span class="text-slate-500">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-slate-400 text-[11px]">{{ $b->created_at->format('M d, Y') }}</td>
                            <td class="p-4 text-right space-x-2">
                                <a href="{{ route('admin.businesses.show', $b->id) }}" class="px-2.5 py-1 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded font-medium text-[11px] transition-colors">View</a>
                                <form action="{{ route('admin.businesses.destroy', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this business record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 rounded font-medium text-[11px] transition-colors">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="p-8 text-center text-slate-500">No businesses match the given search criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/60">
            {{ $businesses->links() }}
        </div>
    </div>

    <!-- Bulk Delete Confirmation Modal (Section 27) -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="showDeleteModal = false" class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center space-x-3 text-rose-400">
                <div class="p-3 bg-rose-500/10 rounded-xl">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h3 class="text-lg font-bold text-white">Confirm Bulk Delete</h3>
            </div>

            <p class="text-sm text-slate-300">
                Are you sure you want to delete <span class="font-bold text-white" x-text="selectedIds.length"></span> selected business record(s)? This action cannot be undone.
            </p>

            <form action="{{ route('admin.businesses.bulk-delete') }}" method="POST" class="flex justify-end space-x-3 pt-2">
                @csrf
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 font-medium rounded-xl text-xs">Cancel</button>
                <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-xl text-xs shadow-lg shadow-rose-600/30">Delete Businesses</button>
            </form>
        </div>
    </div>

</div>
@endsection
