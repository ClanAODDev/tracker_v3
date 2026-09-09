export interface AuthUser {
    id: number;
    name: string;
    memberId: number | null;
    divisionId: number | null;
    avatarUrl: string | null;
    effectiveRole: string | null;
    impersonating: boolean;
    impersonatingRole: boolean;
    settings: {
        reduceAnimations: boolean;
        mobileNavSide: 'left' | 'right';
        theme: 'light' | 'dark';
    };
}

export interface AuthPermissions {
    canWorkTickets: boolean;
    canUseBulkMode: boolean;
    isAdmin: boolean;
    isDeveloper: boolean;
}

export interface NavItem {
    type?: 'heading';
    label: string;
    href?: string;
    external?: boolean;
    icon?: string;
    match?: string[];
    children?: NavItem[];
}

export interface Toast {
    type: 'success' | 'info' | 'warning' | 'error';
    title: string | null;
    message: string;
    options: Record<string, unknown>;
}

/** Mirrors `App\Support\MemberCard::from()` — the canonical member reference. */
export interface MemberCard {
    name: string;
    clanId: number;
    rankName: string;
    rankAbbr: string;
    rankColor: string;
    position: string | null;
    avatarUrl: string | null;
    profileUrl: string;
    division: string | null;
}

export interface SharedProps {
    auth: {
        user: AuthUser | null;
        permissions: AuthPermissions | null;
    };
    nav: NavItem[];
    flash: {
        toasts: Toast[];
    };
    [key: string]: unknown;
}
