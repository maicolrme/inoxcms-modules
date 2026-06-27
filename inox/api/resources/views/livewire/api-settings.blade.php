<div>
    @include('api::partials.api-tabs')

    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">API Settings</h2>
        <p class="text-gray-600 mt-1">Configure authentication, rate limiting, and logging.</p>
    </div>

    @if (!$envWritable)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-4">
            .env file is not writable. Settings will apply for this session only.
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-medium text-gray-900 mb-4">Authentication</h3>
            <div class="space-y-3">
                <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $authType === 'sanctum' ? 'border-blue-500 bg-blue-50' : '' }}">
                    <input type="radio" wire:model="authType" value="sanctum" class="text-blue-600">
                    <div>
                        <p class="font-medium text-gray-900">Sanctum (Tokens)</p>
                        <p class="text-sm text-gray-500">Personal access tokens via Laravel Sanctum. Recommended for most projects.</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $authType === 'none' ? 'border-blue-500 bg-blue-50' : '' }}">
                    <input type="radio" wire:model="authType" value="none" class="text-blue-600">
                    <div>
                        <p class="font-medium text-gray-900">None (Public)</p>
                        <p class="text-sm text-gray-500">No authentication required. Use only for development or public APIs.</p>
                    </div>
                </label>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-medium text-gray-900 mb-4">Rate Limiting</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700">Max requests per minute</label>
                <input type="number" wire:model="rateLimit" min="1" max="10000"
                       class="mt-1 block w-full max-w-xs rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('rateLimit') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="font-medium text-gray-900 mb-4">Logging</h3>
            <label class="flex items-center gap-3 cursor-pointer">
                <button type="button" wire:click="$toggle('logEnabled')"
                        class="relative w-11 h-6 rounded-full transition-colors {{ $logEnabled ? 'bg-blue-600' : 'bg-gray-300' }}">
                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform {{ $logEnabled ? 'translate-x-5' : '' }}"></span>
                </button>
                <div>
                    <p class="font-medium text-gray-900">Log API calls</p>
                    <p class="text-sm text-gray-500">Record every API request and response for debugging and monitoring.</p>
                </div>
            </label>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Save Settings
            </button>
        </div>
    </form>

    <div x-data="{ show: false, msg: '' }"
         x-on:notify.window="msg = $event.detail.message; show = true; setTimeout(() => show = false, 2000)"
         x-show="show"
         x-transition.duration.300ms
         class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded-lg shadow-lg text-sm z-50">
        <span x-text="msg"></span>
    </div>
</div>
