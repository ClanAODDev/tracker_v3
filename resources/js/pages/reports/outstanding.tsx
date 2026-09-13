import { Head, Link } from '@inertiajs/react';
import { ListChecks } from 'lucide-react';

import { ClanReportShell } from '@/components/reports/clan-report-shell';
import { StatTiles } from '@/components/reports/stat-tiles';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Division {
    name: string;
    slug: string;
    population: number;
    divisionMax: number;
    outstanding: number;
    inactive: number;
    active: number;
    pctOutstanding: number;
    pctInactive: number;
    inactiveUrl: string;
}

interface Props {
    clanMax: number;
    divisions: Division[];
    totals: {
        population: number;
        outstanding: number;
        inactive: number;
        active: number;
        pctOutstanding: number;
        pctInactive: number;
        pctActive: number;
    };
}

function HealthBar({ pctInactive, pctOutstanding }: { pctInactive: number; pctOutstanding: number }) {
    const active = Math.max(100 - pctInactive, 0);
    const inactiveOnly = Math.max(pctInactive - pctOutstanding, 0);

    return (
        <div className="flex h-1.5 w-full overflow-hidden rounded-full bg-muted">
            <div className="bg-success" style={{ width: `${active}%` }} />
            <div className="bg-warning" style={{ width: `${inactiveOnly}%` }} />
            <div className="bg-destructive" style={{ width: `${pctOutstanding}%` }} />
        </div>
    );
}

export default function Outstanding({ clanMax, divisions, totals }: Props) {
    return (
        <ClanReportShell title="Outstanding Inactives" active="outstanding">
            <Head title="Outstanding Inactives" />

            <StatTiles
                stats={[
                    { value: totals.population, label: 'Total members' },
                    {
                        value: totals.outstanding,
                        label: `Over ${clanMax} days`,
                        tone: 'danger',
                        note: `${totals.pctOutstanding}% of clan`,
                    },
                    {
                        value: totals.inactive,
                        label: 'Over division max',
                        tone: 'warning',
                        note: `${totals.pctInactive}% of clan`,
                    },
                    { value: totals.active, label: 'Active', tone: 'success', note: `${totals.pctActive}% of clan` },
                ]}
            />

            <p className="text-xs text-muted-foreground">
                <span className="text-destructive">Outstanding</span> members exceed the clan maximum of {clanMax} days.{' '}
                <span className="text-warning">Inactive</span> members exceed their division's configured threshold.
                Members on leave are excluded.
            </p>

            <div className="overflow-x-auto rounded-md border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Division</TableHead>
                            <TableHead className="text-right">Pop</TableHead>
                            <TableHead className="text-right">Outstanding</TableHead>
                            <TableHead className="text-right">Inactive</TableHead>
                            <TableHead className="min-w-40">Health</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {divisions.map((division) => (
                            <TableRow key={division.slug}>
                                <TableCell>
                                    <Link href={`/divisions/${division.slug}`} className="hover:text-primary">
                                        {division.name}
                                    </Link>
                                </TableCell>
                                <TableCell className="numeric text-right text-muted-foreground">
                                    {division.population}
                                </TableCell>
                                <TableCell className="numeric text-right">
                                    {division.outstanding > 0 ? (
                                        <span className="text-destructive">
                                            {division.outstanding}{' '}
                                            <span className="text-muted-foreground">({division.pctOutstanding}%)</span>
                                        </span>
                                    ) : (
                                        <span className="text-success">0</span>
                                    )}
                                </TableCell>
                                <TableCell className="numeric text-right">
                                    <span className="inline-flex items-center gap-2">
                                        <span>
                                            {division.inactive}{' '}
                                            <span className="text-muted-foreground">({division.pctInactive}%)</span>
                                        </span>
                                        <Link
                                            href={division.inactiveUrl}
                                            className="text-muted-foreground hover:text-primary"
                                            title="View inactive members"
                                        >
                                            <ListChecks className="size-3.5" />
                                        </Link>
                                    </span>
                                </TableCell>
                                <TableCell>
                                    <HealthBar
                                        pctInactive={division.pctInactive}
                                        pctOutstanding={division.pctOutstanding}
                                    />
                                    <span className="mt-1 block text-xs text-muted-foreground">
                                        {division.divisionMax}d threshold
                                    </span>
                                </TableCell>
                            </TableRow>
                        ))}
                        <TableRow className="border-t-2 border-border font-medium">
                            <TableCell>Clan total</TableCell>
                            <TableCell className="numeric text-right">{totals.population}</TableCell>
                            <TableCell className="numeric text-right text-destructive">
                                {totals.outstanding}{' '}
                                <span className="text-muted-foreground">({totals.pctOutstanding}%)</span>
                            </TableCell>
                            <TableCell className="numeric text-right">
                                {totals.inactive} <span className="text-muted-foreground">({totals.pctInactive}%)</span>
                            </TableCell>
                            <TableCell>
                                <HealthBar
                                    pctInactive={totals.pctInactive}
                                    pctOutstanding={totals.pctOutstanding}
                                />
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div className="flex flex-wrap gap-4 text-xs text-muted-foreground">
                <span className="flex items-center gap-1.5">
                    <span className="size-2.5 rounded-sm bg-success" /> Active
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="size-2.5 rounded-sm bg-warning" /> Inactive (over division max)
                </span>
                <span className="flex items-center gap-1.5">
                    <span className="size-2.5 rounded-sm bg-destructive" /> Outstanding (over {clanMax}d)
                </span>
            </div>
        </ClanReportShell>
    );
}
