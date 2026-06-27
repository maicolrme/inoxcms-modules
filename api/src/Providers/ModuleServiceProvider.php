<?php

namespace Inox\Api\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Inox\Api\Livewire\ApiTokens;
use Inox\Api\Livewire\ApiSettings;
use Inox\Api\Livewire\ApiLog;
use Inox\Api\Livewire\ApiDynamicModels;
use Inox\Api\Livewire\RouteManager;
use Inox\Api\Http\Middleware\ApiLogger;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/api.php', 'inox.api');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'api');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        if (file_exists(__DIR__ . '/../../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }

        if (class_exists('\Livewire\Livewire')) {
            Livewire::component('api-tokens', ApiTokens::class);
            Livewire::component('api-settings', ApiSettings::class);
            Livewire::component('api-log', ApiLog::class);
            Livewire::component('api-dynamic-models', ApiDynamicModels::class);
            Livewire::component('api-route-manager', RouteManager::class);
        }

        $this->registerMiddleware();
        $this->registerNav();
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('api.logger', ApiLogger::class);
    }

    protected function registerNav(): void
    {
        $engine = app('module.engine');
        if (!$engine) return;

        $engine->registerNav('api', [
            ['label' => 'API', 'route' => 'admin.api.settings', 'active' => 'admin.api.*'],
        ]);
    }
}
