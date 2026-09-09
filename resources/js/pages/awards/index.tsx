import { Head, router } from '@inertiajs/react';
import { Archive, Globe, Hand, Medal, Trophy } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useMemo, useState } from 'react';

import { AwardCard, TieredCard, type AwardSummary, type TieredGroupSummary } from '@/components/awards/award-card';
import { CountUp } from '@/components/count-up';
import { TronIdPlate, TronScanline } from '@/components/tron/flourishes';
import { SimpleSelect } from '@/components/ui/simple-select';
import AppLayout from '@/layouts/AppLayout';
import { cn } from '@/lib/utils';

interface Rarity {
    key: string;
    label: string;
    min: number;
    max: number | null;
}

interface Section {
    name: string;
    logo: string | null;
    awards: AwardSummary[];
    tiered?: TieredGroupSummary[];
}

interface AwardsIndexProps {
    divisionSlug: string | null;
    totals: { awards: number; recipients: number; requestable: number };
    rarityBreakdown: Record<string, number>;
    rarities: Rarity[];
    divisions: Array<{ name: string; slug: string; active: boolean }>;
    clan: { awards: AwardSummary[]; tiered: TieredGroupSummary[] };
    divisionSections: Section[];
    legacySections: Section[];
}

function rarityRange(r: Rarity) {
    if (r.max === null) return `${r.min}+`;
    if (r.min === r.max) return `${r.min}`;
    return `${r.min}–${r.max}`;
}

export default function AwardsIndex({
    divisionSlug,
    totals,
    rarityBreakdown,
    rarities,
    divisions,
    clan,
    divisionSections,
    legacySections,
}: AwardsIndexProps) {
    const [hidden, setHidden] = useState<Set<string>>(new Set());
    const [sort, setSort] = useState<'default' | 'rarity-desc' | 'rarity-asc'>('default');

    const rarityRank = useMemo(() => {
        const order = rarities.map((r) => r.key);
        return (rarity: string) => order.indexOf(rarity);
    }, [rarities]);

    function visible(awards: AwardSummary[]) {
        const filtered = awards.filter((a) => !hidden.has(a.rarity));
        if (sort === 'default') return filtered;
        return [...filtered].sort((a, b) =>
            sort === 'rarity-desc'
                ? rarityRank(b.rarity) - rarityRank(a.rarity)
                : rarityRank(a.rarity) - rarityRank(b.rarity),
        );
    }

    function toggleRarity(key: string) {
        setHidden((prev) => {
            const next = new Set(prev);
            next.has(key) ? next.delete(key) : next.add(key);
            return next;
        });
    }

    const showClan = !divisionSlug && (clan.awards.length > 0 || clan.tiered.length > 0);

    return (
        <AppLayout
            header={{
                title: 'Achievements',
                breadcrumbs: [{ label: 'Clan Information' }, { label: 'Achievements' }],
            }}
        >
            <Head title="Achievements" />

            <div className="space-y-8">
                <div className="tron-corners relative rounded-md border border-border bg-card">
                    <TronScanline />
                    <TronIdPlate label="AWARDS" className="absolute -top-2 left-3" />

                    <div className="flex flex-col divide-y divide-border sm:flex-row sm:divide-x sm:divide-y-0">
                        <HeroStat icon={Trophy} value={totals.awards} label="Total awards" />
                        <HeroStat icon={Medal} value={totals.recipients} label="Times awarded" tone="text-warning" />
                        <HeroStat icon={Hand} value={totals.requestable} label="Requestable" tone="text-success" />
                    </div>

                    <RarityBar breakdown={rarityBreakdown} rarities={rarities} total={totals.awards} />
                </div>

                <div className="flex flex-wrap items-center gap-4">
                    <div className="flex flex-wrap gap-1.5">
                        {rarities.map((r) => (
                            <button
                                key={r.key}
                                onClick={() => toggleRarity(r.key)}
                                className={cn(
                                    'flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs transition-opacity',
                                    hidden.has(r.key) ? 'border-border opacity-40' : 'border-border',
                                )}
                            >
                                <span
                                    className="size-2 rounded-full"
                                    style={{ background: `var(--rarity-${r.key})` }}
                                />
                                {r.label}
                                <span className="text-muted-foreground">{rarityRange(r)}</span>
                            </button>
                        ))}
                    </div>

                    <SimpleSelect
                        value={divisionSlug ?? '__all'}
                        onChange={(v) => router.get('/clan/awards', v === '__all' ? {} : { division: v })}
                        options={[
                            { value: '__all', label: 'All divisions' },
                            ...divisions.map((d) => ({
                                value: d.slug,
                                label: `${d.name}${!d.active ? ' (Legacy)' : ''}`,
                            })),
                        ]}
                    />

                    <SimpleSelect
                        value={sort}
                        onChange={(v) => setSort(v as typeof sort)}
                        options={[
                            { value: 'default', label: 'Default order' },
                            { value: 'rarity-desc', label: 'Rarity: highest first' },
                            { value: 'rarity-asc', label: 'Rarity: lowest first' },
                        ]}
                    />
                </div>

                {showClan && (
                    <AwardSection icon={<Globe className="size-4" />} name="Clan-wide awards" count={clan.awards.length + clan.tiered.length}>
                        {clan.tiered.map((g) => (
                            <TieredCard key={g.slug} group={g} />
                        ))}
                        {visible(clan.awards).map((a) => (
                            <AwardCard key={a.id} award={a} />
                        ))}
                    </AwardSection>
                )}

                {divisionSections.map((section) => (
                    <AwardSection
                        key={section.name}
                        logo={section.logo}
                        name={section.name}
                        count={section.awards.length + (section.tiered?.length ?? 0)}
                    >
                        {section.tiered?.map((g) => (
                            <TieredCard key={g.slug} group={g} />
                        ))}
                        {visible(section.awards).map((a) => (
                            <AwardCard key={a.id} award={a} />
                        ))}
                    </AwardSection>
                ))}

                {!divisionSlug && legacySections.length > 0 && (
                    <div className="space-y-4">
                        <div className="flex items-center gap-2 border-b border-border pb-2">
                            <Archive className="size-4 text-muted-foreground" />
                            <span className="text-sm font-semibold">Legacy awards</span>
                        </div>
                        <p className="text-xs text-muted-foreground">
                            Awards from divisions that are no longer active. No longer requestable but remain on member
                            profiles.
                        </p>
                        {legacySections.map((section) => (
                            <div key={section.name} className="space-y-2">
                                <div className="flex items-center gap-2 text-xs text-muted-foreground">
                                    {section.logo && (
                                        <img src={section.logo} alt="" className="size-3.5 opacity-50" />
                                    )}
                                    {section.name}
                                </div>
                                <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-6">
                                    {visible(section.awards).map((a) => (
                                        <AwardCard key={a.id} award={a} />
                                    ))}
                                </div>
                            </div>
                        ))}
                    </div>
                )}

                {!showClan && divisionSections.length === 0 && legacySections.length === 0 && (
                    <p className="text-center text-sm text-muted-foreground">There are currently no available awards.</p>
                )}
            </div>
        </AppLayout>
    );
}

