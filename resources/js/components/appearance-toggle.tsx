import { Moon, Sun } from 'lucide-react';
import type { ResolvedAppearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';

export default function AppearanceToggle({
    className = '',
}: {
    className?: string;
}) {
    const { resolvedAppearance, updateAppearance } = useAppearance();

    return (
        <ToggleGroup
            type="single"
            value={resolvedAppearance}
            onValueChange={(value) => {
                if (value === 'light' || value === 'dark') {
                    updateAppearance(value as ResolvedAppearance);
                }
            }}
            variant="outline"
            size="sm"
            className={className}
        >
            <ToggleGroupItem value="light" aria-label="Light">
                <Sun className="h-4 w-4" />
            </ToggleGroupItem>
            <ToggleGroupItem value="dark" aria-label="Dark">
                <Moon className="h-4 w-4" />
            </ToggleGroupItem>
        </ToggleGroup>
    );
}
