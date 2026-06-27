<?php

namespace Inox\SchemaStudio\Services;

class FieldTypeRegistry
{
    protected array $types = [];

    public function __construct()
    {
        $this->registerBuiltIn();
    }

    protected function registerBuiltIn(): void
    {
        $this->register('id', [
            'label' => 'Auto Increment ID',
            'migration' => 'id',
            'validation_base' => 'integer',
            'options' => [],
        ]);

        $this->register('text', [
            'label' => 'Text',
            'migration' => 'string',
            'validation_base' => 'string|max:255',
            'options' => ['max_length', 'min_length', 'default', 'placeholder'],
        ]);

        $this->register('longtext', [
            'label' => 'Long Text',
            'migration' => 'text',
            'validation_base' => 'string',
            'options' => ['default', 'rich_text'],
        ]);

        $this->register('integer', [
            'label' => 'Integer',
            'migration' => 'integer',
            'validation_base' => 'integer',
            'options' => ['min', 'max', 'default', 'unsigned'],
        ]);

        $this->register('decimal', [
            'label' => 'Decimal',
            'migration' => 'decimal:{{precision}},{{scale}}',
            'validation_base' => 'numeric',
            'options' => ['precision', 'scale', 'min', 'max', 'default'],
            'defaults' => ['precision' => 10, 'scale' => 2],
        ]);

        $this->register('float', [
            'label' => 'Float',
            'migration' => 'float',
            'validation_base' => 'numeric',
            'options' => ['min', 'max', 'default'],
        ]);

        $this->register('boolean', [
            'label' => 'Boolean',
            'migration' => 'boolean',
            'validation_base' => 'boolean',
            'options' => ['default'],
        ]);

        $this->register('date', [
            'label' => 'Date',
            'migration' => 'date',
            'validation_base' => 'date',
            'options' => ['default'],
        ]);

        $this->register('datetime', [
            'label' => 'Date & Time',
            'migration' => 'dateTime',
            'validation_base' => 'date',
            'options' => ['default'],
        ]);

        $this->register('time', [
            'label' => 'Time',
            'migration' => 'time',
            'validation_base' => 'date_format:H:i',
            'options' => ['default'],
        ]);

        $this->register('select', [
            'label' => 'Select',
            'migration' => 'string',
            'validation_base' => 'in:{{options}}',
            'options' => ['options', 'default'],
        ]);

        $this->register('multi-select', [
            'label' => 'Multi Select',
            'migration' => 'json',
            'validation_base' => 'array',
            'options' => ['options'],
        ]);

        $this->register('media', [
            'label' => 'Media',
            'migration' => 'foreignId:media_nullable',
            'validation_base' => 'nullable|exists:media,id',
            'options' => [],
        ]);

        $this->register('json', [
            'label' => 'JSON',
            'migration' => 'json',
            'validation_base' => 'json',
            'options' => ['default'],
        ]);

        $this->register('slug', [
            'label' => 'Slug',
            'migration' => 'string:unique',
            'validation_base' => 'string|max:255|unique:{{table}},{{column}}',
            'options' => ['source_field'],
        ]);

        $this->register('uuid', [
            'label' => 'UUID',
            'migration' => 'uuid:unique',
            'validation_base' => 'string|size:36|unique:{{table}},{{column}}',
            'options' => [],
        ]);

        $this->register('email', [
            'label' => 'Email',
            'migration' => 'string',
            'validation_base' => 'email|max:255',
            'options' => ['unique', 'default'],
        ]);

        $this->register('url', [
            'label' => 'URL',
            'migration' => 'string',
            'validation_base' => 'url|max:2048',
            'options' => ['default'],
        ]);

        $this->register('phone', [
            'label' => 'Phone',
            'migration' => 'string',
            'validation_base' => 'string|max:30',
            'options' => ['default'],
        ]);

        $this->register('password', [
            'label' => 'Password',
            'migration' => 'string',
            'validation_base' => 'string|min:8',
            'options' => ['min_length', 'hash'],
        ]);

        $this->register('color', [
            'label' => 'Color',
            'migration' => 'string',
            'validation_base' => 'string|max:7|regex:/^#[a-f0-9]{6}$/i',
            'options' => ['default'],
        ]);

        $this->register('coordinates', [
            'label' => 'Coordinates',
            'migration' => 'json',
            'validation_base' => 'json',
            'options' => [],
        ]);

        $this->register('code', [
            'label' => 'Code',
            'migration' => 'text',
            'validation_base' => 'string',
            'options' => ['language'],
        ]);

        $this->register('status', [
            'label' => 'Status',
            'migration' => 'string',
            'validation_base' => 'in:{{options}}',
            'options' => ['options', 'default'],
            'defaults' => ['options' => ['draft', 'published', 'archived']],
        ]);
    }

    public function register(string $type, array $config): void
    {
        $this->types[$type] = $config;
    }

    public function get(string $type): ?array
    {
        return $this->types[$type] ?? null;
    }

    public function all(): array
    {
        return $this->types;
    }

    public function getMigrationColumn(string $type, array $field): string
    {
        $config = $this->get($type);
        if (!$config) return 'string';

        $template = $config['migration'];
        $replacements = [
            '{{precision}}' => $field['precision'] ?? 10,
            '{{scale}}' => $field['scale'] ?? 2,
            '{{table}}' => $field['_table'] ?? 'table',
            '{{column}}' => $field['name'] ?? 'column',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    public function getValidationRules(string $type, array $field): string
    {
        $config = $this->get($type);
        if (!$config) return 'string';

        $rules = $config['validation_base'] ?? '';
        $rules = ($field['required'] ?? false) ? str_replace('nullable|', '', "required|$rules") : "nullable|$rules";

        $replacements = [
            '{{options}}' => implode(',', $field['options'] ?? []),
            '{{table}}' => $field['_table'] ?? 'table',
            '{{column}}' => $field['name'] ?? 'column',
            '{{max}}' => $field['max_length'] ?? 255,
            '{{min}}' => $field['min_length'] ?? 1,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $rules);
    }

    public function getCastType(string $type): ?string
    {
        return match ($type) {
            'integer' => 'integer',
            'decimal', 'float' => 'float',
            'boolean' => 'boolean',
            'json', 'multi-select', 'coordinates' => 'array',
            'date' => 'date',
            'datetime' => 'datetime',
            default => null,
        };
    }
}
