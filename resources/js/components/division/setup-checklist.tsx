import { CircleCheck, CircleDashed } from 'lucide-react';

import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export interface SetupStep {
    key: string;
    label: string;
    done: boolean;
    hint: string;
    action?: { label: string; href: string };
}

export function SetupChecklist({ steps }: { steps: SetupStep[] }) {
    const completed = steps.filter((step) => step.done).length;

    return (
        <section className="rounded-md border border-primary/30 bg-primary/5 p-4">
            <div className="mb-3 flex items-baseline justify-between gap-3">
                <h2 className="text-sm font-semibold">Getting this division started</h2>
                <span className="numeric text-xs text-muted-foreground">
                    {completed} of {steps.length} done
                </span>
            </div>
            <ol className="space-y-2">
                {steps.map((step) => (
                    <li key={step.key} className="flex items-center gap-3 rounded-md border border-border bg-card px-3 py-2.5">
                        {step.done ? (
                            <CircleCheck className="size-4 shrink-0 text-success" aria-label="Done" />
                        ) : (
                            <CircleDashed className="size-4 shrink-0 text-muted-foreground" aria-label="Not done" />
                        )}
                        <div className="min-w-0 flex-1">
                            <p className={cn('text-sm font-medium', step.done && 'text-muted-foreground line-through')}>{step.label}</p>
                            {!step.done && <p className="text-xs text-muted-foreground">{step.hint}</p>}
                        </div>
                        {!step.done && step.action && (
                            <Button size="xs" variant="outline" asChild>
                                <a href={step.action.href}>{step.action.label}</a>
                            </Button>
                        )}
                    </li>
                ))}
            </ol>
        </section>
    );
}
