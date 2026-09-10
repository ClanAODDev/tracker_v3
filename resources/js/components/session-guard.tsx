import { router } from '@inertiajs/react';
import { Clock } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { getJson } from '@/lib/api';

const WARN_BEFORE_MS = 2 * 60 * 1000;
const ACTIVITY_KEY = 'session:activity';

function formatCountdown(ms: number): string {
    const total = Math.max(0, Math.ceil(ms / 1000));
    return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, '0')}`;
}

function readActivity(): number {
    try {
        return Number(localStorage.getItem(ACTIVITY_KEY)) || 0;
    } catch {
        return 0;
    }
}

export function SessionGuard({ lifetimeMinutes }: { lifetimeMinutes: number }) {
    const lifetimeMs = Math.max(1, lifetimeMinutes) * 60 * 1000;
    const expiresAt = useRef(Math.max(Date.now(), readActivity()) + lifetimeMs);
    const [remaining, setRemaining] = useState<number | null>(null);
    const [extending, setExtending] = useState(false);

    const touch = useCallback(() => {
        const now = Date.now();
        expiresAt.current = now + lifetimeMs;
        try {
            localStorage.setItem(ACTIVITY_KEY, String(now));
        } catch {
            /* private mode */
        }
    }, [lifetimeMs]);

    useEffect(() => {
        const stopInertia = router.on('success', touch);
        window.addEventListener('session:touch', touch);

        function onStorage(event: StorageEvent) {
            if (event.key === ACTIVITY_KEY && event.newValue) {
                expiresAt.current = Number(event.newValue) + lifetimeMs;
            }
        }
        window.addEventListener('storage', onStorage);

        return () => {
            stopInertia();
            window.removeEventListener('session:touch', touch);
            window.removeEventListener('storage', onStorage);
        };
    }, [touch, lifetimeMs]);

    useEffect(() => {
        const id = window.setInterval(() => {
            const left = expiresAt.current - Date.now();
            if (left <= 0) {
                window.location.assign('/login?expired=1');
            } else {
                setRemaining(left <= WARN_BEFORE_MS ? left : null);
            }
        }, 1000);
        return () => window.clearInterval(id);
    }, []);

    async function stay() {
        if (extending) return;
        setExtending(true);
        try {
            const res = await getJson<{ expiresAt: number }>('/session/keep-alive');
            const until = res.expiresAt * 1000;
            expiresAt.current = until;
            try {
                localStorage.setItem(ACTIVITY_KEY, String(until - lifetimeMs));
            } catch {
                /* private mode */
            }
            setRemaining(null);
        } catch {
            /* getJson redirects to login on a 401/419 */
        } finally {
            setExtending(false);
        }
    }

    return (
        <Dialog open={remaining !== null} onOpenChange={() => undefined}>
            <DialogContent aria-describedby={undefined} className="max-w-sm" showCloseButton={false}>
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Clock className="size-4 text-warning" /> Still there?
                    </DialogTitle>
                    <DialogDescription>
                        You&apos;ll be signed out for inactivity in{' '}
                        <span className="numeric font-semibold text-foreground">
                            {formatCountdown(remaining ?? 0)}
                        </span>
                        .
                    </DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <Button onClick={stay} disabled={extending}>
                        {extending ? 'Staying…' : 'Stay signed in'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
