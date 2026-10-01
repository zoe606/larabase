# Larabase Agent Conventions

Use PHP 8.4 and the existing Laravel, React, and Inertia conventions. Controllers
validate and authorize requests. Actions perform writes. Queries perform reads.
Keep platform code independent of application features.

Read relevant files before editing. Preserve unrelated work. Use `apply_patch`
for manual changes. Write comments and documentation in plain English.

Run focused tests, then the verification commands in README.md. Backend tests use
`./vendor/bin/pest --parallel`. Architecture tests are included in the suite.
Never commit environment files, credentials, or runtime state. Publishing and
release creation require operator approval.
