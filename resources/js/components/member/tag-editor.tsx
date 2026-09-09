import { router } from '@inertiajs/react';
import { Check, Plus, X } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
    CommandSeparator,
} from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { SimpleSelect } from '@/components/ui/simple-select';
import { getJson, postJson } from '@/lib/api';
import { cn } from '@/lib/utils';

export interface TagManagement {
    getUrl: string;
    addUrl: string;
    removeUrl: string;
    createUrl: string;
    canCreate: boolean;
    visibilityOptions: Array<{ value: string; label: string }>;
}

export interface DisplayTag {
    id: number;
    name: string;
    visibility: string;
    division: string | null;
}

interface AvailableTag {
    id: number;
    name: string;
    visibility: string;
    global: boolean;
}

const VISIBILITY_CLASS: Record<string, string> = {
    senior_leader: 'border-primary/40 text-primary',
    leadership: 'border-warning/40 text-warning',
};

export function MemberTagEditor({ tags, management }: { tags: DisplayTag[]; management: TagManagement | null }) {
    const [open, setOpen] = useState(false);
    const [available, setAvailable] = useState<AvailableTag[]>([]);
    const [assigned, setAssigned] = useState<number[]>([]);
    const [loading, setLoading] = useState(false);
    const [busy, setBusy] = useState<number | 'create' | null>(null);
    const [newName, setNewName] = useState('');
    const [newVisibility, setNewVisibility] = useState(management?.visibilityOptions[0]?.value ?? 'public');

    if (!management) {
        if (tags.length === 0) return null;
        return (
            <div className="flex flex-wrap gap-1.5">
                {tags.map((tag) => (
                    <Badge
                        key={tag.id}
                        variant="outline"
                        title={tag.division ?? undefined}
                        className={cn(VISIBILITY_CLASS[tag.visibility])}
                    >
                        {tag.name}
                    </Badge>
                ))}
            </div>
        );
    }

    async function refresh() {
        setLoading(true);
        try {
            const data = await getJson<{ available: AvailableTag[]; assigned: number[] }>(management!.getUrl);
            setAvailable(data.available);
            setAssigned(data.assigned);
        } catch {
            toast.error('Could not load tags');
        } finally {
            setLoading(false);
        }
    }

    function onOpenChange(next: boolean) {
        setOpen(next);
        if (next) refresh();
    }

    async function toggle(tag: AvailableTag) {
        const isAssigned = assigned.includes(tag.id);
        setBusy(tag.id);
        try {
            await postJson(isAssigned ? management!.removeUrl : management!.addUrl, { tag_id: tag.id });
            setAssigned((prev) => (isAssigned ? prev.filter((id) => id !== tag.id) : [...prev, tag.id]));
            router.reload({ only: ['tags'] });
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to update tag');
        } finally {
            setBusy(null);
        }
    }

    async function removeBadge(tag: DisplayTag) {
        try {
            await postJson(management!.removeUrl, { tag_id: tag.id });
            router.reload({ only: ['tags'] });
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to remove tag');
        }
    }

    async function create() {
        const name = newName.trim();
        if (!name) return;
        setBusy('create');
        try {
            await postJson(management!.createUrl, { name, visibility: newVisibility });
            setNewName('');
            await refresh();
            router.reload({ only: ['tags'] });
            toast.success(`Tag "${name}" created and assigned`);
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to create tag');
        } finally {
            setBusy(null);
        }
    }

    return (
        <div className="flex flex-wrap items-center gap-1.5">
            {tags.map((tag) => (
                <Badge
                    key={tag.id}
                    variant="outline"
                    title={tag.division ?? undefined}
                    className={cn('gap-1 pr-1', VISIBILITY_CLASS[tag.visibility])}
                >
                    {tag.name}
                    <button
                        type="button"
                        onClick={() => removeBadge(tag)}
                        className="rounded-sm opacity-60 transition-opacity hover:opacity-100"
                        aria-label={`Remove ${tag.name}`}
                    >
                        <X className="size-3" />
                    </button>
                </Badge>
            ))}

            <Popover open={open} onOpenChange={onOpenChange}>
                <PopoverTrigger asChild>
                    <Button variant="outline" size="xs" className="h-6 gap-1 border-dashed text-muted-foreground">
                        <Plus className="size-3" /> Tag
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-64 p-0" align="start">
                    <Command shouldFilter>
                        <CommandInput placeholder="Filter tags…" />
                        <CommandList>
                            {loading ? (
                                <div className="py-6 text-center text-sm text-muted-foreground">Loading…</div>
                            ) : (
                                <>
                                    <CommandEmpty>No assignable tags.</CommandEmpty>
                                    <CommandGroup>
                                        {available.map((tag) => (
                                            <CommandItem
                                                key={tag.id}
                                                value={tag.name}
                                                disabled={busy === tag.id}
                                                onSelect={() => toggle(tag)}
                                            >
                                                <Check
                                                    className={cn(
                                                        'size-4',
                                                        assigned.includes(tag.id) ? 'opacity-100' : 'opacity-0',
                                                    )}
                                                />
                                                <span className="flex-1">{tag.name}</span>
                                                {tag.global && (
                                                    <span className="text-[0.65rem] text-muted-foreground">global</span>
                                                )}
                                            </CommandItem>
                                        ))}
                                    </CommandGroup>
                                </>
                            )}
                        </CommandList>
                        {management.canCreate && (
                            <>
                                <CommandSeparator />
                                <div className="space-y-2 p-2">
                                    <Input
                                        value={newName}
                                        maxLength={25}
                                        onChange={(e) => setNewName(e.target.value)}
                                        onKeyDown={(e) => {
                                            if (e.key === 'Enter') {
                                                e.preventDefault();
                                                create();
                                            }
                                        }}
                                        placeholder="New tag name…"
                                        className="h-8"
                                    />
                                    <div className="flex gap-2">
                                        <SimpleSelect
                                            value={newVisibility}
                                            onChange={setNewVisibility}
                                            options={management.visibilityOptions}
                                            className="h-8 flex-1"
                                        />
                                        <Button
                                            size="sm"
                                            className="h-8"
                                            disabled={!newName.trim() || busy === 'create'}
                                            onClick={create}
                                        >
                                            Add
                                        </Button>
                                    </div>
                                </div>
                            </>
                        )}
                    </Command>
                </PopoverContent>
            </Popover>
        </div>
    );
}
