import { Head, router } from '@inertiajs/react';
import { ArrowLeft, ChevronRight, FileEdit, HelpCircle, IdCard, Paperclip, Send, Trophy, X } from 'lucide-react';
import { type FormEvent, useState } from 'react';
import { toast } from 'sonner';

import type { TicketType } from '@/components/tickets/shared';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    ticketTypes: TicketType[];
}

const TYPE_ICON: Record<string, typeof HelpCircle> = {
    'forum-change': FileEdit,
    'awards-medals': Trophy,
    'member-rename': IdCard,
    misc: HelpCircle,
};

export default function TicketCreate({ ticketTypes }: Props) {
    const [selected, setSelected] = useState<TicketType | null>(null);

    return (
        <AppLayout
            header={{
                eyebrow: 'Help center',
                title: 'New request',
                breadcrumbs: [{ label: 'Help center', href: '/help/tickets' }, { label: 'New request' }],
                actions: (
                    <Button variant="ghost" size="sm" onClick={() => (selected ? setSelected(null) : router.get('/help/tickets'))}>
                        <ArrowLeft /> Back
                    </Button>
                ),
            }}
        >
            <Head title="New request" />

            {!selected ? (
                <div className="space-y-4">
                    <div className="text-center">
                        <h2 className="text-lg font-semibold">What do you need help with?</h2>
                        <p className="text-sm text-muted-foreground">
                            Select the category that best describes your request.
                        </p>
                    </div>
                    {ticketTypes.length === 0 ? (
                        <p className="py-10 text-center text-sm text-muted-foreground">
                            There are no ticket types available for your role.
                        </p>
                    ) : (
                        <div className="mx-auto max-w-xl space-y-2">
                            {ticketTypes.map((type) => {
                                const Icon = TYPE_ICON[type.slug] ?? HelpCircle;
                                return (
                                    <button
                                        key={type.id}
                                        onClick={() => setSelected(type)}
                                        className="flex w-full items-center gap-3 rounded-md border border-border bg-card p-4 text-left transition-colors hover:border-primary/30"
                                    >
                                        <Icon className="size-5 shrink-0 text-primary" />
                                        <div className="min-w-0 flex-1">
                                            <p className="text-sm font-medium">{type.name}</p>
                                            <p className="text-xs text-muted-foreground">{type.description}</p>
                                        </div>
                                        <ChevronRight className="size-4 text-muted-foreground" />
                                    </button>
                                );
                            })}
                        </div>
                    )}
                </div>
            ) : (
                <TicketForm type={selected} onCancel={() => setSelected(null)} />
            )}
        </AppLayout>
    );
}

function TicketForm({ type, onCancel }: { type: TicketType; onCancel: () => void }) {
    const [description, setDescription] = useState('');
    const [files, setFiles] = useState<File[]>([]);
    const [previews, setPreviews] = useState<string[]>([]);
    const [submitting, setSubmitting] = useState(false);
    const [error, setError] = useState<string | null>(null);

    function addFiles(list: FileList | null) {
        if (!list) return;
        const remaining = 5 - files.length;
        Array.from(list)
            .slice(0, remaining)
            .forEach((file) => {
                setFiles((prev) => [...prev, file]);
                const reader = new FileReader();
                reader.onload = (e) => setPreviews((prev) => [...prev, e.target?.result as string]);
                reader.readAsDataURL(file);
            });
    }

    function removeFile(i: number) {
        setFiles((prev) => prev.filter((_, idx) => idx !== i));
        setPreviews((prev) => prev.filter((_, idx) => idx !== i));
    }

    async function submit(e: FormEvent) {
        e.preventDefault();
        if (description.trim().length < 25) return;
        setSubmitting(true);
        setError(null);

        const form = new FormData();
        form.append('ticket_type_id', String(type.id));
        form.append('description', description);
        files.forEach((file) => form.append('attachments[]', file));

        try {
            const res = await fetch('/api/tickets', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':
                        document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: form,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(data.message ?? 'Failed to create ticket');
            router.visit(`/help/tickets/${data.ticket.id}`);
        } catch (err) {
            setError(err instanceof Error ? err.message : 'Failed to create ticket');
            toast.error('Failed to create ticket');
            setSubmitting(false);
        }
    }

    return (
        <form onSubmit={submit} className="mx-auto max-w-2xl space-y-5">
            <div className="flex items-center gap-3">
                <FileEdit className="size-5 text-primary" />
                <div>
                    <p className="text-xs uppercase tracking-wide text-muted-foreground">New request</p>
                    <h2 className="text-lg font-semibold">{type.name}</h2>
                </div>
            </div>

            {type.boilerplate && (
                <div className="overflow-hidden rounded-md border border-border">
                    <p className="border-b border-border bg-card/40 px-4 py-2 text-xs font-semibold">
                        Please include the following information
                    </p>
                    <pre className="whitespace-pre-wrap p-4 text-xs text-muted-foreground">{type.boilerplate}</pre>
                </div>
            )}

            {error && (
                <p className="rounded-md border border-destructive/40 bg-destructive/5 px-4 py-2 text-sm">{error}</p>
            )}

            <div className="space-y-1.5">
                <label htmlFor="description" className="text-sm font-medium">
                    Description
                </label>
                <textarea
                    id="description"
                    value={description}
                    onChange={(e) => setDescription(e.target.value)}
                    rows={10}
                    disabled={submitting}
                    placeholder="Describe your issue or request in detail…"
                    className="w-full rounded-md border border-input bg-transparent p-3 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                />
                <p className={cn('text-xs', description.length >= 25 ? 'text-success' : 'text-muted-foreground')}>
                    {description.length} / 25 minimum
                </p>
            </div>

            <div className="space-y-2">
                <div className="flex items-center justify-between">
                    <span className="flex items-center gap-1.5 text-sm font-medium">
                        <Paperclip className="size-3.5" /> Attachments
                        <span className="text-xs font-normal text-muted-foreground">
                            images only · max 5 · 1 MB each
                        </span>
                    </span>
                    {files.length < 5 && (
                        <label className="cursor-pointer text-xs text-primary hover:underline">
                            + Add image
                            <input
                                type="file"
                                accept="image/*"
                                multiple
                                className="hidden"
                                disabled={submitting}
                                onChange={(e) => {
                                    addFiles(e.target.files);
                                    e.target.value = '';
                                }}
                            />
                        </label>
                    )}
                </div>
                {previews.length > 0 && (
                    <div className="flex flex-wrap gap-2">
                        {previews.map((src, i) => (
                            <div key={i} className="relative">
                                <img src={src} alt="" className="size-16 rounded-md object-cover" />
                                <button
                                    type="button"
                                    onClick={() => removeFile(i)}
                                    className="absolute -right-1.5 -top-1.5 grid size-5 place-items-center rounded-full bg-destructive text-white"
                                >
                                    <X className="size-3" />
                                </button>
                            </div>
                        ))}
                    </div>
                )}
            </div>

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" size="sm" onClick={onCancel} disabled={submitting}>
                    Cancel
                </Button>
                <Button type="submit" size="sm" disabled={submitting || description.trim().length < 25}>
                    <Send /> Submit request
                </Button>
            </div>
        </form>
    );
}
