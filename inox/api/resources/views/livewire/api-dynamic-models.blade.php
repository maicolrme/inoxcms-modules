<div>
    @include('api::partials.api-tabs')

    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Dynamic Model Routes</h2>
        <p class="text-gray-600 mt-1">Enable or disable API and Admin routes for each dynamic model.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <th class="px-6 py-3">Model</th>
                    <th class="px-6 py-3">Table</th>
                    <th class="px-6 py-3 text-center">API</th>
                    <th class="px-6 py-3 text-center">Admin</th>
                    <th class="px-6 py-3">API Routes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($models as $model)
                    @php
                        $routes = $model['routes'] ?? ['api' => true, 'admin' => true];
                        $table = $model['table'];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $model['name'] }}</span>
                            @if ($model['description'] ?? false)
                                <p class="text-xs text-gray-500">{{ $model['description'] }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-gray-600 font-mono text-xs">{{ $table }}</td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="toggleApi('{{ $model['name'] }}')"
                                    class="relative w-11 h-6 rounded-full transition-colors inline-block {{ $routes['api'] ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $routes['api'] ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button wire:click="toggleAdmin('{{ $model['name'] }}')"
                                    class="relative w-11 h-6 rounded-full transition-colors inline-block {{ $routes['admin'] ? 'bg-green-500' : 'bg-gray-300' }}">
                                <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $routes['admin'] ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-wrap gap-1">
                                @if ($routes['api'] ?? true)
                                    <span class="text-xs bg-green-50 text-green-700 px-2 py-0.5 rounded">GET    /api/dynamic/{{ $model['name'] }}</span>
                                    <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded">POST   /api/dynamic/{{ $model['name'] }}</span>
                                    <span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded">GET    /api/dynamic/{{ $model['name'] }}/{id}</span>
                                    <span class="text-xs bg-orange-50 text-orange-700 px-2 py-0.5 rounded">PUT    /api/dynamic/{{ $model['name'] }}/{id}</span>
                                    <span class="text-xs bg-red-50 text-red-700 px-2 py-0.5 rounded">DELETE /api/dynamic/{{ $model['name'] }}/{id}</span>
                                @else
                                    <span class="text-xs text-gray-400">API disabled</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                            No dynamic models created yet.
                            @if (\Illuminate\Support\Facades\Route::has('admin.schema-studio.index'))
                                <a href="{{ route('admin.schema-studio.index') }}" class="text-blue-600 hover:underline ml-1">Create one in Schema Studio</a>
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
