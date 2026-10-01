# Extending Larabase

The Users feature is the reference CRUD implementation. Its controller uses Form
Requests, `app/Actions/User`, `app/Queries/User`, `UserPolicy`, and `UserResource`.
Its React forms and list live in `resources/js/pages/users`.

For a new feature, create its model and migration, validation requests, policy,
Actions, Queries, routes, pages, and tests. Register the policy in the application's
provider and add the required permissions and menu entries in application seeders.
Use `php artisan make:crud Post` to generate the backend files. Review every
generated placeholder before registering the feature.

Keep reusable platform code close to the starter. Put application registrations
in a dedicated provider, route file, and seeder when the application grows.
Document the files your application overrides so future starter updates can be
reviewed deliberately.

API clients use Sanctum and the standard response envelopes. Generic database
notifications use the authenticated user's notification relation. Reverb is
configured centrally in `resources/js/app.tsx`; add application events explicitly.

Write tests for the feature behavior and access rules. Run focused tests first,
then the verification commands in the README.
