<?php

namespace Inox\SchemaStudio\Services;

class TypeScriptGenerator
{
    protected FieldTypeRegistry $fields;

    public function __construct(FieldTypeRegistry $fields)
    {
        $this->fields = $fields;
    }

    public function generate(array $schemas): string
    {
        $output = "// Auto-generated TypeScript types from Schema Studio\n";
        $output .= "// Generated at: " . now()->toIso8601String() . "\n\n";

        foreach ($schemas as $schema) {
            $name = $schema['name'];
            $output .= "export interface {$name} {\n";
            $output .= "  id: number;\n";

            foreach ($schema['fields'] as $field) {
                $tsType = $this->fieldToTsType($field['type']);
                $optional = ($field['required'] ?? false) ? '' : '?';
                $output .= "  {$field['name']}{$optional}: {$tsType};\n";
            }

            if ($schema['timestamps'] ?? true) {
                $output .= "  created_at?: string;\n";
                $output .= "  updated_at?: string;\n";
            }
            if ($schema['soft_deletes'] ?? false) {
                $output .= "  deleted_at?: string | null;\n";
            }

            $output .= "}\n\n";

            $output .= "export interface {$name}ListResponse {\n";
            $output .= "  data: {$name}[];\n";
            $output .= "  current_page: number;\n";
            $output .= "  per_page: number;\n";
            $output .= "  total: number;\n";
            $output .= "  last_page: number;\n";
            $output .= "}\n\n";
        }

        return $output;
    }

    protected function fieldToTsType(string $fieldType): string
    {
        return match ($fieldType) {
            'text', 'longtext', 'slug', 'email', 'url', 'phone', 'password', 'color', 'code', 'select', 'status' => 'string',
            'integer' => 'number',
            'decimal', 'float' => 'number',
            'boolean' => 'boolean',
            'date', 'datetime', 'time' => 'string',
            'multi-select' => 'string[]',
            'json', 'coordinates' => 'Record<string, unknown>',
            'media' => 'number | null',
            'uuid' => 'string',
            'relation:belongsTo' => 'number',
            'relation:hasMany' => 'unknown[]',
            'relation:belongsToMany' => 'unknown[]',
            default => 'unknown',
        };
    }
}
