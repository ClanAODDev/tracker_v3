import { Head, Link } from '@inertiajs/react';
import { Headset, Settings, UserPlus } from 'lucide-react';

import { CountUp } from '@/components/count-up';
import { PendingActionIcon } from '@/components/dashboard/pending-action-icon';
import { Leaderboard, type LeaderEntry } from '@/components/dashboard/leaderboard';
import { DivisionToolbar, type DivisionTool } from '@/components/division/division-toolbar';
import { TronIdPlate, TronFlash } from '@/components/tron/flourishes';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface DivisionSummary {
    name: string;
    slug: string;
    logo: string | null;
    memberCount: number;
    isShutdown: boolean;
    voice?: string | null;
    recruits?: number;
}

interface MyDivision {
    name: string;
    slug: string;
    abbr: string;
    logo: string | null;
    memberCount: number;
    isShutdown: boolean;
    canManage: boolean;
    manageUrl: string;
    requestsUrl: string;
    canManageRequests: boolean;
    canRecruit: boolean;
    applicationRequired: boolean;
}

interface PendingActionItem {
    key: string;
    count: number;
    url: string;
    icon: string;
    label: string;
    style: string;
}

interface HomeProps {
    myDivision: MyDivision;
    toolbar: DivisionTool[];
    pendingActions: PendingActionItem[];
    leaderboard: {
        userDivisionId: number | null;
        recruits: LeaderEntry[];
        voice: LeaderEntry[];
        growth: LeaderEntry[];
    };
    divisions: DivisionSummary[];
}

const STYLE_CLASS: Record<string, string> = {
    default: 'border-border',
    warning: 'border-warning/40 bg-warning/5',
    danger: 'border-destructive/40 bg-destructive/5',
    accent: 'border-primary/40 bg-primary/5',
};

export default function Home({ myDivision, toolbar, pendingActions, leaderboard, divisions }: HomeProps) {
    const d = myDivision;

    return (
        <AppLayout header={{ title: 'AOD Tracker', breadcrumbs: [{ label: 'Dashboard' }] }}>
            <Head title="Dashboard" />

            <div className="tron-stagger space-y-8">
                {/* My division */}
                <div className="tron-corners rounded-md border border-border bg-card p-5">
                    <TronFlash />
                    <TronIdPlate
                        label={`DIV · ${d.abbr}`}
                        live
                        className="absolute -top-2 left-3"
                    />
                    <div className="flex flex-wrap items-center justify-between gap-4">
                        <div className="flex items-center gap-4">
                            {d.logo && <img src={d.logo} alt="" className="size-12 rounded" />}
                            <div>
                                <Link href={`/divisions/${d.slug}`} className="text-lg font-semibold hover:text-primary">
                                    {d.name}
                                </Link>
                                <p className="numeric text-sm text-muted-foreground">
                                    <CountUp value={d.memberCount} /> members
                                </p>
                            </div>
                        </div>
                        {d.canManage && (
                            <Button variant="outline" size="sm" asChild>
                                <a href={d.manageUrl}>
                                    <Settings /> Manage
                                </a>
                            </Button>
                        )}
                    </div>

                    <div className="tron-hatch -mx-5 -mb-5 mt-4 rounded-b-md border-t border-border px-5 pt-4 pb-5">
                        <DivisionToolbar tools={toolbar} />
                    </div>
                </div>

                {/* Pending actions */}
                {pendingActions.length > 0 && (
                    <div>
                        <h2 className="tron-eyebrow mb-3">Action items</h2>
                        <div className="flex flex-wrap gap-2">
                            {pendingActions.map((action) => (
                                <a
                                    key={action.key}
                                    href={action.url}
                                    className={cn(
                                        'flex items-center gap-2 rounded-md border px-3 py-2 text-sm transition-colors hover:border-primary/40',
                                        STYLE_CLASS[action.style] ?? STYLE_CLASS.default,
                                    )}
                                >
                                    <PendingActionIcon icon={action.icon} className="size-4 text-muted-foreground" />
                                    <span className="numeric font-semibold">
                                        <CountUp value={action.count} />
                                    </span>
                                    <span className="text-muted-foreground">{action.label}{action.count === 1 ? '' : 's'}</span>
                                </a>
                            ))}
                        </div>
                    </div>
                )}

                <Leaderboard {...leaderboard} />

                {/* All divisions */}
                <div>
                    <div className="mb-3 flex items-baseline justify-between">
                        <h2 className="text-sm font-semibold">
                            All <span className="text-muted-foreground">divisions</span>
                        </h2>
                        <span className="tron-eyebrow">Members</span>
                    </div>
                    <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                        {divisions.map((division) => (
                            <Link
                                key={division.slug}
                                href={`/divisions/${division.slug}`}
                                className={cn(
                                    'flex items-center gap-3 rounded-md border border-border bg-card p-3 text-sm transition-colors hover:border-primary/30',
                                    division.isShutdown && 'opacity-50',
                                )}
                            >
                                {division.logo && <img src={division.logo} alt="" className="size-8 rounded" />}
                                <div className="min-w-0 flex-1">
                                    <span className="block truncate">{division.name}</span>
                                    <span className="numeric text-xs text-muted-foreground">
                                        <CountUp value={division.memberCount} /> members
                                    </span>
                                </div>
                                {!division.isShutdown && (
                                    <div className="flex shrink-0 items-center gap-2 text-xs text-muted-foreground">
                                        {division.voice && (
                                            <span className="flex items-center gap-1" title="Voice active (7d)">
                                                <Headset className="size-3" />
                                                {division.voice}
                                            </span>
                                        )}
                                        {(division.recruits ?? 0) > 0 && (
                                            <span className="flex items-center gap-1" title="Recruits this month">
                                                <UserPlus className="size-3" />
                                                {division.recruits}
                                            </span>
                                        )}
                                    </div>
                                )}
                            </Link>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
