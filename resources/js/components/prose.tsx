import { cn } from '@/lib/utils';

export function Prose({ html, className }: { html: string; className?: string }) {
    return (
        <div
            className={cn('prose-tron', className)}
            dangerouslySetInnerHTML={{ __html: html }}
        />
    );
}
