<?php

namespace Inox\SchemaStudio\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator
{
    protected FieldTypeRegistry $fields;

    public function __construct(FieldTypeRegistry $fields)
    {
        $this->fields = $fields;
    }

    public function generate(array $schema): string
    {
        $className = 'Create' . Str::studly($schema['table']) . 'Table';
        $table = $schema['table'];

        $columns = '';
        foreach ($schema['fields'] as $field) {
            $columns .= $this->generateColumn($field, $table) . "\n";
        }

        if ($schema['timestamps'] ?? true) {
            $columns .= "            \$table->timestamps();\n";
        }
        if ($schema['soft_deletes'] ?? false) {
            $columns .= "            \$table->softDeletes();\n";
        }

        $template = <<<PHP
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('{$table}', function (Blueprint \$table) {
{$columns}        });
    }

    public function down(): void
    {
        Schema::dropIfExists('{$table}');
    }
};

PHP;

        $dir = database_path('migrations');
        if (!File::isDirectory($dir)) File::makeDirectory($dir, 0755, true);

        $path = $dir . '/' . date('Y_m_d_His') . '_create_' . $table . '_table.php';
        File::put($path, $template);

        return $path;
    }

    protected function generateColumn(array $field, string $table): string
    {
        $type = $field['type'];
        $name = $field['name'];
        $typeConfig = $this->fields->get($type);

        if (!$typeConfig) {
            return "            \$table->string('{$name}');";
        }

        if ($type === 'id') {
            return "            \$table->id();";
        }

        $migration = $typeConfig['migration'];
        $nullable = ($field['required'] ?? false) ? '' : '->nullable()';
        $unique = ($field['unique'] ?? false) ? '->unique()' : '';
        $default = isset($field['default']) && $field['default'] !== '' ? "->default('{$field['default']}')" : '';

        if (in_array($type, ['select', 'status', 'multi-select'])) {
            $opts = $field['options'] ?? [];
            $default = isset($field['default']) && $field['default'] !== '' ? "->default('{$field['default']}')" : '';
            return "            \$table->{$migration}('{$name}'){$nullable}{$default};";
        }

        if ($type === 'relation:belongsTo') {
            $fk = $field['foreign_key'] ?? Str::snake($field['target_model'] ?? 'related') . '_id';
            $onDelete = $field['on_delete'] ?? 'cascade';
            return "            \$table->foreignId('{$fk}')" . ($nullable ? '' : '') . "->constrained()" . "->onDelete('{$onDelete}');";
        }

        if ($type === 'decimal') {
            $precision = $field['precision'] ?? 10;
            $scale = $field['scale'] ?? 2;
            return "            \$table->decimal('{$name}', {$precision}, {$scale}){$nullable}{$default};";
        }

        if ($type === 'integer') {
            $col = ($field['unsigned'] ?? false) ? 'unsignedInteger' : 'integer';
            $min = isset($field['min']) ? '' : '';
            $max = isset($field['max']) ? '' : '';
            return "            \$table->{$col}('{$name}'){$nullable}{$default};";
        }

        if ($type === 'slug') {
            return "            \$table->string('{$name}')->unique(){$nullable};";
        }

        if ($type === 'uuid') {
            return "            \$table->uuid('{$name}')->unique();";
        }

        return "            \$table->{$migration}('{$name}'){$nullable}{$unique}{$default};";
    }

    public function removeOldMigrations(string $table): void
    {
        foreach (File::glob(database_path('migrations/*_create_' . $table . '_table.php')) as $file) {
            File::delete($file);
        }
    }
}
