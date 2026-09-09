import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { cn } from '@/lib/utils';

export interface SelectOption {
    value: string;
    label: string;
}

export function SimpleSelect({
    value,
    onChange,
    options,
    placeholder,
    className,
    size = 'sm',
}: {
    value: string;
    onChange: (value: string) => void;
    options: SelectOption[];
    placeholder?: string;
    className?: string;
    size?: 'sm' | 'default';
}) {
    return (
        <Select value={value} onValueChange={onChange}>
            <SelectTrigger className={cn(size === 'sm' && 'h-8', 'w-auto min-w-[10rem]', className)}>
                <SelectValue placeholder={placeholder} />
            </SelectTrigger>
            <SelectContent>
                {options.map((option) => (
                    <SelectItem key={option.value} value={option.value}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}
