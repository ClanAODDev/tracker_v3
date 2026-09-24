import { router } from '@inertiajs/react';
import { Check, Plus, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import type { DetailsManagement } from '@/components/member/handle-editor';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Command, CommandGroup, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { postJson } from '@/lib/api';
import { fieldOutlineClass } from '@/lib/field-colors';
import { cn } from '@/lib/utils';

export interface FieldBadgeData {
    key: string;
    label: string;
    type: 'text' | 'select';
    options: string[];
    value: string | null;
    color: string | null;
    canEdit: boolean;
}

export function MemberFieldBadges({ fields, management }: { fields: FieldBadgeData[]; management: DetailsManagement | null }) {
    const visible = fields.filter((f) => f.value !== null || f.canEdit);

    if (visible.length === 0) {
        return null;
    }

    return (
        <div className="flex flex-wrap items-center gap-1.5">
            {visible.map((field) => (
                <FieldBadge key={field.key} field={field} saveUrl={management?.saveUrl ?? ''} />
            ))}
        </div>
    );
}

function FieldBadge({ field, saveUrl }: { field: FieldBadgeData; saveUrl: string }) {
    const [open, setOpen] = useState(false);
    const [value, setValue] = useState(field.value ?? '');
    const [busy, setBusy] = useState(false);

    if (!field.canEdit) {
        return (
            <Badge variant="outline" className={fieldOutlineClass(field.color)} title={field.label}>
                <span className="tracking-wide uppercase">{field.label}</span>: {field.value}
            </Badge>
        );
    }

    const save = async (newValue: string) => {
        setBusy(true);
        try {
            await postJson(saveUrl, { fields: { [field.key]: newValue } });
            setOpen(false);
            router.reload({ only: ['customFields'] });
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to update field');
        } finally {
            setBusy(false);
        }
    };

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                {field.value ? (
                    <button type="button">
                        <Badge
                            variant="outline"
                            className={cn('cursor-pointer transition-opacity hover:opacity-80', fieldOutlineClass(field.color))}
                        >
                            <span className="tracking-wide uppercase">{field.label}</span>: {field.value}
                        </Badge>
                    </button>
                ) : (
                    <Button variant="outline" size="xs" className="h-6 gap-1 border-dashed text-muted-foreground">
                        <Plus className="size-3" /> <span className="tracking-wide uppercase">{field.label}</span>
                    </Button>
                )}
            </PopoverTrigger>
            <PopoverContent className="w-56 p-0" align="start">
                {field.type === 'select' ? (
                    <Command>
                        <CommandList>
                            <CommandGroup>
                                {field.value && (
                                    <CommandItem disabled={busy} onSelect={() => save('')}>
                                        <X className="size-4" />
                                        <span className="flex-1 text-muted-foreground">Clear</span>
                                    </CommandItem>
                                )}
                                {field.options.map((option) => (
                                    <CommandItem key={option} disabled={busy} onSelect={() => save(option)}>
                                        <Check className={cn('size-4', field.value === option ? 'opacity-100' : 'opacity-0')} />
                                        <span className="flex-1">{option}</span>
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        </CommandList>
                    </Command>
                ) : (
                    <div className="space-y-2 p-2">
                        <Input
                            autoFocus
                            value={value}
                            onChange={(e) => setValue(e.target.value)}
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    e.preventDefault();
                                    save(value);
                                }
                            }}
                            placeholder={field.label}
                            className="h-8"
                        />
                        <div className="flex justify-end gap-2">
                            {field.value && (
                                <Button size="xs" variant="ghost" disabled={busy} onClick={() => save('')}>
                                    Clear
                                </Button>
                            )}
                            <Button size="xs" disabled={busy} onClick={() => save(value)}>
                                Save
                            </Button>
                        </div>
                    </div>
                )}
            </PopoverContent>
        </Popover>
    );
}
