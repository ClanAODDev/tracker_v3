import { Head, Link, router } from '@inertiajs/react';
import { Search, UserX } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { Input } from '@/components/ui/input';
import { usePartialSearchMinChars } from '@/hooks/use-member-search';
import AppLayout from '@/layouts/AppLayout';

interface Result {
    rankName: string;
    clanId: number;
    division: string;
    profileUrl: string;
    discord: string | null;
    handle: string | null;
}

interface Pagination {
    total: number;
    currentPage: number;
    lastPage: number;
    from: number | null;
    to: number | null;
    prevUrl: string | null;
    nextUrl: string | null;
}

interface Props {
    query: string | null;
    results: Result[];
    partial: boolean;
    pagination: Pagination | null;
}

export default function MemberSearch({ query, results, partial, pagination }: Props) {
    const [value, setValue] = useState(query ?? '');
    const partialMinChars = usePartialSearchMinChars();

    function submit(e: FormEvent) {
        e.preventDefault();
        router.get('/search/members', value ? { q: value } : {}, { preserveState: false });
    }

    return (
        <AppLayout
            header={{
                eyebrow: 'AOD Tracker',
                title: 'Search members',
                breadcrumbs: [{ label: 'Search' }],
            }}
        >
            <Head title="Search members" />

            <div className="max-w-2xl space-y-4">
                <form onSubmit={submit} className="relative">
                    <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-muted-foreground" />
                    <Input
                        autoFocus
                        value={value}
                        onChange={(e) => setValue(e.target.value)}
                        placeholder="Name, Discord, or in-game handle"
                        className="h-10 pl-9"
                    />
                </form>

                {query && !partial && (
                    <p className="text-xs text-muted-foreground">
                        Showing exact matches only. Enter at least {partialMinChars} characters to search part of a name.
                    </p>
                )}

                {query && (
                    <div className="space-y-2">
                        {results.length === 0 ? (
                            <div className="flex items-center gap-2 rounded-md border border-border py-8 text-center text-sm text-muted-foreground">
                                <UserX className="mx-auto size-4" /> No members match “{query}”.
                            </div>
                        ) : (
                            results.map((member, i) => (
                                <Link
                                    key={member.clanId}
                                    href={member.profileUrl}
                                    className="block rounded-md border border-border bg-card p-3 transition-colors hover:border-primary/30"
                                >
                                    <div className="flex items-baseline justify-between gap-3">
                                        <p className="text-sm font-medium">
                                            <span className="mr-1.5 text-muted-foreground">{(pagination?.from ?? 1) + i}.</span>
                                            {member.rankName}{' '}
                                            <span className="numeric text-xs text-muted-foreground">
                                                [{member.clanId}]
                                            </span>
                                        </p>
                                        <span className="text-xs text-muted-foreground">{member.division}</span>
                                    </div>
                                    {(member.discord || member.handle) && (
                                        <p className="mt-0.5 text-xs text-muted-foreground">
                                            {[member.discord && `${member.discord} [Discord]`, member.handle]
                                                .filter(Boolean)
                                                .join(' · ')}
                                        </p>
                                    )}
                                </Link>
                            ))
                        )}

                        {pagination && pagination.total > 0 && (
                            <div className="flex items-center justify-between gap-3 pt-2 text-xs text-muted-foreground">
                                <span>
                                    {pagination.from}–{pagination.to} of {pagination.total}{' '}
                                    {pagination.total === 1 ? 'member' : 'members'}
                                </span>
                                <div className="flex items-center gap-2">
                                    {pagination.prevUrl ? (
                                        <Link href={pagination.prevUrl} className="rounded-md border border-border px-3 py-1.5 hover:border-primary/30 hover:text-foreground">
                                            Previous
                                        </Link>
                                    ) : null}
                                    {pagination.nextUrl ? (
                                        <Link href={pagination.nextUrl} className="rounded-md border border-border px-3 py-1.5 hover:border-primary/30 hover:text-foreground">
                                            Next
                                        </Link>
                                    ) : null}
                                </div>
                            </div>
                        )}
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
