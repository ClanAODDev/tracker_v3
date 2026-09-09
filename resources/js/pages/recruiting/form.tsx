import { Head, Link } from '@inertiajs/react';
import {
    ArrowLeft,
    ArrowRight,
    Check,
    ChevronDown,
    ChevronUp,
    CircleCheck,
    Copy,
    IdCard,
    Loader2,
    MessageSquare,
    TriangleAlert,
    Users,
    X,
} from 'lucide-react';
import { type ReactNode, useState } from 'react';
import { toast } from 'sonner';

import type { RecruitForm } from '@/components/recruit/use-recruit-form';
import { useRecruitForm } from '@/components/recruit/use-recruit-form';
import type { RecruitFormProps } from '@/components/recruit/types';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SimpleSelect } from '@/components/ui/simple-select';
import { linkifyHtml } from '@/lib/format';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

export default function RecruitingForm(props: RecruitFormProps) {
    const form = useRecruitForm(props);

    return (
        <AppLayout
            header={{
                eyebrow: 'Recruiting',
                title: `Add recruit — ${props.name}`,
                breadcrumbs: [
                    { label: 'Recruiting', href: '/recruit' },
                    { label: props.name, href: props.cancelUrl },
                    { label: 'Add recruit' },
                ],
            }}
        >
            <Head title={`Add recruit — ${props.name}`} />
            <div className="mx-auto max-w-3xl">
                {form.step === 'form' ? <FormStep form={form} /> : <ConfirmationStep form={form} />}
            </div>
        </AppLayout>
    );
}

function StepDot({ n, complete }: { n: number; complete: boolean }) {
    return (
        <span
            className={cn(
                'ml-auto grid size-5 place-items-center rounded-full border text-[11px]',
                complete ? 'border-success bg-success/15 text-success' : 'border-border text-muted-foreground',
            )}
        >
            {complete ? <Check className="size-3" /> : n}
        </span>
    );
}

function Section({
    icon,
    title,
    step,
    complete,
    collapsible,
    open,
    onToggle,
    children,
}: {
    icon: ReactNode;
    title: string;
    step: number;
    complete: boolean;
    collapsible?: boolean;
    open?: boolean;
    onToggle?: () => void;
    children: ReactNode;
}) {
    return (
        <section className="overflow-hidden rounded-md border border-border">
            <div
                className={cn(
                    'flex items-center gap-2 border-b border-border bg-card/40 px-4 py-2.5 text-sm font-semibold',
                    collapsible && 'cursor-pointer',
                )}
                onClick={collapsible ? onToggle : undefined}
            >
                {icon} {title}
                <StepDot n={step} complete={complete} />
                {collapsible && (open ? <ChevronUp className="size-4" /> : <ChevronDown className="size-4" />)}
            </div>
            {(!collapsible || open) && <div className="space-y-4 p-4">{children}</div>}
        </section>
    );
}

