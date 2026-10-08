import { Head } from '@inertiajs/react';

import { type DiagramLevel, HierarchyDiagram, type Tier } from '@/components/division/hierarchy-diagram';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout';

interface Level {
    depth: number;
    label: string;
    title: string;
    tier: Tier;
    powers: string[];
}

interface Example {
    levels: Array<{ depth: number; label: string; tier: Tier }>;
}

interface Props {
    division: { name: string; slug: string };
    levels: Level[];
    examples: Example[];
}

const TIER_NAME: Record<Tier, string> = {
    platoon: 'Platoon leader powers',
    squad: 'Squad leader powers',
};

const TIER_SWATCH: Record<Tier, string> = {
    platoon: 'bg-primary',
    squad: 'bg-[var(--chart-3)]',
};

const EXAMPLE_NOTES: Record<number, string> = {
    1: 'A single level: its leader gets platoon leader powers.',
    2: 'Two levels: the top level gets platoon leader powers and the bottom level gets squad leader powers.',
    4: 'Four levels: every level above the bottom gets platoon leader powers, and only the bottom level gets squad leader powers.',
};

function TierLegend() {
    return (
        <div className="flex flex-wrap gap-4 text-sm text-muted-foreground">
            {(Object.keys(TIER_NAME) as Tier[]).map((tier) => (
                <span key={tier} className="flex items-center gap-2">
                    <span className={`size-3 rounded-sm ${TIER_SWATCH[tier]}`} />
                    {TIER_NAME[tier]}
                </span>
            ))}
        </div>
    );
}

export default function LeaderPowers({ division, levels, examples }: Props) {
    const diagramLevels: DiagramLevel[] = levels.map((l) => ({ label: l.label, tier: l.tier }));

    return (
        <AppLayout
            width="full"
            header={{
                title: 'Leader powers',
                eyebrow: `${division.name} Division`,
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: division.name, href: `/divisions/${division.slug}` },
                    { label: 'Leader powers' },
                ],
            }}
        >
            <Head title={`Leader powers · ${division.name}`} />

            <div className="mx-auto w-full max-w-5xl space-y-10 px-6 py-8">
                <section className="space-y-4">
                    <div>
                        <h2 className="text-lg font-semibold">What each leader can do</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            A unit leader's powers apply to their own unit and everything beneath it. The filled box in
                            each diagram is the leader; the tinted boxes under it are the units they cover.
                        </p>
                    </div>

                    {levels.length === 0 && (
                        <p className="rounded-md border bg-card p-4 text-sm text-muted-foreground">
                            This division has no unit levels, so there are no unit leaders. Members belong directly to the division.
                        </p>
                    )}

                    <div className="grid gap-4">
                        {levels.map((level) => (
                            <Card key={level.depth}>
                                <CardHeader>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <CardTitle className="text-base">{level.title}</CardTitle>
                                        <Badge variant="outline">{TIER_NAME[level.tier]}</Badge>
                                    </div>
                                    <CardDescription>Leads a {level.label.toLowerCase()}</CardDescription>
                                </CardHeader>
                                <CardContent className="grid gap-6 md:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
                                    <HierarchyDiagram levels={diagramLevels} highlightDepth={level.depth} />
                                    <ul className="space-y-2 text-sm">
                                        {level.powers.map((power) => (
                                            <li key={power} className="flex gap-2">
                                                <span className="mt-1.5 size-1.5 shrink-0 rounded-full bg-primary" />
                                                <span>{power}</span>
                                            </li>
                                        ))}
                                    </ul>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>

                <section className="space-y-4">
                    <div>
                        <h2 className="text-lg font-semibold">How powers follow depth</h2>
                        <p className="mt-1 text-sm text-muted-foreground">
                            Divisions can have no levels, or up to four. Only the bottom level gets squad leader powers; any
                            level above it gets platoon leader powers, and a division with one level treats its leader
                            as a platoon leader.
                        </p>
                    </div>

                    <TierLegend />

                    <div className="grid gap-4 md:grid-cols-2">
                        {examples.map((example) => (
                            <Card key={example.levels.length} className={example.levels.length > 2 ? 'md:col-span-2' : undefined}>
                                <CardHeader>
                                    <CardTitle className="text-base">
                                        {example.levels.length} {example.levels.length === 1 ? 'level' : 'levels'}
                                    </CardTitle>
                                    <CardDescription>{EXAMPLE_NOTES[example.levels.length]}</CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <HierarchyDiagram levels={example.levels} />
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
