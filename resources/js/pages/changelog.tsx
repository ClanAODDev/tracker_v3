import { Head } from '@inertiajs/react';

import { Prose } from '@/components/prose';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    body: string;
}

export default function Changelog({ body }: Props) {
    return (
        <AppLayout
            header={{
                eyebrow: 'AOD Tracker',
                title: 'Changelog',
                breadcrumbs: [{ label: 'Changelog' }],
            }}
        >
            <Head title="Changelog" />
            <div className="max-w-3xl space-y-4">
                <p className="text-sm text-muted-foreground">
                    A historical record of interface and process changes. Minor refactoring and optimization are not
                    recorded — see the{' '}
                    <a
                        href="https://github.com/ClanAODDev/tracker_v3/commits/main/"
                        target="_blank"
                        rel="noreferrer"
                        className="text-primary hover:underline"
                    >
                        commit history
                    </a>{' '}
                    for a full changelog.
                </p>
                <Prose html={body} />
            </div>
        </AppLayout>
    );
}
