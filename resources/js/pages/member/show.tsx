import { Head } from '@inertiajs/react';
import { ListOrdered, TriangleAlert, Wrench } from 'lucide-react';
import { useState } from 'react';

import { NotesDialog, type MemberNote } from '@/components/member/notes';
import {
    RankHistoryDialog,
    RecruitsDialog,
    ReminderHistoryDialog,
    TenureDialog,
} from '@/components/member/profile-dialogs';
import {
    Achievements,
    DivisionComparison,
    DivisionsSection,
    HandlesSection,
    ProfileStats,
    type AwardsData,
    type ComparisonData,
    type DivisionsData,
    type HandlesData,
    type ProfileStatsData,
} from '@/components/member/profile-sections';
import { RankTimeline, type RankTimelineData } from '@/components/member/rank-timeline';
import { MemberTagEditor, type DisplayTag, type TagManagement } from '@/components/member/tag-editor';
import { SectionTitle } from '@/components/section';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout';
import type { MemberCard } from '@/types';

interface MemberShowProps {
    member: MemberCard & {
        status: 'pending' | 'ex-aod' | 'active';
        forumPmUrl: string;
        forumProfileUrl: string;
        hasAccount: boolean;
    };
    actions: Array<{ label: string; url: string; external?: boolean }>;
    tags: DisplayTag[];
    tagManagement: TagManagement | null;
    breadcrumbs: Array<{ label: string; href?: string }>;
    notices: Array<{ type: string; message: string; ctaLabel?: string; ctaUrl?: string }>;
    canCreateNote: boolean;
    canViewTrashed: boolean;
    noteTypes: Record<string, string>;
    stats: ProfileStatsData;
    awards: AwardsData;
    rankTimeline: RankTimelineData;
    canFullHistory: boolean;
    divisionComparison: ComparisonData | null;
    handles: HandlesData;
    divisions: DivisionsData;
    notes: MemberNote[];
    trashedNotes: MemberNote[];
}

export default function MemberShow(props: MemberShowProps) {
    const { member, actions, tags, tagManagement, breadcrumbs, notices, stats, awards, rankTimeline, canFullHistory } =
        props;
    const { divisionComparison, handles, divisions, notes, trashedNotes, noteTypes, canViewTrashed, canCreateNote } =
        props;

    const [tenureOpen, setTenureOpen] = useState(false);
    const [recruitsOpen, setRecruitsOpen] = useState(false);
    const [remindersOpen, setRemindersOpen] = useState(false);
    const [historyOpen, setHistoryOpen] = useState(false);
    const [notesOpen, setNotesOpen] = useState(false);

    const statusLabel =
        member.status === 'pending'
            ? 'Pending'
            : member.status === 'ex-aod'
              ? 'Ex-AOD'
              : (member.position ?? 'No position');

    const actionMenu = (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button size="sm" variant="outline">
                    <Wrench /> Actions
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {actions.map((action) => (
                    <DropdownMenuItem key={action.label} asChild>
                        <a href={action.url} target={action.external ? '_blank' : undefined}>
                            {action.label}
                        </a>
                    </DropdownMenuItem>
                ))}
                {actions.length > 0 && <DropdownMenuSeparator />}
                <DropdownMenuItem asChild>
                    <a href={member.forumPmUrl} target="_blank" rel="noreferrer">
                        Send forum PM
                    </a>
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href={member.forumProfileUrl} target="_blank" rel="noreferrer">
                        View forum profile
                    </a>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );

    return (
        <AppLayout
            header={{
                eyebrow: statusLabel,
                title: member.rankName,
                breadcrumbs,
                actions: actionMenu,
            }}
        >
            <Head title={member.rankName} />

            <div className="tron-stagger space-y-8">
                <div className="flex flex-wrap items-center gap-3">
                    {member.avatarUrl && <img src={member.avatarUrl} alt="" className="size-12 rounded-full" />}
                    <MemberTagEditor tags={tags} management={tagManagement} />
                </div>

                {notices.length > 0 && (
                    <div className="space-y-2">
                        {notices.map((notice, i) => (
                            <div
                                key={i}
                                className="flex flex-wrap items-center gap-3 rounded-md border border-warning/40 bg-warning/5 px-4 py-3 text-sm"
                            >
                                <TriangleAlert className="size-4 shrink-0 text-warning" />
                                <span className="flex-1">{notice.message}</span>
                                {notice.ctaLabel && notice.ctaUrl && (
                                    <Button size="xs" variant="outline" asChild>
                                        <a href={notice.ctaUrl}>{notice.ctaLabel}</a>
                                    </Button>
                                )}
                            </div>
                        ))}
                    </div>
                )}

                <ProfileStats
                    stats={stats}
                    canCreateNote={canCreateNote}
                    onTenure={() => setTenureOpen(true)}
                    onRecruits={() => setRecruitsOpen(true)}
                    onReminders={() => setRemindersOpen(true)}
                    onNotes={() => setNotesOpen(true)}
                />

                <section>
                    <SectionTitle
                        divider
                        action={
                            canFullHistory && rankTimeline.historyItems.length > 1 ? (
                                <Button size="xs" variant="ghost" onClick={() => setHistoryOpen(true)}>
                                    <ListOrdered /> Full history
                                </Button>
                            ) : undefined
                        }
                    >
                        Rank progression
                    </SectionTitle>
                    <div className="rounded-md border border-border bg-card p-5">
                        <RankTimeline timeline={rankTimeline} />
                    </div>
                </section>

                {divisionComparison && <DivisionComparison data={divisionComparison} />}

                {awards.list.length > 0 && <Achievements awards={awards} />}

                {(handles.discord || handles.groups.length > 0) && <HandlesSection handles={handles} />}

                {(divisions.current || divisions.partTime.length > 0) && <DivisionsSection divisions={divisions} />}
            </div>

            {canCreateNote && (
                <NotesDialog
                    memberClanId={member.clanId}
                    notes={notes}
                    trashedNotes={trashedNotes}
                    noteTypes={noteTypes}
                    canViewTrashed={canViewTrashed}
                    open={notesOpen}
                    onOpenChange={setNotesOpen}
                />
            )}

            <TenureDialog open={tenureOpen} onOpenChange={setTenureOpen} tenure={stats.tenure} forumId={member.clanId} />
            <RecruitsDialog
                open={recruitsOpen}
                onOpenChange={setRecruitsOpen}
                recruiting={stats.recruiting}
                memberName={member.name}
            />
            <ReminderHistoryDialog open={remindersOpen} onOpenChange={setRemindersOpen} activity={stats.activity} />
            <RankHistoryDialog open={historyOpen} onOpenChange={setHistoryOpen} items={rankTimeline.historyItems} />
        </AppLayout>
    );
}
