<?php

namespace Inox\SchemaStudio\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Inox\SchemaStudio\Services\SchemaService;
use Inox\SchemaStudio\Services\FieldTypeRegistry;

#[Layout('layouts.admin')]
class SchemaDesigner extends Component
{
    public string $schemaName = '';

    public array $schema = [];

    public array $fields = [];

    public array $relations = [];

    public bool $timestamps = true;

    public bool $softDeletes = false;

    public string $description = '';

    public string $table = '';

    public string $editFieldIndex = '';

    public string $fieldName = '';

    public string $fieldType = 'text';

    public string $fieldLabel = '';

    public bool $fieldRequired = false;

    public bool $fieldUnique = false;

    public string $fieldDefault = '';

    public string $fieldOptions = '';

    public string $fieldTargetModel = '';

    public string $fieldForeignKey = '';

    public string $fieldOnDelete = 'cascade';

    public string $relationType = 'belongsTo';

    public string $relationModel = '';

    public string $relationForeignKey = '';

    public array $allSchemas = [];

    protected SchemaService $schemas;

    protected FieldTypeRegistry $fieldTypes;

    public function boot(): void
    {
        $this->schemas = app(SchemaService::class);
        $this->fieldTypes = app(FieldTypeRegistry::class);
    }

    public function mount(string $name): void
    {
        $this->schemaName = $name;
        $this->allSchemas = $this->schemas->all();
        $schema = $this->schemas->find($name);

        if ($schema) {
            $this->schema = $schema;
            $this->fields = $schema['fields'] ?? [];
            $this->relations = $schema['relations'] ?? [];
            $this->timestamps = $schema['timestamps'] ?? true;
            $this->softDeletes = $schema['soft_deletes'] ?? false;
            $this->description = $schema['description'] ?? '';
            $this->table = $schema['table'] ?? '';
        }
    }

    public function addField(): void
    {
        $this->validate([
            'fieldName' => 'required|alpha_dash|max:100',
            'fieldType' => 'required',
        ]);

        if ($this->fieldType === 'id') {
            $hasId = collect($this->fields)->first(fn($f) => $f['type'] === 'id');
            if ($hasId) {
                session()->flash('error', 'Only one ID field allowed.');
                return;
            }
        }

        $field = [
            'name' => $this->fieldName,
            'type' => $this->fieldType,
            'label' => $this->fieldLabel ?: $this->fieldName,
            'required' => $this->fieldRequired,
            'unique' => $this->fieldUnique,
            'default' => $this->fieldDefault,
        ];

        if (in_array($this->fieldType, ['select', 'multi-select', 'status'])) {
            $field['options'] = array_map('trim', explode(',', $this->fieldOptions));
        }

        if (in_array($this->fieldType, ['relation:belongsTo', 'relation:hasMany', 'relation:belongsToMany'])) {
            $field['target_model'] = $this->fieldTargetModel;
            $field['foreign_key'] = $this->fieldForeignKey ?: strtolower($this->fieldTargetModel) . '_id';
            $field['on_delete'] = $this->fieldOnDelete;
        }

        $this->fields[] = $field;
        $this->dispatch('notify', message: 'Field added.');
        $this->save();
        $this->resetFieldForm();
    }

    public function editField(int $index): void
    {
        if (!isset($this->fields[$index])) return;

        $field = $this->fields[$index];
        $this->editFieldIndex = (string) $index;
        $this->fieldName = $field['name'];
        $this->fieldType = $field['type'];
        $this->fieldLabel = $field['label'] ?? '';
        $this->fieldRequired = $field['required'] ?? false;
        $this->fieldUnique = $field['unique'] ?? false;
        $this->fieldDefault = $field['default'] ?? '';
        $this->fieldOptions = is_array($field['options'] ?? null) ? implode(',', $field['options']) : '';
        $this->fieldTargetModel = $field['target_model'] ?? '';
        $this->fieldForeignKey = $field['foreign_key'] ?? '';
        $this->fieldOnDelete = $field['on_delete'] ?? 'cascade';
    }

