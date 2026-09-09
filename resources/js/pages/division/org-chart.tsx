import { Head } from '@inertiajs/react';
import { Download, Maximize2, Minimize2, Minus, Plus, Scan, Search, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { createOrgChart, type OrgChartHandle, type OrgNode, type SearchMatch } from '@/lib/org-chart';
import AppLayout from '@/layouts/AppLayout';

interface OrgChartProps {
    division: { name: string; slug: string };
    tree: OrgNode;
}

export default function OrgChart({ division, tree }: OrgChartProps) {
    const svgRef = useRef<SVGSVGElement>(null);
    const chartRef = useRef<OrgChartHandle | null>(null);
    const [query, setQuery] = useState('');
    const [matches, setMatches] = useState<SearchMatch[]>([]);
    const [showResults, setShowResults] = useState(false);
    const [handlesOn, setHandlesOn] = useState(false);

    useEffect(() => {
        if (!svgRef.current) return;
        const chart = createOrgChart(svgRef.current, tree);
        chartRef.current = chart;
        return () => chart.destroy();
    }, [tree]);

    function runSearch(value: string) {
        setQuery(value);
        const found = chartRef.current?.setSearch(value) ?? [];
        setMatches(found);
        setShowResults(value.trim().length > 0);
    }

    return (
        <AppLayout
            fullBleed
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
                                            <span style={{ color: m.rankColor }}>{m.rankName}</span>
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

                    <div className="ml-auto flex items-center gap-1">
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
                        <Button variant="outline" size="sm" onClick={() => chartRef.current?.exportPng()}>
                            <Download /> Export
                        </Button>
                    </div>
                </div>
            </div>

            <div className="relative w-full overflow-hidden">
                <svg ref={svgRef} className="block w-full" />
            </div>
        </AppLayout>
    );
}
