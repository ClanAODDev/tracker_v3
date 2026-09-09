import { Head, Link, router } from '@inertiajs/react';
import { FileText, Lock, MessageSquare, Search, ThumbsDown, ThumbsUp, X } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { Input } from '@/components/ui/input';
import { SimpleSelect } from '@/components/ui/simple-select';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface Note {
    id: number;
    type: string;
    body: string;
    memberName: string;
    memberUrl: string;
    author: string;
    date: string;
}

interface Props {
    division: { name: string; slug: string };
    noteTypes: Record<string, string>;
    tags: Array<{ id: number; name: string }>;
    filters: { type: string | null; search: string | null; tag: number | null };
    notes: Note[];
}

const TYPE_ICON: Record<string, typeof ThumbsUp> = {
    positive: ThumbsUp,
    negative: ThumbsDown,
    sr_ldr: Lock,
    misc: MessageSquare,
};

const TYPE_TONE: Record<string, string> = {
    positive: 'text-success',
    negative: 'text-destructive',
    sr_ldr: 'text-primary',
    misc: 'text-muted-foreground',
};

export default function DivisionNotes({ division, noteTypes, tags, filters, notes }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const base = `/divisions/${division.slug}/notes`;

    function navigate(next: Partial<{ type: string | null; search: string | null; tag: number | null }>) {
        const merged = { ...filters, ...next };
        const params: Record<string, string> = {};
        if (merged.type) params.type = merged.type;
        if (merged.search) params.search = merged.search;
        if (merged.tag) params.tag = String(merged.tag);
        router.get(base, params, { preserveState: false });
    }

    function submitSearch(e: FormEvent) {
        e.preventDefault();
        navigate({ search: search || null });
    }

    return (
        <AppLayout
            header={{
                eyebrow: 'Division',
                title: `${division.name} — member notes`,
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: division.name, href: `/divisions/${division.slug}` },
                    { label: 'Notes' },
                ],
            }}
        >
            <Head title={`${division.name} notes`} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center gap-2">
                    <div className="flex flex-wrap gap-1.5">
                        <FilterChip active={!filters.type} onClick={() => navigate({ type: null })}>
                            All
                        </FilterChip>
                        {Object.entries(noteTypes).map(([key, label]) => {
                            const Icon = TYPE_ICON[key] ?? FileText;
                            return (
                                <FilterChip
                                    key={key}
                                    active={filters.type === key}
                                    onClick={() => navigate({ type: key })}
                                >
                                    <Icon className={cn('size-3.5', TYPE_TONE[key])} />
                                    {label}
                                </FilterChip>
                            );
                        })}
                    </div>

                    <div className="ml-auto flex flex-wrap items-center gap-2">
                        {tags.length > 0 && (
                            <SimpleSelect
                                value={filters.tag ? String(filters.tag) : '__all'}
                                onChange={(v) => navigate({ tag: v === '__all' ? null : Number(v) })}
                                options={[
                                    { value: '__all', label: 'All tags' },
                                    ...tags.map((t) => ({ value: String(t.id), label: t.name })),
                                ]}
                            />
                        )}
                        <form onSubmit={submitSearch} className="relative">
                            <Search className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                            <Input
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search member or content"
                                className="h-8 w-64 pl-8 pr-8"
                            />
                            {search && (
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSearch('');
                                        navigate({ search: null });
                                    }}
                                    className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                                >
                                    <X className="size-4" />
                                </button>
                            )}
                        </form>
                    </div>
                </div>

                <div className="flex items-baseline gap-2">
                    <h2 className="text-sm font-semibold">
                        {filters.type ? (noteTypes[filters.type] ?? filters.type) : 'All notes'}
                    </h2>
                    <span className="numeric text-xs text-muted-foreground">{notes.length}</span>
                    {filters.search && (
                        <span className="text-xs text-muted-foreground">for “{filters.search}”</span>
                    )}
                </div>

                {notes.length === 0 ? (
                    <p className="rounded-md border border-border py-12 text-center text-sm text-muted-foreground">
                        No notes found.
                    </p>
                ) : (
                    <div className="space-y-2">
                        {notes.map((note) => {
                            const Icon = TYPE_ICON[note.type] ?? FileText;
                            return (
                                <Link
                                    key={note.id}
                                    href={note.memberUrl}
                                    className="flex gap-3 rounded-md border border-border bg-card p-3 transition-colors hover:border-primary/30"
                                >
                                    <Icon className={cn('mt-0.5 size-4 shrink-0', TYPE_TONE[note.type])} />
                                    <div className="min-w-0 flex-1">
                                        <p className="whitespace-pre-line text-sm">{note.body}</p>
                                        <div className="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                                            <span className="font-medium text-foreground">{note.memberName}</span>
                                            <span>by {note.author}</span>
                                            <span className="ml-auto">{note.date}</span>
                                        </div>
                                    </div>
                                </Link>
                            );
                        })}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}

function FilterChip({
    active,
    onClick,
    children,
}: {
    active: boolean;
    onClick: () => void;
    children: React.ReactNode;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className={cn(
                'flex items-center gap-1.5 rounded-md border px-2.5 py-1 text-xs transition-colors',
                active ? 'border-primary bg-primary/10 text-foreground' : 'border-border text-muted-foreground',
            )}
        >
            {children}
        </button>
    );
}
