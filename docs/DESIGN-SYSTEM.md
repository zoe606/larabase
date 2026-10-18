# Design System

The frontend uses Material Design 3 (MD3). The shadcn/Radix components in
`resources/js/components/ui` are restyled to MD3, so their names and props stay
the same. Read this page before you change any UI.

## Colour comes from one seed

`resources/js/lib/md3-theme.ts` builds the light and dark schemes from one seed
colour and writes them as CSS variables on `<html>`. It uses
`@material/material-color-utilities`. It also adds the custom roles `success`,
`warning` and `info`, harmonised to the seed.

The seed is chosen in this order:

1. The viewer's preset (localStorage key `colorSeed`, set by the theme picker).
2. The application setting `warna` (Settings > App Settings > Theme Color).
3. `DEFAULT_SEED` (`#006a60`, teal).

`resources/css/app.css` ships fallback values for the default seed, so the first
paint has the right colours before JavaScript runs. If you change
`DEFAULT_SEED`, update those fallback values too. The test
`app-css-fallback.test.ts` fails when they do not match.

Never set `--primary` or other colour variables by hand in a component. Change
the seed instead.

## Use MD3 colour roles

Use the role classes, such as `bg-*`, `text-*` and `border-*`. Pair each
background with its `on-*` text colour.

| Use | Classes |
| --- | --- |
| Page background | `bg-surface text-on-surface` |
| Raised areas, in order of emphasis | `bg-surface-container-lowest` … `bg-surface-container-highest` |
| Secondary text, icons, captions | `text-on-surface-variant` |
| Main action | `bg-primary text-on-primary` |
| Selected or highlighted area | `bg-primary-container text-on-primary-container`, `bg-secondary-container text-on-secondary-container` |
| Status | `bg-success-container text-on-success-container`, plus the same for `warning`, `info` and `error` |
| Dividers and field outlines | `border-outline-variant`, `border-outline` |

These are forbidden in `resources/js`:

| Do not use | Use instead |
| --- | --- |
| Tailwind palette colours (`bg-gray-100`, `text-red-600`, `text-white`) | A colour role from the table above |
| Hex colours in code (`#3b82f6`) | A colour role, or a CSS variable |
| `text-muted-foreground` | `text-on-surface-variant` |
| `bg-muted`, `bg-accent` | `bg-surface-container` or `bg-surface-container-high` |
| `text-[11px]` and other pixel font sizes | A typescale class |
| `uppercase` micro-labels | `text-label-md` or `text-title-sm` |

The test `md3-migrated-files.test.ts` scans every file in `resources/js` and
fails on these patterns. The rules are in `resources/js/lib/md3-forbidden.ts`.
If a value really must stay, add a comment with the reason:

```tsx
// md3-exception: keyboard keys are shown in upper case
<kbd className="uppercase">{shortcut}</kbd>
```

The comment covers its own line and the next line. Put
`// md3-exception-file: <reason>` at the top of a file to skip the whole file.
Use this only for data, such as a list of seed colours.

## Typescale

Use the MD3 type classes instead of `text-sm font-semibold` and similar.
Each class sets the size, line height, weight and letter spacing.

| Role | Classes | Typical use |
| --- | --- | --- |
| Display | `text-display-lg`, `-md`, `-sm` | Large numbers, landing page headline |
| Headline | `text-headline-lg`, `-md`, `-sm` | Section headings inside a page |
| Title | `text-title-lg`, `-md`, `-sm` | Card titles, group headings, the top app bar title |
| Body | `text-body-lg`, `-md`, `-sm` | Paragraphs and table cells |
| Label | `text-label-lg`, `-md`, `-sm` | Buttons, chips, captions |

## Headings

The top app bar shows the page title as the only `<h1>`. It takes the title
from the last breadcrumb. Pages inside `AppLayout` start at `<h2>`. The test
`page-headings.test.ts` checks this. Auth pages and the welcome page are outside
`AppLayout`, so they have their own `<h1>`.

## Shape, elevation and motion

- Corners: `rounded-shape-xs`, `-sm`, `-md`, `-lg`, `-xl`, `-full`. Cards use
  `rounded-shape-md` or larger. Buttons and chips use `rounded-shape-full`.
- Elevation: `shadow-elevation-0` to `shadow-elevation-5`. Most surfaces use
  a container colour instead of a shadow. Use `shadow-elevation-1` for cards
  that must stand out.
- Motion: `ease-standard`, `ease-emphasized`, `ease-emphasized-decelerate`,
  `ease-emphasized-accelerate`.
- Hover and press feedback: add the `state-layer` utility. It draws an overlay
  in the current text colour. There are no ripple effects.
- Small icon buttons: add `touch-target` so the tap area is at least 48px.

## Components

| Component | Variants |
| --- | --- |
| `Button` | `filled` (default), `tonal`, `outlined`, `text`, `elevated`, `danger`, `field`, `link`. The old names `default`, `secondary`, `outline`, `ghost` and `destructive` still work and map to the MD3 variants. Sizes include `icon` and `icon-sm`. |
| `Fab` | Floating action button for the main action on a phone screen. Pass `label` for an extended FAB. |
| `Card` | `elevated` (default), `filled`, `outlined` |
| `Chip` | `assist` (default) and `filter`. A filter chip takes `selected`. |
| `Badge` | `default`, `secondary`, `outline`, `destructive`, `success`, `warning`, `info` |
| `SelectTrigger` | `variant="chip"` for filter bars on list pages |
| `SearchField` | The search box on list pages |
| `Input`, `Textarea` | Outlined text fields. The label stays above the field. |

Look at the tests in `resources/js/components/ui/__tests__` for examples.

## Layout

- The app shell has a top app bar and a navigation drawer. On tablets the
  drawer collapses to a navigation rail. On phones it opens as a sheet from the
  menu button.
- Sidebar groups come from `resources/js/lib/sidebar-groups.ts`. Add new route
  prefixes there when you add a menu item.
- Page content uses `p-4 md:p-6` and `gap-4 md:gap-6`.
- Auth pages use `AuthSplitLayout`: a branded panel on desktop and a card with
  the form.

## Check your change

1. Run `pnpm test`. The MD3 guard tests must pass.
2. Open the page in light and dark mode. Use the appearance menu in the top
   app bar, or the light/dark toggle on the auth pages.
3. Check a phone width (about 390px) and a tablet width (about 820px).
4. Pick another seed in App Settings and confirm the page still reads well.
