import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Plus, TriangleAlert, Users } from 'lucide-react';
import { type DragEvent, useMemo, useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface MemberCard {
    id: number;
    name: string;
    isDirectRecruit?: boolean;
}

interface Squad {
    id: number;
    name: string;
    leader: string | null;
    members: MemberCard[];
}

interface Props {
    division: { name: string; squadLabel: string; squadPlural: string; platoonLabel: string };
    platoon: { id: number; name: string };
    squads: Squad[];
    unassigned: MemberCard[];
    assignUrl: string;
    backUrl: string;
    createSquadUrl: string;
}

const UNASSIGNED = 'unassigned';
const REMOVE = 'remove';
type Bucket = number | typeof UNASSIGNED | typeof REMOVE;

export default function ManageMembers({
    division,
    platoon,
    squads: initialSquads,
    unassigned: initialUnassigned,
    assignUrl,
    backUrl,
    createSquadUrl,
}: Props) {
    const [squads, setSquads] = useState(initialSquads);
    const [unassigned, setUnassigned] = useState(initialUnassigned);
    const [dragging, setDragging] = useState<{ id: number; from: Bucket } | null>(null);
    const [hover, setHover] = useState<Bucket | null>(null);

    const memberIndex = useMemo(() => {
        const map = new Map<number, MemberCard>();
        initialSquads.forEach((squad) => squad.members.forEach((member) => map.set(member.id, member)));
        initialUnassigned.forEach((member) => map.set(member.id, member));
        return map;
    }, [initialSquads, initialUnassigned]);

    function removeFrom(bucket: Bucket, id: number) {
        if (bucket === UNASSIGNED) {
            setUnassigned((prev) => prev.filter((member) => member.id !== id));
        } else if (typeof bucket === 'number') {
            setSquads((prev) =>
                prev.map((squad) =>
                    squad.id === bucket
                        ? { ...squad, members: squad.members.filter((member) => member.id !== id) }
                        : squad,
                ),
            );
        }
    }

    function addTo(bucket: Bucket, card: MemberCard) {
        if (bucket === UNASSIGNED) {
            setUnassigned((prev) => [...prev, { id: card.id, name: card.name }]);
        } else if (typeof bucket === 'number') {
            setSquads((prev) =>
                prev.map((squad) =>
                    squad.id === bucket
                        ? { ...squad, members: [...squad.members, { id: card.id, name: card.name }] }
                        : squad,
                ),
            );
        }
    }

    async function drop(target: Bucket) {
        setHover(null);
        if (!dragging || dragging.from === target) {
            setDragging(null);
            return;
        }
        const card = memberIndex.get(dragging.id);
        if (!card) return;

        const from = dragging.from;
        setDragging(null);
        removeFrom(from, card.id);
        addTo(target, card);

        const squadId = target === REMOVE || target === UNASSIGNED ? 0 : target;
        try {
            await postJson(assignUrl, { member_id: card.id, squad_id: squadId });
            toast.success(
                target === REMOVE
                    ? `${card.name} removed from the ${division.platoonLabel}`
                    : `${card.name} reassigned`,
            );
        } catch (e) {
            removeFrom(target, card.id);
            addTo(from, card);
            toast.error(e instanceof Error ? e.message : 'Reassignment failed');
        }
    }

    function onDragStart(e: DragEvent, id: number, from: Bucket) {
        setDragging({ id, from });
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(id));
    }

    function List({
        bucket,
        members,
        empty,
        scroll,
    }: {
        bucket: Bucket;
        members: MemberCard[];
        empty?: string;
        scroll?: boolean;
    }) {
        return (
            <ul
                onDragOver={(e) => {
                    e.preventDefault();
                    setHover(bucket);
                }}
                onDragLeave={() => setHover((h) => (h === bucket ? null : h))}
                onDrop={() => drop(bucket)}
                className={cn(
                    'min-h-16 space-y-1 rounded-md border border-dashed border-border p-2 transition-colors',
                    scroll && 'max-h-96 overflow-y-auto',
                    hover === bucket && 'border-primary/60 bg-primary/5',
                )}
            >
                {members.length === 0 && (
                    <li className="px-1 py-2 text-center text-xs text-muted-foreground">{empty ?? 'Empty'}</li>
                )}
                {members.map((member) => (
                    <li
                        key={member.id}
                        draggable
                        onDragStart={(e) => onDragStart(e, member.id, bucket)}
                        onDragEnd={() => {
                            setDragging(null);
                            setHover(null);
                        }}
                        className={cn(
                            'flex cursor-grab items-center gap-2 rounded border border-border bg-card px-2.5 py-1.5 text-sm active:cursor-grabbing',
                            dragging?.id === member.id && 'opacity-40',
                        )}
                    >
                        <span className="flex-1">{member.name}</span>
                        {member.isDirectRecruit && (
                            <span title="Direct recruit" className="font-bold text-[#e05cff]">
                                *
                            </span>
                        )}
                    </li>
                ))}
            </ul>
        );
    }

    return (
        <AppLayout
            header={{
                eyebrow: `${platoon.name} · ${division.name} Division`,
                title: `Manage ${division.squadLabel} assignments`,
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: platoon.name, href: backUrl },
                    { label: `Manage ${division.squadLabel}s` },
                ],
                actions: (
                    <>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={backUrl}>
                                <ArrowLeft /> Back to {division.platoonLabel}
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <a href={createSquadUrl} target="_blank" rel="noreferrer">
                                <Plus /> Create {division.squadLabel}
                            </a>
                        </Button>
                    </>
                ),
            }}
        >
            <Head title={`Manage ${division.squadLabel}s · ${platoon.name}`} />

            <div className="space-y-6">
                <p className="text-sm text-muted-foreground">
                    Drag members between {division.squadPlural} to reassign them. {division.squadLabel} leaders are not
                    listed here and cannot be moved.
                </p>

                {unassigned.length > 0 && (
                    <section className="rounded-md border border-warning/40 bg-warning/5 p-3">
                        <h2 className="mb-2 flex items-center gap-2 text-sm font-medium">
                            <TriangleAlert className="size-4 text-warning" />
                            {unassigned.length} not assigned to a {division.squadLabel}
                        </h2>
                        <List bucket={UNASSIGNED} members={unassigned} empty="All assigned" />
                    </section>
                )}

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {squads.map((squad) => (
                        <section key={squad.id} className="rounded-md border border-border bg-card">
                            <div className="flex items-center justify-between border-b border-border px-3 py-2">
                                <div>
                                    <span className="text-sm font-semibold">{squad.name}</span>
                                    <span className="ml-2 inline-flex items-center gap-1 text-xs text-muted-foreground">
                                        <Users className="size-3" />
                                        {squad.members.length}
                                    </span>
                                </div>
                                <span className="text-xs text-muted-foreground">{squad.leader ?? 'TBA'}</span>
                            </div>
                            <div className="p-2">
                                <List bucket={squad.id} members={squad.members} empty="Drop members here" scroll />
                            </div>
                        </section>
                    ))}
                </div>

                <section className="rounded-md border border-destructive/40 bg-destructive/5 p-3">
                    <h2 className="mb-2 flex items-center gap-2 text-sm font-medium text-destructive">
                        <TriangleAlert className="size-4" />
                        Unassign from {division.platoonLabel}
                    </h2>
                    <List
                        bucket={REMOVE}
                        members={[]}
                        empty={`Drag members here to remove them from this ${division.platoonLabel}`}
                    />
                </section>
            </div>
        </AppLayout>
    );
}
