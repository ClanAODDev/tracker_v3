export const FIELD_BADGE_COLORS: Record<string, string> = {
    gray: 'bg-muted text-muted-foreground',
    red: 'bg-chart-1/15 text-chart-1',
    orange: 'bg-rarity-legendary/15 text-rarity-legendary',
    yellow: 'bg-chart-3/15 text-chart-3',
    green: 'bg-chart-4/15 text-chart-4',
    blue: 'bg-chart-2/15 text-chart-2',
    violet: 'bg-chart-5/15 text-chart-5',
    pink: 'bg-rarity-epic/15 text-rarity-epic',
};

export function fieldBadgeClass(color: string | null): string {
    return FIELD_BADGE_COLORS[color ?? 'gray'] ?? FIELD_BADGE_COLORS.gray;
}

// Outline-only variant (border + text, no fill) — matches the thin look of
// tag pills elsewhere on the member profile page.
export const FIELD_OUTLINE_COLORS: Record<string, string> = {
    gray: 'border-border text-muted-foreground',
    red: 'border-chart-1/40 text-chart-1',
    orange: 'border-rarity-legendary/40 text-rarity-legendary',
    yellow: 'border-chart-3/40 text-chart-3',
    green: 'border-chart-4/40 text-chart-4',
    blue: 'border-chart-2/40 text-chart-2',
    violet: 'border-chart-5/40 text-chart-5',
    pink: 'border-rarity-epic/40 text-rarity-epic',
};

export function fieldOutlineClass(color: string | null): string {
    return FIELD_OUTLINE_COLORS[color ?? 'gray'] ?? FIELD_OUTLINE_COLORS.gray;
}
