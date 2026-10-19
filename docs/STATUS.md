# Status

This page lists planned work that is not finished. Read it before you start a
dependency upgrade. Update it in the same pull request that changes the status
of an item.

Last reviewed: 2026-10-19.

## Frontend upgrade track

Do the items in this order. Each item is one pull request. Each pull request
must pass the complete CI workflow and the manual check at the end of this page.

| # | Item | Status |
| --- | --- | --- |
| 1 | React 19.3 and TypeScript 6 | Planned |
| 2 | Inertia 3 | Planned |
| 3 | Vite 8 | Planned |
| 4 | Vite+ evaluation | Planned, blocked by item 3 |
| 5 | TypeScript 7 | Blocked by typescript-eslint |

### 1. React 19.3 and TypeScript 6

Current: React 19.2.3, TypeScript 5.9.3.

- Update `react`, `react-dom`, `@types/react` and `@types/react-dom` to the
  latest 19.3 release. This is a minor release.
- Update `typescript` to `^6.0`. Do not use TypeScript 7 yet. typescript-eslint
  8 supports TypeScript up to 6.0 only.
- Run `pnpm exec tsc --noEmit` and fix every error. TypeScript 6 deprecates
  some `tsconfig.json` options. Check the `baseUrl` option. The `paths` option
  works without it.

Done when `tsc`, ESLint, Vitest and the build pass with no new warnings.

### 2. Inertia 3

Current: `@inertiajs/react` and `@inertiajs/core` 2.3.28,
`inertiajs/inertia-laravel` 2.0.28. Read the
[upgrade guide](https://inertiajs.com/docs/v3/getting-started/upgrade-guide).

- Update the npm packages to `^3.0` and the Composer package to `^3.0`.
- Publish the Inertia config again and keep the local changes. The keys under
  `testing` moved to `pages`.
- In `resources/views/app.blade.php`, change `<title inertia>` to
  `<title data-inertia>`.
- Inertia 3 no longer installs Axios. Larabase does not import Axios, so no
  change is expected. Check this again before you start.
- `useForm().processing` now stays `true` until the request finishes. Check
  each form that reads `processing`. 18 files under `resources/js` used it on
  2026-10-19.
- Check that no code uses `Inertia::lazy()`, `router.cancel()`,
  `hideProgress()`, `revealProgress()`, the `invalid` or `exception` events,
  or the `future` option. None were found on 2026-10-19.
- Run `php artisan view:clear` after the update.
- Update the Inertia line in `README.md` and in `docs/VERIFICATION.md`.

Done when the backend and frontend suites pass and every page in the manual
check loads with no console errors.

### 3. Vite 8

Current: Vite 6.4, `laravel-vite-plugin` 1.3, `@vitejs/plugin-react` 4.7.

- Update `vite` to `^8.0`, `laravel-vite-plugin` to `^3.0` (it requires Vite 8)
  and `@vitejs/plugin-react` to `^5.2` or `^6.0`.
- `@tailwindcss/vite` 4.3 already supports Vite 8.
- Check that the installed Vitest version supports Vite 8. Vitest 5 is
  available. Upgrade to it in this item only if Vitest 4 does not work.
- Run `pnpm build` and `pnpm build:ssr`. Compare the build output size with
  the previous build.

Done when the dev server, the production build and the SSR build work.

### 4. Vite+ evaluation

Vite+ (`vp`) combines Vite, Vitest, Oxlint, Oxfmt and a task runner. It
requires Vite 7 or later and Vitest 4 or later, so item 3 comes first.

This item produces a recommendation, not a merge. Run `vp migrate` on a test
branch and answer these questions:

- Can Oxlint replace the current ESLint rules, including `react-hooks` and
  `typescript-eslint`?
- Can Oxfmt replace Prettier with the Tailwind class sorting and import
  sorting plugins, with no large formatting changes?
- Does `laravel-vite-plugin` work through `vp`?
- Can the Husky and lint-staged hooks move to `vp hooks` and `vp staged`?
- How long do CI lint and test jobs take compared with now?

Record the answers here. Adopt Vite+ only if lint and format rules stay the
same or get stricter.

### 5. TypeScript 7

TypeScript 7 is the latest release. typescript-eslint 8 does not support it.
Check typescript-eslint again before you start this item.

## Manual check

After each item:

1. Run `composer run dev` and open the welcome page, login, register, the
   dashboard and every sidebar page.
2. Check the browser console for errors.
3. Submit one form on a create page and one on an edit page.
4. Check light mode, dark mode and a phone width.
