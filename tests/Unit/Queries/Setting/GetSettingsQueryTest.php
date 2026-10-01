<?php

use App\Models\SettingApp;
use App\Queries\Setting\GetSettingsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

it('returns setting app singleton record', function () {
    $setting = SettingApp::create([
        'nama_app' => 'Test Application',
        'deskripsi' => 'Test Description',
        'logo' => 'logo.png',
        'favicon' => 'favicon.ico',
        'warna' => '#3B82F6',
    ]);

    $query = new GetSettingsQuery;
    $result = $query->handle();

    expect($result)->toBeInstanceOf(SettingApp::class)
        ->and($result->nama_app)->toBe('Test Application')
        ->and($result->deskripsi)->toBe('Test Description')
        ->and($result->logo)->toBe('logo.png')
        ->and($result->favicon)->toBe('favicon.ico')
        ->and($result->warna)->toBe('#3B82F6');
});

it('returns null when no settings exist', function () {
    $query = new GetSettingsQuery;
    $result = $query->handle();

    expect($result)->toBeNull();
});

it('caches settings for 24 hours', function () {
    SettingApp::create([
        'nama_app' => 'Cached App',
        'deskripsi' => 'Cached Description',
    ]);

    $query = new GetSettingsQuery;

    // First call should cache the result
    $result1 = $query->handle();

    // Verify cache has the value
    expect(Cache::has('app_settings'))->toBeTrue();

    // Second call should return cached value
    $result2 = $query->handle();

    expect($result1->id)->toBe($result2->id)
        ->and($result1->nama_app)->toBe($result2->nama_app);
});

it('returns cached settings on subsequent calls', function () {
    SettingApp::create([
        'nama_app' => 'Original Name',
        'deskripsi' => 'Original Description',
    ]);

    $query = new GetSettingsQuery;

    // First call - caches the result
    $result1 = $query->handle();
    expect($result1->nama_app)->toBe('Original Name');

    // Update the database directly
    SettingApp::query()->update(['nama_app' => 'Updated Name']);

    // Second call should return cached value (not updated)
    $result2 = $query->handle();
    expect($result2->nama_app)->toBe('Original Name');
});

it('bypasses cache when fresh filter is true', function () {
    SettingApp::create([
        'nama_app' => 'Original Name',
        'deskripsi' => 'Original Description',
    ]);

    $query = new GetSettingsQuery;

    // First call - caches the result
    $result1 = $query->handle();
    expect($result1->nama_app)->toBe('Original Name');

    // Update the database directly
    SettingApp::query()->update(['nama_app' => 'Updated Name']);

    // Call with fresh=true should bypass cache
    $result2 = $query->handle(['fresh' => true]);
    expect($result2->nama_app)->toBe('Updated Name');
});

it('does not bypass cache when fresh filter is false', function () {
    SettingApp::create([
        'nama_app' => 'Original Name',
        'deskripsi' => 'Original Description',
    ]);

    $query = new GetSettingsQuery;

    // First call - caches the result
    $result1 = $query->handle();
    expect($result1->nama_app)->toBe('Original Name');

    // Update the database directly
    SettingApp::query()->update(['nama_app' => 'Updated Name']);

    // Call with fresh=false should return cached value
    $result2 = $query->handle(['fresh' => false]);
    expect($result2->nama_app)->toBe('Original Name');
});

it('clears cache using static clearCache method', function () {
    SettingApp::create([
        'nama_app' => 'Original Name',
        'deskripsi' => 'Original Description',
    ]);

    $query = new GetSettingsQuery;

    // First call - caches the result
    $query->handle();
    expect(Cache::has('app_settings'))->toBeTrue();

    // Clear the cache
    GetSettingsQuery::clearCache();

    expect(Cache::has('app_settings'))->toBeFalse();
});

it('returns fresh data after clearCache is called', function () {
    SettingApp::create([
        'nama_app' => 'Original Name',
        'deskripsi' => 'Original Description',
    ]);

    $query = new GetSettingsQuery;

    // First call - caches the result
    $result1 = $query->handle();
    expect($result1->nama_app)->toBe('Original Name');

    // Update the database directly
    SettingApp::query()->update(['nama_app' => 'Updated Name']);

    // Clear cache
    GetSettingsQuery::clearCache();

    // Next call should return updated data
    $result2 = $query->handle();
    expect($result2->nama_app)->toBe('Updated Name');
});

it('handles empty filters array', function () {
    SettingApp::create([
        'nama_app' => 'Test App',
        'deskripsi' => 'Test Description',
    ]);

    $query = new GetSettingsQuery;
    $result = $query->handle([]);

    expect($result)->toBeInstanceOf(SettingApp::class)
        ->and($result->nama_app)->toBe('Test App');
});

it('handles SEO array attribute', function () {
    $seoData = [
        'title' => 'SEO Title',
        'description' => 'SEO Description',
        'keywords' => ['keyword1', 'keyword2'],
    ];

    SettingApp::create([
        'nama_app' => 'SEO Test App',
        'deskripsi' => 'Description',
        'seo' => $seoData,
    ]);

    $query = new GetSettingsQuery;
    $result = $query->handle();

    expect($result->seo)->toBeArray()
        ->and($result->seo['title'])->toBe('SEO Title')
        ->and($result->seo['description'])->toBe('SEO Description')
        ->and($result->seo['keywords'])->toBe(['keyword1', 'keyword2']);
});

