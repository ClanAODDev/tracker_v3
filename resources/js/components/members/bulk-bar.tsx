import { router } from '@inertiajs/react';
import { ArrowLeftRight, Bell, Megaphone, Tags, X } from 'lucide-react';
import { type FormEvent, useEffect, useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { SimpleSelect } from '@/components/ui/simple-select';
import { getJson, postJson } from '@/lib/api';
import { cn } from '@/lib/utils';

import type { BulkConfig, MemberListDivision } from './types';

interface Props {
    selectedIds: number[];
    parttimersSelected: boolean;
    bulk: BulkConfig;
    division: MemberListDivision;
    onClear: () => void;
    onReminded: (ids: number[], date: string) => void;
}

interface PlatoonOption {
    id: number;
    name: string;
    squads: Array<{ id: number; name: string }>;
}

export function BulkBar({ selectedIds, parttimersSelected, bulk, division, onClear, onReminded }: Props) {
    const [tagsOpen, setTagsOpen] = useState(false);
    const [moveOpen, setMoveOpen] = useState(false);
    const [busy, setBusy] = useState(false);

    if (selectedIds.length === 0) return null;

    const count = selectedIds.length;
    const label = `${count} member${count === 1 ? '' : 's'} selected`;

    async function markReminded() {
        setBusy(true);
        try {
            const res = await postJson<{ count: number; skipped: number; updatedIds: number[]; date: string }>(
                bulk.urls.reminder,
                { member_ids: selectedIds },
            );
            let msg = `${res.count} member${res.count === 1 ? '' : 's'} marked as reminded`;
            if (res.skipped > 0) msg += ` (${res.skipped} skipped — already reminded today)`;
            toast.success(msg);
            onReminded(res.updatedIds, res.date);
            onClear();
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to set reminders');
        } finally {
            setBusy(false);
        }
    }

    return (
        <>
            <div className="fixed inset-x-0 bottom-4 z-40 flex justify-center px-4">
                <div className="flex flex-wrap items-center gap-2 rounded-md border border-border bg-popover px-4 py-2.5 shadow-lg">
                    <span className="text-sm font-medium">{label}</span>
                    <div className="flex items-center gap-1.5">
                        {bulk.canRemind && (
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() =>
                                    router.post(bulk.urls.pm, { 'pm-member-data': selectedIds.join(',') })
                                }
                            >
                                <Megaphone /> Send PM
                            </Button>
                        )}
                        {bulk.canAssignTags && (
                            <Button size="sm" variant="outline" onClick={() => setTagsOpen(true)}>
                                <Tags /> Tags
                            </Button>
                        )}
                        {bulk.canMoveMembers && (
                            <Button
                                size="sm"
                                variant="outline"
                                disabled={parttimersSelected}
                                title={
                                    parttimersSelected
                                        ? 'Cannot move: selection includes part-time members'
                                        : undefined
                                }
                                onClick={() => setMoveOpen(true)}
                            >
                                <ArrowLeftRight /> Move
                            </Button>
                        )}
                        {bulk.canRemind && (
                            <Button size="sm" variant="outline" disabled={busy} onClick={markReminded}>
                                <Bell /> Reminder
                            </Button>
                        )}
                        <Button size="icon-sm" variant="ghost" onClick={onClear} title="Clear selection">
                            <X />
                        </Button>
                    </div>
                </div>
            </div>

            {bulk.canAssignTags && (
                <TagsDialog
                    open={tagsOpen}
                    onOpenChange={setTagsOpen}
                    tags={bulk.assignableTags}
                    memberIds={selectedIds}
                    url={bulk.urls.tags}
                />
            )}

            {bulk.canMoveMembers && (
                <MoveDialog
                    open={moveOpen}
                    onOpenChange={setMoveOpen}
                    memberIds={selectedIds}
                    division={division}
                    dataUrl={bulk.urls.transferData}
                    submitUrl={bulk.urls.transfer}
                />
            )}
        </>
    );
}

