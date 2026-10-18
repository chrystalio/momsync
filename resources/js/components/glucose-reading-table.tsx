import {
    type ColumnDef,
    type SortingState,
    createColumnHelper,
    createCoreRowModel,
    createSortedRowModel,
    flexRender,
    rowSortingFeature,
    sortFn_basic,
    sortFn_datetime,
    sortFn_text,
    tableFeatures,
    useTable,
} from '@tanstack/react-table';
import { ArrowUpDown, MoreHorizontal } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';

type Reading = {
    id: string;
    glucose_level: number;
    measured_at: string;
    reading_context: string;
    notes: string | null;
};

type Context = { value: string; label: string };

type Props = {
    readings: Reading[];
    contexts: Context[];
    onEdit: (reading: Reading) => void;
    onDelete: (reading: Reading) => void;
};

const features = tableFeatures({
    coreRowModel: createCoreRowModel(),
    rowSortingFeature,
    sortedRowModel: createSortedRowModel(),
    sortFns: {
        basic: sortFn_basic,
        datetime: sortFn_datetime,
        text: sortFn_text,
    },
});

const columnHelper = createColumnHelper<typeof features, Reading>();

const dateFormatter = new Intl.DateTimeFormat(undefined, {
    dateStyle: 'medium',
    timeStyle: 'short',
});

function SortableHeader({
    label,
    column,
}: {
    label: string;
    column: {
        getIsSorted: () => false | 'asc' | 'desc';
        toggleSorting: (desc?: boolean) => void;
    };
}) {
    const sorted = column.getIsSorted();
    return (
        <Button
            variant="ghost"
            size="sm"
            className="-ml-2 h-8"
            onClick={() => column.toggleSorting(sorted === 'asc')}
        >
            {label}
            <ArrowUpDown
                className={sorted ? 'opacity-100' : 'opacity-50'}
                aria-hidden="true"
            />
        </Button>
    );
}

const columns = [
    columnHelper.accessor('measured_at', {
        id: 'measured_at',
        header: ({ column }) => <SortableHeader label="When" column={column} />,
        sortFn: 'datetime',
        cell: ({ getValue }) => dateFormatter.format(new Date(getValue())),
    }),
    columnHelper.accessor('glucose_level', {
        id: 'glucose_level',
        header: ({ column }) => (
            <SortableHeader label="Glucose" column={column} />
        ),
        sortFn: 'basic',
        cell: ({ getValue }) => (
            <span className="font-medium tabular-nums">{getValue()} mg/dL</span>
        ),
    }),
    columnHelper.accessor('reading_context', {
        id: 'reading_context',
        header: ({ column }) => (
            <SortableHeader label="Context" column={column} />
        ),
        sortFn: 'text',
        cell: ({ getValue, table }) => {
            const value = getValue();
            const label = table.options.meta.contexts[value] ?? value;
            return <Badge variant="secondary">{label}</Badge>;
        },
    }),
    columnHelper.accessor('notes', {
        id: 'notes',
        header: 'Notes',
        sortFn: 'text',
        cell: ({ getValue }) => {
            const notes = getValue();
            return notes ? (
                <span className="text-muted-foreground">{notes}</span>
            ) : (
                <span className="text-muted-foreground/50">—</span>
            );
        },
    }),
    columnHelper.display({
        id: 'actions',
        enableSorting: false,
        cell: ({ row, table }) => {
            const reading = row.original;
            const meta = table.options.meta as {
                contexts: Record<string, string>;
                onEdit: (reading: Reading) => void;
                onDelete: (reading: Reading) => void;
            };
            return (
                <div className="text-right">
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button
                                variant="ghost"
                                size="icon"
                                aria-label="Open menu"
                            >
                                <MoreHorizontal />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuLabel>Actions</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem
                                onClick={() => meta.onEdit(reading)}
                            >
                                Edit
                            </DropdownMenuItem>
                            <DropdownMenuItem
                                variant="destructive"
                                onClick={() => meta.onDelete(reading)}
                            >
                                Delete
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            );
        },
    }),
] as ColumnDef<typeof features, Reading>[];

export default function GlucoseReadingTable({
    readings,
    contexts,
    onEdit,
    onDelete,
}: Props) {
    const [sorting, setSorting] = useState<SortingState>([
        { id: 'measured_at', desc: true },
    ]);

    const contextLabels: Record<string, string> = Object.fromEntries(
        contexts.map((c) => [c.value, c.label]),
    );

    const table = useTable({
        features,
        data: readings,
        columns,
        state: { sorting },
        onSortingChange: setSorting,
        meta: { contexts: contextLabels, onEdit, onDelete },
    });

    return (
        <div className="overflow-hidden rounded-md border">
            <Table>
                <TableHeader>
                    {table.getHeaderGroups().map((headerGroup) => (
                        <TableRow key={headerGroup.id}>
                            {headerGroup.headers.map((header) => (
                                <TableHead key={header.id}>
                                    {header.isPlaceholder
                                        ? null
                                        : flexRender(
                                              header.column.columnDef.header,
                                              header.getContext(),
                                          )}
                                </TableHead>
                            ))}
                        </TableRow>
                    ))}
                </TableHeader>
                <TableBody>
                    {table.getRowModel().rows.length ? (
                        table.getRowModel().rows.map((row) => (
                            <TableRow key={row.id}>
                                {row.getAllCells().map((cell) => (
                                    <TableCell key={cell.id}>
                                        {flexRender(
                                            cell.column.columnDef.cell,
                                            cell.getContext(),
                                        )}
                                    </TableCell>
                                ))}
                            </TableRow>
                        ))
                    ) : (
                        <TableRow>
                            <TableCell
                                colSpan={columns.length}
                                className="h-24 text-center"
                            >
                                No results.
                            </TableCell>
                        </TableRow>
                    )}
                </TableBody>
            </Table>
        </div>
    );
}
