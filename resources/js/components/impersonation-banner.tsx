import { ShieldAlert, UserCog } from 'lucide-react';

import { useAuth } from '@/hooks/use-shared';

export function ImpersonationBanner() {
    const { user } = useAuth();
    if (!user?.impersonating && !user?.impersonatingRole) return null;

    return (
        <div className="flex flex-col">
            {user.impersonating && (
                <div className="flex items-center justify-between gap-3 border-b border-destructive/40 bg-destructive/10 px-6 py-2 text-sm text-destructive">
                    <span className="flex items-center gap-2">
                        <ShieldAlert className="size-4" /> Impersonating <strong>{user.name}</strong>
                    </span>
                    <a href="/impersonate-end" className="font-medium underline underline-offset-2">
                        End
                    </a>
                </div>
            )}
            {user.impersonatingRole && (
                <div className="flex items-center justify-between gap-3 border-b border-warning/40 bg-warning/10 px-6 py-2 text-sm text-warning">
                    <span className="flex items-center gap-2">
                        <UserCog className="size-4" /> Viewing as <strong>{user.effectiveRole}</strong>
                    </span>
                    <a href="/impersonate-role-end" className="font-medium underline underline-offset-2">
                        End
                    </a>
                </div>
            )}
        </div>
    );
}
