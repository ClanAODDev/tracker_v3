import { usePage } from '@inertiajs/react';
import { ArrowLeft, Clock, MessageSquare, RefreshCw, Send, Trash2, UserPlus } from 'lucide-react';
import { type FormEvent, useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { getJson, postJson } from '@/lib/api';
import { linkify, linkifyHtml, relativeDate } from '@/lib/format';
import type { SharedProps } from '@/types';

interface Response {
    label: string;
    value: string;
}

interface Comment {
    id: number;
    body: string;
    created_at: string;
    user: { id: number; name: string; avatar: string | null } | null;
}

interface ApplicationSummary {
    id: number;
    discord_username: string;
    avatar: string | null;
    created_at: string;
    comments_count: number;
}

interface ApplicationDetail extends ApplicationSummary {
    discord_id: string | null;
    responses: Response[];
    comments: Comment[];
}

interface Props {
    url: string;
    canDelete: boolean;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    initialApplicationId?: number | null;
}

export function ApplicationsModal({ url, canDelete, open, onOpenChange, initialApplicationId }: Props) {
    const currentUserId = usePage<SharedProps>().props.auth.user?.id ?? null;

    const [applications, setApplications] = useState<ApplicationSummary[]>([]);
    const [detail, setDetail] = useState<ApplicationDetail | null>(null);
    const [view, setView] = useState<'list' | 'detail'>('list');
    const [loadingList, setLoadingList] = useState(false);
    const [loadingDetail, setLoadingDetail] = useState(false);
    const [listError, setListError] = useState<string | null>(null);
    const [detailError, setDetailError] = useState<string | null>(null);

    const loadList = useCallback(() => {
        setLoadingList(true);
        setListError(null);
        return getJson<{ applications: ApplicationSummary[] }>(url)
            .then((res) => setApplications(res.applications ?? []))
            .catch(() => setListError('Failed to load applications.'))
            .finally(() => setLoadingList(false));
    }, [url]);

    const openDetail = useCallback(
        (id: number) => {
            setView('detail');
            setLoadingDetail(true);
            setDetailError(null);
            getJson<{ application: ApplicationDetail }>(`${url}/${id}`)
                .then((res) => setDetail(res.application))
                .catch(() => setDetailError('Failed to load application.'))
                .finally(() => setLoadingDetail(false));
        },
        [url],
    );

    useEffect(() => {
        if (!open) return;
        loadList().then(() => {
            if (initialApplicationId) openDetail(initialApplicationId);
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open]);

    function backToList() {
        setView('list');
        setDetail(null);
    }

    async function deleteApplication() {
        if (!detail || !confirm('Delete this application?')) return;
        try {
            await fetch(`${url}/${detail.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });
            setApplications((prev) => prev.filter((a) => a.id !== detail.id));
            toast.success('Application deleted');
            backToList();
        } catch {
            toast.error('Failed to delete application');
        }
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[85vh] max-w-2xl overflow-y-auto">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <UserPlus className="size-4 text-info" /> Pending applications
                    </DialogTitle>
                    <DialogDescription>
                        Discord applications for this division. Removed on recruitment or after 30 days.
                    </DialogDescription>
                </DialogHeader>

                {view === 'detail' && (
                    <Button variant="ghost" size="sm" className="w-fit" onClick={backToList}>
                        <ArrowLeft /> Back
                    </Button>
                )}

                {view === 'list' ? (
                    <ListView
                        applications={applications}
                        loading={loadingList}
                        error={listError}
                        onRetry={loadList}
                        onOpen={openDetail}
                    />
                ) : (
                    <DetailView
                        detail={detail}
                        loading={loadingDetail}
                        error={detailError}
                        canDelete={canDelete}
                        currentUserId={currentUserId}
                        commentsUrl={detail ? `${url}/${detail.id}/comments` : ''}
                        onRetry={() => detail && openDetail(detail.id)}
                        onDelete={deleteApplication}
                        onCommentAdded={(comment) => {
                            setDetail((d) => (d ? { ...d, comments: [...d.comments, comment] } : d));
                            setApplications((prev) =>
                                prev.map((a) =>
                                    a.id === detail?.id ? { ...a, comments_count: a.comments_count + 1 } : a,
                                ),
                            );
                        }}
                        onCommentDeleted={(commentId) => {
                            setDetail((d) =>
                                d ? { ...d, comments: d.comments.filter((c) => c.id !== commentId) } : d,
                            );
                            setApplications((prev) =>
                                prev.map((a) =>
                                    a.id === detail?.id
                                        ? { ...a, comments_count: Math.max(0, a.comments_count - 1) }
                                        : a,
                                ),
                            );
                        }}
                    />
                )}
            </DialogContent>
        </Dialog>
    );
}

function ListView({
    applications,
    loading,
    error,
    onRetry,
    onOpen,
}: {
    applications: ApplicationSummary[];
    loading: boolean;
    error: string | null;
    onRetry: () => void;
    onOpen: (id: number) => void;
}) {
    if (loading) return <p className="py-10 text-center text-sm text-muted-foreground">Loading applications…</p>;
    if (error)
        return (
            <div className="flex items-center justify-between rounded-md border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm">
                {error}
                <Button size="sm" variant="outline" onClick={onRetry}>
                    <RefreshCw /> Retry
                </Button>
            </div>
        );
    if (applications.length === 0)
        return (
            <p className="py-10 text-center text-sm text-muted-foreground">
                No pending Discord applications for this division.
            </p>
        );

    return (
        <div className="space-y-2">
            {applications.map((app) => (
                <button
                    key={app.id}
                    onClick={() => onOpen(app.id)}
                    className="flex w-full items-center gap-3 rounded-md border border-border bg-card p-3 text-left transition-colors hover:border-primary/30"
                >
                    {app.avatar ? (
                        <img src={app.avatar} alt="" className="size-9 rounded-full" />
                    ) : (
                        <span className="grid size-9 place-items-center rounded-full bg-muted text-muted-foreground">
                            <UserPlus className="size-4" />
                        </span>
                    )}
                    <div className="min-w-0 flex-1">
                        <p className="truncate text-sm font-medium">{app.discord_username}</p>
                        <p className="flex items-center gap-3 text-xs text-muted-foreground">
                            <span className="flex items-center gap-1">
                                <Clock className="size-3" /> {relativeDate(app.created_at)}
                            </span>
                            {app.comments_count > 0 && (
                                <span className="flex items-center gap-1">
                                    <MessageSquare className="size-3" /> {app.comments_count}
                                </span>
                            )}
                        </p>
                    </div>
                </button>
            ))}
        </div>
    );
}

function DetailView({
    detail,
    loading,
    error,
    canDelete,
    currentUserId,
    commentsUrl,
    onRetry,
    onDelete,
    onCommentAdded,
    onCommentDeleted,
}: {
    detail: ApplicationDetail | null;
    loading: boolean;
    error: string | null;
    canDelete: boolean;
    currentUserId: number | null;
    commentsUrl: string;
    onRetry: () => void;
    onDelete: () => void;
    onCommentAdded: (comment: Comment) => void;
    onCommentDeleted: (commentId: number) => void;
}) {
    const [body, setBody] = useState('');
    const [sending, setSending] = useState(false);

    if (loading) return <p className="py-10 text-center text-sm text-muted-foreground">Loading application…</p>;
    if (error)
        return (
            <div className="flex items-center justify-between rounded-md border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm">
                {error}
                <Button size="sm" variant="outline" onClick={onRetry}>
                    <RefreshCw /> Retry
                </Button>
            </div>
        );
    if (!detail) return null;

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (body.trim().length < 5) return;
        setSending(true);
        try {
            const res = await postJson<{ comment: Comment }>(commentsUrl, { body });
            onCommentAdded(res.comment);
            setBody('');
        } catch (err) {
            toast.error(err instanceof Error ? err.message : 'Failed to add comment');
        } finally {
            setSending(false);
        }
    }

    async function removeComment(id: number) {
        try {
            await fetch(`${commentsUrl}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
            });
            onCommentDeleted(id);
        } catch {
            toast.error('Failed to delete comment');
        }
    }

    const comments = [...detail.comments].sort(
        (a, b) => new Date(a.created_at).getTime() - new Date(b.created_at).getTime(),
    );

    return (
        <div className="space-y-5">
            <div className="tron-corners flex items-center justify-between gap-3 rounded-md border border-border bg-card p-4">
                <div className="flex items-center gap-3">
                    {detail.avatar ? (
                        <img src={detail.avatar} alt="" className="size-11 rounded-full" />
                    ) : (
                        <span className="grid size-11 place-items-center rounded-full bg-muted text-muted-foreground">
                            <UserPlus className="size-5" />
                        </span>
                    )}
                    <div>
                        <p className="font-semibold">{detail.discord_username}</p>
                        <p className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                            <span className="flex items-center gap-1">
                                <Clock className="size-3" /> {relativeDate(detail.created_at)}
                            </span>
                            {detail.discord_id && (
                                <a
                                    href={`https://discord.com/users/${detail.discord_id}`}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="rounded bg-info/15 px-1.5 py-0.5 text-info hover:bg-info/25"
                                >
                                    Add friend
                                </a>
                            )}
                        </p>
                    </div>
                </div>
                {canDelete && (
                    <Button size="sm" variant="destructive" onClick={onDelete}>
                        <Trash2 /> Delete
                    </Button>
                )}
            </div>

            <section className="overflow-hidden rounded-md border border-border">
                <h3 className="border-b border-border bg-card/40 px-4 py-2 text-sm font-semibold">Responses</h3>
                <dl className="space-y-3 p-4">
                    {detail.responses.map((r, i) => (
                        <div key={i}>
                            <dt
                                className="text-[11px] font-semibold uppercase tracking-wide text-muted-foreground [&_p]:m-0"
                                dangerouslySetInnerHTML={{ __html: linkifyHtml(r.label) }}
                            />
                            <dd
                                className="mt-0.5 whitespace-pre-wrap text-sm"
                                dangerouslySetInnerHTML={{ __html: linkify(r.value) }}
                            />
                        </div>
                    ))}
                </dl>
            </section>

            <section className="overflow-hidden rounded-md border border-border">
                <h3 className="flex items-center gap-2 border-b border-border bg-card/40 px-4 py-2 text-sm font-semibold">
                    Internal comments
                    <span className="text-xs font-normal text-muted-foreground">
                        not visible to member · {comments.length}
                    </span>
                </h3>
                <div className="space-y-3 p-4">
                    {comments.length === 0 && (
                        <p className="py-4 text-center text-sm text-muted-foreground">No comments yet.</p>
                    )}
                    {comments.map((comment) => (
                        <div key={comment.id} className="flex gap-3">
                            {comment.user?.avatar ? (
                                <img src={comment.user.avatar} alt="" className="size-8 shrink-0 rounded-full" />
                            ) : (
                                <span className="grid size-8 shrink-0 place-items-center rounded-full bg-muted text-muted-foreground">
                                    <MessageSquare className="size-3.5" />
                                </span>
                            )}
                            <div className="min-w-0 flex-1 rounded-md bg-card p-2.5">
                                <div className="flex items-center justify-between gap-2 text-xs">
                                    <span className="font-medium">{comment.user?.name ?? 'Unknown'}</span>
                                    <span className="flex items-center gap-2 text-muted-foreground">
                                        {relativeDate(comment.created_at)}
                                        {comment.user?.id === currentUserId && (
                                            <button
                                                onClick={() => removeComment(comment.id)}
                                                className="hover:text-destructive"
                                                title="Delete comment"
                                            >
                                                <Trash2 className="size-3" />
                                            </button>
                                        )}
                                    </span>
                                </div>
                                <p
                                    className="mt-1 whitespace-pre-wrap text-sm text-muted-foreground"
                                    dangerouslySetInnerHTML={{ __html: linkify(comment.body) }}
                                />
                            </div>
                        </div>
                    ))}

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
                </div>
            </section>
        </div>
    );
}
