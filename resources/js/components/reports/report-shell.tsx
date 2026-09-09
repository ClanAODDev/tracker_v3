import { Link, usePage } from '@inertiajs/react';
import type { PropsWithChildren, ReactNode } from 'react';

import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

const REPORTS = [
    { key: 'census', label: 'Census', path: 'census' },
    { key: 'retention', label: 'Retention', path: 'retention' },
    { key: 'promotions', label: 'Promotions', path: 'promotions' },
    { key: 'voice', label: 'Voice', path: 'voice-report' },
    { key: 'transfers', label: 'Transfers', path: 'transfers' },
] as const;

interface ReportShellProps {
    divisionName: string;
    divisionSlug: string;
    title: string;
    active: (typeof REPORTS)[number]['key'];
    actions?: ReactNode;
}

export function ReportShell({
    divisionName,
    divisionSlug,
    title,
    active,
    actions,
    children,
}: PropsWithChildren<ReportShellProps>) {
    const currentUrl = usePage().url;

    return (
        <AppLayout
            header={{
                eyebrow: `${divisionName} Division`,
                title,
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: divisionName, href: `/divisions/${divisionSlug}` },
                    { label: 'Reports' },
                ],
                actions,
            }}
        >
            <div className="space-y-6">
                <nav className="flex flex-wrap gap-1 border-b border-border pb-1">
                    {REPORTS.map((report) => {
                        const href = `/divisions/${divisionSlug}/${report.path}`;
                        const isActive = report.key === active || currentUrl.startsWith(href);
                        return (
                            <Link
                                key={report.key}
                                href={href}
                                prefetch
                                aria-current={isActive ? 'page' : undefined}
                                className={cn(
                                    'rounded-md px-3 py-1.5 text-sm transition-colors',
                                    isActive
                                        ? 'bg-primary/10 font-medium text-foreground'
                                        : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                )}
                            >
                                {report.label}
                            </Link>
                        );
                    })}
                </nav>

                {children}
            </div>
        </AppLayout>
    );
}
