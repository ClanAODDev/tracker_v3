import { Link } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { Bell, Clock } from 'lucide-react';
import { type CSSProperties, type Dispatch, type ReactNode, type SetStateAction, useMemo } from 'react';
import { toast } from 'sonner';

import type { MemberFieldDefinition, MemberRow } from '@/components/members/types';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { postJson } from '@/lib/api';
import { cn } from '@/lib/utils';

const DOT_TINT = ['bg-success', 'bg-warning', 'bg-destructive', 'bg-muted-foreground'] as const;

interface Options {
    assignmentLabel: string;
    bulkMode: boolean;
    selectedTags: Set<number>;
    reminded: Record<number, string>;
    setReminded: Dispatch<SetStateAction<Record<number, string>>>;
    activityStyle: 'row' | 'dot';
    memberFields: MemberFieldDefinition[];
    selectedFieldValues: Record<string, Set<string>>;
}

export function fieldColumnId(key: string): string {
    return `field:${key}`;
}

const FIELD_BADGE_COLORS: Record<string, string> = {
    gray: 'bg-muted text-muted-foreground',
    red: 'bg-chart-1/15 text-chart-1',
    orange: 'bg-rarity-legendary/15 text-rarity-legendary',
    yellow: 'bg-chart-3/15 text-chart-3',
    green: 'bg-chart-4/15 text-chart-4',
    blue: 'bg-chart-2/15 text-chart-2',
    violet: 'bg-chart-5/15 text-chart-5',
    pink: 'bg-rarity-epic/15 text-rarity-epic',
};

