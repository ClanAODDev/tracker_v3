import { usePage } from '@inertiajs/react';
import { type PropsWithChildren, type ReactNode, useEffect, useState } from 'react';

import { AppTopbar } from '@/components/app-topbar';
import { FlashToaster } from '@/components/flash-toaster';
import { ImpersonationBanner } from '@/components/impersonation-banner';
import { NavSidebar } from '@/components/nav-sidebar';
import { PageHeader, type PageHeaderProps } from '@/components/page-header';
import { TrackerMark } from '@/components/tracker-logo';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Sheet, SheetContent, SheetTitle } from '@/components/ui/sheet';
import { cn } from '@/lib/utils';
import type { SharedProps } from '@/types';

interface AppLayoutProps {
    header?: PageHeaderProps;
    fullBleed?: boolean;
}

export default function AppLayout({ header, fullBleed, children }: PropsWithChildren<AppLayoutProps>) {
    const page = usePage<SharedProps>();
    const nav = page.props.nav;
    const currentUrl = page.url;
    const navSide = page.props.auth.user?.settings.mobileNavSide === 'right' ? 'right' : 'left';
    const navLeft = navSide === 'left';
    const reduceAnimations = page.props.auth.user?.settings.reduceAnimations ?? false;
    const [navOpen, setNavOpen] = useState(false);

    useEffect(() => {
        document.documentElement.dataset.reduceMotion = reduceAnimations ? 'true' : 'false';
    }, [reduceAnimations]);

    const sidebar = <NavSidebar items={nav ?? []} currentUrl={currentUrl} />;

    return (
        <div className="min-h-screen bg-background text-foreground">
            <div className={cn('lg:grid', navLeft ? 'lg:grid-cols-[15rem_1fr]' : 'lg:grid-cols-[1fr_15rem]')}>
                <aside
                    className={cn(
                        'fixed inset-y-0 z-40 hidden w-60 bg-card/30 lg:block',
                        navLeft ? 'left-0 border-r border-border' : 'right-0 border-l border-border',
                    )}
                >
                    <div className="flex h-14 items-center gap-2.5 border-b border-border px-5">
                        <TrackerMark className="size-5 drop-shadow-[0_0_6px_var(--primary-glow)]" />
                        <span className="font-mono text-sm font-semibold tracking-[0.3em]">TRACKER</span>
                    </div>
                    <ScrollArea className="h-[calc(100vh-3.5rem)]">{sidebar}</ScrollArea>
                </aside>

                <div className={navLeft ? 'lg:col-start-2' : 'lg:col-start-1 lg:row-start-1'}>
                    <AppTopbar onOpenNav={() => setNavOpen(true)} navSide={navSide} />
                    <ImpersonationBanner />
                    {header && <PageHeader {...header} />}
                    <div className="tron-grid min-h-[calc(100vh-3.5rem)]">
                        <main className={fullBleed ? '' : 'mx-auto w-full max-w-7xl px-6 py-8'}>{children}</main>
                    </div>
                </div>
            </div>

            <Sheet open={navOpen} onOpenChange={setNavOpen}>
                <SheetContent side={navSide} className="w-72 p-0">
                    <SheetTitle className="flex h-14 items-center gap-2.5 border-b border-border px-5 font-mono text-sm font-semibold tracking-[0.3em]">
                        <TrackerMark className="size-5" />
                        TRACKER
                    </SheetTitle>
                    <ScrollArea className="h-[calc(100vh-3.5rem)]" onClick={() => setNavOpen(false)}>
                        {sidebar}
                    </ScrollArea>
                </SheetContent>
            </Sheet>

            <FlashToaster />
        </div>
    );
}

export function renderWithAppLayout(page: ReactNode, props?: AppLayoutProps) {
    return <AppLayout {...props}>{page}</AppLayout>;
}
