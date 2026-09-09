import { Link } from '@inertiajs/react';
import { ChevronRight } from 'lucide-react';
import { useState } from 'react';

import { NavIcon } from '@/components/nav-icon';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { isNavItemActive } from '@/lib/nav';
import { cn } from '@/lib/utils';
import type { NavItem } from '@/types';

function LeafLink({ item, currentUrl, nested }: { item: NavItem; currentUrl: string; nested?: boolean }) {
    const active = isNavItemActive(item, currentUrl);
    const className = cn(
        'group relative flex items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
        nested && 'py-1.5 pl-11 text-[13px]',
        active
            ? 'bg-primary/10 text-foreground before:absolute before:inset-y-1 before:left-0 before:w-0.5 before:rounded-full before:bg-primary before:shadow-[0_0_8px_var(--primary-glow)]'
            : 'text-muted-foreground hover:bg-accent hover:text-foreground',
    );

    const content = (
        <>
            {!nested && <NavIcon name={item.icon} className="size-4 shrink-0" />}
            <span className="truncate">{item.label}</span>
        </>
    );

    if (item.external) {
        return (
            <a href={item.href} className={className}>
                {content}
            </a>
        );
    }

    return (
        <Link href={item.href ?? '#'} prefetch className={className}>
            {content}
        </Link>
    );
}

function NavGroup({ item, currentUrl }: { item: NavItem; currentUrl: string }) {
    const [open, setOpen] = useState(() => isNavItemActive(item, currentUrl));

    return (
        <Collapsible open={open} onOpenChange={setOpen}>
            <CollapsibleTrigger
                className={cn(
                    'flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm transition-colors',
                    'text-muted-foreground hover:bg-accent hover:text-foreground',
                )}
            >
                <NavIcon name={item.icon} className="size-4 shrink-0" />
                <span className="flex-1 truncate text-left">{item.label}</span>
                <ChevronRight className={cn('size-3.5 transition-transform', open && 'rotate-90')} />
            </CollapsibleTrigger>
            <CollapsibleContent className="mt-0.5 space-y-0.5">
                {item.children?.map((child) => (
                    <LeafLink key={child.label} item={child} currentUrl={currentUrl} nested />
                ))}
            </CollapsibleContent>
        </Collapsible>
    );
}

export function NavSidebar({ items, currentUrl }: { items: NavItem[]; currentUrl: string }) {
    return (
        <nav className="flex flex-col gap-0.5 p-3">
            {items.map((item, index) => {
                if (item.type === 'heading') {
                    return (
                        <div
                            key={`${item.label}-${index}`}
                            className="px-3 pt-5 pb-1.5 text-[11px] font-semibold uppercase tracking-[0.15em] text-dim-foreground first:pt-1"
                        >
                            {item.label}
                        </div>
                    );
                }

                if (item.children?.length) {
                    return <NavGroup key={item.label} item={item} currentUrl={currentUrl} />;
                }

                return <LeafLink key={item.label} item={item} currentUrl={currentUrl} />;
            })}
        </nav>
    );
}
