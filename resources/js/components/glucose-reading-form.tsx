import { Form } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/input-error';
import GlucoseReadingController from '@/actions/App/Http/Controllers/GlucoseReadingController';

type Context = { value: string; label: string };

type Reading = {
    id: string;
    glucose_level: number;
    measured_at: string;
    reading_context: string;
    notes: string | null;
};

type Props = {
    reading?: Reading | null;
    contexts: Context[];
    onCancel?: () => void;
};

export default function GlucoseReadingForm({
    reading = null,
    contexts,
    onCancel,
}: Props) {
    const isEdit = reading !== null;
    const action = isEdit
        ? GlucoseReadingController.update.form(reading!)
        : GlucoseReadingController.store.form();

    const initialMeasuredAt = reading
        ? reading.measured_at.substring(0, 16)
        : new Date().toISOString().substring(0, 16);

    return (
        <Form {...action} className="space-y-6">
            {({ errors, processing }) => (
                <>
                    <div className="grid gap-2">
                        <Label htmlFor="glucose_level">Glucose (mg/dL)</Label>
                        <Input
                            id="glucose_level"
                            name="glucose_level"
                            type="number"
                            min={20}
                            max={600}
                            defaultValue={reading?.glucose_level}
                            required
                        />
                        <InputError message={errors.glucose_level} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="measured_at">Measured at</Label>
                        <Input
                            id="measured_at"
                            name="measured_at"
                            type="datetime-local"
                            defaultValue={initialMeasuredAt}
                            required
                        />
                        <InputError message={errors.measured_at} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="reading_context">Context</Label>
                        <select
                            id="reading_context"
                            name="reading_context"
                            defaultValue={reading?.reading_context ?? 'fasting'}
                            className="rounded-md border border-input bg-transparent px-2 py-1"
                            required
                        >
                            {contexts.map((c) => (
                                <option key={c.value} value={c.value}>
                                    {c.label}
                                </option>
                            ))}
                        </select>
                        <InputError message={errors.reading_context} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="notes">Notes</Label>
                        <textarea
                            id="notes"
                            name="notes"
                            defaultValue={reading?.notes ?? ''}
                            rows={3}
                            className="rounded-md border border-input bg-transparent px-3 py-2"
                        />
                        <InputError message={errors.notes} />
                    </div>

                    <div className="flex gap-2">
                        {onCancel && (
                            <Button
                                type="button"
                                variant="secondary"
                                onClick={onCancel}
                                disabled={processing}
                            >
                                Cancel
                            </Button>
                        )}
                        <Button type="submit" disabled={processing}>
                            {isEdit ? 'Update reading' : 'Log reading'}
                        </Button>
                    </div>
                </>
            )}
        </Form>
    );
}
