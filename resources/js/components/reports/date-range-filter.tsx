import { router } from '@inertiajs/react';
import { Filter, X } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';

export function DateRangeFilter({ start, end, baseUrl }: { start: string; end: string; baseUrl: string }) {
    const [from, setFrom] = useState(start);
    const [to, setTo] = useState(end);

    function apply(e: FormEvent) {
        e.preventDefault();
        router.get(baseUrl, { start: from || undefined, end: to || undefined }, { preserveState: false });
    }

    function preset(months: number) {
        const now = new Date();
        const past = new Date(now.getFullYear(), now.getMonth() - months, 1);
        const iso = (d: Date) => d.toISOString().slice(0, 10);
        router.get(baseUrl, { start: iso(past), end: iso(now) }, { preserveState: false });
    }

    return (
        <form onSubmit={apply} className="flex flex-wrap items-end gap-3 rounded-md border border-border bg-card p-3">
            <label className="grid gap-1 text-xs text-muted-foreground">
                Start
                <Input type="date" value={from} onChange={(e) => setFrom(e.target.value)} className="w-40" />
            </label>
            <label className="grid gap-1 text-xs text-muted-foreground">
                End
                <Input type="date" value={to} onChange={(e) => setTo(e.target.value)} className="w-40" />
            </label>
            <Button type="submit" size="sm">
                <Filter /> Apply
            </Button>
            {(start || end) && (
                <Button type="button" size="sm" variant="ghost" onClick={() => router.get(baseUrl)}>
                    <X /> Clear
                </Button>
            )}
            <div className="ml-auto flex gap-1">
                {[
                    [3, '3M'],
                    [6, '6M'],
                    [12, '1Y'],
                    [24, '2Y'],
                ].map(([m, label]) => (
                    <Button key={label} type="button" size="sm" variant="outline" onClick={() => preset(m as number)}>
                        {label}
                    </Button>
                ))}
            </div>
        </form>
    );
}
