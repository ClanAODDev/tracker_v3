import { Head, Link } from '@inertiajs/react';
import { useEffect, useMemo, useState } from 'react';

import { ClanReportShell } from '@/components/reports/clan-report-shell';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';

interface LeadershipRow {
    name: string;
    profileUrl: string;
    position: string;
    positionKind: string;
    positionSort: number;
    lastPromoted: string | null;
    lastTrained: string | null;
}

interface DivisionGroup {
    name: string;
    abbreviation: string;
    logo: string;
    memberCount: number;
    sgtCount: number;
    sgtRatio: string;
    sergeants: LeadershipRow[];
}

interface Props {
    clanLeadership: LeadershipRow[];
    divisions: DivisionGroup[];
}

const POSITION_CLASS: Record<string, string> = {
    admin: 'border-primary/40 text-primary',
    commanding_officer: 'border-chart-2/40 text-chart-2',
    executive_officer: 'border-chart-5/40 text-chart-5',
    platoon_leader: 'border-warning/40 text-warning',
    squad_leader: 'border-success/40 text-success',
    member: 'border-border text-muted-foreground',
};

function PositionBadge({ kind, label }: { kind: string; label: string }) {
    return (
        <span
            className={cn(
                'inline-block rounded border px-1.5 py-0.5 text-[0.65rem] font-semibold uppercase tracking-wide',
                POSITION_CLASS[kind] ?? POSITION_CLASS.member,
            )}
        >
            {label}
        </span>
    );
}

function anchor(id: string) {
    return id.replace(/[^a-z0-9]/gi, '-');
}

interface JumpItem {
    id: string;
    label: string;
    count: number;
    logo?: string;
}

function JumpNav({ items }: { items: JumpItem[] }) {
    const [active, setActive] = useState(items[0]?.id ?? '');

    useEffect(() => {
        const sections = items
            .map((item) => document.getElementById(item.id))
            .filter((el): el is HTMLElement => el !== null);

        const observer = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((entry) => entry.isIntersecting)
                    .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top);
                if (visible[0]) setActive(visible[0].target.id);
            },
            { rootMargin: '-96px 0px -60% 0px' },
        );

        sections.forEach((section) => observer.observe(section));
        return () => observer.disconnect();
    }, [items]);

    function jumpTo(id: string) {
        const el = document.getElementById(id);
        if (!el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setActive(id);
        history.replaceState(null, '', `#${id}`);
    }

    return (
        <div className="sticky top-24 rounded-md border border-border bg-card p-2 text-sm">
            {items.map((item) => (
                <button
                    key={item.id}
                    type="button"
                    onClick={() => jumpTo(item.id)}
                    className={cn(
                        'flex w-full items-center justify-between rounded px-2 py-1.5 text-left transition-colors',
                        active === item.id
                            ? 'bg-primary/10 text-foreground'
                            : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                    )}
                >
                    <span className="flex items-center gap-2">
                        {item.logo && <img src={item.logo} alt="" className="size-4" />}
                        {item.label}
                    </span>
                    <span className="text-xs">{item.count}</span>
                </button>
            ))}
        </div>
    );
}

export default function Leadership({ clanLeadership, divisions }: Props) {
    const jumpItems = useMemo<JumpItem[]>(
        () => [
            { id: 'clan-leadership', label: 'Clan Leadership', count: clanLeadership.length },
            ...divisions.map((division) => ({
                id: anchor(division.abbreviation),
                label: division.name,
                count: division.sergeants.length,
                logo: division.logo,
            })),
        ],
        [clanLeadership.length, divisions],
    );

    return (
        <ClanReportShell title="Leadership Structure" active="leadership">
            <Head title="Leadership Structure" />

            <div className="grid gap-6 lg:grid-cols-[1fr_16rem]">
                <div className="space-y-6">
                    <section id="clan-leadership" className="scroll-mt-24 rounded-md border border-border bg-card">
                        <div className="flex items-center justify-between border-b border-border px-4 py-3">
                            <h2 className="text-sm font-semibold">Clan Leadership</h2>
                            <span className="text-xs text-muted-foreground">{clanLeadership.length} members</span>
                        </div>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Member</TableHead>
                                    <TableHead className="hidden sm:table-cell">Last promoted</TableHead>
                                    <TableHead className="hidden sm:table-cell">Last trained</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {clanLeadership.map((member) => (
                                    <TableRow key={member.profileUrl}>
                                        <TableCell>
                                            <Link href={member.profileUrl} className="hover:text-primary">
                                                {member.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell className="numeric hidden text-muted-foreground sm:table-cell">
                                            {member.lastPromoted ?? '--'}
                                        </TableCell>
                                        <TableCell className="numeric hidden text-muted-foreground sm:table-cell">
                                            {member.lastTrained ?? '--'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </section>

                    {divisions.map((division) => (
                        <section
                            key={division.abbreviation}
                            id={anchor(division.abbreviation)}
                            className="scroll-mt-24 rounded-md border border-border bg-card"
                        >
                            <div className="flex flex-wrap items-center justify-between gap-2 border-b border-border px-4 py-3">
                                <h2 className="flex items-center gap-2 text-sm font-semibold">
                                    <img src={division.logo} alt="" className="size-5" />
                                    {division.name}
                                </h2>
                                <div className="flex gap-1.5 text-[0.65rem] text-muted-foreground">
                                    <span className="rounded border border-border px-1.5 py-0.5" title="Sergeants and Staff Sergeants">
                                        {division.sgtCount} Sgts
                                    </span>
                                    <span className="rounded border border-border px-1.5 py-0.5">
                                        {division.memberCount} Members
                                    </span>
                                    <span className="rounded border border-border px-1.5 py-0.5" title="Sgt to member ratio">
                                        {division.sgtRatio}
                                    </span>
                                </div>
                            </div>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Member</TableHead>
                                        <TableHead>Position</TableHead>
                                        <TableHead className="hidden sm:table-cell">Last promoted</TableHead>
                                        <TableHead className="hidden md:table-cell">Last trained</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {division.sergeants.map((member) => (
                                        <TableRow key={member.profileUrl}>
                                            <TableCell>
                                                <Link href={member.profileUrl} className="hover:text-primary">
                                                    {member.name}
                                                </Link>
                                            </TableCell>
                                            <TableCell>
                                                <PositionBadge kind={member.positionKind} label={member.position} />
                                            </TableCell>
                                            <TableCell className="numeric hidden text-muted-foreground sm:table-cell">
                                                {member.lastPromoted ?? '--'}
                                            </TableCell>
                                            <TableCell className="numeric hidden text-muted-foreground md:table-cell">
                                                {member.lastTrained ?? '--'}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </section>
                    ))}
                </div>

                <nav className="hidden lg:block">
                    <JumpNav items={jumpItems} />
                </nav>
            </div>
        </ClanReportShell>
    );
}
