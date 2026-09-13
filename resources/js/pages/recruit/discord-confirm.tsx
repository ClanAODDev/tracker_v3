import { Head, Link, router } from '@inertiajs/react';
import { ArrowRight, CheckCircle2, Clock, Hash, History, Info, Mail, TriangleAlert, User } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { SimpleSelect } from '@/components/ui/simple-select';
import AppLayout from '@/layouts/AppLayout';

interface ForumAccount {
    found: boolean;
    eligible: boolean;
    username?: string;
    user_id?: number;
    rejection_reason?: string;
}

interface PendingUser {
    id: number;
    discord_username: string;
    avatar_url: string;
    created_at: string;
    obfuscated_email: string | null;
    application: Array<{ label: string; value: string }> | null;
}

interface MemberMatch {
    name: string;
    clan_id: number;
    division: string | null;
    url: string;
    isExMember: boolean;
    avatarUrl: string | null;
}

interface DivisionOption {
    name: string;
    formPath: string;
}

interface Props {
    discordId: string;
    backUrl: string;
    pendingUser: PendingUser | null;
    forumAccount: ForumAccount | null;
    targetDivision: { name: string; logo: string; recruitFormUrl: string } | null;
    memberMatches: MemberMatch[] | null;
    divisions: DivisionOption[] | null;
    pendingUserId: number | null;
}

function DivisionPicker({ options, query }: { options: DivisionOption[]; query: string }) {
    const [choice, setChoice] = useState('');

    return (
        <div className="flex flex-wrap items-center gap-2">
            <SimpleSelect
                value={choice || '__none'}
                onChange={(v) => setChoice(v === '__none' ? '' : v)}
                options={[
                    { value: '__none', label: 'Select division…' },
                    ...options.map((option) => ({ value: option.formPath, label: option.name })),
                ]}
                className="w-56"
            />
            <Button size="sm" disabled={!choice} onClick={() => router.visit(`${choice}${query}`)}>
                Proceed <ArrowRight />
            </Button>
        </div>
    );
}

function Card({ children }: { children: React.ReactNode }) {
    return <div className="tron-corners mx-auto max-w-2xl rounded-md border border-border bg-card">{children}</div>;
}

