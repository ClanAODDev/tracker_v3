export function relativeDate(input: string | null | undefined): string {
    if (!input) return '';
    const date = new Date(input);
    const diffMs = Date.now() - date.getTime();
    const mins = Math.floor(diffMs / 60000);
    const hours = Math.floor(diffMs / 3600000);
    const days = Math.floor(diffMs / 86400000);

    if (mins < 1) return 'just now';
    if (mins < 60) return `${mins}m ago`;
    if (hours < 24) return `${hours}h ago`;
    if (days < 7) return `${days}d ago`;
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

const URL_RE = /(https?:\/\/[^\s<]+)/g;
const LINK_ATTRS = 'target="_blank" rel="noopener noreferrer" class="text-primary underline-offset-2 hover:underline"';

/** Escape HTML then turn bare URLs into links. Returns a string for dangerouslySetInnerHTML. */
export function linkify(text: string | null | undefined): string {
    if (!text) return '';
    const escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    return escaped.replace(URL_RE, `<a href="$1" ${LINK_ATTRS}>$1</a>`);
}

/**
 * Turn bare URLs into links WITHOUT escaping existing markup. Use only for
 * trusted, admin-authored content that is allowed to contain HTML (e.g. a
 * division's in-processing task descriptions). URLs already inside an
 * `href="…"` / `src="…"` attribute or `<a>…</a>` text are left alone.
 */
export function linkifyHtml(text: string | null | undefined): string {
    if (!text) return '';
    const parts = text.split(/(<a\b[^>]*>.*?<\/a>|<[^>]+>)/gis);
    return parts
        .map((part, i) =>
            i % 2 === 0 ? part.replace(URL_RE, `<a href="$1" ${LINK_ATTRS}>$1</a>`) : part,
        )
        .join('');
}
