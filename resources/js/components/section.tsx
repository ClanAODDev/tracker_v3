import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';

export function SectionTitle({
    children,
    action,
    divider = false,
}: {
    children: ReactNode;
    action?: ReactNode;
    divider?: boolean;
}) {
    return (
        <div
            className={cn(
                'mb-3 flex items-center justify-between gap-3',
                divider && 'border-b border-border pb-2',
            )}
        >
            <h2 className="text-sm font-semibold">{children}</h2>
            {action}
        </div>
    );
}
