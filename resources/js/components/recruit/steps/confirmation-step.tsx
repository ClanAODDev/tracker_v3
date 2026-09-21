import { Link } from '@inertiajs/react';
import { ArrowLeft, CircleCheck } from 'lucide-react';

import type { RecruitForm } from '@/components/recruit/use-recruit-form';
import { Button } from '@/components/ui/button';

export function ConfirmationStep({ form }: { form: RecruitForm }) {
    const { props, member } = form;
    const platoon = props.platoons.find((p) => p.id === Number(member.platoon));
    const squad = platoon?.squads.find((s) => s.id === Number(member.squad));
    const assignment = [platoon?.name, squad?.name].filter(Boolean).join(' › ');

    return (
        <div className="space-y-6">
            <div className="rounded-md border border-success/40 bg-success/5 p-6 text-center">
                <CircleCheck className="mx-auto size-10 text-success" />
                <h2 className="mt-2 text-lg font-semibold">Recruit added successfully</h2>
                <p className="mt-1 text-sm text-muted-foreground">
                    <strong>{form.formattedName}</strong> has been added to the division.
                    {assignment && (
                        <>
                            <br />
                            Assigned to: {assignment}
                        </>
                    )}
                </p>
            </div>

            <div className="flex justify-end gap-2">
                <Button variant="outline" size="sm" onClick={form.reset}>
                    Add another recruit
                </Button>
                <Button size="sm" asChild>
                    <Link href={props.cancelUrl}>
                        <ArrowLeft /> Back to division
                    </Link>
                </Button>
            </div>
        </div>
    );
}
