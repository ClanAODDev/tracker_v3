import { CheckCircle2, Circle, Loader, XCircle } from 'lucide-react';

import { cn } from '@/lib/utils';

export interface TicketRef {
    id: number;
    name: string;
    avatar: string | null;
}

export interface TicketComment {
    id: number;
    body: string;
    created_at: string;
    user: (TicketRef & { is_admin?: boolean }) | null;
}

export interface TicketSummary {
    id: number;
    state: string;
    description: string;
    type: { id: number; name: string } | null;
    division: { id: number; name: string } | null;
    owner: TicketRef | null;
    caller?: TicketRef | null;
    created_at: string;
    updated_at: string;
    resolved_at: string | null;
    attachments: string[];
}

export interface TicketDetail extends TicketSummary {
    comments: TicketComment[];
}

export interface TicketType {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    boilerplate: string | null;
}

export interface Worker {
    id: number;
    name: string;
    rank_name: string | null;
    avatar: string | null;
}

export const TICKET_STATUS: Record<
    string,
    { label: string; icon: typeof Circle; tone: string; badge: string }
> = {
    new: { label: 'Open', icon: Circle, tone: 'text-info', badge: 'border-info/40 bg-info/10 text-info' },
    assigned: {
        label: 'In progress',
        icon: Loader,
        tone: 'text-warning',
        badge: 'border-warning/40 bg-warning/10 text-warning',
    },
    resolved: {
        label: 'Resolved',
        icon: CheckCircle2,
        tone: 'text-success',
        badge: 'border-success/40 bg-success/10 text-success',
    },
    rejected: {
        label: 'Closed',
        icon: XCircle,
        tone: 'text-destructive',
        badge: 'border-destructive/40 bg-destructive/10 text-destructive',
    },
};

export function StatusBadge({ state, className }: { state: string; className?: string }) {
    const meta = TICKET_STATUS[state] ?? TICKET_STATUS.new;
    const Icon = meta.icon;
    return (
        <span
            className={cn(
                'inline-flex items-center gap-1.5 rounded-md border px-2 py-0.5 text-xs font-medium',
                meta.badge,
                className,
            )}
        >
            <Icon className="size-3" /> {meta.label}
        </span>
    );
}

const SYSTEM_PATTERNS = [
    'owned the ticket',
    'assigned the ticket to',
    'resolved the ticket',
    'reopened the ticket',
    'rejected the ticket',
];

export function isSystemMessage(comment: TicketComment): boolean {
    const body = comment.body.toLowerCase();
    return SYSTEM_PATTERNS.some((p) => body.includes(p));
}

function escapeHtml(text: string): string {
    return text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function escapeAndLink(text: string): string {
    return escapeHtml(text).replace(
        /(https?:\/\/[^\s<]+)/g,
        '<a href="$1" target="_blank" rel="noopener noreferrer" class="text-primary underline-offset-2 hover:underline">$1</a>',
    );
}

function renderInline(text: string): string {
    const out: string[] = [];
    const re = /`([^`\n]+)`/g;
    let last = 0;
    let m: RegExpExecArray | null;
    while ((m = re.exec(text)) !== null) {
        if (m.index > last) out.push(escapeAndLink(text.slice(last, m.index)));
        out.push(`<code class="rounded bg-muted px-1 py-0.5 text-[0.85em]">${escapeHtml(m[1])}</code>`);
        last = m.index + m[0].length;
    }
    if (last < text.length) out.push(escapeAndLink(text.slice(last)));
    return out.join('');
}

/** Markdown-lite: fenced + inline code, autolinks. Returns HTML for dangerouslySetInnerHTML. */
export function renderTicketText(text: string | null | undefined): string {
    if (!text) return '';
    const out: string[] = [];
    const re = /```(?:\w*)\n?([\s\S]*?)```/g;
    let last = 0;
    let m: RegExpExecArray | null;
    while ((m = re.exec(text)) !== null) {
        if (m.index > last) out.push(renderInline(text.slice(last, m.index)));
        out.push(
            `<pre class="overflow-x-auto rounded-md bg-muted p-3 text-xs"><code>${escapeHtml(m[1])}</code></pre>`,
        );
        last = m.index + m[0].length;
    }
    if (last < text.length) out.push(renderInline(text.slice(last)));
    return out.join('');
}

export function truncate(text: string, len = 140): string {
    return text.length > len ? text.slice(0, len).trimEnd() + '…' : text;
}
