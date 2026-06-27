@php
    $current = request()->route()?->getName() ?? '';
    $tabs = [
        'admin.api.settings'       => ['label' => 'Settings',        'route' => 'admin.api.settings'],
        'admin.api.tokens'         => ['label' => 'Tokens',          'route' => 'admin.api.tokens'],
        'admin.api.routes'         => ['label' => 'Routes',          'route' => 'admin.api.routes'],
        'admin.api.dynamic-models' => ['label' => 'Dynamic Models',  'route' => 'admin.api.dynamic-models'],
        'admin.api.log'            => ['label' => 'API Log',         'route' => 'admin.api.log'],
    ];
@endphp

<div class="mb-6">
    <nav class="flex gap-1 border-b border-gray-200">
        @foreach ($tabs as $name => $tab)
            @if (\Illuminate\Support\Facades\Route::has($tab['route']))
                <a href="{{ route($tab['route']) }}"
                   class="px-4 py-2.5 text-sm font-medium rounded-t-lg -mb-px border border-transparent
                   {{ $current === $name ? 'text-blue-700 bg-white border-gray-200 border-b-white' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ $tab['label'] }}
                </a>
            @endif
        @endforeach
    </nav>
</div>
