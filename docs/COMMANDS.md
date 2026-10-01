# Commands

## Development

- `herd link larabase-starter --isolate=8.4`: link the application in Herd.
- `docker compose up -d postgres redis`: start database services in OrbStack.
- `composer run dev:herd`: start the queue listener, logs, Vite, and Reverb while Herd serves HTTP.
- `composer run dev`: start the built-in HTTP server and development services without Herd.
- `php artisan route:list`: inspect registered routes.
- `php artisan about`: inspect runtime configuration.
- `php artisan typescript:transform`: regenerate PHP enum types.
- `php artisan scramble:export --path=api.json`: regenerate OpenAPI.

## Generators

```bash
php artisan make:action --help
php artisan make:query --help
php artisan make:crud --help
php artisan make:crud Post
```

The CRUD generator writes Actions, Queries, Form Requests, an API Resource,
policy, and controllers. Create the model and migration, then register routes,
permissions, menus, and frontend pages. Replace generated validation and
authorization placeholders before using the feature.

## Operations

- `php artisan queue:work --tries=3`: process queued jobs.
- `php artisan schedule:run`: execute due schedules.
- `php artisan reverb:start`: start the broadcast server.
- `php artisan backup:run --only-db`: create a database backup.
- `php artisan storage:link`: expose public-disk files.