function TagsDialog({
    open,
    onOpenChange,
    tags,
    memberIds,
    url,
}: {
    open: boolean;
    onOpenChange: (v: boolean) => void;
    tags: Array<{ id: number; name: string }>;
    memberIds: number[];
    url: string;
}) {
    const [selected, setSelected] = useState<Set<number>>(new Set());
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (open) setSelected(new Set());
    }, [open]);

    async function run(action: 'assign' | 'remove') {
        if (selected.size === 0) return;
        setBusy(true);
        try {
            const res = await postJson<{ message: string }>(url, {
                member_ids: memberIds,
                tags: [...selected],
                action,
            });
            toast.success(res.message);
            onOpenChange(false);
            router.reload();
        } catch (e) {
            toast.error(e instanceof Error ? e.message : `Failed to ${action} tags`);
        } finally {
            setBusy(false);
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Manage tags</DialogTitle>
                    <DialogDescription>
                        {memberIds.length} member{memberIds.length === 1 ? '' : 's'} selected
                    </DialogDescription>
                </DialogHeader>
                {tags.length === 0 ? (
                    <p className="text-sm text-muted-foreground">No tags available for this division.</p>
                ) : (
                    <div className="flex flex-wrap gap-2">
                        {tags.map((tag) => {
                            const on = selected.has(tag.id);
                            return (
                                <button
                                    key={tag.id}
                                    type="button"
                                    onClick={() =>
                                        setSelected((prev) => {
                                            const next = new Set(prev);
                                            next.has(tag.id) ? next.delete(tag.id) : next.add(tag.id);
                                            return next;
                                        })
                                    }
                                    className={cn(
                                        'rounded-md border px-2.5 py-1 text-xs',
                                        on ? 'border-primary bg-primary/10' : 'border-border',
                                    )}
                                >
                                    {tag.name}
                                </button>
                            );
                        })}
                    </div>
                )}
                <DialogFooter>
                    <Button
                        variant="destructive"
                        size="sm"
                        disabled={busy || selected.size === 0}
                        onClick={() => run('remove')}
                    >
                        Remove
                    </Button>
                    <Button size="sm" disabled={busy || selected.size === 0} onClick={() => run('assign')}>
                        Assign
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function MoveDialog({
    open,
    onOpenChange,
    memberIds,
    division,
    dataUrl,
    submitUrl,
}: {
    open: boolean;
    onOpenChange: (v: boolean) => void;
    memberIds: number[];
    division: MemberListDivision;
    dataUrl: string;
    submitUrl: string;
}) {
    const [platoons, setPlatoons] = useState<PlatoonOption[]>([]);
    const [platoonId, setPlatoonId] = useState('');
    const [squadId, setSquadId] = useState('');
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        if (!open) return;
        setPlatoonId('');
        setSquadId('');
        if (platoons.length === 0) {
            getJson<{ platoons: PlatoonOption[] }>(dataUrl)
                .then((res) => setPlatoons(res.platoons))
                .catch(() => toast.error('Failed to load ' + division.platoonLabel.toLowerCase() + 's'));
        }
    }, [open, dataUrl, division.platoonLabel, platoons.length]);

    const squads = platoons.find((p) => String(p.id) === platoonId)?.squads ?? [];

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (!platoonId) return;
        setBusy(true);
        try {
            const res = await postJson<{ message: string }>(submitUrl, {
                member_ids: memberIds,
                platoon_id: platoonId,
                squad_id: squadId || null,
            });
            toast.success(res.message);
            onOpenChange(false);
            router.reload();
        } catch (err) {
            toast.error(err instanceof Error ? err.message : 'Failed to move members');
        } finally {
            setBusy(false);
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-md">
                <DialogHeader>
                    <DialogTitle>Move members</DialogTitle>
                    <DialogDescription>
                        {memberIds.length} member{memberIds.length === 1 ? '' : 's'} selected
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-1.5">
                        <Label>{division.platoonLabel}</Label>
                        <SimpleSelect
                            value={platoonId || '__all'}
                            onChange={(v) => {
                                setPlatoonId(v === '__all' ? '' : v);
                                setSquadId('');
                            }}
                            placeholder={`Select ${division.platoonLabel.toLowerCase()}…`}
                            options={[
                                { value: '__all', label: `Select ${division.platoonLabel.toLowerCase()}…` },
                                ...platoons.map((p) => ({ value: String(p.id), label: p.name })),
                            ]}
                        />
                    </div>
                    <div className="grid gap-1.5">
                        <Label>
                            {division.squadLabel} <span className="text-muted-foreground">(optional)</span>
                        </Label>
                        <SimpleSelect
                            value={squadId || '__all'}
                            onChange={(v) => setSquadId(v === '__all' ? '' : v)}
                            placeholder={`No ${division.squadLabel.toLowerCase()}`}
                            options={[
                                { value: '__all', label: `No ${division.squadLabel.toLowerCase()} assignment` },
                                ...squads.map((s) => ({ value: String(s.id), label: s.name })),
                            ]}
                        />
                    </div>
                    <DialogFooter>
                        <Button type="submit" size="sm" disabled={busy || !platoonId}>
                            Move
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
