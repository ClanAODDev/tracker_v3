import { Menu, Search, X } from 'lucide-react';
import { useEffect, useState } from 'react';

import { MemberSearch } from '@/components/member-search';
import { SettingsSheet } from '@/components/settings/settings-sheet';
import { TrackerHomeLink } from '@/components/tracker-logo';
import { UserMenu } from '@/components/user-menu';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export function AppTopbar({ onOpenNav, navSide = 'left' }: { onOpenNav: () => void; navSide?: 'left' | 'right' }) {
    const [settingsOpen, setSettingsOpen] = useState(false);
    const [mobileSearchOpen, setMobileSearchOpen] = useState(false);

    useEffect(() => {
        if (!mobileSearchOpen) return;
        const mq = window.matchMedia('(min-width: 40rem)');
        const onChange = () => mq.matches && setMobileSearchOpen(false);
        mq.addEventListener('change', onChange);
        return () => mq.removeEventListener('change', onChange);
    }, [mobileSearchOpen]);

    const hamburger = (
        <Button variant="ghost" size="icon" className="lg:hidden" onClick={onOpenNav} aria-label="Open navigation">
            <Menu className="size-5" />
        </Button>
    );

    return (
        <header className="sticky top-0 z-50 flex h-14 items-center gap-3 border-b border-border bg-background/80 px-4 backdrop-blur">
            {mobileSearchOpen ? (
                <div className="flex w-full animate-in items-center gap-2 fade-in slide-in-from-right-4 duration-200 sm:hidden">
                    <MemberSearch mobile className="flex-1" onClose={() => setMobileSearchOpen(false)} />
                    <Button
                        variant="ghost"
                        size="icon"
                        onClick={() => setMobileSearchOpen(false)}
                        aria-label="Close search"
                    >
                        <X className="size-5" />
                    </Button>
                </div>
            ) : (
                <>
                    {navSide === 'left' && hamburger}

                    <TrackerHomeLink className={cn('lg:hidden', navSide === 'right' && 'ml-1')} />

                    <div className="ml-auto flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="icon"
                            className="sm:hidden"
                            onClick={() => setMobileSearchOpen(true)}
                            aria-label="Search members"
                        >
                            <Search className="size-5" />
                        </Button>
                        <MemberSearch className="hidden sm:block" />
                        <UserMenu onOpenSettings={() => setSettingsOpen(true)} />
                        {navSide === 'right' && hamburger}
                    </div>
                </>
            )}

            <SettingsSheet open={settingsOpen} onOpenChange={setSettingsOpen} />
        </header>
    );
}
