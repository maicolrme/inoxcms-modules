<?php

namespace Inox\Api\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Inox\SchemaStudio\Services\SchemaService;

#[Layout('layouts.admin')]
class ApiDynamicModels extends Component
{
    protected SchemaService $schemas;

    public function boot(): void
    {
        $this->schemas = app(SchemaService::class);
    }

    public function toggleApi(string $name): void
    {
        $schema = $this->schemas->find($name);
        if (!$schema) return;

        $routes = $schema['routes'] ?? ['api' => true, 'admin' => true];
        $routes['api'] = !($routes['api'] ?? true);

        $this->schemas->update($name, ['routes' => $routes]);
    }

    public function toggleAdmin(string $name): void
    {
        $schema = $this->schemas->find($name);
        if (!$schema) return;

        $routes = $schema['routes'] ?? ['api' => true, 'admin' => true];
        $routes['admin'] = !($routes['admin'] ?? true);

        $this->schemas->update($name, ['routes' => $routes]);
    }

    public function render()
    {
        $models = $this->schemas->all();

        return view('api::livewire.api-dynamic-models', [
            'models' => $models,
        ]);
    }
}
