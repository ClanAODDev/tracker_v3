import { router } from '@inertiajs/react';
import { ChevronRight, Gamepad2, Loader2, Plus, Puzzle, RefreshCw, Trash2 } from 'lucide-react';
import { useEffect, useState, type ReactNode } from 'react';
import { toast } from 'sonner';

import { TronSpinner } from '@/components/tron-spinner';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle } from '@/components/ui/sheet';
import { SimpleSelect } from '@/components/ui/simple-select';
import { Switch } from '@/components/ui/switch';
import { getJson, postJson } from '@/lib/api';
import { cn } from '@/lib/utils';

interface DivisionRef {
    id: number;
    name: string;
}

interface SettingsData {
    settings: { disable_animations: boolean; mobile_nav_side: 'left' | 'right'; theme: 'light' | 'dark' };
    member: {
        name: string;
        avatarUrl: string | null;
        canSyncAvatar: boolean;
        division: { name: string; logo: string } | null;
        canRequestTransfer: boolean;
    } | null;
    pendingTransfer?: { division: string } | null;
    transferableDivisions?: DivisionRef[];
    partTimeDivisions?: { available: DivisionRef[]; selected: number[] };
    handles?: {
        types: Array<{ id: number; label: string }>;
        current: Array<{ id: number | null; handleId: number | null; value: string; primary: boolean }>;
    };
}

type HandleRow = { id: number | null; handleId: number | null; value: string; primary: boolean };

function Section({ title, children }: { title: string; children: ReactNode }) {
    return (
        <section className="space-y-3">
            <h3 className="text-xs font-semibold uppercase tracking-[0.15em] text-primary">{title}</h3>
            {children}
        </section>
    );
}

function SettingRow({
    icon,
    label,
    count,
    onClick,
}: {
    icon: ReactNode;
    label: string;
    count: number;
    onClick: () => void;
}) {
    return (
        <button
            type="button"
            onClick={onClick}
            className="flex w-full items-center gap-3 rounded-md border border-border px-3 py-2.5 text-sm transition-colors hover:border-primary/30"
        >
            {icon}
            <span className="flex-1 text-left">{label}</span>
            <span className="numeric rounded bg-muted px-1.5 text-xs text-muted-foreground">{count}</span>
            <ChevronRight className="size-4 text-muted-foreground" />
        </button>
    );
}

export function SettingsSheet({ open, onOpenChange }: { open: boolean; onOpenChange: (v: boolean) => void }) {
    const [data, setData] = useState<SettingsData | null>(null);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        if (!open) return;
        setLoading(true);
        getJson<SettingsData>('/settings')
            .then(setData)
            .catch(() => toast.error('Could not load settings'))
            .finally(() => setLoading(false));
    }, [open]);

    return (
        <Sheet open={open} onOpenChange={onOpenChange}>
            <SheetContent side="right" className="w-full gap-0 overflow-y-auto sm:max-w-md">
                <SheetHeader>
                    <SheetTitle>Settings</SheetTitle>
                    <SheetDescription>Manage your profile, assignments, and preferences.</SheetDescription>
                </SheetHeader>

                {loading || !data ? (
                    <div className="flex items-center justify-center gap-2 py-16 text-sm text-muted-foreground">
                        <Loader2 className="size-4 animate-spin" /> Loading…
                    </div>
                ) : (
                    <div className="space-y-8 px-4 pb-8">
                        {data.member && <ProfileSection member={data.member} />}
                        {data.member?.canRequestTransfer && (
                            <TransferSection
                                pending={data.pendingTransfer ?? null}
                                divisions={data.transferableDivisions ?? []}
                                currentDivision={data.member.division}
                            />
                        )}
                        {data.member && (data.partTimeDivisions || data.handles) && (
                            <Section title="Assignments">
                                {data.partTimeDivisions && (
                                    <PartTimeDialog
                                        available={data.partTimeDivisions.available}
                                        selected={data.partTimeDivisions.selected}
                                    />
                                )}
                                {data.handles && (
                                    <HandlesDialog types={data.handles.types} current={data.handles.current} />
                                )}
                            </Section>
                        )}
                        <AppearanceSection settings={data.settings} />
                    </div>
                )}
            </SheetContent>
        </Sheet>
    );
}

