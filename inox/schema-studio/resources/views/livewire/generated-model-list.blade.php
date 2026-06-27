<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">{{ $schema['name'] }} Data</h2>
            <p class="text-gray-600">Table: {{ $schema['table'] }} &middot; {{ count($fields) }} fields</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.schema-studio.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">&larr; Back</a>
            <a href="{{ route('admin.schema-studio.designer', $modelName) }}" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Edit Schema</a>
            <button wire:click="toggleCreateForm" class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                {{ $showCreateForm ? 'Cancel' : 'New Record' }}
            </button>
        </div>
    </div>

    {{-- Create Form --}}
    @if ($showCreateForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="font-medium text-gray-900 mb-4">New {{ $schema['name'] }}</h3>
            <div class="grid grid-cols-2 gap-4">
                @foreach ($fields as $field)
                    @if ($field['type'] === 'id' && ($field['auto_increment'] ?? true))
                        @continue
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ $field['label'] ?? $field['name'] }}</label>
                        @if (in_array($field['type'], ['text', 'email', 'url', 'slug', 'phone', 'color', 'password']))
                            <input type="{{ $field['type'] === 'password' ? 'password' : ($field['type'] === 'color' ? 'color' : 'text') }}"
                                   wire:model="createData.{{ $field['name'] }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @elseif ($field['type'] === 'longtext')
                            <textarea wire:model="createData.{{ $field['name'] }}" rows="3"
                                      class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                        @elseif ($field['type'] === 'boolean')
                            <label class="flex items-center gap-2 mt-2">
                                <input type="checkbox" wire:model="createData.{{ $field['name'] }}" class="rounded border-gray-300">
                                <span class="text-sm text-gray-500">{{ $field['label'] ?? $field['name'] }}</span>
                            </label>
                        @elseif (in_array($field['type'], ['select', 'status']))
                            <select wire:model="createData.{{ $field['name'] }}"
                                    class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Select...</option>
                                @foreach (($field['options'] ?? []) as $opt)
                                    <option value="{{ $opt }}">{{ $opt }}</option>
                                @endforeach
                            </select>
                        @elseif ($field['type'] === 'integer')
                            <input type="number" wire:model="createData.{{ $field['name'] }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @elseif (in_array($field['type'], ['decimal', 'float']))
                            <input type="number" step="any" wire:model="createData.{{ $field['name'] }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @elseif (in_array($field['type'], ['date', 'datetime']))
                            <input type="{{ $field['type'] === 'date' ? 'date' : 'datetime-local' }}"
                                   wire:model="createData.{{ $field['name'] }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @else
                            <input type="text" wire:model="createData.{{ $field['name'] }}"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @endif
                        @error("createData.{$field['name']}") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="createRecord" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Create</button>
            </div>
        </div>
    @endif

    {{-- Search & Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="p-4 border-b border-gray-200">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search..."
                   class="block w-full max-w-sm rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b bg-gray-50">
                        <th class="text-left px-4 py-3 font-medium text-gray-600 cursor-pointer" wire:click="sortBy('id')">
                            ID @if ($sortField === 'id') {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                        </th>
                        @foreach ($fields as $field)
                            @if ($field['type'] === 'id' && ($field['auto_increment'] ?? true)) @continue @endif
                            <th class="text-left px-4 py-3 font-medium text-gray-600 cursor-pointer" wire:click="sortBy('{{ $field['name'] }}')">
                                {{ $field['label'] ?? $field['name'] }}
                                @if ($sortField === $field['name']) {{ $sortDirection === 'asc' ? '↑' : '↓' }} @endif
                            </th>
                        @endforeach
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($records as $record)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-gray-500">{{ $record->id }}</td>
                            @foreach ($fields as $field)
                                @if ($field['type'] === 'id' && ($field['auto_increment'] ?? true)) @continue @endif
                                <td class="px-4 py-3">
                                    @php $val = $record->{$field['name']} ?? ''; @endphp
                                    @if ($field['type'] === 'boolean')
                                        <span class="{{ $val ? 'text-green-600' : 'text-gray-400' }}">{{ $val ? 'Yes' : 'No' }}</span>
                                    @elseif ($field['type'] === 'color' && $val)
                                        <span class="inline-block w-5 h-5 rounded border" style="background: {{ $val }}"></span>
                                    @elseif ($field['type'] === 'longtext' || $field['type'] === 'code')
                                        {{ Str::limit(strip_tags((string) $val), 60) }}
                                    @elseif (in_array($field['type'], ['select', 'status']))
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full
                                            {{ $val === 'published' ? 'bg-green-100 text-green-700' : ($val === 'draft' ? 'bg-yellow-100 text-yellow-700' : ($val === 'archived' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600')) }}">
                                            {{ $val }}
                                        </span>
                                    @elseif ($field['type'] === 'json')
                                        <span class="text-gray-400">{{ is_array($val) ? json_encode($val) : $val }}</span>
                                    @else
                                        {{ Str::limit((string) $val, 80) }}
                                    @endif
                                </td>
                            @endforeach
                            <td class="px-4 py-3 text-right">
                                <button wire:click="deleteRecord({{ $record->id }})" wire:confirm="Delete this record?"
                                        class="text-red-600 hover:text-red-800 text-xs font-medium">Delete</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($fields) + 2 }}" class="px-4 py-12 text-center text-gray-400">
                                No records yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $records->links() }}
            </div>
        @endif
    </div>
</div>
