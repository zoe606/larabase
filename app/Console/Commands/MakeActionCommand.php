<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name : The name of the action (e.g., User/CreateUser)}';

    protected $description = 'Create a new action class';

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
        $path = app_path("Actions/{$relativePath}.php");
        $directory = dirname($path);

        if (! $this->files->isDirectory($directory)) {
            $this->files->makeDirectory($directory, 0755, true);
        }

        if ($this->files->exists($path)) {
            $this->error("Action already exists: {$path}");

            return self::FAILURE;
        }

        $this->files->put($path, $stub);

        $this->info("Action created successfully: {$path}");

        return self::SUCCESS;
    }

    protected function getStub(): string
    {
        $stubPath = base_path('stubs/action.stub');

        if ($this->files->exists($stubPath)) {
            return $this->files->get($stubPath);
        }

        return <<<'STUB'
<?php

namespace App\Actions{{ namespace }};

use App\Contracts\ActionInterface;
use Illuminate\Support\Facades\DB;

class {{ class }} implements ActionInterface
{
    public function handle(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            // TODO: Implement action logic
        });
    }
}
STUB;
    }
}
