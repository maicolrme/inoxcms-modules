<div>
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-4">{{ session('error') }}</div>
    @endif

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Design: {{ $schemaName }}</h2>
            <p class="text-gray-600">{{ $description ?: 'No description' }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.schema-studio.index') }}" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">&larr; Back to Models</a>
            <button wire:click="save" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Save</button>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Model Settings --}}
        <div class="lg:col-span-1 space-y-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="font-medium text-gray-900 mb-3">Model Settings</h3>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Table Name</label>
                        <input type="text" wire:model.live="table" placeholder="auto" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea wire:model.live="description" rows="2" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"></textarea>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:click="toggleTimestamps" {{ $timestamps ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Timestamps</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:click="toggleSoftDeletes" {{ $softDeletes ? 'checked' : '' }} class="rounded border-gray-300">
                        <span class="text-sm text-gray-700">Soft Deletes</span>
                    </label>
                </div>
            </div>

            {{-- Relations --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="font-medium text-gray-900 mb-3">Relationships</h3>
                @if ($relations)
                    <div class="space-y-2 mb-3">
                        @foreach ($relations as $i => $rel)
                            <div class="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2 text-sm">
                                <span><span class="text-purple-600 font-medium">{{ $rel['type'] }}</span> {{ $rel['model'] }}</span>
                                <button wire:click="removeRelation({{ $i }})" class="text-red-500 hover:text-red-700">&times;</button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="space-y-2">
                    <select wire:model="relationType" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="belongsTo">Belongs To</option>
                        <option value="hasMany">Has Many</option>
                        <option value="belongsToMany">Belongs To Many</option>
                    </select>
                    <select wire:model="relationModel" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        <option value="">Target model...</option>
                        @foreach ($availableModels as $m)
                            <option value="{{ $m }}">{{ $m }}</option>
                        @endforeach
                    </select>
                    <input type="text" wire:model="relationForeignKey" placeholder="foreign_key" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    <button wire:click="addRelation" class="w-full px-3 py-1.5 text-sm bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">Add Relation</button>
                </div>
            </div>
        </div>

        {{-- Fields Designer --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Add / Edit Field --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="font-medium text-gray-900 mb-3">{{ $editFieldIndex !== '' ? 'Edit Field' : 'Add Field' }}</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Field Name *</label>
                        <input type="text" wire:model="fieldName" placeholder="e.g. title" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        @error('fieldName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Type *</label>
                        <select wire:model.live="fieldType" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            @foreach ($fieldTypes as $key => $config)
                                <option value="{{ $key }}">{{ $config['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Label</label>
                        <input type="text" wire:model="fieldLabel" placeholder="Auto from name" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Default</label>
                        <input type="text" wire:model="fieldDefault" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                </div>

                @if (in_array($fieldType, ['select', 'multi-select', 'status']))
                    <div class="mt-3">
                        <label class="block text-xs font-medium text-gray-700">Options (comma separated)</label>
                        <input type="text" wire:model="fieldOptions" placeholder="option1,option2,option3" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                    </div>
                @endif

                @if (in_array($fieldType, ['relation:belongsTo', 'relation:hasMany', 'relation:belongsToMany']))
                    <div class="grid grid-cols-3 gap-3 mt-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Target Model</label>
                            <select wire:model="fieldTargetModel" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="">Select...</option>
                                @foreach ($availableModels as $m)
                                    <option value="{{ $m }}">{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Foreign Key</label>
                            <input type="text" wire:model="fieldForeignKey" placeholder="auto" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">On Delete</label>
                            <select wire:model="fieldOnDelete" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <option value="cascade">Cascade</option>
                                <option value="restrict">Restrict</option>
                                <option value="set null">Set Null</option>
                            </select>
                        </div>
                    </div>
                @endif

                <div class="mt-3 flex items-center gap-4">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="fieldRequired" class="rounded border-gray-300">
                        <span class="text-xs text-gray-700">Required</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" wire:model="fieldUnique" class="rounded border-gray-300">
                        <span class="text-xs text-gray-700">Unique</span>
                    </label>
                    <div class="ml-auto flex gap-2">
                        @if ($editFieldIndex !== '')
                            <button wire:click="updateField" class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Update</button>
                            <button wire:click="resetFieldForm" class="px-4 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">Cancel</button>
                        @else
                            <button wire:click="addField" class="px-4 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">Add Field</button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Fields List --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-4 py-3 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Fields ({{ count($fields) }})</h3>
                </div>
                @if ($fields)
                    <div class="divide-y divide-gray-100" x-data="{}">
                        @foreach ($fields as $i => $field)
                            <div class="px-4 py-3 flex items-center justify-between hover:bg-gray-50 transition-colors"
                                 x-sortable-item="{{ $i }}">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-gray-600 cursor-move" x-sortable-handle>&#x2630;</span>
                                        <span class="font-medium text-gray-900">{{ $field['name'] }}</span>
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">{{ $field['type'] }}</span>
                                        @if (($field['auto_increment'] ?? false) || $field['type'] === 'id')
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">auto-increment</span>
                                        @endif
                                        @if ($field['required'] ?? false)
                                            <span class="text-xs text-red-500">*</span>
                                        @endif
                                        @if ($field['unique'] ?? false)
                                            <span class="text-xs text-amber-600">unique</span>
                                        @endif
                                        @if (isset($field['options']) && is_array($field['options']))
                                            <span class="text-xs text-purple-600">{{ implode(', ', $field['options']) }}</span>
                                        @endif
                                        @if (isset($field['target_model']))
                                            <span class="text-xs text-blue-600">→ {{ $field['target_model'] }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button wire:click="moveFieldUp({{ $i }})" class="p-1 text-gray-400 hover:text-gray-700" title="Move up">&uarr;</button>
                                    <button wire:click="moveFieldDown({{ $i }})" class="p-1 text-gray-400 hover:text-gray-700" title="Move down">&darr;</button>
                                    <button wire:click="editField({{ $i }})" class="p-1 text-blue-500 hover:text-blue-700" title="Edit">&#9998;</button>
                                    <button wire:click="removeField({{ $i }})" class="p-1 text-red-500 hover:text-red-700" title="Remove">&times;</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-gray-400">
                        <p>No fields defined. Add fields above.</p>
                    </div>
                @endif
            </div>

            {{-- Relationship Canvas (Alpine) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                <h3 class="font-medium text-gray-900 mb-3">Relationship Canvas</h3>
                <div class="relative overflow-hidden border border-dashed border-gray-300 rounded-lg" style="min-height: 300px"
                     x-data="{
                        models: {{ json_encode($availableModels) }},
                        relations: {{ json_encode($relations) }},
                        cards: [],
                        init() {
                            this.renderCanvas();
                        },
                        renderCanvas() {
                            // Cards positioned by Alpine
                        }
                     }">
                    <div class="p-4 text-center text-gray-400 text-sm">
                        <p>Models appear as draggable cards. Lines show relationships.</p>
                        <p class="mt-1">Use the <strong>Relationships</strong> panel on the left to define connections.</p>
                    </div>
                    <svg class="absolute inset-0 pointer-events-none w-full h-full"></svg>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{ show: false, msg: '' }"
         x-on:notify.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2000)"
         x-show="show"
         x-transition.duration.300ms
         class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm z-50">
        <span x-text="msg"></span>
    </div>
</div>
