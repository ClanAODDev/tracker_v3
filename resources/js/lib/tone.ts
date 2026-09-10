export type Tone = 'default' | 'success' | 'warning' | 'danger' | 'info' | 'accent' | 'muted';

const TEXT: Record<Tone, string> = {
    default: 'text-foreground',
    success: 'text-success',
    warning: 'text-warning',
    danger: 'text-destructive',
    info: 'text-info',
    accent: 'text-primary',
    muted: 'text-muted-foreground',
};

const BORDER: Record<Tone, string> = {
    default: 'border-border',
    success: 'border-success/30',
    warning: 'border-warning/30',
    danger: 'border-destructive/30',
    info: 'border-info/30',
    accent: 'border-primary/30',
    muted: 'border-border',
};

const SURFACE: Record<Tone, string> = {
    default: 'border-border',
    success: 'border-success/40 bg-success/5',
    warning: 'border-warning/40 bg-warning/5',
    danger: 'border-destructive/40 bg-destructive/5',
    info: 'border-info/40 bg-info/5',
    accent: 'border-primary/40 bg-primary/5',
    muted: 'border-border',
};

const FILL: Record<Tone, string> = {
    default: 'bg-muted',
    success: 'bg-success/10',
    warning: 'bg-warning/10',
    danger: 'bg-destructive/10',
    info: 'bg-info/10',
    accent: 'bg-primary/10',
    muted: 'bg-muted',
};

const asTone = (tone: string | null | undefined): Tone =>
    tone && tone in TEXT ? (tone as Tone) : 'default';

export const toneText = (tone: string | null | undefined) => TEXT[asTone(tone)];
export const toneBorder = (tone: string | null | undefined) => BORDER[asTone(tone)];
export const toneSurface = (tone: string | null | undefined) => SURFACE[asTone(tone)];
export const toneFill = (tone: string | null | undefined) => FILL[asTone(tone)];
