import { Head, Link, useForm } from '@inertiajs/react';
import { TriangleAlert } from 'lucide-react';

import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout';
import type { MemberCard } from '@/types';

interface Props {
    member: MemberCard;
    resetUrl: string;
}

export default function ConfirmUnassign({ member, resetUrl }: Props) {
    const form = useForm({});
    const profileUrl = member.profileUrl;

    return (
        <AppLayout
            header={{
                eyebrow: member.division ?? 'Member',
                title: member.rankName,
                breadcrumbs: [{ label: member.name, href: profileUrl }, { label: 'Reset assignments' }],
            }}
        >
            <Head title="Reset assignments" />

            <div className="tron-corners max-w-lg rounded-md border border-border bg-card p-6">
                <h2 className="flex items-center gap-2 text-base font-semibold">
                    <TriangleAlert className="size-4 text-destructive" />
                    Reset member assignments
                </h2>
                <p className="mt-3 text-sm text-muted-foreground">
                    You are about to clear the platoon and squad assignments for{' '}
                    <span className="text-foreground">{member.name}</span> ({member.position ?? 'no position'}). This
                    cannot be undone from here.
                </p>

                <div className="mt-6 flex items-center gap-3">
                    <Button variant="outline" size="sm" asChild>
                        <Link href={profileUrl}>Cancel</Link>
                    </Button>
                    <Button
                        size="sm"
                        disabled={form.processing}
                        onClick={() => form.post(resetUrl)}
                    >
                        Reset assignments
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
