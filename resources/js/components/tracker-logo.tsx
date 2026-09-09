import { cn } from '@/lib/utils';

const BAND: Record<number, string> = {
    3: 'color-mix(in srgb, var(--primary) 20%, #000)',
    2: 'color-mix(in srgb, var(--primary) 42%, #000)',
    1: 'color-mix(in srgb, var(--primary) 70%, #000)',
    0: 'var(--primary)',
    [-1]: 'color-mix(in srgb, var(--primary) 78%, #fff)',
    [-2]: 'color-mix(in srgb, var(--primary) 45%, #fff)',
    [-3]: 'color-mix(in srgb, var(--primary) 14%, #fff)',
};

const CELLS = Array.from({ length: 4 }, (_, row) =>
    Array.from({ length: 4 }, (_, col) => ({ row, col })),
).flat();

export function TrackerMark({ className }: { className?: string }) {
    return (
        <svg
            viewBox="0 0 16 16"
            className={cn('size-5 shrink-0', className)}
            role="img"
            aria-label="AOD Tracker"
        >
            {CELLS.map(({ row, col }) => (
                <rect
                    key={`${row}-${col}`}
                    x={col * 4 + 0.35}
                    y={row * 4 + 0.35}
                    width={3.3}
                    height={3.3}
                    rx={0.4}
                    fill={BAND[col - row]}
                />
            ))}
        </svg>
    );
}

export function TrackerLogo({ className }: { className?: string }) {
    return (
        <span className={cn('flex items-center gap-2', className)}>
            <TrackerMark />
            <span className="font-mono text-sm font-semibold tracking-[0.3em] text-foreground">TRACKER</span>
        </span>
    );
}
