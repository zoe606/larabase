import { Button } from '@/components/ui/button';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Sidebar, SidebarContent, SidebarHeader, useSidebar } from '@/components/ui/sidebar';
import { iconMapper } from '@/lib/iconMapper';
import { findGroupForMenu, SIDEBAR_GROUPS, type SidebarGroup } from '@/lib/sidebar-groups';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { ChevronDown, Menu } from 'lucide-react';
import { createContext, useCallback, useContext, useMemo, useState } from 'react';
import AppLogo from './logo';

interface MenuItem {
    id: number;
    title: string;
    route: string | null;
    icon: string;
    children?: MenuItem[];
}

const focusRing = 'outline-none focus-visible:outline-2 focus-visible:outline-offset-[-2px] focus-visible:outline-ring';
const drawerItem = cn(
    'state-layer rounded-shape-full text-label-lg text-on-surface-variant flex h-14 w-full items-center gap-3 px-4 [&_svg]:size-6 [&_svg]:shrink-0',
    focusRing,
);
const drawerActive = 'bg-secondary-container font-semibold text-on-secondary-container';
const railItem = cn('text-label-md text-on-surface-variant flex w-full flex-col items-center gap-1 pt-1 pb-2', focusRing);
const railIndicator = 'state-layer flex h-8 w-14 items-center justify-center rounded-shape-full [&_svg]:size-6';
// 11px without extra tracking so the longest captions ("Categorization") fit the 80px rail on one line per word.
const railCaption = 'text-label-sm line-clamp-2 max-w-full px-0.5 text-center tracking-normal [overflow-wrap:anywhere]';
const railActive = 'bg-secondary-container text-on-secondary-container';

function isRouteActive(route: string | null, currentUrl: string): boolean {
    return !!route && route !== '#' && currentUrl.startsWith(route);
}

/** Check if any item or its children matches the current URL */
function hasActiveDescendant(items: MenuItem[], currentUrl: string): boolean {
    return items.some((item) => isRouteActive(item.route, currentUrl) || (item.children ? hasActiveDescendant(item.children, currentUrl) : false));
}

/** Open parents live above the groups so they survive the rail → drawer remount. */
const OpenParentsContext = createContext<{ openIds: Set<number>; setParentOpen: (id: number, open: boolean) => void }>({
    openIds: new Set(),
    setParentOpen: () => {},
});

function collectActiveParents(items: MenuItem[], currentUrl: string, into: Set<number>): Set<number> {
    for (const item of items) {
        const children = item.children ?? [];
        if (children.length > 0 && hasActiveDescendant(children, currentUrl)) into.add(item.id);
        collectActiveParents(children, currentUrl, into);
    }
    return into;
}

function Destination({ menu, level, currentUrl, rail }: { menu: MenuItem; level: number; currentUrl: string; rail: boolean }) {
    const { setOpen } = useSidebar();
    const Icon = iconMapper(menu.icon || 'Folder') as LucideIcon;
    const children = Array.isArray(menu.children) ? menu.children.filter(Boolean) : [];
    const active = isRouteActive(menu.route, currentUrl);
    const activeChild = children.length > 0 && hasActiveDescendant(children, currentUrl);
    const { openIds, setParentOpen } = useContext(OpenParentsContext);
    const open = openIds.has(menu.id);

    if (!menu.route && children.length === 0) return null;

    if (children.length > 0) {
        if (rail) {
            // The rail has no room for children: expand the drawer and open this parent.
            return (
                <li>
                    <button
                        type="button"
                        aria-expanded={false}
                        className={railItem}
                        onClick={() => {
                            setParentOpen(menu.id, true);
                            setOpen(true);
                        }}
                    >
                        <span data-slot="rail-indicator" className={cn(railIndicator, activeChild && railActive)}>
                            <Icon aria-hidden />
                        </span>
                        <span className={railCaption}>{menu.title}</span>
                    </button>
                </li>
            );
        }

        return (
            <li>
                <Collapsible open={open} onOpenChange={(value) => setParentOpen(menu.id, value)}>
                    <CollapsibleTrigger className={cn(drawerItem, 'group', activeChild && 'text-on-surface')}>
                        <Icon aria-hidden />
                        <span className="flex-1 truncate text-left">{menu.title}</span>
                        <ChevronDown className="ease-standard transition-transform duration-200 group-data-[state=open]:rotate-180" aria-hidden />
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <ul className="flex flex-col pl-4">
                            {children.map((child) => (
                                <Destination key={child.id} menu={child} level={level + 1} currentUrl={currentUrl} rail={false} />
                            ))}
                        </ul>
                    </CollapsibleContent>
                </Collapsible>
            </li>
        );
    }

    if (rail) {
        return (
            <li>
                <Link href={menu.route || '#'} aria-current={active ? 'page' : undefined} className={railItem}>
                    <span data-slot="rail-indicator" className={cn(railIndicator, active && railActive)}>
                        <Icon aria-hidden />
                    </span>
                    <span className={cn(railCaption, active && 'text-on-surface font-semibold')}>{menu.title}</span>
                </Link>
            </li>
        );
    }

    return (
        <li>
            <Link href={menu.route || '#'} aria-current={active ? 'page' : undefined} className={cn(drawerItem, active && drawerActive)}>
                <Icon aria-hidden />
                <span className="truncate">{menu.title}</span>
            </Link>
        </li>
    );
}

