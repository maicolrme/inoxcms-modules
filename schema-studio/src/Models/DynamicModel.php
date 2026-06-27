<?php

namespace Inox\SchemaStudio\Models;

use Illuminate\Database\Eloquent\Model;

class DynamicModel extends Model
{
    protected array $schemaFields = [];

    protected array $schemaCasts = [];

    protected bool $schemaTimestamps = true;

    protected string $schemaTable = '';

    public function loadFromSchema(array $schema): static
    {
        $this->schemaTable = $schema['table'] ?? '';
        $this->schemaTimestamps = $schema['timestamps'] ?? true;
        $this->schemaFields = [];
        $this->schemaCasts = [];

        foreach ($schema['fields'] ?? [] as $field) {
            $this->schemaFields[] = $field['name'];
            $cast = $this->resolveCast($field['type']);
            if ($cast) {
                $this->schemaCasts[$field['name']] = $cast;
            }
        }

        $this->fillable($this->schemaFields);
        $this->casts($this->schemaCasts);
        $this->setTable($this->schemaTable);
        $this->timestamps = $this->schemaTimestamps;

        return $this;
    }

    protected function resolveCast(string $type): ?string
    {
        return match ($type) {
            'integer' => 'integer',
            'decimal', 'float' => 'float',
            'boolean' => 'boolean',
            'json', 'multi-select', 'coordinates' => 'array',
            'date' => 'date:Y-m-d',
            'datetime' => 'datetime:Y-m-d\TH:i:s\Z',
            default => null,
        };
    }
}
