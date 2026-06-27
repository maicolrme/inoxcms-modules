<div>
    @include('api::partials.api-tabs')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">API Call Log</h2>
            <p class="text-gray-600 mt-1">Monitor all API requests and responses.</p>
        </div>
        <button wire:click="clearFilters" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Clear Filters</button>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <div class="grid grid-cols-4 gap-3">
            <div>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search URL or IP..."
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div>
                <select wire:model.live="methodFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">All methods</option>
                    @foreach ($methods as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select wire:model.live="statusFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">All status codes</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <input type="date" wire:model.live="dateFrom" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" title="From date">
                <input type="date" wire:model.live="dateTo" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" title="To date">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-4 py-3">Time</th>
                    <th class="px-4 py-3">Method</th>
                    <th class="px-4 py-3">URL</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">IP</th>
                    <th class="px-4 py-3">Duration</th>
                    <th class="px-4 py-3">User</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($entries as $entry)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $entry->created_at->format('H:i:s') }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                {{ $entry->method === 'GET' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $entry->method === 'POST' ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $entry->method === 'PUT' ? 'bg-orange-100 text-orange-700' : '' }}
                                {{ $entry->method === 'DELETE' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $entry->method }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-900 max-w-xs truncate font-mono text-xs">{{ $entry->url }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                {{ $entry->status_code < 300 ? 'bg-green-100 text-green-700' : '' }}
                                {{ $entry->status_code >= 300 && $entry->status_code < 400 ? 'bg-blue-100 text-blue-700' : '' }}
                                {{ $entry->status_code >= 400 && $entry->status_code < 500 ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $entry->status_code >= 500 ? 'bg-red-100 text-red-700' : '' }}">
                                {{ $entry->status_code }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $entry->ip_address }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $entry->duration_ms }}ms</td>
                        <td class="px-4 py-3 text-gray-500">{{ $entry->user_id ?? '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-400">No API calls logged yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($entries->hasPages())
        <div class="mt-4">
            {{ $entries->links() }}
        </div>
    @endif
</div>