function ProfileSection({ member }: { member: NonNullable<SettingsData['member']> }) {
    const [avatarUrl, setAvatarUrl] = useState(member.avatarUrl);
    const [syncing, setSyncing] = useState(false);
    const [cooldown, setCooldown] = useState(false);

    async function sync() {
        if (syncing || cooldown) return;
        setSyncing(true);
        try {
            const res = await postJson<{ avatarUrl: string }>('/settings/sync-avatar', {});
            setAvatarUrl(res.avatarUrl);
            router.reload({ only: ['auth'] });
            toast.success('Avatar synced from Discord');
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to sync avatar');
        } finally {
            setSyncing(false);
            setCooldown(true);
            setTimeout(() => setCooldown(false), 15000);
        }
    }

    return (
        <Section title="Profile">
            <div className="flex items-center gap-3">
                {avatarUrl ? (
                    <img src={avatarUrl} alt="" className="size-12 rounded-md" />
                ) : (
                    <div className="flex size-12 items-center justify-center rounded-md bg-muted text-sm">
                        {member.name.slice(0, 2).toUpperCase()}
                    </div>
                )}
                <div className="flex-1">
                    <p className="text-sm font-medium">{member.name}</p>
                    {member.canSyncAvatar ? (
                        <Button
                            variant="link"
                            size="sm"
                            className="h-auto p-0 text-xs"
                            disabled={syncing || cooldown}
                            onClick={sync}
                        >
                            <RefreshCw className={cn('size-3', syncing && 'animate-spin')} /> Sync avatar from Discord
                        </Button>
                    ) : (
                        <p className="text-xs text-muted-foreground">Sign in with Discord to load an avatar</p>
                    )}
                </div>
            </div>
        </Section>
    );
}

function TransferSection({
    pending,
    divisions,
    currentDivision,
}: {
    pending: { division: string } | null;
    divisions: DivisionRef[];
    currentDivision: { name: string; logo: string } | null;
}) {
    const [dialogOpen, setDialogOpen] = useState(false);
    const [choice, setChoice] = useState('');
    const [submitting, setSubmitting] = useState(false);

    async function submit() {
        if (!choice) return;
        setSubmitting(true);
        try {
            await postJson('/settings/transfer-request', { division_id: Number(choice) });
            toast.success('Transfer request submitted');
            setDialogOpen(false);
            router.reload();
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Transfer request failed');
        } finally {
            setSubmitting(false);
        }
    }

    return (
        <Section title="Primary division">
            <div className="rounded-md border border-border bg-card p-3">
                {currentDivision && (
                    <div className="flex items-center gap-2">
                        <img src={currentDivision.logo} alt="" className="size-5" />
                        <span className="text-sm font-medium">{currentDivision.name}</span>
                    </div>
                )}
                {pending ? (
                    <p className="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
                        <TronSpinner className="size-3.5 shrink-0" />
                        <span>
                            Transfer to <span className="text-foreground">{pending.division}</span> pending leadership
                            approval.
                        </span>
                    </p>
                ) : (
                    <Button variant="outline" size="sm" className="mt-3" onClick={() => setDialogOpen(true)}>
                        Request division transfer
                    </Button>
                )}
            </div>

            <Dialog open={dialogOpen} onOpenChange={setDialogOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Request division transfer</DialogTitle>
                    </DialogHeader>
                    <div className="space-y-2">
                        <Label>Transfer to</Label>
                        <SimpleSelect
                            value={choice || '__none'}
                            onChange={(v) => setChoice(v === '__none' ? '' : v)}
                            options={[
                                { value: '__none', label: 'Select a division…' },
                                ...divisions.map((d) => ({ value: String(d.id), label: d.name })),
                            ]}
                            className="w-full"
                        />
                    </div>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDialogOpen(false)}>
                            Cancel
                        </Button>
                        <Button disabled={!choice || submitting} onClick={submit}>
                            Submit request
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </Section>
    );
}

