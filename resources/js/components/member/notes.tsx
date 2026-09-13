import { router, useForm } from '@inertiajs/react';
import { ExternalLink, MessageSquare, Pencil, Plus, RotateCcw, Shield, ThumbsDown, ThumbsUp, Trash2 } from 'lucide-react';
import { type FormEvent, type ReactNode, useState } from 'react';

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
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

export interface MemberNote {
    id: number;
    type: string;
    body: string;
    authorName: string | null;
    authorUrl: string | null;
    authorAvatar: string | null;
    createdAt: string;
    createdAtFull: string;
    forumThreadUrl: string | null;
    canDelete: boolean;
    deleteUrl: string;
    deletedAt: string | null;
    restoreUrl: string | null;
    forceDeleteUrl: string | null;
}

const TYPE_META: Record<string, { icon: typeof ThumbsUp; className: string }> = {
    positive: { icon: ThumbsUp, className: 'text-success' },
    negative: { icon: ThumbsDown, className: 'text-destructive' },
    sr_ldr: { icon: Shield, className: 'text-primary' },
    general: { icon: MessageSquare, className: 'text-muted-foreground' },
};

function NoteCard({ note, memberClanId, trashed }: { note: MemberNote; memberClanId: number; trashed?: boolean }) {
    const meta = TYPE_META[note.type] ?? TYPE_META.general;
    const Icon = meta.icon;
    const edit = useForm({ body: note.body, type: note.type });
    const [editing, setEditing] = useState(false);

    function submitEdit(e: FormEvent) {
        e.preventDefault();
        edit.post(`/members/${memberClanId}/notes/${note.id}`, { onSuccess: () => setEditing(false) });
    }

    return (
        <div
            className={cn(
                'rounded-md border border-border bg-card p-3',
                trashed && 'border-destructive/30 opacity-80',
            )}
        >
            {editing ? (
                <form onSubmit={submitEdit} className="space-y-2">
                    <textarea
                        value={edit.data.body}
                        onChange={(e) => edit.setData('body', e.target.value)}
                        rows={3}
                        className="w-full rounded-md border border-input bg-transparent p-2 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                    />
                    <div className="flex justify-end gap-2">
                        <Button type="button" size="sm" variant="ghost" onClick={() => setEditing(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" size="sm" disabled={edit.processing}>
                            Save
                        </Button>
                    </div>
                </form>
            ) : (
                <p className="whitespace-pre-line text-sm">{note.body}</p>
            )}

            <div className="mt-2 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                <Icon className={cn('size-3.5', meta.className)} />
                {note.authorUrl ? (
                    <a href={note.authorUrl} className="hover:text-foreground">
                        {note.authorName}
                    </a>
                ) : (
                    <span>{note.authorName ?? 'Unknown'}</span>
                )}
                <span title={note.createdAtFull}>· {trashed ? `deleted ${note.deletedAt}` : note.createdAt}</span>
                {note.forumThreadUrl && (
                    <a
                        href={note.forumThreadUrl}
                        target="_blank"
                        rel="noreferrer"
                        className="inline-flex items-center gap-1 hover:text-foreground"
                    >
                        <ExternalLink className="size-3" /> Discussion
                    </a>
                )}
                <span className="ml-auto flex items-center gap-1">
                    {trashed ? (
                        <>
                            <Button
                                size="icon-xs"
                                variant="ghost"
                                onClick={() => router.post(note.restoreUrl!, {}, { preserveScroll: true })}
                                title="Restore"
                            >
                                <RotateCcw />
                            </Button>
                            <Button
                                size="icon-xs"
                                variant="ghost"
                                className="text-destructive"
                                onClick={() => router.delete(note.forceDeleteUrl!, { preserveScroll: true })}
                                title="Delete forever"
                            >
                                <Trash2 />
                            </Button>
                        </>
                    ) : (
                        note.canDelete && (
                            <>
                                <Button size="icon-xs" variant="ghost" onClick={() => setEditing(true)} title="Edit">
                                    <Pencil />
                                </Button>
                                <Button
                                    size="icon-xs"
                                    variant="ghost"
                                    className="text-destructive"
                                    onClick={() =>
                                        confirm('Delete this note?') &&
                                        router.delete(note.deleteUrl, { preserveScroll: true })
                                    }
                                    title="Delete"
                                >
                                    <Trash2 />
                                </Button>
                            </>
                        )
                    )}
                </span>
            </div>
        </div>
    );
}

export function NotesDialog({
    memberClanId,
    notes,
    trashedNotes,
    noteTypes,
    canViewTrashed,
    trigger,
    open,
    onOpenChange,
}: {
    memberClanId: number;
    notes: MemberNote[];
    trashedNotes: MemberNote[];
    noteTypes: Record<string, string>;
    canViewTrashed: boolean;
    trigger?: ReactNode;
    open?: boolean;
    onOpenChange?: (open: boolean) => void;
}) {
    const [showTrashed, setShowTrashed] = useState(false);
    const [adding, setAdding] = useState(false);
    const create = useForm({ type: Object.keys(noteTypes)[0], body: '' });

    function submit(e: FormEvent) {
        e.preventDefault();
        create.post(`/members/${memberClanId}/notes`, {
            preserveScroll: true,
            onSuccess: () => {
                create.reset('body');
                setAdding(false);
            },
        });
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            {trigger && <DialogTrigger asChild>{trigger}</DialogTrigger>}
            <DialogContent className="max-h-[80vh] max-w-2xl overflow-hidden">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        Member notes <span className="numeric text-sm text-muted-foreground">{notes.length}</span>
                    </DialogTitle>
                    <DialogDescription className="sr-only">Notes recorded for this member</DialogDescription>
                </DialogHeader>

                <div className="flex items-center justify-between">
                    {canViewTrashed && trashedNotes.length > 0 && (
                        <Button size="sm" variant="ghost" onClick={() => setShowTrashed((v) => !v)}>
                            <Trash2 /> {showTrashed ? 'Hide' : 'Show'} deleted ({trashedNotes.length})
                        </Button>
                    )}
                    <Button size="sm" className="ml-auto" onClick={() => setAdding((v) => !v)}>
                        <Plus /> Add note
                    </Button>
                </div>

                {adding && (
                    <form onSubmit={submit} className="space-y-3 rounded-md border border-border p-3">
                        <div className="flex flex-wrap gap-2">
                            {Object.entries(noteTypes).map(([value, label]) => (
                                <label
                                    key={value}
                                    className={cn(
                                        'cursor-pointer rounded-md border px-2.5 py-1 text-xs',
                                        create.data.type === value ? 'border-primary bg-primary/10' : 'border-border',
                                    )}
                                >
                                    <input
                                        type="radio"
                                        name="type"
                                        value={value}
                                        checked={create.data.type === value}
                                        onChange={() => create.setData('type', value)}
                                        className="sr-only"
                                    />
                                    {label}
                                </label>
                            ))}
                        </div>
                        <div className="grid gap-1.5">
                            <Label htmlFor="note-body">Note content</Label>
                            <textarea
                                id="note-body"
                                value={create.data.body}
                                onChange={(e) => create.setData('body', e.target.value)}
                                rows={3}
                                className="w-full rounded-md border border-input bg-transparent p-2 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                            />
                            {create.errors.body && <p className="text-xs text-destructive">{create.errors.body}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="submit" size="sm" disabled={create.processing}>
                                Save note
                            </Button>
                        </DialogFooter>
                    </form>
                )}

                <div className="space-y-2 overflow-y-auto">
                    {(showTrashed ? trashedNotes : notes).length === 0 ? (
                        <p className="py-6 text-center text-sm text-muted-foreground">
                            {showTrashed ? 'No deleted notes.' : 'No notes recorded for this member.'}
                        </p>
                    ) : (
                        (showTrashed ? trashedNotes : notes).map((note) => (
                            <NoteCard key={note.id} note={note} memberClanId={memberClanId} trashed={showTrashed} />
                        ))
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}
