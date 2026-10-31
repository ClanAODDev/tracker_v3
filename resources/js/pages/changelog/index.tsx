import { Head, Link } from '@inertiajs/react';
import { Settings2 } from 'lucide-react';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout';

interface Note {
    id: number;
    title: string;
    version: string | null;
    publishedAt: string;
    excerpt: string;
    url: string;
}

interface Props {
    notes: Note[];
    pagination: { total: number; prevUrl: string | null; nextUrl: string | null };
    manageUrl: string | null;
}

export default function ReleaseNotes({ notes, pagination, manageUrl }: Props) {
    return (
        <AppLayout
            header={{
                eyebrow: 'AOD Tracker',
                title: 'Release notes',
                breadcrumbs: [{ label: 'Release notes' }],
                actions: manageUrl && (
                    <Button asChild variant="outline" size="sm">
                        <a href={manageUrl}>
                            <Settings2 /> Manage
                        </a>
                    </Button>
                ),
            }}
        >
            <Head title="Release notes" />
            <div className="space-y-4">
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

                <div className="space-y-3">
                    {notes.map((note) => (
                        <Link
                            key={note.id}
                            href={note.url}
                            className="block rounded-md border border-border bg-card p-5 transition-colors hover:border-primary/30"
                        >
                            <div className="flex flex-wrap items-baseline justify-between gap-2">
                                <h2 className="text-lg font-semibold">
                                    {note.title}
                                    {note.version && (
                                        <span className="ml-2 rounded border border-border px-1.5 py-0.5 align-middle text-xs font-normal text-muted-foreground">
                                            {note.version}
                                        </span>
                                    )}
                                </h2>
                                <time className="text-xs text-muted-foreground">{note.publishedAt}</time>
                            </div>
                            {note.excerpt && <p className="mt-2 text-sm text-muted-foreground">{note.excerpt}</p>}
                            <span className="mt-3 inline-block text-xs text-primary">Read the full notes</span>
                        </Link>
                    ))}
                </div>

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