function FormStep({ form }: { form: RecruitForm }) {
    const { props } = form;
    const [attempted, setAttempted] = useState(false);
    const [agreementsOpen, setAgreementsOpen] = useState(true);
    const [tasksOpen, setTasksOpen] = useState(true);
    const [appOpen, setAppOpen] = useState(false);

    return (
        <form
            onSubmit={(e) => {
                e.preventDefault();
                setAttempted(true);
                form.submit();
            }}
            className="space-y-4"
        >
            <Section
                icon={<IdCard className="size-4" />}
                title="Member verification"
                step={1}
                complete={form.memberVerificationComplete}
            >
                {!form.path ? (
                    <div className="space-y-2">
                        <p className="text-sm text-muted-foreground">Does the recruit have a forum account?</p>
                        <div className="grid gap-2 sm:grid-cols-3">
                            <Button type="button" variant="outline" onClick={() => form.setPath('forum')}>
                                <IdCard /> I have their member ID
                            </Button>
                            <Button type="button" variant="outline" onClick={() => form.setPath('discord')}>
                                <MessageSquare /> Registered via Discord
                            </Button>
                            <Button type="button" variant="outline" asChild>
                                <a
                                    href="https://www.clanaod.net/forums/memberlist.php?order=desc&sort=joindate&pp=30"
                                    target="_blank"
                                    rel="noreferrer"
                                >
                                    Look up their account
                                </a>
                            </Button>
                        </div>
                    </div>
                ) : form.path === 'forum' ? (
                    <ForumIdPath form={form} />
                ) : (
                    <DiscordPath form={form} />
                )}
            </Section>

            <Section
                icon={<IdCard className="size-4" />}
                title="Recruit details"
                step={2}
                complete={form.detailsComplete}
            >
                <div className="grid gap-4 sm:grid-cols-3">
                    <div className="grid gap-1.5">
                        <Label htmlFor="forum_name">Forum name *</Label>
                        <div className="flex gap-1.5">
                            <Input
                                id="forum_name"
                                value={form.member.forum_name}
                                onChange={(e) => {
                                    form.patchMember({ forum_name: e.target.value });
                                    form.validateForumName(e.target.value, form.member.id);
                                }}
                                placeholder="Desired forum name"
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="icon-sm"
                                title="Copy to in-game handle"
                                onClick={() => form.patchMember({ ingame_name: form.member.forum_name })}
                            >
                                <ArrowRight />
                            </Button>
                        </div>
                        <ForumNameHint form={form} />
                    </div>
                    <div className="grid gap-1.5">
                        <Label htmlFor="ingame">In-game handle *</Label>
                        <Input
                            id="ingame"
                            value={form.member.ingame_name}
                            onChange={(e) => form.patchMember({ ingame_name: e.target.value })}
                            placeholder="In-game name"
                        />
                    </div>
                    <div className="grid gap-1.5">
                        <Label>Rank *</Label>
                        <SimpleSelect
                            value={form.member.rank || '__all'}
                            onChange={(v) => form.patchMember({ rank: v === '__all' ? '' : v })}
                            placeholder="Select rank…"
                            options={[
                                { value: '__all', label: 'Select rank…' },
                                ...Object.entries(props.ranks).map(([id, name]) => ({ value: id, label: name })),
                            ]}
                        />
                    </div>
                </div>
            </Section>

            {props.platoons.length > 0 && (
                <Section
                    icon={<Users className="size-4" />}
                    title="Assignment"
                    step={3}
                    complete={form.assignmentComplete}
                >
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="grid gap-1.5">
                            <Label>{props.locality.platoon} *</Label>
                            <SimpleSelect
                                value={form.member.platoon || '__all'}
                                onChange={(v) =>
                                    form.patchMember({ platoon: v === '__all' ? '' : v, squad: '' })
                                }
                                placeholder={`Select ${props.locality.platoon.toLowerCase()}…`}
                                options={[
                                    { value: '__all', label: `Select ${props.locality.platoon.toLowerCase()}…` },
                                    ...props.platoons.map((p) => ({
                                        value: String(p.id),
                                        label: `${p.name} (${p.members_count})${p.leader_name ? ` — ${p.leader_name}` : ''}`,
                                    })),
                                ]}
                            />
                        </div>
                        <div className="grid gap-1.5">
                            <Label>
                                {props.locality.squad}
                                {form.selectedPlatoonSquads.length > 0 && ' *'}
                            </Label>
                            <SimpleSelect
                                value={form.member.squad || '__all'}
                                onChange={(v) => form.patchMember({ squad: v === '__all' ? '' : v })}
                                placeholder={`No ${props.locality.squad.toLowerCase()}s available`}
                                options={[
                                    {
                                        value: '__all',
                                        label:
                                            form.selectedPlatoonSquads.length > 0
                                                ? `Select ${props.locality.squad.toLowerCase()}…`
                                                : `No ${props.locality.squad.toLowerCase()}s available`,
                                    },
                                    ...form.selectedPlatoonSquads.map((s) => ({
                                        value: String(s.id),
                                        label: `${s.name || `Squad #${s.id}`} (${s.members_count})${s.leader_name ? ` — ${s.leader_name}` : ''}`,
                                    })),
                                ]}
                            />
                        </div>
                    </div>
                </Section>
            )}

            {form.threads.length > 0 && (
                <Section
                    icon={<Check className="size-4" />}
                    title="Agreements"
                    step={4}
                    complete={form.agreementsComplete}
                    collapsible
                    open={agreementsOpen}
                    onToggle={() => setAgreementsOpen((v) => !v)}
                >
                    <p className="text-sm text-muted-foreground">Confirm the recruit has read the following:</p>
                    {form.threads.map((thread, i) => (
                        <div key={i} className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={thread.read}
                                onChange={(e) =>
                                    form.setThreads((prev) =>
                                        prev.map((t, idx) => (idx === i ? { ...t, read: e.target.checked } : t)),
                                    )
                                }
                            />
                            <div className="flex-1">
                                <a href={thread.url} target="_blank" rel="noreferrer" className="text-primary hover:underline">
                                    {thread.name}
                                </a>
                                {thread.comments && (
                                    <span className="ml-2 text-xs text-muted-foreground">{thread.comments}</span>
                                )}
                            </div>
                            <Button
                                type="button"
                                size="icon-xs"
                                variant="ghost"
                                onClick={() =>
                                    navigator.clipboard.writeText(thread.url).then(() => {
                                        form.setThreads((prev) =>
                                            prev.map((t, idx) => (idx === i ? { ...t, read: true } : t)),
                                        );
                                        toast.success('URL copied');
                                    })
                                }
                            >
                                <Copy />
                            </Button>
                        </div>
                    ))}
                </Section>
            )}

            {form.tasks.length > 0 && (
                <Section
                    icon={<Check className="size-4" />}
                    title="In-processing tasks"
                    step={5}
                    complete={form.tasksComplete}
                    collapsible
                    open={tasksOpen}
                    onToggle={() => setTasksOpen((v) => !v)}
                >
                    <p className="text-sm text-muted-foreground">Checklist for onboarding the new recruit:</p>
                    {form.tasks.map((task, i) => (
                        <label key={i} className="flex items-start gap-2 text-sm">
                            <input
                                type="checkbox"
                                checked={task.complete}
                                onChange={(e) =>
                                    form.setTasks((prev) =>
                                        prev.map((t, idx) => (idx === i ? { ...t, complete: e.target.checked } : t)),
                                    )
                                }
                            />
                            <span
                                className="[&_a]:text-primary [&_a]:underline-offset-2 hover:[&_a]:underline"
                                dangerouslySetInnerHTML={{ __html: linkifyHtml(task.description) }}
                            />
                        </label>
                    ))}
                </Section>
            )}

            {form.selectedPending?.application && (
                <Section
                    icon={<MessageSquare className="size-4" />}
                    title="Application submitted"
                    step={0}
                    complete
                    collapsible
                    open={appOpen}
                    onToggle={() => setAppOpen((v) => !v)}
                >
                    <div className="grid gap-3 sm:grid-cols-2">
                        {form.selectedPending.application.map((item, i) => (
                            <div key={i}>
                                <p className="text-[11px] uppercase tracking-wide text-muted-foreground">
                                    {item.label}
                                </p>
                                <p className="text-sm">{item.value || '—'}</p>
                            </div>
                        ))}
                    </div>
                </Section>
            )}

            {form.submitError && (
                <p className="rounded-md border border-destructive/40 bg-destructive/5 px-4 py-2 text-sm">
                    {form.submitError}
                </p>
            )}

            <div className="flex justify-end gap-2">
                <Button type="button" variant="outline" size="sm" asChild>
                    <Link href={props.cancelUrl}>Cancel</Link>
                </Button>
                <Button type="submit" size="sm" disabled={!form.formValid || form.submitting}>
                    {form.submitting ? <Loader2 className="animate-spin" /> : <ArrowRight />}
                    {form.submitting ? 'Adding recruit…' : 'Add recruit'}
                </Button>
            </div>
            {attempted && !form.formValid && (
                <p className="text-right text-xs text-muted-foreground">Complete the required fields above.</p>
            )}
        </form>
    );
}

