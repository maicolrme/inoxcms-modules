<div>
    @include('api::partials.api-tabs')

    <div class="mb-6">
        <h2 class="text-2xl font-semibold text-gray-900">API Documentation</h2>
        <p class="text-gray-600 mt-1">RESTful API reference — all endpoints require authentication via Bearer token.</p>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-2">Authentication</h3>
            <p class="text-sm text-gray-600 mb-3">Include your token in the <code class="bg-gray-100 px-1 rounded">Authorization</code> header:</p>
            <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg text-sm overflow-x-auto">Authorization: Bearer YOUR_TOKEN_HERE</pre>
        </div>

        @php
            $endpoints = [
                ['GET', '/api/posts', 'List all posts (paginated)', '?status=published&type=post&search=...&sort=created_at&direction=desc&per_page=15'],
                ['GET', '/api/posts/{id}', 'Get a single post', ''],
                ['POST', '/api/posts', 'Create a new post', '{ "title": "...", "slug": "...", "content": "...", "status": "draft", "type": "post", "categories": [1], "tags": [1] }'],
                ['PUT', '/api/posts/{id}', 'Update a post', '{ "title": "..." }'],
                ['DELETE', '/api/posts/{id}', 'Delete a post (soft)', ''],
                ['GET', '/api/categories', 'List all categories', ''],
                ['GET', '/api/categories/{id}', 'Get a single category', ''],
                ['POST', '/api/categories', 'Create a category', '{ "name": "...", "slug": "..." }'],
                ['PUT', '/api/categories/{id}', 'Update a category', '{ "name": "..." }'],
                ['DELETE', '/api/categories/{id}', 'Delete a category', ''],
                ['GET', '/api/tags', 'List all tags', ''],
                ['GET', '/api/tags/{id}', 'Get a single tag', ''],
                ['POST', '/api/tags', 'Create a tag', '{ "name": "...", "slug": "..." }'],
                ['PUT', '/api/tags/{id}', 'Update a tag', '{ "name": "..." }'],
                ['DELETE', '/api/tags/{id}', 'Delete a tag', ''],
                ['GET', '/api/tokens', 'List your tokens', ''],
                ['POST', '/api/tokens', 'Create a token', '{ "name": "..." }'],
                ['DELETE', '/api/tokens/{id}', 'Revoke a token', ''],
            ];
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-200 text-left text-sm font-medium text-gray-500">
                        <th class="px-6 py-3 w-20">Method</th>
                        <th class="px-6 py-3">Endpoint</th>
                        <th class="px-6 py-3">Description</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 font-mono text-sm">
                    @foreach ($endpoints as [$method, $path, $desc])
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-3">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium
                                    {{ $method === 'GET' ? 'bg-green-100 text-green-700' : '' }}
                                    {{ $method === 'POST' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $method === 'PUT' ? 'bg-orange-100 text-orange-700' : '' }}
                                    {{ $method === 'DELETE' ? 'bg-red-100 text-red-700' : '' }}">
                                    {{ $method }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-900">{{ $path }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $desc }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
