import { router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';

import { TronSpinner } from '@/components/tron-spinner';
import { useMemberSearch, type MemberSearchResult } from '@/hooks/use-member-search';
import { cn } from '@/lib/utils';

type Result = MemberSearchResult;

export function MemberSearch({ className }: { className?: string }) {
    const [value, setValue] = useState('');
    const [open, setOpen] = useState(false);
    const [active, setActive] = useState(-1);
    const [anchor, setAnchor] = useState<{ top: number; right: number } | null>(null);
    const boxRef = useRef<HTMLDivElement>(null);
    const dropdownRef = useRef<HTMLDivElement>(null);
    const { results, loading, matchedQuery } = useMemberSearch(value, { delayMs: 2000, jitterMs: 1000 });

    useEffect(() => {
        if (!open) return;
        const measure = () => {
            const el = boxRef.current;
            if (!el) return;
            const r = el.getBoundingClientRect();
            setAnchor({ top: r.bottom + 8, right: window.innerWidth - r.right });
        };
        measure();
        window.addEventListener('resize', measure);
        window.addEventListener('scroll', measure, true);
        return () => {
            window.removeEventListener('resize', measure);
            window.removeEventListener('scroll', measure, true);
        };
    }, [open]);

    useEffect(() => {
        if (matchedQuery.length >= 2) {
            setOpen(true);
            setActive(-1);
        } else {
            setOpen(false);
        }
    }, [results, matchedQuery]);

    useEffect(() => {
        function onClick(e: MouseEvent) {
            const target = e.target as Node;
            if (boxRef.current?.contains(target) || dropdownRef.current?.contains(target)) return;
            setOpen(false);
        }
        document.addEventListener('mousedown', onClick);
        return () => document.removeEventListener('mousedown', onClick);
    }, []);

    function go(result: Result) {
        setOpen(false);
        setValue('');
        router.visit(result.profileUrl);
    }

    return (
        <div ref={boxRef} className={cn('group relative', className)}>
            <Search className="pointer-events-none absolute left-1.5 top-1/2 size-3.5 -translate-y-1/2 text-muted-foreground transition-colors group-focus-within:text-foreground" />
            <input
                value={value}
                onChange={(e) => setValue(e.target.value)}
                onFocus={() => results.length > 0 && setOpen(true)}
                onKeyDown={(e) => {
                    if (e.key === 'Escape') setOpen(false);
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        setActive((a) => Math.min(a + 1, Math.min(results.length, 8) - 1));
                    }
                    if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        setActive((a) => Math.max(a - 1, 0));
                    }
                    if (e.key === 'Enter') {
                        if (active >= 0 && results[active]) go(results[active]);
                        else if (value.trim())
                            router.get('/search/members', { q: value.trim() });
                    }
                }}
                placeholder="Search players…"
                className="h-8 w-40 rounded-none border-0 border-b border-transparent bg-transparent pl-7 pr-6 text-sm outline-none transition-all placeholder:text-muted-foreground focus:w-64 focus:border-b-border-strong"
            />
            {loading ? (
                <TronSpinner className="absolute right-1 top-1/2 size-3.5 -translate-y-1/2" />
            ) : value ? (
                <button
                    type="button"
                    onClick={() => {
                        setValue('');
                        setOpen(false);
                    }}
                    className="absolute right-0.5 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                >
                    <X className="size-3.5" />
                </button>
            ) : null}

            {open &&
                anchor &&
                createPortal(
                    <>
                        <div
                            className="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm"
                            onMouseDown={() => setOpen(false)}
                        />
                        <div
                            ref={dropdownRef}
                            className="fixed z-50 w-80"
                            style={{ top: anchor.top, right: anchor.right }}
                        >
                <div className="tron-corners rounded-md border border-border bg-popover shadow-lg [box-shadow:0_0_24px_-6px_var(--primary-glow)]">
                    <div className="flex items-center gap-2 border-b border-border px-3 py-2">
                        <span className="h-3 w-0.5 shrink-0 rounded-full bg-primary shadow-[0_0_6px_var(--primary-glow)]" />
                        <span className="font-mono text-[10px] uppercase tracking-[0.2em] text-muted-foreground">
                            {loading ? 'Scanning…' : `${results.length} ${results.length === 1 ? 'match' : 'matches'}`}
                        </span>
                    </div>
                    {results.length === 0 ? (
                        <p className="px-3 py-4 text-center text-sm text-muted-foreground">
                            {loading ? 'Searching…' : 'No members found.'}
                        </p>
                    ) : (
                        <ul className="max-h-96 overflow-y-auto py-1">
                            {results.slice(0, 8).map((result, i) => (
                                <li key={result.clanId}>
                                    <button
                                        type="button"
                                        onMouseEnter={() => setActive(i)}
                                        onClick={() => go(result)}
                                        className={cn(
                                            'flex w-full flex-col items-start gap-0.5 border-l-2 border-transparent px-3 py-2 text-left text-sm transition-colors',
                                            i === active && 'border-l-primary bg-primary/10',
                                        )}
                                    >
                                        <span className="font-medium">
                                            {result.rankName}{' '}
                                            <span className="numeric text-xs text-muted-foreground">
                                                [{result.clanId}]
                                            </span>
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {[
                                                result.division,
                                                result.discord && `${result.discord} [Discord]`,
                                                result.handle,
                                            ]
                                                .filter(Boolean)
                                                .join(' · ')}
                                        </span>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    )}
                    {results.length > 8 && (
                        <button
                            type="button"
                            onClick={() => router.get('/search/members', { q: value.trim() })}
                            className="flex w-full items-center gap-2 border-t border-border px-3 py-2 text-left font-mono text-[10px] uppercase tracking-[0.2em] text-muted-foreground hover:text-foreground"
                        >
                            <span className="h-px flex-1 bg-gradient-to-r from-primary/60 to-transparent" />
                            {results.length - 8} more — view all
                        </button>
                    )}
                </div>
                        </div>
                    </>,
                    document.body,
                )}
        </div>
    );
}
