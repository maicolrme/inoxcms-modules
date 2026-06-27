<?php

namespace Inox\SchemaStudio\Http\Controllers\Api;

use App\Core\SettingRegistry\SettingRegistry;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Inox\SchemaStudio\Models\DynamicModel;
use Inox\SchemaStudio\Services\SchemaService;
use Inox\SchemaStudio\Services\FieldTypeRegistry;

class DynamicModelController extends Controller
{
    protected SchemaService $schemas;

    protected FieldTypeRegistry $fields;

    protected SettingRegistry $settings;

    protected array $actionUriMap = [
        'index'   => ['method' => 'GET',    'uri' => 'api/dynamic/{model}'],
        'store'   => ['method' => 'POST',   'uri' => 'api/dynamic/{model}'],
        'show'    => ['method' => 'GET',    'uri' => 'api/dynamic/{model}/{id}'],
        'update'  => ['method' => 'PUT',    'uri' => 'api/dynamic/{model}/{id}'],
        'destroy' => ['method' => 'DELETE', 'uri' => 'api/dynamic/{model}/{id}'],
    ];

    public function __construct(SchemaService $schemas, FieldTypeRegistry $fields)
    {
        $this->schemas = $schemas;
        $this->fields = $fields;

        if (app()->has(SettingRegistry::class)) {
            $this->settings = app(SettingRegistry::class);
        }
    }

    protected function resolveSchema(string $name): ?array
    {
        $schema = $this->schemas->find($name);
        if (!$schema) return null;
        if (!($schema['routes']['api'] ?? true)) return null;
        return $schema;
    }

    protected function isRouteEnabled(string $modelName, string $action): bool
    {
        if (!isset($this->actionUriMap[$action])) return true;

        $map = $this->actionUriMap[$action];
        $method = $map['method'];
        $uri = str_replace('{model}', $modelName, $map['uri']);

        if (!$this->settings) return true;

        $prefix = 'api.route.';
        $key = $prefix . $method . '.' . str_replace('/', '.', $uri);
        $val = $this->settings->get($key);

        if ($val !== null) {
            return $val === '1' || $val === true;
        }

        return true;
    }

    public function index(Request $request, string $modelName)
    {
        if (!$this->isRouteEnabled($modelName, 'index')) {
            return response()->json(['error' => 'This endpoint is disabled'], 404);
        }

        $schema = $this->resolveSchema($modelName);
        if (!$schema) {
            return response()->json(['error' => 'Model not found or API disabled'], 404);
        }

        $model = (new DynamicModel)->loadFromSchema($schema);
        $query = $model->newQuery();

        if ($search = $request->get('search')) {
            $searchable = $schema['fields'][0]['name'] ?? 'id';
            $query->where($searchable, 'like', "%{$search}%");
        }

        $perPage = min((int) $request->get('per_page', 15), 100);
        return $query->paginate($perPage);
    }

    public function show(string $modelName, $id)
    {
        if (!$this->isRouteEnabled($modelName, 'show')) {
            return response()->json(['error' => 'This endpoint is disabled'], 404);
        }

        $schema = $this->resolveSchema($modelName);
        if (!$schema) {
            return response()->json(['error' => 'Model not found or API disabled'], 404);
        }

        $model = (new DynamicModel)->loadFromSchema($schema);

        return $model->findOrFail($id);
    }

    public function store(Request $request, string $modelName)
    {
        if (!$this->isRouteEnabled($modelName, 'store')) {
            return response()->json(['error' => 'This endpoint is disabled'], 404);
        }

        $schema = $this->resolveSchema($modelName);
        if (!$schema) {
            return response()->json(['error' => 'Model not found or API disabled'], 404);
        }

        $rules = $this->buildValidationRules($schema);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $model = (new DynamicModel)->loadFromSchema($schema);
        $record = $model->create($validator->validated());

        return response()->json($record, 201);
    }

    public function update(Request $request, string $modelName, $id)
    {
        if (!$this->isRouteEnabled($modelName, 'update')) {
            return response()->json(['error' => 'This endpoint is disabled'], 404);
        }

        $schema = $this->resolveSchema($modelName);
        if (!$schema) {
            return response()->json(['error' => 'Model not found or API disabled'], 404);
        }

        $rules = $this->buildValidationRules($schema);
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $model = (new DynamicModel)->loadFromSchema($schema);
        $record = $model->findOrFail($id);
        $record->update($validator->validated());

        return response()->json($record);
    }

    public function destroy(string $modelName, $id)
    {
        if (!$this->isRouteEnabled($modelName, 'destroy')) {
            return response()->json(['error' => 'This endpoint is disabled'], 404);
        }

        $schema = $this->resolveSchema($modelName);
        if (!$schema) {
            return response()->json(['error' => 'Model not found or API disabled'], 404);
        }

        $model = (new DynamicModel)->loadFromSchema($schema);
        $record = $model->findOrFail($id);
        $record->delete();

        return response()->json(null, 204);
    }

    protected function buildValidationRules(array $schema): array
    {
        $rules = [];
        foreach ($schema['fields'] as $field) {
            $field['_table'] = $schema['table'];
            $rules[$field['name']] = $this->fields->getValidationRules($field['type'], $field);
        }
        return $rules;
    }
}
