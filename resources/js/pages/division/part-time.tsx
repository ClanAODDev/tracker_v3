import { Head, Link, router, useForm } from '@inertiajs/react';
import { ExternalLink, Plus, Search, Trash2, Users } from 'lucide-react';
import { useMemo, useState } from 'react';

import { MemberCombobox, type MemberResult } from '@/components/member-combobox';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';
import type { MemberCard } from '@/types';

interface Row extends MemberCard {
    primaryDivision: string | null;
    status: 'active' | 'onLeave' | 'removed';
    handle: { value: string; url: string | null } | null;
    removeUrl: string;
}

interface Props {
    division: { name: string; slug: string; handleLabel: string | null };
    members: Row[];
    stats: { total: number; active: number; onLeave: number; removed: number };
    canManage: boolean;
    addUrl: string;
}

const STATUS_BADGE: Record<Row['status'], { label: string; className: string }> = {
    active: { label: 'Active', className: 'border-success/40 text-success' },
    onLeave: { label: 'On leave', className: 'border-chart-2/40 text-chart-2' },
    removed: { label: 'Removed', className: 'border-destructive/40 text-destructive' },
};

function StatTile({ value, label, className }: { value: number; label: string; className?: string }) {
    return (
        <div className={cn('rounded-md border border-border bg-card p-4', className)}>
            <div className="numeric text-2xl font-semibold">{value}</div>
            <p className="mt-0.5 text-xs text-muted-foreground">{label}</p>
        </div>
    );
}

export default function PartTime({ division, members, stats, canManage, addUrl }: Props) {
    const [search, setSearch] = useState('');
    const [addOpen, setAddOpen] = useState(false);
    const [selected, setSelected] = useState<MemberResult | null>(null);

    const form = useForm({ member_id: '', handle_value: '' });

    const filtered = useMemo(() => {
        const q = search.trim().toLowerCase();
        if (!q) return members;
        return members.filter((m) => m.name.toLowerCase().includes(q));
    }, [members, search]);

    function submitAdd(e: React.FormEvent) {
        e.preventDefault();
        if (!selected) return;
        form.transform((data) => ({ ...data, member_id: selected.clanId }));
        form.post(addUrl, {
            preserveScroll: true,
            onSuccess: () => {
                setAddOpen(false);
                setSelected(null);
                form.reset();
            },
        });
    }

    return (
        <AppLayout
            header={{
                eyebrow: division.name,
                title: 'Part-timers',
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: division.name, href: `/divisions/${division.slug}` },
                    { label: 'Part-timers' },
                ],
                actions: canManage ? (
                    <Button size="sm" onClick={() => setAddOpen(true)}>
                        <Plus /> Add part-timer
                    </Button>
                ) : undefined,
            }}
        >
            <Head title={`Part-timers · ${division.name}`} />

            <div className="space-y-6">
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <StatTile value={stats.total} label="Total" />
                    <StatTile value={stats.active} label="Active" className="border-success/30" />
                    <StatTile value={stats.onLeave} label="On leave" className="border-chart-2/30" />
                    <StatTile value={stats.removed} label="Removed" className="border-destructive/30" />
                </div>

                {members.length === 0 ? (
                    <div className="rounded-md border border-border bg-card p-10 text-center">
                        <Users className="mx-auto size-8 text-muted-foreground" />
                        <h3 className="mt-3 text-sm font-semibold">No part-time members</h3>
                        <p className="mt-1 text-sm text-muted-foreground">
                            This division currently has no part-time members assigned.
                        </p>
                    </div>
                ) : (
                    <>
                        <div className="relative max-w-xs">
                            <Search className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search members…"
                                className="pl-8"
                            />
                        </div>

                        <div className="overflow-x-auto rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Member</TableHead>
                                        <TableHead>Primary division</TableHead>
                                        <TableHead>In-game name</TableHead>
                                        <TableHead>Status</TableHead>
                                        {canManage && <TableHead className="text-right">Actions</TableHead>}
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {filtered.map((member) => {
                                        const badge = STATUS_BADGE[member.status];
                                        return (
                                            <TableRow key={member.clanId}>
                                                <TableCell>
                                                    <Link href={member.profileUrl} className="hover:text-primary">
                                                        {member.name}
                                                    </Link>{' '}
                                                    <span className="text-xs text-muted-foreground">
                                                        {member.rankAbbr}
                                                    </span>
                                                </TableCell>
                                                <TableCell>
                                                    {member.primaryDivision ?? (
                                                        <span className="text-destructive">None</span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    {member.handle ? (
                                                        <span className="inline-flex items-center gap-1.5">
                                                            <code className="numeric text-xs">
                                                                {member.handle.value}
                                                            </code>
                                                            {member.handle.url && (
                                                                <a
                                                                    href={member.handle.url}
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                    className="text-muted-foreground hover:text-primary"
                                                                >
                                                                    <ExternalLink className="size-3" />
                                                                </a>
                                                            )}
                                                        </span>
                                                    ) : (
                                                        <span className="text-muted-foreground">—</span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <span
                                                        className={cn(
                                                            'inline-block rounded border px-1.5 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide',
                                                            badge.className,
                                                        )}
                                                    >
                                                        {badge.label}
                                                    </span>
                                                </TableCell>
                                                {canManage && (
                                                    <TableCell className="text-right">
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            className="size-8 text-destructive hover:text-destructive"
                                                            title="Remove from part-timers"
                                                            onClick={() => {
                                                                if (
                                                                    confirm(
                                                                        `Remove ${member.name} from ${division.name} part-timers?`,
                                                                    )
                                                                ) {
                                                                    router.get(member.removeUrl, {}, { preserveScroll: true });
                                                                }
                                                            }}
                                                        >
                                                            <Trash2 className="size-4" />
                                                        </Button>
                                                    </TableCell>
                                                )}
                                            </TableRow>
                                        );
                                    })}
                                </TableBody>
                            </Table>
                        </div>
                    </>
                )}
            </div>

            <Dialog open={addOpen} onOpenChange={setAddOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Add part-time member</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitAdd} className="space-y-4">
                        <div className="grid gap-1.5">
                            <Label>Member</Label>
                            <MemberCombobox value={selected} onSelect={setSelected} />
                            {form.errors.member_id && (
                                <p className="text-xs text-destructive">{form.errors.member_id}</p>
                            )}
                        </div>
                        {division.handleLabel && (
                            <div className="grid gap-1.5">
                                <Label htmlFor="handle_value">
                                    {division.handleLabel} handle{' '}
                                    <span className="text-muted-foreground">(optional)</span>
                                </Label>
                                <Input
                                    id="handle_value"
                                    value={form.data.handle_value}
                                    onChange={(e) => form.setData('handle_value', e.target.value)}
                                    placeholder={`Their ${division.handleLabel.toLowerCase()} name…`}
                                />
                                <p className="text-xs text-muted-foreground">
                                    Sets their in-game handle for {division.name}.
                                </p>
                            </div>
                        )}
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setAddOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={!selected || form.processing}>
                                <Plus /> Add part-timer
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
