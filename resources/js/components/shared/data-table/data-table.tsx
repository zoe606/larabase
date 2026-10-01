import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useIsMobile } from '@/hooks/use-mobile';
import { cn } from '@/lib/utils';
import { Column, ColumnDef, SortingState, flexRender, getCoreRowModel, getSortedRowModel, useReactTable } from '@tanstack/react-table';
import { ChevronDown, ChevronUp, ChevronsUpDown, Inbox } from 'lucide-react';
import { useState } from 'react';
import { MobileColumnConfig, MobileDataCardList } from './mobile-data-card';
import { DataTablePagination } from './pagination';

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface DataTableProps<TData, TValue> {
    columns: ColumnDef<TData, TValue>[];
    data: TData[];
    pagination?: PaginatedData<TData>;
    emptyMessage?: string;
    emptyDescription?: string;
    className?: string;
    isLoading?: boolean;
    /** Configuration for mobile card view. If provided, shows cards on mobile instead of table. */
    mobileConfig?: MobileColumnConfig;
}

function SortIndicator<TData, TValue>({ column }: { column: Column<TData, TValue> }) {
    if (!column.getCanSort()) return null;

    const sorted = column.getIsSorted();

    return (
        <span className="text-muted-foreground/70 ml-1.5 inline-flex">
            {sorted === 'asc' ? (
                <ChevronUp className="size-4" />
            ) : sorted === 'desc' ? (
                <ChevronDown className="size-4" />
            ) : (
                <ChevronsUpDown className="size-3.5 opacity-50" />
            )}
        </span>
    );
}

function LoadingSkeleton({ columns }: { columns: number }) {
    return (
        <>
            {[...Array(5)].map((_, rowIndex) => (
                <TableRow key={rowIndex}>
                    {[...Array(columns)].map((_, colIndex) => (
                        <TableCell key={colIndex}>
                            <Skeleton className="h-5 w-full" />
                        </TableCell>
                    ))}
                </TableRow>
            ))}
        </>
    );
}

function EmptyState({ message, description }: { message: string; description?: string }) {
    return (
        <TableRow>
            <TableCell colSpan={100} className="h-48">
                <div className="flex flex-col items-center justify-center text-center">
                    <div className="bg-muted mb-3 rounded-full p-3">
                        <Inbox className="text-muted-foreground size-6" />
                    </div>
                    <p className="text-foreground text-sm font-medium">{message}</p>
                    {description && <p className="text-muted-foreground mt-1 text-sm">{description}</p>}
                </div>
            </TableCell>
        </TableRow>
    );
}

export function DataTable<TData, TValue>({
    columns,
    data,
    pagination,
    emptyMessage = 'No results found',
    emptyDescription,
    className,
    isLoading = false,
    mobileConfig,
}: DataTableProps<TData, TValue>) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const isMobile = useIsMobile();

    const table = useReactTable({
        data,
        columns,
        getCoreRowModel: getCoreRowModel(),
        onSortingChange: setSorting,
        getSortedRowModel: getSortedRowModel(),
        state: {
            sorting,
        },
    });

    // Mobile card view
    if (isMobile && mobileConfig) {
        return (
            <div className={cn('space-y-4', className)}>
                <MobileDataCardList
                    rows={table.getRowModel().rows}
                    columns={columns}
                    mobileConfig={mobileConfig}
                    emptyMessage={emptyMessage}
                    emptyDescription={emptyDescription}
                    isLoading={isLoading}
                />
                {pagination && pagination.last_page > 1 && <DataTablePagination pagination={pagination} />}
            </div>
        );
    }

    // Desktop table view
    return (
        <div className={cn('space-y-4', className)}>
            <div className="-mx-4 overflow-x-auto px-4 md:mx-0 md:px-0">
                <div className="border-border min-w-[640px] rounded-lg border md:min-w-0">
                    <Table>
                        <TableHeader>
                            {table.getHeaderGroups().map((headerGroup) => (
                                <TableRow key={headerGroup.id} className="hover:bg-transparent">
                                    {headerGroup.headers.map((header) => (
                                        <TableHead
                                            key={header.id}
                                            className={cn(
                                                header.column.getCanSort() && 'hover:bg-muted/50 cursor-pointer transition-colors select-none',
                                            )}
                                            onClick={header.column.getToggleSortingHandler()}
                                        >
                                            <div className="flex items-center">
                                                {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                                <SortIndicator column={header.column} />
                                            </div>
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {isLoading ? (
                                <LoadingSkeleton columns={columns.length} />
                            ) : table.getRowModel().rows?.length ? (
                                table.getRowModel().rows.map((row) => (
                                    <TableRow
                                        key={row.id}
                                        data-state={row.getIsSelected() && 'selected'}
                                        className="hover:bg-muted/50 transition-colors"
                                    >
                                        {row.getVisibleCells().map((cell) => (
                                            <TableCell key={cell.id}>{flexRender(cell.column.columnDef.cell, cell.getContext())}</TableCell>
                                        ))}
                                    </TableRow>
                                ))
                            ) : (
                                <EmptyState message={emptyMessage} description={emptyDescription} />
                            )}
                        </TableBody>
                    </Table>
                </div>
            </div>
            {pagination && pagination.last_page > 1 && <DataTablePagination pagination={pagination} />}
        </div>
    );
}
