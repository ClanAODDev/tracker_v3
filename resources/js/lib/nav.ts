import type { NavItem } from '@/types';

function matchesPattern(path: string, pattern: string): boolean {
    const normalized = path.replace(/^\/+|\/+$/g, '');
    const target = pattern.replace(/^\/+|\/+$/g, '');

    if (target === '' || target === '/') {
        return normalized === '';
    }

    if (target.endsWith('/*')) {
        const base = target.slice(0, -2);
        return normalized === base || normalized.startsWith(`${base}/`);
    }

    return normalized === target;
}

export function isNavItemActive(item: NavItem, currentUrl: string): boolean {
    const path = currentUrl.split('?')[0];
    const patterns = item.match ?? [];

    if (patterns.some((pattern) => matchesPattern(path, pattern))) {
        return true;
    }

    return (item.children ?? []).some((child) => isNavItemActive(child, currentUrl));
}
