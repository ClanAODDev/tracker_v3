import { Head, Link } from '@inertiajs/react';
import { Headset, History, Settings, Shield, Star, TriangleAlert, UserPlus, Users } from 'lucide-react';
import { type CSSProperties, type DragEvent, useEffect, useRef, useState } from 'react';

import { ThemedLineChart } from '@/components/charts';
import { CountUp } from '@/components/count-up';
import { ApplicationsModal } from '@/components/division/applications-modal';
import { OrganizeBanner, dropZoneProps, useOrganize, type OrganizeMember } from '@/components/division/organize';
import { RecentActivityModal, type RecentActivityGroup } from '@/components/division/recent-activity-modal';
import { DivisionToolbar, type DivisionTool } from '@/components/division/division-toolbar';
import { PendingActionIcon } from '@/components/dashboard/pending-action-icon';
import { FillBar, FlashOnChange } from '@/components/motion';
import { SectionTitle } from '@/components/section';
import { StatCard } from '@/components/stat-card';
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { toneSurface } from '@/lib/tone';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';
import type { MemberCard } from '@/types';

type Leader = Partial<MemberCard>;

interface Squad {
    id: number;
    name: string;
    memberCount: number;
    leader: Leader | null;
}

interface Platoon {
    id: number;
    name: string;
    description: string | null;
    logo: string | null;
    url: string;
    memberCount: number;
    voiceRate: number;
    leader: Leader | null;
    squads: Squad[];
}

interface DivisionShowProps {
    division: {
        name: string;
        slug: string;
        logo: string | null;
        platoonLabel: string;
        isShutdown: boolean;
        shutdownAt: string | null;
        shutdownPending: boolean;
        applicationRequired: boolean;
        applicationsUrl: string;
        canDeleteApplications: boolean;
        canRecruit: boolean;
        canCreatePlatoon: boolean;
        canManageUnassigned: boolean;
        editUrl: string;
        recruitUrl: string;
    };
    stats: {
        memberCount: number;
        voiceActiveCount: number;
        voiceRate: number;
        recruitsThisMonth: number;
        activityThresholdDays: number;
    };
    census: {
        labels: string[];
        population: number[];
        voiceActive: number[];
        previous: { count: number; date: string } | null;
    };
    leaders: MemberCard[];
    platoons: Platoon[];
    anniversaries: Array<{
        name: string;
        clanId: number;
        rankAbbr: string | null;
        years: number;
        hasTenureAward: boolean;
        trophy: { color: string; title: string } | null;
    }>;
    toolbar: DivisionTool[];
    pendingActions: Array<{ key: string; count: number; url: string; icon: string; label: string; style: string }>;
    recentActivityCount: number;
    recentActivity: RecentActivityGroup[];
    canViewAllActivity: boolean;
    allActivityUrl: string;
    organize: { canOrganize: boolean; members: OrganizeMember[] };
    pendingApplicationCount: number;
}

function voiceTone(rate: number) {
    return rate >= 30 ? 'text-success' : rate >= 15 ? 'text-warning' : 'text-destructive';
}

function voiceFill(rate: number) {
    return rate >= 30 ? 'bg-success/15' : rate >= 15 ? 'bg-warning/15' : 'bg-destructive/15';
}

