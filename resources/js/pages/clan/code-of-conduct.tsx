import { Head } from '@inertiajs/react';

import { useAuth } from '@/hooks/use-shared';
import AppLayout from '@/layouts/AppLayout';
import BlankLayout from '@/layouts/BlankLayout';

interface CodeOfConductProps {
    rules: string[];
}

function Content({ rules }: CodeOfConductProps) {
    return (
        <>
            <div className="space-y-4 text-sm leading-relaxed text-muted-foreground">
                <p>
                    The Angels of Death Code of Conduct is the face of our clan. It is not only a guideline for member
                    conduct — it is a statement about what kind of gamers we are, and who we're looking for. AOD is an
                    honor clan first. This includes{' '}
                    <strong className="text-foreground">
                        zero tolerance for hacking or any other malicious game modding.
                    </strong>
                </p>
                <p>
                    All members agree to the Code of Conduct upon being accepted into the clan, and are expected to uphold
                    it at all times.
                </p>
            </div>

            <ol className="mt-8 grid gap-3 sm:grid-cols-2">
                {rules.map((rule, index) => (
                    <li
                        key={index}
                        className="flex gap-4 rounded-md border border-border bg-card p-4 transition-colors hover:border-primary/40"
                    >
                        <span className="numeric text-sm text-primary">{String(index + 1).padStart(2, '0')}</span>
                        <span className="text-sm text-card-foreground">{rule}</span>
                    </li>
                ))}
            </ol>

            <div className="mt-8 rounded-md border border-primary/30 bg-primary/5 p-4">
                <p className="text-sm text-foreground">
                    <strong>Violation of the Code of Conduct will result in removal from AOD.</strong> By joining the clan,
                    every member has agreed to uphold these standards at all times.
                </p>
            </div>
        </>
    );
}

export default function CodeOfConduct({ rules }: CodeOfConductProps) {
    const { user } = useAuth();
    const head = <Head title="Code of Conduct" />;

    if (user) {
        return (
            <AppLayout
                header={{
                    eyebrow: 'Angels of Death',
                    title: 'Code of Conduct',
                    breadcrumbs: [{ label: 'Clan Information' }, { label: 'Code of Conduct' }],
                }}
            >
                {head}
                <Content rules={rules} />
            </AppLayout>
        );
    }

    return (
        <BlankLayout title="Code of Conduct">
            {head}
            <Content rules={rules} />
        </BlankLayout>
    );
}
