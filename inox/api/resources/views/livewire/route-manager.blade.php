<div>
    @include('api::partials.api-tabs')

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Route Manager</h2>
            <p class="text-gray-600 mt-1">View and manage all API routes.</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex gap-3 items-center">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search routes..."
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            </div>
            <div class="w-40">
                <select wire:model.live="methodFilter" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <option value="">All methods</option>
                    @foreach ($methods as $m)
                        <option value="{{ $m }}">{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Dynamic Model Routes --}}
    @if (!empty($groups['dynamic']))
        @php $hasVisible = false; @endphp
        @foreach ($groups['dynamic'] as $group)
            @if (!empty($group['routes']))
                @php $hasVisible = true; @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4" x-data="{ open: true }">
                    <button @click="open = !open"
                            class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors text-left">
                        <div class="flex items-center gap-3">
                            <svg x-show="!open" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            <svg x-show="open" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            <span class="font-semibold text-gray-900">{{ $group['name'] }}</span>
                            <span class="text-xs text-gray-500 font-mono">{{ $group['table'] }}</span>
                            <span class="text-xs px-2 py-0.5 rounded {{ $group['api_enabled'] ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                {{ $group['enabled_count'] }}/{{ $group['total'] }} enabled
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button wire:click="toggleModel('{{ $group['name'] }}', {{ $group['api_enabled'] ? 'false' : 'true' }})"
                                    class="relative w-11 h-6 rounded-full transition-colors inline-block {{ $group['api_enabled'] ? 'bg-green-500' : 'bg-gray-300' }}"
                                    title="{{ $group['api_enabled'] ? 'Disable all' : 'Enable all' }}">
                                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $group['api_enabled'] ? 'translate-x-5' : '' }}"></span>
                            </button>
                            <span class="text-xs text-gray-500 w-10">{{ $group['api_enabled'] ? 'ON' : 'OFF' }}</span>
                        </div>
                    </button>

                    <div x-show="open">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-t text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                                    <th class="px-6 py-2 w-20">Method</th>
                                    <th class="px-6 py-2">Route</th>
                                    <th class="px-6 py-2 w-16 text-center">Auth</th>
                                    <th class="px-6 py-2 w-20 text-center">Status</th>
                                    <th class="px-6 py-2 w-20 text-center">Toggle</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($group['routes'] as $route)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-2.5">
                                            <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                                {{ $route['method'] === 'GET' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $route['method'] === 'POST' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $route['method'] === 'PUT' ? 'bg-orange-100 text-orange-700' : '' }}
                                                {{ $route['method'] === 'DELETE' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $route['method'] === 'PATCH' ? 'bg-purple-100 text-purple-700' : '' }}">
                                                {{ $route['method'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-2.5">
                                            <span class="font-mono text-xs text-gray-900">{{ $route['uri'] }}</span>
                                            <span class="text-xs text-gray-400 ml-2">{{ $route['action'] }}</span>
                                            @if ($route['inherited'])
                                                <span class="text-xs text-gray-400 ml-1" title="Inherited from model settings">↗</span>
                                            @else
                                                <span class="text-xs text-blue-500 ml-1" title="Individual override">✦</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-2.5 text-center">
                                            @if ($route['auth'])
                                                <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">🔒</span>
                                            @else
                                                <span class="text-xs text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-2.5 text-center">
                                            @if ($route['enabled'])
                                                <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">ON</span>
                                            @else
                                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-medium">OFF</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-2.5 text-center">
                                            @if ($route['enabled'])
                                                <button wire:click="toggleRoute('{{ $route['method'] }}', '{{ $route['uri'] }}')"
                                                        class="relative w-9 h-5 rounded-full transition-colors inline-block bg-green-500">
                                                    <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform translate-x-4"></span>
                                                </button>
                                            @else
                                                <button wire:click="toggleRoute('{{ $route['method'] }}', '{{ $route['uri'] }}')"
                                                        class="relative w-9 h-5 rounded-full transition-colors inline-block bg-gray-300">
                                                    <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow"></span>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endforeach
        @if (!$hasVisible)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-gray-400">
                No routes match your filters.
            </div>
        @endif
    @endif

    {{-- System Routes --}}
    @if (!empty($groups['system']))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-4" x-data="{ open: true }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-2 px-6 py-4 hover:bg-gray-50 transition-colors text-left">
                <svg x-show="!open" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <svg x-show="open" class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                <span class="font-semibold text-gray-900">System Routes</span>
                <span class="text-xs text-gray-500">{{ count($groups['system']) }} routes</span>
            </button>

            <div x-show="open">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-t text-left text-xs font-medium text-gray-500 uppercase tracking-wider bg-gray-50">
                            <th class="px-6 py-2 w-20">Method</th>
                            <th class="px-6 py-2">Route</th>
                            <th class="px-6 py-2 w-24">Name</th>
                            <th class="px-6 py-2 w-16 text-center">Auth</th>
                            <th class="px-6 py-2 w-20 text-center">Status</th>
                            <th class="px-6 py-2 w-20 text-center">Toggle</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($groups['system'] as $route)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-2.5">
                                    <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                        {{ $route['method'] === 'GET' ? 'bg-green-100 text-green-700' : '' }}
                                        {{ $route['method'] === 'POST' ? 'bg-blue-100 text-blue-700' : '' }}
                                        {{ $route['method'] === 'PUT' ? 'bg-orange-100 text-orange-700' : '' }}
                                        {{ $route['method'] === 'DELETE' ? 'bg-red-100 text-red-700' : '' }}
                                        {{ $route['method'] === 'PATCH' ? 'bg-purple-100 text-purple-700' : '' }}">
                                        {{ $route['method'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-2.5 font-mono text-xs text-gray-900">{{ $route['uri'] }}</td>
                                <td class="px-6 py-2.5 text-xs text-gray-500">{{ $route['name'] ?? '—' }}</td>
                                <td class="px-6 py-2.5 text-center">
                                    @if ($route['auth'])
                                        <span class="text-xs bg-yellow-100 text-yellow-700 px-1.5 py-0.5 rounded">🔒</span>
                                    @else
                                        <span class="text-xs text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-2.5 text-center">
                                    @if ($route['enabled'])
                                        <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded font-medium">ON</span>
                                    @else
                                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded font-medium">OFF</span>
                                    @endif
                                </td>
                                <td class="px-6 py-2.5 text-center">
                                    <button wire:click="toggleRoute('{{ $route['method'] }}', '{{ $route['uri'] }}')"
                                            class="relative w-9 h-5 rounded-full transition-colors inline-block {{ $route['enabled'] ? 'bg-green-500' : 'bg-gray-300' }}">
                                        <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform {{ $route['enabled'] ? 'translate-x-4' : '' }}"></span>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (empty($groups['dynamic']) && empty($groups['system']))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center text-gray-400">
            No API routes found.
            @if (\Illuminate\Support\Facades\Route::has('admin.schema-studio.index'))
                <div class="mt-2">
                    <a href="{{ route('admin.schema-studio.index') }}" class="text-blue-600 hover:underline">Create a dynamic model in Schema Studio</a>
                </div>
            @endif
        </div>
    @endif
</div>
