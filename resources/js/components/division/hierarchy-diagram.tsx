import { cn } from '@/lib/utils';

export type Tier = 'platoon' | 'squad';

export interface DiagramLevel {
    label: string;
    tier: Tier;
}

interface HierarchyDiagramProps {
    levels: DiagramLevel[];
    highlightDepth?: number;
    className?: string;
}

const LABEL_WIDTH = 96;
const CELL = 30;
const NODE_W = 22;
const NODE_H = 14;
const ROW = 46;
const TOP = 16;

const TIER_FILL: Record<Tier, string> = {
    platoon: 'fill-primary',
    squad: 'fill-[var(--chart-3)]',
};

export function HierarchyDiagram({ levels, highlightDepth, className }: HierarchyDiagramProps) {
    const depth = levels.length;
    const leaves = 2 ** depth;
    const width = LABEL_WIDTH + leaves * CELL;
    const height = TOP + depth * ROW + NODE_H + 8;

    const xs: number[][] = [];
    xs[depth] = Array.from({ length: leaves }, (_, i) => LABEL_WIDTH + (i + 0.5) * CELL);
    for (let d = depth - 1; d >= 0; d--) {
        xs[d] = Array.from({ length: 2 ** d }, (_, i) => (xs[d + 1][2 * i] + xs[d + 1][2 * i + 1]) / 2);
    }

    const y = (d: number) => TOP + d * ROW;
    const showHighlight = highlightDepth !== undefined;

    function state(d: number, i: number): 'selected' | 'covered' | 'dim' | 'tier' {
        if (!showHighlight) return 'tier';
        if (d === highlightDepth && i === 0) return 'selected';
        if (d > highlightDepth && i >> (d - highlightDepth) === 0) return 'covered';
        return 'dim';
    }

    return (
        <svg
            viewBox={`0 0 ${width} ${height}`}
            style={{ maxWidth: width }}
            className={cn('h-auto w-full', className)}
            role="img"
        >
            {xs.slice(1).map((row, di) =>
                row.map((x, i) => {
                    const d = di + 1;
                    const px = xs[d - 1][i >> 1];
                    const midY = y(d) - (ROW - NODE_H) / 2;
                    return (
                        <path
                            key={`e-${d}-${i}`}
                            d={`M${px} ${y(d - 1) + NODE_H} V${midY} H${x} V${y(d)}`}
                            className="fill-none stroke-border"
                            strokeWidth={1}
                        />
                    );
                }),
            )}

            {xs.map((row, d) =>
                row.map((x, i) => {
                    const s = d === 0 ? 'tier' : state(d, i);
                    const tier = d === 0 ? null : levels[d - 1].tier;
                    return (
                        <rect
                            key={`n-${d}-${i}`}
                            x={x - (d === 0 ? NODE_W : NODE_W / 2)}
                            y={y(d)}
                            width={d === 0 ? NODE_W * 2 : NODE_W}
                            height={NODE_H}
                            rx={3}
                            className={cn(
                                s === 'tier' && (tier ? TIER_FILL[tier] : 'fill-muted-foreground'),
                                s === 'selected' && 'fill-primary stroke-primary',
                                s === 'covered' && 'fill-primary/35 stroke-primary',
                                s === 'dim' && 'fill-muted stroke-border',
                            )}
                            strokeWidth={s === 'selected' ? 2 : 1}
                            style={s === 'selected' ? { filter: 'drop-shadow(0 0 5px var(--primary-glow))' } : undefined}
                        />
                    );
                }),
            )}

            <text x={4} y={y(0) + NODE_H - 3} className="fill-muted-foreground text-[10px]">
                Division
            </text>
            {levels.map((level, i) => (
                <text
                    key={`l-${i}`}
                    x={4}
                    y={y(i + 1) + NODE_H - 3}
                    className={cn(
                        'text-[10px]',
                        highlightDepth === i + 1 ? 'fill-foreground font-semibold' : 'fill-muted-foreground',
                    )}
                >
                    {level.label}
                </text>
            ))}
        </svg>
    );
}
