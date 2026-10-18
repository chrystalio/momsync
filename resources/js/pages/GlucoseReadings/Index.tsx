import { Head, router } from '@inertiajs/react';
import { CalendarDays, Droplets, Filter, Plus } from 'lucide-react';
import { useCallback, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogTitle,
} from '@/components/ui/dialog';
import GlucoseReadingDialog from '@/components/glucose-reading-dialog';
import GlucoseReadingTable from '@/components/glucose-reading-table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import glucoseReadings from '@/routes/glucose-readings';

type Reading = {
    id: string;
    glucose_level: number;
    measured_at: string;
    reading_context: string;
    notes: string | null;
};

type Context = { value: string; label: string };

type Filters = {
    from_date?: string;
    reading_context?: string;
};

type Props = {
    readings: Reading[];
    contexts: Context[];
    filters: Filters;
};

type FormDialogState =
    | { mode: 'closed' }
    | { mode: 'create' }
    | { mode: 'edit'; reading: Reading };

const ALL = '__all__';

function fromDateValue(days: string): string | undefined {
    if (days === ALL) return undefined;
    const n = Number(days);
    const date = new Date();
    date.setDate(date.getDate() - n);
    return date.toISOString().slice(0, 10);
}

function fromDateToSelectValue(fromDate: string | undefined): string {
    if (!fromDate) return ALL;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const diff = Math.round(
        (today.getTime() - new Date(fromDate).getTime()) / 86_400_000,
    );
    return [7, 30, 90].includes(diff) ? String(diff) : ALL;
}

export default function Index({ readings, contexts, filters }: Props) {
    const [formDialog, setFormDialog] = useState<FormDialogState>({
        mode: 'closed',
    });
    const [pendingDelete, setPendingDelete] = useState<string | null>(null);

    const applyFilters = useCallback((next: Filters) => {
        router.get(
            glucoseReadings.index.url(),
            {
                from_date: next.from_date ?? null,
                reading_context: next.reading_context ?? null,
            },
            { preserveScroll: true, preserveState: true },
        );
    }, []);

    const onFromDateChange = (value: string) => {
        applyFilters({
            ...filters,
            from_date: fromDateValue(value),
        });
    };

    const onContextChange = (value: string) => {
        applyFilters({
            ...filters,
            reading_context: value === ALL ? undefined : value,
        });
    };

    const confirmDelete = () => {
        if (pendingDelete !== null) {
            router.delete(glucoseReadings.destroy(pendingDelete));
            setPendingDelete(null);
        }
    };

    return (
        <>
            <Head title="Glucose readings" />

            <h1 className="sr-only">Glucose readings</h1>

            <div className="space-y-8 px-4 md:px-6">
                {readings.length === 0 ? (
                    <div className="flex min-h-[60vh] items-center justify-center">
                        <Card className="w-full max-w-lg">
                            <CardContent className="flex flex-col items-center gap-4 py-20 text-center">
                                <div className="rounded-full bg-muted p-4 text-muted-foreground">
                                    <Droplets
                                        className="size-8"
                                        aria-hidden="true"
                                    />
                                </div>
                                <div className="space-y-1">
                                    <h2 className="text-lg font-semibold">
                                        No readings yet
                                    </h2>
                                    <p className="text-sm text-muted-foreground">
                                        Log your first glucose reading to start
                                        tracking your trends.
                                    </p>
                                </div>
                                <Button
                                    className="mt-2"
                                    onClick={() =>
                                        setFormDialog({ mode: 'create' })
                                    }
                                >
                                    Log your first reading
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                ) : (
                    <>
                        <div className="flex flex-wrap items-center justify-between gap-3 rounded-lg border bg-card px-4 py-3 my-8">
                            <div className="flex flex-wrap items-center gap-4">
                                <div className="flex items-center gap-2">
                                    <CalendarDays
                                        className="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    <ToggleGroup
                                        type="single"
                                        variant="outline"
                                        size="sm"
                                        value={fromDateToSelectValue(
                                            filters.from_date,
                                        )}
                                        onValueChange={(value) =>
                                            value && onFromDateChange(value)
                                        }
                                    >
                                        <ToggleGroupItem
                                            value={ALL}
                                            aria-label="All time"
                                        >
                                            All
                                        </ToggleGroupItem>
                                        <ToggleGroupItem
                                            value="7"
                                            aria-label="Last 7 days"
                                        >
                                            7d
                                        </ToggleGroupItem>
                                        <ToggleGroupItem
                                            value="30"
                                            aria-label="Last 30 days"
                                        >
                                            30d
                                        </ToggleGroupItem>
                                        <ToggleGroupItem
                                            value="90"
                                            aria-label="Last 90 days"
                                        >
                                            90d
                                        </ToggleGroupItem>
                                    </ToggleGroup>
                                </div>
                                <div className="flex items-center gap-2">
                                    <Filter
                                        className="size-4 text-muted-foreground"
                                        aria-hidden="true"
                                    />
                                    <Select
                                        value={filters.reading_context ?? ALL}
                                        onValueChange={onContextChange}
                                    >
                                        <SelectTrigger className="w-45">
                                            <SelectValue placeholder="Context" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value={ALL}>
                                                All contexts
                                            </SelectItem>
                                            {contexts.map((c) => (
                                                <SelectItem
                                                    key={c.value}
                                                    value={c.value}
                                                >
                                                    {c.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                            </div>
                            <Button
                                onClick={() =>
                                    setFormDialog({ mode: 'create' })
                                }
                            >
                                <Plus aria-hidden="true" />
                                Log reading
                            </Button>
                        </div>
                        <GlucoseReadingTable
                            readings={readings}
                            contexts={contexts}
                            onEdit={(reading) =>
                                setFormDialog({ mode: 'edit', reading })
                            }
                            onDelete={(reading) => setPendingDelete(reading.id)}
                        />
                    </>
                )}
            </div>

            <GlucoseReadingDialog
                contexts={contexts}
                reading={formDialog.mode === 'edit' ? formDialog.reading : null}
                open={formDialog.mode !== 'closed'}
                onOpenChange={(open) => {
                    if (!open) setFormDialog({ mode: 'closed' });
                }}
            />

            <Dialog
                open={pendingDelete !== null}
                onOpenChange={(open) => {
                    if (!open) setPendingDelete(null);
                }}
            >
                <DialogContent>
                    <DialogTitle>Delete this reading?</DialogTitle>
                    <DialogDescription>
                        This action cannot be undone.
                    </DialogDescription>
                    <DialogFooter className="gap-2">
                        <DialogClose asChild>
                            <Button variant="secondary">Cancel</Button>
                        </DialogClose>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}

Index.layout = {
    breadcrumbs: [
        {
            title: 'Glucose readings',
            href: glucoseReadings.index(),
        },
    ],
};
