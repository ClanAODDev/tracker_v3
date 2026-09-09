import type { PropsWithChildren } from 'react';

import { FlashToaster } from '@/components/flash-toaster';
import { TrackerLogo } from '@/components/tracker-logo';

interface BlankLayoutProps {
    title?: string;
}

export default function BlankLayout({ title, children }: PropsWithChildren<BlankLayoutProps>) {
    return (
        <div className="tron-grid min-h-screen bg-background text-foreground">
            <div className="mx-auto max-w-5xl px-6 py-12">
                <TrackerLogo className="mb-10" />
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
