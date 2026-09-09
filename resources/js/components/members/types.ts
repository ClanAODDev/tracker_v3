export interface MemberRow {
    id: number;
    name: string;
    rankName: string;
    rankAbbr: string | null;
    rankValue: number;
    rankColor: string | null;
    position: string | null;
    positionAbbr: string | null;
    positionClass: string | null;
    profileUrl: string;
    assignment: { label: string; url: string } | null;
    joinDate: string | null;
    voice: { label: string; tone: string; iso: string | null };
    lastPromotedAt: string | null;
    reminder: { date: string | null; remindedToday: boolean; human: string; sortKey: string };
    canRemind: boolean;
    tags: Array<{ id: number; name: string; visibility: string }>;
    tagIds: number[];
    handle: { value: string; url: string | null } | null;
    posts: number;
    onLeave: boolean;
    isParttimer: boolean;
    primaryDivision: string | null;
    directRecruit: boolean;
}

export interface UnitStats {
    totalCount: number;
    onLeaveCount: number;
    inactiveCount: number;
    inactivityDays: number;
    avgTenureYears: number;
    officerCount: number;
    memberCount: number;
    voiceActivity: { labels: string[]; values: number[]; colors: string[] };
}

export interface BulkConfig {
    enabled: boolean;
    canRemind: boolean;
    canAssignTags: boolean;
    canMoveMembers: boolean;
    assignableTags: Array<{ id: number; name: string; visibility: string }>;
    urls: {
        pm: string;
        tags: string;
        transfer: string;
        transferData: string;
        reminder: string;
    };
}

export interface MemberListDivision {
    name: string;
    slug: string;
    platoonLabel: string;
    squadLabel: string;
}
