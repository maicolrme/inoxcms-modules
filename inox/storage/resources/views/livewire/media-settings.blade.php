<div>
    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">Storage Settings</h2>
        <p class="text-gray-600 mt-1">Configure where media files are stored.</p>
    </div>

    @if (session('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('message') }}</div>
    @endif

    @if (!$envWritable)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-lg mb-4">
            ⚠ .env file is not writable. Settings will be saved but environment variables won't be updated.
        </div>
    @endif

    <form wire:submit="save" class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <label class="block text-sm font-medium text-gray-700 mb-3">Storage Driver</label>
            <div class="grid grid-cols-3 gap-4">
                <label class="relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer
                    {{ $disk === 'local' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" wire:model="disk" value="local" class="sr-only">
                    <svg class="w-8 h-8 mb-2 {{ $disk === 'local' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                    </svg>
                    <span class="text-sm font-medium {{ $disk === 'local' ? 'text-blue-700' : 'text-gray-700' }}">Local</span>
                    <span class="text-xs text-gray-400 mt-1">Server storage</span>
                </label>

                <label class="relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer
                    {{ $disk === 's3' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" wire:model="disk" value="s3" class="sr-only">
                    <svg class="w-8 h-8 mb-2 {{ $disk === 's3' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/>
                    </svg>
                    <span class="text-sm font-medium {{ $disk === 's3' ? 'text-blue-700' : 'text-gray-700' }}">S3</span>
                    <span class="text-xs text-gray-400 mt-1">Amazon S3</span>
                </label>

                <label class="relative flex flex-col items-center p-4 border-2 rounded-lg cursor-pointer
                    {{ $disk === 'r2' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300' }}">
                    <input type="radio" wire:model="disk" value="r2" class="sr-only">
                    <svg class="w-8 h-8 mb-2 {{ $disk === 'r2' ? 'text-blue-600' : 'text-gray-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                    </svg>
                    <span class="text-sm font-medium {{ $disk === 'r2' ? 'text-blue-700' : 'text-gray-700' }}">R2</span>
                    <span class="text-xs text-gray-400 mt-1">Cloudflare R2</span>
                </label>
            </div>
        </div>

        @if ($disk === 's3')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h3 class="font-medium text-gray-900">S3 Configuration</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Access Key ID</label>
                        <input type="text" wire:model="s3_key" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Secret Access Key</label>
                        <input type="password" wire:model="s3_secret" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Region</label>
                        <input type="text" wire:model="s3_region" placeholder="us-east-1" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bucket</label>
                        <input type="text" wire:model="s3_bucket" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700">Endpoint (optional)</label>
                        <input type="text" wire:model="s3_endpoint" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        @endif

        @if ($disk === 'r2')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
                <h3 class="font-medium text-gray-900">Cloudflare R2 Configuration</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Access Key ID</label>
                        <input type="text" wire:model="r2_key" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Secret Access Key</label>
                        <input type="password" wire:model="r2_secret" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Region</label>
                        <input type="text" wire:model="r2_region" placeholder="auto" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Bucket</label>
                        <input type="text" wire:model="r2_bucket" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Public URL</label>
                        <input type="text" wire:model="r2_url" placeholder="https://pub-xxxx.r2.dev" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Endpoint</label>
                        <input type="text" wire:model="r2_endpoint" placeholder="https://xxxx.r2.cloudflarestorage.com" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        @endif

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Save Settings
            </button>
        </div>
    </form>
</div>
