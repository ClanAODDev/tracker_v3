import { Head, Link } from '@inertiajs/react';
import { ArrowLeft, Plus, TriangleAlert, Users } from 'lucide-react';
import { type DragEvent, useMemo, useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { postJson } from '@/lib/api';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';
import type { Crumb } from '@/components/page-header';

interface MemberCard {
    id: number;
    name: string;
    isDirectRecruit?: boolean;
}

interface Column {
    id: number;
    name: string;
    trail?: string;
    levelLabel: string;
    leader?: string | null;
    members: MemberCard[];
}

interface Props {
    division: { name: string };
    root: { id: number; name: string; levelLabel: string; members: MemberCard[] };
    units: Column[];
    assignUrl: string;
    backUrl: string;
    breadcrumbs: Crumb[];
    createUrl: string;
}

const REMOVE = 'remove';
type Bucket = number | typeof REMOVE;

function DropList({
    bucket,
    members,
    empty,
    scroll,
    hover,
    draggingId,
    onDragOver,
    onDragLeave,
    onDrop,
    onDragStart,
    onDragEnd,
}: {
    bucket: Bucket;
    members: MemberCard[];
    empty?: string;
    scroll?: boolean;
    hover: boolean;
    draggingId: number | null;
    onDragOver: () => void;
    onDragLeave: () => void;
    onDrop: () => void;
    onDragStart: (e: DragEvent, id: number, from: Bucket) => void;
    onDragEnd: () => void;
}) {
    return (
        <ul
            onDragOver={(e) => {
                e.preventDefault();
                onDragOver();
            }}
            onDragLeave={onDragLeave}
            onDrop={onDrop}
            className={cn(
                'min-h-16 space-y-1 rounded-md border border-dashed border-border p-2 transition-colors',
                scroll && 'max-h-96 overflow-y-auto',
                hover && 'border-primary/60 bg-primary/5',
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
                    onDragEnd={onDragEnd}
                    className={cn(
                        'flex cursor-grab items-center gap-2 rounded border border-border bg-card px-2.5 py-1.5 text-sm active:cursor-grabbing',
                        draggingId === member.id && 'opacity-40',
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

export default function ManageMembers({ division, root, units, assignUrl, backUrl, breadcrumbs, createUrl }: Props) {
    const [columns, setColumns] = useState<Column[]>(() => [
        { id: root.id, name: root.name, levelLabel: root.levelLabel, members: root.members },
        ...units,
    ]);
    const [dragging, setDragging] = useState<{ id: number; from: Bucket } | null>(null);
    const [hover, setHover] = useState<Bucket | null>(null);

    const memberIndex = useMemo(() => {
        const map = new Map<number, MemberCard>();
        columns.forEach((column) => column.members.forEach((member) => map.set(member.id, member)));
        return map;
    }, [columns]);

    function removeFrom(bucket: Bucket, id: number) {
        if (bucket === REMOVE) return;
        setColumns((prev) =>
            prev.map((column) =>
                column.id === bucket ? { ...column, members: column.members.filter((member) => member.id !== id) } : column,
            ),
        );
    }

    function addTo(bucket: Bucket, card: MemberCard) {
        if (bucket === REMOVE) return;
        setColumns((prev) =>
            prev.map((column) =>
                column.id === bucket
                    ? { ...column, members: [...column.members, { id: card.id, name: card.name }] }
                    : column,
            ),
        );
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
        const targetName = target === REMOVE ? null : columns.find((column) => column.id === target)?.name;
        setDragging(null);
        removeFrom(from, card.id);
        addTo(target, card);

        try {
            await postJson(assignUrl, { member_id: card.id, unit_id: target === REMOVE ? 0 : target });
            toast.success(
                target === REMOVE ? `${card.name} removed from ${root.name}` : `${card.name} moved to ${targetName}`,
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

    function onDragEnd() {
        setDragging(null);
        setHover(null);
    }

    const rootColumn = columns[0];
    const rest = columns.slice(1);

    function listFor(column: Column, empty: string, scroll?: boolean) {
        return (
            <DropList
                bucket={column.id}
                members={column.members}
                empty={empty}
                scroll={scroll}
                hover={hover === column.id}
                draggingId={dragging?.id ?? null}
                onDragOver={() => setHover(column.id)}
                onDragLeave={() => setHover((h) => (h === column.id ? null : h))}
                onDrop={() => drop(column.id)}
                onDragStart={onDragStart}
                onDragEnd={onDragEnd}
            />
        );
    }

    return (
        <AppLayout
            header={{
                eyebrow: `${root.name} · ${division.name} Division`,
                title: `Manage ${root.levelLabel} assignments`,
                breadcrumbs: [...breadcrumbs, { label: 'Manage assignments' }],
                actions: (
                    <>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={backUrl}>
                                <ArrowLeft /> Back to {root.levelLabel}
                            </Link>
                        </Button>
                        <Button variant="outline" size="sm" asChild>
                            <a href={createUrl} target="_blank" rel="noreferrer">
                                <Plus /> Edit {root.levelLabel}
                            </a>
                        </Button>
                    </>
                ),
            }}
        >
            <Head title={`Manage assignments · ${root.name}`} />

            <div className="space-y-6">
                <p className="text-sm text-muted-foreground">
                    Drag members into any unit under {root.name} to reassign them. Members dropped on {root.name} belong
                    to it directly. Unit leaders are not listed here and cannot be moved.
                </p>

                <section className="rounded-md border border-border bg-card">
                    <div className="flex items-center justify-between border-b border-border px-3 py-2">
                        <div>
                            <span className="text-sm font-semibold">Directly in {rootColumn.name}</span>
                            <span className="ml-2 inline-flex items-center gap-1 text-xs text-muted-foreground">
                                <Users className="size-3" />
                                {rootColumn.members.length}
                            </span>
                        </div>
                        <span className="text-xs text-muted-foreground">{rootColumn.levelLabel}</span>
                    </div>
                    <div className="p-2">{listFor(rootColumn, `No members placed directly in ${rootColumn.name}`, true)}</div>
                </section>

                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {rest.map((column) => (
                        <section key={column.id} className="rounded-md border border-border bg-card">
                            <div className="flex items-center justify-between gap-2 border-b border-border px-3 py-2">
                                <div className="min-w-0">
                                    <div className="truncate text-sm font-semibold">{column.name}</div>
                                    <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                        <span>{column.levelLabel}</span>
                                        <span className="inline-flex items-center gap-1">
                                            <Users className="size-3" />
                                            {column.members.length}
                                        </span>
                                    </div>
                                    {column.trail && column.trail !== column.name && (
                                        <div className="truncate text-xs text-muted-foreground">{column.trail}</div>
                                    )}
                                </div>
                                <span className="shrink-0 text-xs text-muted-foreground">{column.leader ?? 'TBA'}</span>
                            </div>
                            <div className="p-2">{listFor(column, 'Drop members here', true)}</div>
                        </section>
                    ))}
                </div>

                <section className="rounded-md border border-destructive/40 bg-destructive/5 p-3">
                    <h2 className="mb-2 flex items-center gap-2 text-sm font-medium text-destructive">
                        <TriangleAlert className="size-4" />
                        Unassign from {root.name}
                    </h2>
                    <DropList
                        bucket={REMOVE}
                        members={[]}
                        empty={`Drag members here to remove them from ${root.name}`}
                        hover={hover === REMOVE}
                        draggingId={dragging?.id ?? null}
                        onDragOver={() => setHover(REMOVE)}
                        onDragLeave={() => setHover((h) => (h === REMOVE ? null : h))}
                        onDrop={() => drop(REMOVE)}
                        onDragStart={onDragStart}
                        onDragEnd={onDragEnd}
                    />
                </section>
            </div>
        </AppLayout>
    );
}