it('handles null SEO attribute', function () {
    SettingApp::create([
        'nama_app' => 'Test App',
        'deskripsi' => 'Description',
        'seo' => null,
    ]);

    $query = new GetSettingsQuery;
    $result = $query->handle();

    expect($result->seo)->toBeNull();
});

it('does not cache null when no settings exist', function () {
    // Note: Laravel's Cache::remember() does not cache null values
    $query = new GetSettingsQuery;

    // First call - returns null but doesn't cache it
    $result1 = $query->handle();
    expect($result1)->toBeNull();

    // Create settings after first call
    SettingApp::create([
        'nama_app' => 'New App',
        'deskripsi' => 'New Description',
    ]);

    // Second call should return the new settings (not cached null)
    $result2 = $query->handle();
    expect($result2)->toBeInstanceOf(SettingApp::class)
        ->and($result2->nama_app)->toBe('New App');
});

it('returns fresh data after creating settings and bypassing cache', function () {
    $query = new GetSettingsQuery;

    // First call - caches null
    $result1 = $query->handle();
    expect($result1)->toBeNull();

    // Create settings
    SettingApp::create([
        'nama_app' => 'New App',
        'deskripsi' => 'New Description',
    ]);

    // Call with fresh=true to get new settings
    $result2 = $query->handle(['fresh' => true]);
    expect($result2)->toBeInstanceOf(SettingApp::class)
        ->and($result2->nama_app)->toBe('New App');
});

it('uses correct cache key', function () {
    SettingApp::create([
        'nama_app' => 'Cache Key Test',
        'deskripsi' => 'Description',
    ]);

    $query = new GetSettingsQuery;
    $query->handle();

    // Check that the specific cache key is used
    expect(Cache::has('app_settings'))->toBeTrue();

    // Clear using the specific key
    Cache::forget('app_settings');

    expect(Cache::has('app_settings'))->toBeFalse();
});

it('re-caches after fresh bypass', function () {
    SettingApp::create([
        'nama_app' => 'Original Name',
        'deskripsi' => 'Description',
    ]);

    $query = new GetSettingsQuery;

    // First call - caches
    $query->handle();

    // Update database
    SettingApp::query()->update(['nama_app' => 'Updated Name']);

    // Fresh call - clears and re-caches
    $result = $query->handle(['fresh' => true]);
    expect($result->nama_app)->toBe('Updated Name');

    // Update database again
    SettingApp::query()->update(['nama_app' => 'Third Name']);

    // Regular call should return cached updated value
    $result2 = $query->handle();
    expect($result2->nama_app)->toBe('Updated Name');
});

it('handles multiple queries with same cached data', function () {
    SettingApp::create([
        'nama_app' => 'Shared Cache Test',
        'deskripsi' => 'Description',
    ]);

    $query1 = new GetSettingsQuery;
    $query2 = new GetSettingsQuery;

    $result1 = $query1->handle();
    $result2 = $query2->handle();

    expect($result1->nama_app)->toBe($result2->nama_app)
        ->and($result1->id)->toBe($result2->id);
});

it('handles settings with all nullable fields as null', function () {
    SettingApp::create([
        'nama_app' => 'Minimal App',
        'deskripsi' => null,
        'logo' => null,
        'favicon' => null,
        'warna' => null,
        'seo' => null,
    ]);

    $query = new GetSettingsQuery;
    $result = $query->handle();

    expect($result)->toBeInstanceOf(SettingApp::class)
        ->and($result->nama_app)->toBe('Minimal App')
        ->and($result->deskripsi)->toBeNull()
        ->and($result->logo)->toBeNull()
        ->and($result->favicon)->toBeNull()
        ->and($result->warna)->toBeNull()
        ->and($result->seo)->toBeNull();
});

it('clears cache and handles null result correctly', function () {
    // Note: Laravel's Cache::remember() does not cache null values
    $query = new GetSettingsQuery;

    // First call with no settings - returns null (not cached)
    $result1 = $query->handle();
    expect($result1)->toBeNull();

    // Clear cache (does nothing since null wasn't cached)
    GetSettingsQuery::clearCache();

    // Still no settings, should return null
    $result2 = $query->handle();
    expect($result2)->toBeNull();
    // Cache won't have the key since null values aren't cached
    expect(Cache::has('app_settings'))->toBeFalse();
});

it('returns first record when multiple records exist', function () {
    // This tests the singleton behavior - should return first record
    SettingApp::create([
        'nama_app' => 'First App',
        'deskripsi' => 'First Description',
    ]);

    // Bypass normal flow to insert second record
    SettingApp::create([
        'nama_app' => 'Second App',
        'deskripsi' => 'Second Description',
    ]);

    // Clear cache to ensure fresh query
    Cache::flush();

    $query = new GetSettingsQuery;
    $result = $query->handle();

    // Should return the first record
    expect($result->nama_app)->toBe('First App');
});
