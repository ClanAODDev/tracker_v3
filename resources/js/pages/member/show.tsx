import { Head, Link } from '@inertiajs/react';
import {
    Bell,
    Copy,
    ExternalLink,
    Gamepad2,
    Layers,
    ListOrdered,
    MessageSquareText,
    Mic,
    Sparkles,
    TriangleAlert,
    Trophy,
    UserPlus,
    Wrench,
} from 'lucide-react';
import { type ReactNode, useState } from 'react';

import { RankHistoryList, RankTimeline, type RankTimelineData } from '@/components/member/rank-timeline';
import { NotesDialog, type MemberNote } from '@/components/member/notes';
import { MemberTagEditor, type DisplayTag, type TagManagement } from '@/components/member/tag-editor';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout';
import { cn } from '@/lib/utils';
import type { MemberCard } from '@/types';

interface Ref {
    name: string;
    url: string;
}

interface MemberShowProps {
    member: MemberCard & {
        status: 'pending' | 'ex-aod' | 'active';
        forumPmUrl: string;
        forumProfileUrl: string;
        hasAccount: boolean;
    };
    actions: Array<{ label: string; url: string; external?: boolean }>;
    tags: DisplayTag[];
    tagManagement: TagManagement | null;
    breadcrumbs: Array<{ label: string; href?: string }>;
    notices: Array<{ type: string; message: string; ctaLabel?: string; ctaUrl?: string }>;
    canCreateNote: boolean;
    canViewTrashed: boolean;
    noteTypes: Record<string, string>;
    stats: {
        tenure: {
            years: number;
            months: number;
            joinDate: string | null;
            recruitedBy: Ref | null;
            trainedOn: string | null;
            trainedBy: Ref | null;
        };
        activity: {
            daysSinceVoice: number | null;
            health: string;
            healthPct: number;
            divisionMax: number;
            reminders: Array<{ date: string; by: string }>;
            canClearReminders: boolean;
            clearRemindersUrl: string;
        };
        recruiting: {
            total: number;
            active: number;
            retentionRate: number | null;
            recruits: Array<{ name: string; url: string; joinDate: string | null; division: string; active: boolean }>;
        };
        notes: {
            total: number;
            positive: number;
            negative: number;
            misc: number;
            srLdr: number;
            latestType: string | null;
        };
    };
    awards: {
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
    };
    rankTimeline: RankTimelineData;
    canFullHistory: boolean;
    divisionComparison: {
        divisionName: string;
        tenurePercentile: number;
        tenureBetter: boolean;
        avgTenureYears: number;
        activityPercentile: number;
        activityBetter: boolean;
        avgVoiceDays: number;
        hasActivity: boolean;
    } | null;
    handles: {
        discord: string | null;
        discordUrl: string | null;
        groups: Array<{
            label: string;
            value: string;
            url: string | null;
            extras: Array<{ value: string; url: string | null }>;
        }>;
    };
    divisions: {
        current: { name: string; slug: string; logo: string; since: string | null } | null;
        partTime: Array<{ name: string; slug: string; logo: string; since: string | null }>;
        past: Array<{ name: string; slug: string; logo: string; duration: string; visits: number }>;
    };
    notes: MemberNote[];
    trashedNotes: MemberNote[];
}

const RARITY_ORDER = ['mythic', 'legendary', 'epic', 'rare', 'common'];

function rarityColor(rarity: string) {
    return `var(--rarity-${rarity}, var(--muted-foreground))`;
}

function SectionTitle({ children, action }: { children: ReactNode; action?: ReactNode }) {
    return (
        <div className="mb-3 flex items-center justify-between border-b border-border pb-2">
            <h2 className="text-sm font-semibold">{children}</h2>
            {action}
        </div>
    );
}

