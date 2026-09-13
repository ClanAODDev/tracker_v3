import { Head, useForm } from '@inertiajs/react';

import { Button } from '@/components/ui/button';
import BlankLayout from '@/layouts/BlankLayout';

interface Props {
    memberName: string;
    rankLabel: string;
    trainingNote: boolean;
    expiresText: string;
    acceptUrl: string;
    declineUrl: string;
}

export default function Promotion({ memberName, rankLabel, trainingNote, expiresText, acceptUrl, declineUrl }: Props) {
    const accept = useForm({});
    const decline = useForm({});
    const working = accept.processing || decline.processing;

    return (
        <BlankLayout>
            <Head title="Promotion pending" />
            <div className="tron-corners mx-auto mt-16 max-w-lg rounded-md border border-border bg-card p-8">
                <p className="tron-eyebrow">Promotion pending</p>
                <h1 className="mt-3 text-xl font-semibold">Congratulations, {memberName}</h1>

                <p className="mt-5 text-sm text-muted-foreground">
                    The Angels of Death congratulate you on your achievements and contributions to the community. Please
                    indicate whether you choose to accept the rank of:
                </p>
                <p className="mt-3 text-lg font-semibold uppercase tracking-wide text-primary">{rankLabel}</p>

                {trainingNote && (
                    <p className="mt-4 text-sm text-muted-foreground">
                        Your promotion takes effect immediately after accepting. Additional training is required before
                        full permissions are granted — seek assistance from division or clan leadership on next steps.
                    </p>
                )}

                <p className="mt-4 text-xs text-muted-foreground">This page will expire {expiresText}.</p>

                <div className="mt-8 flex items-center justify-between gap-3">
                    <Button
                        variant="outline"
                        size="sm"
                        disabled={working}
                        onClick={() => {
                            if (confirm('By declining this promotion, you agree to remain at your current rank.')) {
                                decline.post(declineUrl);
                            }
                        }}
                    >
                        Decline
                    </Button>
                    <Button
                        size="sm"
                        disabled={working}
                        onClick={() => {
                            if (confirm('Upon acceptance, your forum rank will be updated automatically.')) {
                                accept.post(acceptUrl);
                            }
                        }}
                    >
                        Accept promotion
                    </Button>
                </div>
            </div>
        </BlankLayout>
    );
}
