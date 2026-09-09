import { Link } from '@inertiajs/react';
import type { ReactNode } from 'react';

import { RankHistoryList, type RankTimelineData } from '@/components/member/rank-timeline';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';

export interface ProfileRef {
    name: string;
    url: string;
}

export interface TenureStats {
    years: number;
    months: number;
    joinDate: string | null;
    recruitedBy: ProfileRef | null;
    trainedOn: string | null;
    trainedBy: ProfileRef | null;
}

export interface ActivityStats {
    daysSinceVoice: number | null;
    health: string;
    healthPct: number;
    divisionMax: number;
    reminders: Array<{ date: string; by: string }>;
    canClearReminders: boolean;
    clearRemindersUrl: string;
}

export interface RecruitingStats {
    total: number;
    active: number;
    retentionRate: number | null;
    recruits: Array<{ name: string; url: string; joinDate: string | null; division: string; active: boolean }>;
}

interface DialogControl {
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

function DetailRow({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="flex items-center justify-between gap-4 border-b border-border py-2 text-sm last:border-0">
            <span className="text-muted-foreground">{label}</span>
            <span className="text-right">{children}</span>
        </div>
    );
}

export function TenureDialog({
    open,
    onOpenChange,
    tenure,
    forumId,
}: DialogControl & { tenure: TenureStats; forumId: number }) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Membership details</DialogTitle>
                    <DialogDescription className="sr-only">Tenure and training details</DialogDescription>
                </DialogHeader>
                <div>
                    <DetailRow label="Time in AOD">
                        {tenure.years}y {tenure.months > 0 && `${tenure.months}m`}
                    </DetailRow>
                    {tenure.joinDate && <DetailRow label="Join date">{tenure.joinDate}</DetailRow>}
                    {tenure.recruitedBy && (
                        <DetailRow label="Recruited by">
                            <Link href={tenure.recruitedBy.url} className="text-primary hover:underline">
                                {tenure.recruitedBy.name}
                            </Link>
                        </DetailRow>
                    )}
                    {tenure.trainedOn && <DetailRow label="Trained on">{tenure.trainedOn}</DetailRow>}
                    {tenure.trainedBy && (
                        <DetailRow label="Trained by">
                            <Link href={tenure.trainedBy.url} className="text-primary hover:underline">
                                {tenure.trainedBy.name}
                            </Link>
                        </DetailRow>
                    )}
                    <DetailRow label="Forum ID">{forumId}</DetailRow>
                </div>
            </DialogContent>
        </Dialog>
    );
}

export function RecruitsDialog({
    open,
    onOpenChange,
    recruiting,
    memberName,
}: DialogControl & { recruiting: RecruitingStats; memberName: string }) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Recruits ({recruiting.total})</DialogTitle>
                    <DialogDescription className="sr-only">Members recruited by {memberName}</DialogDescription>
                </DialogHeader>
                <div className="max-h-[60vh] space-y-1 overflow-y-auto">
                    {recruiting.recruits.map((recruit) => (
                        <div
                            key={recruit.url}
                            className="flex items-center justify-between gap-3 border-b border-border py-2 text-sm last:border-0"
                        >
                            <Link href={recruit.url} className="text-primary hover:underline">
                                {recruit.name}
                            </Link>
                            <span className="text-xs text-muted-foreground">{recruit.joinDate}</span>
                            <span className="text-xs text-muted-foreground">{recruit.division}</span>
                            <Badge variant={recruit.active ? 'default' : 'outline'} className="text-[10px]">
                                {recruit.active ? 'Active' : 'Inactive'}
                            </Badge>
                        </div>
                    ))}
                </div>
            </DialogContent>
        </Dialog>
    );
}

export function ReminderHistoryDialog({
    open,
    onOpenChange,
    activity,
}: DialogControl & { activity: ActivityStats }) {
    const count = activity.reminders.length;
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Reminder history</DialogTitle>
                    <DialogDescription>
                        {count} inactivity reminder{count === 1 ? '' : 's'} sent to this member.
                    </DialogDescription>
                </DialogHeader>
                <ul className="-mx-1 max-h-[55vh] space-y-1.5 overflow-y-auto px-1">
                    {activity.reminders.map((reminder, i) => (
                        <li key={i} className="flex items-center justify-between gap-4 text-sm">
                            <span className="numeric shrink-0 text-xs text-muted-foreground">{reminder.date}</span>
                            <span className="truncate">{reminder.by}</span>
                        </li>
                    ))}
                </ul>
                {activity.canClearReminders && (
                    <Button
                        variant="destructive"
                        size="sm"
                        onClick={() => {
                            // eslint-disable-next-line no-alert
                            if (confirm('Clear all reminders for this member?')) {
                                window.location.href = activity.clearRemindersUrl;
                            }
                        }}
                    >
                        Clear reminders
                    </Button>
                )}
            </DialogContent>
        </Dialog>
    );
}

export function RankHistoryDialog({
    open,
    onOpenChange,
    items,
}: DialogControl & { items: RankTimelineData['historyItems'] }) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-sm">
                <DialogHeader>
                    <DialogTitle>Rank history</DialogTitle>
                    <DialogDescription className="sr-only">Full rank change history</DialogDescription>
                </DialogHeader>
                <RankHistoryList items={items} />
            </DialogContent>
        </Dialog>
    );
}
