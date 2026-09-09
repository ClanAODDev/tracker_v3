import { Head, useForm } from '@inertiajs/react';
import { ArrowRight, MessageSquare } from 'lucide-react';
import { type FormEvent } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import BlankLayout from '@/layouts/BlankLayout';
import { cn } from '@/lib/utils';

interface ApplicationField {
    id: number;
    label: string;
    helperText: string | null;
    type: 'text' | 'textarea' | 'radio' | 'checkbox';
    required: boolean;
    options: string[];
}

interface Props {
    discordUsername: string;
    email: string;
    defaultUsername: string;
    preview: boolean;
    previewDivisionId: number | null;
    needsRegistration: boolean;
    divisions: Array<{ id: number; name: string; logo: string }>;
    applicationFields: ApplicationField[];
}

function Panel({ children }: { children: React.ReactNode }) {
    return (
        <div className="tron-corners rounded-md border border-border bg-card p-6">{children}</div>
    );
}

export default function DiscordPending(props: Props) {
    const { preview, needsRegistration, applicationFields } = props;
    const showRegister = preview || needsRegistration;

    return (
        <BlankLayout>
            <Head title="ClanAOD registration" />

            <div className="mx-auto max-w-xl space-y-6">
                {preview && (
                    <p className="rounded-md border border-warning/40 bg-warning/5 px-4 py-2 text-center text-sm">
                        Preview mode — registration flow
                    </p>
                )}

                <div className="text-center">
                    <div className="mb-3 flex items-center justify-center gap-3 text-2xl">
                        <MessageSquare className="size-6 text-info" />
                        <span className="text-muted-foreground">+</span>
                        <img src="/images/aod-logo.png" alt="AOD" className="h-7" />
                    </div>
                    <h1 className="text-lg font-semibold">ClanAOD registration</h1>
                    <p className="text-sm text-muted-foreground">
                        Welcome, <strong className="text-foreground">{props.discordUsername}</strong>
                    </p>
                </div>

                {showRegister && <RegisterForm {...props} />}
                {(preview || (!needsRegistration && applicationFields.length > 0)) && (
                    <ApplicationForm fields={applicationFields} disabled={preview} />
                )}
                {!preview && !needsRegistration && applicationFields.length === 0 && <RecruiterContact />}

                {!preview && (
                    <p className="text-center text-xs text-muted-foreground">
                        Already a member?{' '}
                        <a href="/logout" className="text-primary hover:underline">
                            Sign out and use forum login
                        </a>
                    </p>
                )}
            </div>
        </BlankLayout>
    );
}

function RegisterForm({
    divisions,
    email,
    defaultUsername,
    preview,
    previewDivisionId,
}: Props) {
    const form = useForm({
        division_id: previewDivisionId ? String(previewDivisionId) : '',
        username: defaultUsername,
        date_of_birth: '',
        password: '',
        password_confirmation: '',
    });

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post('/auth/discord/register');
    }

    return (
        <Panel>
            <p className="mb-4 text-center text-sm text-muted-foreground">
                Before we continue, we need a few more details.
            </p>
            <form onSubmit={submit} className="space-y-4">
                <fieldset disabled={preview} className="space-y-4">
                    <div className="space-y-1.5">
                        <Label>What game are you interested in playing? *</Label>
                        {form.errors.division_id && (
                            <p className="text-xs text-destructive">{form.errors.division_id}</p>
                        )}
                        <div className="grid grid-cols-2 gap-2 sm:grid-cols-3">
                            {divisions.map((division) => (
                                <label
                                    key={division.id}
                                    className={cn(
                                        'flex cursor-pointer flex-col items-center gap-1.5 rounded-md border p-3 text-center text-xs transition-colors',
                                        form.data.division_id === String(division.id)
                                            ? 'border-primary bg-primary/10'
                                            : 'border-border hover:border-primary/30',
                                    )}
                                >
                                    <input
                                        type="radio"
                                        name="division_id"
                                        value={division.id}
                                        checked={form.data.division_id === String(division.id)}
                                        onChange={() => form.setData('division_id', String(division.id))}
                                        className="sr-only"
                                    />
                                    <img src={division.logo} alt="" className="size-8" />
                                    {division.name}
                                </label>
                            ))}
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Forum username *" error={form.errors.username} hint="Letters, numbers, and underscores only.">
                            <Input
                                value={form.data.username}
                                onChange={(e) => form.setData('username', e.target.value)}
                                maxLength={50}
                                required
                            />
                        </Field>
                        <Field label="Email" hint="From your Discord account.">
                            <Input value={email} readOnly />
                        </Field>
                    </div>

                    <Field
                        label="Date of birth *"
                        error={form.errors.date_of_birth}
                        hint="You must be at least 13 years old to join."
                    >
                        <Input
                            type="date"
                            value={form.data.date_of_birth}
                            onChange={(e) => form.setData('date_of_birth', e.target.value)}
                            required
                        />
                    </Field>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Password *" error={form.errors.password}>
                            <Input
                                type="password"
                                autoComplete="new-password"
                                minLength={8}
                                value={form.data.password}
                                onChange={(e) => form.setData('password', e.target.value)}
                                required
                            />
                        </Field>
                        <Field label="Confirm password *" error={form.errors.password_confirmation}>
                            <Input
                                type="password"
                                autoComplete="new-password"
                                value={form.data.password_confirmation}
                                onChange={(e) => form.setData('password_confirmation', e.target.value)}
                                required
                            />
                        </Field>
                    </div>
                    <p className="text-xs text-muted-foreground">This will be your forum account password.</p>
                </fieldset>

                {!preview && (
                    <div className="text-center">
                        <Button type="submit" disabled={form.processing}>
                            Continue <ArrowRight />
                        </Button>
                    </div>
                )}
            </form>
        </Panel>
    );
}