function ForumIdPath({ form }: { form: RecruitForm }) {
    const v = form.memberIdValidation;
    return (
        <div className="space-y-3">
            <button
                type="button"
                onClick={() => form.setPath(null)}
                className="flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft className="size-3" /> Back
            </button>
            <div className="grid gap-4 sm:grid-cols-2">
                <div className="grid gap-1.5">
                    <Label htmlFor="member_id">Forum member ID *</Label>
                    <div className="relative">
                        <Input
                            id="member_id"
                            type="number"
                            value={form.member.id}
                            onChange={(e) => {
                                form.patchMember({ id: e.target.value });
                                form.validateMemberId(e.target.value);
                            }}
                            placeholder="e.g. 12345"
                        />
                        {form.member.id && (
                            <span className="absolute right-2.5 top-1/2 -translate-y-1/2">
                                {form.validating ? (
                                    <Loader2 className="size-4 animate-spin text-muted-foreground" />
                                ) : v.valid && v.verifiedEmail ? (
                                    <Check className="size-4 text-success" />
                                ) : (
                                    <X className="size-4 text-destructive" />
                                )}
                            </span>
                        )}
                    </div>
                    {form.member.id && !v.valid && !form.validating && (
                        <p className="text-xs text-destructive">Member ID not found on forums</p>
                    )}
                    {form.member.id && v.valid && !v.verifiedEmail && !form.validating && (
                        <p className="text-xs text-warning">
                            {v.groupId === 3
                                ? 'Member needs to validate their email address before recruitment.'
                                : 'Member is not in the Registered Users group.'}
                        </p>
                    )}
                </div>
                {v.currentUsername && (
                    <div className="grid gap-1.5">
                        <Label>Current forum username</Label>
                        <p className="text-sm">
                            {v.currentUsername}
                            {v.existsInTracker && (
                                <span className="ml-2 rounded bg-muted px-1.5 py-0.5 text-xs text-muted-foreground">
                                    Previous member
                                </span>
                            )}
                        </p>
                        {v.tags.length > 0 && (
                            <div className="flex flex-wrap gap-1">
                                {v.tags.map((tag, i) => (
                                    <span
                                        key={i}
                                        title={tag.division}
                                        className="rounded bg-muted px-1.5 py-0.5 text-[11px] text-muted-foreground"
                                    >
                                        {tag.name}
                                    </span>
                                ))}
                            </div>
                        )}
                        {v.discordUsername && (
                            <p className="text-xs text-muted-foreground">
                                Linked Discord: <strong>{v.discordUsername}</strong>
                            </p>
                        )}
                        {v.discordMatches.length > 0 && (
                            <p className="text-xs text-warning">
                                Discord ID matches existing member{v.discordMatches.length > 1 ? 's' : ''}:{' '}
                                {v.discordMatches.map((m) => (
                                    <a key={m.clan_id} href={m.url} target="_blank" rel="noreferrer" className="mr-2 underline">
                                        {m.name} ({m.division ?? 'Ex-AOD'})
                                    </a>
                                ))}
                            </p>
                        )}
                    </div>
                )}
            </div>
        </div>
    );
}

