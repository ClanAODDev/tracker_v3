import { Head, Link } from '@inertiajs/react';

import AppLayout from '@/layouts/AppLayout';

interface Props {
    divisions: Array<{ name: string; slug: string; logo: string; url: string }>;
}

export default function RecruitingIndex({ divisions }: Props) {
    return (
        <AppLayout
            header={{
                eyebrow: 'Recruiting',
                title: 'Add new recruit',
                breadcrumbs: [{ label: 'Recruiting' }],
            }}
        >
            <Head title="Add new recruit" />

            <div className="space-y-4">
                <p className="text-sm text-muted-foreground">
                    Select the division you're recruiting for. Each division's recruiting process is unique — coordinate
                    with them before processing a recruit for a division that isn't your own.
                </p>
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {divisions.map((division) => (
                        <Link
                            key={division.slug}
                            href={division.url}
                            className="flex items-center gap-3 rounded-md border border-border bg-card p-4 transition-colors hover:border-primary/30"
                        >
                            <img src={division.logo} alt="" className="size-9 shrink-0" />
                            <span className="font-medium">{division.name}</span>
                        </Link>
                    ))}
                </div>
            </div>
        </AppLayout>
    );
}
