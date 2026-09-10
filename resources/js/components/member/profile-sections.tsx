import { Link } from '@inertiajs/react';
import {
    Bell,
    Copy,
    ExternalLink,
    Gamepad2,
    Layers,
    MessageSquareText,
    Mic,
    Sparkles,
    Trophy,
    UserPlus,
} from 'lucide-react';
import { type CSSProperties, type ReactNode, useState } from 'react';

import type { ActivityStats, RecruitingStats, TenureStats } from '@/components/member/profile-dialogs';
import { FillBar } from '@/components/motion';
import { SectionTitle as BaseSectionTitle } from '@/components/section';
import { StatCard } from '@/components/stat-card';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

export interface NoteSummary {
    total: number;
    positive: number;
    negative: number;
    misc: number;
    srLdr: number;
    latestType: string | null;
}

export interface ProfileStatsData {
    tenure: TenureStats;
    activity: ActivityStats;
    recruiting: RecruitingStats;
    notes: NoteSummary;
}

export interface AwardsData {
    total: number;
    byRarity: Record<string, number>;
    list: Array<{
        id: number;
        name: string;
        rarity: string;
        image: string | null;
        count: number;
        earnedOn: string;
        reason: string | null;
        url: string;
        tiers: Array<{ name: string; image: string | null }> | null;
    }>;
}

export interface ComparisonData {
    divisionName: string;
    tenurePercentile: number;
    tenureBetter: boolean;
    avgTenureYears: number;
    activityPercentile: number;
    activityBetter: boolean;
    avgVoiceDays: number;
    hasActivity: boolean;
}

export interface HandlesData {
    discord: string | null;
    discordUrl: string | null;
    groups: Array<{
        label: string;
        value: string;
        url: string | null;
        extras: Array<{ value: string; url: string | null }>;
    }>;
}

export interface DivisionsData {
    current: { name: string; slug: string; logo: string; since: string | null } | null;
    partTime: Array<{ name: string; slug: string; logo: string; since: string | null }>;
    past: Array<{ name: string; slug: string; logo: string; duration: string; visits: number }>;
}

const RARITY_ORDER = ['mythic', 'legendary', 'epic', 'rare', 'common'];

function rarityColor(rarity: string) {
    return `var(--rarity-${rarity}, var(--muted-foreground))`;
}

function SectionTitle({ children, action }: { children: ReactNode; action?: ReactNode }) {
    return (
        <BaseSectionTitle action={action} divider>
            {children}
        </BaseSectionTitle>
    );
}

interface ProfileStatsProps {
    stats: ProfileStatsData;
    canCreateNote: boolean;
    onTenure: () => void;
    onRecruits: () => void;
    onReminders: () => void;
    onNotes: () => void;
}