function StatTile({
    icon,
    value,
    unit,
    label,
    detail,
    onClick,
    tone,
}: {
    icon: ReactNode;
    value: ReactNode;
    unit?: string;
    label: string;
    detail?: ReactNode;
    onClick?: () => void;
    tone?: string;
}) {
    const Comp = onClick ? 'button' : 'div';
    return (
        <Comp
            onClick={onClick}
            className={cn(
                'flex w-full items-start gap-3 rounded-md border border-border bg-card p-4 text-left',
                onClick && 'transition-colors hover:border-primary/40',
            )}
        >
            <div className={cn('mt-0.5 text-muted-foreground', tone)}>{icon}</div>
            <div className="min-w-0 flex-1">
                <div className="numeric text-2xl font-semibold">
                    {value}
                    {unit && <span className="ml-0.5 text-sm text-muted-foreground">{unit}</span>}
                </div>
                <p className="text-xs text-muted-foreground">{label}</p>
                {detail && <div className="mt-1 text-xs text-muted-foreground">{detail}</div>}
            </div>
        </Comp>
    );
}

function DetailRow({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="flex items-center justify-between gap-4 border-b border-border py-2 text-sm last:border-0">
            <span className="text-muted-foreground">{label}</span>
            <span className="text-right">{children}</span>
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

export default function MemberShow(props: MemberShowProps) {
    const { member, actions, tags, tagManagement, breadcrumbs, notices, stats, awards, rankTimeline, canFullHistory } =
        props;
    const { divisionComparison, handles, divisions, notes, trashedNotes, noteTypes, canViewTrashed, canCreateNote } =
        props;

    const [tenureOpen, setTenureOpen] = useState(false);
    const [recruitsOpen, setRecruitsOpen] = useState(false);
    const [remindersOpen, setRemindersOpen] = useState(false);
    const [historyOpen, setHistoryOpen] = useState(false);
    const [notesOpen, setNotesOpen] = useState(false);

    const statusLabel =
        member.status === 'pending' ? 'Pending' : member.status === 'ex-aod' ? 'Ex-AOD' : (member.position ?? 'No position');

    const actionMenu = (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button size="sm" variant="outline">
                    <Wrench /> Actions
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {actions.map((action) => (
                    <DropdownMenuItem key={action.label} asChild>
                        <a href={action.url} target={action.external ? '_blank' : undefined}>
                            {action.label}
                        </a>
                    </DropdownMenuItem>
                ))}
                {actions.length > 0 && <DropdownMenuSeparator />}
                <DropdownMenuItem asChild>
                    <a href={member.forumPmUrl} target="_blank" rel="noreferrer">
                        Send forum PM
                    </a>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href={member.forumProfileUrl} target="_blank" rel="noreferrer">
                        View forum profile
                    </a>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );

    return (
        <AppLayout
            header={{
                eyebrow: statusLabel,
                title: member.rankName,
                breadcrumbs,
                actions: actionMenu,
            }}
        >
            <Head title={member.rankName} />

            <div className="space-y-8">
                <div className="flex flex-wrap items-center gap-3">
                    {member.avatarUrl && (
                        <img src={member.avatarUrl} alt="" className="size-12 rounded-full" />
                    )}
                    <MemberTagEditor tags={tags} management={tagManagement} />
                </div>

                {notices.length > 0 && (
                    <div className="space-y-2">
                        {notices.map((notice, i) => (
                            <div
                                key={i}
                                className="flex flex-wrap items-center gap-3 rounded-md border border-warning/40 bg-warning/5 px-4 py-3 text-sm"
                            >
                                <TriangleAlert className="size-4 shrink-0 text-warning" />
                                <span className="flex-1">{notice.message}</span>
                                {notice.ctaLabel && notice.ctaUrl && (
                                    <Button size="xs" variant="outline" asChild>
                                        <a href={notice.ctaUrl}>{notice.ctaLabel}</a>
                                    </Button>
                                )}
                            </div>
                        ))}
                    </div>
                )}

                {/* Stat tiles */}
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <StatTile
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
                        onClick={() => setTenureOpen(true)}
                    />

                    <StatTile
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
                                            <div
                                                className={cn(
                                                    'h-full rounded-full',
                                                    stats.activity.health === 'critical'
                                                        ? 'bg-destructive'
                                                        : stats.activity.health === 'warning'
                                                          ? 'bg-warning'
                                                          : 'bg-success',
                                                )}
                                                style={{ width: `${stats.activity.healthPct}%` }}
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
                                        onClick={() => setRemindersOpen(true)}
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

                    <StatTile
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
                        onClick={stats.recruiting.total > 0 ? () => setRecruitsOpen(true) : undefined}
                    />

                    {canCreateNote && (
                        <StatTile
                            icon={<MessageSquareText className="size-5" />}
                            value={stats.notes.total}
                            label="Notes"
                            onClick={() => setNotesOpen(true)}
                            detail={
                                stats.notes.total > 0 ? (
                                    <span className="flex gap-2">
                                        {stats.notes.positive > 0 && (
                                            <span className="text-success">+{stats.notes.positive}</span>
                                        )}
                                        {stats.notes.negative > 0 && (
                                            <span className="text-destructive">−{stats.notes.negative}</span>
                                        )}
                                        {stats.notes.misc > 0 && <span>{stats.notes.misc} general</span>}
                                        {stats.notes.srLdr > 0 && (
                                            <span className="text-primary">{stats.notes.srLdr} sr</span>
                                        )}
                                    </span>
                                ) : (
                                    'No notes recorded'
                                )
                            }
                        />
                    )}
                </div>

                {/* Rank progression */}
                <section>
                    <SectionTitle
                        action={
                            canFullHistory && rankTimeline.historyItems.length > 1 ? (
                                <Button size="xs" variant="ghost" onClick={() => setHistoryOpen(true)}>
                                    <ListOrdered /> Full history
                                </Button>
                            ) : undefined
                        }
                    >
                        Rank progression
                    </SectionTitle>
                    <div className="rounded-md border border-border bg-card p-5">
                        <RankTimeline timeline={rankTimeline} />
                    </div>
                </section>

                {/* Division comparison */}
                {divisionComparison && (
                    <section>
                        <SectionTitle>
                            Division comparison{' '}
                            <span className="ml-1 text-xs font-normal text-muted-foreground">
                                vs {divisionComparison.divisionName} avg
                            </span>
                        </SectionTitle>
                        <div className="grid gap-6 rounded-md border border-border bg-card p-5 md:grid-cols-2">
                            <ComparisonBar
                                label="Tenure"
                                percentile={divisionComparison.tenurePercentile}
                                better={divisionComparison.tenureBetter}
                                avgLabel={`Avg ${divisionComparison.avgTenureYears}y`}
                                lowLabel="Newer"
                                highLabel="Longer"
                            />
                            <ComparisonBar
                                label="Voice activity"
                                percentile={divisionComparison.activityPercentile}
                                better={divisionComparison.activityBetter}
                                avgLabel={`Avg ${divisionComparison.avgVoiceDays}d`}
                                lowLabel="Less active"
                                highLabel="More active"
                                na={!divisionComparison.hasActivity}
                            />
                        </div>
                    </section>
                )}

                {/* Achievements */}
                {awards.list.length > 0 && (
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
                                    style={{ '--rarity': rarityColor(award.rarity) } as React.CSSProperties}
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
                                            <span
                                                className="flex items-center"
                                                title={award.tiers.map((t) => t.name).join(', ')}
                                            >
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
                )}

                {/* Handles */}
                {(handles.discord || handles.groups.length > 0) && (
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
                )}

                {/* Divisions */}
                {(divisions.current || divisions.partTime.length > 0) && (
                    <section>
                        <SectionTitle>Divisions</SectionTitle>
                        <div className="flex flex-wrap gap-3">
                            {divisions.current && (
                                <DivisionCard division={divisions.current} badge="Primary" primary />
                            )}
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
                                            {d.visits > 1 && (
                                                <span className="text-muted-foreground">×{d.visits}</span>
                                            )}
                                        </Link>
                                    ))}
                                </div>
                            </>
                        )}
                    </section>
                )}
            </div>

            {canCreateNote && (
                <NotesDialog
                    memberClanId={member.clanId}
                    notes={notes}
                    trashedNotes={trashedNotes}
                    noteTypes={noteTypes}
                    canViewTrashed={canViewTrashed}
                    open={notesOpen}
                    onOpenChange={setNotesOpen}
                />
            )}

            {/* Tenure dialog */}
            <Dialog open={tenureOpen} onOpenChange={setTenureOpen}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Membership details</DialogTitle>
                        <DialogDescription className="sr-only">Tenure and training details</DialogDescription>
                    </DialogHeader>
                    <div>
                        <DetailRow label="Time in AOD">
                            {stats.tenure.years}y {stats.tenure.months > 0 && `${stats.tenure.months}m`}
                        </DetailRow>
                        {stats.tenure.joinDate && <DetailRow label="Join date">{stats.tenure.joinDate}</DetailRow>}
                        {stats.tenure.recruitedBy && (
                            <DetailRow label="Recruited by">
                                <Link href={stats.tenure.recruitedBy.url} className="text-primary hover:underline">
                                    {stats.tenure.recruitedBy.name}
                                </Link>
                            </DetailRow>
                        )}
                        {stats.tenure.trainedOn && <DetailRow label="Trained on">{stats.tenure.trainedOn}</DetailRow>}
                        {stats.tenure.trainedBy && (
                            <DetailRow label="Trained by">
                                <Link href={stats.tenure.trainedBy.url} className="text-primary hover:underline">
                                    {stats.tenure.trainedBy.name}
                                </Link>
                            </DetailRow>
                        )}
                        <DetailRow label="Forum ID">{member.clanId}</DetailRow>
                    </div>
                </DialogContent>
            </Dialog>

            {/* Recruits dialog */}
            <Dialog open={recruitsOpen} onOpenChange={setRecruitsOpen}>
                <DialogContent className="max-w-lg">
                    <DialogHeader>
                        <DialogTitle>Recruits ({stats.recruiting.total})</DialogTitle>
                        <DialogDescription className="sr-only">Members recruited by {member.name}</DialogDescription>
                    </DialogHeader>
                    <div className="max-h-[60vh] space-y-1 overflow-y-auto">
                        {stats.recruiting.recruits.map((recruit) => (
                            <div
                                key={recruit.url}
                                className="flex items-center justify-between gap-3 border-b border-border py-2 text-sm last:border-0"
                            >
                                <Link href={recruit.url} className="text-primary hover:underline">
                                    {recruit.name}
                                </Link>
                                <span className="text-xs text-muted-foreground">{recruit.joinDate}</span>
                                <span className="text-xs text-muted-foreground">{recruit.division}</span>
                                <Badge variant={recruit.active ? 'default' : 'outline'} className="text-[10px]">
                                    {recruit.active ? 'Active' : 'Inactive'}
                                </Badge>
                            </div>
                        ))}
                    </div>
                </DialogContent>
            </Dialog>

            {/* Reminder history dialog */}
            <Dialog open={remindersOpen} onOpenChange={setRemindersOpen}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Reminder history</DialogTitle>
                        <DialogDescription>
                            {stats.activity.reminders.length} inactivity reminder
                            {stats.activity.reminders.length === 1 ? '' : 's'} sent to this member.
                        </DialogDescription>
                    </DialogHeader>
                    <ul className="-mx-1 max-h-[55vh] space-y-1.5 overflow-y-auto px-1">
                        {stats.activity.reminders.map((reminder, i) => (
                            <li key={i} className="flex items-center justify-between gap-4 text-sm">
                                <span className="numeric shrink-0 text-xs text-muted-foreground">{reminder.date}</span>
                                <span className="truncate">{reminder.by}</span>
                            </li>
                        ))}
                    </ul>
                    {stats.activity.canClearReminders && (
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={() => {
                                // eslint-disable-next-line no-alert
                                if (confirm('Clear all reminders for this member?')) {
                                    window.location.href = stats.activity.clearRemindersUrl;
                                }
                            }}
                        >
                            Clear reminders
                        </Button>
                    )}
                </DialogContent>
            </Dialog>

            {/* Full rank history dialog */}
            <Dialog open={historyOpen} onOpenChange={setHistoryOpen}>
                <DialogContent className="max-w-sm">
                    <DialogHeader>
                        <DialogTitle>Rank history</DialogTitle>
                        <DialogDescription className="sr-only">Full rank change history</DialogDescription>
                    </DialogHeader>
                    <RankHistoryList items={rankTimeline.historyItems} />
                </DialogContent>
            </Dialog>
        </AppLayout>
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
