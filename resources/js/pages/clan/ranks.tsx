import { Head } from '@inertiajs/react';
import { Info } from 'lucide-react';
import { Fragment, type CSSProperties } from 'react';

import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface RankEntry {
    abbr: string;
    name: string;
    duties: string;
    tier: string;
    color: string;
}

interface RankSection {
    label: string;
    ranks: RankEntry[];
}

interface RanksProps {
    sections: RankSection[];
}

const ROLES = [
    {
        abbr: 'CO',
        label: 'Commanding Officer',
        body: "Awarded to members accepted to lead a division. COs run their division as they see fit within AOD's Code of Conduct and policies. This title carries no extra authority outside of the officer's own division.",
        minimum: 'Sergeant',
    },
    {
        abbr: 'XO',
        label: 'Executive Officer',
        body: "Nominated by the division CO and approved by clan leadership to assist in leading a division. XOs support the CO in all aspects of division leadership and take command in the CO's absence.",
        minimum: 'Corporal',
    },
];

export default function Ranks({ sections }: RanksProps) {
    let rowIndex = 0;

    return (
        <AppLayout
            header={{
                eyebrow: 'Angels of Death',
                title: 'Ranking Structure',
                breadcrumbs: [{ label: 'Clan Information' }, { label: 'Ranking Structure' }],
            }}
        >
            <Head title="Ranking Structure" />

            <div className="grid gap-8 xl:grid-cols-[1fr_20rem]">
                <div className="space-y-4">
                    <p className="max-w-prose text-sm text-muted-foreground">
                        Rank in AOD is earned through tenure and service, not handed out for authority. The structure
                        runs as one continuous line from the enlisted path up through clan administration.
                    </p>

                    <div className="rank-tree">
                        {sections.map((section) => (
                            <Fragment key={section.label}>
                                <div className="rank-tier-band">
                                    <span className="rank-tier-band-label">{section.label}</span>
                                </div>
                                <div className="rank-tier">
                                    <span className="rank-tier-side" aria-hidden="true">
                                        {section.label}
                                    </span>
                                    {section.ranks.map((rank) => (
                                        <div
                                            key={rank.abbr}
                                            className={cn(
                                                'rank-row',
                                                rank.tier === 'enlisted' && 'rank-row--muted',
                                            )}
                                            style={
                                                {
                                                    '--rank-color': rank.color,
                                                    '--i': rowIndex++,
                                                } as CSSProperties
                                            }
                                        >
                                            <div className="rank-row-track">
                                                <span className="rank-row-node" />
                                            </div>
                                            <div className="rank-row-body">
                                                <div className="rank-row-head">
                                                    <span className="rank-row-abbr">{rank.abbr}</span>
                                                    <span className="rank-row-name">{rank.name}</span>
                                                </div>
                                                <p className="rank-row-duties">{rank.duties}</p>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            </Fragment>
                        ))}
                    </div>
                </div>

                <aside className="space-y-4">
                    <h2 className="text-xs font-semibold uppercase tracking-[0.2em] text-primary">Roles</h2>
                    {ROLES.map((role) => (
                        <Card key={role.abbr}>
                            <CardContent className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <span className="numeric text-sm font-semibold text-primary">{role.abbr}</span>
                                    <span className="text-sm font-medium">{role.label}</span>
                                </div>
                                <p className="text-sm text-muted-foreground">{role.body}</p>
                                <p className="text-sm">
                                    <span className="text-muted-foreground">Minimum rank:</span>{' '}
                                    <strong>{role.minimum}</strong>
                                </p>
                            </CardContent>
                        </Card>
                    ))}

                    <div className="flex gap-3 rounded-md border border-border bg-muted/40 p-4 text-sm text-muted-foreground">
                        <Info className="mt-0.5 size-4 shrink-0" />
                        <p>
                            Only Sergeants and above may issue promotions or demotions, and these are given solely for
                            good service to AOD. Promotions to CO or XO positions and all Sergeant-level promotions
                            require approval from clan leadership.
                        </p>
                    </div>

                    <p className="text-sm text-muted-foreground">
                        Divisional roles carry authority within a specific division, are independent of rank, and require
                        nomination and approval by clan leadership.
                    </p>
                </aside>
            </div>
        </AppLayout>
    );
}
