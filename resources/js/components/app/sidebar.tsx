import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';

import { NavUser } from '@/components/navigation/nav-user';
import { iconMapper } from '@/lib/iconMapper';
import { findGroupForMenu, SIDEBAR_GROUPS, type SidebarGroup } from '@/lib/sidebar-groups';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { ChevronDown, ChevronRight } from 'lucide-react';
import { useMemo } from 'react';
import AppLogo from './logo';

interface MenuItem {
    id: number;
    title: string;
    route: string | null;
    icon: string;
    children?: MenuItem[];
}

/** Check if any item or its children matches the current URL */
function hasActiveDescendant(items: MenuItem[], currentUrl: string): boolean {
    return items.some(
        (item) =>
            (item.route && item.route !== '#' && currentUrl.startsWith(item.route)) ||
            (item.children && hasActiveDescendant(item.children, currentUrl)),
    );
}

function MenuItemRow({ menu, level = 0, currentUrl }: { menu: MenuItem; level?: number; currentUrl: string }) {
    const Icon = iconMapper(menu.icon || 'Folder') as LucideIcon;
    const children = Array.isArray(menu.children) ? menu.children.filter(Boolean) : [];
    const hasChildren = children.length > 0;
    const isActive = menu.route && menu.route !== '#' && currentUrl.startsWith(menu.route);
    const hasActiveChild = hasChildren && hasActiveDescendant(children, currentUrl);

    if (!menu.route && !hasChildren) return null;

    if (hasChildren) {
        return (
            <SidebarMenuItem>
                <Collapsible defaultOpen={hasActiveChild}>
                    <CollapsibleTrigger asChild>
                        <SidebarMenuButton
                            className={cn(
                                'group flex w-full items-center justify-between rounded-md transition-colors',
                                level > 0 ? 'py-1 pr-3 pl-6' : 'px-3 py-1.5',
                                isActive || hasActiveChild
                                    ? 'bg-accent text-foreground font-medium'
                                    : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                            )}
                        >
                            <div className="flex items-center gap-2">
                                <Icon className={cn('size-4 opacity-60', (isActive || hasActiveChild) && 'opacity-100')} />
                                <span className="text-sm">{menu.title}</span>
                            </div>
                            <ChevronDown className="size-3.5 opacity-40 transition-transform duration-200 group-data-[state=open]:rotate-180" />
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenu className="border-border/50 mt-0.5 ml-3 border-l pl-1.5">
                            {children.map((child) => (
                                <MenuItemRow key={child.id} menu={child} level={level + 1} currentUrl={currentUrl} />
                            ))}
                        </SidebarMenu>
                    </CollapsibleContent>
                </Collapsible>
            </SidebarMenuItem>
        );
    }

    return (
        <SidebarMenuItem>
            <SidebarMenuButton
                asChild
                className={cn(
                    'group flex items-center gap-2 rounded-md transition-colors',
                    level > 0 ? 'py-1 pr-3 pl-6' : 'px-3 py-1.5',
                    isActive ? 'bg-accent text-foreground font-medium' : 'text-muted-foreground hover:bg-accent hover:text-accent-foreground',
                )}
            >
                <Link href={menu.route || '#'}>
                    <Icon className={cn('size-4 opacity-60', isActive && 'opacity-100')} />
                    <span className="text-sm">{menu.title}</span>
                    {level > 0 && <ChevronRight className="ml-auto size-3.5 opacity-0 group-hover:opacity-40" />}
                </Link>
            </SidebarMenuButton>
        </SidebarMenuItem>
    );
}

function GroupSection({ group, items, currentUrl }: { group: SidebarGroup; items: MenuItem[]; currentUrl: string }) {
    if (items.length === 0) return null;

    const isAnyActive = hasActiveDescendant(items, currentUrl);

    const content = (
        <SidebarMenu>
            {items.map((menu) => (
                <MenuItemRow key={menu.id} menu={menu} currentUrl={currentUrl} />
            ))}
        </SidebarMenu>
    );

    // Collapsible group (e.g. System)
    if (group.collapsible) {
        const defaultOpen = isAnyActive || !group.defaultClosed;
        return (
            <div className="px-2">
                <Collapsible defaultOpen={defaultOpen}>
                    <CollapsibleTrigger className="group flex w-full items-center gap-1 px-1 py-1.5">
                        <span className="text-muted-foreground/70 text-[11px] font-medium tracking-wider uppercase">{group.label}</span>
                        <ChevronDown className="text-muted-foreground/50 size-3 transition-transform duration-200 group-data-[state=open]:rotate-180" />
                    </CollapsibleTrigger>
                    <CollapsibleContent>{content}</CollapsibleContent>
                </Collapsible>
            </div>
        );
    }

    return (
        <div className="px-2">
            {group.label && (
                <div className="px-1 pt-2 pb-1">
                    <span className="text-muted-foreground/70 text-[11px] font-medium tracking-wider uppercase">{group.label}</span>
                </div>
            )}
            {content}
        </div>
    );
}

export function AppSidebar() {
    const { url: currentUrl, props } = usePage();
    const { menus = [] } = props as { menus?: MenuItem[] };

    /** Flatten all menus and their children into groups */
    const grouped = useMemo(() => {
        const result: Record<string, MenuItem[]> = {};
        for (const g of SIDEBAR_GROUPS) {
            result[g.key] = [];
        }

        // Helper to collect all flat items (top-level only for grouping)
        for (const menu of menus) {
            const groupKey = findGroupForMenu(menu.route, menu.title);
            if (groupKey && result[groupKey]) {
                result[groupKey].push(menu);
            }
        }

        return result;
    }, [menus]);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader className="border-b px-4 py-2.5">
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild className="hover:bg-transparent">
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent className="gap-0 py-2">
                {SIDEBAR_GROUPS.map((group) => (
                    <GroupSection key={group.key} group={group} items={grouped[group.key] || []} currentUrl={currentUrl} />
                ))}
            </SidebarContent>

            <SidebarFooter className="border-t px-4 py-2">
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
