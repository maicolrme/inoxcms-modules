<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Schema Studio</h2>
            <p class="text-gray-600">Design data models visually. {{ $modelCount }} model(s) defined.</p>
        </div>
        <div class="flex gap-2">
            <button wire:click="exportOpenApi" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Export OpenAPI</button>
            <button wire:click="exportTypeScript" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Export TypeScript</button>
            <button wire:click="regenerateAll" class="px-4 py-2 text-sm bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors">Regenerate All</button>
            <button wire:click="toggleCreateForm" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                {{ $showCreateForm ? 'Cancel' : 'New Model' }}
            </button>
        </div>
    </div>

    @if ($showCreateForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="font-medium text-gray-900 mb-4">Create New Model</h3>
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Model Name *</label>
                    <input type="text" wire:model="newModelName" placeholder="e.g. Product" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('newModelName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Table Name</label>
                    <input type="text" wire:model="newModelTable" placeholder="auto-generated" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" wire:model="newModelDescription" placeholder="Optional description" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="create" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Create Model</button>
            </div>
        </div>
    @endif

    @if ($schemas)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($schemas as $s)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-gray-900 text-lg">{{ $s['name'] }}</h3>
                            <p class="text-sm text-gray-500">table: {{ $s['table'] }}</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded-full">{{ count($s['fields']) }} fields</span>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">{{ $s['description'] ?: 'No description' }}</p>
                    <div class="flex flex-wrap gap-1 mb-4">
                        @if ($s['timestamps'] ?? true)
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">timestamps</span>
                        @endif
                        @if ($s['soft_deletes'] ?? false)
                            <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">soft-deletes</span>
                        @endif
                        @if ($s['relations'] ?? [])
                            <span class="text-xs bg-purple-100 text-purple-600 px-2 py-0.5 rounded">{{ count($s['relations']) }} relations</span>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.schema-studio.designer', $s['name']) }}" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Design</a>
                        <button wire:click="generate('{{ $s['name'] }}')" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">Generate</button>
                        <a href="{{ route('admin.schema-studio.data', $s['name']) }}" class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Data</a>
                        <button wire:click="delete('{{ $s['name'] }}')" wire:confirm="Delete this model?" class="px-3 py-1.5 text-sm bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors">Delete</button>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <p class="text-gray-500 text-lg mb-2">No models defined yet.</p>
            <p class="text-gray-400 text-sm">Click "New Model" to create your first data model.</p>
        </div>
    @endif

    @if ($generationLog)
        <div class="mt-6 bg-gray-900 text-green-400 rounded-xl p-4 font-mono text-xs overflow-x-auto">
            <pre>{{ $generationLog }}</pre>
        </div>
    @endif
</div>
