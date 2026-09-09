import { ArrowDown, ArrowUp } from 'lucide-react';

import { CountUp } from '@/components/count-up';
import { cn } from '@/lib/utils';

type Tone = 'default' | 'success' | 'warning' | 'danger' | 'info' | 'primary' | 'muted';

const TONE_CLASS: Record<Tone, string> = {
    default: 'border-border',
    success: 'border-success/30',
    warning: 'border-warning/30',
    danger: 'border-destructive/30',
    info: 'border-chart-2/30',
    primary: 'border-primary/30',
    muted: 'border-border',
};

const TONE_TEXT: Record<Tone, string> = {
    default: 'text-foreground',
    success: 'text-success',
    warning: 'text-warning',
    danger: 'text-destructive',
    info: 'text-chart-2',
    primary: 'text-primary',
    muted: 'text-muted-foreground',
};

export interface Stat {
    value: React.ReactNode;
    label: string;
    unit?: string;
    tone?: Tone;
    change?: { value: number; suffix?: string } | null;
    note?: string;
}

export function StatTiles({ stats }: { stats: Stat[] }) {
    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            {stats.map((stat, i) => (
                <div key={i} className={cn('rounded-md border bg-card p-4', TONE_CLASS[stat.tone ?? 'default'])}>
                    <div className="flex items-baseline gap-1">
                        <span className={cn('numeric text-2xl font-semibold', TONE_TEXT[stat.tone ?? 'default'])}>
                            {typeof stat.value === 'number' ? <CountUp value={stat.value} /> : stat.value}
                        </span>
                        {stat.unit && <span className="text-sm text-muted-foreground">{stat.unit}</span>}
                    </div>
                    <p className="mt-0.5 text-xs text-muted-foreground">{stat.label}</p>
                    {stat.change != null && stat.change.value !== 0 && (
                        <p
                            className={cn(
                                'mt-1 flex items-center gap-1 text-xs',
                                stat.change.value > 0 ? 'text-success' : 'text-destructive',
                            )}
                        >
                            {stat.change.value > 0 ? (
                                <ArrowUp className="size-3" />
                            ) : (
                                <ArrowDown className="size-3" />
                            )}
                            {Math.abs(stat.change.value)}
                            {stat.change.suffix}
                        </p>
                    )}
                    {stat.note && <p className="mt-1 text-xs text-muted-foreground">{stat.note}</p>}
                </div>
            ))}
        </div>
    );
}
