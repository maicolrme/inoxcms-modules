<?php

namespace Inox\SchemaStudio\Services;

use Illuminate\Support\Facades\File;

class SchemaService
{
    protected string $path;

    protected FieldTypeRegistry $fields;

    public function __construct(FieldTypeRegistry $fields)
    {
        $this->path = config('schema-studio.schema_path', cms_path('schema'));
        $this->fields = $fields;

        if (!File::isDirectory($this->path)) {
            File::makeDirectory($this->path, 0755, true);
        }
    }

    public function all(): array
    {
        $schemas = [];
        foreach (File::files($this->path) as $file) {
            if ($file->getExtension() !== 'json') continue;
            $schema = $this->decode($file->getContents());
            if ($schema) $schemas[] = $schema;
        }

        usort($schemas, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
        return $schemas;
    }

    public function find(string $name): ?array
    {
        $path = $this->filePath($name);
        if (!File::exists($path)) return null;
        return $this->decode(File::get($path));
    }

    public function create(array $data): array
    {
        $fields = $data['fields'] ?? [];
        $hasId = collect($fields)->first(fn($f) => ($f['name'] ?? '') === 'id');

        if (!$hasId) {
            array_unshift($fields, [
                'name' => 'id',
                'type' => 'id',
                'label' => 'ID',
                'required' => true,
                'auto_increment' => true,
            ]);
        }

        $schema = [
            'name' => $data['name'],
            'table' => $data['table'] ?? $this->nameToTable($data['name']),
            'description' => $data['description'] ?? '',
            'timestamps' => $data['timestamps'] ?? true,
            'soft_deletes' => $data['soft_deletes'] ?? false,
            'fields' => $fields,
            'relations' => $data['relations'] ?? [],
            'routes' => ['api' => true, 'admin' => true],
            'generated_at' => now()->toIso8601String(),
        ];

        $this->write($schema);
        return $schema;
    }

    public function update(string $name, array $data): ?array
    {
        $schema = $this->find($name);
        if (!$schema) return null;

        foreach ($data as $key => $value) {
            if ($key !== 'name') {
                $schema[$key] = $value;
            }
        }
        $schema['generated_at'] = now()->toIso8601String();

        $this->write($schema);
        return $schema;
    }

    public function delete(string $name): bool
    {
        $path = $this->filePath($name);
        if (!File::exists($path)) return false;
        return File::delete($path);
    }

    public function filePath(string $name): string
    {
        return $this->path . '/' . $this->nameToFile($name);
    }

    public function nameToTable(string $name): string
    {
        return \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly($name));
    }

    public function nameToFile(string $name): string
    {
        return \Illuminate\Support\Str::slug($name) . '.schema.json';
    }

    public function nameToClass(string $name): string
    {
        return \Illuminate\Support\Str::studly($name);
    }

    public function nameToTableName(string $name): string
    {
        return \Illuminate\Support\Str::snake(\Illuminate\Support\Str::pluralStudly($name));
    }

    public function tableToModelClass(string $table): string
    {
        return \Illuminate\Support\Str::studly(\Illuminate\Support\Str::singular($table));
    }

    protected function decode(string $contents): ?array
    {
        $data = json_decode($contents, true);
        return is_array($data) ? $data : null;
    }

    protected function write(array $schema): void
    {
        $path = $this->filePath($schema['name']);
        File::put($path, json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
