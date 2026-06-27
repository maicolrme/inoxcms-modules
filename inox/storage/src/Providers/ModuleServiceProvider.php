<?php

namespace Inox\Storage\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Inox\Storage\Livewire\MediaManager;
use Inox\Storage\Livewire\MediaSettings;
use Livewire\Livewire;
use Inox\Storage\Models\Media;

class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/storage.php', 'storage'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'storage');
        $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');

        if (File::exists(__DIR__ . '/../../routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/api.php');
        }

        $this->configureMediaDisk();
        $this->registerLivewireComponents();
        $this->registerNav();
        $this->registerSettingsTab();
    }

    protected function registerNav(): void
    {
        $engine = app('module.engine');
        if (!$engine) return;

        $engine->registerNav('storage', [
            ['label' => 'Storage', 'route' => 'admin.storage.index', 'active' => 'admin.storage.*'],
        ]);
    }

    protected function registerSettingsTab(): void
    {
        $engine = app('module.engine');
        if (!$engine) return;

        $engine->registerSettingsComponent('storage', 'storage-settings', 'Storage', 'storage-settings');
    }

    protected function registerLivewireComponents(): void
    {
        Livewire::component('storage-manager', MediaManager::class);
        Livewire::component('storage-settings', MediaSettings::class);
    }

    protected function configureMediaDisk(): void
    {
        $selected = config('storage.disk', 'local');
        $disks = config('storage.disks', []);
        $filesystem = config('filesystems.disks', []);
        $config = $disks[$selected] ?? [];

        if ($selected === 'local') {
            $root = storage_path('app/public/media');
            if (! File::exists($root)) {
                File::makeDirectory($root, 0755, true);
            }
            $config['root'] = $root;
        }

        $existing = $filesystem[$selected] ?? [];

        config([
            'filesystems.disks.media' => array_merge($existing, $config),
        ]);
    }
}
