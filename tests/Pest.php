<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Create a fake image file that doesn't require GD extension.
 * Uses a tiny valid PNG (1x1 transparent pixel).
 */
function fakeImage(string $name = 'test.png'): Illuminate\Http\UploadedFile
{
    // Tiny 1x1 transparent PNG (67 bytes)
    $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==');

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');
    file_put_contents($tempFile, $pngData);

    return new Illuminate\Http\UploadedFile(
        $tempFile,
        $name,
        'image/png',
        null,
        true
    );
}

/**
 * Check if GD extension is available with image manipulation support.
 */
function hasGd(): bool
{
    // Check both that GD is loaded AND that image functions work
    // Some environments have GD loaded but functions are not available
    return extension_loaded('gd')
        && function_exists('imagecreatefromjpeg')
        && function_exists('imagejpeg')
        && function_exists('imagecreatefrompng')
        && function_exists('imagepng');
}

/**
 * Skip test if GD extension is not available.
 */
function skipIfNoGd(): void
{
    if (! hasGd()) {
        test()->markTestSkipped('GD extension is not available');
    }
}

/**
 * Create a user with refreshed profile relationship.
 */
function createUserWithProfile(array $attributes = []): App\Models\User
{
    $user = App\Models\User::factory()->create($attributes);
    $user->refresh(); // Refresh to load the auto-created profile

    return $user;
}
