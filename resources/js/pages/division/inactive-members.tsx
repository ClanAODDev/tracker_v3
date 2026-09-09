import { Head, Link, router } from '@inertiajs/react';
import { Bell, Flag, History, Mail, Search, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { SimpleSelect } from '@/components/ui/simple-select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { postJson } from '@/lib/api';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface InactiveRow {
    id: number;
    name: string;
    rankAbbr: string | null;
    profileUrl: string;
    voice: { label: string; iso: string | null };
    reminder: { date: string | null; remindedToday: boolean; human: string };
    status: string;
    unit: string;
    severity: 'severe' | 'warning' | 'normal';
    forumPmUrl: string;
    flagUrl: string;
    unflagUrl: string | null;
    removeUrl: string | null;
    canRemind: boolean;
    canFlag: boolean;
}

interface Props {
    division: { name: string; slug: string; platoonLabel: string; inactivityDays: number };
    stats: { total: number; flagged: number; severe: number };
    activePlatoon: number | null;
    platoons: Array<{ id: number; name: string; count: number }>;
    inactive: InactiveRow[];
    flagged: InactiveRow[];
    activityLog: Array<{ icon: string; user: string; verb: string; subject: string; when: string }>;
    can: { remind: boolean; flag: boolean };
    bulk: { pm: string; reminder: string; flag: string; unflag: string };
}

const SEVERITY_ROW: Record<string, string> = {
    severe: 'border-l-2 border-l-destructive',
    warning: 'border-l-2 border-l-warning',
    normal: '',
};

