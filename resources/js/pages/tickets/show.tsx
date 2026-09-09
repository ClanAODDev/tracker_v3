import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowLeft,
    Bell,
    BellOff,
    Check,
    CheckCircle2,
    Hand,
    RotateCcw,
    Send,
    UserCog,
    X,
} from 'lucide-react';
import { type FormEvent, useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';

import {
    isSystemMessage,
    renderTicketText,
    StatusBadge,
    type TicketComment,
    type TicketDetail,
    type Worker,
} from '@/components/tickets/shared';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { SimpleSelect } from '@/components/ui/simple-select';
import { postJson } from '@/lib/api';
import { relativeDate } from '@/lib/format';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    ticket: TicketDetail;
    canWork: boolean;
    currentUserId: number;
    workers: Worker[];
}

function stateColor(state: string): string {
    return `var(--${
        state === 'resolved'
            ? 'success'
            : state === 'rejected'
              ? 'destructive'
              : state === 'assigned'
                ? 'warning'
                : 'info'
    })`;
}

function playChime() {
    try {
        const ctx = new (window.AudioContext || (window as unknown as { webkitAudioContext: typeof AudioContext }).webkitAudioContext)();
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.setValueAtTime(800, ctx.currentTime);
        osc.frequency.setValueAtTime(600, ctx.currentTime + 0.1);
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.3);
        osc.start();
        osc.stop(ctx.currentTime + 0.3);
    } catch {
        /* no audio */
    }
}

