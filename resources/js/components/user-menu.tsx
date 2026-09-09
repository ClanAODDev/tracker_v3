import { ChevronsUpDown, LogOut, Settings, ShieldAlert, UserCog } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAuth } from '@/hooks/use-shared';

function initials(name: string) {
    return name
        .split(/\s+/)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

export function UserMenu({ onOpenSettings }: { onOpenSettings: () => void }) {
    const { user } = useAuth();
    if (!user) return null;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger className="flex items-center gap-2 rounded-md px-2 py-1.5 text-sm outline-none transition-colors hover:bg-accent data-[state=open]:bg-accent">
                <Avatar className="size-7 rounded-md">
                    {user.avatarUrl && <AvatarImage src={user.avatarUrl} alt={user.name} />}
                    <AvatarFallback className="rounded-md bg-secondary text-xs">{initials(user.name)}</AvatarFallback>
                </Avatar>
                <span className="hidden max-w-[12rem] truncate sm:block">{user.name}</span>
                <ChevronsUpDown className="hidden size-3.5 text-muted-foreground sm:block" />
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-56">
                <DropdownMenuLabel className="flex flex-col gap-0.5">
                    <span className="truncate">{user.name}</span>
                    {user.effectiveRole && (
                        <span className="text-xs font-normal text-muted-foreground">{user.effectiveRole}</span>
                    )}
                </DropdownMenuLabel>
                <DropdownMenuSeparator />
                {user.impersonating && (
                    <DropdownMenuItem asChild>
                        <a href="/impersonate-end" className="text-destructive">
                            <ShieldAlert className="size-4" /> End impersonation
                        </a>
                    </DropdownMenuItem>
                )}
                {user.impersonatingRole && (
                    <DropdownMenuItem asChild>
                        <a href="/impersonate-role-end" className="text-warning">
                            <UserCog className="size-4" /> End role view
                        </a>
                    </DropdownMenuItem>
                )}
                <DropdownMenuItem onSelect={() => onOpenSettings()}>
                    <Settings className="size-4" /> Settings
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <a href="/logout">
                        <LogOut className="size-4" /> Log out
                    </a>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