export function ProfileStats({ stats, canCreateNote, onTenure, onRecruits, onReminders, onNotes }: ProfileStatsProps) {
    return (
        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
            <StatCard
                icon={<Sparkles className="size-5" />}
                value={
                    <>
                        {stats.tenure.years}
                        <span className="text-sm text-muted-foreground">y</span>
                        {stats.tenure.months > 0 && (
                            <>
                                {' '}
                                {stats.tenure.months}
                                <span className="text-sm text-muted-foreground">m</span>
                            </>
                        )}
                    </>
                }
                label="Time in AOD"
                detail={stats.tenure.joinDate ? `Joined ${stats.tenure.joinDate}` : undefined}
                onClick={onTenure}
            />

            <StatCard
                icon={<Mic className="size-5" />}
                tone={
                    stats.activity.health === 'critical'
                        ? 'text-destructive'
                        : stats.activity.health === 'warning'
                          ? 'text-warning'
                          : 'text-success'
                }
                value={stats.activity.daysSinceVoice ?? '--'}
                unit={stats.activity.daysSinceVoice !== null ? 'd' : undefined}
                label="Since voice activity"
                detail={
                    <>
                        {stats.activity.daysSinceVoice !== null ? (
                            <>
                                <div className="h-1 w-full overflow-hidden rounded-full bg-muted">
                                    <FillBar
                                        pct={stats.activity.healthPct}
                                        className={cn(
                                            'rounded-full',
                                            stats.activity.health === 'critical'
                                                ? 'bg-destructive'
                                                : stats.activity.health === 'warning'
                                                  ? 'bg-warning'
                                                  : 'bg-success',
                                        )}
                                    />
                                </div>
                                <span>{stats.activity.divisionMax}d threshold</span>
                            </>
                        ) : (
                            <span>Never connected</span>
                        )}
                        {stats.activity.reminders.length > 0 && (
                            <button
                                type="button"
                                onClick={onReminders}
                                className="ml-2 inline-flex items-center gap-1 text-primary hover:underline"
                                title="View inactivity reminder history"
                            >
                                <Bell className="size-3" />
                                {stats.activity.reminders.length} reminder
                                {stats.activity.reminders.length === 1 ? '' : 's'}
                            </button>
                        )}
                    </>
                }
            />

            <StatCard
                icon={<UserPlus className="size-5" />}
                value={stats.recruiting.total}
                label="Recruits"
                detail={
                    stats.recruiting.total > 0 ? (
                        <>
                            <span className="text-success">{stats.recruiting.active} active</span>
                            {stats.recruiting.retentionRate !== null && (
                                <span> ({stats.recruiting.retentionRate}% retention)</span>
                            )}
                        </>
                    ) : (
                        'No recruits yet'
                    )
                }
                onClick={stats.recruiting.total > 0 ? onRecruits : undefined}
            />

            {canCreateNote && (
                <StatCard
                    icon={<MessageSquareText className="size-5" />}
                    value={stats.notes.total}
                    label="Notes"
                    onClick={onNotes}
                    detail={
                        stats.notes.total > 0 ? (
                            <span className="flex gap-2">
                                {stats.notes.positive > 0 && <span className="text-success">+{stats.notes.positive}</span>}
                                {stats.notes.negative > 0 && (
                                    <span className="text-destructive">−{stats.notes.negative}</span>
                                )}
                                {stats.notes.misc > 0 && <span>{stats.notes.misc} general</span>}
                                {stats.notes.srLdr > 0 && <span className="text-primary">{stats.notes.srLdr} sr</span>}
                            </span>
                        ) : (
                            'No notes recorded'
                        )
                    }
                />
            )}
        </div>
    );
}

function ComparisonBar({
    label,
    percentile,
    better,
    avgLabel,
    lowLabel,
    highLabel,
    na,
}: {
    label: string;
    percentile: number;
    better: boolean;
    avgLabel: string;
    lowLabel: string;
    highLabel: string;
    na?: boolean;
}) {
    const pos = Math.min(95, Math.max(5, percentile));
    const rankText = na
        ? 'N/A'
        : percentile >= 50
          ? `Top ${Math.max(1, 100 - percentile)}%`
          : `Bottom ${Math.max(1, percentile)}%`;

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between text-sm">
                <span className="font-medium">{label}</span>
                <span className={cn('text-xs', better ? 'text-success' : 'text-muted-foreground')}>{rankText}</span>
            </div>
            <div className="relative h-1.5 rounded-full bg-muted">
                <div className="absolute inset-y-0 left-1/2 w-px bg-border-strong" />
                {!na && (
                    <div
                        className={cn(
                            'absolute top-1/2 size-2.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-background',
                            better ? 'bg-success' : 'bg-primary',
                        )}
                        style={{ left: `${pos}%` }}
                    />
                )}
            </div>
            <div className="flex justify-between text-[11px] text-muted-foreground">
                <span>{lowLabel}</span>
                <span>{avgLabel}</span>
                <span>{highLabel}</span>
            </div>
        </div>
    );
}

