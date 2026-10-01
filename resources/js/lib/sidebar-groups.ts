/**
 * Sidebar group definitions.
 * Maps menu items (by route prefix) into visual groups.
 * No DB changes — purely frontend grouping of the existing `menus` prop.
 */

export interface SidebarGroup {
    key: string;
    label: string | null; // null = no visible label (pinned)
    /** Route prefixes that belong to this group */
    routes: string[];
    /** Menu titles that belong to this group (for parent-only items with # routes) */
    titles: string[];
    /** Whether the group is collapsible (e.g. System) */
    collapsible?: boolean;
    /** Whether collapsed by default */
    defaultClosed?: boolean;
}

export const SIDEBAR_GROUPS: SidebarGroup[] = [
    {
        key: 'pinned',
        label: null,
        routes: ['/dashboard'],
        titles: ['Dashboard'],
    },
    {
        key: 'system',
        label: 'System',
        routes: ['/permissions', '/users', '/roles', '/menus', '/settingsapp', '/backup', '/audit-logs', '/files'],
        titles: ['Access', 'Settings', 'Utilities'],
        collapsible: true,
        defaultClosed: true,
    },
];

/**
 * Find which group a menu item belongs to by its route or title.
 */
export function findGroupForMenu(route: string | null, title: string): string | null {
    for (const group of SIDEBAR_GROUPS) {
        if (route && route !== '#' && group.routes.some((r) => route.startsWith(r))) {
            return group.key;
        }
        if (group.titles.includes(title)) {
            return group.key;
        }
    }
    return null;
}
