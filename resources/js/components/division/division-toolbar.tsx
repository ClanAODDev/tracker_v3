import { Link } from '@inertiajs/react';
import {
    Award,
    BarChart3,
    Calendar,
    ClipboardList,
    Home,
    Inbox,
    MoreHorizontal,
    Network,
    StickyNote,
    UserCog,
    UserPlus,
    Users,
    UsersRound,
    type LucideIcon,
} from 'lucide-react';
import { useLayoutEffect, useMemo, useRef, useState, type ReactNode } from 'react';

import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';

export interface DivisionTool {
    key: string;
    label: string;
    icon: string;
    href: string;
    tier: 'core' | 'secondary' | 'overflow';
    external?: boolean;
    accent?: boolean;
    menu?: Array<{ label: string; href: string }>;
}

const ICONS: Record<string, LucideIcon> = {
    home: Home,
    users: Users,
    'user-plus': UserPlus,
    'clipboard-list': ClipboardList,
    'bar-chart': BarChart3,
    'users-round': UsersRound,
    award: Award,
    inbox: Inbox,
    network: Network,
    'user-cog': UserCog,
    calendar: Calendar,
    'sticky-note': StickyNote,
};

const CARD_BASE =
    'flex h-[4.25rem] flex-col items-center justify-center gap-1.5 rounded-md border px-2 text-center text-xs transition-colors';

const MIN_CARD = 92;

function cardClass(accent?: boolean) {
    return cn(
        CARD_BASE,
        'min-w-0 flex-1 basis-0',
        accent
            ? 'border-primary/40 bg-primary/10 text-foreground hover:bg-primary/15'
            : 'border-border text-muted-foreground hover:border-primary/30 hover:text-foreground data-[state=open]:border-primary/30 data-[state=open]:text-foreground',
    );
}

function ToolBody({ icon, label }: { icon: string; label: string }) {
    const Icon = ICONS[icon] ?? Users;
    return (
        <>
            <Icon className="size-4" />
            <span className="text-center leading-tight">{label}</span>
        </>
    );
}

function ToolCard({ tool, onOverride }: { tool: DivisionTool; onOverride?: () => void }) {
    const body = <ToolBody icon={tool.icon} label={tool.label} />;

    if (tool.menu) {
        return (
            <DropdownMenu>
                <DropdownMenuTrigger className={cardClass(tool.accent)}>{body}</DropdownMenuTrigger>
                <DropdownMenuContent align="start">
                    {tool.menu.map((item) => (
                        <DropdownMenuItem key={item.href} asChild>
                            <Link href={item.href}>{item.label}</Link>
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        );
    }
    if (onOverride) {
        return (
            <button type="button" onClick={onOverride} className={cardClass(tool.accent)}>
                {body}
            </button>
        );
    }
    if (tool.external) {
        return (
            <a href={tool.href} target="_blank" rel="noreferrer" className={cardClass(tool.accent)}>
                {body}
            </a>
        );
    }
    return (
        <Link href={tool.href} className={cardClass(tool.accent)}>
            {body}
        </Link>
    );
}

function OverflowItem({ tool, onOverride }: { tool: DivisionTool; onOverride?: () => void }) {
    const Icon = ICONS[tool.icon] ?? Users;
    const content: ReactNode = (
        <>
            <Icon className="size-4" />
            {tool.label}
        </>
    );

    if (onOverride) {
        return <DropdownMenuItem onSelect={() => onOverride()}>{content}</DropdownMenuItem>;
    }
    return (
        <DropdownMenuItem asChild>
            {tool.external ? (
                <a href={tool.href} target="_blank" rel="noreferrer">
                    {content}
                </a>
            ) : (
                <Link href={tool.href}>{content}</Link>
            )}
        </DropdownMenuItem>
    );
}

export function DivisionToolbar({
    tools,
    overrides,
}: {
    tools: DivisionTool[];
    overrides?: Record<string, () => void>;
}) {
    const { rowTools, menuOnly, minVisible } = useMemo(() => {
        const row = tools.filter((t) => t.tier !== 'overflow');
        return {
            rowTools: row,
            menuOnly: tools.filter((t) => t.tier === 'overflow'),
            minVisible: row.filter((t) => t.tier === 'core').length,
        };
    }, [tools]);

    const rowRef = useRef<HTMLDivElement>(null);
    const [visibleCount, setVisibleCount] = useState(rowTools.length);

    useLayoutEffect(() => {
        const row = rowRef.current;
        if (!row) return;

        const recompute = () => {
            const gap = 8;
            const ellipsis = 44 + gap;
            const perCard = MIN_CARD + gap;
            const fit = Math.floor((row.clientWidth - ellipsis) / perCard);
            setVisibleCount(Math.min(rowTools.length, Math.max(fit, minVisible)));
        };

        const observer = new ResizeObserver(recompute);
        observer.observe(row);
        recompute();
        return () => observer.disconnect();
    }, [rowTools, minVisible]);

    const visible = rowTools.slice(0, visibleCount);
    const overflow = [...rowTools.slice(visibleCount), ...menuOnly];

    return (
        <div ref={rowRef} className="flex w-full gap-2">
            {visible.map((tool) => (
                <ToolCard key={tool.key} tool={tool} onOverride={overrides?.[tool.key]} />
            ))}
            <DropdownMenu>
                <DropdownMenuTrigger
                    className={cn(
                        CARD_BASE,
                        'w-11 shrink-0 border-border text-muted-foreground hover:border-primary/30 hover:text-foreground data-[state=open]:border-primary/30 data-[state=open]:text-foreground',
                    )}
                    aria-label="More tools"
                >
                    <MoreHorizontal className="size-4" />
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                    {overflow.map((tool) => (
                        <OverflowItem key={tool.key} tool={tool} onOverride={overrides?.[tool.key]} />
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
