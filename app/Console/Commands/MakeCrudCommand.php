<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeCrudCommand extends Command
{
    protected $signature = 'make:crud
        {name : The model name (e.g., Post)}
        {--api : Generate API controller only}
        {--web : Generate Web controller only}
        {--force : Overwrite existing files}';

    protected $description = 'Generate complete CRUD scaffolding (Actions, Queries, Requests, Resource, Policy, Controllers)';

    public function __construct(
        private readonly Filesystem $files
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = Str::studly($this->argument('name'));
        $namePlural = Str::plural($name);
        $nameKebab = Str::kebab($name);
        $namePluralKebab = Str::kebab($namePlural);

        $this->info("Generating CRUD for {$name}...\n");

        // Generate Actions
        $this->generateActions($name);

        // Generate Queries
        $this->generateQueries($name, $namePlural);

        // Generate Form Requests
        $this->generateFormRequests($name);

        // Generate Resource
        $this->generateResource($name);

        // Generate Policy
        $this->generatePolicy($name);

        // Generate Controllers
        if (! $this->option('api')) {
            $this->generateWebController($name, $namePlural);
        }
        if (! $this->option('web')) {
            $this->generateApiController($name, $namePlural);
        }

        $this->newLine();
        $this->info("CRUD scaffolding for {$name} generated successfully!");
        $this->newLine();

        $this->warn('Next steps:');
        $this->line("  1. Create migration: php artisan make:migration create_{$namePluralKebab}_table");
        $this->line("  2. Create model: php artisan make:model {$name}");
        $this->line('  3. Add routes to routes/web.php and/or routes/api.php');
        $this->line('  4. Register policy in AuthServiceProvider');
        $this->line("  5. Create permissions in database: {$namePluralKebab}-view, {$namePluralKebab}-create, {$namePluralKebab}-edit, {$namePluralKebab}-delete");

        return self::SUCCESS;
    }

    protected function generateActions(string $name): void
    {
        $actions = ['Create', 'Update', 'Delete'];

        foreach ($actions as $action) {
            $this->call('make:action', [
                'name' => "{$name}/{$action}{$name}",
            ]);
        }
    }

    protected function generateQueries(string $name, string $namePlural): void
    {
        $this->call('make:query', [
            'name' => "{$name}/List{$namePlural}Query",
        ]);
        $this->call('make:query', [
            'name' => "{$name}/Get{$name}Query",
        ]);
    }

    protected function generateFormRequests(string $name): void
    {
        $requests = [
            'Store' => $this->getStoreRequestStub($name),
            'Update' => $this->getUpdateRequestStub($name),
        ];

        foreach ($requests as $type => $stub) {
            $path = app_path("Http/Requests/{$name}/{$type}{$name}Request.php");
            $this->writeFile($path, $stub, "Form Request {$type}{$name}Request");
        }
    }

    protected function generateResource(string $name): void
    {
        $stub = $this->getResourceStub($name);
        $path = app_path("Http/Resources/{$name}Resource.php");
        $this->writeFile($path, $stub, "Resource {$name}Resource");
    }

    protected function generatePolicy(string $name): void
    {
        $stub = $this->getPolicyStub($name);
        $path = app_path("Policies/{$name}Policy.php");
        $this->writeFile($path, $stub, "Policy {$name}Policy");
    }

    protected function generateWebController(string $name, string $namePlural): void
    {
        $stub = $this->getWebControllerStub($name, $namePlural);
        $path = app_path("Http/Controllers/{$name}Controller.php");
        $this->writeFile($path, $stub, "Web Controller {$name}Controller");
    }

    protected function generateApiController(string $name, string $namePlural): void
    {
        $stub = $this->getApiControllerStub($name, $namePlural);
        $path = app_path("Http/Controllers/Api/V1/{$name}Controller.php");
        $this->writeFile($path, $stub, "API Controller {$name}Controller");
    }

    protected function writeFile(string $path, string $content, string $label): void
    {
        $directory = dirname($path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($this->files->exists($path) && ! $this->option('force')) {
            $this->warn("  [SKIP] {$label} already exists");

            return;
        }

        $this->files->put($path, $content);
        $this->line("  [OK] {$label}");
    }

    protected function getStoreRequestStub(string $name): string
    {
        $namePluralKebab = Str::kebab(Str::plural($name));

        return <<<STUB
<?php

namespace App\Http\Requests\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class Store{$name}Request extends FormRequest
{
    public function authorize(): bool
    {
        return \$this->user()->can('{$namePluralKebab}-create');
    }

    public function rules(): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}
STUB;
    }

    protected function getUpdateRequestStub(string $name): string
    {
        $namePluralKebab = Str::kebab(Str::plural($name));

        return <<<STUB
<?php

namespace App\Http\Requests\\{$name};

use Illuminate\Foundation\Http\FormRequest;

class Update{$name}Request extends FormRequest
{
    public function authorize(): bool
    {
        return \$this->user()->can('{$namePluralKebab}-edit');
    }

    public function rules(): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}
STUB;
    }

    protected function getResourceStub(string $name): string
    {
        return <<<STUB
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
            'id' => \$this->id,
            // TODO: Add resource fields
            'created_at' => \$this->created_at->toISOString(),
            'updated_at' => \$this->updated_at->toISOString(),
        ];
    }
}
STUB;
    }

    protected function getPolicyStub(string $name): string
    {
        $namePluralKebab = Str::kebab(Str::plural($name));
        $modelVar = Str::camel($name);

        return <<<STUB
<?php

namespace App\Policies;

use App\Models\\{$name};
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class {$name}Policy
{
    use HandlesAuthorization;

    public function viewAny(User \$user): bool
    {
        return \$user->hasPermissionTo('{$namePluralKebab}-view');
    }

    public function view(User \$user, {$name} \${$modelVar}): bool
    {
        return \$user->hasPermissionTo('{$namePluralKebab}-view');
    }

    public function create(User \$user): bool
    {
        return \$user->hasPermissionTo('{$namePluralKebab}-create');
    }

    public function update(User \$user, {$name} \${$modelVar}): bool
    {
        return \$user->hasPermissionTo('{$namePluralKebab}-edit');
    }

    public function delete(User \$user, {$name} \${$modelVar}): bool
    {
        return \$user->hasPermissionTo('{$namePluralKebab}-delete');
    }
}
STUB;
    }

    protected function getWebControllerStub(string $name, string $namePlural): string
    {
        $modelVar = Str::camel($name);
        $namePluralCamel = Str::camel($namePlural);
        $routeName = Str::kebab($namePlural);
        $viewPath = Str::kebab($namePlural);

        return <<<STUB
<?php

namespace App\Http\Controllers;

use App\Actions\\{$name}\Create{$name};
use App\Actions\\{$name}\Delete{$name};
use App\Actions\\{$name}\Update{$name};
use App\Http\Requests\\{$name}\Store{$name}Request;
use App\Http\Requests\\{$name}\Update{$name}Request;
use App\Models\\{$name};
use App\Queries\\{$name}\List{$namePlural}Query;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class {$name}Controller extends Controller
{
    public function __construct(
        private readonly List{$namePlural}Query \$list{$namePlural},
        private readonly Create{$name} \$create{$name},
        private readonly Update{$name} \$update{$name},
        private readonly Delete{$name} \$delete{$name},
    ) {}

    public function index(Request \$request): Response
    {
        \$this->authorize('viewAny', {$name}::class);

        \${$namePluralCamel} = \$this->list{$namePlural}->handle([
            'search' => \$request->search,
            'per_page' => \$request->per_page ?? 15,
        ]);

        return Inertia::render('{$viewPath}/Index', [
            '{$namePluralCamel}' => \${$namePluralCamel},
        ]);
    }

    public function create(): Response
    {
        \$this->authorize('create', {$name}::class);

        return Inertia::render('{$viewPath}/Form');
    }

    public function store(Store{$name}Request \$request): RedirectResponse
    {
        \$this->create{$name}->handle(\$request->validated());

        return redirect()->route('{$routeName}.index')
            ->with('success', __('{$routeName}.created'));
    }

    public function edit({$name} \${$modelVar}): Response
    {
        \$this->authorize('update', \${$modelVar});

        return Inertia::render('{$viewPath}/Form', [
            '{$modelVar}' => \${$modelVar},
        ]);
    }

    public function update(Update{$name}Request \$request, {$name} \${$modelVar}): RedirectResponse
    {
        \$this->update{$name}->handle([...\$request->validated(), 'id' => \${$modelVar}->id]);

        return redirect()->route('{$routeName}.index')
            ->with('success', __('{$routeName}.updated'));
    }

    public function destroy({$name} \${$modelVar}): RedirectResponse
    {
        \$this->authorize('delete', \${$modelVar});

        \$this->delete{$name}->handle(['id' => \${$modelVar}->id]);

        return redirect()->route('{$routeName}.index')
            ->with('success', __('{$routeName}.deleted'));
    }
}
STUB;
    }

    protected function getApiControllerStub(string $name, string $namePlural): string
    {
        $modelVar = Str::camel($name);
        $namePluralCamel = Str::camel($namePlural);
        $routeName = Str::kebab($namePlural);

        return <<<STUB
<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\\{$name}\Create{$name};
use App\Actions\\{$name}\Delete{$name};
use App\Actions\\{$name}\Update{$name};
use App\Http\Controllers\Controller;
use App\Http\Requests\\{$name}\Store{$name}Request;
use App\Http\Requests\\{$name}\Update{$name}Request;
use App\Http\Resources\\{$name}Resource;
use App\Http\Responses\ApiResponse;
use App\Models\\{$name};
use App\Queries\\{$name}\List{$namePlural}Query;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {$name}Controller extends Controller
{
    public function __construct(
        private readonly List{$namePlural}Query \$list{$namePlural},
        private readonly Create{$name} \$create{$name},
        private readonly Update{$name} \$update{$name},
        private readonly Delete{$name} \$delete{$name},
    ) {}

    public function index(Request \$request): JsonResponse
    {
        \$this->authorize('viewAny', {$name}::class);

        \${$namePluralCamel} = \$this->list{$namePlural}->handle([
            'search' => \$request->search,
            'per_page' => \$request->per_page ?? 15,
        ]);

        return ApiResponse::paginated(
            {$name}Resource::collection(\${$namePluralCamel})
        );
    }

    public function store(Store{$name}Request \$request): JsonResponse
    {
        \${$modelVar} = \$this->create{$name}->handle(\$request->validated());

        return ApiResponse::created(
            new {$name}Resource(\${$modelVar}),
            __('{$routeName}.created')
        );
    }

    public function show({$name} \${$modelVar}): JsonResponse
    {
        \$this->authorize('view', \${$modelVar});

        return ApiResponse::success(
            new {$name}Resource(\${$modelVar})
        );
    }

    public function update(Update{$name}Request \$request, {$name} \${$modelVar}): JsonResponse
    {
        \${$modelVar} = \$this->update{$name}->handle([...\$request->validated(), 'id' => \${$modelVar}->id]);

        return ApiResponse::success(
            new {$name}Resource(\${$modelVar}),
            __('{$routeName}.updated')
        );
    }

    public function destroy({$name} \${$modelVar}): JsonResponse
    {
        \$this->authorize('delete', \${$modelVar});

        \$this->delete{$name}->handle(['id' => \${$modelVar}->id]);

        return ApiResponse::success(null, __('{$routeName}.deleted'));
    }
}
STUB;
    }
}