export function DivisionComparison({ data }: { data: ComparisonData }) {
    return (
        <section>
            <SectionTitle>
                Division comparison{' '}
                <span className="ml-1 text-xs font-normal text-muted-foreground">vs {data.divisionName} avg</span>
            </SectionTitle>
            <div className="grid gap-6 rounded-md border border-border bg-card p-5 md:grid-cols-2">
                <ComparisonBar
                    label="Tenure"
                    percentile={data.tenurePercentile}
                    better={data.tenureBetter}
                    avgLabel={`Avg ${data.avgTenureYears}y`}
                    lowLabel="Newer"
                    highLabel="Longer"
                />
                <ComparisonBar
                    label="Voice activity"
                    percentile={data.activityPercentile}
                    better={data.activityBetter}
                    avgLabel={`Avg ${data.avgVoiceDays}d`}
                    lowLabel="Less active"
                    highLabel="More active"
                    na={!data.hasActivity}
                />
            </div>
        </section>
    );
}

export function Achievements({ awards }: { awards: AwardsData }) {
    return (
        <section>
            <SectionTitle>Achievements</SectionTitle>
            <div className="mb-4 flex flex-wrap gap-1.5">
                {RARITY_ORDER.filter((r) => (awards.byRarity[r] ?? 0) > 0).map((r) => (
                    <span
                        key={r}
                        className="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                        style={{
                            background: `color-mix(in srgb, ${rarityColor(r)} 15%, transparent)`,
                            color: rarityColor(r),
                        }}
                    >
                        {awards.byRarity[r]} {r}
                    </span>
                ))}
            </div>
            <div className="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                {awards.list.map((award) => (
                    <Link
                        key={award.id}
                        href={award.url}
                        title={award.reason ?? undefined}
                        className="group relative flex flex-col items-center rounded-md border border-border bg-card p-3 text-center transition-colors hover:border-[color:var(--rarity)]"
                        style={{ '--rarity': rarityColor(award.rarity) } as CSSProperties}
                    >
                        {award.count > 1 && (
                            <span className="absolute right-1.5 top-1.5 rounded-full bg-muted px-1.5 py-0.5 text-[10px] text-muted-foreground">
                                ×{award.count}
                            </span>
                        )}
                        <span
                            className="mb-2 h-0.5 w-8 rounded-full"
                            style={{ background: rarityColor(award.rarity) }}
                        />
                        <div className="flex size-16 items-center justify-center">
                            {award.tiers ? (
                                <span className="flex items-center" title={award.tiers.map((t) => t.name).join(', ')}>
                                    <Layers className="size-7 text-muted-foreground" />
                                </span>
                            ) : award.image ? (
                                <img
                                    src={award.image}
                                    alt=""
                                    loading="lazy"
                                    className="max-h-16 max-w-full object-contain"
                                />
                            ) : (
                                <Trophy className="size-8 text-muted-foreground" />
                            )}
                        </div>
                        <p className="mt-2 text-xs font-medium">{award.name}</p>
                        <span
                            className="mt-1.5 rounded-full px-2 py-0.5 text-[11px]"
                            style={{
                                background: `color-mix(in srgb, ${rarityColor(award.rarity)} 15%, transparent)`,
                                color: rarityColor(award.rarity),
                            }}
                        >
                            {award.earnedOn}
                        </span>
                    </Link>
                ))}
            </div>
        </section>
    );
}

function CopyButton({ text }: { text: string }) {
    const [copied, setCopied] = useState(false);
    return (
        <Button
            size="icon-xs"
            variant="ghost"
            title="Copy"
            onClick={() => {
                navigator.clipboard.writeText(text).then(() => {
                    setCopied(true);
                    setTimeout(() => setCopied(false), 1500);
                });
            }}
        >
            <Copy className={copied ? 'text-success' : undefined} />
        </Button>
    );
}

