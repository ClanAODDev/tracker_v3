import { Head, useForm } from '@inertiajs/react';
import { Clock, LockKeyhole } from 'lucide-react';
import type { FormEvent } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import BlankLayout from '@/layouts/BlankLayout';

interface LoginProps {
    discordEnabled: boolean;
    expired?: boolean;
}

export default function Login({ discordEnabled, expired }: LoginProps) {
    const form = useForm({ username: '', password: '', remember: false });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post('/login', { onFinish: () => form.reset('password') });
    }

    return (
        <BlankLayout>
            <Head title="Sign in" />

            <div className="mx-auto flex min-h-[70vh] w-full max-w-sm flex-col justify-center">
                <div className="mb-8 flex items-center gap-3">
                    <span className="flex size-10 items-center justify-center rounded-md border border-primary/40 bg-primary/10 text-primary tron-glow">
                        <LockKeyhole className="size-5" />
                    </span>
                    <div>
                        <p className="text-xs font-medium uppercase tracking-[0.2em] text-primary">AOD Tracker</p>
                        <h1 className="text-lg font-semibold tracking-tight">Sign in</h1>
                    </div>
                </div>

                {expired && (
                    <div className="mb-4 flex items-center gap-2 rounded-md border border-warning/40 bg-warning/5 px-3 py-2.5 text-sm text-warning">
                        <Clock className="size-4 shrink-0" />
                        Your session expired. Please sign in again.
                    </div>
                )}

                <form onSubmit={submit} className="tron-corners space-y-4 rounded-md border border-border bg-card p-6">
                    <p className="text-sm text-muted-foreground">
                        Enter your <strong className="text-foreground">AOD forum credentials</strong>.
                    </p>

                    <div className="grid gap-1.5">
                        <Label htmlFor="username">Username</Label>
                        <Input
                            id="username"
                            value={form.data.username}
                            onChange={(e) => form.setData('username', e.target.value)}
                            autoFocus
                            autoComplete="username"
                            aria-invalid={!!form.errors.username}
                        />
                    </div>

                    <div className="grid gap-1.5">
                        <Label htmlFor="password">Password</Label>
                        <Input
                            id="password"
                            type="password"
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            autoComplete="current-password"
                            aria-invalid={!!form.errors.password}
                        />
                    </div>

                    {(form.errors.username || form.errors.password) && (
                        <p className="text-sm text-destructive">{form.errors.username ?? form.errors.password}</p>
                    )}

                    <label className="flex items-center gap-2 text-sm text-muted-foreground">
                        <input
                            type="checkbox"
                            checked={form.data.remember}
                            onChange={(e) => form.setData('remember', e.target.checked)}
                            className="size-3.5 accent-primary"
                        />
                        Remember me
                    </label>

                    <div className="flex items-center justify-between gap-2 pt-1">
                        <div className="flex gap-2 text-sm">
                            <a
                                href="https://www.clanaod.net/forums/register.php"
                                className="text-muted-foreground hover:text-foreground"
                            >
                                Register
                            </a>
                            <a
                                href="https://www.clanaod.net/forums/login.php?do=lostpw"
                                className="text-muted-foreground hover:text-foreground"
                            >
                                Forgot
                            </a>
                        </div>
                        <Button type="submit" disabled={form.processing}>
                            {form.processing ? 'Signing in…' : 'Sign in'}
                        </Button>
                    </div>
                </form>

                {discordEnabled && (
                    <div className="mt-6 text-center">
                        <p className="mb-3 text-sm text-muted-foreground">or</p>
                        <Button variant="outline" className="w-full" asChild>
                            <a href="/auth/discord">Continue with Discord</a>
                        </Button>
                    </div>
                )}
            </div>
        </BlankLayout>
    );
}
