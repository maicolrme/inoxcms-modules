<?php

namespace Inox\SchemaStudio\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Inox\SchemaStudio\Livewire\SchemaList;
use Inox\SchemaStudio\Livewire\SchemaDesigner;
use Inox\SchemaStudio\Livewire\GeneratedModelList;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/schema-studio.php', 'schema-studio');
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'schema-studio');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        if (file_exists(__DIR__ . '/../../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }

        $this->registerLivewireComponents();
        $this->registerNav();
    }

    protected function registerLivewireComponents(): void
    {
        if (class_exists('\Livewire\Livewire')) {
            Livewire::component('schema-studio-list', SchemaList::class);
            Livewire::component('schema-studio-designer', SchemaDesigner::class);
            Livewire::component('schema-studio-data', GeneratedModelList::class);
        }
    }

    protected function registerNav(): void
    {
        $engine = app('module.engine');
        if (!$engine) return;

        $engine->registerNav('schema-studio', [
            [
                'label' => 'Schema Studio',
                'route' => 'admin.schema-studio.index',
                'active' => 'admin.schema-studio.*',
            ],
        ]);
    }
}