export default function DiscordConfirm({
    discordId,
    backUrl,
    pendingUser,
    forumAccount,
    targetDivision,
    memberMatches,
    divisions,
    pendingUserId,
}: Props) {
    return (
        <AppLayout
            header={{
                eyebrow: 'Recruitment',
                title: 'Confirm Discord recruit',
                breadcrumbs: [{ label: 'Recruitment', href: backUrl }, { label: 'Confirm Discord recruit' }],
            }}
        >
            <Head title="Confirm Discord recruit" />

            {pendingUser ? (
                <Card>
                    <div className="flex flex-wrap items-center gap-4 border-b border-border p-5">
                        <img src={pendingUser.avatar_url} alt="" className="size-12 rounded-full" />
                        <div className="flex-1">
                            <h2 className="text-base font-semibold">{pendingUser.discord_username}</h2>
                            <div className="mt-1 flex flex-wrap gap-3 text-xs text-muted-foreground">
                                <span className="flex items-center gap-1">
                                    <Clock className="size-3" /> Applied {pendingUser.created_at}
                                </span>
                                {pendingUser.obfuscated_email && (
                                    <span className="flex items-center gap-1">
                                        <Mail className="size-3" /> {pendingUser.obfuscated_email}
                                    </span>
                                )}
                            </div>
                        </div>
                        {targetDivision && (
                            <span className="flex items-center gap-2 rounded-md border border-border px-2.5 py-1 text-xs">
                                <img src={targetDivision.logo} alt="" className="size-4" />
                                {targetDivision.name} Division
                            </span>
                        )}
                    </div>

                    <div className="border-b border-border p-5 text-sm">
                        {forumAccount?.found ? (
                            forumAccount.eligible ? (
                                <p className="flex items-start gap-2 text-success">
                                    <CheckCircle2 className="mt-0.5 size-4 shrink-0" />
                                    <span>
                                        Forum account found: <strong>{forumAccount.username}</strong> (ID:{' '}
                                        {forumAccount.user_id})
                                    </span>
                                </p>
                            ) : (
                                <p className="flex items-start gap-2 text-warning">
                                    <TriangleAlert className="mt-0.5 size-4 shrink-0" />
                                    <span>
                                        A forum account was found: <strong>{forumAccount.username}</strong>
                                        <span className="mt-0.5 block text-xs text-muted-foreground">
                                            {forumAccount.rejection_reason}
                                        </span>
                                    </span>
                                </p>
                            )
                        ) : (
                            <p className="flex items-start gap-2 text-muted-foreground">
                                <Info className="mt-0.5 size-4 shrink-0" />
                                No existing forum account found. One will be created during recruitment.
                            </p>
                        )}
                    </div>

                    {pendingUser.application && (
                        <div className="border-b border-border p-5">
                            <h3 className="mb-3 text-xs font-semibold uppercase tracking-wide text-primary">
                                Application responses
                            </h3>
                            <div className="grid gap-3 sm:grid-cols-2">
                                {pendingUser.application.map((item, i) => (
                                    <div key={i}>
                                        <div className="text-xs text-muted-foreground">{item.label}</div>
                                        <div className="text-sm">{item.value || '—'}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    <div className="p-5">
                        {targetDivision ? (
                            <Button asChild>
                                <Link href={targetDivision.recruitFormUrl}>
                                    Start recruitment <ArrowRight />
                                </Link>
                            </Button>
                        ) : (
                            <>
                                <p className="mb-3 text-sm text-muted-foreground">
                                    No division application on file — select which division to recruit them into:
                                </p>
                                {divisions && (
                                    <DivisionPicker
                                        options={divisions}
                                        query={`?pending_user_id=${pendingUserId}`}
                                    />
                                )}
                            </>
                        )}
                    </div>
                </Card>
            ) : memberMatches && memberMatches.length > 0 ? (
                <Card>
                    <div className="border-b border-border p-5">
                        <h2 className="text-base font-semibold">No pending Discord application</h2>
                        <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                            <Hash className="size-3" /> {discordId}
                        </p>
                        <p className="mt-3 text-sm text-muted-foreground">
                            This Discord account isn't tied to a pending registration, but it matches{' '}
                            {memberMatches.length > 1 ? 'these member records' : 'a member record'} already in the
                            Tracker:
                        </p>
                    </div>

                    {memberMatches.map((match) => (
                        <div
                            key={match.clan_id}
                            className="flex flex-wrap items-center gap-3 border-b border-border p-5"
                        >
                            {match.avatarUrl ? (
                                <img src={match.avatarUrl} alt="" className="size-10 rounded-full" />
                            ) : (
                                <div className="flex size-10 items-center justify-center rounded-full bg-muted">
                                    {match.isExMember ? (
                                        <History className="size-4" />
                                    ) : (
                                        <User className="size-4" />
                                    )}
                                </div>
                            )}
                            <div className="flex-1">
                                <a
                                    href={match.url}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="font-semibold hover:text-primary"
                                >
                                    {match.name}
                                </a>
                                <div className="text-xs text-muted-foreground">
                                    {match.isExMember
                                        ? 'Former member — no longer in a division'
                                        : `Active member of ${match.division}`}
                                </div>
                            </div>
                            {match.isExMember && divisions ? (
                                <DivisionPicker options={divisions} query={`?member_id=${match.clan_id}`} />
                            ) : (
                                <span className="text-xs text-muted-foreground">
                                    Already active — no recruitment needed
                                </span>
                            )}
                        </div>
                    ))}

                    <div className="flex flex-wrap items-center justify-between gap-2 p-5">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={backUrl}>Back to recruitment</Link>
                        </Button>
                        <span className="text-xs text-muted-foreground">
                            None of these? Have the recruit apply via{' '}
                            <a href="https://clanaod.net" target="_blank" rel="noreferrer" className="text-primary">
                                clanaod.net
                            </a>
                            .
                        </span>
                    </div>
                </Card>
            ) : (
                <Card>
                    <div className="border-b border-border p-5">
                        <h2 className="text-base font-semibold">No pending registration found</h2>
                        <p className="mt-1 flex items-center gap-1 text-xs text-muted-foreground">
                            <Hash className="size-3" /> {discordId}
                        </p>
                    </div>
                    <div className="border-b border-border p-5 text-sm text-muted-foreground">
                        This Discord account isn't tied to a pending registration or an existing member record. Have the
                        recruit apply to AOD via{' '}
                        <a href="https://clanaod.net" target="_blank" rel="noreferrer" className="text-primary">
                            clanaod.net
                        </a>{' '}
                        and click "Apply".
                    </div>
                    <div className="p-5">
                        <Button variant="outline" size="sm" asChild>
                            <Link href={backUrl}>Back to recruitment</Link>
                        </Button>
                    </div>
                </Card>
            )}
        </AppLayout>
    );
}
