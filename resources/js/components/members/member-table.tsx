import { Link } from '@inertiajs/react';
import {
    type ColumnDef,
    type ColumnFiltersState,
    type SortingState,
    type VisibilityState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { ArrowDown, ArrowUp, Bell, ChevronsUpDown, Clock, Columns3, Search } from 'lucide-react';
import { type ReactNode, useEffect, useMemo, useRef, useState } from 'react';
import { toast } from 'sonner';

import { BulkBar } from '@/components/members/bulk-bar';
import type { BulkConfig, MemberListDivision, MemberRow } from '@/components/members/types';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { postJson } from '@/lib/api';
import { cn } from '@/lib/utils';

interface Props {
    rows: MemberRow[];
    division: MemberListDivision;
    assignmentKind: 'platoon' | 'squad';
    bulk: BulkConfig;
    tagFilter: Array<{ id: number; name: string; count: number }>;
    storageKey: string;
}

interface PersistedState {
    sorting: SortingState;
    columnVisibility: VisibilityState;
}

const DEFAULT_HIDDEN: VisibilityState = {
    select: false,
    tags: false,
    reminder: false,
    handle: false,
    posts: false,
};

function loadState(key: string): Partial<PersistedState> {
    try {
        return JSON.parse(localStorage.getItem(key) ?? '{}');
    } catch {
        return {};
    }
}

export function MemberTable({
    rows,
    division,
    assignmentKind,
    bulk,
    tagFilter,
    storageKey,
}: Props) {
    const persisted = useMemo(() => loadState(storageKey), [storageKey]);

    const [sorting, setSorting] = useState<SortingState>(persisted.sorting ?? [{ id: 'name', desc: false }]);
    const [columnVisibility, setColumnVisibility] = useState<VisibilityState>({
        ...DEFAULT_HIDDEN,
        ...(persisted.columnVisibility ?? {}),
        select: false,
    });
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);
    const [globalFilter, setGlobalFilter] = useState('');
    const [rowSelection, setRowSelection] = useState<Record<string, boolean>>({});
    const [bulkMode, setBulkMode] = useState(false);
    const [selectedTags, setSelectedTags] = useState<Set<number>>(new Set());
    const [reminded, setReminded] = useState<Record<number, string>>({});

    useEffect(() => {
        try {
            localStorage.setItem(storageKey, JSON.stringify({ sorting, columnVisibility }));
        } catch {
            /* private mode */
        }
    }, [storageKey, sorting, columnVisibility]);

    const assignmentLabel = assignmentKind === 'squad' ? division.squadLabel : division.platoonLabel;

    const columns = useMemo<ColumnDef<MemberRow>[]>(
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
                    return (
                        <div className="flex items-center gap-2">
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
                            <span style={{ color: m.rankColor ?? undefined }}>
                                {m.positionAbbr && <strong className={cn('mr-1', m.positionClass)}>{m.positionAbbr}</strong>}
                                {m.name}
                            </span>
                            {m.isParttimer && (
                                <span
                                    className="text-info"
                                    title={`Part-timer (primary: ${m.primaryDivision ?? 'None'})`}
                                >
                                    <Clock className="size-3" />
                                </span>
                            )}
                            <Link
                                href={m.profileUrl}
                                className="ml-auto text-muted-foreground hover:text-foreground"
                                title="View profile"
                            >
                                <Search className="size-3.5" />
                            </Link>
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
        ],
        [assignmentLabel, selectedTags, reminded],
    );

    const table = useReactTable({
        data: rows,
        columns,
        state: { sorting, columnVisibility, columnFilters, globalFilter, rowSelection },
        getRowId: (row) => String(row.id),
        enableRowSelection: bulkMode,
        onSortingChange: setSorting,
        onColumnVisibilityChange: setColumnVisibility,
        onColumnFiltersChange: setColumnFilters,
        onGlobalFilterChange: setGlobalFilter,
        onRowSelectionChange: setRowSelection,
        globalFilterFn: (row, _columnId, value) => {
            const q = String(value).toLowerCase();
            const m = row.original as MemberRow;
            return (
                m.name.toLowerCase().includes(q) ||
                (m.rankAbbr ?? '').toLowerCase().includes(q) ||
                (m.handle?.value ?? '').toLowerCase().includes(q) ||
                (m.assignment?.label ?? '').toLowerCase().includes(q)
            );
        },
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getFilteredRowModel: getFilteredRowModel(),
    });

    useEffect(() => {
        table.getColumn('select')?.toggleVisibility(bulkMode);
        if (!bulkMode) setRowSelection({});
    }, [bulkMode, table]);

    useEffect(() => {
        table.getColumn('tags')?.setFilterValue(selectedTags.size > 0 ? [...selectedTags] : undefined);
    }, [selectedTags, table]);

    const selectedRows = table.getFilteredSelectedRowModel().rows.map((r) => r.original);
    const selectedIds = selectedRows.map((r) => r.id);
    const parttimersSelected = selectedRows.some((r) => r.isParttimer);

    // drag-to-select
    const dragging = useRef(false);
    const dragValue = useRef(true);

    const hideableColumns = table.getAllColumns().filter((c) => c.getCanHide());

    return (
        <div className="space-y-3">
            <div className="flex flex-wrap items-center gap-2">
                <div className="relative">
                    <Search className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        value={globalFilter}
                        onChange={(e) => setGlobalFilter(e.target.value)}
                        placeholder="Search players"
                        aria-label="Search players"
                        className="h-8 w-56 pl-8"
                    />
                </div>

                {tagFilter.length > 0 && (
                    <DropdownMenu>
                        <DropdownMenuTrigger asChild>
                            <Button variant="outline" size="sm">
                                Tags
                                {selectedTags.size > 0 && (
                                    <span className="numeric rounded bg-primary/15 px-1 text-primary">
                                        {selectedTags.size}
                                    </span>
                                )}
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="start" className="max-h-72 overflow-y-auto">
                            <DropdownMenuLabel>Filter by tag</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            {tagFilter.map((tag) => (
                                <DropdownMenuCheckboxItem
                                    key={tag.id}
                                    checked={selectedTags.has(tag.id)}
                                    onCheckedChange={() =>
                                        setSelectedTags((prev) => {
                                            const next = new Set(prev);
                                            next.has(tag.id) ? next.delete(tag.id) : next.add(tag.id);
                                            return next;
                                        })
                                    }
                                    onSelect={(e) => e.preventDefault()}
                                >
                                    {tag.name} <span className="ml-1 text-muted-foreground">({tag.count})</span>
                                </DropdownMenuCheckboxItem>
                            ))}
                        </DropdownMenuContent>
                    </DropdownMenu>
                )}

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="outline" size="sm">
                            <Columns3 /> Columns
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="start">
                        {hideableColumns.map((column) => (
                            <DropdownMenuCheckboxItem
                                key={column.id}
                                checked={column.getIsVisible()}
                                onCheckedChange={(v) => column.toggleVisibility(!!v)}
                                onSelect={(e) => e.preventDefault()}
                                className="capitalize"
                            >
                                {columnLabel(column.id, assignmentLabel)}
                            </DropdownMenuCheckboxItem>
                        ))}
                    </DropdownMenuContent>
                </DropdownMenu>

                {bulk.enabled && (
                    <Button
                        variant={bulkMode ? 'default' : 'outline'}
                        size="sm"
                        onClick={() => setBulkMode((v) => !v)}
                    >
                        {bulkMode ? 'Exit bulk mode' : 'Bulk mode'}
                    </Button>
                )}

                <span className="ml-auto text-xs text-muted-foreground">
                    {table.getFilteredRowModel().rows.length} of {rows.length}
                </span>
            </div>

            <div className="overflow-x-auto rounded-md border border-border">
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id} className="bg-card/40">
                                {headerGroup.headers.map((header) => {
                                    const canSort = header.column.getCanSort();
                                    const sorted = header.column.getIsSorted();
                                    return (
                                        <TableHead
                                            key={header.id}
                                            className="text-xs"
                                            aria-sort={
                                                !canSort
                                                    ? undefined
                                                    : sorted === 'asc'
                                                      ? 'ascending'
                                                      : sorted === 'desc'
                                                        ? 'descending'
                                                        : 'none'
                                            }
                                        >
                                            {header.isPlaceholder ? null : canSort ? (
                                                <button
                                                    type="button"
                                                    className="flex items-center gap-1 hover:text-foreground"
                                                    onClick={header.column.getToggleSortingHandler()}
                                                >
                                                    {flexRender(
                                                        header.column.columnDef.header,
                                                        header.getContext(),
                                                    )}
                                                    {sorted === 'asc' ? (
                                                        <ArrowUp className="size-3" />
                                                    ) : sorted === 'desc' ? (
                                                        <ArrowDown className="size-3" />
                                                    ) : (
                                                        <ChevronsUpDown className="size-3 opacity-40" />
                                                    )}
                                                </button>
                                            ) : (
                                                flexRender(header.column.columnDef.header, header.getContext())
                                            )}
                                        </TableHead>
                                    );
                                })}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows.length === 0 ? (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="tron-hatch py-10 text-center text-muted-foreground"
                                >
                                    No members match your filters.
                                </TableCell>
                            </TableRow>
                        ) : (
                            table.getRowModel().rows.map((row) => (
                                <TableRow
                                    key={row.id}
                                    data-state={row.getIsSelected() ? 'selected' : undefined}
                                    className={cn(
                                        row.original.leave && 'bg-warning/[0.05] text-muted-foreground',
                                        bulkMode && 'cursor-pointer select-none',
                                    )}
                                    onMouseDown={(e) => {
                                        if (!bulkMode) return;
                                        if ((e.target as HTMLElement).closest('a, button, input, [role=checkbox]'))
                                            return;
                                        if (e.button !== 0) return;
                                        dragging.current = true;
                                        dragValue.current = !row.getIsSelected();
                                        row.toggleSelected(dragValue.current);
                                        e.preventDefault();
                                    }}
                                    onMouseEnter={() => {
                                        if (dragging.current) row.toggleSelected(dragValue.current);
                                    }}
                                    onMouseUp={() => {
                                        dragging.current = false;
                                    }}
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>

            <div className="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-muted-foreground">
                <span className="flex items-center gap-1.5">
                    <span className="rounded-sm border border-warning/40 bg-warning/10 px-1 font-mono text-[9px] font-semibold uppercase leading-[1.4] tracking-wide text-warning">
                        LOA
                    </span>
                    On leave
                </span>
                <span className="flex items-center gap-1.5">
                    <Clock className="size-3 text-info" /> Part-timer
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="font-bold text-[#e05cff]">*</span> Direct recruit
                </span>
            </div>

            <BulkBar
                selectedIds={selectedIds}
                parttimersSelected={parttimersSelected}
                bulk={bulk}
                division={division}
                onClear={() => setRowSelection({})}
                onReminded={(ids, date) => {
                    setReminded((prev) => ({ ...prev, ...Object.fromEntries(ids.map((id) => [id, date])) }));
                }}
            />
        </div>
    );
}

function columnLabel(id: string, assignmentLabel: string): ReactNode {
    switch (id) {
        case 'promoted':
            return 'Last promoted';
        case 'voice':
            return 'Discord activity';
        case 'reminder':
            return 'Inactivity reminder';
        case 'assignment':
            return assignmentLabel;
        default:
            return id;
    }
}
