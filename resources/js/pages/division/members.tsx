import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Settings2, Users } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { OrganizeBanner, dropZoneProps, useOrganize, type OrganizeMember } from '@/components/division/organize';
import { MemberTable } from '@/components/members/member-table';
import type { BulkConfig, MemberListDivision, MemberRow, UnitStats } from '@/components/members/types';
import { UnitStatsPanel } from '@/components/members/unit-stats-panel';
import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import AppLayout from '@/layouts/AppLayout';
import { cn } from '@/lib/utils';

interface Crumb {
    label: string;
    href?: string;
}

interface SquadEntry {
    id?: number;
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
    squadLabel?: string;
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
    organize?: { canOrganize: boolean; members: OrganizeMember[] };
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
    organize: organizeProps,
}: Props) {
    const title =
        scope.kind === 'division' ? `${division.name} members` : scope.name;

    const [squadList, setSquadList] = useState<SquadEntry[]>(squads ?? []);
    const [dropHoverId, setDropHoverId] = useState<number | null>(null);
    const squadsRef = useRef<HTMLDivElement>(null);

    useEffect(() => setSquadList(squads ?? []), [squads]);

    const canOrganize = scope.kind === 'platoon' && (organizeProps?.canOrganize ?? false);
    const autoOrganize =
        typeof window !== 'undefined' && new URLSearchParams(window.location.search).get('organize') === '1';

    const organize = useOrganize({
        members: organizeProps?.members ?? [],
        autoOpen: canOrganize && autoOrganize,
        assign: async (memberId, squadId) => {
            await postJson('/members/assign-squad', { member_id: memberId, squad_id: squadId });
            setSquadList((prev) =>
                prev.map((s) => (s.id === squadId ? { ...s, count: (s.count ?? 0) + 1 } : s)),
            );
        },
    });

    useEffect(() => {
        if (canOrganize && autoOrganize) {
            squadsRef.current?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }, [canOrganize, autoOrganize]);

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

            {canOrganize && (
                <OrganizeBanner
                    members={organize.members}
                    unitLabel={scope.squadLabel ?? 'Squad'}
                    organizing={organize.organizing}
                    onToggle={() => organize.setOrganizing((v) => !v)}
                    onDragStart={organize.onDragStart}
                    onDragEnd={organize.onDragEnd}
                    draggingId={organize.draggingId}
                />
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
                    {squadList.length > 0 && (
                        <div ref={squadsRef}>
                            <SquadsList
                                squads={squadList}
                                organizing={organize.organizing}
                                dropHoverId={dropHoverId}
                                setDropHoverId={setDropHoverId}
                                onDrop={organize.drop}
                            />
                        </div>
                    )}
                    <UnitStatsPanel stats={unitStats} />
                </div>
            </div>
        </AppLayout>
    );
}

function SquadsList({
    squads,
    organizing,
    dropHoverId,
    setDropHoverId,
    onDrop,
}: {
    squads: SquadEntry[];
    organizing: boolean;
    dropHoverId: number | null;
    setDropHoverId: (id: number | null) => void;
    onDrop: (targetId: number) => void;
}) {
    return (
        <div
            className={cn(
                'divide-y divide-border overflow-hidden rounded-md border',
                organizing ? 'border-dashed border-primary/40' : 'border-border',
            )}
        >
            {squads.map((squad) => {
                const inner = (
                    <>
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
                    </>
                );

                if (organizing && squad.id !== undefined) {
                    return (
                        <div
                            key={squad.id}
                            {...dropZoneProps({
                                organizing,
                                targetId: squad.id,
                                setHoverId: setDropHoverId,
                                onDrop,
                            })}
                            className={cn(
                                'block px-3 py-2.5 text-sm transition-colors',
                                dropHoverId === squad.id && 'bg-primary/10',
                            )}
                        >
                            {inner}
                        </div>
                    );
                }

                return (
                    <Link
                        key={squad.url}
                        href={squad.url}
                        className={cn(
                            'block px-3 py-2.5 text-sm transition-colors hover:bg-muted/50',
                            squad.current && 'bg-primary/10',
                        )}
                    >
                        {inner}
                    </Link>
                );
            })}
        </div>
    );
}
