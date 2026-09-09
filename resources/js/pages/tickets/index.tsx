import { Head, Link, router } from '@inertiajs/react';
import { Inbox, LifeBuoy, Plus, Search, X } from 'lucide-react';
import { useMemo, useState } from 'react';

import { StatusBadge, truncate, type TicketSummary, type TicketType } from '@/components/tickets/shared';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { SimpleSelect } from '@/components/ui/simple-select';
import { relativeDate } from '@/lib/format';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface Props {
    view: 'user' | 'assigned' | 'all';
    canWorkTickets: boolean;
    currentUserId: number;
    ticketTypes: TicketType[];
    tickets: TicketSummary[];
}

type Sort = 'created_desc' | 'created_asc' | 'updated_desc';

export default function TicketsIndex({ view, canWorkTickets, currentUserId, ticketTypes, tickets }: Props) {
    const [search, setSearch] = useState('');
    const [state, setState] = useState<string>('__all');
    const [type, setType] = useState<string>('__all');
    const [hideResolved, setHideResolved] = useState(true);
    const [sort, setSort] = useState<Sort>('created_desc');

    const rows = useMemo(() => {
        let list = view === 'assigned' ? tickets.filter((t) => t.owner?.id === currentUserId) : tickets;

        if (state !== '__all') list = list.filter((t) => t.state === state);
        else if (hideResolved) list = list.filter((t) => t.state !== 'resolved' && t.state !== 'rejected');

        if (type !== '__all') list = list.filter((t) => String(t.type?.id) === type);

        if (search) {
            const q = search.toLowerCase();
            list = list.filter(
                (t) =>
                    t.description.toLowerCase().includes(q) ||
                    t.type?.name.toLowerCase().includes(q) ||
                    (view !== 'user' && t.caller?.name.toLowerCase().includes(q)),
            );
        }

        const [key, dir] = sort.split('_') as ['created' | 'updated', 'asc' | 'desc'];
        const field = key === 'updated' ? 'updated_at' : 'created_at';
        return [...list].sort((a, b) => {
            const cmp = a[field] < b[field] ? -1 : a[field] > b[field] ? 1 : 0;
            return dir === 'desc' ? -cmp : cmp;
        });
    }, [tickets, view, currentUserId, state, type, hideResolved, search, sort]);

    function switchView(next: 'user' | 'assigned' | 'all') {
        router.get('/help/tickets', next === 'user' ? {} : { view: next }, { preserveState: false });
    }

    return (
        <AppLayout
            header={{
                eyebrow: 'Help center',
                title: 'Support requests',
                breadcrumbs: [{ label: 'Help center' }],
                actions:
                    view === 'user' ? (
                        <Button size="sm" asChild>
                            <Link href="/help/tickets/create">
                                <Plus /> New ticket
                            </Link>
                        </Button>
                    ) : undefined,
            }}
        >
            <Head title="Support requests" />

            <div className="space-y-5">
                {canWorkTickets && (
                    <div className="flex gap-1 rounded-md border border-border p-1 text-sm">
                        {(
                            [
                                ['user', 'My requests'],
                                ['assigned', 'Assigned to me'],
                                ['all', 'All tickets'],
                            ] as const
                        ).map(([key, label]) => (
                            <button
                                key={key}
                                onClick={() => switchView(key)}
                                className={cn(
                                    'flex-1 rounded px-3 py-1.5 transition-colors',
                                    view === key
                                        ? 'bg-primary/15 font-medium text-foreground'
                                        : 'text-muted-foreground hover:text-foreground',
                                )}
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                )}

                <div className="flex flex-wrap items-center gap-2">
                    <div className="relative">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="Search tickets"
                            className="h-8 w-56 pl-8 pr-8"
                        />
                        {search && (
                            <button
                                onClick={() => setSearch('')}
                                className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <X className="size-4" />
                            </button>
                        )}
                    </div>
                    <SimpleSelect
                        value={state}
                        onChange={setState}
                        options={[
                            { value: '__all', label: 'All states' },
                            { value: 'new', label: 'Open' },
                            { value: 'assigned', label: 'In progress' },
                            { value: 'resolved', label: 'Resolved' },
                            { value: 'rejected', label: 'Rejected' },
                        ]}
                    />
                    {ticketTypes.length > 0 && (
                        <SimpleSelect
                            value={type}
                            onChange={setType}
                            options={[
                                { value: '__all', label: 'All types' },
                                ...ticketTypes.map((t) => ({ value: String(t.id), label: t.name })),
                            ]}
                        />
                    )}
                    <Button
                        variant={hideResolved && state === '__all' ? 'default' : 'outline'}
                        size="sm"
                        disabled={state !== '__all'}
                        onClick={() => setHideResolved((v) => !v)}
                    >
                        Active only
                    </Button>
                    <SimpleSelect
                        value={sort}
                        onChange={(v) => setSort(v as Sort)}
                        options={[
                            { value: 'created_desc', label: 'Newest' },
                            { value: 'created_asc', label: 'Oldest' },
                            { value: 'updated_desc', label: 'Last updated' },
                        ]}
                    />
                    <span className="ml-auto text-xs text-muted-foreground">
                        {rows.length} of {tickets.length}
                    </span>
                </div>

                {rows.length === 0 ? (
                    <div className="rounded-md border border-border py-12 text-center">
                        <Inbox className="mx-auto size-8 text-muted-foreground" />
                        <p className="mt-2 text-sm text-muted-foreground">
                            {tickets.length === 0 && view === 'user'
                                ? "You haven't submitted any support requests yet."
                                : 'No tickets match your filters.'}
                        </p>
                        {tickets.length === 0 && view === 'user' && (
                            <Button size="sm" className="mt-3" asChild>
                                <Link href="/help/tickets/create">
                                    <Plus /> Create your first ticket
                                </Link>
                            </Button>
                        )}
                    </div>
                ) : (
                    <div className="space-y-2">
                        {rows.map((ticket) => (
                            <Link
                                key={ticket.id}
                                href={`/help/tickets/${ticket.id}`}
                                className="block rounded-md border border-border bg-card p-3 transition-colors hover:border-primary/30"
                            >
                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                    <span className="numeric font-medium text-foreground">#{ticket.id}</span>
                                    <span className="rounded bg-muted px-1.5 py-0.5">
                                        {ticket.type?.name ?? 'Unknown'}
                                    </span>
                                    <StatusBadge state={ticket.state} className="ml-auto" />
                                </div>
                                <p className="mt-1.5 text-sm">{truncate(ticket.description)}</p>
                                <div className="mt-2 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                    {view !== 'user' && ticket.caller && (
                                        <span className="flex items-center gap-1.5">
                                            <Avatar person={ticket.caller} /> {ticket.caller.name}
                                        </span>
                                    )}
                                    {ticket.owner && (
                                        <span className="flex items-center gap-1.5">
                                            <Avatar person={ticket.owner} /> {ticket.owner.name}
                                        </span>
                                    )}
                                    <span className="ml-auto">{relativeDate(ticket.created_at)}</span>
                                </div>
                            </Link>
                        ))}
                    </div>
                )}

                {tickets.length === 0 && view !== 'user' && (
                    <p className="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                        <LifeBuoy className="size-4" /> No workable tickets right now.
                    </p>
                )}
            </div>
        </AppLayout>
    );
}

function Avatar({ person: r }: { person: { name: string; avatar: string | null } }) {
    return r.avatar ? (
        <img src={r.avatar} alt="" className="size-4 rounded-full" />
    ) : (
        <span className="grid size-4 place-items-center rounded-full bg-muted text-[8px]">
            {r.name.charAt(0).toUpperCase()}
        </span>
    );
}
