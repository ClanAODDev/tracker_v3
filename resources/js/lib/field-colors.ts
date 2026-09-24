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
