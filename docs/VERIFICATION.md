# Larabase Verification

Verified on 2026-10-01 after upgrading to Laravel 13. The extracted product source
was commit `9034e6956992c086087835a31c9f040b51775610`.

## Release state

Fresh history, public template publication, and release `v1.0.0` were approved
before this verification. The source has no inherited product Git history.
Hosted GitHub Actions and the hosted container scan run after publication.

The release source excludes installed dependencies, local environments,
databases, uploads, compiled assets, logs, caches, and the extraction marker.
Only `.env.example` and `.env.production.example` are included as environment files.

## Runtime

| Component | Verified version or configuration |
| --- | --- |
| PHP | Herd PHP 8.4.25 |
| Composer | 2.10.2 |
| Laravel | 13.34.0 |
| Pest / PHPUnit | 4.7.8 / 12.5.33 |
| Pint | 1.27.1; the 1.27 patch series preserves the existing formatting rules |
| Node.js | Native 24.14.1; Node 24 in the deployment image |
| pnpm | 10.30.1 |
| PostgreSQL | 17.7 in an isolated OrbStack container |
| Redis | Redis 7 in an isolated OrbStack container |
| Local application | Laravel Herd |
| Fast test database | SQLite in memory |

## Laravel 13 compatibility

Framework, Tinker, Boost, Pest, and PHPUnit use the supported major versions.
Backup uses version 10. Debugbar uses `fruitcake/laravel-debugbar` version 4.
Inertia remains on version 2. Other application packages retain their existing
major versions.

Sanctum references `PreventRequestForgery`. This new starter uses JSON session
serialization. Cache deserialization explicitly permits only the models and
collections used by settings, permissions, and menus. Database cache round trips
and rejection of an unlisted object are covered by `CacheSerializationTest`.
Frontend `ziggy-js` matches the PHP Ziggy version, 2.6.4.

See the official [Laravel 13 upgrade guide](https://laravel.com/framework/docs/13.x/upgrade).

## Backend

All checks below exited with code `0`.

| Check | Result |
| --- | --- |
| Fresh Composer installation | Passed in the clean room using the updated lockfile |
| `composer validate --strict` | Valid metadata and lockfile |
| `composer audit` | No advisories or abandoned dependencies |
| `./vendor/bin/pint --test` | Passed |
| `./vendor/bin/phpstan analyse --memory-limit=2G` | Passed, 122 application files |
| Parallel Pest suite with coverage | 534 tests, 2,064 assertions; 89.4% statement coverage |
| PostgreSQL workflow selection | 163 tests, 893 assertions passed |
| Domain isolation check | Passed |
| OpenAPI export and normalized comparison | Passed, 19 API paths |
| Controller route reflection check | Every registered method resolves |
| `backup:run --only-db --disable-notifications` | SQLite backup completed successfully |

Parallel coverage used Xdebug with the extension passed explicitly to child PHP
processes. It covered 1,692 of 1,891 statements. The coverage gate remains 85%.
Application generators remain in the coverage scope.

PostgreSQL checks include authentication, settings, API contracts, files, folder
filtering, production seeder safety, platform CRUD, and cache serialization.
Only the dedicated verification database was migrated and seeded.

File-folder filtering accepts both string and legacy numeric JSON folder IDs.
Media routes retain `index`, `store`, `update`, and `destroy`. Resource routes omit
`show` when the controller has no show method. Production seeding creates no
default account. Local seeding is idempotent.

## Frontend

A fresh source copy was installed with Node.js 24 and the frozen pnpm lockfile.
It contained no PHP `vendor` directory. Ziggy is an npm dependency, so frontend
CI does not depend on Composer installation.

All commands below exited with code `0`:

```bash
pnpm install --frozen-lockfile
pnpm run lint:check
pnpm run format:check
pnpm exec tsc --noEmit
pnpm test
pnpm build
pnpm audit --audit-level=high
```

Vitest passed 16 tests across 3 files. Production page imports exclude test and
spec files. Audit found no high or critical advisories. Five moderate and one
low advisory remain. The build reports a main-bundle size warning.

## Herd and OrbStack

Herd served `http://larabase-starter.test` with PostgreSQL and Redis in OrbStack.
Login, dashboard, and health checks passed. An invalid CSRF token returned 419.
A valid token allowed login, the JSON session persisted, and logout succeeded.
Health reported working PostgreSQL, Redis cache, and Redis queue.

Local Compose starts PostgreSQL and Redis by default. Their host ports bind to
localhost. Application and queue containers require the optional `container-app`
profile. `composer run dev:herd` starts queue, logs, Vite, and Reverb. Herd handles
HTTP. See [Installation](INSTALLATION.md).

Temporary verification containers were stopped after the checks. The local Herd
preview uses SQLite and built assets so it remains available independently.

## Optional deployment image

The Laravel 13 image build passed using OrbStack's container engine:

```text
sha256:0bf55190e1f6d746a2025d4296abc786212f50cd6fc2c88971c0f1a1b3f9204a
```

The application image is an optional deployment foundation. Local development
uses Herd for PHP. Hosted vulnerability scan results are separate from this
local build result.

## Reproduce checks

Follow [Installation](INSTALLATION.md), then run the commands in [README](../README.md).
Coverage requires PCOV or Xdebug. PostgreSQL checks require a dedicated empty
verification database. Do not run database reset commands against application data.
