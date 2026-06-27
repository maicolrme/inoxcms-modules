<?php

namespace Inox\SchemaStudio\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Inox\SchemaStudio\Models\DynamicModel;
use Inox\SchemaStudio\Services\SchemaService;
use Inox\SchemaStudio\Services\FieldTypeRegistry;

#[Layout('layouts.admin')]
class GeneratedModelList extends Component
{
    use WithPagination;

    public string $modelName = '';

    public array $schema = [];

    public string $search = '';

    public string $sortField = 'id';

    public string $sortDirection = 'desc';

    public array $editing = [];

    public bool $showCreateForm = false;

    public array $createData = [];

    protected SchemaService $schemas;

    protected FieldTypeRegistry $fields;

    public function boot(): void
    {
        $this->schemas = app(SchemaService::class);
        $this->fields = app(FieldTypeRegistry::class);
    }

    public function mount(string $model): void
    {
        $this->modelName = $model;
        $schema = $this->schemas->find($model);

        if (!$schema) {
            abort(404, "Schema '{$model}' not found.");
        }

        if (!($schema['routes']['admin'] ?? true)) {
            abort(404, "Admin data page disabled for this model.");
        }

        $this->schema = $schema;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function deleteRecord(int $id): void
    {
        $table = $this->schema['table'] ?? $this->schemas->nameToTableName($this->modelName);
        DB::table($table)->where('id', $id)->delete();
    }

    public function toggleCreateForm(): void
    {
        $this->showCreateForm = !$this->showCreateForm;
        if ($this->showCreateForm) {
            $this->createData = [];
            foreach ($this->schema['fields'] as $field) {
                if ($field['type'] === 'id' && ($field['auto_increment'] ?? true)) continue;
                $this->createData[$field['name']] = $field['default'] ?? '';
            }
        }
    }

    public function createRecord(): void
    {
        $rules = [];
        foreach ($this->schema['fields'] as $field) {
            if ($field['type'] === 'id' && ($field['auto_increment'] ?? true)) continue;
            $field['_table'] = $this->schema['table'];
            $rules["createData.{$field['name']}"] = $this->fields->getValidationRules($field['type'], $field);
        }

        $this->validate($rules);

        $table = $this->schema['table'] ?? $this->schemas->nameToTableName($this->modelName);
        DB::table($table)->insert($this->createData);

        $this->showCreateForm = false;
        $this->createData = [];
        $this->dispatch('record-created');
    }

    public function render()
    {
        $table = $this->schema['table'] ?? $this->schemas->nameToTableName($this->modelName);

        $query = DB::table($table);

        if ($this->search) {
            $firstField = $this->schema['fields'][0]['name'] ?? 'id';
            $query->where($firstField, 'like', "%{$this->search}%");
        }

        $records = $query->orderBy($this->sortField, $this->sortDirection)->paginate(15);

        return view('schema-studio::livewire.generated-model-list', [
            'records' => $records,
            'fields' => $this->schema['fields'],
        ]);
    }
}
