import { ArrowLeft, CircleCheck, Loader2, MessageSquare, TriangleAlert, X } from 'lucide-react';
import { useState } from 'react';

import type { RecruitForm } from '@/components/recruit/use-recruit-form';
import { SimpleSelect } from '@/components/ui/simple-select';
import { Label } from '@/components/ui/label';

export function DiscordPath({ form }: { form: RecruitForm }) {
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
