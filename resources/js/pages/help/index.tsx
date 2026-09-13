import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';

import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout';
import type { SharedProps } from '@/types';

const ACCESS_MATRIX: Array<{ role: string; abilities: string[] }> = [
    { role: 'Member', abilities: ['View access only'] },
    {
        role: 'Officer (NCO)',
        abilities: [
            'View the division structure',
            'Manage part-time members of a division',
            'Manage member in-game handles',
        ],
    },
    {
        role: 'Senior Leader (SGT+)',
        abilities: [
            'Create, delete, and update any division platoons, squads, and members',
            'Remove any member (requires SGT+ forum permissions)',
            'Change own division settings (must be CO/XO)',
            'Grant account access up to one role below their own',
            'Edit the division structure template',
        ],
    },
    {
        role: 'Admin (MSGT+)',
        abilities: ['Inherits all Senior Leader permissions', 'Change any division settings', 'Access the admin panel'],
    },
];

const DOC_LINKS: Array<{ label: string; href: string; blurb: string; adminOnly?: boolean; officerOnly?: boolean }> = [
    { label: 'Awards Images', href: '/help/docs/member-awards', blurb: 'Embedding member award galleries.' },
    { label: 'Managing Rank', href: '/help/docs/managing-rank', blurb: 'Promotion and demotion process and rules.' },
    {
        label: 'Recruiting',
        href: '/help/docs/recruiting',
        blurb: 'How recruitment adds members and syncs the forums.',
        officerOnly: true,
    },
    {
        label: 'Contributing',
        href: '/help/docs/admin',
        blurb: 'Writing and updating these documentation pages.',
        adminOnly: true,
    },
];

export default function HelpIndex() {
    const perms = usePage<SharedProps>().props.auth.permissions;
    const isAdmin = perms?.isAdmin ?? false;
    const isOfficerPlus = isAdmin || perms?.canUseBulkMode === true;

    return (
        <AppLayout
            header={{
                eyebrow: 'Documentation',
                title: 'General',
                breadcrumbs: [{ label: 'Documentation' }, { label: 'General' }],
            }}
        >
            <Head title="Documentation" />

            <div className="max-w-3xl space-y-8">
                <section className="space-y-3 text-sm text-muted-foreground">
                    <p>
                        The Tracker complements the AOD forums — it doesn't replace them — helping clan and division
                        leadership do their everyday work more easily and effectively.
                    </p>
                    <p>
                        It began as a way to solve recruiting: consolidating everything a recruiter needs in one place
                        and automating the tedious parts. It has since grown into a way to track member activity in the
                        context of the whole clan, with weekly censuses that surface population and activity trends.
                    </p>
                </section>

                <section>
                    <h2 className="mb-1 text-sm font-semibold">User access matrix</h2>
                    <p className="mb-3 text-sm text-muted-foreground">
                        Policies govern every action on the Tracker. This matrix breaks down what each role can do.
                    </p>
                    <div className="overflow-x-auto rounded-md border border-border">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-48">Role</TableHead>
                                    <TableHead>Access</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {ACCESS_MATRIX.map((row) => (
                                    <TableRow key={row.role}>
                                        <TableCell className="align-top font-medium text-primary">{row.role}</TableCell>
                                        <TableCell>
                                            <ul className="list-disc space-y-1 pl-4 text-sm text-muted-foreground">
                                                {row.abilities.map((ability) => (
                                                    <li key={ability}>{ability}</li>
                                                ))}
                                            </ul>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </section>

                <section>
                    <h2 className="mb-3 text-sm font-semibold">More documentation</h2>
                    <div className="grid gap-2 sm:grid-cols-2">
                        {DOC_LINKS.filter(
                            (link) => (!link.adminOnly || isAdmin) && (!link.officerOnly || isOfficerPlus),
                        ).map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className="group flex items-start justify-between gap-3 rounded-md border border-border bg-card p-4 transition-colors hover:border-primary/30"
                            >
                                <span>
                                    <span className="block text-sm font-medium">{link.label}</span>
                                    <span className="mt-0.5 block text-xs text-muted-foreground">{link.blurb}</span>
                                </span>
                                <ArrowRight className="size-4 shrink-0 text-muted-foreground transition-transform group-hover:translate-x-0.5" />
                            </Link>
                        ))}
                    </div>
                </section>
            </div>
        </AppLayout>
    );
}
