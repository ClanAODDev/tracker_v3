import { useCountUp } from '@/hooks/use-count-up';

/**
 * Renders a number that eases up from zero on mount. `format` defaults to
 * locale grouping; pass a custom formatter for units, percentages, etc.
 */
export function CountUp({
    value,
    durationMs,
    format = (n) => n.toLocaleString(),
}: {
    value: number;
    durationMs?: number;
    format?: (n: number) => string;
}) {
    const current = useCountUp(value, durationMs);
    return <>{format(current)}</>;
}
