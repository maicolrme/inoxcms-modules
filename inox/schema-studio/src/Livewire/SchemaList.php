<?php

namespace Inox\SchemaStudio\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Inox\SchemaStudio\Services\SchemaService;
use Inox\SchemaStudio\Services\MigrationGenerator;
use Inox\SchemaStudio\Services\ModelFileGenerator;
use Inox\SchemaStudio\Services\PolicyGenerator;
use Inox\SchemaStudio\Services\OpenApiGenerator;
use Inox\SchemaStudio\Services\TypeScriptGenerator;
use Illuminate\Support\Facades\Artisan;

#[Layout('layouts.admin')]
class SchemaList extends Component
{
    public string $newModelName = '';

    public string $newModelTable = '';

    public string $newModelDescription = '';

    public bool $showCreateForm = false;

    public string $generationLog = '';

    protected SchemaService $schemas;

    public function boot(): void
    {
        $this->schemas = app(SchemaService::class);
    }

    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        $this->reset(['newModelName', 'newModelTable', 'newModelDescription']);
    }

    public function create(): void
    {
        $this->validate([
            'newModelName' => 'required|alpha_dash|max:100',
            'newModelTable' => 'nullable|alpha_dash|max:100',
        ]);

        $data = ['name' => $this->newModelName];
        if ($this->newModelTable) $data['table'] = $this->newModelTable;
        if ($this->newModelDescription) $data['description'] = $this->newModelDescription;

        $this->schemas->create($data);

        $this->showCreateForm = false;
        $this->reset(['newModelName', 'newModelTable', 'newModelDescription']);
        $this->dispatch('schema-created');
    }

    public function delete(string $name): void
    {
        $this->schemas->delete($name);
        $this->dispatch('schema-deleted');
    }

    public function generate(string $name): void
    {
        $schema = $this->schemas->find($name);
        if (!$schema) {
            $this->generationLog = 'Error: Schema not found.';
            return;
        }

        try {
            $app = app();
            $migGen = $app->make(MigrationGenerator::class);
            $modelGen = $app->make(ModelFileGenerator::class);
            $policyGen = $app->make(PolicyGenerator::class);

            $migGen->removeOldMigrations($schema['table']);
            $migPath = $migGen->generate($schema);
            $modelPath = $modelGen->generate($schema);
            $policyPath = $policyGen->generate($schema);

            Artisan::call('migrate', ['--force' => true]);

            $this->generationLog = "Migration: {$migPath}\nModel: {$modelPath}\nPolicy: {$policyPath}\nMigration ran successfully.";
            $this->dispatch('generation-complete');
        } catch (\Exception $e) {
            $this->generationLog = 'Error: ' . $e->getMessage();
        }
    }

    public function regenerateAll(): void
    {
        $schemas = app(SchemaService::class)->all();
        if (empty($schemas)) {
            $this->generationLog = 'No schemas to generate.';
            return;
        }

        $app = app();
        $migGen = $app->make(MigrationGenerator::class);
        $modelGen = $app->make(ModelFileGenerator::class);
        $policyGen = $app->make(PolicyGenerator::class);

        $log = '';
        foreach ($schemas as $schema) {
            $log .= "Generating {$schema['name']}...\n";
            try {
                $migGen->removeOldMigrations($schema['table']);
                $migPath = $migGen->generate($schema);
                $modelPath = $modelGen->generate($schema);
                $policyPath = $policyGen->generate($schema);
                $log .= "  Migration: {$migPath}\n  Model: {$modelPath}\n  Policy: {$policyPath}\n";
            } catch (\Exception $e) {
                $log .= "  Error: {$e->getMessage()}\n";
            }
        }

        Artisan::call('migrate', ['--force' => true]);
        $log .= 'All migrations ran.';
        $this->generationLog = $log;
    }

    public function exportOpenApi(): void
    {
        $schemas = app(SchemaService::class)->all();
        $gen = app(OpenApiGenerator::class);
        $json = $gen->generate($schemas);
        $path = storage_path('app/openapi.json');
        file_put_contents($path, $json);
        $this->generationLog = "OpenAPI spec exported to: {$path}";
    }

    public function exportTypeScript(): void
    {
        $schemas = app(SchemaService::class)->all();
        $gen = app(TypeScriptGenerator::class);
        $types = $gen->generate($schemas);
        $path = storage_path('app/types.d.ts');
        file_put_contents($path, $types);
        $this->generationLog = "TypeScript types exported to: {$path}";
    }

    public function render()
    {
        $schemas = app(SchemaService::class)->all();
        return view('schema-studio::livewire.schema-list', [
            'schemas' => $schemas,
            'modelCount' => count($schemas),
        ]);
    }
}
