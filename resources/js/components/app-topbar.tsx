import { Menu } from 'lucide-react';
import { useState } from 'react';

import { MemberSearch } from '@/components/member-search';
import { SettingsSheet } from '@/components/settings/settings-sheet';
import { TrackerHomeLink } from '@/components/tracker-logo';
import { UserMenu } from '@/components/user-menu';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export function AppTopbar({ onOpenNav, navSide = 'left' }: { onOpenNav: () => void; navSide?: 'left' | 'right' }) {
    const [settingsOpen, setSettingsOpen] = useState(false);

    const hamburger = (
        <Button variant="ghost" size="icon" className="lg:hidden" onClick={onOpenNav} aria-label="Open navigation">
            <Menu className="size-5" />
        </Button>
    );

    return (
        <header className="sticky top-0 z-50 flex h-14 items-center gap-3 border-b border-border bg-background/80 px-4 backdrop-blur">
            {navSide === 'left' && hamburger}

            <TrackerHomeLink className={cn('lg:hidden', navSide === 'right' && 'ml-1')} />

            <div className="ml-auto flex items-center gap-2">
                <MemberSearch className="hidden sm:block" />
                <UserMenu onOpenSettings={() => setSettingsOpen(true)} />
                {navSide === 'right' && hamburger}
            </div>

            <SettingsSheet open={settingsOpen} onOpenChange={setSettingsOpen} />
        </header>
    );
}
