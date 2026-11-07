import { Link } from '@inertiajs/react';
import { ChevronsUpDown, Eye, LogOut, Settings, ShieldAlert, UserCog } from 'lucide-react';

import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuSub,
    DropdownMenuSubContent,
    DropdownMenuSubTrigger,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAuth } from '@/hooks/use-shared';

const VIEW_AS_ROLES = [
    { slug: 'member', label: 'Member' },
    { slug: 'officer', label: 'Officer' },
    { slug: 'sr_ldr', label: 'Senior Leader' },
];

function initials(name: string) {
    return name
        .split(/\s+/)
        .map((part) => part[0])
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

export function UserMenu({ onOpenSettings }: { onOpenSettings: () => void }) {
    const { user, permissions } = useAuth();
    if (!user) return null;

    const canViewAsRole = (permissions?.isAdmin || permissions?.isDeveloper) && !user.impersonatingRole;

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
                        <Link href="/impersonate-end" method="post" as="button" className="w-full text-destructive">
                            <ShieldAlert className="size-4" /> End impersonation
                        </Link>
                    </DropdownMenuItem>
                )}
                {user.impersonatingRole && (
                    <DropdownMenuItem asChild>
                        <Link href="/impersonate-role-end" method="post" as="button" className="w-full text-warning">
                            <UserCog className="size-4" /> End role view
                        </Link>
                    </DropdownMenuItem>
                )}
                {canViewAsRole && (
                    <DropdownMenuSub>
                        <DropdownMenuSubTrigger>
                            <Eye className="size-4" /> View as role
                        </DropdownMenuSubTrigger>
                        <DropdownMenuSubContent>
                            {VIEW_AS_ROLES.map((role) => (
                                <DropdownMenuItem key={role.slug} asChild>
                                    <Link href={`/impersonate-role/${role.slug}`} method="post" as="button" className="w-full">
                                        {role.label}
                                    </Link>
                                </DropdownMenuItem>
                            ))}
                        </DropdownMenuSubContent>
                    </DropdownMenuSub>
                )}
                <DropdownMenuItem onSelect={() => onOpenSettings()}>
                    <Settings className="size-4" /> Settings
                </DropdownMenuItem>
                <DropdownMenuItem asChild>
                    <Link href="/logout" method="post" as="button" className="w-full">
                        <LogOut className="size-4" /> Log out
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
