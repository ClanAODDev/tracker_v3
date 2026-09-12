const VOICE_CORNER = ['var(--destructive)', 'var(--warning)', 'var(--success)'] as const;

export function voiceTone(rate: number) {
    return rate >= 30 ? 'text-success' : rate >= 15 ? 'text-warning' : 'text-destructive';
}

export function voiceFill(rate: number) {
    return rate >= 30 ? 'bg-success/15' : rate >= 15 ? 'bg-warning/15' : 'bg-destructive/15';
}

export function voiceCorner(rate: number) {
    return rate >= 30 ? VOICE_CORNER[2] : rate >= 15 ? VOICE_CORNER[1] : VOICE_CORNER[0];
}
