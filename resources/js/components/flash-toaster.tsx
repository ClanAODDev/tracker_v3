import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import { toast } from 'sonner';

import { Toaster } from '@/components/ui/sonner';
import type { SharedProps } from '@/types';

export function FlashToaster() {
    const { flash } = usePage<SharedProps>().props;
    const lastPayload = useRef<string>('');

    useEffect(() => {
        const toasts = flash?.toasts ?? [];
        if (toasts.length === 0) return;

        const payload = JSON.stringify(toasts);
        if (payload === lastPayload.current) return;
        lastPayload.current = payload;

        for (const item of toasts) {
            const fn =
                item.type === 'success'
                    ? toast.success
                    : item.type === 'error'
                      ? toast.error
                      : item.type === 'warning'
                        ? toast.warning
                        : toast.info;
            fn(item.title ?? item.message, item.title ? { description: item.message } : undefined);
        }
    }, [flash]);

    return <Toaster position="top-right" closeButton richColors={false} />;
}
