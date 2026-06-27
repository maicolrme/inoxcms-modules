<?php

namespace Inox\Seo\Providers;

use App\Core\HookSystem\Hook;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/seo.php',
            'inox.seo'
        );
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'inox-seo');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        }

        Livewire::component('seo-settings', \Inox\Seo\Livewire\SeoSettings::class);

        Hook::filter('head.meta', function (array $meta, $content = null) {
            $meta[] = view('inox-seo::meta', compact('content'))->render();
            return $meta;
        });

        Hook::action('content.saved', function ($content) {
            logger()->info('SEO: content saved, sitemap may need update', [
                'id' => $content->id ?? null,
            ]);
        });
    }
}