function ApplicationForm({ fields, disabled }: { fields: ApplicationField[]; disabled: boolean }) {
    const initial = Object.fromEntries(
        fields.map((f) => [`field_${f.id}`, f.type === 'checkbox' ? [] : '']),
    );
    const form = useForm<Record<string, string | string[]>>(initial);

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post('/auth/discord/application');
    }

    return (
        <Panel>
            <p className="mb-4 text-center text-sm text-muted-foreground">
                Almost there — complete this application for your selected division.
            </p>
            <form onSubmit={submit} className="space-y-4">
                <fieldset disabled={disabled} className="space-y-4">
                    {fields.map((field) => {
                        const key = `field_${field.id}`;
                        const error = form.errors[key as keyof typeof form.errors] as string | undefined;
                        return (
                            <Field
                                key={field.id}
                                label={`${field.label}${field.required ? ' *' : ''}`}
                                error={error}
                                hint={field.helperText ?? undefined}
                            >
                                {field.type === 'text' && (
                                    <Input
                                        value={form.data[key] as string}
                                        onChange={(e) => form.setData(key, e.target.value)}
                                        maxLength={500}
                                        required={field.required}
                                    />
                                )}
                                {field.type === 'textarea' && (
                                    <textarea
                                        value={form.data[key] as string}
                                        onChange={(e) => form.setData(key, e.target.value)}
                                        rows={4}
                                        maxLength={500}
                                        required={field.required}
                                        className="w-full rounded-md border border-input bg-transparent p-2 text-sm outline-none focus:border-ring focus:ring-2 focus:ring-ring/40"
                                    />
                                )}
                                {field.type === 'radio' &&
                                    field.options.map((opt) => (
                                        <label key={opt} className="flex items-center gap-2 text-sm">
                                            <input
                                                type="radio"
                                                name={key}
                                                checked={form.data[key] === opt}
                                                onChange={() => form.setData(key, opt)}
                                                required={field.required}
                                            />
                                            {opt}
                                        </label>
                                    ))}
                                {field.type === 'checkbox' &&
                                    field.options.map((opt) => (
                                        <label key={opt} className="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                checked={(form.data[key] as string[]).includes(opt)}
                                                onChange={(e) => {
                                                    const cur = form.data[key] as string[];
                                                    form.setData(
                                                        key,
                                                        e.target.checked
                                                            ? [...cur, opt]
                                                            : cur.filter((v) => v !== opt),
                                                    );
                                                }}
                                            />
                                            {opt}
                                        </label>
                                    ))}
                            </Field>
                        );
                    })}
                </fieldset>
                {!disabled && (
                    <div className="text-center">
                        <Button type="submit" disabled={form.processing}>
                            Submit application <ArrowRight />
                        </Button>
                    </div>
                )}
            </form>
        </Panel>
    );
}

function RecruiterContact() {
    return (
        <Panel>
            <p className="mb-4 text-center text-sm text-muted-foreground">
                Connect with one of our recruiters to complete the process.
            </p>
            <ol className="mx-auto max-w-xs space-y-3 text-sm">
                <li className="flex gap-3">
                    <span className="grid size-6 shrink-0 place-items-center rounded-full border border-primary/40 text-xs text-primary">
                        1
                    </span>
                    Join our Discord server
                </li>
                <li className="flex gap-3">
                    <span className="grid size-6 shrink-0 place-items-center rounded-full border border-primary/40 text-xs text-primary">
                        2
                    </span>
                    Post in <code className="rounded bg-muted px-1">#recruiting</code>
                </li>
            </ol>
            <div className="mt-5 text-center">
                <Button asChild>
                    <a href="https://discord.gg/clanaod" target="_blank" rel="noreferrer">
                        <MessageSquare /> Join Discord
                    </a>
                </Button>
            </div>
        </Panel>
    );
}

function Field({
    label,
    error,
    hint,
    children,
}: {
    label: string;
    error?: string;
    hint?: string;
    children: React.ReactNode;
}) {
    return (
        <div className="space-y-1.5">
            <Label>{label}</Label>
            {children}
            {error ? (
                <p className="text-xs text-destructive">{error}</p>
            ) : hint ? (
                <p className="text-xs text-muted-foreground">{hint}</p>
            ) : null}
        </div>
    );
}
