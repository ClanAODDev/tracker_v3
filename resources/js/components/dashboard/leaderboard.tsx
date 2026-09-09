import { Link } from '@inertiajs/react';
import { ChevronDown, ChevronUp, TrendingUp } from 'lucide-react';
import { useState } from 'react';

import { Sparkline } from '@/components/charts';
import { cn } from '@/lib/utils';

export interface LeaderEntry {
    id: number;
    name: string;
    slug: string;
    logo: string | null;
    value: number;
    formatted: string | number;
    trend: number[];
    rank_change?: number;
}

interface LeaderboardProps {
    userDivisionId: number | null;
    recruits: LeaderEntry[];
    voice: LeaderEntry[];
    growth: LeaderEntry[];
}

const TABS = [
    { key: 'recruits', label: 'Recruits', period: 'Monthly', footer: 'New members this month' },
    { key: 'voice', label: 'Voice %', period: 'Weekly', footer: 'Voice active / total members' },
    { key: 'growth', label: 'Growth', period: 'Weekly', footer: 'Member change since last census' },
] as const;

function Movement({ change }: { change?: number }) {
    if (!change) return null;
    const up = change > 0;
    return (
        <span className={cn('flex items-center gap-0.5 text-[11px]', up ? 'text-success' : 'text-destructive')}>
            {up ? <ChevronUp className="size-3" /> : <ChevronDown className="size-3" />}
            {Math.abs(change)}
        </span>
    );
}

function Row({ entry, rank, highlight, signed }: { entry: LeaderEntry; rank: number; highlight: boolean; signed?: boolean }) {
    const numeric = typeof entry.value === 'number' ? entry.value : 0;
    const valueClass = signed
        ? numeric > 0
            ? 'text-success'
            : numeric < 0
              ? 'text-destructive'
              : 'text-muted-foreground'
        : 'text-foreground';

    return (
        <Link
            href={`/divisions/${entry.slug}`}
            className={cn(
                'flex items-center gap-3 rounded-md px-2 py-1.5 text-sm transition-colors hover:bg-accent',
                highlight && 'bg-primary/10',
            )}
        >
            <span
                className={cn(
                    'numeric w-5 shrink-0 text-center text-xs',
                    rank <= 3 ? 'font-semibold text-primary' : 'text-muted-foreground',
                )}
            >
                {rank}
            </span>
            {entry.logo && <img src={entry.logo} alt="" className="size-5 shrink-0 rounded" />}
            <span className="flex min-w-0 flex-1 items-center gap-1.5">
                <span className="truncate">{entry.name}</span>
                <Movement change={entry.rank_change} />
            </span>
            {entry.trend.length > 1 && <Sparkline data={entry.trend} tone="auto" width={64} height={20} />}
            <span className={cn('numeric shrink-0 text-right text-xs', valueClass)}>{entry.formatted}</span>
        </Link>
    );
}

export function Leaderboard({ userDivisionId, recruits, voice, growth }: LeaderboardProps) {
    const data = { recruits, voice, growth };
    const [tab, setTab] = useState<(typeof TABS)[number]['key']>('recruits');

    return (
        <div>
            <div className="mb-3 flex items-center gap-2">
                <TrendingUp className="size-4 text-primary" />
                <h2 className="text-sm font-semibold">Leaderboard</h2>
            </div>

            {/* Desktop: all three */}
            <div className="hidden gap-4 lg:grid lg:grid-cols-3">
                {TABS.map(({ key, label, period, footer }) => (
                    <div key={key} className="rounded-md border border-border bg-card">
                        <div className="flex items-center justify-between border-b border-border px-3 py-2">
                            <span className="text-xs font-semibold uppercase tracking-wide">{label}</span>
                            <span className="text-[11px] text-muted-foreground">{period}</span>
                        </div>
                        <div className="space-y-0.5 p-2">
                            {data[key].map((entry, i) => (
                                <Row
                                    key={entry.id}
                                    entry={entry}
                                    rank={i + 1}
                                    highlight={entry.id === userDivisionId}
                                    signed={key === 'growth'}
                                />
                            ))}
                        </div>
                        <div className="border-t border-border px-3 py-2 text-[11px] text-muted-foreground">{footer}</div>
                    </div>
                ))}
            </div>

            {/* Mobile: tabbed */}
            <div className="rounded-md border border-border bg-card lg:hidden">
                <div className="flex border-b border-border">
                    {TABS.map(({ key, label }) => (
                        <button
                            key={key}
                            onClick={() => setTab(key)}
                            className={cn(
                                'flex-1 px-3 py-2 text-xs font-medium transition-colors',
                                tab === key
                                    ? 'border-b-2 border-primary text-foreground'
                                    : 'text-muted-foreground',
                            )}
                        >
                            {label}
                        </button>
                    ))}
                </div>
                <div className="space-y-0.5 p-2">
                    {data[tab].map((entry, i) => (
                        <Row
                            key={entry.id}
                            entry={entry}
                            rank={i + 1}
                            highlight={entry.id === userDivisionId}
                            signed={tab === 'growth'}
                        />
                    ))}
                </div>
            </div>
        </div>
    );
}