function TileLink({
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
    children: React.ReactNode;
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

function LeaderAvatar({ leader, size = 'sm' }: { leader: Leader; size?: 'sm' | 'md' }) {
    const dim = size === 'md' ? 'size-9' : 'size-5';
    if (leader.avatarUrl) {
        return <img src={leader.avatarUrl} alt="" className={cn(dim, 'shrink-0 rounded-full')} />;
    }
    return (
        <span
            className={cn(dim === 'size-9' ? 'size-2.5' : 'size-2', 'shrink-0 rounded-full')}
            style={{ background: leader.rankColor ?? 'var(--muted-foreground)' }}
        />
    );
}

const VOICE_CORNER = ['var(--destructive)', 'var(--warning)', 'var(--success)'] as const;

function voiceCorner(rate: number) {
    return rate >= 30 ? VOICE_CORNER[2] : rate >= 15 ? VOICE_CORNER[1] : VOICE_CORNER[0];
}

function PlatoonCard({
    platoon,
    index,
    organizing,
    isHover,
    onDragOver,
    onDragLeave,
    onDrop,
}: {
    platoon: Platoon;
    index: number;
    organizing: boolean;
    isHover: boolean;
    onDragOver?: (e: DragEvent) => void;
    onDragLeave?: () => void;
    onDrop?: (e: DragEvent) => void;
}) {
    const ledSquads = platoon.squads.filter((s) => s.leader).length;
    const body = (
        <>
            <div className="flex items-start gap-3">
                {platoon.logo && <img src={platoon.logo} alt="" className="size-9 shrink-0 rounded" />}
                <div className="min-w-0 flex-1">
                    <p className="font-mono text-[13px] font-semibold uppercase leading-tight tracking-[0.08em]">
                        <span className="text-dim-foreground">P{index + 1}</span>
                        <span className="mx-1.5 text-border-strong">·</span>
                        {platoon.name}
                    </p>
                    <p className="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                        <span className="font-semibold text-primary">»</span>
                        {platoon.leader ? (
                            <>
                                <LeaderAvatar leader={platoon.leader} />
                                {platoon.leader.rankName}
                            </>
                        ) : (
                            'No leader assigned'
                        )}
                    </p>
                </div>
            </div>

            {platoon.squads.length > 0 && (
                <div className="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    {platoon.squads.map((squad) => (
                        <div
                            key={squad.id}
                            className={cn(
                                'rounded border p-2 text-xs',
                                squad.leader
                                    ? 'border-border/60'
                                    : 'tron-hatch border-dashed border-border/60 opacity-75',
                            )}
                            style={
                                squad.leader
                                    ? { borderLeftColor: squad.leader.rankColor, borderLeftWidth: 2 }
                                    : undefined
                            }
                        >
                            <div className="flex items-center justify-between">
                                <span className="font-medium">{squad.name}</span>
                                <span className="numeric text-muted-foreground">{squad.memberCount}</span>
                            </div>
                            {squad.leader ? (
                                <span className="text-muted-foreground">{squad.leader.rankName}</span>
                            ) : (
                                <span className="font-mono text-[11px] uppercase tracking-[0.1em] text-dim-foreground">
                                    TBA
                                </span>
                            )}
                        </div>
                    ))}
                </div>
            )}

            <div className="relative -mx-4 -mb-4 mt-3 overflow-hidden rounded-b-[5px] border-t border-border bg-black/15 px-4 py-2">
                <FillBar
                    pct={platoon.voiceRate}
                    className={cn('absolute inset-y-0 left-0 border-r', voiceFill(platoon.voiceRate))}
                    style={{ borderRightColor: voiceCorner(platoon.voiceRate) }}
                />
                <div className="relative flex items-center font-mono text-[11px] tracking-[0.04em]">
                    <span className="flex items-baseline gap-1.5 pr-3">
                        <span className="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Voice</span>
                        <span className={cn('font-semibold', voiceTone(platoon.voiceRate))}>
                            {platoon.voiceRate}%
                        </span>
                    </span>
                    <span className="flex items-baseline gap-1.5 border-l border-border-strong px-3">
                        <span className="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Members</span>
                        <FlashOnChange value={platoon.memberCount} className="font-semibold">
                            {platoon.memberCount}
                        </FlashOnChange>
                    </span>
                    <span className="flex items-baseline gap-1.5 border-l border-border-strong px-3">
                        <span className="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Squads</span>
                        <span className="font-semibold">
                            {ledSquads}/{platoon.squads.length}
                        </span>
                    </span>
                </div>
            </div>
        </>
    );

    if (organizing) {
        return (
            <div
                onDragOver={onDragOver}
                onDragLeave={onDragLeave}
                onDrop={onDrop}
                className={cn(
                    'overflow-hidden rounded-md border border-dashed p-4 transition-colors',
                    isHover ? 'border-primary bg-primary/10' : 'border-primary/40',
                )}
            >
                {body}
            </div>
        );
    }

    return (
        <Link
            href={platoon.url}
            className="tron-corners rounded-md border border-border bg-card p-4 transition-[border-color,transform] hover:-translate-y-0.5 hover:border-primary/30"
            style={{ '--corner-color': voiceCorner(platoon.voiceRate) } as CSSProperties}
        >
            {body}
        </Link>
    );
}

