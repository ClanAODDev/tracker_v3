import { Head } from '@inertiajs/react';

import { ConfirmationStep } from '@/components/recruit/steps/confirmation-step';
import { FormStep } from '@/components/recruit/steps/form-step';
import type { RecruitFormProps } from '@/components/recruit/types';
import { useRecruitForm } from '@/components/recruit/use-recruit-form';
import AppLayout from '@/layouts/AppLayout';

export default function RecruitingForm(props: RecruitFormProps) {
    const form = useRecruitForm(props);

    return (
        <AppLayout
            header={{
                eyebrow: 'Recruiting',
                title: `Add recruit — ${props.name}`,
                breadcrumbs: [
                    { label: 'Recruiting', href: '/recruit' },
                    { label: props.name, href: props.cancelUrl },
                    { label: 'Add recruit' },
                ],
            }}
        >
            <Head title={`Add recruit — ${props.name}`} />
            <div className="mx-auto max-w-3xl">
                {form.step === 'form' ? <FormStep form={form} /> : <ConfirmationStep form={form} />}
            </div>
        </AppLayout>
    );
}
