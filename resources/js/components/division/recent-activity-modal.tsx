import { Link } from '@inertiajs/react';
import {
    ArrowLeftRight,
    ChevronDown,
    Circle,
    CircleCheck,
    CircleMinus,
    CirclePlus,
    Clock,
    Flag,
    FlagOff,
    History,
    ListOrdered,
    Shield,
    UserMinus,
    UserPlus,
    type LucideIcon,
} from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { toneBorder, toneFill, toneText } from '@/lib/tone';
import { cn } from '@/lib/utils';

interface ActivityTarget {
    name: string;
    url: string | null;
}

export interface RecentActivityGroup {
    icon: string;
    tone: string;
    description: string;
    count: number;
    timeAgo: string;
    targets: ActivityTarget[];
}

interface Props {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    groups: RecentActivityGroup[];
    canViewAll: boolean;
    allActivityUrl: string;
}

const ICONS: Record<string, LucideIcon> = {
    'user-plus': UserPlus,
    'user-minus': UserMinus,
    'arrow-left-right': ArrowLeftRight,
    flag: Flag,
    'flag-off': FlagOff,
    clock: Clock,
    'circle-check': CircleCheck,
    'circle-plus': CirclePlus,
    'circle-minus': CircleMinus,
    shield: Shield,
    circle: Circle,
};

function TargetName({ target }: { target: ActivityTarget }) {
    if (!target.url) {
        return <span className="text-muted-foreground">{target.name}</span>;
    }
    return (
        <Link href={target.url} className="font-medium text-primary hover:underline">
            {target.name}
        </Link>
    );
}

function ActivityRow({ group, index }: { group: RecentActivityGroup; index: number }) {
    const [expanded, setExpanded] = useState(false);
    const Icon = ICONS[group.icon] ?? Circle;
    const grouped = group.count > 1;

    return (
        <li className="flex gap-3 px-4 py-3">
            <span
                className={cn(
                    'mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full border',
                    toneFill(group.tone),
                    toneBorder(group.tone),
                )}
            >
                <Icon className={cn('size-3.5', toneText(group.tone))} />
            </span>

            <div className="min-w-0 flex-1">
                <div className="flex items-baseline gap-2">
                    <div className="min-w-0 flex-1 text-sm">
                        {grouped ? (
                            <button
                                type="button"
                                onClick={() => setExpanded((v) => !v)}
                                className="inline-flex items-center gap-1.5 text-left hover:text-foreground"
                                aria-expanded={expanded}
                                aria-controls={`activity-group-${index}`}
                            >
                                <span>
                                    <strong className="font-semibold">{group.count}</strong> members {group.description}
                                </span>
                                <ChevronDown
                                    className={cn('size-3.5 transition-transform', expanded && 'rotate-180')}
                                />
                            </button>
                        ) : (
                            <span>
                                <TargetName target={group.targets[0] ?? { name: 'Unknown', url: null }} />{' '}
                                {group.description}
                            </span>
                        )}
                    </div>
                    <span className="shrink-0 text-xs text-muted-foreground">{group.timeAgo}</span>
                </div>

                {grouped && expanded && (
                    <ul id={`activity-group-${index}`} className="mt-1.5 space-y-1 border-l border-border pl-3 text-sm">
                        {group.targets.map((target, i) => (
                            <li key={i}>
                                <TargetName target={target} />
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </li>
    );
}

export function RecentActivityModal({ open, onOpenChange, groups, canViewAll, allActivityUrl }: Props) {
    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent aria-describedby={undefined} className="max-h-[85vh] max-w-xl gap-0 overflow-hidden p-0">
                <DialogHeader className="border-b border-border px-6 py-4">
                    <DialogTitle className="flex items-center gap-2">
                        <History className="size-5 text-primary" /> Recent Activity
                    </DialogTitle>
                </DialogHeader>

                <div className="max-h-[60vh] overflow-y-auto">
                    {groups.length === 0 ? (
                        <p className="px-6 py-10 text-center text-sm text-muted-foreground">No recent activity.</p>
                    ) : (
                        <ul className="divide-y divide-border">
                            {groups.map((group, i) => (
                                <ActivityRow key={i} group={group} index={i} />
                            ))}
                        </ul>
                    )}
                </div>

                {canViewAll && (
                    <div className="flex justify-end border-t border-border px-6 py-3">
                        <Button variant="outline" size="sm" asChild>
                            <a href={allActivityUrl}>
                                <ListOrdered /> View All Activity
                            </a>
                        </Button>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
