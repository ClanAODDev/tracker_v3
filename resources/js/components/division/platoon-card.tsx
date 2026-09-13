import { Link } from '@inertiajs/react';
import { type CSSProperties, type DragEvent } from 'react';

import { LeaderAvatar, type Leader } from '@/components/division/leader-avatar';
import { FillBar, FlashOnChange } from '@/components/motion';
import { voiceCorner, voiceFill, voiceTone } from '@/lib/voice-tone';
import { cn } from '@/lib/utils';

export interface Squad {
    id: number;
    name: string;
    memberCount: number;
    leader: Leader | null;
}

export interface Platoon {
    id: number;
    name: string;
    description: string | null;
    logo: string | null;
    url: string;
    memberCount: number;
    voiceRate: number;
    leader: Leader | null;
    squads: Squad[];
}

export function PlatoonCard({
    platoon,
    index,
    organizing,
    isHover,
    onDragOver,
    onDragLeave,
    onDrop,
}: {
    platoon: Platoon;
    index: number;
    organizing: boolean;
    isHover: boolean;
    onDragOver?: (e: DragEvent) => void;
    onDragLeave?: () => void;
    onDrop?: (e: DragEvent) => void;
}) {
    const ledSquads = platoon.squads.filter((s) => s.leader).length;
    const body = (
        <>
            <div className="flex items-start gap-3">
                {platoon.logo && <img src={platoon.logo} alt="" className="size-9 shrink-0 rounded" />}
                <div className="min-w-0 flex-1">
                    <p className="font-mono text-[13px] font-semibold uppercase leading-tight tracking-[0.08em]">
                        <span className="text-dim-foreground">P{index + 1}</span>
                        <span className="mx-1.5 text-border-strong">·</span>
                        {platoon.name}
                    </p>
                    <p className="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                        <span className="font-semibold text-primary">»</span>
                        {platoon.leader ? (
                            <>
                                <LeaderAvatar leader={platoon.leader} />
                                {platoon.leader.rankName}
                            </>
                        ) : (
                            'No leader assigned'
                        )}
                    </p>
                </div>
            </div>

            {platoon.squads.length > 0 && (
                <div className="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3">
                    {platoon.squads.map((squad) => (
                        <div
                            key={squad.id}
                            className={cn(
                                'rounded border p-2 text-xs',
                                squad.leader
                                    ? 'border-border/60'
                                    : 'tron-hatch border-dashed border-border/60 opacity-75',
                            )}
                            style={
                                squad.leader
                                    ? { borderLeftColor: squad.leader.rankColor, borderLeftWidth: 2 }
                                    : undefined
                            }
                        >
                            <div className="flex items-center justify-between">
                                <span className="font-medium">{squad.name}</span>
                                <span className="numeric text-muted-foreground">{squad.memberCount}</span>
                            </div>
                            {squad.leader ? (
                                <span className="text-muted-foreground">{squad.leader.rankName}</span>
                            ) : (
                                <span className="font-mono text-[11px] uppercase tracking-[0.1em] text-dim-foreground">
                                    TBA
                                </span>
                            )}
                        </div>
                    ))}
                </div>
            )}

            <div className="relative -mx-4 -mb-4 mt-3 overflow-hidden rounded-b-[5px] border-t border-border bg-black/15 px-4 py-2">
                <FillBar
                    pct={platoon.voiceRate}
                    className={cn(
                        'absolute inset-y-0 left-0',
                        platoon.voiceRate < 100 && 'border-r',
                        voiceFill(platoon.voiceRate),
                    )}
                    style={{ borderRightColor: voiceCorner(platoon.voiceRate) }}
                />
                <div className="relative flex items-center font-mono text-[11px] tracking-[0.04em]">
                    <span className="flex items-baseline gap-1.5 pr-3">
                        <span className="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Voice</span>
                        <span className={cn('font-semibold', voiceTone(platoon.voiceRate))}>
                            {platoon.voiceRate}%
                        </span>
                    </span>
                    <span className="flex items-baseline gap-1.5 border-l border-border-strong px-3">
                        <span className="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Members</span>
                        <FlashOnChange value={platoon.memberCount} className="font-semibold">
                            {platoon.memberCount}
                        </FlashOnChange>
                    </span>
                    <span className="flex items-baseline gap-1.5 border-l border-border-strong px-3">
                        <span className="text-[10px] uppercase tracking-[0.12em] text-muted-foreground">Squads</span>
                        <span className="font-semibold">
                            {ledSquads}/{platoon.squads.length}
                        </span>
                    </span>
                </div>
            </div>
        </>
    );

    if (organizing) {
        return (
            <div
                onDragOver={onDragOver}
                onDragLeave={onDragLeave}
                onDrop={onDrop}
                className={cn(
                    'overflow-hidden rounded-md border border-dashed p-4 transition-colors',
                    isHover ? 'border-primary bg-primary/10' : 'border-primary/40',
                )}
            >
                {body}
            </div>
        );
    }

    return (
        <Link
            href={platoon.url}
            className="tron-corners tron-corners-round rounded-md border border-border bg-card p-4 transition-[border-color,transform] hover:-translate-y-0.5 hover:border-primary/30"
            style={{ '--corner-color': voiceCorner(platoon.voiceRate) } as CSSProperties}
        >
            {body}
        </Link>
    );
}
