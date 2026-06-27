<?php

namespace Inox\Api\Livewire;

use App\Core\SettingRegistry\SettingRegistry;
use Illuminate\Support\Facades\Route;
use Inox\SchemaStudio\Services\SchemaService;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class RouteManager extends Component
{
    public string $search = '';

    public string $methodFilter = '';

    public array $groups = [];

    public array $overrides = [];

    protected SettingRegistry $settings;

    protected ?SchemaService $schemas = null;

    public function boot(SettingRegistry $settings): void
    {
        $this->settings = $settings;

        if (class_exists(SchemaService::class)) {
            try {
                $this->schemas = app(SchemaService::class);
            } catch (\Throwable $e) {
                $this->schemas = null;
            }
        }
    }

    public function mount(): void
    {
        $this->loadOverrides();
        $this->loadRoutes();
    }

    protected function loadOverrides(): void
    {
        $all = Route::getRoutes();

        $prefix = 'api.route.';
        foreach ($all as $route) {
            $uri = $route->uri();
            if (!str_starts_with($uri, 'api/')) continue;

            $methods = array_values(array_filter(
                $route->methods(),
                fn($m) => $m !== 'HEAD'
            ));
            if (empty($methods)) continue;

            foreach ($methods as $method) {
                $routeKey = $method . ' ' . $uri;
                $key = $prefix . $method . '.' . str_replace('/', '.', $uri);
                $val = $this->settings->get($key);
                if ($val !== null) {
                    $this->overrides[$routeKey] = $val === '1' || $val === true;
                }
            }
        }
    }

    protected function loadRoutes(): void
    {
        $all = Route::getRoutes();
        $dynamicRoutes = [];
        $systemRoutes = [];
        $seen = [];

        // Collect dynamic model schemas for grouping
        $schemas = [];
        if ($this->schemas) {
            $schemas = $this->schemas->all();
        }

        foreach ($all as $route) {
            $uri = $route->uri();
            if (!str_starts_with($uri, 'api/')) continue;

            $methods = array_values(array_filter(
                $route->methods(),
                fn($m) => $m !== 'HEAD'
            ));
            if (empty($methods)) continue;

            $method = $methods[0];
            $routeKey = $method . ' ' . $uri;
            if (isset($seen[$routeKey])) continue;
            $seen[$routeKey] = true;

            $middleware = $route->middleware();
            $requiresAuth = in_array('auth:sanctum', $middleware) || in_array('auth', $middleware);

            $entry = [
                'method' => $method,
                'uri' => $uri,
                'name' => $route->getName(),
                'auth' => $requiresAuth,
                'middleware' => $middleware,
            ];

            // Check if this is a dynamic model route
            if (preg_match('#^api/dynamic/(\{model\}|[^/]+)#', $uri, $m)) {
                $dynamicRoutes[] = $entry;
            } else {
                $systemRoutes[] = $entry;
            }
        }

        // Group dynamic routes by model
        $dynamicGroups = [];
        foreach ($schemas as $schema) {
            $modelName = $schema['name'];
            $apiEnabled = $schema['routes']['api'] ?? true;
            $routes = [];

            $patternMethods = [
                'GET' => '/api/dynamic/' . $modelName,
                'GET' => '/api/dynamic/' . $modelName . '/{id}',
                'POST' => '/api/dynamic/' . $modelName,
                'PUT' => '/api/dynamic/' . $modelName . '/{id}',
                'DELETE' => '/api/dynamic/' . $modelName . '/{id}',
            ];

            // We need to handle duplicate patternMethods keys, use separate entries
            $routePatterns = [
                ['method' => 'GET', 'uri' => 'api/dynamic/' . $modelName, 'action' => 'index'],
                ['method' => 'POST', 'uri' => 'api/dynamic/' . $modelName, 'action' => 'store'],
                ['method' => 'GET', 'uri' => 'api/dynamic/' . $modelName . '/{id}', 'action' => 'show'],
                ['method' => 'PUT', 'uri' => 'api/dynamic/' . $modelName . '/{id}', 'action' => 'update'],
                ['method' => 'DELETE', 'uri' => 'api/dynamic/' . $modelName . '/{id}', 'action' => 'destroy'],
            ];

            foreach ($routePatterns as $rp) {
                // Check if route exists in dynamic routes
                $matched = false;
                foreach ($dynamicRoutes as $dr) {
                    // Match by stripping the model name from URI
                    $drUri = str_replace('{model}', $modelName, 'api/dynamic/{model}');
                    if ($dr['method'] === $rp['method'] && $dr['uri'] === $rp['uri']) {
                        $matched = true;
                        $routes[] = [
                            'method' => $rp['method'],
                            'uri' => $rp['uri'],
                            'action' => $rp['action'],
                            'enabled' => $this->isRouteEnabled($rp['method'], $rp['uri'], $apiEnabled),
                            'inherited' => !array_key_exists($rp['method'] . ' ' . $rp['uri'], $this->overrides),
                            'auth' => $this->requiresAuth($rp['method'], $rp['uri'], $dynamicRoutes),
                        ];
                        break;
                    }
                }

                if (!$matched) {
                    $routes[] = [
                        'method' => $rp['method'],
                        'uri' => $rp['uri'],
                        'action' => $rp['action'],
                        'enabled' => $this->isRouteEnabled($rp['method'], $rp['uri'], $apiEnabled),
                        'inherited' => !array_key_exists($rp['method'] . ' ' . $rp['uri'], $this->overrides),
                        'auth' => true,
                    ];
                }
            }

            $modelEnabledCount = count(array_filter($routes, fn($r) => $r['enabled']));

            $dynamicGroups[] = [
                'name' => $modelName,
                'table' => $schema['table'] ?? '',
                'api_enabled' => $apiEnabled,
                'routes' => $routes,
                'enabled_count' => $modelEnabledCount,
                'total' => count($routes),
            ];
        }

        // Compute auth for system routes
        $systemProcessed = [];
        foreach ($systemRoutes as $sr) {
            $systemProcessed[] = [
                'method' => $sr['method'],
                'uri' => $sr['uri'],
                'name' => $sr['name'],
                'auth' => $sr['auth'],
                'enabled' => $this->isRouteEnabled($sr['method'], $sr['uri'], true),
                'inherited' => !array_key_exists($sr['method'] . ' ' . $sr['uri'], $this->overrides),
            ];
        }

        $this->groups = [
            'dynamic' => $dynamicGroups,
            'system' => $systemProcessed,
        ];
    }

    protected function isRouteEnabled(string $method, string $uri, bool $default = true): bool
    {
        $routeKey = $method . ' ' . $uri;
        if (array_key_exists($routeKey, $this->overrides)) {
            return $this->overrides[$routeKey];
        }
        return $default;
    }

    protected function requiresAuth(string $method, string $uri, array $dynamicRoutes): bool
    {
        foreach ($dynamicRoutes as $dr) {
            if ($dr['method'] === $method && $dr['uri'] === $uri) {
                return $dr['auth'];
            }
        }
        return true;
    }

    public function toggleRoute(string $method, string $uri): void
    {
        $routeKey = $method . ' ' . $uri;
        $current = $this->isRouteEnabled($method, $uri);
        $new = !$current;

        $prefix = 'api.route.';
        $key = $prefix . $method . '.' . str_replace('/', '.', $uri);

        $this->settings->set($key, $new ? '1' : '0', 'api');
        $this->settings->flushCache();

        $this->overrides[$routeKey] = $new;
        $this->loadRoutes();
    }

    public function toggleModel(string $modelName, bool $enabled): void
    {
        if (!$this->schemas) return;

        $schema = $this->schemas->find($modelName);
        if (!$schema) return;

        $schema['routes']['api'] = $enabled;
        $this->schemas->update($modelName, $schema);

        // Remove per-route overrides for this model
        $prefix = 'api.route.';
        $methods = ['GET', 'POST', 'PUT', 'DELETE'];
        $patterns = [
            'api/dynamic/' . $modelName,
            'api/dynamic/' . $modelName . '/{id}',
        ];
        foreach ($patterns as $uri) {
            foreach ($methods as $method) {
                $key = $prefix . $method . '.' . str_replace('/', '.', $uri);
                $this->settings->set($key, '', 'api');
                unset($this->overrides[$method . ' ' . $uri]);
            }
        }

        $this->settings->flushCache();
        $this->loadRoutes();
    }

    public function updatingSearch(): void
    {
        $this->loadRoutes();
    }

    public function updatingMethodFilter(): void
    {
        $this->loadRoutes();
    }

    public function render()
    {
        $filtered = $this->groups;

        if ($this->search || $this->methodFilter) {
            $filtered['dynamic'] = array_map(function ($group) {
                $group['routes'] = array_values(array_filter($group['routes'], function ($r) {
                    $matchSearch = !$this->search || str_contains(strtolower($r['uri']), strtolower($this->search));
                    $matchMethod = !$this->methodFilter || $r['method'] === $this->methodFilter;
                    return $matchSearch && $matchMethod;
                }));
                return $group;
            }, $filtered['dynamic']);

            $filtered['system'] = array_values(array_filter($filtered['system'], function ($r) {
                $matchSearch = !$this->search || str_contains(strtolower($r['uri']), strtolower($this->search));
                $matchMethod = !$this->methodFilter || $r['method'] === $this->methodFilter;
                return $matchSearch && $matchMethod;
            }));
        }

        $methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        return view('api::livewire.route-manager', [
            'groups' => $filtered,
            'methods' => $methods,
        ]);
    }
}
