import { Head, Link } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import BlankLayout from '@/layouts/BlankLayout';

interface Props {
    status: number;
    title?: string;
    message?: string;
}

const MESSAGES: Record<number, { title: string; message: string; home?: boolean }> = {
    400: {
        title: 'Bad request',
        message:
            "The request couldn't be processed. If you typed a URL manually, check it for errors — otherwise contact a clan administrator.",
    },
    403: {
        title: 'Access denied',
        message:
            'Your clearance level is insufficient for this area. Contact your division leadership or a clan administrator if you believe this is a mistake.',
    },
    404: {
        title: 'Signal lost',
        message:
            "The coordinates you entered don't match any known location. Check your navigation and try again — or fall back to base.",
    },
    405: {
        title: 'Not permitted',
        message:
            "You're not allowed to do that. Contact your division leadership or a clan administrator if you believe this is a mistake.",
    },
    409: {
        title: 'Member inactive',
        message: 'The member you tried to view is no longer an active member of AOD.',
    },
    419: {
        title: 'Page expired',
        message: 'Your session timed out. Refresh the page and try again.',
    },
    500: {
        title: 'System failure',
        message:
            'Something went sideways on our end. Our engineers have been alerted — report this to a clan administrator if it persists.',
    },
    503: {
        title: 'Offline',
        message:
            "The Tracker is currently unavailable due to maintenance or an update in progress. Stand by — we'll be back online shortly.",
        home: false,
    },
};

export default function ErrorPage({ status, title, message }: Props) {
    const fallback = MESSAGES[status] ?? MESSAGES[500];
    const info = { ...fallback, ...(title ? { title } : {}), ...(message ? { message } : {}) };

    return (
        <BlankLayout>
            <Head title={`${status} — ${info.title}`} />
            <div className="tron-corners mx-auto mt-16 max-w-md rounded-md border border-border bg-card p-10 text-center">
                <p className="numeric text-6xl font-bold tracking-tight text-primary">{status}</p>
                <span className="mx-auto my-4 block h-px w-16 bg-gradient-to-r from-transparent via-primary to-transparent" />
                <h1 className="text-lg font-semibold">{info.title}</h1>
                <p className="mt-2 text-sm text-muted-foreground">{info.message}</p>
                {info.home !== false && (
                    <Button size="sm" className="mt-6" asChild>
                        <Link href="/home">Return to base</Link>
                    </Button>
                )}
            </div>
        </BlankLayout>
    );
}