function PartTimeDialog({ available, selected }: { available: DivisionRef[]; selected: number[] }) {
    const [open, setOpen] = useState(false);
    const [chosen, setChosen] = useState<Set<number>>(new Set(selected));
    const [count, setCount] = useState(selected.length);
    const [saving, setSaving] = useState(false);
    const dirty = chosen.size !== count || [...chosen].some((id) => !selected.includes(id));

    function toggle(id: number) {
        setChosen((prev) => {
            const next = new Set(prev);
            next.has(id) ? next.delete(id) : next.add(id);
            return next;
        });
    }

    async function save() {
        setSaving(true);
        try {
            await postJson('/settings/part-time-divisions', { divisions: [...chosen] });
            setCount(chosen.size);
            toast.success('Part-time divisions updated');
            setOpen(false);
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to save');
        } finally {
            setSaving(false);
        }
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <SettingRow
                icon={<Puzzle className="size-4 text-muted-foreground" />}
                label="Part-time divisions"
                count={count}
                onClick={() => setOpen(true)}
            />
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Part-time divisions</DialogTitle>
                </DialogHeader>
                <p className="text-xs text-muted-foreground">
                    Divisions you participate in alongside your primary division.
                </p>
                <div className="grid max-h-72 grid-cols-2 gap-2 overflow-y-auto">
                    {available.map((division) => (
                        <label
                            key={division.id}
                            className="flex items-center gap-2 rounded border border-border px-2.5 py-1.5 text-sm"
                        >
                            <Checkbox checked={chosen.has(division.id)} onCheckedChange={() => toggle(division.id)} />
                            {division.name}
                        </label>
                    ))}
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setOpen(false)}>
                        Cancel
                    </Button>
                    <Button disabled={!dirty || saving} onClick={save}>
                        Save
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function HandlesDialog({
    types,
    current,
}: {
    types: Array<{ id: number; label: string }>;
    current: HandleRow[];
}) {
    const [open, setOpen] = useState(false);
    const [rows, setRows] = useState<HandleRow[]>(current);
    const [count, setCount] = useState(current.length);
    const [saving, setSaving] = useState(false);

    function update(index: number, patch: Partial<HandleRow>) {
        setRows((prev) => prev.map((row, i) => (i === index ? { ...row, ...patch } : row)));
    }

    async function save() {
        setSaving(true);
        try {
            const kept = rows.filter((r) => r.handleId && r.value.trim());
            await postJson('/settings/ingame-handles', {
                handles: kept.map((r) => ({
                    id: r.id ?? '',
                    handle_id: r.handleId,
                    value: r.value.trim(),
                    primary: r.primary,
                })),
            });
            setCount(kept.length);
            toast.success('In-game handles updated');
            setOpen(false);
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to save');
        } finally {
            setSaving(false);
        }
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <SettingRow
                icon={<Gamepad2 className="size-4 text-muted-foreground" />}
                label="In-game handles"
                count={count}
                onClick={() => setOpen(true)}
            />
            <DialogContent className="sm:max-w-lg">
                <DialogHeader>
                    <DialogTitle>In-game handles</DialogTitle>
                </DialogHeader>
                <p className="text-xs text-muted-foreground">
                    Your usernames for different games and platforms, so other members can find you.
                </p>
                <div className="max-h-80 space-y-2 overflow-y-auto">
                {rows.length === 0 && <p className="text-sm text-muted-foreground">No handles added.</p>}
                {rows.map((row, i) => (
                    <div key={i} className="flex flex-wrap items-center gap-2">
                        <SimpleSelect
                            value={row.handleId ? String(row.handleId) : '__none'}
                            onChange={(v) => update(i, { handleId: v === '__none' ? null : Number(v) })}
                            options={[
                                { value: '__none', label: 'Platform…' },
                                ...types.map((t) => ({ value: String(t.id), label: t.label })),
                            ]}
                            className="w-32"
                        />
                        <Input
                            value={row.value}
                            onChange={(e) => update(i, { value: e.target.value })}
                            placeholder="Username"
                            className="h-9 flex-1"
                        />
                        <label className="flex items-center gap-1 text-xs text-muted-foreground">
                            <Checkbox checked={row.primary} onCheckedChange={(v) => update(i, { primary: v === true })} />
                            Primary
                        </label>
                        <Button
                            size="icon"
                            variant="ghost"
                            className="size-8 text-destructive"
                            onClick={() => setRows((prev) => prev.filter((_, idx) => idx !== i))}
                        >
                            <Trash2 className="size-4" />
                        </Button>
                    </div>
                ))}
            </div>
                <div className="mt-2">
                    <Button
                        size="sm"
                        variant="outline"
                        onClick={() =>
                            setRows((prev) => [...prev, { id: null, handleId: null, value: '', primary: false }])
                        }
                    >
                        <Plus /> Add handle
                    </Button>
                </div>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setOpen(false)}>
                        Cancel
                    </Button>
                    <Button disabled={saving} onClick={save}>
                        Save handles
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}

function AppearanceSection({ settings }: { settings: SettingsData['settings'] }) {
    const [reduce, setReduce] = useState(settings.disable_animations);
    const [navSide, setNavSide] = useState<'left' | 'right'>(settings.mobile_nav_side);
    const [theme, setTheme] = useState<'light' | 'dark'>(settings.theme);

    async function persist(patch: Record<string, unknown>) {
        try {
            await postJson('/settings', patch);
            router.reload({ only: ['auth'] });
        } catch {
            toast.error('Failed to save preference');
        }
    }

    return (
        <Section title="Appearance">
            <div className="space-y-1.5 text-sm">
                <span className="block">Theme</span>
                <div className="flex gap-1">
                    {(['dark', 'light'] as const).map((option) => (
                        <Button
                            key={option}
                            size="sm"
                            variant={theme === option ? 'default' : 'outline'}
                            className="flex-1 capitalize"
                            onClick={() => {
                                setTheme(option);
                                document.documentElement.dataset.theme = option === 'light' ? 'light' : 'tron';
                                persist({ theme: option });
                            }}
                        >
                            {option}
                        </Button>
                    ))}
                </div>
            </div>

            <label className="flex items-center justify-between gap-3 text-sm">
                <span>
                    Reduce animations
                    <span className="mt-0.5 block text-xs text-muted-foreground">
                        Minimise motion across the interface and charts
                    </span>
                </span>
                <Switch
                    checked={reduce}
                    onCheckedChange={(v) => {
                        setReduce(v);
                        document.documentElement.dataset.reduceMotion = v ? 'true' : 'false';
                        persist({ disable_animations: v });
                    }}
                />
            </label>

            <div className="space-y-1.5 text-sm">
                <span className="block">
                    Mobile navigation side
                    <span className="mt-0.5 block text-xs text-muted-foreground">
                        Which side the menu opens from on phones — pick the side easiest to reach one-handed
                    </span>
                </span>
                <div className="flex gap-1">
                    {(['left', 'right'] as const).map((side) => (
                        <Button
                            key={side}
                            size="sm"
                            variant={navSide === side ? 'default' : 'outline'}
                            className="flex-1 capitalize"
                            onClick={() => {
                                setNavSide(side);
                                persist({ mobile_nav_side: side });
                            }}
                        >
                            {side}
                        </Button>
                    ))}
                </div>
            </div>
        </Section>
    );
}
