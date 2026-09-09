import { Head } from '@inertiajs/react';
import { Home } from 'lucide-react';

import { ThemedBarChart } from '@/components/charts';
import { DateRangeFilter } from '@/components/reports/date-range-filter';
import { ReportShell } from '@/components/reports/report-shell';
import { StatTiles } from '@/components/reports/stat-tiles';
import { cn } from '@/lib/utils';

interface TransfersProps {
    division: { name: string; slug: string };
    range: { start: string; end: string };
    stats: { total: number; sourceCount: number; transferredIn: number; startedHere: number };
    sources: Array<{ name: string; count: number; percentage: number; is_original: boolean }>;
}

export default function TransfersReport({ division, range, stats, sources }: TransfersProps) {
    const chartData = sources.filter((s) => !s.is_original).map((s) => ({ name: s.name, count: s.count }));

    return (
        <ReportShell divisionName={division.name} divisionSlug={division.slug} title="Transfer origins" active="transfers">
            <Head title={`Transfers · ${division.name}`} />

            <StatTiles
                stats={[
                    { value: stats.total, label: 'Total members', tone: 'primary' },
                    { value: stats.transferredIn, label: 'Transferred in', tone: 'info' },
                    { value: stats.startedHere, label: 'Started here', tone: 'success' },
                    { value: stats.sourceCount, label: 'Source divisions' },
                ]}
            />

            <DateRangeFilter start={range.start} end={range.end} baseUrl={`/divisions/${division.slug}/transfers`} />

            {sources.length === 0 ? (
                <p className="rounded-md border border-border bg-card p-6 text-center text-sm text-muted-foreground">
                    No approved transfers into {division.name}
                    {range.start || range.end ? ' in this date range' : ''}.
                </p>
            ) : (
                <>
                    {chartData.length > 0 && (
                        <section>
                            <h2 className="mb-3 text-sm font-semibold">Transfers by source division</h2>
                            <div className="rounded-md border border-border bg-card p-4">
                                <ThemedBarChart
                                    data={chartData}
                                    x="name"
                                    series={[{ key: 'count', label: 'Transfers' }]}
                                    height={260}
                                />
                            </div>
                        </section>
                    )}

                    <section>
                        <div className="mb-3 flex items-center justify-between">
                            <h2 className="text-sm font-semibold">Breakdown</h2>
                            <span className="numeric text-xs text-muted-foreground">{stats.total} total</span>
                        </div>
                        <div className="divide-y divide-border overflow-hidden rounded-md border border-border">
                            {sources.map((source, i) => (
                                <div
                                    key={i}
                                    className={cn(
                                        'flex items-center gap-3 p-3 text-sm',
                                        source.is_original && 'text-muted-foreground',
                                    )}
                                >
                                    <span
                                        className={cn(
                                            'flex w-5 justify-center text-xs',
                                            !source.is_original && i < 3 && 'font-semibold text-primary',
                                        )}
                                    >
                                        {source.is_original ? <Home className="size-3.5" /> : i + 1}
                                    </span>
                                    <span className="flex-1">{source.name}</span>
                                    <span className="numeric">
                                        {source.count}{' '}
                                        <span className="text-xs text-muted-foreground">({source.percentage}%)</span>
                                    </span>
                                </div>
                            ))}
                        </div>
                    </section>
                </>
            )}
        </ReportShell>
    );
}
