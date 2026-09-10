import { type CSSProperties, type ReactNode, useEffect, useRef, useState } from 'react';

import { cn } from '@/lib/utils';

/**
 * A meter fill that grows from 0 to `pct` on mount (and animates to new values
 * afterward). base.css collapses the transition under reduced motion.
 */
export function FillBar({ pct, className, style }: { pct: number; className?: string; style?: CSSProperties }) {
    const [width, setWidth] = useState(0);

    useEffect(() => {
        const id = requestAnimationFrame(() => setWidth(Math.max(0, Math.min(100, pct))));
        return () => cancelAnimationFrame(id);
    }, [pct]);

    return (
        <div
            aria-hidden="true"
            className={cn('h-full transition-[width] duration-500 ease-out', className)}
            style={{ ...style, width: `${width}%` }}
        />
    );
}

/**
 * Wraps a value that updates in place and pulses it crimson on change — never on
 * first render.
 */
export function FlashOnChange({
    value,
    children,
    className,
}: {
    value: string | number;
    children: ReactNode;
    className?: string;
}) {
    const [flashing, setFlashing] = useState(false);
    const first = useRef(true);

    useEffect(() => {
        if (first.current) {
            first.current = false;
            return;
        }
        setFlashing(true);
        const id = window.setTimeout(() => setFlashing(false), 600);
        return () => window.clearTimeout(id);
    }, [value]);

    return <span className={cn(className, flashing && 'tron-flash-value')}>{children}</span>;
}
