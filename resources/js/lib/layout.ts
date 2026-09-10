export type LayoutWidth = 'default' | 'wide' | 'full';

export const LAYOUT_MAX_WIDTH: Record<LayoutWidth, string> = {
    default: 'max-w-[88rem]',
    wide: 'max-w-[104rem]',
    full: 'max-w-none',
};
