import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Check, Trophy, Users } from 'lucide-react';

import { RarityPill } from '@/components/awards/award-card';
import { StatTiles } from '@/components/reports/stat-tiles';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface Tier {
    id: number;
    name: string;
    description: string | null;
    rarity: string;
    recipientsCount: number;
    image: string | null;
    earned: boolean;
    isNext: boolean;
    earnedDate: string | null;
    pct: number;
}

interface TieredProps {
    group: { name: string; slug: string; description: string | null };
    stats: {
        totalRecipients: number;
        firstAwarded: string | null;
        earnedCount: number;
        totalTiers: number;
        progressPct: number;
    };
    tiers: Tier[];
}

export default function TieredAward({ group, stats, tiers }: TieredProps) {
    const complete = stats.earnedCount === stats.totalTiers && stats.totalTiers > 0;

    return (
        <AppLayout
            header={{
                eyebrow: 'Tiered Award',
                title: group.name,
                breadcrumbs: [
                    { label: 'Achievements', href: '/clan/awards' },
                    { label: group.name },
                ],
            }}
        >
            <Head title={group.name} />

            <div className="space-y-6">
                <StatTiles
                    stats={[
                        { value: stats.totalRecipients, label: 'Members with awards' },
                        { value: stats.firstAwarded ?? '—', label: 'First awarded', tone: 'info' },
                        { value: stats.totalTiers, label: 'Tiers' },
                        {
                            value: complete ? '✓' : `${stats.earnedCount}/${stats.totalTiers}`,
                            label: 'Your progress',
                            tone: complete ? 'success' : stats.earnedCount > 0 ? 'warning' : 'muted',
                        },
                    ]}
                />

                {stats.earnedCount > 0 && (
                    <div>
                        <div className="h-2 overflow-hidden rounded-full bg-muted">
                            <div className="h-full bg-primary" style={{ width: `${stats.progressPct}%` }} />
                        </div>
                        <p className="mt-1 text-center text-xs text-muted-foreground">
                            {complete ? (
                                <>
                                    <Trophy className="mr-1 inline size-3 text-warning" /> All tiers completed
                                </>
                            ) : (
                                `${stats.earnedCount} of ${stats.totalTiers} tiers earned`
                            )}
                        </p>
                    </div>
                )}

                {group.description && <p className="text-sm text-muted-foreground">{group.description}</p>}

                <ol className="space-y-2">
                    {tiers.map((tier) => (
                        <li key={tier.id}>
                            <Link
                                href={`/clan/awards/${tier.id}`}
                                className={cn(
                                    'flex items-center gap-4 rounded-md border p-4 transition-colors',
                                    tier.earned
                                        ? 'border-success/30 bg-success/5'
                                        : tier.isNext
                                          ? 'border-primary/40 bg-primary/5'
                                          : 'border-border opacity-70 hover:opacity-100',
                                )}
                            >
                                <div
                                    className="relative flex size-12 shrink-0 items-center justify-center rounded-md border"
                                    style={{ borderColor: `var(--rarity-${tier.rarity})` }}
                                >
                                    {tier.image ? (
                                        <img src={tier.image} alt="" className="max-h-10 max-w-full object-contain" />
                                    ) : (
                                        <Trophy className="size-5 text-muted-foreground" />
                                    )}
                                    {tier.earned && (
                                        <span className="absolute -right-1.5 -top-1.5 flex size-4 items-center justify-center rounded-full bg-success text-[var(--background)]">
                                            <Check className="size-2.5" />
                                        </span>
                                    )}
                                </div>

                                <div className="min-w-0 flex-1">
                                    <p className="flex items-center gap-2 text-sm font-medium">
                                        {tier.name}
                                        {tier.isNext && (
                                            <span className="rounded bg-primary px-1.5 py-0.5 text-[10px] text-primary-foreground">
                                                Next
                                            </span>
                                        )}
                                    </p>
                                    {tier.description && (
                                        <p className="mt-0.5 text-xs text-muted-foreground">{tier.description}</p>
                                    )}
                                    <div className="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                        <RarityPill rarity={tier.rarity} />
                                        <span className="flex items-center gap-1">
                                            <Users className="size-3" />
                                            {tier.recipientsCount} ({tier.pct}%)
                                        </span>
                                        {tier.earned && tier.earnedDate && (
                                            <span className="text-success">Earned {tier.earnedDate}</span>
                                        )}
                                    </div>
                                </div>

                                <ArrowRight className="size-4 shrink-0 text-muted-foreground" />
                            </Link>
                        </li>
                    ))}
                </ol>
            </div>
        </AppLayout>
    );
}
