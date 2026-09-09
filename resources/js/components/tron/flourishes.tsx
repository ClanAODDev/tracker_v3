import { useEffect, useRef } from 'react';

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
 * Two crimson gradients that flash along the top and bottom edges of the
 * positioned parent on mount — and again whenever it is hovered — converging
 * on the corner brackets. Drop it as the first child of a `relative` container.
 */
export function TronFlash() {
    const ref = useRef<HTMLSpanElement>(null);

    useEffect(() => {
        const el = ref.current;
        const parent = el?.parentElement;
        if (!el || !parent) return;
        const replay = () => {
            el.classList.remove('tron-flash-run');
            void el.offsetWidth;
            el.classList.add('tron-flash-run');
        };
        parent.addEventListener('mouseenter', replay);
        return () => parent.removeEventListener('mouseenter', replay);
    }, []);

    return <span ref={ref} aria-hidden className="tron-flash tron-flash-run" />;
}
