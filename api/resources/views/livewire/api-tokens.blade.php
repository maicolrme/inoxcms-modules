<div>
    @include('api::partials.api-tabs')

    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">API Tokens</h2>
        <p class="text-gray-600 mt-1">Manage personal access tokens for API authentication.</p>
    </div>

    @if (session('message'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="font-medium text-gray-900 mb-4">Create New Token</h3>
        <form wire:submit="createToken" class="flex gap-4 items-end">
            <div class="flex-1">
                <input type="text" wire:model="newTokenName" placeholder="Token name (e.g. My App)"
                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                @error('newTokenName') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Generate</button>
        </form>

        @if ($newToken)
            <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-sm font-medium text-yellow-800">Copy this token now — it won't be shown again.</p>
                <code class="mt-2 block bg-yellow-100 px-3 py-2 rounded text-sm break-all">{{ $newToken }}</code>
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="border-b border-gray-200 text-left text-sm font-medium text-gray-500">
                    <th class="px-6 py-3">Name</th>
                    <th class="px-6 py-3">Last used</th>
                    <th class="px-6 py-3">Created</th>
                    <th class="px-6 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($tokens as $token)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $token->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $token->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4">
                            <button wire:click="revokeToken({{ $token->id }})" wire:confirm="Revoke this token?"
                                    class="text-sm text-red-600 hover:text-red-800">Revoke</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">No tokens yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
