import { Head } from '@inertiajs/react';

import BlankLayout from '@/layouts/BlankLayout';

interface Props {
    memberName: string;
    outcome: 'accepted' | 'declined';
}

export default function PromotionConfirm({ memberName, outcome }: Props) {
    const accepted = outcome === 'accepted';

    return (
        <BlankLayout>
            <Head title="Promotion" />
            <div className="tron-corners mx-auto mt-16 max-w-lg rounded-md border border-border bg-card p-8">
                <p className="tron-eyebrow">{accepted ? 'Promotion accepted' : 'Promotion declined'}</p>
                <h1 className="mt-3 text-xl font-semibold">Congratulations, {memberName}</h1>

                {accepted ? (
                    <div className="mt-5 space-y-3 text-sm text-muted-foreground">
                        <p>Your promotion has been accepted, and will reflect on the forums and Discord momentarily.</p>
                        <p>Thank you for your contributions to the AOD community.</p>
                        <p>You may now close this window.</p>
                    </div>
                ) : (
                    <div className="mt-5 space-y-3 text-sm text-muted-foreground">
                        <p>
                            Your promotion has been declined. No change will be made, but a record will be kept should you
                            change your mind in the future.
                        </p>
                        <p>You may now close this window.</p>
                    </div>
                )}
            </div>
        </BlankLayout>
    );
}