function DiscordPath({ form }: { form: RecruitForm }) {
    const [showAll, setShowAll] = useState(false);
    const user = form.selectedPending;

    return (
        <div className="space-y-3">
            <button
                type="button"
                onClick={() => {
                    form.setPath(null);
                    form.clearPending();
                }}
                className="flex items-center gap-1 text-xs text-muted-foreground hover:text-foreground"
            >
                <ArrowLeft className="size-3" /> Back
            </button>

            {!user ? (
                <div className="grid gap-1.5">
                    <Label>Pending Discord registrations ({form.pending.length})</Label>
                    <SimpleSelect
                        value="__all"
                        onChange={(v) => v !== '__all' && form.selectPending(Number(v))}
                        placeholder={form.loadingPending ? 'Loading…' : 'Select a pending registration…'}
                        options={[
                            {
                                value: '__all',
                                label: form.loadingPending ? 'Loading…' : 'Select a pending registration…',
                            },
                            ...form.pending.map((u) => ({
                                value: String(u.id),
                                label: `${u.discord_username} (${u.created_at})${u.application_division ? ` — ${u.application_division}` : ''}`,
                            })),
                        ]}
                    />
                    <label className="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                        <input
                            type="checkbox"
                            checked={showAll}
                            onChange={(e) => {
                                setShowAll(e.target.checked);
                                form.clearPending();
                                form.reloadPending(e.target.checked);
                            }}
                        />
                        Show all pending registrations
                    </label>
                    {form.pending.length === 0 && !form.loadingPending && (
                        <p className="text-xs text-muted-foreground">
                            No pending Discord registrations found for this division.
                        </p>
                    )}
                </div>
            ) : (
                <div className="rounded-md border border-border p-3">
                    <div className="flex items-center gap-2">
                        {user.avatar_url ? (
                            <img src={user.avatar_url} alt="" className="size-6 rounded-full" />
                        ) : (
                            <MessageSquare className="size-4 text-info" />
                        )}
                        <strong className="text-sm">{user.discord_username}</strong>
                        <button
                            type="button"
                            onClick={() => form.clearPending()}
                            className="ml-auto text-xs text-muted-foreground hover:text-foreground"
                        >
                            <X className="mr-1 inline size-3" /> Change
                        </button>
                    </div>
                    <div className="mt-2 text-sm">
                        {form.emailCheck.loading ? (
                            <p className="flex items-center gap-2 text-muted-foreground">
                                <Loader2 className="size-4 animate-spin" /> Checking for existing forum account…
                            </p>
                        ) : form.emailCheck.checked && form.emailCheck.found && form.emailCheck.eligible ? (
                            <p className="flex items-start gap-2 text-success">
                                <CircleCheck className="mt-0.5 size-4 shrink-0" />
                                <span>
                                    Existing forum account: <strong>{form.emailCheck.username}</strong> (ID{' '}
                                    {form.emailCheck.userId}). It will be used for recruitment.
                                </span>
                            </p>
                        ) : form.emailCheck.checked && form.emailCheck.found && !form.emailCheck.eligible ? (
                            <p className="flex items-start gap-2 text-warning">
                                <TriangleAlert className="mt-0.5 size-4 shrink-0" />
                                <span>
                                    A forum account exists (<strong>{form.emailCheck.username}</strong>) but is not
                                    eligible:{' '}
                                    {form.emailCheck.groupId === 3
                                        ? 'pending email verification.'
                                        : form.emailCheck.rejectionReason}
                                </span>
                            </p>
                        ) : form.emailCheck.checked && !form.emailCheck.found ? (
                            <p className="text-muted-foreground">
                                No existing forum account found. One will be created during recruitment.
                            </p>
                        ) : null}
                    </div>
                </div>
            )}
        </div>
    );
}

