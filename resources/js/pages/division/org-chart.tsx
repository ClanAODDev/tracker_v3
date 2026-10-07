import { Head } from '@inertiajs/react';
import { Download, Maximize2, Minimize2, Minus, Plus, Scan, Search, ShieldCheck, X } from 'lucide-react';
import { type CSSProperties, useEffect, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    createOrgChart,
    type OrgChartHandle,
    type OrgNode,
    type SearchMatch,
    type UnitSelection,
} from '@/lib/org-chart';
import AppLayout from '@/layouts/AppLayout';

interface OrgChartProps {
    division: { name: string; slug: string };
    tree: OrgNode;
    powers: Record<number, { label: string; title: string; powers: string[] }>;
}

export default function OrgChart({ division, tree, powers }: OrgChartProps) {
    const svgRef = useRef<SVGSVGElement>(null);
    const chartRef = useRef<OrgChartHandle | null>(null);
    const [query, setQuery] = useState(
        () => new URLSearchParams(window.location.search).get('q') ?? '',
    );
    const [matches, setMatches] = useState<SearchMatch[]>([]);
    const [showResults, setShowResults] = useState(false);
    const [handlesOn, setHandlesOn] = useState(false);
    const [inspecting, setInspecting] = useState(false);
    const [selection, setSelection] = useState<UnitSelection | null>(null);

    useEffect(() => {
        if (!svgRef.current) return;
        const chart = createOrgChart(svgRef.current, tree, { onUnitSelect: setSelection });
        chartRef.current = chart;
        setSelection(null);
        setInspecting(false);
        if (query.trim()) setMatches(chart.setSearch(query));
        return () => chart.destroy();
    }, [tree]);

    function runSearch(value: string) {
        setQuery(value);
        const found = chartRef.current?.setSearch(value) ?? [];
        setMatches(found);
        setShowResults(value.trim().length > 0);

        const url = new URL(window.location.href);
        value.trim() ? url.searchParams.set('q', value.trim()) : url.searchParams.delete('q');
        window.history.replaceState(null, '', url);
    }

    function toggleInspecting() {
        const next = !inspecting;
        setInspecting(next);
        chartRef.current?.setInspecting(next);
    }

    const levelPowers = selection ? powers[selection.depth] : null;

    return (
        <AppLayout
            width="full"
            header={{
                title: 'Organization chart',
                eyebrow: `${division.name} Division`,
                breadcrumbs: [
                    { label: 'Divisions' },
                    { label: division.name, href: `/divisions/${division.slug}` },
                    { label: 'Structure' },
                ],
            }}
        >
            <Head title={`Org chart · ${division.name}`} />

            <div className="border-b border-border bg-card/40">
                <div className="mx-auto flex w-full max-w-7xl flex-wrap items-center gap-2 px-6 py-3">
                    <div className="relative">
                        <Search className="pointer-events-none absolute left-2.5 top-1/2 size-3.5 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            value={query}
                            onChange={(e) => runSearch(e.target.value)}
                            onFocus={() => setShowResults(matches.length > 0)}
                            placeholder="Search members…"
                            className="h-8 w-56 pl-8"
                        />
                        {query && (
                            <button
                                onClick={() => {
                                    runSearch('');
                                    chartRef.current?.collapseAll();
                                }}
                                className="absolute right-2 top-1/2 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                            >
                                <X className="size-3.5" />
                            </button>
                        )}
                        {showResults && (
                            <div className="absolute z-20 mt-1 max-h-72 w-72 overflow-auto rounded-md border border-border bg-popover p-1 shadow-lg">
                                {matches.length === 0 ? (
                                    <p className="px-2 py-1.5 text-sm text-muted-foreground">No members found</p>
                                ) : (
                                    matches.map((m) => (
                                        <button
                                            key={m.id}
                                            onClick={() => {
                                                chartRef.current?.focusNode(m.id);
                                                setShowResults(false);
                                            }}
                                            className="flex w-full items-center gap-2 rounded px-2 py-1.5 text-left text-sm hover:bg-accent"
                                        >
                                            <span className="rank-ink" style={{ '--rank-color': m.rankColor } as CSSProperties}>
                                                {m.rankName}
                                            </span>
                                            <span className="truncate">{m.name}</span>
                                            {m.handle && (
                                                <span className="ml-auto truncate text-xs text-muted-foreground">
                                                    {m.handle}
                                                </span>
                                            )}
                                        </button>
                                    ))
                                )}
                            </div>
                        )}
                    </div>

                    <div className="ml-auto flex flex-wrap items-center justify-end gap-1">
                        <Button variant="outline" size="icon-sm" onClick={() => chartRef.current?.zoomIn()} title="Zoom in">
                            <Plus />
                        </Button>
                        <Button variant="outline" size="icon-sm" onClick={() => chartRef.current?.zoomOut()} title="Zoom out">
                            <Minus />
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => chartRef.current?.resetView()}>
                            <Scan /> Reset
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => chartRef.current?.expandAll()}>
                            <Maximize2 /> Expand
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => chartRef.current?.collapseAll()}>
                            <Minimize2 /> Collapse
                        </Button>
                        <Button
                            variant={handlesOn ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setHandlesOn(chartRef.current?.toggleHandles() ?? false)}
                        >
                            Handles
                        </Button>
                        <Button variant={inspecting ? 'default' : 'outline'} size="sm" onClick={toggleInspecting}>
                            <ShieldCheck /> Leader powers
                        </Button>
                        <Button variant="outline" size="sm" onClick={() => chartRef.current?.exportPng()}>
                            <Download /> Export
                        </Button>
                    </div>
                </div>
            </div>

            <div className="relative w-full overflow-hidden">
                <svg ref={svgRef} className="block w-full" />

                {inspecting && !selection && (
                    <div className="pointer-events-none absolute left-4 top-4 max-w-xs rounded-md border border-border bg-popover/95 px-3 py-2 text-sm text-muted-foreground shadow-lg">
                        Select a unit to see what its leader can do and which units and members they cover.
                    </div>
                )}

                {selection && levelPowers && (
                    <div className="absolute left-4 top-4 max-h-[70vh] w-80 max-w-[calc(100%-2rem)] overflow-auto rounded-md border border-border bg-popover/95 p-4 shadow-lg">
                        <div className="flex items-start justify-between gap-2">
                            <div>
                                <p className="text-xs uppercase tracking-wide text-muted-foreground">
                                    {levelPowers.title}
                                </p>
                                <p className="font-semibold">{selection.name}</p>
                            </div>
                            <button
                                onClick={() => chartRef.current?.clearSelection()}
                                className="text-muted-foreground hover:text-foreground"
                                title="Clear selection"
                            >
                                <X className="size-4" />
                            </button>
                        </div>
                        <p className="mt-2 text-xs text-muted-foreground">
                            Covers {selection.memberCount} {selection.memberCount === 1 ? 'person' : 'people'}
                            {selection.unitCount > 0 &&
                                ` across ${selection.unitCount} ${selection.unitCount === 1 ? 'unit' : 'units'} below`}
                            . Highlighted on the chart.
                        </p>
                        <ul className="mt-3 space-y-1.5 text-sm">
                            {levelPowers.powers.map((power) => (
                                <li key={power} className="flex gap-2">
                                    <span className="mt-1.5 size-1.5 shrink-0 rounded-full bg-primary" />
                                    <span>{power}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
