<?php

namespace Inox\SchemaStudio\Services;

class OpenApiGenerator
{
    protected FieldTypeRegistry $fields;

    public function __construct(FieldTypeRegistry $fields)
    {
        $this->fields = $fields;
    }

    public function generate(array $schemas): string
    {
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'INOX Generated API',
                'version' => '1.0.0',
                'description' => 'Auto-generated REST API from Schema Studio models.',
            ],
            'paths' => [],
            'components' => [
                'schemas' => [],
            ],
        ];

        foreach ($schemas as $schema) {
            $name = $schema['name'];
            $table = $schema['table'];

            $properties = [];
            $required = [];

            foreach ($schema['fields'] as $field) {
                $properties[$field['name']] = $this->fieldToOpenApiProperty($field);
                if ($field['required'] ?? false) {
                    $required[] = $field['name'];
                }
            }

            $spec['components']['schemas'][$name] = [
                'type' => 'object',
                'properties' => $properties,
                'required' => $required,
            ];

            $spec['components']['schemas'][$name . 'List'] = [
                'type' => 'object',
                'properties' => [
                    'data' => ['type' => 'array', 'items' => ['$ref' => "#/components/schemas/{$name}"]],
                    'current_page' => ['type' => 'integer'],
                    'per_page' => ['type' => 'integer'],
                    'total' => ['type' => 'integer'],
                    'last_page' => ['type' => 'integer'],
                ],
            ];

            $basePath = "/api/{$table}";

            $spec['paths'][$basePath]['get'] = [
                'summary' => "List {$name}",
                'parameters' => [
                    ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                    ['name' => 'per_page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                    ['name' => 'search', 'in' => 'query', 'schema' => ['type' => 'string']],
                ],
                'responses' => ['200' => ['description' => 'OK', 'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/{$name}List"]]]]],
            ];

            $spec['paths'][$basePath]['post'] = [
                'summary' => "Create {$name}",
                'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/{$name}"]]]],
                'responses' => ['201' => ['description' => 'Created']],
            ];

            $spec['paths']["{$basePath}/{id}"]['get'] = [
                'summary' => "Get {$name}",
                'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                'responses' => ['200' => ['description' => 'OK']],
            ];

            $spec['paths']["{$basePath}/{id}"]['put'] = [
                'summary' => "Update {$name}",
                'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                'requestBody' => ['required' => true, 'content' => ['application/json' => ['schema' => ['$ref' => "#/components/schemas/{$name}"]]]],
                'responses' => ['200' => ['description' => 'Updated']],
            ];

            $spec['paths']["{$basePath}/{id}"]['delete'] = [
                'summary' => "Delete {$name}",
                'parameters' => [['name' => 'id', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'integer']]],
                'responses' => ['204' => ['description' => 'Deleted']],
            ];
        }

        return json_encode($spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    protected function fieldToOpenApiProperty(array $field): array
    {
        $typeMap = [
            'text' => ['type' => 'string'],
            'longtext' => ['type' => 'string'],
            'integer' => ['type' => 'integer'],
            'decimal' => ['type' => 'number'],
            'float' => ['type' => 'number'],
            'boolean' => ['type' => 'boolean'],
            'date' => ['type' => 'string', 'format' => 'date'],
            'datetime' => ['type' => 'string', 'format' => 'date-time'],
            'time' => ['type' => 'string'],
            'email' => ['type' => 'string', 'format' => 'email'],
            'url' => ['type' => 'string', 'format' => 'uri'],
            'slug' => ['type' => 'string'],
            'uuid' => ['type' => 'string', 'format' => 'uuid'],
            'password' => ['type' => 'string', 'format' => 'password'],
            'color' => ['type' => 'string'],
            'phone' => ['type' => 'string'],
            'code' => ['type' => 'string'],
        ];

        return $typeMap[$field['type']] ?? ['type' => 'string'];
    }
}
