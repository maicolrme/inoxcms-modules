<?php

namespace Inox\SchemaStudio\Services;

use Illuminate\Support\Facades\File;

class PolicyGenerator
{
    public function generate(array $schema): string
    {
        $className = $schema['name'] . 'Policy';
        $modelClass = "\\App\\Models\\{$schema['name']}";

        $template = <<<PHP
<?php

namespace App\Policies;

use App\Models\User;
use {$modelClass};
use Illuminate\Auth\Access\HandlesAuthorization;

class {$className}
{
    use HandlesAuthorization;

    public function viewAny(User \$user): bool
    {
        return true;
    }

    public function view(User \$user, {$schema['name']} \$model): bool
    {
        return true;
    }

    public function create(User \$user): bool
    {
        return true;
    }

    public function update(User \$user, {$schema['name']} \$model): bool
    {
        return true;
    }

    public function delete(User \$user, {$schema['name']} \$model): bool
    {
        return true;
    }
}

PHP;

        $dir = app_path('Policies');
        if (!File::isDirectory($dir)) File::makeDirectory($dir, 0755, true);

        $path = "{$dir}/{$className}.php";
        File::put($path, $template);

        return $path;
    }
}
