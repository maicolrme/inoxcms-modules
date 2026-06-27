<div>
    <div class="mb-6 flex items-center justify-between">
        <h2 class="text-2xl font-semibold text-gray-900">Media</h2>
        <div class="flex gap-2">
            <button wire:click="$set('viewMode', 'grid')"
                    class="px-3 py-1.5 rounded-lg text-sm {{ $viewMode === 'grid' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">Grid</button>
            <button wire:click="$set('viewMode', 'list')"
                    class="px-3 py-1.5 rounded-lg text-sm {{ $viewMode === 'list' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">List</button>
        </div>
    </div>

    @if (session('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center"
             x-data="{ dragging: false }"
             x-on:dragover="dragging = true"
             x-on:dragleave="dragging = false"
             x-on:drop="dragging = false"
             :class="{ 'border-blue-400 bg-blue-50': dragging }">
            <label for="upload" class="cursor-pointer">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <p class="mt-2 text-sm text-gray-600">Drop files here or <span class="text-blue-600">browse</span></p>
                <p class="text-xs text-gray-400 mt-1">JPG, PNG, GIF, WebP, SVG, AVIF — max 10MB</p>
                <input id="upload" type="file" wire:model="upload" class="hidden">
            </label>
            <div wire:loading wire:target="upload" class="mt-4">
                <div class="animate-pulse flex items-center justify-center gap-2 text-blue-600">
                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Uploading...
                </div>
            </div>
            @error('upload') <p class="text-red-500 text-sm mt-2">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="mb-4">
        <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search files..."
               class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
    </div>

    @if ($viewMode === 'grid')
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse ($files as $media)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden group">
                    <div class="aspect-square bg-gray-100 relative overflow-hidden">
                        @if ($media->isImage())
                            <img src="{{ $media->url() }}" alt="{{ $media->alt_text ?? $media->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <div class="flex items-center justify-center h-full text-gray-400">
                                <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                        <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 transition-all flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                            <button wire:click="startEdit({{ $media->id }})" class="p-1.5 bg-white rounded-full text-gray-700 hover:text-blue-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="delete({{ $media->id }})" wire:confirm="Delete this file?" class="p-1.5 bg-white rounded-full text-gray-700 hover:text-red-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-2">
                        <p class="text-xs text-gray-900 truncate">{{ $media->original_name }}</p>
                        <p class="text-xs text-gray-400">{{ $media->humanSize() }}</p>
                    </div>
                </div>
            @empty
                <p class="col-span-full text-center py-12 text-gray-500">No files yet.</p>
            @endforelse
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-sm font-medium text-gray-500">
                        <th class="px-6 py-3">Preview</th>
                        <th class="px-6 py-3">Name</th>
                        <th class="px-6 py-3">Type</th>
                        <th class="px-6 py-3">Size</th>
                        <th class="px-6 py-3">Disk</th>
                        <th class="px-6 py-3">Uploaded</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse ($files as $media)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                @if ($media->isImage())
                                    <img src="{{ $media->url() }}" alt="" class="w-10 h-10 rounded object-cover">
                                @else
                                    <div class="w-10 h-10 rounded bg-gray-100 flex items-center justify-center text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-sm font-medium text-gray-900 truncate max-w-xs">{{ $media->original_name }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $media->mime_type }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $media->humanSize() }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $media->disk }}</td>
                            <td class="px-6 py-3 text-sm text-gray-600">{{ $media->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-3">
                                <div class="flex gap-2">
                                    <button wire:click="startEdit({{ $media->id }})" class="text-sm text-blue-600 hover:text-blue-800">Edit</button>
                                    <button wire:click="delete({{ $media->id }})" wire:confirm="Delete this file?" class="text-sm text-red-600 hover:text-red-800">Delete</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">No files yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif

    <div class="mt-4">{{ $files->links() }}</div>

    @if ($editingId)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" wire:click.self="cancelEdit">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Media</h3>
                <form wire:submit="saveEdit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alt Text</label>
                        <input type="text" wire:model="editAlt"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Caption</label>
                        <textarea wire:model="editCaption" rows="3"
                                  class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" wire:click="cancelEdit"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
