import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';

export function TileLink({
    href,
    onClick,
    tint,
    hover = true,
    children,
}: {
    href?: string;
    onClick?: () => void;
    tint?: string;
    hover?: boolean;
    children: ReactNode;
}) {
    const className = cn(
        'flex items-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors',
        hover && 'hover:border-primary/40',
        tint,
    );
    if (onClick) {
        return (
            <button type="button" onClick={onClick} className={className}>
                {children}
            </button>
        );
    }
    return (
        <a href={href} className={className}>
            {children}
        </a>
    );
}
