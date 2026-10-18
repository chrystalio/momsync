import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import GlucoseReadingForm from '@/components/glucose-reading-form';

type Reading = {
    id: string;
    glucose_level: number;
    measured_at: string;
    reading_context: string;
    notes: string | null;
};

type Context = { value: string; label: string };

type Props = {
    contexts: Context[];
    reading: Reading | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export default function GlucoseReadingDialog({
    contexts,
    reading,
    open,
    onOpenChange,
}: Props) {
    const isEdit = reading !== null;

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {isEdit ? 'Edit reading' : 'Log reading'}
                    </DialogTitle>
                </DialogHeader>
                <GlucoseReadingForm
                    key={reading?.id ?? 'new'}
                    reading={reading}
                    contexts={contexts}
                    onCancel={() => onOpenChange(false)}
                />
            </DialogContent>
        </Dialog>
    );
}
