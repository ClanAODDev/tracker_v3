interface TooltipEntry {
    dataKey?: string | number;
    name?: string | number;
    value?: number | string;
    color?: string;
}

/**
 * Themed tooltip: labels and values wear text tokens, the small square carries
 * series identity. Matches the dataviz skill's "text never wears the series
 * colour" rule.
 */
export function ChartTooltip({
    active,
    payload,
    label,
}: {
    active?: boolean;
    payload?: TooltipEntry[];
    label?: string | number;
}) {
    if (!active || !payload?.length) return null;

    return (
        <div className="rounded-md border border-border bg-popover px-3 py-2 text-xs shadow-lg">
            {label != null && <div className="mb-1 font-medium text-popover-foreground">{label}</div>}
            <div className="space-y-1">
                {payload.map((entry) => (
                    <div key={String(entry.dataKey)} className="flex items-center gap-2">
                        <span className="size-2 shrink-0 rounded-[2px]" style={{ background: entry.color }} />
                        <span className="text-muted-foreground">{entry.name}</span>
                        <span className="numeric ml-auto text-popover-foreground">
                            {typeof entry.value === 'number' ? entry.value.toLocaleString() : entry.value}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}
