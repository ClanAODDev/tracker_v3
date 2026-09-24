import { router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { SimpleSelect } from '@/components/ui/simple-select';
import { postJson } from '@/lib/api';

export interface DetailsManagement {
    saveUrl: string;
    canEditHandles: boolean;
    canEditFields: boolean;
    handles: Array<{ id: number; handleId: number; value: string; primary: boolean }>;
    availableHandleTypes: Array<{ value: number; label: string }>;
}

interface HandleRow {
    id: number | null;
    handleId: number | null;
    value: string;
    primary: boolean;
}

export function HandleEditor({ management }: { management: DetailsManagement | null }) {
    const [open, setOpen] = useState(false);
    const [saving, setSaving] = useState(false);
    const [handles, setHandles] = useState<HandleRow[]>([]);

    if (!management || !management.canEditHandles) {
        return null;
    }

    const openDialog = () => {
        setHandles(management.handles.map((h) => ({ id: h.id, handleId: h.handleId, value: h.value, primary: h.primary })));
        setOpen(true);
    };

    const addHandleRow = () =>
        setHandles((prev) => [...prev, { id: null, handleId: null, value: '', primary: prev.length === 0 }]);
    const removeHandleRow = (index: number) => setHandles((prev) => prev.filter((_, i) => i !== index));
    const updateHandleRow = (index: number, patch: Partial<HandleRow>) =>
        setHandles((prev) => prev.map((row, i) => (i === index ? { ...row, ...patch } : row)));

    const save = async () => {
        setSaving(true);
        try {
            const payload = {
                handles: handles
                    .filter((h) => h.handleId && h.value.trim() !== '')
                    .map((h) => ({ id: h.id ?? undefined, handle_id: h.handleId, value: h.value, primary: h.primary })),
            };
            await postJson(management.saveUrl, payload);
            toast.success('Handles updated');
            setOpen(false);
            router.reload({ only: ['handles', 'detailsManagement'] });
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to save handles');
        } finally {
            setSaving(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={(v) => (v ? openDialog() : setOpen(false))}>
            <DialogTrigger asChild>
                <Button size="xs" variant="ghost">
                    <Pencil /> Edit
                </Button>
            </DialogTrigger>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Edit handles</DialogTitle>
                    <DialogDescription className="sr-only">Manage this member's in-game handles</DialogDescription>
                </DialogHeader>

                <div className="space-y-2">
                    {handles.map((row, i) => (
                        <div key={i} className="flex items-center gap-2">
                            <SimpleSelect
                                value={row.handleId ? String(row.handleId) : ''}
                                onChange={(v) => updateHandleRow(i, { handleId: Number(v) })}
                                options={management.availableHandleTypes.map((t) => ({
                                    value: String(t.value),
                                    label: t.label,
                                }))}
                                placeholder="Type..."
                            />
                            <Input
                                value={row.value}
                                onChange={(e) => updateHandleRow(i, { value: e.target.value })}
                                placeholder="Value"
                                className="flex-1"
                            />
                            <label className="flex items-center gap-1 text-xs whitespace-nowrap text-muted-foreground">
                                <Checkbox checked={row.primary} onCheckedChange={(v) => updateHandleRow(i, { primary: !!v })} />
                                Primary
                            </label>
                            <Button size="icon-xs" variant="ghost" onClick={() => removeHandleRow(i)}>
                                <Trash2 />
                            </Button>
                        </div>
                    ))}
                </div>
                <Button size="xs" variant="outline" onClick={addHandleRow}>
                    <Plus /> Add handle
                </Button>

                <DialogFooter>
                    <Button variant="outline" onClick={() => setOpen(false)}>
                        Cancel
                    </Button>
                    <Button onClick={save} disabled={saving}>
                        {saving ? 'Saving...' : 'Save'}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
