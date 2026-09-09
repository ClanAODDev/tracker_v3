import { Head, router, useForm } from '@inertiajs/react';
import { Check, RefreshCw, Trophy } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { RarityPill } from '@/components/awards/award-card';
import { StatTiles } from '@/components/reports/stat-tiles';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout';

interface AwardShowProps {
    award: {
        id: number;
        name: string;
        description: string | null;
        rarity: string;
        image: string | null;
        repeatable: boolean;
        allowRequest: boolean;
        canRequest: boolean;
        division: { name: string; slug: string; active: boolean } | null;
    };
    stats: { total: number; firstAwarded: string | null; lastAwarded: string | null; rarity: string };
    userHasAward: boolean;
    currentMember: { name: string; clanId: number } | null;
    recipients: {
        data: Array<{
            name: string | null;
            url: string | null;
            avatarUrl: string | null;
            division: string | null;
            divisionUrl: string | null;
            rank: string | null;
            timesReceived: number | null;
            awardedAt: string;
        }>;
        currentPage: number;
        lastPage: number;
    };
}

function RequestDialog({ awardId, currentMember }: { awardId: number; currentMember: { name: string; clanId: number } | null }) {
    const form = useForm({ member_id: '', reason: '' });

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post(`/clan/awards/${awardId}`, { preserveScroll: true, onSuccess: () => form.reset() });
    }

    return (
        <Dialog>
            <DialogTrigger asChild>
                <Button variant="outline" size="sm">
                    Request award
                </Button>
            </DialogTrigger>
            <DialogContent>
                <form onSubmit={submit}>
                    <DialogHeader>
                        <DialogTitle>Request this award</DialogTitle>
                        <DialogDescription>Request for yourself or someone else.</DialogDescription>
                    </DialogHeader>
                    <div className="mt-4 space-y-3">
                        <div className="grid gap-1.5">
                            <Label htmlFor="member_id">Member clan ID</Label>
                            <div className="flex gap-2">
                                <Input
                                    id="member_id"
                                    value={form.data.member_id}
                                    onChange={(e) => form.setData('member_id', e.target.value)}
                                    aria-invalid={!!form.errors.member_id}
                                />
                                {currentMember && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={() => form.setData('member_id', String(currentMember.clanId))}
                                    >
                                        Me
                                    </Button>
                                )}
                            </div>
                            {form.errors.member_id && (
                                <p className="text-xs text-destructive">{form.errors.member_id}</p>
                            )}
                        </div>
                        <div className="grid gap-1.5">
                            <Label htmlFor="reason">Reason</Label>
                            <Input
                                id="reason"
                                value={form.data.reason}
                                onChange={(e) => form.setData('reason', e.target.value)}
                                aria-invalid={!!form.errors.reason}
                            />
                            {form.errors.reason && <p className="text-xs text-destructive">{form.errors.reason}</p>}
                        </div>
                    </div>
                    <DialogFooter className="mt-4">
                        <Button type="submit" disabled={form.processing}>
                            Submit request
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AwardShow({ award, stats, userHasAward, currentMember, recipients }: AwardShowProps) {
    const [page] = useState(recipients.currentPage);

    return (
        <AppLayout
            header={{
                eyebrow: 'Achievement',
                title: award.name,
                breadcrumbs: [
                    { label: 'Achievements', href: '/clan/awards' },
                    { label: award.name },
                ],
            }}
        >
            <Head title={award.name} />

            <div className="space-y-6">
                <StatTiles
                    stats={[
                        { value: stats.total, label: 'Total recipients' },
                        { value: stats.firstAwarded ?? '—', label: 'First awarded', tone: 'info' },
                        { value: stats.lastAwarded ?? '—', label: 'Most recent', tone: 'success' },
                        {
                            value: award.allowRequest ? '✓' : '✕',
                            label: 'Requestable',
                            tone: award.allowRequest ? 'success' : 'muted',
                        },
                    ]}
                />

                <div
                    className="tron-corners flex flex-col items-center gap-5 rounded-md border bg-card p-6 text-center sm:flex-row sm:text-left"
                    style={{ borderColor: `color-mix(in srgb, var(--rarity-${stats.rarity}) 40%, var(--border))` }}
                >
                    <div className="flex size-24 shrink-0 items-center justify-center">
                        {award.image ? (
                            <img src={award.image} alt="" className="max-h-24 max-w-full object-contain" />
                        ) : (
                            <Trophy className="size-12 text-muted-foreground" />
                        )}
                    </div>
                    <div className="min-w-0 flex-1">
                        <div className="flex flex-wrap items-center gap-2">
                            <h1 className="text-lg font-semibold">{award.name}</h1>
                            {award.division ? (
                                <span className="rounded-full bg-secondary px-2 py-0.5 text-xs">
                                    {award.division.name}
                                    {!award.division.active ? ' · Legacy' : ''}
                                </span>
                            ) : (
                                <span className="rounded-full bg-secondary px-2 py-0.5 text-xs">Clan-wide</span>
                            )}
                            <RarityPill rarity={stats.rarity} />
                            {award.repeatable && (
                                <span className="flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs text-muted-foreground">
                                    <RefreshCw className="size-3" /> Repeatable
                                </span>
                            )}
                        </div>
                        {award.description && (
                            <p className="mt-2 text-sm text-muted-foreground">{award.description}</p>
                        )}
                        <div className="mt-3">
                            {userHasAward && !award.repeatable ? (
                                <span className="inline-flex items-center gap-1.5 rounded-md bg-success/15 px-3 py-1.5 text-sm text-success">
                                    <Check className="size-4" /> Already received
                                </span>
                            ) : award.canRequest ? (
                                <RequestDialog awardId={award.id} currentMember={currentMember} />
                            ) : null}
                        </div>
                    </div>
                </div>

                <section>
                    <div className="mb-3 flex items-center justify-between">
                        <h2 className="text-sm font-semibold">Recipients</h2>
                        <span className="numeric text-xs text-muted-foreground">{stats.total}</span>
                    </div>
                    {recipients.data.length === 0 ? (
                        <p className="rounded-md border border-border bg-card p-6 text-center text-sm text-muted-foreground">
                            This award remains elusive. Perhaps you are up to the task?
                        </p>
                    ) : (
                        <>
                            <div className="overflow-x-auto rounded-md border border-border">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Member</TableHead>
                                            <TableHead>Division</TableHead>
                                            <TableHead>Rank</TableHead>
                                            <TableHead className="text-right">
                                                {award.repeatable ? 'Most recent' : 'Awarded'}
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {recipients.data.map((r, i) => (
                                            <TableRow key={i}>
                                                <TableCell>
                                                    <span className="flex items-center gap-2">
                                                        {r.avatarUrl && (
                                                            <img
                                                                src={r.avatarUrl}
                                                                alt=""
                                                                className="size-6 rounded-full"
                                                            />
                                                        )}
                                                        {r.url ? (
                                                            <a href={r.url} className="font-medium hover:text-primary">
                                                                {r.name}
                                                            </a>
                                                        ) : (
                                                            <span className="text-muted-foreground">Unknown</span>
                                                        )}
                                                        {r.timesReceived && r.timesReceived > 1 && (
                                                            <span className="text-xs text-muted-foreground">
                                                                ×{r.timesReceived}
                                                            </span>
                                                        )}
                                                    </span>
                                                </TableCell>
                                                <TableCell className="text-muted-foreground">
                                                    {r.divisionUrl ? (
                                                        <a href={r.divisionUrl} className="hover:text-foreground">
                                                            {r.division}
                                                        </a>
                                                    ) : (
                                                        '—'
                                                    )}
                                                </TableCell>
                                                <TableCell className="text-muted-foreground">{r.rank ?? '—'}</TableCell>
                                                <TableCell className="numeric text-right text-muted-foreground">
                                                    {r.awardedAt}
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                            {recipients.lastPage > 1 && (
                                <div className="mt-3 flex items-center justify-center gap-2 text-sm">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={page <= 1}
                                        onClick={() =>
                                            router.get(`/clan/awards/${award.id}`, { page: page - 1 }, { preserveScroll: true })
                                        }
                                    >
                                        Previous
                                    </Button>
                                    <span className="numeric text-muted-foreground">
                                        {page} / {recipients.lastPage}
                                    </span>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={page >= recipients.lastPage}
                                        onClick={() =>
                                            router.get(`/clan/awards/${award.id}`, { page: page + 1 }, { preserveScroll: true })
                                        }
                                    >
                                        Next
                                    </Button>
                                </div>
                            )}
                        </>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