export default function TicketShow({ ticket, canWork, currentUserId, workers }: Props) {
    const [soundOn, setSoundOn] = useState(true);
    const [rejectOpen, setRejectOpen] = useState(false);
    const [reassignOpen, setReassignOpen] = useState(false);
    const prev = useRef({ comments: ticket.comments.length, owner: ticket.owner?.id ?? null });

    // poll every 30s for new comments / owner changes
    useEffect(() => {
        const timer = setInterval(() => {
            router.reload({ only: ['ticket'] });
        }, 30000);
        return () => clearInterval(timer);
    }, []);

    useEffect(() => {
        const now = { comments: ticket.comments.length, owner: ticket.owner?.id ?? null };
        if (soundOn && (now.comments > prev.current.comments || now.owner !== prev.current.owner)) {
            playChime();
        }
        prev.current = now;
    }, [ticket, soundOn]);

    const resolved = ticket.state === 'resolved' || ticket.state === 'rejected';
    const canAssignSelf =
        ticket.state === 'new' || (ticket.state === 'assigned' && ticket.owner?.id !== currentUserId);
    const canModify = ticket.state === 'new' || ticket.state === 'assigned';

    async function act(action: string, data: Record<string, unknown> = {}) {
        try {
            await postJson(`/api/tickets/${ticket.id}/${action}`, data);
            router.reload({ only: ['ticket', 'workers'] });
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Action failed');
        }
    }

    const comments = useMemo(
        () => [...ticket.comments].sort((a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime()),
        [ticket.comments],
    );

    return (
        <AppLayout
            header={{
                eyebrow: 'Help center',
                title: `Ticket #${ticket.id}`,
                breadcrumbs: [
                    { label: 'Help center', href: '/help/tickets' },
                    { label: `#${ticket.id}` },
                ],
                actions: (
                    <Button variant="ghost" size="sm" asChild>
                        <Link href="/help/tickets">
                            <ArrowLeft /> Back
                        </Link>
                    </Button>
                ),
            }}
        >
            <Head title={`Ticket #${ticket.id}`} />

            <div className="space-y-5">
                <div
                    className="tron-corners relative rounded-md border border-border bg-card p-4"
                    style={{ '--corner-color': stateColor(ticket.state) } as React.CSSProperties}
                >
                    <span
                        className="absolute inset-x-0 top-0 h-px rounded-t-md"
                        style={{
                            background: `linear-gradient(90deg, transparent, ${stateColor(ticket.state)}, transparent)`,
                        }}
                    />
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="numeric font-semibold">#{ticket.id}</span>
                        <StatusBadge state={ticket.state} />
                        <span className="text-sm text-muted-foreground">{ticket.type?.name ?? 'Unknown type'}</span>
                    </div>
                    <div className="mt-3 grid gap-3 text-sm sm:grid-cols-3">
                        <Meta label="Submitted by">
                            {ticket.caller ? (
                                <span className="flex items-center gap-1.5">
                                    <Avatar person={ticket.caller} /> {ticket.caller.name}
                                    {ticket.division && (
                                        <span className="rounded bg-muted px-1.5 py-0.5 text-xs text-muted-foreground">
                                            {ticket.division.name}
                                        </span>
                                    )}
                                </span>
                            ) : (
                                '—'
                            )}
                        </Meta>
                        <Meta label="Assigned to">
                            {ticket.owner ? (
                                <span className="flex items-center gap-1.5">
                                    <Avatar person={ticket.owner} /> {ticket.owner.name}
                                </span>
                            ) : (
                                <span className="text-muted-foreground">Awaiting assignment</span>
                            )}
                        </Meta>
                        <Meta label="Created">
                            <span className="numeric text-muted-foreground">{relativeDate(ticket.created_at)}</span>
                        </Meta>
                    </div>
                </div>

                {canWork && (
                    <div className="flex flex-wrap gap-2">
                        {canAssignSelf && (
                            <Button size="sm" onClick={() => act('own')}>
                                <Hand /> Assign to me
                            </Button>
                        )}
                        {canModify && (
                            <Button size="sm" variant="outline" onClick={() => setReassignOpen(true)}>
                                <UserCog /> Assign to…
                            </Button>
                        )}
                        {canModify && (
                            <Button size="sm" variant="outline" className="text-success" onClick={() => act('resolve')}>
                                <CheckCircle2 /> Resolve
                            </Button>
                        )}
                        {canModify && (
                            <Button
                                size="sm"
                                variant="outline"
                                className="text-destructive"
                                onClick={() => setRejectOpen(true)}
                            >
                                <X /> Reject
                            </Button>
                        )}
                        {resolved && (
                            <Button size="sm" variant="outline" className="text-warning" onClick={() => act('reopen')}>
                                <RotateCcw /> Reopen
                            </Button>
                        )}
                    </div>
                )}

                <section className="overflow-hidden rounded-md border border-border">
                    <h3 className="border-b border-border bg-card/40 px-4 py-2 text-sm font-semibold">Description</h3>
                    <div className="p-4">
                        <div
                            className="whitespace-pre-wrap text-sm"
                            dangerouslySetInnerHTML={{ __html: renderTicketText(ticket.description) }}
                        />
                        {ticket.attachments.length > 0 && (
                            <div className="mt-3 flex flex-wrap gap-2">
                                {ticket.attachments.map((url, i) => (
                                    <a key={i} href={url} target="_blank" rel="noreferrer">
                                        <img src={url} alt="" className="size-20 rounded-md object-cover" />
                                    </a>
                                ))}
                            </div>
                        )}
                    </div>
                </section>

                <section className="overflow-hidden rounded-md border border-border">
                    <h3 className="flex items-center gap-2 border-b border-border bg-card/40 px-4 py-2 text-sm font-semibold">
                        Discussion
                        <span className="text-xs font-normal text-muted-foreground">
                            {comments.filter((c) => !isSystemMessage(c)).length}
                        </span>
                        <button
                            onClick={() => setSoundOn((v) => !v)}
                            className="ml-auto text-muted-foreground hover:text-foreground"
                            title={soundOn ? 'Sound on' : 'Sound off'}
                        >
                            {soundOn ? <Bell className="size-3.5" /> : <BellOff className="size-3.5" />}
                        </button>
                    </h3>
                    <div className="space-y-3 p-4">
                        {comments.length === 0 && (
                            <p className="py-4 text-center text-sm text-muted-foreground">No comments yet.</p>
                        )}
                        {comments.map((comment) =>
                            isSystemMessage(comment) ? (
                                <p key={comment.id} className="flex items-center gap-2 text-xs text-muted-foreground">
                                    <Check className="size-3" />
                                    <strong className="font-medium text-foreground">
                                        {comment.user?.name ?? 'Unknown'}
                                    </strong>
                                    {comment.body.replace(/^.*?\b(owned|assigned|resolved|reopened|rejected)\b/i, '$1')}
                                    <span className="ml-auto">{relativeDate(comment.created_at)}</span>
                                </p>
                            ) : (
                                <CommentRow
                                    key={comment.id}
                                    comment={comment}
                                    isOwner={comment.user?.id === ticket.owner?.id}
                                />
                            ),
                        )}

                        {!resolved && <CommentForm ticketId={ticket.id} />}
                    </div>
                </section>
            </div>

            <Dialog open={rejectOpen} onOpenChange={setRejectOpen}>
                <RejectDialog
                    onSubmit={async (reason) => {
                        await act('reject', { reason });
                        setRejectOpen(false);
                    }}
                />
            </Dialog>

            <Dialog open={reassignOpen} onOpenChange={setReassignOpen}>
                <ReassignDialog
                    workers={workers}
                    onSubmit={async (userId) => {
                        await act('reassign', { user_id: userId });
                        setReassignOpen(false);
                    }}
                />
            </Dialog>
        </AppLayout>
    );
}

function Meta({ label, children }: { label: string; children: React.ReactNode }) {
    return (
        <div>
            <p className="text-xs uppercase tracking-wide text-muted-foreground">{label}</p>
            <div className="mt-0.5">{children}</div>
        </div>
    );
}

function Avatar({ person }: { person: { name: string; avatar: string | null } }) {
    return person.avatar ? (
        <img src={person.avatar} alt="" className="size-5 rounded-full" />
    ) : (
        <span className="grid size-5 place-items-center rounded-full bg-muted text-[9px]">
            {person.name.charAt(0).toUpperCase()}
        </span>
    );
}

function CommentRow({ comment, isOwner }: { comment: TicketComment; isOwner: boolean }) {
    return (
        <div className="flex gap-3">
            <Avatar person={comment.user ?? { name: '?', avatar: null }} />
            <div className="min-w-0 flex-1 rounded-md bg-card p-2.5">
                <div className="flex items-center justify-between gap-2 text-xs">
                    <span className="font-medium">
                        {comment.user?.name ?? 'Unknown'}
                        {isOwner && (
                            <span className="ml-1.5 rounded bg-primary/15 px-1 text-[10px] text-primary">Support</span>
                        )}
                    </span>
                    <span className="text-muted-foreground">{relativeDate(comment.created_at)}</span>
                </div>
                <div
                    className="mt-1 whitespace-pre-wrap text-sm text-muted-foreground"
                    dangerouslySetInnerHTML={{ __html: renderTicketText(comment.body) }}
                />
            </div>
        </div>
    );
}

function CommentForm({ ticketId }: { ticketId: number }) {
    const [body, setBody] = useState('');
    const [sending, setSending] = useState(false);

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (body.trim().length < 5) return;
        setSending(true);
        try {
            await postJson(`/api/tickets/${ticketId}/comments`, { body });
            setBody('');
            router.reload({ only: ['ticket'] });
        } catch (err) {
            toast.error(err instanceof Error ? err.message : 'Failed to add comment');
        } finally {
            setSending(false);
        }
    }

    return (
        <form onSubmit={submit} className="border-t border-border pt-3">
            <textarea
                value={body}
                onChange={(e) => setBody(e.target.value)}
                rows={3}
                placeholder="Write a comment…"
                disabled={sending}
                className="w-full rounded-md border border-input bg-transparent p-2 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
            />
            <div className="mt-2 flex items-center justify-between">
                <span className="text-xs text-muted-foreground">Minimum 5 characters</span>
                <Button type="submit" size="sm" disabled={sending || body.trim().length < 5}>
                    <Send /> Send
                </Button>
            </div>
        </form>
    );
}

