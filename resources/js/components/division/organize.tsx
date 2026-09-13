import { ArrowLeftRight, Check, TriangleAlert } from 'lucide-react';
import { type DragEvent, useCallback, useEffect, useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export interface OrganizeMember {
    id: number;
    name: string;
}

interface UseOrganizeOptions {
    members: OrganizeMember[];
    assign: (memberId: number, targetId: number) => Promise<void>;
    autoOpen?: boolean;
}

export function useOrganize({ members: initial, assign, autoOpen = false }: UseOrganizeOptions) {
    const [members, setMembers] = useState(initial);
    const [organizing, setOrganizing] = useState(false);
    const [draggingId, setDraggingId] = useState<number | null>(null);
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        setMembers(initial);
    }, [initial]);

    useEffect(() => {
        if (autoOpen && initial.length > 0) setOrganizing(true);
    }, [autoOpen, initial.length]);

    useEffect(() => {
        if (organizing && members.length === 0) setOrganizing(false);
    }, [organizing, members.length]);

    const onDragStart = useCallback((e: DragEvent, id: number) => {
        setDraggingId(id);
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/plain', String(id));
    }, []);

    const onDragEnd = useCallback(() => setDraggingId(null), []);

    const drop = useCallback(
        async (targetId: number) => {
            const id = draggingId;
            setDraggingId(null);
            if (id == null || busy) return;
            const member = members.find((m) => m.id === id);
            if (!member) return;

            setBusy(true);
            setMembers((prev) => prev.filter((m) => m.id !== id));
            try {
                await assign(id, targetId);
                toast.success(`${member.name} assigned`);
            } catch (e) {
                setMembers((prev) => [...prev, member]);
                toast.error(e instanceof Error ? e.message : 'Assignment failed');
            } finally {
                setBusy(false);
            }
        },
        [assign, busy, draggingId, members],
    );

    return { members, organizing, setOrganizing, draggingId, onDragStart, onDragEnd, drop };
}

export function OrganizeBanner({
    members,
    unitLabel,
    organizing,
    onToggle,
    onDragStart,
    onDragEnd,
    draggingId,
}: {
    members: OrganizeMember[];
    unitLabel: string;
    organizing: boolean;
    onToggle: () => void;
    onDragStart: (e: DragEvent, id: number) => void;
    onDragEnd: () => void;
    draggingId: number | null;
}) {
    if (members.length === 0) return null;

    return (
        <section
            className={cn(
                'mb-6 rounded-md border px-4 py-3 transition-colors',
                organizing ? 'border-primary/50 bg-primary/5' : 'border-warning/40 bg-warning/5',
            )}
        >
            <div className="flex items-center justify-between gap-3">
                <span className="flex items-center gap-2 text-sm font-medium">
                    <TriangleAlert className="size-4 text-warning" />
                    <span className="numeric font-semibold">{members.length}</span> member
                    {members.length === 1 ? '' : 's'} not assigned to a {unitLabel}
                </span>
                <Button size="sm" variant={organizing ? 'default' : 'outline'} onClick={onToggle}>
                    {organizing ? (
                        <>
                            <Check /> Done
                        </>
                    ) : (
                        <>
                            <ArrowLeftRight /> Organize
                        </>
                    )}
                </Button>
            </div>

            {organizing && (
                <div className="mt-3 flex flex-wrap gap-2">
                    {members.map((member) => (
                        <span
                            key={member.id}
                            draggable
                            onDragStart={(e) => onDragStart(e, member.id)}
                            onDragEnd={onDragEnd}
                            className={cn(
                                'cursor-grab rounded border border-border bg-card px-2.5 py-1.5 text-sm active:cursor-grabbing',
                                draggingId === member.id && 'opacity-40',
                            )}
                        >
                            {member.name}
                        </span>
                    ))}
                    <span className="self-center text-xs text-muted-foreground">
                        Drag onto a {unitLabel} below
                    </span>
                </div>
            )}
        </section>
    );
}

export function dropZoneProps({
    organizing,
    targetId,
    setHoverId,
    onDrop,
}: {
    organizing: boolean;
    targetId: number;
    setHoverId: (id: number | null) => void;
    onDrop: (targetId: number) => void;
}) {
    if (!organizing) return {};
    return {
        onDragOver: (e: DragEvent) => {
            e.preventDefault();
            setHoverId(targetId);
        },
        onDragLeave: () => setHoverId(null),
        onDrop: (e: DragEvent) => {
            e.preventDefault();
            setHoverId(null);
            onDrop(targetId);
        },
    };
}
