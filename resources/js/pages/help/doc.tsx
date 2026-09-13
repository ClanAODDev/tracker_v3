import { Head } from '@inertiajs/react';

import { Prose } from '@/components/prose';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    title: string;
    eyebrow: string;
    body: string;
}

export default function HelpDoc({ title, eyebrow, body }: Props) {
    return (
        <AppLayout
            header={{
                eyebrow,
                title,
                breadcrumbs: [{ label: 'Documentation', href: '/help/docs' }, { label: title }],
            }}
        >
            <Head title={title} />
            <div className="tron-corners max-w-3xl rounded-md border border-border bg-card p-6">
                <Prose html={body} />
            </div>
        </AppLayout>
    );
}
