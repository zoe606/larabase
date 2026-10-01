# Larabase

## What You Get

Authentication, profiles, users, roles, permissions, menus, application settings,
audit logs, files, backups, and database notifications. The Users feature is a
working example of the application CRUD conventions.

## Technology

Laravel 13, PHP 8.4, React 19, Inertia 2, TypeScript, Tailwind 4, and ShadCN UI.
Use Node.js 24 and pnpm 10.30.1. SQLite and PostgreSQL 17 are the database targets.

## Quick Start

```bash
git clone https://github.com/zoe606/larabase.git
cd larabase
composer install --no-interaction --prefer-dist
pnpm install --frozen-lockfile
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
pnpm build
herd link larabase-starter --isolate=8.4
composer run dev:herd
```

Open `http://larabase-starter.test`. This quick start uses SQLite. The standard
local setup uses Laravel Herd for the application and OrbStack for PostgreSQL
and Redis. See [Installation](docs/INSTALLATION.md).

## Default Local Account

Local and testing environments create `admin@admin.com` with password `admin123`.
Production seeding never creates this account. Create a production administrator
using the procedure in [Deployment](docs/DEPLOYMENT.md).

## Verification

```bash
composer validate --strict
composer audit
./vendor/bin/pint --test
./vendor/bin/phpstan analyse --memory-limit=2G
./vendor/bin/pest --parallel --coverage --min=85
pnpm audit --audit-level=high
pnpm run lint:check
pnpm run format:check
pnpm exec tsc --noEmit
pnpm test
pnpm build
bash .github/scripts/check-domain-isolation.sh
```

Coverage requires PCOV or Xdebug. Release evidence is recorded in
[Verification](docs/VERIFICATION.md).

## Architecture

Controllers validate and authorize requests. Actions perform writes. Queries
perform reads. Policies enforce access. See [Architecture](docs/ARCHITECTURE.md).

## API Foundation

Sanctum bearer-token authentication, standard response envelopes, pagination,
health endpoints, and generated OpenAPI. See [API](docs/API.md).

## Deployment

PostgreSQL, Redis, queue workers, scheduler, Reverb, and optional application
container configuration are included. See [Deployment](docs/DEPLOYMENT.md).

## Extending Larabase

Add application code using the existing conventions. See
[Extending](docs/EXTENDING.md) and [Commands](docs/COMMANDS.md).

## Upgrade Policy

Read release notes and import selected changes in a dedicated branch. See
[Upgrading](docs/UPGRADING.md).

## Contributing

See [Contributing](CONTRIBUTING.md).

## Security

See [Security](SECURITY.md).

## License

[MIT](LICENSE).
