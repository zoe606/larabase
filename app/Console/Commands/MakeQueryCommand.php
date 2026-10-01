<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeQueryCommand extends Command
{
    protected $signature = 'make:query {name : The name of the query (e.g., User/ListUsersQuery)}';

    protected $description = 'Create a new query class';

    public function __construct(
        private readonly Filesystem $files
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $name = $this->argument('name');
        $name = str_replace('/', '\\', $name);

        $parts = explode('\\', $name);
        $className = array_pop($parts);
        $namespace = implode('\\', $parts);

        $stub = $this->getStub();
        $stub = str_replace('{{ namespace }}', $namespace ? "\\{$namespace}" : '', $stub);
        $stub = str_replace('{{ class }}', $className, $stub);

        $relativePath = str_replace('\\', '/', $name);
        $path = app_path("Queries/{$relativePath}.php");
        $directory = dirname($path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($this->files->exists($path)) {
            $this->error("Query already exists: {$path}");

            return self::FAILURE;
        }

        $this->files->put($path, $stub);

        $this->info("Query created successfully: {$path}");

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        $stubPath = base_path('stubs/query.stub');

        if ($this->files->exists($stubPath)) {
            return $this->files->get($stubPath);
        }

        return <<<'STUB'
<?php

namespace App\Queries{{ namespace }};

use App\Contracts\QueryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class {{ class }} implements QueryInterface
{
    public function handle(array $filters = []): LengthAwarePaginator|Collection
    {
        // TODO: Implement query logic
    }
}
STUB;
    }
}
