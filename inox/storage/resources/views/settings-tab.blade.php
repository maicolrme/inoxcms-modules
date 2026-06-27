<div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
    <div class="flex items-center justify-between">
        <h3 class="font-medium text-gray-900">Storage Configuration</h3>
        <a href="{{ route('admin.storage.settings') }}" wire:navigate
           class="text-sm text-blue-600 hover:text-blue-800">Full settings →</a>
    </div>
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <span class="text-gray-500">Active Disk</span>
            <p class="font-medium text-gray-900 mt-0.5">{{ strtoupper($disk) }}</p>
        </div>
        <div>
            <span class="text-gray-500">Driver</span>
            <p class="font-medium text-gray-900 mt-0.5">{{ $current['driver'] ?? '-' }}</p>
        </div>
        @if ($disk === 's3' || $disk === 'r2')
            <div>
                <span class="text-gray-500">Bucket</span>
                <p class="font-medium text-gray-900 mt-0.5">{{ $current['bucket'] ?? '-' }}</p>
            </div>
            <div>
                <span class="text-gray-500">Region</span>
                <p class="font-medium text-gray-900 mt-0.5">{{ $current['region'] ?? '-' }}</p>
            </div>
        @endif
        @if ($disk === 'local')
            <div class="col-span-2">
                <span class="text-gray-500">Root Path</span>
                <p class="font-medium text-gray-900 mt-0.5 break-all">{{ $current['root'] ?? storage_path('app/public/storage') }}</p>
            </div>
        @endif
    </div>
</div>