export default function DivisionShow({
    division: d,
    stats,
    census,
    leaders,
    platoons,
    anniversaries,
    toolbar,
    pendingActions,
    recentActivityCount,
    recentActivity,
    canViewAllActivity,
    allActivityUrl,
    organize: organizeProps,
}: DivisionShowProps) {
    const populationTrend = census.population.slice(-8);
    const voiceRateTrend = census.voiceActive
        .slice(-8)
        .map((v, i) => {
            const pop = populationTrend[i] ?? 1;
            return pop > 0 ? Math.round((v / pop) * 100) : 0;
        });
    const prev = census.previous;
    const memberDelta =
        prev && prev.count && stats.memberCount
            ? Math.round((1 - prev.count / stats.memberCount) * 1000) / 10
            : 0;

    const censusData = census.labels.map((label, i) => ({
        label,
        Members: census.population[i],
        'Voice active': census.voiceActive[i],
    }));

    const [applicationsOpen, setApplicationsOpen] = useState(false);
    const [initialAppId, setInitialAppId] = useState<number | null>(null);
    const [activityOpen, setActivityOpen] = useState(false);

    const [platoonList, setPlatoonList] = useState(platoons);
    const [dropHoverId, setDropHoverId] = useState<number | null>(null);
    const platoonsRef = useRef<HTMLElement>(null);

    useEffect(() => setPlatoonList(platoons), [platoons]);

    const autoOrganize =
        typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('organize') === '1';

    const organize = useOrganize({
        members: organizeProps.members,
        autoOpen: autoOrganize,
        assign: async (memberId, platoonId) => {
            await postJson(`/members/${memberId}/assign-platoon`, { platoon_id: platoonId });
            setPlatoonList((prev) =>
                prev.map((p) => (p.id === platoonId ? { ...p, memberCount: p.memberCount + 1 } : p)),
            );
        },
    });

    useEffect(() => {
        if (autoOrganize) platoonsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }, [autoOrganize]);

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (params.has('applications') || params.has('application')) {
            const id = params.get('application');
            setInitialAppId(id ? Number(id) : null);
            setApplicationsOpen(true);
        }
    }, []);

    return (
        <AppLayout
            header={{
                eyebrow: 'Division',
                title: d.name,
                breadcrumbs: [{ label: 'Divisions' }, { label: d.name }],
                actions: (
                    <Button variant="outline" size="sm" asChild>
                        <a href={d.editUrl}>
                            <Settings /> Edit
                        </a>
                    </Button>
                ),
            }}
        >
            <Head title={d.name} />

            <div className="tron-stagger space-y-8">
                {d.shutdownAt && (
                    <div className="flex items-center gap-2 rounded-md border border-destructive/40 bg-destructive/5 px-4 py-3 text-sm font-medium text-destructive">
                        <TriangleAlert className="size-4 shrink-0" />
                        {d.shutdownPending
                            ? `This division is scheduled to shut down on ${d.shutdownAt}.`
                            : `This division was shut down on ${d.shutdownAt}.`}
                    </div>
                )}

                {/* Tools */}
                <DivisionToolbar
                    tools={toolbar}
                    overrides={{
                        applications: () => {
                            setInitialAppId(null);
                            setApplicationsOpen(true);
                        },
                    }}
                />

                {/* Pending actions */}
                {pendingActions.length > 0 && (
                    <div className="flex flex-wrap gap-2">
                        {pendingActions.map((action) => {
                            const inner = (
                                <>
                                    <PendingActionIcon icon={action.icon} className="size-4 text-muted-foreground" />
                                    <span className="numeric font-semibold">{action.count}</span>
                                    <span className="text-muted-foreground">
                                        {action.label}
                                        {action.count === 1 ? '' : 's'}
                                    </span>
                                </>
                            );
                            const opensOrganize =
                                action.key === 'unassigned-members' && organizeProps.canOrganize;
                            return (
                                <TileLink
                                    key={action.key}
                                    href={opensOrganize ? undefined : action.url}
                                    onClick={
                                        opensOrganize
                                            ? () => {
                                                  organize.setOrganizing(true);
                                                  platoonsRef.current?.scrollIntoView({
                                                      behavior: 'smooth',
                                                      block: 'start',
                                                  });
                                              }
                                            : undefined
                                    }
                                    tint={toneSurface(action.style)}
                                >
                                    {inner}
                                </TileLink>
                            );
                        })}
                    </div>
                )}

                {/* Quick stats */}
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <StatCard
                        align="center"
                        icon={<Users className="size-5" />}
                        value={<CountUp value={stats.memberCount} />}
                        label="Total members"
                        trend={populationTrend}
                        delta={
                            memberDelta !== 0 ? (
                                <span className={cn('text-xs', memberDelta < 0 ? 'text-destructive' : 'text-success')}>
                                    {memberDelta > 0 ? '+' : ''}
                                    {memberDelta}%
                                </span>
                            ) : undefined
                        }
                    />
                    <StatCard
                        align="center"
                        icon={<Headset className={cn('size-5', voiceTone(stats.voiceRate))} />}
                        value={<CountUp value={stats.voiceActiveCount} />}
                        label="Voice active"
                        sub={<span className="text-muted-foreground">({stats.activityThresholdDays}d)</span>}
                        trend={voiceRateTrend}
                        delta={<span className={cn('text-xs', voiceTone(stats.voiceRate))}>{stats.voiceRate}%</span>}
                    />
                    <StatCard
                        align="center"
                        icon={<UserPlus className="size-5 text-success" />}
                        value={<CountUp value={stats.recruitsThisMonth} />}
                        label="Recruits"
                        sub={<span className="text-muted-foreground">this month</span>}
                    />
                    {recentActivityCount > 0 && (
                        <StatCard
                            align="center"
                            icon={<History className="size-5 text-primary" />}
                            value={<CountUp value={recentActivityCount} />}
                            label="Recent actions"
                            onClick={recentActivity.length > 0 ? () => setActivityOpen(true) : undefined}
                        />
                    )}
                </div>

                {/* Census chart */}
                <section>
                    <div className="mb-3 flex items-baseline gap-3">
                        <h2 className="text-sm font-semibold">Population trend</h2>
                        {prev && (
                            <span className="text-xs text-muted-foreground">
                                prev. {prev.count} members · {prev.date}
                            </span>
                        )}
                    </div>
                    <div className="rounded-md border border-border bg-card p-4">
                        <ThemedLineChart
                            data={censusData}
                            x="label"
                            series={[
                                { key: 'Members', label: 'Members' },
                                { key: 'Voice active', label: 'Voice active' },
                            ]}
                            height={260}
                        />
                    </div>
                </section>

                {/* Leadership */}
                <section>
                    <SectionTitle>Leadership</SectionTitle>
                    {leaders.length === 0 ? (
                        <div className="rounded-md border border-primary/30 bg-primary/5 p-4 text-sm">
                            <p className="font-medium">No leadership assigned</p>
                            <p className="text-muted-foreground">See clan leadership for assistance with assignments</p>
                        </div>
                    ) : (
                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                            {leaders.map((leader) => (
                                <a
                                    key={leader.profileUrl}
                                    href={leader.profileUrl}
                                    className="flex items-center gap-3 rounded-md border border-destructive/30 bg-card p-4 transition-colors hover:border-destructive/50"
                                >
                                    <LeaderAvatar leader={leader} size="md" />
                                    <div className="min-w-0">
                                        <p className="truncate text-sm font-medium">{leader.rankName}</p>
                                        <p className="text-xs text-muted-foreground">{leader.position}</p>
                                    </div>
                                    <Shield className="ml-auto size-4 text-muted-foreground" />
                                </a>
                            ))}
                        </div>
                    )}
                </section>

                {/* Milestones */}
                {anniversaries.length > 0 && (
                    <section>
                        <SectionTitle>Milestones</SectionTitle>
                        <div className="flex flex-wrap gap-2">
                            {anniversaries.map((a) => (
                                <TileLink
                                    key={a.clanId}
                                    href={`/members/${a.clanId}-${encodeURIComponent(a.name)}`}
                                    hover={false}
                                    tint={cn(
                                        a.trophy ? 'border-primary/30 bg-primary/5' : 'border-border',
                                        !a.hasTenureAward && 'border-warning/40',
                                    )}
                                >
                                    <Star
                                        className="size-3.5"
                                        style={a.trophy ? { color: a.trophy.color } : undefined}
                                    />
                                    <span>
                                        {a.rankAbbr} {a.name}
                                    </span>
                                    <span className="numeric text-xs text-muted-foreground">{a.years}yr</span>
                                    {!a.hasTenureAward && (
                                        <TriangleAlert className="size-3 text-warning" aria-label="Tenure award not granted" />
                                    )}
                                </TileLink>
                            ))}
                        </div>
                    </section>
                )}

                {/* Platoons */}
                <section ref={platoonsRef}>
                    <SectionTitle
                        action={
                            d.canCreatePlatoon ? (
                                <Button variant="ghost" size="sm" asChild>
                                    <a href={d.editUrl}>Create {d.platoonLabel}</a>
                                </Button>
                            ) : undefined
                        }
                    >
                        {d.platoonLabel}s
                    </SectionTitle>

                    {organizeProps.canOrganize && (
                        <OrganizeBanner
                            members={organize.members}
                            unitLabel={d.platoonLabel}
                            organizing={organize.organizing}
                            onToggle={() => organize.setOrganizing((v) => !v)}
                            onDragStart={organize.onDragStart}
                            onDragEnd={organize.onDragEnd}
                            draggingId={organize.draggingId}
                        />
                    )}

                    {platoonList.length === 0 ? (
                        <p className="rounded-md border border-destructive/30 bg-card p-4 text-sm text-muted-foreground">
                            No {d.platoonLabel.toLowerCase()}s found
                        </p>
                    ) : (
                        <div className="grid gap-3 lg:grid-cols-2">
                            {platoonList.map((platoon, i) => (
                                <PlatoonCard
                                    key={platoon.id}
                                    index={i}
                                    platoon={platoon}
                                    organizing={organize.organizing}
                                    isHover={dropHoverId === platoon.id}
                                    {...dropZoneProps({
                                        organizing: organize.organizing,
                                        targetId: platoon.id,
                                        setHoverId: setDropHoverId,
                                        onDrop: organize.drop,
                                    })}
                                />
                            ))}
                        </div>
                    )}
                </section>
            </div>

            {d.applicationRequired && d.canRecruit && (
                <ApplicationsModal
                    url={d.applicationsUrl}
                    canDelete={d.canDeleteApplications}
                    open={applicationsOpen}
                    onOpenChange={setApplicationsOpen}
                    initialApplicationId={initialAppId}
                />
            )}

            <RecentActivityModal
                open={activityOpen}
                onOpenChange={setActivityOpen}
                groups={recentActivity}
                canViewAll={canViewAllActivity}
                allActivityUrl={allActivityUrl}
            />
        </AppLayout>
    );
}
