import { cn } from '@/lib/utils';

/** A 1px "circuit trace" rule — bright lead + node square + travelling pulse. */
export function TronTrace({ className }: { className?: string }) {
    return <div aria-hidden className={cn('tron-trace', className)} />;
}

/** A small monospace HUD tag, e.g. `DIV·BF`, with an optional live status pip. */
export function TronIdPlate({ label, live, className }: { label: string; live?: boolean; className?: string }) {
    return (
        <span className={cn('tron-id-plate', className)}>
            {live && <span className="tron-pip" title="Synced with the forums hourly" />}
            {label}
        </span>
    );
}

/**
 * A one-shot bright line that sweeps down its positioned parent on mount.
 * Drop it as the first child of a `relative` container.
 */
export function TronScanline() {
    return <span aria-hidden className="tron-scan-overlay" />;
}