function GroupSection({ group, items, currentUrl, rail }: { group: SidebarGroup; items: MenuItem[]; currentUrl: string; rail: boolean }) {
    const { openIds } = useContext(OpenParentsContext);
    if (items.length === 0) return null;

    const list = (
        <ul className="flex flex-col">
            {items.map((menu) => (
                <Destination key={menu.id} menu={menu} level={0} currentUrl={currentUrl} rail={rail} />
            ))}
        </ul>
    );

    if (rail) {
        return <div className="flex flex-col gap-1 py-2">{list}</div>;
    }

    if (group.collapsible) {
        const defaultOpen = hasActiveDescendant(items, currentUrl) || !group.defaultClosed || items.some((m) => openIds.has(m.id));
        return (
            <div className="px-3">
                <Collapsible defaultOpen={defaultOpen}>
                    <CollapsibleTrigger className={cn('group rounded-shape-full flex h-12 w-full items-center gap-2 px-4', focusRing)}>
                        <span className="text-title-sm text-on-surface-variant flex-1 text-left">{group.label}</span>
                        <ChevronDown
                            className="text-on-surface-variant ease-standard size-5 transition-transform duration-200 group-data-[state=open]:rotate-180"
                            aria-hidden
                        />
                    </CollapsibleTrigger>
                    <CollapsibleContent>{list}</CollapsibleContent>
                </Collapsible>
            </div>
        );
    }

    return (
        <div className="px-3">
            {group.label && <h2 className="text-title-sm text-on-surface-variant flex h-12 items-center px-4">{group.label}</h2>}
            {list}
        </div>
    );
}

export function AppSidebar() {
    const { url: currentUrl, props } = usePage();
    const { menus = [] } = props as { menus?: MenuItem[] };
    const { state, isMobile, toggleSidebar } = useSidebar();
    const rail = !isMobile && state === 'collapsed';
    const [openIds, setOpenIds] = useState(() => collectActiveParents(menus, currentUrl, new Set()));
    const setParentOpen = useCallback((id: number, open: boolean) => {
        setOpenIds((prev) => {
            const next = new Set(prev);
            if (open) next.add(id);
            else next.delete(id);
            return next;
        });
    }, []);
    const openParents = useMemo(() => ({ openIds, setParentOpen }), [openIds, setParentOpen]);

    const grouped = useMemo(() => {
        const result: Record<string, MenuItem[]> = {};
        for (const g of SIDEBAR_GROUPS) {
            result[g.key] = [];
        }
        for (const menu of menus) {
            const groupKey = findGroupForMenu(menu.route, menu.title);
            if (groupKey && result[groupKey]) {
                result[groupKey].push(menu);
            }
        }
        return result;
    }, [menus]);

    return (
        <OpenParentsContext.Provider value={openParents}>
            <Sidebar collapsible="icon">
                <SidebarHeader className={cn('h-16 flex-row items-center gap-1 p-0', rail ? 'justify-center' : 'px-3')}>
                    {!isMobile && (
                        <Button
                            variant="text"
                            size="icon"
                            aria-label={rail ? 'Expand navigation' : 'Collapse navigation'}
                            aria-expanded={!rail}
                            onClick={toggleSidebar}
                        >
                            <Menu />
                        </Button>
                    )}
                    {!rail && (
                        <Link href="/dashboard" prefetch className={cn('rounded-shape-full flex min-w-0 items-center px-2 py-1', focusRing)}>
                            <AppLogo />
                        </Link>
                    )}
                </SidebarHeader>

                <SidebarContent className={cn('gap-0 pb-4', rail && 'group-data-[collapsible=icon]:overflow-y-auto')}>
                    <nav aria-label="Main">
                        {SIDEBAR_GROUPS.map((group) => (
                            <GroupSection key={group.key} group={group} items={grouped[group.key] || []} currentUrl={currentUrl} rail={rail} />
                        ))}
                    </nav>
                </SidebarContent>
            </Sidebar>
        </OpenParentsContext.Provider>
    );
}
