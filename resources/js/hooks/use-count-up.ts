import { useEffect, useRef, useState } from 'react';

import { useReducedMotion } from '@/hooks/use-reduced-motion';

/**
 * Eases a number from 0 (or the previous value) up to `target` on mount and
 * whenever `target` changes. Honours the reduce-motion preference.
 */
export function useCountUp(target: number, durationMs = 900): number {
    const reduced = useReducedMotion();
    const [value, setValue] = useState(reduced ? target : 0);
    const fromRef = useRef(0);

    useEffect(() => {
        if (reduced || !Number.isFinite(target)) {
            setValue(target);
            fromRef.current = target;
            return;
        }

        const from = fromRef.current;
        const start = performance.now();
        let raf = 0;

        const tick = (now: number) => {
            const t = Math.min((now - start) / durationMs, 1);
            const eased = 1 - Math.pow(1 - t, 2);
            setValue(Math.round(from + (target - from) * eased));
            if (t < 1) {
                raf = requestAnimationFrame(tick);
            } else {
                fromRef.current = target;
            }
        };

        raf = requestAnimationFrame(tick);
        return () => cancelAnimationFrame(raf);
    }, [target, durationMs, reduced]);

    return value;
}
