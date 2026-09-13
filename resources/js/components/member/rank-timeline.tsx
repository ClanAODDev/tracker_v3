import { Fragment } from 'react';

import { ArrowDown, ArrowUp } from 'lucide-react';

import { cn } from '@/lib/utils';

interface TimelineNode {
    type: 'join' | 'consolidated' | 'promotion';
    date?: string;
    dateRange?: string;
    label?: string;
    rank?: string;
    rankColor?: string;
    duration?: string | null;
}

interface HistoryItem {
    type: 'join' | 'promotion' | 'demotion';
    date: string;
    label?: string;
    rank?: string;
}

export interface RankTimelineData {
    hasHistory: boolean;
    nodes: TimelineNode[];
    historyItems: HistoryItem[];
}

function nodeTitle(node: TimelineNode): string {
    if (node.type === 'join') return `${node.label} · ${node.date}`;
    if (node.type === 'consolidated') return `${node.label} · ${node.dateRange}`;
    return [`Promoted to ${node.rank}`, node.date, node.duration && `held ${node.duration}`]
        .filter(Boolean)
        .join(' · ');
}

export function RankTimeline({ timeline }: { timeline: RankTimelineData }) {
    if (!timeline.hasHistory || timeline.nodes.length === 0) {
        return <p className="text-center text-sm text-muted-foreground">No rank history available.</p>;
    }

    return (
        <div className="rank-timeline-wrapper">
        <div className="rank-timeline">
            {timeline.nodes.map((node, i) => {
                const prev = timeline.nodes[i - 1];
                return (
                    <Fragment key={i}>
                        {i > 0 && (
                            <span
                                className="timeline-connector"
                                style={
                                    {
                                        '--i': i,
                                        '--seg-color': node.rankColor ?? undefined,
                                    } as React.CSSProperties
                                }
                            >
                                {prev?.duration ?? ''}
                            </span>
                        )}
                        <div
                            className="timeline-node"
                            title={nodeTitle(node)}
                            style={
                                {
                                    '--i': i,
                                    '--rank-color': node.rankColor ?? undefined,
                                } as React.CSSProperties
                            }
                        >
                            <span className={cn('timeline-marker', `marker-${node.type}`)} />
                            <div className="timeline-content">
                                {node.type === 'join' && (
                                    <>
                                        <p className="timeline-label">{node.label}</p>
                                        <p className="timeline-date">{node.date}</p>
                                    </>
                                )}
                                {node.type === 'consolidated' && (
                                    <>
                                        <span className="timeline-rank timeline-rank-consolidated">{node.label}</span>
                                        <p className="timeline-date">{node.dateRange}</p>
                                    </>
                                )}
                                {node.type === 'promotion' && (
                                    <>
                                        <span className="timeline-rank">{node.rank}</span>
                                        <p className="timeline-date">{node.date}</p>
                                    </>
                                )}
                            </div>
                        </div>
                    </Fragment>
                );
            })}
        </div>
        </div>
    );
}

export function RankHistoryList({ items }: { items: HistoryItem[] }) {
    return (
        <ul className="space-y-1.5">
            {items.map((item, i) => (
                <li key={i} className="flex items-center gap-2 text-sm">
                    <span className="numeric w-24 shrink-0 text-xs text-muted-foreground">{item.date}</span>
                    {item.type === 'join' ? (
                        <span>{item.label}</span>
                    ) : (
                        <>
                            <span>{item.rank}</span>
                            {item.type === 'demotion' ? (
                                <ArrowDown className={cn('size-3.5 text-destructive')} />
                            ) : (
                                <ArrowUp className="size-3.5 text-success" />
                            )}
                        </>
                    )}
                </li>
            ))}
        </ul>
    );
}
