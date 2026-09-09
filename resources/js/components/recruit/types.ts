export interface RecruitSquad {
    id: number;
    name: string | null;
    members_count: number;
    leader_name: string | null;
}

export interface RecruitPlatoon {
    id: number;
    name: string;
    members_count: number;
    leader_name: string | null;
    squads: RecruitSquad[];
}

export interface RecruitThread {
    name: string;
    url: string;
    comments: string;
    read: boolean;
}

export interface RecruitTask {
    description: string;
    complete: boolean;
}

export interface PendingDiscordUser {
    id: number;
    discord_username: string;
    created_at: string;
    email: string | null;
    avatar_url: string | null;
    forum_name: string | null;
    application_division: string | null;
    application: Array<{ label: string; value: string }> | null;
}

export interface DivisionRecruitData {
    name: string;
    platoons: RecruitPlatoon[];
    threads: RecruitThread[];
    tasks: RecruitTask[];
    welcome_area: string;
    welcome_pm: string;
    use_welcome_thread: boolean;
    locality: { platoon: string; squad: string };
    pending_discord: PendingDiscordUser[];
}

export interface RecruitFormProps extends DivisionRecruitData {
    divisionSlug: string;
    cancelUrl: string;
    recruiterId: number;
    ranks: Record<string, string>;
    forbiddenNamePrefixes: string[];
    pendingUserId: number | null;
    memberId: number | null;
}

export interface MemberIdValidation {
    valid: boolean;
    verifiedEmail: boolean;
    groupId: number | null;
    currentUsername: string;
    existsInTracker: boolean;
    tags: Array<{ name: string; division: string }>;
    division: string | null;
    discordMatches: Array<{ clan_id: number; name: string; division: string | null; url: string }>;
    discordUsername: string | null;
}

export interface ForumNameValidation {
    valid: boolean;
    available: boolean;
    existingAccount?: boolean;
    rejectedPrefix?: 'aod' | 'rank';
    invalidChars?: boolean;
}

export interface ForumEmailCheck {
    loading: boolean;
    checked: boolean;
    found: boolean;
    userId: number | null;
    username: string | null;
    groupId: number | null;
    eligible: boolean;
    rejectionReason: string | null;
}

export const EMPTY_MEMBER_ID: MemberIdValidation = {
    valid: false,
    verifiedEmail: false,
    groupId: null,
    currentUsername: '',
    existsInTracker: false,
    tags: [],
    division: null,
    discordMatches: [],
    discordUsername: null,
};

export const EMPTY_EMAIL_CHECK: ForumEmailCheck = {
    loading: false,
    checked: false,
    found: false,
    userId: null,
    username: null,
    groupId: null,
    eligible: false,
    rejectionReason: null,
};
