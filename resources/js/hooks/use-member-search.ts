import { useEffect, useRef, useState } from 'react';

import { getJson } from '@/lib/api';

export interface MemberSearchResult {
    rankName: string;
    clanId: number;
    division: string;
    profileUrl: string;
    discord: string | null;
    handle: string | null;
}

interface Options {
    minChars?: number;
    delayMs?: number;
    jitterMs?: number;
}

interface State {
    results: MemberSearchResult[];
    loading: boolean;
    matchedQuery: string;
}

export function useMemberSearch(
    query: string,
    { minChars = 2, delayMs = 250, jitterMs = 0 }: Options = {},
): State {
    const [state, setState] = useState<Omit<State, 'loading'>>({ results: [], matchedQuery: '' });
    const [loading, setLoading] = useState(false);
    const reqId = useRef(0);

    useEffect(() => {
        const q = query.trim();
        const id = ++reqId.current;

        if (q.length < minChars) {
            setState({ results: [], matchedQuery: '' });
            setLoading(false);
            return;
        }

        setLoading(true);
        const timer = setTimeout(() => {
            getJson<{ results: MemberSearchResult[] }>(`/search/members?q=${encodeURIComponent(q)}`)
                .then((res) => id === reqId.current && setState({ results: res.results, matchedQuery: q }))
                .catch(() => id === reqId.current && setState({ results: [], matchedQuery: q }))
                .finally(() => id === reqId.current && setLoading(false));
        }, delayMs + Math.random() * jitterMs);

        return () => clearTimeout(timer);
    }, [query, minChars, delayMs, jitterMs]);

    return { ...state, loading };
}