export function useMemberColumns({
    assignmentLabel,
    bulkMode,
    selectedTags,
    reminded,
    setReminded,
    activityStyle,
    memberFields,
    selectedFieldValues,
}: Options): ColumnDef<MemberRow>[] {
    return useMemo<ColumnDef<MemberRow>[]>(
        () => [
            {
                id: 'select',
                header: ({ table }) => (
                    <Checkbox
                        checked={
                            table.getIsAllRowsSelected() || (table.getIsSomeRowsSelected() && 'indeterminate')
                        }
                        onCheckedChange={(v) => table.toggleAllRowsSelected(!!v)}
                        aria-label="Select all"
                    />
                ),
                cell: ({ row }) => (
                    <Checkbox
                        checked={row.getIsSelected()}
                        onCheckedChange={(v) => row.toggleSelected(!!v)}
                        aria-label="Select row"
                    />
                ),
                enableSorting: false,
                enableHiding: false,
            },
            {
                accessorKey: 'name',
                header: 'Member',
                cell: ({ row }) => {
                    const m = row.original;
                    const nameInner = (
                        <>
                            {m.positionAbbr && <strong className={cn('mr-1', m.positionClass)}>{m.positionAbbr}</strong>}
                            {m.name}
                        </>
                    );
                    const nameStyle = { '--rank-color': m.rankColor ?? undefined } as CSSProperties;
                    return (
                        <div className="flex items-center gap-2">
                            {activityStyle === 'dot' && (
                                <span
                                    aria-hidden="true"
                                    title={`Discord activity: ${m.voice.label}`}
                                    className={cn('size-1.5 shrink-0 rounded-full', DOT_TINT[m.voice.bucket])}
                                />
                            )}
                            {m.directRecruit && (
                                <span title="Direct recruit" className="font-bold text-[#e05cff]">
                                    *
                                </span>
                            )}
                            {m.leave && (
                                <span
                                    title={
                                        m.leave.pending
                                            ? 'Leave of absence — pending approval'
                                            : `On leave${m.leave.reason ? ` (${m.leave.reason})` : ''}${m.leave.until ? ` · back ${m.leave.until}` : ''}`
                                    }
                                    className={cn(
                                        'shrink-0 rounded-sm border px-1 font-mono text-[9px] font-semibold uppercase leading-[1.4] tracking-wide',
                                        m.leave.pending
                                            ? 'border-muted-foreground/30 text-muted-foreground'
                                            : 'border-warning/40 bg-warning/10 text-warning',
                                    )}
                                >
                                    LOA
                                </span>
                            )}
                            {bulkMode ? (
                                <span className="rank-ink" style={nameStyle}>
                                    {nameInner}
                                </span>
                            ) : (
                                <Link
                                    href={m.profileUrl}
                                    className="rank-ink underline-offset-2 hover:underline"
                                    style={nameStyle}
                                    title="View profile"
                                >
                                    {nameInner}
                                </Link>
                            )}
                            {m.isParttimer && (
                                <span
                                    className="text-info"
                                    title={`Part-timer (primary: ${m.primaryDivision ?? 'None'})`}
                                >
                                    <Clock className="size-3" />
                                </span>
                            )}
                        </div>
                    );
                },
                filterFn: (row, _id, value: string) => {
                    const q = value.toLowerCase();
                    const m = row.original;
                    return (
                        m.name.toLowerCase().includes(q) ||
                        (m.rankAbbr ?? '').toLowerCase().includes(q) ||
                        (m.handle?.value ?? '').toLowerCase().includes(q) ||
                        (m.assignment?.label ?? '').toLowerCase().includes(q)
                    );
                },
            },
            {
                id: 'rank',
                accessorFn: (m) => m.rankValue,
                header: 'Rank',
                cell: ({ row }) => <span className="text-muted-foreground">{row.original.rankAbbr}</span>,
            },
            {
                id: 'assignment',
                accessorFn: (m) => m.assignment?.label ?? '',
                header: assignmentLabel,
                cell: ({ row }) =>
                    row.original.assignment ? (
                        <Link href={row.original.assignment.url} className="text-muted-foreground hover:text-foreground">
                            {row.original.assignment.label}
                        </Link>
                    ) : (
                        <span className="text-muted-foreground">—</span>
                    ),
            },
            {
                id: 'joined',
                accessorKey: 'joinDate',
                header: 'Joined',
                cell: ({ row }) => <span className="numeric text-muted-foreground">{row.original.joinDate ?? '—'}</span>,
            },
            {
                id: 'voice',
                accessorFn: (m) => m.voice.iso ?? '',
                header: 'Discord activity',
                cell: ({ row }) => (
                    <span className={row.original.voice.tone} title={row.original.voice.iso ?? undefined}>
                        {row.original.voice.label}
                    </span>
                ),
            },
            {
                id: 'promoted',
                accessorKey: 'lastPromotedAt',
                header: 'Last promoted',
                cell: ({ row }) => (
                    <span className="numeric text-muted-foreground">{row.original.lastPromotedAt ?? 'Never'}</span>
                ),
            },
            {
                id: 'reminder',
                accessorFn: (m) => reminded[m.id] ?? m.reminder.sortKey,
                header: 'Inactivity reminder',
                enableGlobalFilter: false,
                cell: ({ row }) => {
                    const m = row.original;
                    if (!m.canRemind) return <span className="text-muted-foreground">—</span>;
                    const justReminded = reminded[m.id];
                    const done = m.reminder.remindedToday || !!justReminded;
                    return (
                        <Button
                            size="xs"
                            variant={done ? 'outline' : 'default'}
                            disabled={done}
                            title={justReminded ? 'Reminded just now' : m.reminder.human}
                            onClick={async () => {
                                try {
                                    const res = await postJson<{ date: string }>(
                                        `/members/${m.id}/set-activity-reminder`,
                                        {},
                                    );
                                    setReminded((prev) => ({ ...prev, [m.id]: res.date }));
                                    toast.success(`${m.name} marked as reminded`);
                                } catch (e) {
                                    toast.error(e instanceof Error ? e.message : 'Failed');
                                }
                            }}
                        >
                            <Bell />
                            {(justReminded || m.reminder.date) && (
                                <span className="numeric">{justReminded ?? m.reminder.date}</span>
                            )}
                        </Button>
                    );
                },
            },
            {
                id: 'tags',
                accessorFn: (m) => m.tags.map((t) => t.name).join(' '),
                header: 'Tags',
                enableSorting: false,
                filterFn: (row) => {
                    if (selectedTags.size === 0) return true;
                    return row.original.tagIds.some((id) => selectedTags.has(id));
                },
                cell: ({ row }) => (
                    <div className="flex flex-wrap gap-1">
                        {row.original.tags.map((tag) => (
                            <span
                                key={tag.id}
                                className="rounded bg-muted px-1.5 py-0.5 text-[11px] text-muted-foreground"
                            >
                                {tag.name}
                            </span>
                        ))}
                    </div>
                ),
            },
            {
                id: 'handle',
                accessorFn: (m) => m.handle?.value ?? '',
                header: 'Handle',
                cell: ({ row }) => {
                    const h = row.original.handle;
                    if (!h) return <span className="text-destructive">N/A</span>;
                    return h.url ? (
                        <a href={h.url} target="_blank" rel="noreferrer" className="hover:text-foreground">
                            {h.value}
                        </a>
                    ) : (
                        <code className="text-xs">{h.value}</code>
                    );
                },
            },
            {
                id: 'posts',
                accessorKey: 'posts',
                header: 'Posts',
                cell: ({ row }) => <span className="numeric text-muted-foreground">{row.original.posts}</span>,
            },
            {
                id: 'leave',
                accessorFn: (m) => (m.leave ? 1 : 0),
                header: 'LOA',
                enableGlobalFilter: false,
                sortDescFirst: true,
                cell: () => null,
            },
            ...memberFields.map(
                (field): ColumnDef<MemberRow> => ({
                    id: fieldColumnId(field.key),
                    accessorFn: (m) => m.customFields[field.key] ?? '',
                    header: field.label,
                    cell: ({ row }) => {
                        const value = row.original.customFields[field.key];
                        if (!value) return <span className="text-muted-foreground">—</span>;
                        if (field.type !== 'select') {
                            return <span className="text-muted-foreground">{value}</span>;
                        }
                        const colorClass = FIELD_BADGE_COLORS[field.colors[value] ?? 'gray'] ?? FIELD_BADGE_COLORS.gray;
                        return (
                            <span className={cn('rounded px-1.5 py-0.5 text-[11px]', colorClass)}>{value}</span>
                        );
                    },
                    filterFn:
                        field.type === 'select'
                            ? (row) => {
                                  const selected = selectedFieldValues[field.key];
                                  if (!selected || selected.size === 0) return true;
                                  const value = row.original.customFields[field.key];
                                  return value != null && selected.has(value);
                              }
                            : undefined,
                }),
            ),
        ],
        [assignmentLabel, selectedTags, reminded, bulkMode, activityStyle, memberFields, selectedFieldValues],
    );
}

export function columnLabel(id: string, assignmentLabel: string, memberFields: MemberFieldDefinition[]): ReactNode {
    switch (id) {
        case 'promoted':
            return 'Last promoted';
        case 'voice':
            return 'Discord activity';
        case 'reminder':
            return 'Inactivity reminder';
        case 'assignment':
            return assignmentLabel;
        default: {
            const field = memberFields.find((f) => fieldColumnId(f.key) === id);
            return field ? field.label : id;
        }
    }
}
