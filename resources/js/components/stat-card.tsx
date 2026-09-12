import type { ReactNode } from 'react';

import { Sparkline } from '@/components/charts';
import { cn } from '@/lib/utils';

interface StatCardProps {
    icon: ReactNode;
    value: ReactNode;
    label: string;
    unit?: string;
    sub?: ReactNode;
    detail?: ReactNode;
    delta?: ReactNode;
    trend?: number[];
    tone?: string;
    align?: 'start' | 'center';
    onClick?: () => void;
}

export function StatCard({
    icon,
    value,
    label,
    unit,
    sub,
    detail,
    delta,
    trend,
    tone,
    align = 'start',
    onClick,
}: StatCardProps) {
    const Comp = onClick ? 'button' : 'div';
    return (
        <Comp
            {...(onClick ? { type: 'button' as const, onClick } : {})}
            className={cn(
                'flex w-full gap-3 rounded-md border border-border bg-card p-4 text-left',
                align === 'center' ? 'items-center' : 'items-start',
                onClick && 'transition-colors hover:border-primary/40 hover:bg-primary/5',
            )}
        >
            <div className={cn('text-muted-foreground', align === 'start' && 'mt-0.5', tone)}>{icon}</div>
            <div className="min-w-0 flex-1">
                <div className="flex items-baseline gap-1.5">
                    <span className="numeric text-2xl font-semibold">
                        {value}
                        {unit && <span className="ml-0.5 text-sm text-muted-foreground">{unit}</span>}
                    </span>
                    {delta}
                </div>
                <p className="text-xs text-muted-foreground">
                    {label}
                    {sub != null && <> {sub}</>}
                </p>
                {detail && <div className="mt-1 text-xs text-muted-foreground">{detail}</div>}
            </div>
            {trend && trend.length > 1 && (
                <div className="hidden sm:block">
                    <Sparkline data={trend} tone="auto" width={56} height={24} />
                </div>
            )}
        </Comp>
    );
}
