import { Link } from '@inertiajs/react';
import { Hand, Layers, Trophy } from 'lucide-react';

import { cn } from '@/lib/utils';

export interface AwardSummary {
    id: number;
    name: string;
    rarity: string;
    recipientsCount: number;
    image: string | null;
    allowRequest: boolean;
}

export interface TieredGroupSummary {
    name: string;
    slug: string;
    tierCount: number;
    recipientCount: number;
    image: string | null;
}

function rarityColor(rarity: string) {
    return `var(--rarity-${rarity}, var(--muted-foreground))`;
}

function counted(noun: string, n: number) {
    return `${n.toLocaleString()} ${noun}${n === 1 ? '' : 's'}`;
}

function CardShell({
    href,
    rarity,
    image,
    children,
}: {
    href: string;
    rarity: string;
    image: string | null;
    children: React.ReactNode;
}) {
    return (
        <Link
            href={href}
            className="group flex flex-col items-center rounded-md border border-border bg-card p-3 text-center transition-colors hover:border-[color:var(--rarity)]"
            style={{ '--rarity': rarityColor(rarity) } as React.CSSProperties}
        >
            <span
                className="mb-2 h-0.5 w-8 rounded-full"
                style={{ background: rarityColor(rarity) }}
            />
            <div className="flex size-16 items-center justify-center">
                {image ? (
                    <img src={image} alt="" loading="lazy" className="max-h-16 max-w-full object-contain" />
                ) : (
                    <Trophy className="size-8 text-muted-foreground" />
                )}
            </div>
            {children}
        </Link>
    );
}

export function AwardCard({ award }: { award: AwardSummary }) {
    return (
        <CardShell href={`/clan/awards/${award.id}`} rarity={award.rarity} image={award.image}>
            <p className="mt-2 flex items-center gap-1 text-xs font-medium">
                {award.name}
                {award.allowRequest && <Hand className="size-3 text-success" aria-label="Requestable" />}
            </p>
            <span
                className="mt-1.5 rounded-full px-2 py-0.5 text-[11px]"
                style={{ background: `color-mix(in srgb, ${rarityColor(award.rarity)} 15%, transparent)`, color: rarityColor(award.rarity) }}
            >
                {counted('recipient', award.recipientsCount)}
            </span>
        </CardShell>
    );
}

export function TieredCard({ group }: { group: TieredGroupSummary }) {
    return (
        <CardShell href={`/clan/awards/tiered/${group.slug}`} rarity="legendary" image={group.image}>
            <p className="mt-2 flex items-center gap-1 text-xs font-medium">
                {group.name}
                <Layers className="size-3 text-muted-foreground" aria-label="Tiered award" />
            </p>
            <div className="mt-1.5 flex flex-wrap justify-center gap-1">
                <span
                    className="rounded-full px-2 py-0.5 text-[11px]"
                    style={{
                        background: 'color-mix(in srgb, var(--rarity-legendary) 15%, transparent)',
                        color: 'var(--rarity-legendary)',
                    }}
                >
                    {group.tierCount} tiers
                </span>
                <span className="rounded-full bg-muted px-2 py-0.5 text-[11px] text-muted-foreground">
                    {counted('recipient', group.recipientCount)}
                </span>
            </div>
        </CardShell>
    );
}

export function RarityPill({ rarity }: { rarity: string }) {
    return (
        <span
            className={cn('rounded-full px-2.5 py-0.5 text-xs font-medium capitalize')}
            style={{
                background: `color-mix(in srgb, ${rarityColor(rarity)} 15%, transparent)`,
                color: rarityColor(rarity),
            }}
        >
            {rarity}
        </span>
    );
}