    public function updateField(): void
    {
        $idx = (int) $this->editFieldIndex;
        if (!isset($this->fields[$idx])) return;

        $field = [
            'name' => $this->fieldName,
            'type' => $this->fieldType,
            'label' => $this->fieldLabel ?: $this->fieldName,
            'required' => $this->fieldRequired,
            'unique' => $this->fieldUnique,
            'default' => $this->fieldDefault,
        ];

        if (in_array($this->fieldType, ['select', 'multi-select', 'status'])) {
            $field['options'] = array_map('trim', explode(',', $this->fieldOptions));
        }

        if (in_array($this->fieldType, ['relation:belongsTo', 'relation:hasMany', 'relation:belongsToMany'])) {
            $field['target_model'] = $this->fieldTargetModel;
            $field['foreign_key'] = $this->fieldForeignKey ?: strtolower($this->fieldTargetModel) . '_id';
            $field['on_delete'] = $this->fieldOnDelete;
        }

        $this->fields[$idx] = $field;
        $this->dispatch('notify', message: 'Field updated.');
        $this->save();
        $this->resetFieldForm();
    }

    public function removeField(int $index): void
    {
        unset($this->fields[$index]);
        $this->fields = array_values($this->fields);
        $this->dispatch('notify', message: 'Field removed.');
        $this->save();
    }

    public function moveFieldUp(int $index): void
    {
        if ($index <= 0) return;
        $tmp = $this->fields[$index];
        $this->fields[$index] = $this->fields[$index - 1];
        $this->fields[$index - 1] = $tmp;
        $this->fields = array_values($this->fields);
        $this->save();
    }

    public function moveFieldDown(int $index): void
    {
        if ($index >= count($this->fields) - 1) return;
        $tmp = $this->fields[$index];
        $this->fields[$index] = $this->fields[$index + 1];
        $this->fields[$index + 1] = $tmp;
        $this->fields = array_values($this->fields);
        $this->save();
    }

    public function addRelation(): void
    {
        $this->validate([
            'relationType' => 'required|in:belongsTo,hasMany,belongsToMany',
            'relationModel' => 'required|alpha_dash',
        ]);

        $relation = [
            'type' => $this->relationType,
            'model' => $this->relationModel,
            'foreign_key' => $this->relationForeignKey ?: strtolower($this->relationModel) . '_id',
        ];

        $this->relations[] = $relation;
        $this->save();
        $this->resetRelationForm();
    }

    public function removeRelation(int $index): void
    {
        unset($this->relations[$index]);
        $this->relations = array_values($this->relations);
        $this->save();
    }

    public function toggleTimestamps(): void
    {
        $this->timestamps = !$this->timestamps;
        $this->save();
    }

    public function toggleSoftDeletes(): void
    {
        $this->softDeletes = !$this->softDeletes;
        $this->save();
    }

    public function save(): void
    {
        $data = [
            'fields' => $this->fields,
            'relations' => $this->relations,
            'timestamps' => $this->timestamps,
            'soft_deletes' => $this->softDeletes,
            'description' => $this->description,
            'table' => $this->table ?: $this->schemas->nameToTableName($this->schemaName),
        ];

        $this->schemas->update($this->schemaName, $data);
        $this->dispatch('notify', message: 'Saved.');
    }

    public function resetFieldForm(): void
    {
        $this->editFieldIndex = '';
        $this->fieldName = '';
        $this->fieldType = 'text';
        $this->fieldLabel = '';
        $this->fieldRequired = false;
        $this->fieldUnique = false;
        $this->fieldDefault = '';
        $this->fieldOptions = '';
        $this->fieldTargetModel = '';
        $this->fieldForeignKey = '';
        $this->fieldOnDelete = 'cascade';
    }

    public function resetRelationForm(): void
    {
        $this->relationType = 'belongsTo';
        $this->relationModel = '';
        $this->relationForeignKey = '';
    }

    public function render()
    {
        return view('schema-studio::livewire.schema-designer', [
            'fieldTypes' => $this->fieldTypes->all(),
            'availableModels' => array_map(fn($s) => $s['name'], $this->allSchemas),
        ]);
    }
}
