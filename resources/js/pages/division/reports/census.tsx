import { Head } from '@inertiajs/react';

import { ThemedLineChart } from '@/components/charts';
import { ReportShell } from '@/components/reports/report-shell';
import { StatTiles } from '@/components/reports/stat-tiles';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';

interface CensusProps {
    division: { name: string; slug: string };
    stats: {
        population: number;
        voicePercent: number;
        popChange: number;
        voiceChange: number;
        avgVoice: number;
        peakCount: number;
        peakDate: string | null;
    };
    series: Array<{ date: string; population: number; voice: number }>;
    weeks: Array<{
        date: string;
        population: number;
        change: number;
        voicePercent: number;
        voiceCount: number;
    }>;
}

export default function CensusReport({ division, stats, series, weeks }: CensusProps) {
    const chartData = series.map((row) => ({
        date: row.date,
        Population: row.population,
        'Voice active': row.voice,
    }));

    return (
        <ReportShell divisionName={division.name} divisionSlug={division.slug} title="Census data" active="census">
            <Head title={`Census · ${division.name}`} />

            <StatTiles
                stats={[
                    {
                        value: stats.population,
                        label: 'Population',
                        change: stats.popChange ? { value: stats.popChange, suffix: ' from last week' } : null,
                    },
                    {
                        value: stats.voicePercent,
                        unit: '%',
                        label: 'Voice active',
                        change: stats.voiceChange ? { value: stats.voiceChange, suffix: '% from last week' } : null,
                    },
                    { value: stats.avgVoice, unit: '%', label: '4-week avg voice' },
                    stats.peakCount
                        ? { value: stats.peakCount, label: 'Peak population', note: stats.peakDate ?? undefined }
                        : { value: '—', label: 'Peak population' },
                ]}
            />

            <p className="text-xs text-muted-foreground">
                Census data is collected automatically every Sunday at midnight (server time).
            </p>

            <section>
                <h2 className="mb-3 text-sm font-semibold">Census history</h2>
                <div className="rounded-md border border-border bg-card p-4">
                    <ThemedLineChart
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

            <section>
                <div className="mb-3 flex items-center justify-between">
                    <h2 className="text-sm font-semibold">Weekly data</h2>
                    <span className="text-xs text-muted-foreground">{weeks.length} weeks</span>
                </div>
                <div className="overflow-x-auto rounded-md border border-border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Date</TableHead>
                                <TableHead className="text-right">Population</TableHead>
                                <TableHead className="text-right">Change</TableHead>
                                <TableHead className="text-right">Voice %</TableHead>
                                <TableHead className="text-right">Voice count</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {weeks.map((week, i) => (
                                <TableRow key={i}>
                                    <TableCell>{week.date}</TableCell>
                                    <TableCell className="numeric text-right">{week.population}</TableCell>
                                    <TableCell
                                        className={cn(
                                            'numeric text-right',
                                            week.change > 0
                                                ? 'text-success'
                                                : week.change < 0
                                                  ? 'text-destructive'
                                                  : 'text-muted-foreground',
                                        )}
                                    >
                                        {week.change > 0 ? '+' : ''}
                                        {week.change}
                                    </TableCell>
                                    <TableCell className="numeric text-right">{week.voicePercent}%</TableCell>
                                    <TableCell className="numeric text-right text-muted-foreground">
                                        {week.voiceCount}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            </section>
        </ReportShell>
    );
}
