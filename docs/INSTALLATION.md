# Installation

Use PHP 8.4, Composer 2, Node.js 24, and pnpm 10.30.1. PHP needs ctype, curl,
dom, exif, fileinfo, gd, intl, mbstring, openssl, PDO, pdo_sqlite, tokenizer,
xml, and zip. PostgreSQL needs pdo_pgsql. Redis needs phpredis.

## Laravel Herd and OrbStack

Laravel Herd serves the application. OrbStack runs PostgreSQL 17 and Redis 7.
The Docker CLI below connects to OrbStack. Docker Desktop is not required.
The default Compose services contain no application server or queue worker.

```bash
git clone https://github.com/zoe606/larabase.git
cd larabase
composer install --no-interaction --prefer-dist
pnpm install --frozen-lockfile
cp .env.example .env
php artisan key:generate
herd link larabase-starter --isolate=8.4
docker compose up -d postgres redis
```

Use a unique Herd site name if another application already uses `larabase.test`.
Set these values in `.env` before running migrations:

```dotenv
APP_URL=http://larabase-starter.test
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=larabase
DB_USERNAME=larabase
DB_PASSWORD=larabase_secret
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

`larabase_secret` is the local Compose example password. Use different credentials
for deployment. Database ports bind to localhost. If a port is already occupied,
set `POSTGRES_PORT` or `REDIS_PORT` in `.env` and use the same value for `DB_PORT`
or `REDIS_PORT`. Use a new empty database for the initial installation.

```bash
php artisan migrate --seed
php artisan storage:link
pnpm build
composer run dev:herd
```

Open `http://larabase-starter.test`. Herd handles HTTP. `dev:herd` starts the queue
listener, logs, Vite, and Reverb. The local administrator is `admin@admin.com`
with password `admin123`. The account is created only in local and testing environments.

## SQLite

SQLite is available for a local installation without database containers. Keep
`DB_CONNECTION=sqlite` and the database-backed session, cache, and queue defaults.
After installing dependencies and generating the application key, run:

```bash
touch database/database.sqlite
php artisan migrate --seed
php artisan storage:link
pnpm build
composer run dev:herd
```

Use the Herd link above. Without Herd, set `APP_URL=http://localhost:8000` and
run `composer run dev` to start the built-in HTTP server and development services.

## Broadcasting and API origins

Set `BROADCAST_CONNECTION=reverb` to use broadcasting. Replace the local Reverb
example credentials before deployment. Add the actual frontend origins to
`CORS_ALLOWED_ORIGINS` when another origin consumes the API.

## Optional application containers

`docker compose --profile container-app up -d --build` starts the application and
queue containers in addition to the databases. This profile is optional when
Herd serves the application. See [Deployment](DEPLOYMENT.md) for production setup.
