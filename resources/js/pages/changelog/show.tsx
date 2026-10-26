import { Head, Link } from '@inertiajs/react';

import { Prose } from '@/components/prose';
import AppLayout from '@/layouts/AppLayout';

interface NoteLink {
    title: string;
    url: string;
}

interface Props {
    note: { title: string; version: string | null; publishedAt: string; html: string };
    newer: NoteLink | null;
    older: NoteLink | null;
    indexUrl: string;
}

export default function ReleaseNote({ note, newer, older, indexUrl }: Props) {
    return (
        <AppLayout
            header={{
                eyebrow: note.version ? `Release notes · ${note.version}` : 'Release notes',
                title: note.title,
                breadcrumbs: [{ label: 'Release notes', href: indexUrl }, { label: note.title }],
            }}
        >
            <Head title={note.title} />
            <article className="space-y-4">
                <time className="block text-xs text-muted-foreground">{note.publishedAt}</time>
                <Prose html={note.html} />
            </article>

            <nav className="mt-8 flex flex-wrap items-center justify-between gap-3 border-t border-border pt-4 text-sm">
                <div>
                    {newer && (
                        <Link href={newer.url} className="text-primary hover:underline">
                            ← {newer.title}
                        </Link>
                    )}
                </div>
                <Link href={indexUrl} className="text-muted-foreground hover:text-foreground">
                    All release notes
                </Link>
                <div>
                    {older && (
                        <Link href={older.url} className="text-primary hover:underline">
                            {older.title} →
                        </Link>
                    )}
                </div>
            </nav>
        </AppLayout>
    );
}
