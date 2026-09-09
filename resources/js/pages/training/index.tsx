import { Head, Link } from '@inertiajs/react';
import { ArrowRight, GraduationCap } from 'lucide-react';

import AppLayout from '@/layouts/AppLayout';

interface TrainingModuleSummary {
    slug: string;
    name: string;
    description: string | null;
    sectionsCount: number;
}

interface TrainingIndexProps {
    modules: TrainingModuleSummary[];
}

export default function TrainingIndex({ modules }: TrainingIndexProps) {
    return (
        <AppLayout
            header={{
                eyebrow: 'Leadership Training',
                title: 'Training Modules',
                breadcrumbs: [{ label: 'Training' }],
            }}
        >
            <Head title="Training Modules" />

            {modules.length === 0 ? (
                <p className="text-sm text-muted-foreground">No training modules are available to you right now.</p>
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {modules.map((module) => (
                        <Link
                            key={module.slug}
                            href={`/training/${module.slug}`}
                            className="group flex flex-col justify-between rounded-md border border-border bg-card p-5 transition-colors hover:border-primary/40"
                        >
                            <div>
                                <div className="mb-3 flex size-9 items-center justify-center rounded-md border border-primary/30 bg-primary/10 text-primary">
                                    <GraduationCap className="size-4" />
                                </div>
                                <h3 className="text-sm font-semibold">{module.name}</h3>
                                {module.description && (
                                    <p className="mt-1 text-sm text-muted-foreground">{module.description}</p>
                                )}
                            </div>
                            <div className="mt-4 flex items-center justify-between text-xs text-muted-foreground">
                                <span className="numeric">{module.sectionsCount} sections</span>
                                <ArrowRight className="size-4 transition-transform group-hover:translate-x-0.5 group-hover:text-primary" />
                            </div>
                        </Link>
                    ))}
                </div>
            )}
        </AppLayout>
    );
}
