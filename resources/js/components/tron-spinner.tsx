import { cn } from '@/lib/utils';

export function TronSpinner({ className }: { className?: string }) {
    return (
        <svg
            role="status"
            aria-label="Loading"
            viewBox="0 0 24 24"
            fill="none"
            className={cn('inline-block size-4 shrink-0 text-primary', className)}
        >
            <rect x="3" y="3" width="18" height="18" stroke="currentColor" strokeOpacity="0.16" strokeWidth="2.5" />
            <rect
                x="3"
                y="3"
                width="18"
                height="18"
                stroke="currentColor"
                strokeWidth="2.5"
                strokeLinecap="square"
                strokeDasharray="18 54"
                className="tron-spinner-trace"
            />
        </svg>
    );
}
