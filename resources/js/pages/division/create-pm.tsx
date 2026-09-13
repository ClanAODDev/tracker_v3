import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Bell, Link as LinkIcon } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { postJson } from '@/lib/api';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    division: { name: string; slug: string };
    groups: Array<{ label: string; url: string }>;
    recipientCount: number;
    omitted: string[];
    canRemind: boolean;
    reminderUrl: string;
    reminderIds: number[];
}

export default function CreatePm({
    division,
    groups,
    recipientCount,
    omitted,
    canRemind,
    reminderUrl,
    reminderIds,
}: Props) {
    const [visited, setVisited] = useState<Set<string>>(new Set());
    const [confirmReminder, setConfirmReminder] = useState(false);
    const [reminding, setReminding] = useState(false);
    const [reminderDone, setReminderDone] = useState(false);

    async function markReminded() {
        setReminding(true);
        try {
            const res = await postJson<{ count: number; skipped: number }>(reminderUrl, { member_ids: reminderIds });
            let msg = `${res.count} member${res.count === 1 ? '' : 's'} marked as reminded`;
            if (res.skipped > 0) msg += ` (${res.skipped} skipped — already reminded today)`;
            toast.success(msg);
            setReminderDone(true);
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to set reminders');
        } finally {
            setReminding(false);
        }
    }

    return (
        <AppLayout
            header={{
                eyebrow: division.name,
                title: 'Bulk messaging',
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: division.name, href: `/divisions/${division.slug}` },
                    { label: 'Send private message' },
                ],
            }}
        >
            <Head title={`Bulk messaging · ${division.name}`} />

            <div className="max-w-2xl space-y-6">
                <p className="text-sm text-muted-foreground">
                    The AOD forums cap private messages at 20 recipients. Recipients have been split into groups — open
                    each to compose that batch.
                </p>

                <section className="tron-corners rounded-md border border-border bg-card">
                    <div className="flex items-center justify-between border-b border-border px-4 py-3">
                        <h2 className="text-sm font-semibold">Message groups</h2>
                        <span className="text-xs text-muted-foreground">{recipientCount} recipients</span>
                    </div>
                    <div className="flex flex-wrap gap-2 p-4">
                        {groups.length === 0 && (
                            <p className="text-sm text-muted-foreground">No selected members accept forum PMs.</p>
                        )}
                        {groups.map((group) => (
                            <Button
                                key={group.label}
                                variant={visited.has(group.label) ? 'ghost' : 'outline'}
                                size="sm"
                                asChild
                                onClick={() => setVisited((prev) => new Set(prev).add(group.label))}
                            >
                                <a href={group.url} target="_blank" rel="noreferrer">
                                    <LinkIcon /> {group.label}
                                </a>
                            </Button>
                        ))}
                    </div>
                    {omitted.length > 0 && (
                        <div className="border-t border-border px-4 py-3 text-xs text-muted-foreground">
                            <strong className="text-foreground">{omitted.length}</strong> member
                            {omitted.length === 1 ? '' : 's'} filtered out — they don't accept PMs from forum
                            administrators: {omitted.join(', ')}
                        </div>
                    )}
                </section>

                {canRemind && groups.length > 0 && (
                    <section className="rounded-md border border-border bg-card">
                        <div className="border-b border-border px-4 py-3">
                            <h2 className="flex items-center gap-2 text-sm font-semibold">
                                <Bell className="size-4" /> Inactivity reminder tracking
                            </h2>
                        </div>
                        <div className="space-y-3 p-4">
                            <p className="text-xs text-muted-foreground">
                                If this is an inactivity reminder, mark these members as reminded to track follow-ups.
                            </p>
                            {reminderDone ? (
                                <p className="text-sm text-success">Reminder date recorded.</p>
                            ) : (
                                <>
                                    <label className="flex items-center gap-2 text-sm">
                                        <Checkbox
                                            checked={confirmReminder}
                                            onCheckedChange={(v) => setConfirmReminder(v === true)}
                                        />
                                        Mark {recipientCount} member{recipientCount === 1 ? '' : 's'} as reminded
                                    </label>
                                    <Button
                                        size="sm"
                                        disabled={!confirmReminder || reminding}
                                        onClick={markReminded}
                                    >
                                        <Bell /> Set reminder date
                                    </Button>
                                </>
                            )}
                        </div>
                    </section>
                )}

                <Button variant="ghost" size="sm" asChild>
                    <Link href={`/divisions/${division.slug}/members`}>
                        <ArrowLeft /> Back to members
                    </Link>
                </Button>
            </div>
        </AppLayout>
    );
}
