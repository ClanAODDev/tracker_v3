import { Head, router } from '@inertiajs/react';
import { Check, Copy } from 'lucide-react';
import { useState } from 'react';

import { ThemedBarChart } from '@/components/charts';
import { ReportShell } from '@/components/reports/report-shell';
import { Button } from '@/components/ui/button';
import { SimpleSelect } from '@/components/ui/simple-select';

interface PromotionsProps {
    division: { name: string; slug: string };
    periods: Array<{ key: string; label: string }>;
    selectedKey: string | null;
    periodLabel: string;
    chart: Array<{ rank: string; count: number }>;
    groups: Array<{
        rankName: string;
        members: Array<{ name: string; url: string | null; date: string | null }>;
    }>;
    total: number;
    bbCode: string;
}

export default function PromotionsReport({
    division,
    periods,
    selectedKey,
    periodLabel,
    chart,
    groups,
    total,
    bbCode,
}: PromotionsProps) {
    const [copied, setCopied] = useState(false);

    return (
        <ReportShell
            divisionName={division.name}
            divisionSlug={division.slug}
            title="Promotions report"
            active="promotions"
            actions={
                periods.length > 0 ? (
                    <SimpleSelect
                        value={selectedKey ?? periods[0]?.key}
                        onChange={(v) => router.get(`/divisions/${division.slug}/promotions`, { period: v })}
                        options={periods.map((p) => ({ value: p.key, label: p.label }))}
                    />
                ) : undefined
            }
        >
            <Head title={`Promotions · ${division.name}`} />

            {total === 0 ? (
                <p className="rounded-md border border-border bg-card p-6 text-center text-sm text-muted-foreground">
                    No promotions were recorded for {periodLabel}.
                </p>
            ) : (
                <div className="grid gap-6 lg:grid-cols-[1fr_20rem]">
                    <div className="space-y-6">
                        <section>
                            <h2 className="mb-3 text-sm font-semibold">By rank</h2>
                            <div className="rounded-md border border-border bg-card p-4">
                                <ThemedBarChart
                                    data={chart}
                                    x="rank"
                                    series={[{ key: 'count', label: 'Promotions' }]}
                                    height={200}
                                />
                            </div>
                        </section>

                        <section>
                            <div className="mb-3 flex items-center justify-between">
                                <h2 className="text-sm font-semibold">{periodLabel}</h2>
                                <span className="numeric text-xs text-muted-foreground">{total} promotions</span>
                            </div>
                            <div className="space-y-4">
                                {groups.map((group) => (
                                    <div key={group.rankName} className="rounded-md border border-border">
                                        <div className="flex items-center justify-between border-b border-border px-3 py-2">
                                            <span className="text-sm font-medium">{group.rankName}</span>
                                            <span className="numeric text-xs text-muted-foreground">
                                                {group.members.length}
                                            </span>
                                        </div>
                                        <div className="divide-y divide-border">
                                            {group.members.map((member, i) => (
                                                <div
                                                    key={i}
                                                    className="flex items-center justify-between px-3 py-2 text-sm"
                                                >
                                                    {member.url ? (
                                                        <a href={member.url} className="hover:text-primary">
                                                            {member.name}
                                                        </a>
                                                    ) : (
                                                        <span>{member.name}</span>
                                                    )}
                                                    <span className="text-xs text-muted-foreground">{member.date}</span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </section>
                    </div>

                    <section>
                        <h2 className="mb-3 text-sm font-semibold">Share</h2>
                        <div className="rounded-md border border-border bg-card p-3">
                            <pre className="max-h-64 overflow-auto rounded bg-muted p-3 font-mono text-xs whitespace-pre-wrap">
                                {bbCode}
                            </pre>
                            <Button
                                size="sm"
                                className="mt-3 w-full"
                                onClick={() => {
                                    navigator.clipboard.writeText(bbCode);
                                    setCopied(true);
                                }}
                            >
                                {copied ? <Check /> : <Copy />} {copied ? 'Copied' : 'Copy BB-code'}
                            </Button>
                        </div>
                    </section>
                </div>
            )}
        </ReportShell>
    );
}
