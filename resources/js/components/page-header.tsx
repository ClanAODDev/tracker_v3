import type { ReactNode } from 'react';

import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';

export interface Crumb {
    label: string;
    href?: string;
}

export interface PageHeaderProps {
    title: string;
    /** Small red tick-label above the title. Use a section/category, not the title again. */
    eyebrow?: string;
    breadcrumbs?: Crumb[];
    actions?: ReactNode;
}

export function PageHeader({ title, eyebrow, breadcrumbs, actions }: PageHeaderProps) {
    const crumbs = (breadcrumbs ?? []).filter((c) => c.label.toLowerCase() !== title.toLowerCase());
    const showCrumbs = crumbs.length >= 2 || crumbs.some((c) => c.href);

    return (
        <>
            <div className="tron-hairline relative overflow-hidden bg-card/40">
                <div
                    aria-hidden
                    className="tron-hatch pointer-events-none absolute inset-y-0 right-0 w-1/3 [mask-image:linear-gradient(to_left,black,transparent)]"
                />
                <div className="relative mx-auto w-full max-w-7xl px-6 py-6">
                    <div className="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            {eyebrow && <p className="tron-eyebrow">{eyebrow}</p>}
                            <h1 className="mt-1.5 text-2xl font-semibold tracking-tight">{title}</h1>
                        </div>
                        {actions && <div className="flex items-center gap-2">{actions}</div>}
                    </div>
                </div>
            </div>

            {showCrumbs && (
                <div className="border-b border-border bg-card/20">
                    <div className="mx-auto w-full max-w-7xl px-6 py-2">
                        <Breadcrumb>
                            <BreadcrumbList>
                                {crumbs.map((crumb, index) => {
                                    const last = index === crumbs.length - 1;
                                    return (
                                        <span key={`${crumb.label}-${index}`} className="contents">
                                            <BreadcrumbItem>
                                                {last || !crumb.href ? (
                                                    <BreadcrumbPage>{crumb.label}</BreadcrumbPage>
                                                ) : (
                                                    <BreadcrumbLink href={crumb.href}>{crumb.label}</BreadcrumbLink>
                                                )}
                                            </BreadcrumbItem>
                                            {!last && <BreadcrumbSeparator />}
                                        </span>
                                    );
                                })}
                            </BreadcrumbList>
                        </Breadcrumb>
                    </div>
                </div>
            )}
        </>
    );
}
