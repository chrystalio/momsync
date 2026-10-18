import type { Table } from '@tanstack/react-table';
import type { PaginationState } from '@tanstack/react-table';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type DataTablePaginationProps<TData> = {
    table: Table<any, any, TData>;
    pagination: PaginationState;
    onPaginationChange: (
        updater: PaginationState | ((old: PaginationState) => PaginationState),
    ) => void;
};

export function DataTablePagination<TData>({
    table,
    pagination,
    onPaginationChange,
}: DataTablePaginationProps<TData>) {
    const { pageIndex, pageSize } = pagination;
    const pageCount = table.getPageCount();

    return (
        <div className="flex items-center justify-between px-2 py-4">
            <div className="flex items-center gap-2 text-sm text-muted-foreground">
                <p className="text-sm font-medium">Rows per page</p>
                <Select
                    value={String(pageSize)}
                    onValueChange={(value) =>
                        onPaginationChange((prev) => ({
                            ...prev,
                            pageSize: Number(value),
                        }))
                    }
                >
                    <SelectTrigger className="h-8 w-[70px]">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent side="top">
                        {[10, 20, 30, 50, 100].map((size) => (
                            <SelectItem key={size} value={String(size)}>
                                {size}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
            </div>
            <div className="flex items-center gap-2">
                <p className="text-sm font-medium">
                    Page {pageIndex + 1} of {pageCount}
                </p>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => table.previousPage()}
                    disabled={!table.getCanPreviousPage()}
                >
                    Previous
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => table.nextPage()}
                    disabled={!table.getCanNextPage()}
                >
                    Next
                </Button>
            </div>
        </div>
    );
}
