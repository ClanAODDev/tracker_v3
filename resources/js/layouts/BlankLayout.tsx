import type { PropsWithChildren } from 'react';

import { FlashToaster } from '@/components/flash-toaster';
import { cn } from '@/lib/utils';

interface BlankLayoutProps {
    title?: string;
    /** Set false to omit the drifting grid background, e.g. when the page supplies its
     * own animated background (like the login page's constellation). Defaults to true. */
    grid?: boolean;
}

export default function BlankLayout({ title, grid = true, children }: PropsWithChildren<BlankLayoutProps>) {
    return (
        <div className={cn('min-h-screen bg-background text-foreground', grid && 'tron-grid')}>
            <div className="mx-auto max-w-5xl px-6 py-12">
                {title && (
                    <header className="mb-10 border-b border-border pb-6">
                        <h1 className="text-2xl font-semibold tracking-tight">{title}</h1>
                    </header>
                )}
                {children}
            </div>
            <FlashToaster />
        </div>
    );
}
