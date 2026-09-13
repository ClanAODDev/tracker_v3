import { Head } from '@inertiajs/react';

import { ClanReportShell } from '@/components/reports/clan-report-shell';
import { StatTiles } from '@/components/reports/stat-tiles';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

interface Row {
    name: string;
    population: number;
    last30: number;
    last60: number;
    last90: number;
    pct30: number;
    pct60: number;
    pct90: number;
}

interface Props {
    divisions: Row[];
    totals: Omit<Row, 'name'>;
}

function Meter({ pct, tone }: { pct: number; tone: string }) {
    return (
        <div className="h-1.5 w-full overflow-hidden rounded-full bg-muted">
            <div className={tone} style={{ width: `${Math.min(pct, 100)}%`, height: '100%' }} />
        </div>
    );
}

export default function Turnover({ divisions, totals }: Props) {
    return (
        <ClanReportShell title="Division Turnover" active="turnover">
            <Head title="Division Turnover" />

            <StatTiles
                stats={[
                    { value: totals.last30, label: 'New in 30 days', tone: 'info', note: `${totals.pct30}% of clan` },
                    { value: totals.last60, label: 'New in 60 days', tone: 'warning', note: `${totals.pct60}% of clan` },
                    { value: totals.last90, label: 'New in 90 days', tone: 'success', note: `${totals.pct90}% of clan` },
                    { value: totals.population, label: 'Clan population' },
                ]}
            />

            <p className="text-xs text-muted-foreground">
                Counts and percentages of members recruited within 30/60/90-day windows. Each column is cumulative — 90
                days includes 60 and 30. Calculated in real time from member join dates.
            </p>

            <div className="overflow-x-auto rounded-md border border-border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Division</TableHead>
                            <TableHead className="text-right">Population</TableHead>
                            <TableHead>30 days</TableHead>
                            <TableHead>60 days</TableHead>
                            <TableHead>90 days</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {divisions.map((row) => (
                            <TableRow key={row.name}>
                                <TableCell>{row.name}</TableCell>
                                <TableCell className="numeric text-right text-muted-foreground">
                                    {row.population}
                                </TableCell>
                                <TableCell className="min-w-32">
                                    <Meter pct={row.pct30} tone="bg-chart-2" />
                                    <span className="mt-1 block text-xs">
                                        {row.last30} <span className="text-muted-foreground">({row.pct30}%)</span>
                                    </span>
                                </TableCell>
                                <TableCell className="min-w-32">
                                    <Meter pct={row.pct60} tone="bg-warning" />
                                    <span className="mt-1 block text-xs">
                                        {row.last60} <span className="text-muted-foreground">({row.pct60}%)</span>
                                    </span>
                                </TableCell>
                                <TableCell className="min-w-32">
                                    <Meter pct={row.pct90} tone="bg-success" />
                                    <span className="mt-1 block text-xs">
                                        {row.last90} <span className="text-muted-foreground">({row.pct90}%)</span>
                                    </span>
                                </TableCell>
                            </TableRow>
                        ))}
                        <TableRow className="border-t-2 border-border font-medium">
                            <TableCell>Clan total</TableCell>
                            <TableCell className="numeric text-right">{totals.population}</TableCell>
                            <TableCell>
                                {totals.last30} <span className="text-muted-foreground">({totals.pct30}%)</span>
                            </TableCell>
                            <TableCell>
                                {totals.last60} <span className="text-muted-foreground">({totals.pct60}%)</span>
                            </TableCell>
                            <TableCell>
                                {totals.last90} <span className="text-muted-foreground">({totals.pct90}%)</span>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </ClanReportShell>
    );
}
