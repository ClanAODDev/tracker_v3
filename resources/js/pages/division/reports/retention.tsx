import { Head } from '@inertiajs/react';

import { ThemedBarChart } from '@/components/charts';
import { DateRangeFilter } from '@/components/reports/date-range-filter';
import { ReportShell } from '@/components/reports/report-shell';
import { StatTiles } from '@/components/reports/stat-tiles';
import { cn } from '@/lib/utils';

interface RetentionProps {
    division: { name: string; slug: string };
    range: { start: string; end: string };
    stats: { recruits: number; removals: number; netChange: number; retentionRate: number };
    totalRecruitCount: number;
    series: Array<{ month: string; recruits: number; removals: number }>;
    topRecruiters: Array<{ rankName: string; recruits: number; url: string }>;
}

export default function RetentionReport({
    division,
    range,
    stats,
    totalRecruitCount,
    series,
    topRecruiters,
}: RetentionProps) {
    return (
        <ReportShell divisionName={division.name} divisionSlug={division.slug} title="Member retention" active="retention">
            <Head title={`Retention · ${division.name}`} />

            <StatTiles
                stats={[
                    { value: stats.recruits, label: 'Recruited', tone: 'success' },
                    { value: stats.removals, label: 'Removed', tone: 'warning' },
                    {
                        value: `${stats.netChange > 0 ? '+' : ''}${stats.netChange}`,
                        label: 'Net change',
                        tone: stats.netChange >= 0 ? 'info' : 'danger',
                    },
                    { value: stats.retentionRate, unit: '%', label: 'Retention rate' },
                ]}
            />

            <DateRangeFilter start={range.start} end={range.end} baseUrl={`/divisions/${division.slug}/retention`} />

            <section>
                <h2 className="mb-3 text-sm font-semibold">Retention trends</h2>
                <div className="rounded-md border border-border bg-card p-4">
                    <ThemedBarChart
                        data={series}
                        x="month"
                        series={[
                            { key: 'recruits', label: 'Recruited' },
                            { key: 'removals', label: 'Removed' },
                        ]}
                        height={280}
                    />
                </div>
            </section>

            <section>
                <div className="mb-3 flex items-center justify-between">
                    <h2 className="text-sm font-semibold">Top recruiters</h2>
                    <span className="numeric text-xs text-muted-foreground">{totalRecruitCount} total</span>
                </div>
                {topRecruiters.length === 0 ? (
                    <p className="rounded-md border border-border bg-card p-6 text-center text-sm text-muted-foreground">
                        No recruits in this period.
                    </p>
                ) : (
                    <div className="divide-y divide-border overflow-hidden rounded-md border border-border">
                        {topRecruiters.map((recruiter, i) => (
                            <a
                                key={recruiter.url}
                                href={recruiter.url}
                                className="flex items-center gap-3 p-3 text-sm transition-colors hover:bg-accent"
                            >
                                <span
                                    className={cn(
                                        'numeric w-5 text-center text-xs',
                                        i < 3 ? 'font-semibold text-primary' : 'text-muted-foreground',
                                    )}
                                >
                                    {i + 1}
                                </span>
                                <span className="flex-1">{recruiter.rankName}</span>
                                <span className="numeric text-muted-foreground">{recruiter.recruits}</span>
                            </a>
                        ))}
                    </div>
                )}
            </section>
        </ReportShell>
    );
}
