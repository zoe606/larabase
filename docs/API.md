# API

## Authentication

Public routes:

- `GET /api/ping`: returns `{"status":"ok"}` without database access.
- `GET /api/health`: reports service status.
- `POST /api/v1/auth/register`: accepts name, email, password, and password_confirmation.
- `POST /api/v1/auth/login`: accepts email and password.

Send `Accept: application/json`. Protected routes accept
`Authorization: Bearer <token>`. Use `GET /api/v1/auth/user` for the current user
and `POST /api/v1/auth/logout` to revoke the current token.

## Platform resources

Users, roles, permissions, and menus expose conventional REST resource endpoints
under `/api/v1`. Notifications expose list, mark-read, and mark-all-read endpoints.
Resource authorization remains enforced by policies and Form Requests.

Successful responses use `success`, `message`, and `data`. Paginated responses also
include `meta` and `links`. Errors use `success`, `message`, and `errors`.
Validation errors return HTTP 422. Authentication and authorization failures return
HTTP 401 and 403.

## OpenAPI

```bash
php artisan scramble:export --path=api.json
```

The checked-in `api.json` is the API baseline. CI regenerates it and compares the
content after normalizing server URLs. Local documentation is available at
`/docs/api`; Scramble restricts access outside permitted environments.

## CORS

`CORS_ALLOWED_ORIGINS` is a comma-separated list of frontend origins. The local
example permits all origins. Production examples use an explicit application
origin. API requests are rate limited by the configured middleware.
