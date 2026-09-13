import { Head, Link } from '@inertiajs/react';
import { useCallback, useRef } from 'react';

import { Button } from '@/components/ui/button';
import BlankLayout from '@/layouts/BlankLayout';

interface Props {
    status: number;
    title?: string;
    message?: string;
    path?: string | null;
}

const MESSAGES: Record<number, { slug: string; title: string; message: string; home?: boolean }> = {
    400: {
        slug: 'BAD_REQUEST',
        title: 'Bad request',
        message:
            "The request couldn't be processed. If you typed a URL manually, check it for errors — otherwise contact a clan administrator.",
    },
    403: {
        slug: 'ACCESS_DENIED',
        title: 'Access denied',
        message:
            'Your clearance level is insufficient for this area. Contact your division leadership or a clan administrator if you believe this is a mistake.',
    },
    404: {
        slug: 'SIGNAL_LOST',
        title: 'Signal lost',
        message:
            "The coordinates you entered don't match any known location. Check your navigation and try again — or fall back to base.",
    },
    405: {
        slug: 'METHOD_BLOCKED',
        title: 'Not permitted',
        message:
            "You're not allowed to do that. Contact your division leadership or a clan administrator if you believe this is a mistake.",
    },
    409: {
        slug: 'MEMBER_INACTIVE',
        title: 'Member inactive',
        message: 'The member you tried to view is no longer an active member of AOD.',
    },
    419: {
        slug: 'SESSION_EXPIRED',
        title: 'Page expired',
        message: 'Your session timed out. Refresh the page and try again.',
    },
    500: {
        slug: 'SYSTEM_FAILURE',
        title: 'System failure',
        message:
            'Something went sideways on our end. Our engineers have been alerted — report this to a clan administrator if it persists.',
    },
    503: {
        slug: 'MAINTENANCE',
        title: 'Offline',
        message:
            "The Tracker is currently unavailable due to maintenance or an update in progress. Stand by — we'll be back online shortly.",
        home: false,
    },
};

export default function ErrorPage({ status, title, message, path }: Props) {
    const fallback = MESSAGES[status] ?? MESSAGES[500];
    const info = { ...fallback, ...(title ? { title } : {}), ...(message ? { message } : {}) };

    const glitchRef = useRef<HTMLSpanElement>(null);

    const replay = useCallback(() => {
        const el = glitchRef.current;
        if (!el) return;
        el.classList.remove('tron-glitch-run');
        void el.offsetWidth;
        el.classList.add('tron-glitch-run');
    }, []);

    return (
        <BlankLayout>
            <Head title={`${status} — ${info.title}`} />
            <div
                onMouseEnter={replay}
                className="tron-corners mx-auto mt-16 max-w-md rounded-md border border-border bg-card p-10 text-center"
            >
                <div>
                    <span className="tron-eyebrow">
                        ERR · {status} · {info.slug}
                    </span>
                </div>

                <div className="mt-4">
                    <span
                        ref={glitchRef}
                        aria-hidden
                        data-text={String(status)}
                        className="tron-glitch tron-glitch-run numeric text-6xl font-bold tracking-tight text-primary"
                    >
                        {status}
                    </span>
                </div>

                <span className="mx-auto my-4 block h-px w-16 bg-gradient-to-r from-transparent via-primary to-transparent" />

                <h1 className="text-lg font-semibold">{info.title}</h1>
                <p className="mt-2 text-sm text-muted-foreground">{info.message}</p>

                {path && (
                    <p className="mt-4 truncate border-t border-border pt-4 font-mono text-xs text-muted-foreground">
                        <span className="text-primary">&gt;</span> {path}{' '}
                        <span className="text-primary">✗</span>
                        <span className="tron-caret ml-1 align-middle" />
                    </p>
                )}

                {info.home !== false && (
                    <Button size="sm" className="mt-6" asChild>
                        <Link href="/home">Return to base</Link>
                    </Button>
                )}
            </div>
        </BlankLayout>
    );
}
