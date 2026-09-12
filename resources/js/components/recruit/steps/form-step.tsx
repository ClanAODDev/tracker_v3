import { Link } from '@inertiajs/react';
import { ArrowRight, Check, Copy, IdCard, Loader2, MessageSquare, Users } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { DiscordPath } from '@/components/recruit/steps/discord-path';
import { ForumIdPath } from '@/components/recruit/steps/forum-id-path';
import { ForumNameHint } from '@/components/recruit/steps/forum-name-hint';
import { Section } from '@/components/recruit/steps/section';
import type { RecruitForm } from '@/components/recruit/use-recruit-form';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { SimpleSelect } from '@/components/ui/simple-select';
import { linkifyHtml } from '@/lib/format';

export function FormStep({ form }: { form: RecruitForm }) {
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