function HeroStat({
    icon: Icon,
    value,
    label,
    tone,
}: {
    icon: LucideIcon;
    value: number;
    label: string;
    tone?: string;
}) {
    return (
        <div className="flex-1 px-5 py-4">
            <div className="flex items-center gap-1.5 text-muted-foreground">
                <Icon className="size-3.5" />
                <span className="text-[11px] font-medium uppercase tracking-wider">{label}</span>
            </div>
            <span className={cn('numeric mt-1.5 block text-2xl font-semibold', tone)}>
                <CountUp value={value} />
            </span>
        </div>
    );
}

function RarityBar({
    breakdown,
    rarities,
    total,
}: {
    breakdown: Record<string, number>;
    rarities: Rarity[];
    total: number;
}) {
    const segments = rarities
        .filter((r) => r.key !== 'unclaimed' && (breakdown[r.key] ?? 0) > 0)
        .map((r) => ({ key: r.key, label: r.label, count: breakdown[r.key] }));

    if (segments.length === 0 || total === 0) return null;

    return (
        <div className="border-t border-border px-5 py-3">
            <div className="flex h-1.5 gap-0.5 overflow-hidden rounded-full">
                {segments.map((s) => (
                    <span
                        key={s.key}
                        className="block first:rounded-l-full last:rounded-r-full"
                        style={{ flexGrow: s.count, background: `var(--rarity-${s.key})` }}
                        title={`${s.label}: ${s.count}`}
                    />
                ))}
            </div>
            <div className="mt-2 flex flex-wrap gap-x-4 gap-y-1">
                {segments.map((s) => (
                    <span key={s.key} className="inline-flex items-center gap-1.5 text-[11px] text-muted-foreground">
                        <span className="size-2 rounded-full" style={{ background: `var(--rarity-${s.key})` }} />
                        {s.label}
                        <span className="numeric text-foreground">{s.count}</span>
                    </span>
                ))}
            </div>
        </div>
    );
}

function AwardSection({
    icon,
    logo,
    name,
    count,
    children,
}: {
    icon?: React.ReactNode;
    logo?: string | null;
    name: string;
    count: number;
    children: React.ReactNode;
}) {
    return (
        <section>
            <div className="mb-3 flex items-center gap-2 border-b border-border pb-2">
                {logo ? <img src={logo} alt="" className="size-4" /> : icon}
                <span className="text-sm font-semibold">{name}</span>
                <span className="numeric text-xs text-muted-foreground">{count}</span>
            </div>
            <div className="grid grid-cols-3 gap-2 sm:grid-cols-4 lg:grid-cols-6">{children}</div>
        </section>
    );
}
