import { ArrowLeft, Check, Loader2, X } from 'lucide-react';

import type { RecruitForm } from '@/components/recruit/use-recruit-form';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function ForumIdPath({ form }: { form: RecruitForm }) {
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
