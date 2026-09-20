import { Head, useForm } from '@inertiajs/react';
import { Copy, KeyRound, Pencil, Trash2 } from 'lucide-react';
import { useState } from 'react';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout';

interface Token {
    id: number;
    name: string;
    lastUsedAt: string | null;
    scopes: string[];
}

interface AvailableScope {
    value: string;
    label: string;
    description: string;
}

interface DeveloperIndexProps {
    tokens: Token[];
    plainTextToken: string | null;
    availableScopes: AvailableScope[];
}

function ScopeCheckboxes({
    availableScopes,
    selected,
    onToggle,
}: {
    availableScopes: AvailableScope[];
    selected: string[];
    onToggle: (value: string, checked: boolean) => void;
}) {
    return (
        <div className="space-y-2">
            {availableScopes.map((scope) => (
                <label key={scope.value} className="flex items-start gap-2.5 text-sm">
                    <Checkbox
                        checked={selected.includes(scope.value)}
                        onCheckedChange={(checked) => onToggle(scope.value, checked === true)}
                        className="mt-0.5"
                    />
                    <span>
                        <span className="font-medium">{scope.label}</span>
                        <span className="block text-xs text-muted-foreground">{scope.description}</span>
                    </span>
                </label>
            ))}
        </div>
    );
}

function EditScopesDialog({ token, availableScopes }: { token: Token; availableScopes: AvailableScope[] }) {
    const [open, setOpen] = useState(false);
    const update = useForm({ token_id: token.id, scopes: token.scopes });

    return (
        <Dialog
            open={open}
            onOpenChange={(next) => {
                setOpen(next);
                if (next) {
                    update.setData({ token_id: token.id, scopes: token.scopes });
                }
            }}
        >
            <Button size="icon-sm" variant="ghost" onClick={() => setOpen(true)}>
                <Pencil />
            </Button>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit scopes — {token.name}</DialogTitle>
                    <DialogDescription>Choose which abilities this token is allowed to use.</DialogDescription>
                </DialogHeader>
                <ScopeCheckboxes
                    availableScopes={availableScopes}
                    selected={update.data.scopes}
                    onToggle={(value, checked) =>
                        update.setData(
                            'scopes',
                            checked ? [...update.data.scopes, value] : update.data.scopes.filter((s) => s !== value),
                        )
                    }
                />
                <DialogFooter>
                    <Button
                        disabled={update.processing}
                        onClick={() =>
                            update.patch('/developers/tokens', {
                                preserveScroll: true,
                                onSuccess: () => setOpen(false),
                            })
                        }
                    >
                        Save scopes
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

export default function DeveloperIndex({ tokens, plainTextToken, availableScopes }: DeveloperIndexProps) {
    const create = useForm({ token_name: '', scopes: ['division:read'] as string[] });
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
                                        <div className="mt-1.5 flex flex-wrap gap-1.5">
                                            {token.scopes.length === 0 ? (
                                                <span className="text-xs text-muted-foreground">No scopes</span>
                                            ) : (
                                                token.scopes.map((scope) => (
                                                    <Badge key={scope} variant="outline" className="font-mono text-[10px]">
                                                        {scope}
                                                    </Badge>
                                                ))
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-1">
                                        <EditScopesDialog token={token} availableScopes={availableScopes} />
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
                                    </div>
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
                            create.post('/developers/tokens', {
                                onSuccess: () => create.reset(),
                                preserveScroll: true,
                            });
                        }}
                        className="space-y-4"
                    >
                        <div className="flex flex-wrap items-end gap-3">
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
                        </div>
                        {create.errors.token_name && (
                            <p className="text-sm text-destructive">{create.errors.token_name}</p>
                        )}

                        <div className="grid gap-1.5">
                            <Label>Scopes</Label>
                            <ScopeCheckboxes
                                availableScopes={availableScopes}
                                selected={create.data.scopes}
                                onToggle={(value, checked) =>
                                    create.setData(
                                        'scopes',
                                        checked ? [...create.data.scopes, value] : create.data.scopes.filter((s) => s !== value),
                                    )
                                }
                            />
                        </div>
                    </form>
                </section>
            </div>
        </AppLayout>
    );
}
