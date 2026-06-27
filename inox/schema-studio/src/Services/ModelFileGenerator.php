<?php

namespace Inox\SchemaStudio\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ModelFileGenerator
{
    protected FieldTypeRegistry $fields;

    public function __construct(FieldTypeRegistry $fields)
    {
        $this->fields = $fields;
    }

    public function generate(array $schema): string
    {
        $className = $schema['name'];
        $table = $schema['table'];
        $fillable = [];
        $casts = [];
        $relations = '';

        foreach ($schema['fields'] as $field) {
            $fillable[] = $field['name'];

            $cast = $this->fields->getCastType($field['type']);
            if ($cast) {
                $casts[$field['name']] = $cast;
            }

            if (in_array($field['type'], ['relation:belongsTo', 'relation:hasMany', 'relation:belongsToMany'])) {
                $relations .= $this->generateRelationMethod($field) . "\n\n";
            }
        }

        foreach ($schema['relations'] as $relation) {
            $relations .= $this->generateRelationFromArray($relation) . "\n\n";
        }

        $fillableStr = "['" . implode("', '", $fillable) . "']";
        $castsStr = empty($casts) ? "[]" : "[\n        '" . implode("' => '", array_map(fn($k, $v) => "$k' => '$v", array_keys($casts), array_values($casts))) . "',\n    ]";

        $timestamps = ($schema['timestamps'] ?? true) ? 'true' : 'false';
        $softDeletes = ($schema['soft_deletes'] ?? false) ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n    " : '';

        $template = <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
{$softDeletes}class {$className} extends Model
{
    use HasFactory;

    protected \$table = '{$table}';

    public \$timestamps = {$timestamps};

    protected \$fillable = {$fillableStr};

    protected \$casts = {$castsStr};

{$relations}}
PHP;

        $dir = app_path('Models');
        if (!File::isDirectory($dir)) File::makeDirectory($dir, 0755, true);

        $path = "{$dir}/{$className}.php";
        File::put($path, $template);

        return $path;
    }

    protected function generateRelationMethod(array $field): string
    {
        $type = $field['type'];
        $target = $field['target_model'] ?? 'Related';
        $method = Str::camel($target);

        return match ($type) {
            'relation:belongsTo' => $this->belongsTo($method, $target, $field),
            'relation:hasMany' => $this->hasMany($method, $target, $field),
            'relation:belongsToMany' => $this->belongsToMany($method, $target, $field),
            default => '',
        };
    }

    protected function generateRelationFromArray(array $relation): string
    {
        $type = $relation['type'];
        $target = $relation['model'] ?? 'Related';
        $method = Str::camel($target);

        return match ($type) {
            'belongsTo' => $this->belongsTo($method, $target, $relation),
            'hasMany' => $this->hasMany($method, $target, $relation),
            'belongsToMany' => $this->belongsToMany($method, $target, $relation),
            default => '',
        };
    }

    protected function belongsTo(string $method, string $target, array $config): string
    {
        $fk = $config['foreign_key'] ?? Str::snake($target) . '_id';
        $ownerKey = $config['owner_key'] ?? 'id';
        return "    public function {$method}()\n    {\n        return \$this->belongsTo(\\App\\Models\\{$target}::class, '{$fk}', '{$ownerKey}');\n    }";
    }

    protected function hasMany(string $method, string $target, array $config): string
    {
        $fk = $config['foreign_key'] ?? Str::snake($config['_source_table'] ?? '') . '_id';
        $localKey = $config['local_key'] ?? 'id';
        return "    public function {$method}()\n    {\n        return \$this->hasMany(\\App\\Models\\{$target}::class, '{$fk}', '{$localKey}');\n    }";
    }

    protected function belongsToMany(string $method, string $target, array $config): string
    {
        $pivot = $config['pivot_table'] ?? '';
        $fk = $config['foreign_pivot_key'] ?? Str::snake($config['_source_table'] ?? '') . '_id';
        $rk = $config['related_pivot_key'] ?? Str::snake($target) . '_id';
        return "    public function {$method}()\n    {\n        return \$this->belongsToMany(\\App\\Models\\{$target}::class" .
            ($pivot ? ", '{$pivot}'" : '') .
            ($fk ? ", '{$fk}'" : '') .
            ($rk ? ", '{$rk}'" : '') .
            ");\n    }";
    }
}
