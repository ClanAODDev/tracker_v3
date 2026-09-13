import { Head } from '@inertiajs/react';
import { ExternalLink, Search } from 'lucide-react';

import { ReportShell } from '@/components/reports/report-shell';
import { StatTiles } from '@/components/reports/stat-tiles';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';

interface VoiceProps {
    division: { name: string; slug: string; platoonLabel: string };
    stats: { total: number; disconnected: number; neverConnected: number; neverConfigured: number };
    members: Array<{
        rankName: string;
        status: string;
        statusLabel: string;
        platoon: string | null;
        discord: string | null;
        lastActive: string;
        url: string;
        forumUrl: string;
    }>;
}

const STATUS_TONE: Record<string, string> = {
    disconnected: 'text-destructive',
    never_connected: 'text-muted-foreground',
    never_configured: 'text-warning',
};

export default function VoiceReport({ division, stats, members }: VoiceProps) {
    return (
        <ReportShell divisionName={division.name} divisionSlug={division.slug} title="Voice comms issues" active="voice">
            <Head title={`Voice · ${division.name}`} />

            <StatTiles
                stats={[
                    { value: stats.total, label: 'Total issues', tone: 'primary' },
                    { value: stats.disconnected, label: 'Disconnected', tone: 'danger' },
                    { value: stats.neverConnected, label: 'Never connected', tone: 'muted' },
                    { value: stats.neverConfigured, label: 'Never configured', tone: 'warning' },
                ]}
            />

            {members.length === 0 ? (
                <p className="rounded-md border border-border bg-card p-6 text-center text-sm text-muted-foreground">
                    All members in {division.name} have properly configured Discord.
                </p>
            ) : (
                <div className="overflow-x-auto rounded-md border border-border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Member</TableHead>
                                <TableHead>Status</TableHead>
                                <TableHead>{division.platoonLabel}</TableHead>
                                <TableHead>Discord</TableHead>
                                <TableHead>Last activity</TableHead>
                                <TableHead className="text-right">Forum</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {members.map((member, i) => (
                                <TableRow key={i}>
                                    <TableCell>
                                        <a
                                            href={member.url}
                                            className="flex items-center gap-1.5 font-medium hover:text-primary"
                                        >
                                            <Search className="size-3 text-muted-foreground" />
                                            {member.rankName}
                                        </a>
                                    </TableCell>
                                    <TableCell className={cn('text-sm', STATUS_TONE[member.status])}>
                                        {member.statusLabel}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">{member.platoon ?? '—'}</TableCell>
                                    <TableCell className="numeric text-muted-foreground">
                                        {member.discord ?? '—'}
                                    </TableCell>
                                    <TableCell className="text-muted-foreground">{member.lastActive}</TableCell>
                                    <TableCell className="text-right">
                                        <a
                                            href={member.forumUrl}
                                            target="_blank"
                                            rel="noreferrer"
                                            className="inline-flex text-muted-foreground hover:text-foreground"
                                        >
                                            <ExternalLink className="size-4" />
                                        </a>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>
            )}
        </ReportShell>
    );
}
