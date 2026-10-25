import { Head, Link } from '@inertiajs/react';

import { Prose } from '@/components/prose';
import AppLayout from '@/layouts/AppLayout';

interface Note {
    id: number;
    title: string;
    version: string | null;
    publishedAt: string;
    html: string;
}

interface Props {
    notes: Note[];
    pagination: { total: number; prevUrl: string | null; nextUrl: string | null };
}

export default function ReleaseNotes({ notes, pagination }: Props) {
    return (
        <AppLayout
            header={{
                eyebrow: 'AOD Tracker',
                title: 'Release notes',
                breadcrumbs: [{ label: 'Release notes' }],
            }}
        >
            <Head title="Release notes" />
            <div className="max-w-3xl space-y-6">
                <p className="text-sm text-muted-foreground">
                    What has changed in Tracker. Minor refactoring and optimization are not recorded — see the{' '}
                    <a
                        href="https://github.com/ClanAODDev/tracker_v3/commits/main/"
                        target="_blank"
                        rel="noreferrer"
                        className="text-primary hover:underline"
                    >
                        commit history
                    </a>{' '}
                    for everything.
                </p>

                {notes.length === 0 && <p className="text-sm text-muted-foreground">No release notes yet.</p>}

                {notes.map((note) => (
                    <article key={note.id} id={`note-${note.id}`} className="space-y-3 rounded-md border border-border bg-card p-5">
                        <header className="flex flex-wrap items-baseline justify-between gap-2">
                            <h2 className="text-lg font-semibold">
                                {note.title}
                                {note.version && (
                                    <span className="ml-2 rounded border border-border px-1.5 py-0.5 align-middle text-xs font-normal text-muted-foreground">
                                        {note.version}
                                    </span>
                                )}
                            </h2>
                            <time className="text-xs text-muted-foreground">{note.publishedAt}</time>
                        </header>
                        <Prose html={note.html} />
                    </article>
                ))}

                {(pagination.prevUrl || pagination.nextUrl) && (
                    <div className="flex items-center justify-between text-xs text-muted-foreground">
                        <span>{pagination.total} release notes</span>
                        <div className="flex items-center gap-2">
                            {pagination.prevUrl && (
                                <Link href={pagination.prevUrl} className="rounded-md border border-border px-3 py-1.5 hover:border-primary/30 hover:text-foreground">
                                    Newer
                                </Link>
                            )}
                            {pagination.nextUrl && (
                                <Link href={pagination.nextUrl} className="rounded-md border border-border px-3 py-1.5 hover:border-primary/30 hover:text-foreground">
                                    Older
                                </Link>
                            )}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
