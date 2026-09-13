import { Link } from '@inertiajs/react';
import { ArrowLeft, CircleCheck, Copy } from 'lucide-react';
import { toast } from 'sonner';

import type { RecruitForm } from '@/components/recruit/use-recruit-form';
import { Button } from '@/components/ui/button';

export function ConfirmationStep({ form }: { form: RecruitForm }) {
    const { props, member } = form;
    const platoon = props.platoons.find((p) => p.id === Number(member.platoon));
    const squad = platoon?.squads.find((s) => s.id === Number(member.squad));
    const assignment = [platoon?.name, squad?.name].filter(Boolean).join(' › ');
    const welcomePm = (props.welcome_pm || '')
        .replace(/\{\{\s*name\s*\}\}/g, member.forum_name)
        .replace(/\{\{\s*ingame_name\s*\}\}/g, member.ingame_name);

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

            {(props.welcome_area || props.welcome_pm) && (
                <section className="overflow-hidden rounded-md border border-border">
                    <h3 className="border-b border-border bg-card/40 px-4 py-2 text-sm font-semibold">Housekeeping</h3>
                    <div className="space-y-4 p-4">
                        {props.welcome_area && (
                            <div>
                                <p className="text-sm font-medium">Create welcome post</p>
                                <Button size="sm" variant="outline" className="mt-1.5" asChild>
                                    <a
                                        href={
                                            props.use_welcome_thread
                                                ? `https://www.clanaod.net/forums/newreply.php?do=newreply&t=${props.welcome_area}`
                                                : `https://www.clanaod.net/forums/newthread.php?do=newthread&f=${props.welcome_area}`
                                        }
                                        target="_blank"
                                        rel="noreferrer"
                                    >
                                        {props.use_welcome_thread ? 'Create post' : 'Create thread'}
                                    </a>
                                </Button>
                            </div>
                        )}
                        {props.welcome_pm && (
                            <div>
                                <p className="text-sm font-medium">Send welcome DM</p>
                                <textarea
                                    readOnly
                                    rows={4}
                                    value={welcomePm}
                                    className="mt-1.5 w-full rounded-md border border-border bg-transparent p-2 text-sm"
                                />
                                <div className="mt-1.5 flex gap-2">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        onClick={() =>
                                            navigator.clipboard.writeText(welcomePm).then(() => toast.success('Copied'))
                                        }
                                    >
                                        <Copy /> Copy
                                    </Button>
                                    <Button size="sm" variant="outline" asChild>
                                        <a
                                            href={`https://clanaod.net/forums/private.php?do=newpm&u=${member.id}`}
                                            target="_blank"
                                            rel="noreferrer"
                                        >
                                            Send forum PM
                                        </a>
                                    </Button>
                                </div>
                            </div>
                        )}
                    </div>
                </section>
            )}

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
