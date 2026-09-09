import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren, ReactNode } from 'react';

import type { SharedProps } from '@/types';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface ReportLink {
    key: string;
    label: string;
    href: string;
    adminOnly?: boolean;
}

const REPORTS: ReportLink[] = [
    { key: 'census', label: 'Census', href: '/clan/census' },
    { key: 'leadership', label: 'Leadership', href: '/clan/leadership' },
    { key: 'outstanding', label: 'Outstanding Inactives', href: '/clan/outstanding-inactives' },
    { key: 'turnover', label: 'Turnover', href: '/clan/division-turnover', adminOnly: true },
];

interface ClanReportShellProps {
    title: string;
    active: 'census' | 'leadership' | 'outstanding' | 'turnover';
    actions?: ReactNode;
}

export function ClanReportShell({ title, active, actions, children }: PropsWithChildren<ClanReportShellProps>) {
    const isAdmin = usePage<SharedProps>().props.auth.permissions?.isAdmin ?? false;

    return (
        <AppLayout
            header={{
                eyebrow: 'Clan Reports',
                title,
                breadcrumbs: [{ label: 'Clan Information' }, { label: 'Reports' }, { label: title }],
                actions,
            }}
        >
            <div className="space-y-6">
                <nav className="flex flex-wrap gap-1 border-b border-border pb-1">
                    {REPORTS.filter((report) => !report.adminOnly || isAdmin).map((report) => (
                        <Link
                            key={report.key}
                            href={report.href}
                            prefetch
                            aria-current={report.key === active ? 'page' : undefined}
                            className={cn(
                                'rounded-md px-3 py-1.5 text-sm transition-colors',
                                report.key === active
                                    ? 'bg-primary/10 font-medium text-foreground'
                                    : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                            )}
                        >
                            {report.label}
                        </Link>
                    ))}
                </nav>

                {children}
            </div>
        </AppLayout>
    );
}
