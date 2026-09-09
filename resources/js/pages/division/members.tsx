import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Settings2, Users } from 'lucide-react';

import { MemberTable } from '@/components/members/member-table';
import type { BulkConfig, MemberListDivision, MemberRow, UnitStats } from '@/components/members/types';
import { UnitStatsPanel } from '@/components/members/unit-stats-panel';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout';
import { cn } from '@/lib/utils';

interface Crumb {
    label: string;
    href?: string;
}

interface SquadEntry {
    name: string;
    url: string;
    leader: string | null;
    count?: number;
    voiceRate?: number;
    current?: boolean;
}

interface Scope {
    kind: 'division' | 'platoon' | 'squad';
    name: string;
    breadcrumbs?: Crumb[];
    canManage?: boolean;
    editUrl?: string | null;
    manageUrl?: string | null;
    unassignedCount?: number;
    platoonLabel?: string;
}

interface Props {
    division: MemberListDivision;
    members: MemberRow[];
    assignmentKind: 'platoon' | 'squad';
    unitStats: UnitStats;
    tagFilter: Array<{ id: number; name: string; count: number }>;
    bulk: BulkConfig;
    scope: Scope;
    includeParttimers?: boolean;
    squads?: SquadEntry[];
}

export default function MembersPage({
    division,
    members,
    assignmentKind,
    unitStats,
    tagFilter,
    bulk,
    scope,
    includeParttimers,
    squads,
}: Props) {
    const title =
        scope.kind === 'division' ? `${division.name} members` : scope.name;

    const breadcrumbs: Crumb[] =
        scope.breadcrumbs ??
        [
            { label: 'Divisions' },
            { label: division.name, href: `/divisions/${division.slug}` },
            { label: 'Members' },
        ];

    const actions =
        scope.kind === 'division' ? (
            <Button
                variant={includeParttimers ? 'default' : 'outline'}
                size="sm"
                onClick={() =>
                    router.get(
                        `/divisions/${division.slug}/members`,
                        includeParttimers ? {} : { parttimers: 1 },
                        { preserveState: false },
                    )
                }
            >
                <Users /> Part-timers
            </Button>
        ) : (
            <>
                {scope.manageUrl && (
                    <Button variant="outline" size="sm" asChild>
                        <a href={scope.manageUrl}>
                            <Settings2 /> Manage assignments
                        </a>
                    </Button>
                )}
                {scope.editUrl && (
                    <Button variant="outline" size="sm" asChild>
                        <a href={scope.editUrl}>
                            <Pencil /> Edit
                        </a>
                    </Button>
                )}
            </>
        );

    return (
        <AppLayout
            header={{
                eyebrow:
                    scope.kind === 'division'
                        ? 'Division'
                        : scope.kind === 'platoon'
                          ? division.platoonLabel
                          : division.squadLabel,
                title,
                breadcrumbs,
                actions,
            }}
        >
            <Head title={title} />

            {scope.kind === 'platoon' && !scope.canManage && (scope.unassignedCount ?? 0) > 0 && (
                <div className="mb-6 rounded-md border border-warning/40 bg-warning/5 px-4 py-3 text-sm">
                    This {scope.platoonLabel?.toLowerCase()} has{' '}
                    <span className="numeric font-semibold">{scope.unassignedCount}</span> unassigned member
                    {scope.unassignedCount === 1 ? '' : 's'}.
                </div>
            )}

            <div className="grid gap-6 lg:grid-cols-[1fr_15rem]">
                <MemberTable
                    rows={members}
                    division={division}
                    assignmentKind={assignmentKind}
                    bulk={bulk}
                    tagFilter={tagFilter}
                    storageKey={`member-table:${scope.kind}:${division.slug}`}
                />
                <div className="order-first space-y-6 lg:order-last">
                    {squads && squads.length > 0 && <SquadsList squads={squads} />}
                    <UnitStatsPanel stats={unitStats} />
                </div>
            </div>
        </AppLayout>
    );
}

function SquadsList({ squads }: { squads: SquadEntry[] }) {
    return (
        <div className="divide-y divide-border overflow-hidden rounded-md border border-border">
            {squads.map((squad) => (
                <Link
                    key={squad.url}
                    href={squad.url}
                    className={cn(
                        'block px-3 py-2.5 text-sm transition-colors hover:bg-muted/50',
                        squad.current && 'bg-primary/10',
                    )}
                >
                    <div className="flex items-center justify-between gap-2">
                        <span className="font-medium">{squad.name}</span>
                        {squad.count !== undefined && (
                            <span className="numeric text-xs text-muted-foreground">{squad.count}</span>
                        )}
                    </div>
                    <div className="flex items-center justify-between gap-2 text-xs text-muted-foreground">
                        <span>{squad.leader ?? 'TBA'}</span>
                        {squad.voiceRate !== undefined && (
                            <span
                                className={cn(
                                    'numeric',
                                    squad.voiceRate >= 50
                                        ? 'text-success'
                                        : squad.voiceRate >= 25
                                          ? 'text-warning'
                                          : 'text-destructive',
                                )}
                            >
                                {squad.voiceRate}%
                            </span>
                        )}
                    </div>
                </Link>
            ))}
        </div>
    );
}