export default function InactiveMembers({
    division,
    stats,
    activePlatoon,
    platoons,
    inactive,
    flagged,
    activityLog,
    can,
    bulk,
}: Props) {
    const [query, setQuery] = useState('');
    const [selected, setSelected] = useState<Set<number>>(new Set());
    const [tab, setTab] = useState<'inactive' | 'flagged'>(
        typeof window !== 'undefined' && window.location.hash === '#flagged' ? 'flagged' : 'inactive',
    );

    const rows = tab === 'inactive' ? inactive : flagged;
    const filtered = useMemo(() => {
        const q = query.toLowerCase();
        return q ? rows.filter((r) => r.name.toLowerCase().includes(q) || r.unit.toLowerCase().includes(q)) : rows;
    }, [rows, query]);

    function toggleTab(next: 'inactive' | 'flagged') {
        setTab(next);
        setSelected(new Set());
        history.replaceState(null, '', `#${next}`);
    }

    function toggle(id: number) {
        setSelected((prev) => {
            const next = new Set(prev);
            next.has(id) ? next.delete(id) : next.add(id);
            return next;
        });
    }

    async function bulkAction(url: string, verb: string) {
        try {
            const res = await postJson<{ message: string }>(url, { member_ids: [...selected] });
            toast.success(res.message);
            router.reload();
        } catch (e) {
            toast.error(e instanceof Error ? e.message : `Failed to ${verb}`);
        }
    }

    const selectedIds = [...selected];

    return (
        <AppLayout
            header={{
                eyebrow: 'Division',
                title: `${division.name} — inactive members`,
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: division.name, href: `/divisions/${division.slug}` },
                    { label: 'Inactive members' },
                ],
            }}
        >
            <Head title={`${division.name} inactive members`} />

            <div className="space-y-6">
                <div className="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <StatTile label="Inactive" value={stats.total} />
                    <StatTile label="Flagged" value={stats.flagged} tone="text-warning" />
                    <StatTile label="Severe (2× threshold)" value={stats.severe} tone="text-destructive" />
                    <StatTile label="Threshold" value={`${division.inactivityDays}d`} tone="text-info" />
                </div>

                <div className="flex flex-wrap items-center gap-2">
                    <SimpleSelect
                        value={activePlatoon ? String(activePlatoon) : '__all'}
                        onChange={(v) =>
                            router.get(
                                `/divisions/${division.slug}/inactive-members${v === '__all' ? '' : `/${v}`}`,
                            )
                        }
                        options={[
                            { value: '__all', label: `All ${division.platoonLabel.toLowerCase()}s` },
                            ...platoons.map((p) => ({
                                value: String(p.id),
                                label: p.count > 0 ? `${p.name} (${p.count})` : p.name,
                            })),
                        ]}
                    />
                    <div className="relative">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Search members"
                            className="h-8 w-56 pl-8"
                        />
                    </div>
                </div>

                <Tabs value={tab} onValueChange={(v) => toggleTab(v as 'inactive' | 'flagged')}>
                    <TabsList>
                        <TabsTrigger value="inactive">Discord inactive ({inactive.length})</TabsTrigger>
                        <TabsTrigger value="flagged">Flagged ({flagged.length})</TabsTrigger>
                    </TabsList>

                    <TabsContent value={tab} className="mt-4">
                        {filtered.length === 0 ? (
                            <p className="rounded-md border border-border py-10 text-center text-sm text-muted-foreground">
                                {tab === 'inactive'
                                    ? 'No inactive members match the selected filter.'
                                    : 'No members are flagged for removal.'}
                            </p>
                        ) : (
                            <div className="overflow-x-auto rounded-md border border-border">
                                <Table>
                                    <TableHeader>
                                        <TableRow className="bg-card/40 text-xs">
                                            {can.remind && <TableHead className="w-8" />}
                                            <TableHead>Member</TableHead>
                                            <TableHead>Last voice activity</TableHead>
                                            {can.remind && <TableHead>Reminded</TableHead>}
                                            {tab === 'inactive' && <TableHead>Status</TableHead>}
                                            <TableHead>{division.platoonLabel} / Squad</TableHead>
                                            <TableHead className="text-right">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {filtered.map((row) => (
                                            <TableRow key={row.id} className={SEVERITY_ROW[row.severity]}>
                                                {can.remind && (
                                                    <TableCell>
                                                        <Checkbox
                                                            checked={selected.has(row.id)}
                                                            onCheckedChange={() => toggle(row.id)}
                                                        />
                                                    </TableCell>
                                                )}
                                                <TableCell>
                                                    <Link href={row.profileUrl} className="hover:text-foreground">
                                                        {row.name}{' '}
                                                        <span className="text-xs text-muted-foreground">
                                                            {row.rankAbbr}
                                                        </span>
                                                    </Link>
                                                </TableCell>
                                                <TableCell title={row.voice.iso ?? undefined}>
                                                    {row.voice.label}
                                                </TableCell>
                                                {can.remind && (
                                                    <TableCell>
                                                        <ReminderButton row={row} />
                                                    </TableCell>
                                                )}
                                                {tab === 'inactive' && (
                                                    <TableCell className="text-muted-foreground">{row.status}</TableCell>
                                                )}
                                                <TableCell className="text-muted-foreground">{row.unit}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-1.5">
                                                        {tab === 'inactive' ? (
                                                            <>
                                                                {row.canRemind && (
                                                                    <Button size="icon-sm" variant="outline" asChild>
                                                                        <a
                                                                            href={row.forumPmUrl}
                                                                            target="_blank"
                                                                            rel="noreferrer"
                                                                            title="Send forum PM"
                                                                        >
                                                                            <Mail />
                                                                        </a>
                                                                    </Button>
                                                                )}
                                                                {row.canFlag && (
                                                                    <Button
                                                                        size="sm"
                                                                        variant="outline"
                                                                        className="text-warning"
                                                                        onClick={() => router.get(row.flagUrl)}
                                                                    >
                                                                        <Flag /> Flag
                                                                    </Button>
                                                                )}
                                                            </>
                                                        ) : (
                                                            <>
                                                                {row.unflagUrl && (
                                                                    <Button
                                                                        size="sm"
                                                                        variant="outline"
                                                                        className="text-warning"
                                                                        onClick={() => router.get(row.unflagUrl!)}
                                                                    >
                                                                        <Flag /> Unflag
                                                                    </Button>
                                                                )}
                                                                {row.removeUrl && (
                                                                    <Button
                                                                        size="sm"
                                                                        variant="destructive"
                                                                        onClick={() => {
                                                                            if (
                                                                                confirm(
                                                                                    `Remove ${row.name} from AOD?`,
                                                                                )
                                                                            )
                                                                                router.delete(row.removeUrl!, {
                                                                                    data: {
                                                                                        removal_reason:
                                                                                            'Member removed for inactivity',
                                                                                    },
                                                                                });
                                                                        }}
                                                                    >
                                                                        <Trash2 /> Remove
                                                                    </Button>
                                                                )}
                                                            </>
                                                        )}
                                                    </div>
                                                </TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </TabsContent>
                </Tabs>

                {activityLog.length > 0 && (
                    <div className="rounded-md border border-border">
                        <div className="flex items-center gap-2 border-b border-border px-4 py-2.5 text-sm font-semibold">
                            <History className="size-4" /> Recent activity
                        </div>
                        <ul className="divide-y divide-border">
                            {activityLog.map((entry, i) => (
                                <li key={i} className="flex items-center gap-2 px-4 py-2 text-sm">
                                    <span className="font-medium">{entry.user}</span>
                                    <span className="text-muted-foreground">{entry.verb}</span>
                                    <span className="font-medium">{entry.subject}</span>
                                    <span className="ml-auto text-xs text-muted-foreground">{entry.when}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>

            {selectedIds.length > 0 && (
                <div className="fixed inset-x-0 bottom-4 z-40 flex justify-center px-4">
                    <div className="flex flex-wrap items-center gap-2 rounded-md border border-border bg-popover px-4 py-2.5 shadow-lg">
                        <span className="text-sm font-medium">
                            {selectedIds.length} member{selectedIds.length === 1 ? '' : 's'} selected
                        </span>
                        {tab === 'inactive' ? (
                            <>
                                {can.remind && (
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() =>
                                            router.post(bulk.pm, { 'pm-member-data': selectedIds.join(',') })
                                        }
                                    >
                                        <Mail /> Send PM
                                    </Button>
                                )}
                                {can.remind && (
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() => bulkAction(bulk.reminder, 'remind')}
                                    >
                                        <Bell /> Reminder
                                    </Button>
                                )}
                                {can.flag && (
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        className="text-warning"
                                        onClick={() => bulkAction(bulk.flag, 'flag')}
                                    >
                                        <Flag /> Flag
                                    </Button>
                                )}
                            </>
                        ) : (
                            can.flag && (
                                <Button
                                    size="sm"
                                    variant="outline"
                                    className="text-warning"
                                    onClick={() => bulkAction(bulk.unflag, 'unflag')}
                                >
                                    <Flag /> Unflag
                                </Button>
                            )
                        )}
                        <Button size="sm" variant="ghost" onClick={() => setSelected(new Set())}>
                            Clear
                        </Button>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}

function StatTile({ label, value, tone }: { label: string; value: number | string; tone?: string }) {
    return (
        <div className="rounded-md border border-border bg-card p-4">
            <p className={cn('numeric text-2xl font-semibold', tone)}>{value}</p>
            <p className="text-xs text-muted-foreground">{label}</p>
        </div>
    );
}

function ReminderButton({ row }: { row: InactiveRow }) {
    const [date, setDate] = useState(row.reminder.date);
    const [done, setDone] = useState(row.reminder.remindedToday);

    return (
        <Button
            size="xs"
            variant={done ? 'outline' : 'default'}
            disabled={done}
            title={row.reminder.human}
            onClick={async () => {
                try {
                    const res = await postJson<{ date: string }>(`/members/${row.id}/set-activity-reminder`, {});
                    setDate(res.date);
                    setDone(true);
                    toast.success(`${row.name} marked as reminded`);
                } catch (e) {
                    toast.error(e instanceof Error ? e.message : 'Failed');
                }
            }}
        >
            <Bell />
            {date && <span className="numeric">{date}</span>}
        </Button>
    );
}
