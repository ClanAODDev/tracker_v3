import { Head, Link } from '@inertiajs/react';

import { ThemedAreaChart } from '@/components/charts';
import { CountUp } from '@/components/count-up';
import { ClanReportShell } from '@/components/reports/clan-report-shell';
import { DateRangeFilter } from '@/components/reports/date-range-filter';
import { StatTiles } from '@/components/reports/stat-tiles';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';

interface Milestone {
    total: number;
    date: string;
}

interface Props {
    stats: {
        memberCount: number;
        previousCount: number | null;
        firstCensus: Milestone | null;
        peakCensus: Milestone | null;
    };
    chart: Array<{ date: string; population: number; voice: number }>;
    censusTable: Array<{
        date: string;
        population: number;
        change: number;
        voiceActive: number;
        voicePercent: number;
    }>;
    divisionPopulations: {
        rows: Array<{ name: string; slug: string; population: number; voiceActive: number; voicePercent: number }>;
        totalPopulation: number;
        totalVoiceActive: number;
        totalPercent: number;
    };
    rankDemographic: Array<{ abbreviation: string; count: number; percent: number }>;
    dateRange: { start: string; end: string };
    hasDateFilter: boolean;
}

export default function ClanCensus({
    stats,
    chart,
    censusTable,
    divisionPopulations,
    rankDemographic,
    dateRange,
    hasDateFilter,
}: Props) {
    const chartData = chart.map((row) => ({ date: row.date, Population: row.population, 'Voice active': row.voice }));

    const change =
        stats.previousCount && stats.previousCount > 0
            ? Number((((stats.memberCount - stats.previousCount) / stats.previousCount) * 100).toFixed(1))
            : 0;

    return (
        <ClanReportShell title="Clan Census Data" active="census">
            <Head title="Clan Census Data" />

            <p className="text-xs text-muted-foreground">
                Tracker data is synced with the AOD forums hourly. Census statistics are polled weekly. Division-specific
                populations carry annotations viewable on individual division pages.
            </p>

            <StatTiles
                stats={[
                    {
                        value: stats.memberCount,
                        label: 'Current members',
                        change: change ? { value: change, suffix: '% from last census' } : null,
                    },
                    stats.firstCensus
                        ? {
                              value: stats.firstCensus.total,
                              label: 'First census',
                              note: stats.firstCensus.date,
                          }
                        : { value: '—', label: 'First census' },
                    stats.peakCensus
                        ? {
                              value: stats.peakCensus.total,
                              label: 'Peak census',
                              note: stats.peakCensus.date,
                          }
                        : { value: '—', label: 'Peak census' },
                    {
                        value: divisionPopulations.totalPercent,
                        unit: '%',
                        label: 'Clan voice active',
                        note: `${divisionPopulations.totalVoiceActive.toLocaleString()} members`,
                    },
                ]}
            />

            <DateRangeFilter start={dateRange.start} end={dateRange.end} baseUrl="/clan/census" />

            <div className="grid gap-6 lg:grid-cols-[1fr_18rem]">
                <div className="space-y-6">
                    {chartData.length > 0 && (
                        <section>
                            <h2 className="mb-3 text-sm font-semibold">Census history</h2>
                            <div className="rounded-md border border-border bg-card p-4">
                                <ThemedAreaChart
                                    data={chartData}
                                    x="date"
                                    series={[
                                        { key: 'Population', label: 'Population' },
                                        { key: 'Voice active', label: 'Voice active' },
                                    ]}
                                    height={280}
                                />
                            </div>
                        </section>
                    )}

                    <section>
                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="text-sm font-semibold">Weekly data</h2>
                            <span className="text-xs text-muted-foreground">
                                {censusTable.length} weeks{hasDateFilter ? ' (filtered)' : ''}
                            </span>
                        </div>
                        <div className="overflow-x-auto rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Date</TableHead>
                                        <TableHead className="text-right">Population</TableHead>
                                        <TableHead className="text-right">Change</TableHead>
                                        <TableHead className="text-right">Voice active</TableHead>
                                        <TableHead className="text-right">Voice %</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {censusTable.map((row, i) => (
                                        <TableRow key={i}>
                                            <TableCell>{row.date}</TableCell>
                                            <TableCell className="numeric text-right">
                                                {row.population.toLocaleString()}
                                            </TableCell>
                                            <TableCell
                                                className={cn(
                                                    'numeric text-right',
                                                    row.change > 0
                                                        ? 'text-success'
                                                        : row.change < 0
                                                          ? 'text-destructive'
                                                          : 'text-muted-foreground',
                                                )}
                                            >
                                                {row.change > 0 ? '+' : ''}
                                                {row.change || '—'}
                                            </TableCell>
                                            <TableCell className="numeric text-right text-muted-foreground">
                                                {row.voiceActive.toLocaleString()}
                                            </TableCell>
                                            <TableCell
                                                className={cn(
                                                    'numeric text-right',
                                                    row.voicePercent >= 50
                                                        ? 'text-success'
                                                        : row.voicePercent >= 30
                                                          ? 'text-warning'
                                                          : 'text-destructive',
                                                )}
                                            >
                                                {row.voicePercent}%
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </section>

                    <section>
                        <h2 className="mb-3 text-sm font-semibold">Division populations</h2>
                        <div className="overflow-x-auto rounded-md border border-border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Division</TableHead>
                                        <TableHead className="text-right">Population</TableHead>
                                        <TableHead className="text-right">Weekly voice</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {divisionPopulations.rows.map((division) => (
                                        <TableRow key={division.slug}>
                                            <TableCell>
                                                <Link
                                                    href={`/divisions/${division.slug}/census`}
                                                    className="hover:text-primary"
                                                >
                                                    {division.name}
                                                </Link>
                                            </TableCell>
                                            <TableCell className="numeric text-right">{division.population}</TableCell>
                                            <TableCell className="numeric text-right text-muted-foreground">
                                                {division.voicePercent}%
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                    <TableRow className="border-t-2 border-border font-medium">
                                        <TableCell>Total</TableCell>
                                        <TableCell className="numeric text-right">
                                            <CountUp value={divisionPopulations.totalPopulation} />
                                        </TableCell>
                                        <TableCell className="numeric text-right">
                                            {divisionPopulations.totalPercent}%
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </section>
                </div>

                <section>
                    <h2 className="mb-3 text-sm font-semibold">Rank distribution</h2>
                    <div className="rounded-md border border-border bg-card p-2 text-xs">
                        {rankDemographic.map((rank) => (
                            <div key={rank.abbreviation} className="flex items-center gap-3 px-2 py-2">
                                <span className="numeric w-14 shrink-0 font-semibold">{rank.abbreviation}</span>
                                <div className="h-1.5 flex-1 overflow-hidden rounded-full bg-muted">
                                    <div className="h-full bg-warning" style={{ width: `${rank.percent}%` }} />
                                </div>
                                <span className="numeric w-12 shrink-0 text-right text-muted-foreground">
                                    {rank.count.toLocaleString()}
                                </span>
                                <span className="numeric w-14 shrink-0 text-right text-muted-foreground">
                                    {rank.percent}%
                                </span>
                            </div>
                        ))}
                        <div className="mt-1 flex items-center justify-between border-t border-border px-2 pb-1 pt-2 font-medium">
                            <span>Total</span>
                            <span className="numeric">
                                <CountUp value={stats.memberCount} />
                            </span>
                        </div>
                    </div>
                </section>
            </div>
        </ClanReportShell>
    );
}