function HandleCard({
    label,
    value,
    url,
    extras,
    icon,
}: {
    label: string;
    value: string;
    url: string | null;
    extras?: Array<{ value: string; url: string | null }>;
    icon: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    return (
        <div className="flex items-center gap-3 rounded-md border border-border bg-card p-3">
            <div className="text-muted-foreground">{icon}</div>
            <div className="min-w-0 flex-1">
                <p className="text-xs text-muted-foreground">{label}</p>
                <p className="truncate text-sm">{value}</p>
            </div>
            <div className="flex items-center gap-1">
                <CopyButton text={value} />
                {url && (
                    <Button size="icon-xs" variant="ghost" asChild title="Open profile">
                        <a href={url} target="_blank" rel="noreferrer">
                            <ExternalLink />
                        </a>
                    </Button>
                )}
                {extras && extras.length > 0 && (
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button size="xs" variant="ghost">
                                +{extras.length}
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-w-sm">
                            <DialogHeader>
                                <DialogTitle>{label} handles</DialogTitle>
                                <DialogDescription className="sr-only">Additional {label} handles</DialogDescription>
                            </DialogHeader>
                            <ul className="space-y-2">
                                {[{ value, url }, ...extras].map((handle, i) => (
                                    <li key={i} className="flex items-center gap-2 text-sm">
                                        <span className="flex-1 truncate">{handle.value}</span>
                                        <CopyButton text={handle.value} />
                                        {handle.url && (
                                            <Button size="icon-xs" variant="ghost" asChild>
                                                <a href={handle.url} target="_blank" rel="noreferrer">
                                                    <ExternalLink />
                                                </a>
                                            </Button>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        </DialogContent>
                    </Dialog>
                )}
            </div>
        </div>
    );
}

export function HandlesSection({ handles }: { handles: HandlesData }) {
    return (
        <section>
            <SectionTitle>Handles</SectionTitle>
            <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {handles.discord && (
                    <HandleCard
                        label="Discord"
                        value={handles.discord}
                        url={handles.discordUrl}
                        icon={<MessageSquareText className="size-4" />}
                    />
                )}
                {handles.groups.map((group) => (
                    <HandleCard
                        key={group.label}
                        label={group.label}
                        value={group.value}
                        url={group.url}
                        extras={group.extras}
                        icon={<Gamepad2 className="size-4" />}
                    />
                ))}
            </div>
        </section>
    );
}

function DivisionCard({
    division,
    badge,
    primary,
}: {
    division: { name: string; slug: string; logo: string; since: string | null };
    badge: string;
    primary?: boolean;
}) {
    return (
        <Link
            href={`/divisions/${division.slug}`}
            className={cn(
                'flex items-center gap-3 rounded-md border bg-card p-4 transition-colors hover:border-primary/30',
                primary ? 'border-primary/40' : 'border-border',
            )}
        >
            <img src={division.logo} alt="" className="size-9 shrink-0" />
            <div className="min-w-0">
                <p className="text-sm font-medium">{division.name}</p>
                <p className="flex items-center gap-1.5 text-xs text-muted-foreground">
                    <span className={primary ? 'text-primary' : undefined}>{badge}</span>
                    {division.since && <span>· Since {division.since}</span>}
                </p>
            </div>
        </Link>
    );
}

export function DivisionsSection({ divisions }: { divisions: DivisionsData }) {
    return (
        <section>
            <SectionTitle>Divisions</SectionTitle>
            <div className="flex flex-wrap gap-3">
                {divisions.current && <DivisionCard division={divisions.current} badge="Primary" primary />}
                {divisions.partTime.map((d) => (
                    <DivisionCard key={d.slug} division={d} badge="Part-Time" />
                ))}
            </div>
            {divisions.past.length > 0 && (
                <>
                    <p className="mt-4 mb-2 text-xs font-medium text-muted-foreground">Past</p>
                    <div className="flex flex-wrap gap-2">
                        {divisions.past.map((d) => (
                            <Link
                                key={d.slug}
                                href={`/divisions/${d.slug}`}
                                className="flex items-center gap-2 rounded-md border border-border px-3 py-1.5 text-xs transition-colors hover:border-primary/30"
                            >
                                <img src={d.logo} alt="" className="size-4" />
                                <span>{d.name}</span>
                                <span className="text-muted-foreground">{d.duration}</span>
                                {d.visits > 1 && <span className="text-muted-foreground">×{d.visits}</span>}
                            </Link>
                        ))}
                    </div>
                </>
            )}
        </section>
    );
}