function RejectDialog({ onSubmit }: { onSubmit: (reason: string) => Promise<void> }) {
    const [reason, setReason] = useState('');
    const [busy, setBusy] = useState(false);
    return (
        <DialogContent className="max-w-md">
            <DialogHeader>
                <DialogTitle>Reject ticket</DialogTitle>
                <DialogDescription>The caller is notified with your reason.</DialogDescription>
            </DialogHeader>
            <textarea
                value={reason}
                onChange={(e) => setReason(e.target.value)}
                rows={3}
                placeholder="Explain why this ticket is being rejected…"
                className="w-full rounded-md border border-input bg-transparent p-2 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
            />
            <DialogFooter>
                <Button
                    variant="destructive"
                    size="sm"
                    disabled={busy || reason.trim().length < 5}
                    onClick={async () => {
                        setBusy(true);
                        await onSubmit(reason);
                        setBusy(false);
                    }}
                >
                    Reject ticket
                </Button>
            </DialogFooter>
        </DialogContent>
    );
}

function ReassignDialog({
    workers,
    onSubmit,
}: {
    workers: Worker[];
    onSubmit: (userId: string) => Promise<void>;
}) {
    const [userId, setUserId] = useState('');
    const [busy, setBusy] = useState(false);
    return (
        <DialogContent className="max-w-md">
            <DialogHeader>
                <DialogTitle>Assign ticket</DialogTitle>
                <DialogDescription>Assign this ticket to another eligible worker.</DialogDescription>
            </DialogHeader>
            <div className="grid gap-1.5">
                <Label>Assign to</Label>
                <SimpleSelect
                    value={userId || '__all'}
                    onChange={(v) => setUserId(v === '__all' ? '' : v)}
                    placeholder="Select a member…"
                    options={[
                        { value: '__all', label: 'Select a member…' },
                        ...workers.map((w) => ({ value: String(w.id), label: w.rank_name ?? w.name })),
                    ]}
                />
            </div>
            <DialogFooter>
                <Button
                    size="sm"
                    disabled={busy || !userId}
                    onClick={async () => {
                        setBusy(true);
                        await onSubmit(userId);
                        setBusy(false);
                    }}
                >
                    Assign
                </Button>
            </DialogFooter>
        </DialogContent>
    );
}
