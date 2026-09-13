import { Check, ChevronDown, ChevronUp } from 'lucide-react';
import type { ReactNode } from 'react';

import { cn } from '@/lib/utils';

export function StepDot({ n, complete }: { n: number; complete: boolean }) {
    return (
        <span
            className={cn(
                'ml-auto grid size-5 place-items-center rounded-full border text-[11px]',
                complete ? 'border-success bg-success/15 text-success' : 'border-border text-muted-foreground',
            )}
        >
            {complete ? <Check className="size-3" /> : n}
        </span>
    );
}

export function Section({
    icon,
    title,
    step,
    complete,
    collapsible,
    open,
    onToggle,
    children,
}: {
    icon: ReactNode;
    title: string;
    step: number;
    complete: boolean;
    collapsible?: boolean;
    open?: boolean;
    onToggle?: () => void;
    children: ReactNode;
}) {
    return (
        <section className="overflow-hidden rounded-md border border-border">
            <div
                className={cn(
                    'flex items-center gap-2 border-b border-border bg-card/40 px-4 py-2.5 text-sm font-semibold',
                    collapsible && 'cursor-pointer',
                )}
                onClick={collapsible ? onToggle : undefined}
            >
                {icon} {title}
                <StepDot n={step} complete={complete} />
                {collapsible && (open ? <ChevronUp className="size-4" /> : <ChevronDown className="size-4" />)}
            </div>
            {(!collapsible || open) && <div className="space-y-4 p-4">{children}</div>}
        </section>
    );
}
