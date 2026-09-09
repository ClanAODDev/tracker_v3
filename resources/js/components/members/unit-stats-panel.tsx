import { Users } from 'lucide-react';

import { cn } from '@/lib/utils';

import type { UnitStats } from './types';

const BUCKET_TONE = ['bg-success', 'bg-warning', 'bg-destructive', 'bg-muted-foreground/40'];
const BUCKET_TEXT = ['text-success', 'text-warning', 'text-destructive', 'text-muted-foreground'];

function Stat({ label, value, tone, title }: { label: string; value: string; tone?: string; title?: string }) {
    return (
        <div title={title}>
            <p className="text-[11px] uppercase tracking-wide text-muted-foreground">{label}</p>
            <p className={cn('numeric text-lg font-semibold', tone)}>{value}</p>
        </div>
    );
}

export function UnitStatsPanel({ stats }: { stats: UnitStats }) {
    const graph = stats.voiceActivity;
    const total = graph.values.reduce((a, b) => a + b, 0);

    return (
        <div className="space-y-4">
            <div className="rounded-md border border-border bg-card p-4 text-center">
                <Users className="mx-auto size-5 text-warning" />
                <p className="numeric mt-1 text-2xl font-semibold">{stats.totalCount}</p>
                <p className="text-xs text-muted-foreground">Members</p>
            </div>

            {(stats.onLeaveCount > 0 || stats.inactiveCount > 0) && (
                <div className="grid grid-cols-2 gap-3 rounded-md border border-border bg-card p-4">
                    {stats.onLeaveCount > 0 && (
                        <Stat
                            label="On leave"
                            value={String(stats.onLeaveCount)}
                            title="Members with an active leave of absence"
                        />
                    )}
                    {stats.inactiveCount > 0 && (
                        <Stat
                            label="Inactive"
                            value={String(stats.inactiveCount)}
                            tone="text-destructive"
                            title={`No Discord activity in ${stats.inactivityDays}+ days`}
                        />
                    )}
                </div>
            )}

            <div className="grid grid-cols-2 gap-3 rounded-md border border-border bg-card p-4">
                <Stat label="Avg tenure" value={`${stats.avgTenureYears}y`} title="Average time in AOD" />
                <Stat
                    label="Officer ratio"
                    value={`${stats.officerCount}/${stats.memberCount}`}
                    title="Officers (LCpl+) to members"
                />
            </div>

            {total > 0 && (
                <div className="rounded-md border border-border bg-card p-4">
                    <p className="mb-2 text-xs font-semibold">Discord activity</p>
                    <div className="flex h-2 overflow-hidden rounded-full">
                        {graph.values.map((value, i) =>
                            value > 0 ? (
                                <div
                                    key={i}
                                    className={BUCKET_TONE[i] ?? 'bg-muted'}
                                    style={{ width: `${(value / total) * 100}%` }}
                                    title={`${graph.labels[i]}: ${value}`}
                                />
                            ) : null,
                        )}
                    </div>
                    <ul className="mt-2 space-y-1">
                        {graph.labels.map((labelText, i) => (
                            <li key={labelText} className="flex items-center justify-between text-[11px]">
                                <span className="flex items-center gap-1.5 text-muted-foreground">
                                    <span className={cn('size-1.5 rounded-full', BUCKET_TONE[i] ?? 'bg-muted')} />
                                    {labelText}
                                </span>
                                <span className={cn('numeric', BUCKET_TEXT[i] ?? 'text-muted-foreground')}>
                                    {graph.values[i]}
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
