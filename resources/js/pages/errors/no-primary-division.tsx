import { Head } from '@inertiajs/react';
import { ExternalLink, LogOut, MessageSquare, Newspaper } from 'lucide-react';

import { Button } from '@/components/ui/button';
import BlankLayout from '@/layouts/BlankLayout';

interface Props {
    impersonating: boolean;
}

export default function NoPrimaryDivision({ impersonating }: Props) {
    return (
        <BlankLayout>
            <Head title="No primary division" />
            <div className="tron-corners mx-auto mt-12 max-w-md rounded-md border border-border bg-card p-8">
                <div className="text-center">
                    <img src="/images/logo_v2.svg" alt="AOD" className="mx-auto size-12" />
                    <h1 className="mt-4 text-lg font-semibold">No primary division</h1>
                    <p className="text-sm text-muted-foreground">
                        You are no longer associated with a primary division.
                    </p>
                </div>

                <p className="mt-6 text-sm">
                    <strong>Returning to AOD?</strong> If you're a former member looking to rejoin, reach out to us:
                </p>
                <div className="mt-3 space-y-2">
                    <a
                        href="https://discord.gg/clanaod"
                        target="_blank"
                        rel="noreferrer"
                        className="flex items-center gap-3 rounded-md border border-border p-3 text-sm transition-colors hover:border-primary/30"
                    >
                        <MessageSquare className="size-4 text-info" />
                        <span className="flex-1">
                            <strong className="block">Join Discord</strong>
                            <span className="text-xs text-muted-foreground">Get with one of our recruiters</span>
                        </span>
                        <ExternalLink className="size-3.5 text-muted-foreground" />
                    </a>
                    <a
                        href="https://clanaod.net/forums"
                        target="_blank"
                        rel="noreferrer"
                        className="flex items-center gap-3 rounded-md border border-border p-3 text-sm transition-colors hover:border-primary/30"
                    >
                        <Newspaper className="size-4 text-primary" />
                        <span className="flex-1">
                            <strong className="block">Visit forums</strong>
                            <span className="text-xs text-muted-foreground">Browse division discussions</span>
                        </span>
                        <ExternalLink className="size-3.5 text-muted-foreground" />
                    </a>
                </div>

                <p className="mt-4 text-xs text-muted-foreground">
                    If you believe this is an error, speak with your intended division leadership.
                </p>
                {impersonating && (
                    <p className="mt-2 text-xs text-warning">
                        You appear to be impersonating. Try refreshing the page.
                    </p>
                )}

                <Button variant="outline" size="sm" className="mt-6 w-full" asChild>
                    <a href="/logout">
                        <LogOut /> Log out
                    </a>
                </Button>
            </div>
        </BlankLayout>
    );
}