function ForumNameHint({ form }: { form: RecruitForm }) {
    const { member, forumNameValidation: v, validating, formattedName } = form;
    if (!member.forum_name) return null;
    if (validating) return <p className="text-xs text-muted-foreground">Checking…</p>;
    if (v.rejectedPrefix === 'aod') return <p className="text-xs text-destructive">Do not include the "AOD_" prefix</p>;
    if (v.rejectedPrefix === 'rank')
        return <p className="text-xs text-destructive">Name cannot begin with a rank abbreviation</p>;
    if (v.invalidChars) return <p className="text-xs text-destructive">Name cannot contain special characters</p>;
    if (!v.available) return <p className="text-xs text-destructive">This name is already taken</p>;
    if (v.valid && !v.existingAccount)
        return (
            <p className="text-xs text-muted-foreground">
                Will become: <strong>{formattedName}</strong>
            </p>
        );
    return null;
}

function ConfirmationStep({ form }: { form: RecruitForm }) {
    const { props, member } = form;
    const platoon = props.platoons.find((p) => p.id === Number(member.platoon));
    const squad = platoon?.squads.find((s) => s.id === Number(member.squad));
    const assignment = [platoon?.name, squad?.name].filter(Boolean).join(' › ');
    const welcomePm = (props.welcome_pm || '')
        .replace(/\{\{\s*name\s*\}\}/g, member.forum_name)
        .replace(/\{\{\s*ingame_name\s*\}\}/g, member.ingame_name);

    return (
        <div className="space-y-6">
            <div className="rounded-md border border-success/40 bg-success/5 p-6 text-center">
                <CircleCheck className="mx-auto size-10 text-success" />
                <h2 className="mt-2 text-lg font-semibold">Recruit added successfully</h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    <strong>{form.formattedName}</strong> has been added to the division.
                    {assignment && (
                        <>
                            <br />
                            Assigned to: {assignment}
                        </>
                    )}
                </p>
            </div>

            {(props.welcome_area || props.welcome_pm) && (
                <section className="overflow-hidden rounded-md border border-border">
                    <h3 className="border-b border-border bg-card/40 px-4 py-2 text-sm font-semibold">Housekeeping</h3>
                    <div className="space-y-4 p-4">
                        {props.welcome_area && (
                            <div>
                                <p className="text-sm font-medium">Create welcome post</p>
                                <Button size="sm" variant="outline" className="mt-1.5" asChild>
                                    <a
                                        href={
                                            props.use_welcome_thread
                                                ? `https://www.clanaod.net/forums/newreply.php?do=newreply&t=${props.welcome_area}`
                                                : `https://www.clanaod.net/forums/newthread.php?do=newthread&f=${props.welcome_area}`
                                        }
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        {props.use_welcome_thread ? 'Create post' : 'Create thread'}
                                    </a>
                                </Button>
                            </div>
                        )}
                        {props.welcome_pm && (
                            <div>
                                <p className="text-sm font-medium">Send welcome DM</p>
                                <textarea
                                    readOnly
                                    rows={4}
                                    value={welcomePm}
                                    className="mt-1.5 w-full rounded-md border border-border bg-transparent p-2 text-sm"
                                />
                                <div className="mt-1.5 flex gap-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() =>
                                            navigator.clipboard.writeText(welcomePm).then(() => toast.success('Copied'))
                                        }
                                    >
                                        <Copy /> Copy
                                    </Button>
                                    <Button size="sm" variant="outline" asChild>
                                        <a
                                            href={`https://clanaod.net/forums/private.php?do=newpm&u=${member.id}`}
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            Send forum PM
                                        </a>
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                </section>
            )}

            <div className="flex justify-end gap-2">
                <Button variant="outline" size="sm" onClick={form.reset}>
                    Add another recruit
                </Button>
                <Button size="sm" asChild>
                    <Link href={props.cancelUrl}>
                        <ArrowLeft /> Back to division
                    </Link>
                </Button>
            </div>
        </div>
    );
}
