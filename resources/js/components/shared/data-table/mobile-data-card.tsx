import { Card, CardContent } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import { ColumnDef, flexRender, Row } from '@tanstack/react-table';
import { Inbox } from 'lucide-react';

export interface MobileColumnConfig {
    /** Column ID to display as the card title/primary field */
    titleColumn: string;
    /** Column IDs to display as secondary fields */
    secondaryColumns?: string[];
    /** Column ID for the actions menu (usually 'actions') */
    actionsColumn?: string;
}

interface MobileDataCardProps<TData, TValue> {
    row: Row<TData>;
    columns: ColumnDef<TData, TValue>[];
    mobileConfig: MobileColumnConfig;
}

export function MobileDataCard<TData, TValue>({ row, columns, mobileConfig }: MobileDataCardProps<TData, TValue>) {
    const { titleColumn, secondaryColumns = [], actionsColumn = 'actions' } = mobileConfig;

    const getColumnCell = (columnId: string) => {
        const cell = row.getVisibleCells().find((c) => c.column.id === columnId);
        if (!cell) return null;
        return flexRender(cell.column.columnDef.cell, cell.getContext());
    };

    const getColumnHeader = (columnId: string) => {
        const column = columns.find((c) => {
            const id = 'id' in c ? c.id : 'accessorKey' in c ? String(c.accessorKey) : undefined;
            return id === columnId;
        });
        if (!column) return columnId;
        if (typeof column.header === 'string') return column.header;
        return columnId;
    };

    return (
        <Card className="hover:bg-muted/50 transition-colors">
            <CardContent className="p-4">
                <div className="flex items-start justify-between gap-3">
                    <div className="min-w-0 flex-1 space-y-2">
                        {/* Title/Primary field */}
                        <div className="font-medium">{getColumnCell(titleColumn)}</div>

                        {/* Secondary fields */}
                        {secondaryColumns.length > 0 && (
                            <div className="space-y-1.5">
                                {secondaryColumns.map((columnId) => {
                                    const cell = getColumnCell(columnId);
                                    if (!cell) return null;
                                    const header = getColumnHeader(columnId);
                                    return (
                                        <div key={columnId} className="flex items-center gap-2 text-sm">
                                            <span className="text-muted-foreground shrink-0">{header}:</span>
                                            <span className="min-w-0">{cell}</span>
                                        </div>
                                    );
                                })}
                            </div>
                        )}
                    </div>

                    {/* Actions */}
                    {actionsColumn && <div className="shrink-0">{getColumnCell(actionsColumn)}</div>}
                </div>
            </CardContent>
        </Card>
    );
}

interface MobileDataCardListProps<TData, TValue> {
    rows: Row<TData>[];
    columns: ColumnDef<TData, TValue>[];
    mobileConfig: MobileColumnConfig;
    emptyMessage?: string;
    emptyDescription?: string;
    isLoading?: boolean;
}

export function MobileDataCardList<TData, TValue>({
    rows,
    columns,
    mobileConfig,
    emptyMessage = 'No results found',
    emptyDescription,
    isLoading = false,
}: MobileDataCardListProps<TData, TValue>) {
    if (isLoading) {
        return (
            <div className="space-y-3">
                {[...Array(5)].map((_, index) => (
                    <Card key={index}>
                        <CardContent className="p-4">
                            <div className="flex items-start justify-between gap-3">
                                <div className="flex-1 space-y-2">
                                    <Skeleton className="h-5 w-3/4" />
                                    <Skeleton className="h-4 w-1/2" />
                                    <Skeleton className="h-4 w-2/3" />
                                </div>
                                <Skeleton className="h-8 w-8 rounded" />
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </div>
        );
    }

    if (rows.length === 0) {
        return (
            <Card>
                <CardContent className={cn('flex flex-col items-center justify-center py-12 text-center')}>
                    <div className="bg-muted mb-3 rounded-full p-3">
                        <Inbox className="text-muted-foreground size-6" />
                    </div>
                    <p className="text-foreground text-sm font-medium">{emptyMessage}</p>
                    {emptyDescription && <p className="text-muted-foreground mt-1 text-sm">{emptyDescription}</p>}
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-3">
            {rows.map((row) => (
                <MobileDataCard key={row.id} row={row} columns={columns} mobileConfig={mobileConfig} />
            ))}
        </div>
    );
}
