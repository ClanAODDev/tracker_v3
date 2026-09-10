import { Head, useForm } from '@inertiajs/react';
import { ArrowLeft, ArrowRight, Check } from 'lucide-react';
import { useMemo, useState } from 'react';

import { Prose } from '@/components/prose';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';
import AppLayout from '@/layouts/AppLayout';

interface Checkpoint {
    label: string;
    description: string | null;
}

interface Section {
    title: string;
    content: string;
    checkpoints: Checkpoint[];
}

interface TrainingModuleProps {
    module: {
        slug: string;
        name: string;
        checkpointLabel: string;
        showCompletionForm: boolean;
        sections: Section[];
    };
    trainee: { clanId: number; name: string; rankName: string } | null;
}

export default function TrainingModulePage({ module, trainee }: TrainingModuleProps) {
    const [active, setActive] = useState(0);
    const [checked, setChecked] = useState<Record<string, boolean>>({});
    const section = module.sections[active];

    const totals = useMemo(() => {
        const all = module.sections.flatMap((s, si) => s.checkpoints.map((_, ci) => `${si}:${ci}`));
        const done = all.filter((key) => checked[key]).length;
        return { all: all.length, done };
    }, [module.sections, checked]);

    const form = useForm({ module: module.slug, clan_id: trainee?.clanId ?? '' });

    return (
        <AppLayout
            header={{
                eyebrow: 'Leadership Training',
                title: module.name,
                breadcrumbs: [{ label: 'Training', href: '/training' }, { label: module.name }],
            }}
        >
            <Head title={module.name} />

            <div className="flex gap-6">
                <aside className="hidden w-64 shrink-0 lg:block">
                    <ol className="space-y-1">
                        {module.sections.map((s, index) => {
                            const total = s.checkpoints.length;
                            const done = s.checkpoints.filter((_, ci) => checked[`${index}:${ci}`]).length;
                            return (
                                <li key={s.title}>
                                    <button
                                        onClick={() => setActive(index)}
                                        className={cn(
                                            'flex w-full items-center gap-3 rounded-md px-3 py-2 text-left text-sm transition-colors',
                                            index === active
                                                ? 'bg-primary/10 text-foreground'
                                                : 'text-muted-foreground hover:bg-accent hover:text-foreground',
                                        )}
                                    >
                                        <span
                                            className={cn(
                                                'numeric flex size-6 shrink-0 items-center justify-center rounded border text-xs',
                                                index === active
                                                    ? 'border-primary/50 text-primary'
                                                    : 'border-border',
                                            )}
                                        >
                                            {index + 1}
                                        </span>
                                        <span className="flex-1 truncate">{s.title}</span>
                                        {total > 0 && (
                                            <span className="numeric text-xs text-muted-foreground">
                                                {done}/{total}
                                            </span>
                                        )}
                                    </button>
                                </li>
                            );
                        })}
                    </ol>

                    <div className="mt-4 rounded-md border border-border p-3">
                        <div className="flex items-center justify-between text-xs text-muted-foreground">
                            <span>Progress</span>
                            <span className="numeric">
                                {totals.done}/{totals.all}
                            </span>
                        </div>
                        <div className="mt-2 h-1 overflow-hidden rounded-full bg-muted">
                            <div
                                className="h-full bg-primary transition-all"
                                style={{ width: `${totals.all ? (totals.done / totals.all) * 100 : 0}%` }}
                            />
                        </div>
                    </div>

                    {trainee && (
                        <p className="mt-4 text-xs text-muted-foreground">
                            Trainee: <strong className="text-foreground">{trainee.rankName}</strong>
                        </p>
                    )}
                </aside>

                <div className="min-w-0 flex-1">
                    <h2 className="mb-4 text-lg font-semibold">{section.title}</h2>

                    <Prose html={section.content} />

                    {section.checkpoints.length > 0 && (
                        <div className="mt-6 rounded-md border border-border bg-card p-4">
                            <h3 className="mb-3 text-xs font-semibold uppercase tracking-[0.2em] text-primary">
                                {module.checkpointLabel}
                            </h3>
                            <div className="space-y-1">
                                {section.checkpoints.map((checkpoint, ci) => {
                                    const key = `${active}:${ci}`;
                                    return (
                                        <label
                                            key={key}
                                            className="flex cursor-pointer gap-3 rounded-md p-2 hover:bg-accent/50"
                                        >
                                            <input
                                                type="checkbox"
                                                checked={!!checked[key]}
                                                onChange={(e) =>
                                                    setChecked((prev) => ({ ...prev, [key]: e.target.checked }))
                                                }
                                                className="mt-0.5 size-4 shrink-0 accent-primary"
                                            />
                                            <span className="text-sm">
                                                <span className={checked[key] ? 'text-muted-foreground line-through' : ''}>
                                                    {checkpoint.label}
                                                </span>
                                                {checkpoint.description && (
                                                    <Prose html={checkpoint.description} className="mt-1" />
                                                )}
                                            </span>
                                        </label>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    <div className="mt-6 flex items-center justify-between">
                        <Button
                            variant="outline"
                            size="sm"
                            disabled={active === 0}
                            onClick={() => setActive((i) => Math.max(0, i - 1))}
                        >
                            <ArrowLeft /> Previous
                        </Button>
                        {active < module.sections.length - 1 ? (
                            <Button size="sm" onClick={() => setActive((i) => i + 1)}>
                                Next <ArrowRight />
                            </Button>
                        ) : (
                            <span />
                        )}
                    </div>

                    {trainee && module.showCompletionForm && (
                        <div className="mt-8 rounded-md border border-success/30 bg-success/5 p-4">
                            <p className="text-sm font-medium">Complete training</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Mark this session complete for <strong>{trainee.name}</strong> — updates their last
                                training date and records you as the trainer.
                            </p>
                            <Dialog>
                                <DialogTrigger asChild>
                                    <Button className="mt-3" size="sm">
                                        <Check /> Mark training complete
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Confirm training completion</DialogTitle>
                                        <DialogDescription>
                                            Mark <strong>{trainee.name}</strong> as having completed {module.name}?
                                        </DialogDescription>
                                    </DialogHeader>
                                    <DialogFooter>
                                        <Button
                                            onClick={() => form.post('/training')}
                                            disabled={form.processing}
                                        >
                                            <Check /> Confirm
                                        </Button>
                                    </DialogFooter>
                                </DialogContent>
                            </Dialog>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
