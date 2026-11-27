import type { HandleType } from '@/components/members/types';

export interface FilledHandle extends HandleType {
    value: string;
}

export function ingameName(handleTypes: HandleType[], filled: FilledHandle[]): string {
    if (handleTypes.length <= 1) {
        return filled[0]?.value ?? '';
    }

    return filled.map((handle) => `${handle.label}: ${handle.value}`).join(' · ');
}

export function renderWelcomeMessage(template: string, name: string, handleTypes: HandleType[], filled: FilledHandle[]): string {
    const byTag = new Map(filled.filter((handle) => handle.tag).map((handle) => [handle.tag as string, handle.value]));

    return template
        .replace(/\{\{\s*name\s*\}\}/g, name)
        .replace(/\{\{\s*ingame_name\s*\}\}/g, ingameName(handleTypes, filled))
        .replace(/\{\{\s*handle\.([a-z0-9_]+)\s*\}\}/g, (_match, tag: string) => byTag.get(tag) ?? '');
}
