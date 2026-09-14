import { Gamepad2 } from 'lucide-react';
import type { ComponentType } from 'react';

import { SteamVanityUrlTool } from '@/components/tools/steam-vanity-url-tool';

export interface ToolDefinition {
    id: string;
    label: string;
    description: string;
    icon: ComponentType<{ className?: string }>;
    component: ComponentType<{ open: boolean; onOpenChange: (open: boolean) => void }>;
}

/**
 * Small, situational utilities that don't warrant nav real estate — surfaced
 * only via the topbar Tools menu. Add new entries here; nothing else to wire.
 */
export const TOOLS: ToolDefinition[] = [
    {
        id: 'steam-vanity-url',
        label: 'Steam Vanity URL → ID',
        description: 'Convert a Steam profile URL or vanity name to a SteamID64.',
        icon: Gamepad2,
        component: SteamVanityUrlTool,
    },
];
