import { Head, useForm } from '@inertiajs/react';
import { Copy, KeyRound, Trash2 } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout';

interface Token {
    id: number;
    name: string;
    lastUsedAt: string | null;
}

interface DeveloperIndexProps {
    tokens: Token[];
    plainTextToken: string | null;
}

export default function DeveloperIndex({ tokens, plainTextToken }: DeveloperIndexProps) {
    const create = useForm({ token_name: '' });
    const destroy = useForm({ token_id: 0 });
    const [copied, setCopied] = useState(false);

    return (
        <AppLayout header={{ title: 'Developers', breadcrumbs: [{ label: 'Developers' }] }}>
            <Head title="Developers" />

            <div className="max-w-3xl space-y-8">
                <div className="space-y-3 text-sm text-muted-foreground">
                    <p>
                        AOD provides several consumable APIs intended solely for internal community use. AOD data,
                        tokens, and other resources are not to be shared outside the AOD community.
                    </p>
                    <p className="text-destructive">
                        <strong>
                            Misuse of these resources will result in revocation of access, and could lead to permanent
                            removal from the clan.
                        </strong>
                    </p>
                </div>

                {plainTextToken && (
                    <div className="tron-corners rounded-md border border-primary/30 bg-primary/5 p-4">
                        <p className="text-sm font-medium">Your new token — copy it now, it won't be shown again.</p>
                        <div className="mt-2 flex items-center gap-2">
                            <code className="flex-1 truncate rounded bg-muted px-2 py-1.5 font-mono text-xs">
                                {plainTextToken}
                            </code>
                            <Button
                                size="sm"
                                variant="outline"
                                onClick={() => {
                                    navigator.clipboard.writeText(plainTextToken);
                                    setCopied(true);
                                }}
                            >
                                <Copy /> {copied ? 'Copied' : 'Copy'}
                            </Button>
                        </div>
                    </div>
                )}

                <section>
                    <h2 className="mb-3 text-sm font-semibold">Personal access tokens</h2>
                    {tokens.length === 0 ? (
                        <p className="text-sm text-muted-foreground">You do not currently have any tokens.</p>
                    ) : (
                        <ul className="divide-y divide-border overflow-hidden rounded-md border border-border">
                            {tokens.map((token) => (
                                <li key={token.id} className="flex items-center justify-between gap-4 p-4">
                                    <div>
                                        <p className="text-sm font-medium">{token.name}</p>
                                        <p className="numeric text-xs text-muted-foreground">
                                            Last used: {token.lastUsedAt ?? 'never'}
                                        </p>
                                    </div>
                                    <Button
                                        size="icon-sm"
                                        variant="ghost"
                                        className="text-destructive"
                                        disabled={destroy.processing}
                                        onClick={() => {
                                            destroy.transform(() => ({ token_id: token.id }));
                                            destroy.delete('/developers/tokens', { preserveScroll: true });
                                        }}
                                    >
                                        <Trash2 />
                                    </Button>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section>
                    <h2 className="mb-3 text-sm font-semibold">Generate token</h2>
                    <form
                        onSubmit={(e) => {
                            e.preventDefault();
                            create.post('/developers/tokens', { onSuccess: () => create.reset() });
                        }}
                        className="flex flex-wrap items-end gap-3"
                    >
                        <div className="grid gap-1.5">
                            <Label htmlFor="token_name">Token name</Label>
                            <Input
                                id="token_name"
                                value={create.data.token_name}
                                onChange={(e) => create.setData('token_name', e.target.value)}
                                placeholder="My API token"
                                className="w-64"
                                aria-invalid={!!create.errors.token_name}
                            />
                        </div>
                        <Button type="submit" disabled={create.processing}>
                            <KeyRound /> Create token
                        </Button>
                    </form>
                    {create.errors.token_name && (
                        <p className="mt-2 text-sm text-destructive">{create.errors.token_name}</p>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
