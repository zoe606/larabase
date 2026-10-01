import { Button } from '@/components/ui/button';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Link, router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationData {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: PaginationLink[];
}

interface DataTablePaginationProps {
    pagination: PaginationData;
}

const perPageOptions = [10, 25, 50, 100];

export function DataTablePagination({ pagination }: DataTablePaginationProps) {
    const { current_page, last_page, per_page, total, links } = pagination;

    const handlePerPageChange = (value: string) => {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', '1');
        router.visit(url.toString(), { preserveScroll: true });
    };

    // Find first and last page links
    const firstPageUrl = links.find((link) => link.label.includes('Previous'))?.url?.replace(/page=\d+/, 'page=1');
    const lastPageUrl = links.find((link) => link.label.includes('Next'))?.url?.replace(/page=\d+/, `page=${last_page}`);
    const prevLink = links.find((link) => link.label.includes('Previous'));
    const nextLink = links.find((link) => link.label.includes('Next'));

    // Get page number links (exclude Previous/Next)
    const pageLinks = links.filter((link) => !link.label.includes('Previous') && !link.label.includes('Next'));

    return (
        <div className="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row">
            <div className="flex items-center gap-4">
                <div className="flex items-center gap-2">
                    <span className="text-muted-foreground text-sm">Show</span>
                    <Select value={String(per_page)} onValueChange={handlePerPageChange}>
                        <SelectTrigger className="h-8 w-[70px]">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {perPageOptions.map((option) => (
                                <SelectItem key={option} value={String(option)}>
                                    {option}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <span className="text-muted-foreground text-sm">entries</span>
                </div>
                <div className="text-muted-foreground hidden text-sm sm:block">
                    Page {current_page} of {last_page} ({total} items)
                </div>
            </div>
            <div className="flex items-center space-x-2">
                {/* First Page */}
                <Button variant="outline" size="icon" className="hidden h-8 w-8 lg:flex" disabled={current_page === 1} asChild={current_page !== 1}>
                    {current_page !== 1 && firstPageUrl ? (
                        <Link href={firstPageUrl} preserveScroll>
                            <ChevronsLeft className="h-4 w-4" />
                            <span className="sr-only">First page</span>
                        </Link>
                    ) : (
                        <>
                            <ChevronsLeft className="h-4 w-4" />
                            <span className="sr-only">First page</span>
                        </>
                    )}
                </Button>

                {/* Previous Page */}
                <Button variant="outline" size="icon" className="h-8 w-8" disabled={!prevLink?.url} asChild={!!prevLink?.url}>
                    {prevLink?.url ? (
                        <Link href={prevLink.url} preserveScroll>
                            <ChevronLeft className="h-4 w-4" />
                            <span className="sr-only">Previous page</span>
                        </Link>
                    ) : (
                        <>
                            <ChevronLeft className="h-4 w-4" />
                            <span className="sr-only">Previous page</span>
                        </>
                    )}
                </Button>

                {/* Page Numbers */}
                <div className="hidden items-center gap-1 sm:flex">
                    {pageLinks.map((link, index) => {
                        const pageNum = link.label;
                        const isEllipsis = pageNum === '...';

                        if (isEllipsis) {
                            return (
                                <span key={`ellipsis-${index}`} className="text-muted-foreground px-2">
                                    ...
                                </span>
                            );
                        }

                        return (
                            <Button
                                key={pageNum}
                                variant={link.active ? 'default' : 'outline'}
                                size="icon"
                                className="h-8 w-8"
                                asChild={!link.active && !!link.url}
                                disabled={link.active}
                            >
                                {!link.active && link.url ? (
                                    <Link href={link.url} preserveScroll>
                                        {pageNum}
                                    </Link>
                                ) : (
                                    <span>{pageNum}</span>
                                )}
                            </Button>
                        );
                    })}
                </div>

                {/* Next Page */}
                <Button variant="outline" size="icon" className="h-8 w-8" disabled={!nextLink?.url} asChild={!!nextLink?.url}>
                    {nextLink?.url ? (
                        <Link href={nextLink.url} preserveScroll>
                            <ChevronRight className="h-4 w-4" />
                            <span className="sr-only">Next page</span>
                        </Link>
                    ) : (
                        <>
                            <ChevronRight className="h-4 w-4" />
                            <span className="sr-only">Next page</span>
                        </>
                    )}
                </Button>

                {/* Last Page */}
                <Button
                    variant="outline"
                    size="icon"
                    className="hidden h-8 w-8 lg:flex"
                    disabled={current_page === last_page}
                    asChild={current_page !== last_page}
                >
                    {current_page !== last_page && lastPageUrl ? (
                        <Link href={lastPageUrl} preserveScroll>
                            <ChevronsRight className="h-4 w-4" />
                            <span className="sr-only">Last page</span>
                        </Link>
                    ) : (
                        <>
                            <ChevronsRight className="h-4 w-4" />
                            <span className="sr-only">Last page</span>
                        </>
                    )}
                </Button>
            </div>
        </div>
    );
}
