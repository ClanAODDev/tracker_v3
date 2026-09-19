import { UsersRound } from 'lucide-react';
import { useEffect, useState } from 'react';

import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { getJson } from '@/lib/api';

interface UnassignedMember {
    id: number;
    name: string;
    platoon: string;
    platoon_id: number;
    manage_url: string;
}

interface Props {
    url: string;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}

export function NoSquadModal({ url, open, onOpenChange }: Props) {
    const [members, setMembers] = useState<UnassignedMember[]>([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        setLoading(true);
        setError(null);

        getJson<{ members: UnassignedMember[] }>(url)
            .then((data) => setMembers(data.members))
            .catch(() => setError('Failed to load members.'))
            .finally(() => setLoading(false));
    }, [open, url]);

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent aria-describedby={undefined} className="max-h-[85vh] max-w-lg gap-0 overflow-hidden p-0">
                <DialogHeader className="border-b border-border px-6 py-4">
                    <DialogTitle className="flex items-center gap-2">
                        <UsersRound className="size-5 text-primary" /> Members Without Squad Assignment
                    </DialogTitle>
                </DialogHeader>

                <div className="max-h-[60vh] overflow-y-auto">
                    {loading ? (
                        <p className="px-6 py-10 text-center text-sm text-muted-foreground">Loading...</p>
                    ) : error ? (
                        <p className="px-6 py-10 text-center text-sm text-destructive">{error}</p>
                    ) : members.length === 0 ? (
                        <p className="px-6 py-10 text-center text-sm text-muted-foreground">
                            No members without a squad.
                        </p>
                    ) : (
                        <ul className="divide-y divide-border">
                            {members.map((member) => (
                                <li key={member.id} className="flex items-center justify-between gap-3 px-6 py-3 text-sm">
                                    <span>{member.name}</span>
                                    <a href={member.manage_url} className="font-medium text-primary hover:underline">
                                        {member.platoon}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}
                </div>
            </DialogContent>
        </Dialog>
    );
}
