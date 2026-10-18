import type { BreadcrumbItem } from '@/types';

export interface TopAppBarState {
    title: string;
    backHref: string | null;
}

/**
 * Pages pass breadcrumbs; the top app bar shows only the last item as its title
 * and, on detail pages, a back button to the parent item.
 */
export function resolveTopAppBar(breadcrumbs: BreadcrumbItem[]): TopAppBarState {
    const current = breadcrumbs[breadcrumbs.length - 1];
    const parent = breadcrumbs.length > 1 ? breadcrumbs[breadcrumbs.length - 2] : undefined;
    const backHref = parent && parent.href && parent.href !== '#' ? parent.href : null;

    return { title: current?.title ?? '', backHref };
}
