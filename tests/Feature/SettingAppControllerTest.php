<?php

declare(strict_types=1);

use App\Models\SettingApp;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->user = User::factory()->create();
});

/*
|--------------------------------------------------------------------------
| Edit Tests
|--------------------------------------------------------------------------
*/

describe('edit', function () {
    it('redirects to login for unauthenticated users', function () {
        $response = $this->get(route('setting.edit'));

        $response->assertRedirect(route('login'));
    });

    it('returns inertia response for authenticated user', function () {
        $response = $this->actingAs($this->user)
            ->get(route('setting.edit'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settingapp/Form')
                ->has('setting')
            );
    });

    it('returns null setting when no setting exists', function () {
        $response = $this->actingAs($this->user)
            ->get(route('setting.edit'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settingapp/Form')
                ->where('setting', null)
            );
    });

    it('returns existing setting data', function () {
        SettingApp::create([
            'nama_app' => 'My Application',
            'deskripsi' => 'A great app',
            'warna' => '#FF0000',
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('setting.edit'));

        $response->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settingapp/Form')
                ->where('setting.nama_app', 'My Application')
                ->where('setting.deskripsi', 'A great app')
                ->where('setting.warna', '#FF0000')
            );
    });
});

/*
|--------------------------------------------------------------------------
| Update Tests
|--------------------------------------------------------------------------
*/

describe('update', function () {
    it('redirects to login for unauthenticated users', function () {
        $response = $this->post(route('setting.update'), [
            'nama_app' => 'Test App',
        ]);

        $response->assertRedirect(route('login'));
    });

    it('creates setting with valid data when none exists', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'New Application',
                'deskripsi' => 'App description',
                'warna' => '#0000FF',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Pengaturan berhasil disimpan.');

        $this->assertDatabaseHas('settingapp', [
            'nama_app' => 'New Application',
            'deskripsi' => 'App description',
            'warna' => '#0000FF',
        ]);
    });

    it('updates existing setting with valid data', function () {
        SettingApp::create([
            'nama_app' => 'Old Name',
            'deskripsi' => 'Old description',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Updated Name',
                'deskripsi' => 'Updated description',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success', 'Pengaturan berhasil disimpan.');

        $this->assertDatabaseHas('settingapp', [
            'nama_app' => 'Updated Name',
            'deskripsi' => 'Updated description',
        ]);

        // Ensure only one record exists
        expect(SettingApp::count())->toBe(1);
    });

    it('validates nama_app is required', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'deskripsi' => 'Some description',
            ]);

        $response->assertSessionHasErrors(['nama_app']);
    });

    it('validates nama_app must be a string', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => ['array'],
            ]);

        $response->assertSessionHasErrors(['nama_app']);
    });

    it('validates nama_app max length is 255', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors(['nama_app']);
    });

    it('accepts nama_app at exactly 255 characters', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => str_repeat('a', 255),
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('settingapp', [
            'nama_app' => str_repeat('a', 255),
        ]);
    });

    it('allows nullable deskripsi', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'deskripsi' => null,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');
    });

    it('validates deskripsi must be a string when provided', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'deskripsi' => ['not', 'a', 'string'],
            ]);

        $response->assertSessionHasErrors(['deskripsi']);
    });

    it('validates warna max length is 20', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'warna' => str_repeat('a', 21),
            ]);

        $response->assertSessionHasErrors(['warna']);
    });

    it('allows nullable warna', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'warna' => null,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');
    });

    it('validates seo must be an array when provided', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'seo' => 'not-an-array',
            ]);

        $response->assertSessionHasErrors(['seo']);
    });

    it('accepts valid seo array', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'seo' => ['title' => 'SEO Title', 'description' => 'SEO Desc'],
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $setting = SettingApp::first();
        expect($setting->seo)->toBe(['title' => 'SEO Title', 'description' => 'SEO Desc']);
    });

    it('saves data with minimal required fields only', function () {
        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Minimal App',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $setting = SettingApp::first();
        expect($setting)->not->toBeNull()
            ->and($setting->nama_app)->toBe('Minimal App')
            ->and($setting->deskripsi)->toBeNull()
            ->and($setting->warna)->toBeNull();
    });
});

/*
|--------------------------------------------------------------------------
| File Upload Tests
|--------------------------------------------------------------------------
*/

describe('file uploads', function () {
    beforeEach(function () {
        Storage::fake('public');
    });

    it('uploads logo file successfully', function () {
        $logo = fakeImage('logo.png');

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'App With Logo',
                'logo' => $logo,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $setting = SettingApp::first();
        expect($setting->logo)->not->toBeNull()
            ->and($setting->logo)->toContain('logo/');

        Storage::disk('public')->assertExists($setting->logo);
    });

    it('uploads favicon file successfully', function () {
        $favicon = fakeImage('favicon.png');

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'App With Favicon',
                'favicon' => $favicon,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $setting = SettingApp::first();
        expect($setting->favicon)->not->toBeNull()
            ->and($setting->favicon)->toContain('favicon/');

        Storage::disk('public')->assertExists($setting->favicon);
    });

    it('uploads both logo and favicon simultaneously', function () {
        $logo = fakeImage('logo.png');
        $favicon = fakeImage('favicon.png');

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Full App',
                'logo' => $logo,
                'favicon' => $favicon,
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $setting = SettingApp::first();
        expect($setting->logo)->not->toBeNull()
            ->and($setting->favicon)->not->toBeNull();

        Storage::disk('public')->assertExists($setting->logo);
        Storage::disk('public')->assertExists($setting->favicon);
    });

    it('validates logo must be an image', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'not an image');
        $file = new UploadedFile($tempFile, 'document.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'logo' => $file,
            ]);

        $response->assertSessionHasErrors(['logo']);
    });

    it('validates favicon must be an image', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, 'not an image');
        $file = new UploadedFile($tempFile, 'document.pdf', 'application/pdf', null, true);

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'favicon' => $file,
            ]);

        $response->assertSessionHasErrors(['favicon']);
    });

    it('validates logo max size is 2MB', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, str_repeat('x', 3 * 1024 * 1024));
        $file = new UploadedFile($tempFile, 'large.png', 'image/png', null, true);

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'logo' => $file,
            ]);

        $response->assertSessionHasErrors(['logo']);
    });

    it('validates favicon max size is 1MB', function () {
        $tempFile = tempnam(sys_get_temp_dir(), 'test_');
        file_put_contents($tempFile, str_repeat('x', 2 * 1024 * 1024));
        $file = new UploadedFile($tempFile, 'large.png', 'image/png', null, true);

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Test App',
                'favicon' => $file,
            ]);

        $response->assertSessionHasErrors(['favicon']);
    });

    it('preserves existing logo when no new logo is uploaded', function () {
        $setting = SettingApp::create([
            'nama_app' => 'Original',
            'logo' => 'logo/original.png',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Updated Name',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $setting->refresh();
        expect($setting->nama_app)->toBe('Updated Name')
            ->and($setting->logo)->toBe('logo/original.png');
    });

    it('preserves existing favicon when no new favicon is uploaded', function () {
        $setting = SettingApp::create([
            'nama_app' => 'Original',
            'favicon' => 'favicon/original.png',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('setting.update'), [
                'nama_app' => 'Updated Name',
            ]);

        $response->assertRedirect()
            ->assertSessionHas('success');

        $setting->refresh();
        expect($setting->nama_app)->toBe('Updated Name')
            ->and($setting->favicon)->toBe('favicon/original.png');
    });
});
