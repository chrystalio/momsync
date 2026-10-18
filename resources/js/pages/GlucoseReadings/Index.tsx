import { Head, router } from '@inertiajs/react';
import { Droplets } from 'lucide-react';
import { useState } from 'react';
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
import glucoseReadings from '@/routes/glucose-readings';

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
};

type FormDialogState =
    | { mode: 'closed' }
    | { mode: 'create' }
    | { mode: 'edit'; reading: Reading };

export default function Index({ readings, contexts }: Props) {
    const [formDialog, setFormDialog] = useState<FormDialogState>({
        mode: 'closed',
    });
    const [pendingDelete, setPendingDelete] = useState<string | null>(null);

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
                        <div className="mt-8 flex items-center justify-between">
                            <h2 className="text-lg font-semibold">Readings</h2>
                            <Button
                                onClick={() =>
                                    setFormDialog({ mode: 'create' })
                                }
                            >
                                Add Data
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
