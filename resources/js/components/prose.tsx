import { type KeyboardEvent, type MouseEvent, useEffect, useRef, useState } from 'react';

import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

interface EnlargedImage {
    src: string;
    alt: string;
}

export function Prose({
    html,
    className,
    zoomableImages = false,
}: {
    html: string;
    className?: string;
    zoomableImages?: boolean;
}) {
    const container = useRef<HTMLDivElement>(null);
    const [enlarged, setEnlarged] = useState<EnlargedImage | null>(null);

    useEffect(() => {
        if (!zoomableImages || !container.current) return;
        container.current.querySelectorAll('img').forEach((img) => {
            img.tabIndex = 0;
            img.setAttribute('role', 'button');
            img.setAttribute('aria-label', img.alt ? `Enlarge image: ${img.alt}` : 'Enlarge image');
        });
    }, [html, zoomableImages]);

    function enlarge(target: EventTarget) {
        if (target instanceof HTMLImageElement) {
            setEnlarged({ src: target.currentSrc || target.src, alt: target.alt });
            return true;
        }
        return false;
    }

    function onClick(event: MouseEvent<HTMLDivElement>) {
        if (zoomableImages && enlarge(event.target)) event.preventDefault();
    }

    function onKeyDown(event: KeyboardEvent<HTMLDivElement>) {
        if (zoomableImages && (event.key === 'Enter' || event.key === ' ') && enlarge(event.target)) {
            event.preventDefault();
        }
    }

    return (
        <>
            <div
                ref={container}
                className={cn('prose-tron', zoomableImages && 'prose-tron-zoomable', className)}
                onClick={onClick}
                onKeyDown={onKeyDown}
                dangerouslySetInnerHTML={{ __html: html }}
            />
            {zoomableImages && (
                <Dialog open={enlarged !== null} onOpenChange={(open) => !open && setEnlarged(null)}>
                    <DialogContent className="w-auto max-w-[95vw] p-2 sm:max-w-[95vw]" aria-describedby={undefined}>
                        <DialogTitle className="sr-only">{enlarged?.alt || 'Image'}</DialogTitle>
                        {enlarged && (
                            <img
                                src={enlarged.src}
                                alt={enlarged.alt}
                                className="max-h-[85vh] max-w-full rounded object-contain"
                            />
                        )}
                    </DialogContent>
                </Dialog>
            )}
        </>
    );
}
