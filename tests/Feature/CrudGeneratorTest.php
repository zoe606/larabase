<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->originalAppPath = app_path();
    $this->generatedAppPath = storage_path('framework/testing/generator-'.bin2hex(random_bytes(8)));
    File::ensureDirectoryExists($this->generatedAppPath);
    app()->useAppPath($this->generatedAppPath);
});

afterEach(function () {
    app()->useAppPath($this->originalAppPath);
    File::deleteDirectory($this->generatedAppPath);
});

it('generates valid PHP with matching class namespaces and directories', function () {
    $this->artisan('make:crud', ['name' => 'Article'])->assertSuccessful();

    $paths = [
        'Actions/Article/CreateArticle.php',
        'Actions/Article/UpdateArticle.php',
        'Actions/Article/DeleteArticle.php',
        'Queries/Article/ListArticlesQuery.php',
        'Queries/Article/GetArticleQuery.php',
        'Http/Requests/Article/StoreArticleRequest.php',
        'Http/Requests/Article/UpdateArticleRequest.php',
        'Http/Resources/ArticleResource.php',
        'Policies/ArticlePolicy.php',
        'Http/Controllers/ArticleController.php',
        'Http/Controllers/Api/V1/ArticleController.php',
    ];
    foreach ($paths as $path) {
        expect(File::exists(app_path($path)))->toBeTrue($path);
        $contents = File::get(app_path($path));
        expect(token_get_all($contents, TOKEN_PARSE))->not->toBeEmpty();
        $namespace = 'App\\'.str_replace('/', '\\', dirname($path));
        expect($contents)->toContain('namespace '.$namespace.';');
    }
});

it('preserves existing controller files unless force is requested', function () {
    $this->artisan('make:crud', ['name' => 'Article'])->assertSuccessful();
    $path = app_path('Http/Controllers/ArticleController.php');
    File::put($path, '<?php // Existing controller');

    $this->artisan('make:crud', ['name' => 'Article'])->assertSuccessful();
    expect(File::get($path))->toBe('<?php // Existing controller');
    $this->artisan('make:crud', ['name' => 'Article', '--force' => true])->assertSuccessful();
    expect(File::get($path))->toContain('class ArticleController');
});

it('generates only the requested controller surface', function (string $option, string $expected, string $excluded) {
    $this->artisan('make:crud', ['name' => 'Article', $option => true])->assertSuccessful();
    expect(File::exists(app_path($expected)))->toBeTrue()
        ->and(File::exists(app_path($excluded)))->toBeFalse();
})->with([
    ['--api', 'Http/Controllers/Api/V1/ArticleController.php', 'Http/Controllers/ArticleController.php'],
    ['--web', 'Http/Controllers/ArticleController.php', 'Http/Controllers/Api/V1/ArticleController.php'],
]);

it('rejects overwriting existing action and query files', function (string $command, string $path) {
    $this->artisan($command, ['name' => 'Article/Example'])->assertSuccessful();
    $contents = File::get(app_path($path));
    $this->artisan($command, ['name' => 'Article/Example'])->assertFailed();
    expect(File::get(app_path($path)))->toBe($contents);
})->with([
    ['make:action', 'Actions/Article/Example.php'],
    ['make:query', 'Queries/Article/Example.php'],
]);
