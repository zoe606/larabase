# Deployment

## Configuration

Copy `.env.production.example` to the server environment and fill in the database,
mail, application key, public URL, allowed origins, and Reverb credentials.
Keep `APP_ENV=production` and `APP_DEBUG=false`. Generate a unique key with
`php artisan key:generate --force`. Store runtime secrets outside source control.

Use PostgreSQL 17 and Redis. Set the queue and session/cache stores to Redis when
using the provided production services. Configure SMTP for verification and
password-reset messages. Set the Sentry DSN and sampling rate when monitoring is used.

## Optional application containers

Local development uses Laravel Herd and OrbStack database services. See
[Installation](INSTALLATION.md). The production container configuration is an
optional deployment foundation.

```bash
docker build -t larabase:production .
docker compose -f docker-compose.prod.yml --env-file .env.production config --quiet
docker compose -f docker-compose.prod.yml --env-file .env.production up -d --build
```

Supply the `VITE_REVERB_*` build arguments when broadcasting is enabled; frontend
environment values are compiled during the build. The application listens on
container port 8080. The reference configuration exposes the HTTP service and
separate queue, scheduler, and Reverb services. Put HTTPS in front of the application
and Reverb. Keep storage and database volumes persistent.

## Database and administrator

Run `php artisan migrate --force` and `php artisan db:seed --force` during initial
deployment. Production seeding installs permissions and menus and creates no
default user. Provision the administrator explicitly:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Administrator',
    'email' => 'your-admin@example.com',
    'password' => Illuminate\Support\Facades\Hash::make('replace-with-a-unique-strong-password'),
]);
$user->assignRole('admin');
```

## Services

Run a queue worker for queued notifications and jobs. Invoke
`php artisan schedule:run` every minute. Run Reverb when broadcasting is enabled.
Configure Pulse access for administrators and supply a Sentry DSN when error
monitoring is needed.

After configuring the environment, run `config:cache`, `route:cache`, `view:cache`,
and `event:cache`. Run `php artisan storage:link` for public files.

## Backups and health

Backups use the configured `backup.backup.name`. The file manager uses the configured
storage disk. Schedule backups, copy them to storage outside the application host,
and test restoration before relying on them.

`/api/ping` is the process health endpoint. `/api/health` checks dependent services.
Check both after deploying and verify that the queue worker is running.
