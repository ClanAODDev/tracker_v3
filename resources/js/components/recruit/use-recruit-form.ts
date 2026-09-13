import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

import { postJson } from '@/lib/api';

import {
    EMPTY_EMAIL_CHECK,
    EMPTY_MEMBER_ID,
    type ForumEmailCheck,
    type ForumNameValidation,
    type MemberIdValidation,
    type PendingDiscordUser,
    type RecruitFormProps,
    type RecruitTask,
    type RecruitThread,
} from './types';

interface MemberFields {
    id: string;
    forum_name: string;
    ingame_name: string;
    rank: string;
    platoon: string;
    squad: string;
}

const BLANK_MEMBER: MemberFields = { id: '', forum_name: '', ingame_name: '', rank: '', platoon: '', squad: '' };

function formatName(raw: string): string {
    return raw.replace(/\b\w/g, (c) => c.toUpperCase());
}

export function useRecruitForm(props: RecruitFormProps) {
    const [step, setStep] = useState<'form' | 'confirmation'>('form');
    const [path, setPath] = useState<'forum' | 'discord' | null>(null);
    const [member, setMember] = useState<MemberFields>(BLANK_MEMBER);
    const [memberIdValidation, setMemberIdValidation] = useState<MemberIdValidation>(EMPTY_MEMBER_ID);
    const [forumNameValidation, setForumNameValidation] = useState<ForumNameValidation>({
        valid: false,
        available: false,
    });
    const [validating, setValidating] = useState(false);
    const [emailCheck, setEmailCheck] = useState<ForumEmailCheck>(EMPTY_EMAIL_CHECK);
    const [selectedPending, setSelectedPending] = useState<PendingDiscordUser | null>(null);
    const [pending, setPending] = useState<PendingDiscordUser[]>(props.pending_discord);
    const [loadingPending, setLoadingPending] = useState(false);
    const [threads, setThreads] = useState<RecruitThread[]>(props.threads);
    const [tasks, setTasks] = useState<RecruitTask[]>(props.tasks);
    const [submitting, setSubmitting] = useState(false);
    const [submitError, setSubmitError] = useState<string | null>(null);
    const [submitted, setSubmitted] = useState(false);

    const timers = useRef<Record<string, ReturnType<typeof setTimeout>>>({});
    const debounce = useCallback((key: string, fn: () => void, delay = 300) => {
        clearTimeout(timers.current[key]);
        timers.current[key] = setTimeout(fn, delay);
    }, []);

    const patchMember = useCallback((patch: Partial<MemberFields>) => setMember((m) => ({ ...m, ...patch })), []);

    const validateForumName = useCallback(
        (name: string, memberId: string, email?: string) => {
            if (!name) {
                setForumNameValidation({ valid: false, available: false });
                return;
            }
            const lower = name.toLowerCase();
            if (lower.startsWith('aod_')) {
                setForumNameValidation({ valid: false, available: false, rejectedPrefix: 'aod' });
                return;
            }
            if (props.forbiddenNamePrefixes.some((r) => lower.startsWith(r))) {
                setForumNameValidation({ valid: false, available: false, rejectedPrefix: 'rank' });
                return;
            }
            if (/[<>&"']/.test(name)) {
                setForumNameValidation({ valid: false, available: false, invalidChars: true });
                return;
            }

            setValidating(true);
            debounce('forumName', () => {
                postJson<{ memberExists: boolean; existingAccount?: boolean; existingUserId?: number }>(
                    '/validate-name',
                    { name, member_id: memberId, ...(email ? { email } : {}) },
                )
                    .then((res) => {
                        const available = !res.memberExists;
                        setForumNameValidation({
                            valid: available,
                            available,
                            existingAccount: res.existingAccount ?? false,
                        });
                        if (res.existingAccount && res.existingUserId) {
                            patchMember({ id: String(res.existingUserId) });
                            setMemberIdValidation({
                                ...EMPTY_MEMBER_ID,
                                valid: true,
                                verifiedEmail: true,
                                currentUsername: name,
                            });
                        }
                    })
                    .catch(() => setForumNameValidation({ valid: false, available: false }))
                    .finally(() => setValidating(false));
            });
        },
        [debounce, patchMember, props.forbiddenNamePrefixes],
    );

    const validateMemberId = useCallback(
        (id: string) => {
            if (!id) {
                setMemberIdValidation(EMPTY_MEMBER_ID);
                return;
            }
            setValidating(true);
            debounce('memberId', () => {
                postJson<Record<string, unknown>>(`/validate-id/${id}`, {})
                    .then((data) => {
                        const v: MemberIdValidation = {
                            valid: Boolean(data.is_member),
                            verifiedEmail: Boolean(data.valid_group),
                            groupId: (data.group_id as number) ?? null,
                            currentUsername: (data.username as string) ?? '',
                            existsInTracker: Boolean(data.exists_in_tracker),
                            tags: (data.tags as MemberIdValidation['tags']) ?? [],
                            division: (data.division as string) ?? null,
                            discordMatches: (data.discord_matches as MemberIdValidation['discordMatches']) ?? [],
                            discordUsername: (data.discord_username as string) ?? null,
                        };
                        setMemberIdValidation(v);
                        if (v.valid && v.verifiedEmail && v.currentUsername) {
                            const name = formatName(v.currentUsername.replace(/^AOD_/i, ''));
                            patchMember({ forum_name: name });
                            validateForumName(name, id);
                        }
                    })
                    .catch(() => setMemberIdValidation(EMPTY_MEMBER_ID))
                    .finally(() => setValidating(false));
            });
        },
        [debounce, patchMember, validateForumName],
    );

    const applyPendingDefaults = useCallback(
        (user: PendingDiscordUser) => {
            const name = user.forum_name || user.discord_username;
            patchMember({ forum_name: name });
            setForumNameValidation({ valid: false, available: false });
            validateForumName(name, member.id, user.email ?? undefined);
        },
        [member.id, patchMember, validateForumName],
    );

    const checkForumEmail = useCallback(
        (user: PendingDiscordUser) => {
            if (!user.email) {
                setEmailCheck({ ...EMPTY_EMAIL_CHECK, checked: true, found: false });
                applyPendingDefaults(user);
                return;
            }
            setEmailCheck({ ...EMPTY_EMAIL_CHECK, loading: true });
            postJson<Record<string, unknown>>('/check-forum-email', { email: user.email })
                .then((data) => {
                    const found = Boolean(data.found);
                    const eligible = Boolean(data.eligible);
                    setEmailCheck({
                        loading: false,
                        checked: true,
                        found,
                        userId: (data.user_id as number) ?? null,
                        username: (data.username as string) ?? null,
                        groupId: (data.group_id as number) ?? null,
                        eligible,
                        rejectionReason: (data.rejection_reason as string) ?? null,
                    });
                    if (found && eligible) {
                        patchMember({ id: String(data.user_id) });
                        setMemberIdValidation({
                            ...EMPTY_MEMBER_ID,
                            valid: true,
                            verifiedEmail: true,
                            groupId: (data.group_id as number) ?? null,
                            currentUsername: (data.username as string) ?? '',
                        });
                        const name = formatName(String(data.username ?? '').replace(/^AOD_/i, ''));
                        patchMember({ forum_name: name });
                        validateForumName(name, String(data.user_id), user.email ?? undefined);
                    } else if (!found) {
                        applyPendingDefaults(user);
                    }
                })
                .catch(() => {
                    setEmailCheck({ ...EMPTY_EMAIL_CHECK, checked: true });
                    applyPendingDefaults(user);
                });
        },
        [applyPendingDefaults, patchMember, validateForumName],
    );

    const selectPending = useCallback(
        (id: number | null) => {
            if (!id) {
                setSelectedPending(null);
                return;
            }
            const user = pending.find((u) => u.id === id);
            if (!user) return;
            setSelectedPending(user);
            patchMember({ rank: '1' });
            setEmailCheck(EMPTY_EMAIL_CHECK);
            checkForumEmail(user);
        },
        [pending, checkForumEmail, patchMember],
    );

    const clearPending = useCallback(() => {
        setSelectedPending(null);
        setMember(BLANK_MEMBER);
        setMemberIdValidation(EMPTY_MEMBER_ID);
        setForumNameValidation({ valid: false, available: false });
        setEmailCheck(EMPTY_EMAIL_CHECK);
    }, []);

    const reloadPending = useCallback(
        (all: boolean) => {
            setLoadingPending(true);
            fetch(`/divisions/${props.divisionSlug}/recruit/pending-discord${all ? '?all_pending=1' : ''}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            })
                .then((r) => r.json())
                .then((data) => setPending(data.pending_discord ?? []))
                .catch(() => {})
                .finally(() => setLoadingPending(false));
        },
        [props.divisionSlug],
    );

    const selectedPlatoonSquads = useMemo(
        () => props.platoons.find((p) => p.id === Number(member.platoon))?.squads ?? [],
        [props.platoons, member.platoon],
    );

    const memberVerificationComplete = useMemo(() => {
        if (path === 'discord' && selectedPending) {
            if (emailCheck.found && !emailCheck.eligible) return false;
            if (emailCheck.found && emailCheck.eligible) return true;
            return emailCheck.checked && !emailCheck.found;
        }
        return memberIdValidation.valid && memberIdValidation.verifiedEmail;
    }, [path, selectedPending, emailCheck, memberIdValidation]);

    const detailsComplete = Boolean(
        member.forum_name && forumNameValidation.valid && member.ingame_name && member.rank,
    );
    const assignmentComplete =
        props.platoons.length === 0 ||
        Boolean(member.platoon && (selectedPlatoonSquads.length === 0 || member.squad));
    const agreementsComplete = threads.length === 0 || threads.every((t) => t.read);
    const tasksComplete = tasks.length === 0 || tasks.every((t) => t.complete);

    const formValid = useMemo(() => {
        const hasPending = selectedPending !== null;
        if (hasPending && emailCheck.found && !emailCheck.eligible) return false;
        const idValid =
            hasPending || Boolean(member.id && memberIdValidation.valid && memberIdValidation.verifiedEmail);
        return (
            idValid &&
            Boolean(member.forum_name && member.ingame_name && member.rank && member.platoon) &&
            (selectedPlatoonSquads.length === 0 || Boolean(member.squad)) &&
            forumNameValidation.valid
        );
    }, [selectedPending, emailCheck, member, memberIdValidation, forumNameValidation, selectedPlatoonSquads]);

    const submit = useCallback(async () => {
        if (!formValid || submitting) return;
        setSubmitting(true);
        setSubmitError(null);
        try {
            await postJson('/add-member', {
                division: props.divisionSlug,
                member_id: member.id,
                forum_name: member.forum_name,
                ingame_name: member.ingame_name,
                platoon: member.platoon,
                rank: member.rank,
                squad: member.squad,
                pending_user_id: selectedPending?.id ?? null,
            });
            setSubmitted(true);
            setStep('confirmation');
        } catch (e) {
            setSubmitError(e instanceof Error ? e.message : 'Failed to add recruit. Please try again.');
        } finally {
            setSubmitting(false);
        }
    }, [formValid, submitting, member, props.divisionSlug, selectedPending]);

    const reset = useCallback(() => {
        setStep('form');
        setPath(null);
        setMember(BLANK_MEMBER);
        setMemberIdValidation(EMPTY_MEMBER_ID);
        setForumNameValidation({ valid: false, available: false });
        setEmailCheck(EMPTY_EMAIL_CHECK);
        setSelectedPending(null);
        setThreads(props.threads.map((t) => ({ ...t, read: false })));
        setTasks(props.tasks.map((t) => ({ ...t, complete: false })));
        setSubmitError(null);
        setSubmitted(false);
    }, [props.threads, props.tasks]);

    // deep-link handling
    const resolvedDeepLink = useRef(false);
    useEffect(() => {
        if (resolvedDeepLink.current) return;
        resolvedDeepLink.current = true;
        if (props.memberId) {
            setPath('forum');
            patchMember({ id: String(props.memberId) });
            validateMemberId(String(props.memberId));
        } else if (props.pendingUserId) {
            setPath('discord');
            const match = pending.find((u) => u.id === props.pendingUserId);
            if (match) selectPending(match.id);
            else reloadPending(true);
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const formattedName = member.forum_name ? `AOD_${formatName(member.forum_name)}` : '';

    return {
        props,
        step,
        path,
        setPath,
        member,
        patchMember,
        memberIdValidation,
        forumNameValidation,
        validating,
        emailCheck,
        selectedPending,
        pending,
        loadingPending,
        threads,
        setThreads,
        tasks,
        setTasks,
        submitting,
        submitError,
        submitted,
        selectedPlatoonSquads,
        memberVerificationComplete,
        detailsComplete,
        assignmentComplete,
        agreementsComplete,
        tasksComplete,
        formValid,
        formattedName,
        validateMemberId,
        validateForumName,
        selectPending,
        clearPending,
        reloadPending,
        submit,
        reset,
    };
}

export type RecruitForm = ReturnType<typeof useRecruitForm>;
