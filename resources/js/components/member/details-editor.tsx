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
import { Label } from '@/components/ui/label';
import { SimpleSelect } from '@/components/ui/simple-select';
import { postJson } from '@/lib/api';

export interface DetailsManagement {
    saveUrl: string;
    canEditHandles: boolean;
    canEditFields: boolean;
    handles: Array<{ id: number; handleId: number; value: string; primary: boolean }>;
    availableHandleTypes: Array<{ value: number; label: string }>;
    fields: Array<{ key: string; label: string; type: 'text' | 'select'; options: string[]; value: string | null }>;
}

interface HandleRow {
    id: number | null;
    handleId: number | null;
    value: string;
    primary: boolean;
}

const UNSET = '__unset__';

export function MemberDetailsEditor({ management }: { management: DetailsManagement | null }) {
    const [open, setOpen] = useState(false);
    const [saving, setSaving] = useState(false);
    const [handles, setHandles] = useState<HandleRow[]>([]);
    const [fields, setFields] = useState<Record<string, string>>({});

    if (!management || (!management.canEditHandles && !management.canEditFields)) {
        return null;
    }

    const openDialog = () => {
        setHandles(management.handles.map((h) => ({ id: h.id, handleId: h.handleId, value: h.value, primary: h.primary })));
        setFields(Object.fromEntries(management.fields.map((f) => [f.key, f.value ?? ''])));
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
            const payload: Record<string, unknown> = {};
            if (management.canEditHandles) {
                payload.handles = handles
                    .filter((h) => h.handleId && h.value.trim() !== '')
                    .map((h) => ({ id: h.id ?? undefined, handle_id: h.handleId, value: h.value, primary: h.primary }));
            }
            if (management.canEditFields) {
                payload.fields = fields;
            }
            await postJson(management.saveUrl, payload);
            toast.success('Details updated');
            setOpen(false);
            router.reload({ only: ['handles', 'customFields', 'detailsManagement'] });
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to save details');
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
                    <DialogTitle>Edit details</DialogTitle>
                    <DialogDescription className="sr-only">Manage handles and custom fields for this member</DialogDescription>
                </DialogHeader>

                <div className="space-y-6">
                    {management.canEditHandles && (
                        <div className="space-y-3">
                            <Label>Handles</Label>
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
                                            <Checkbox
                                                checked={row.primary}
                                                onCheckedChange={(v) => updateHandleRow(i, { primary: !!v })}
                                            />
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
                        </div>
                    )}

                    {management.canEditFields && management.fields.length > 0 && (
                        <div className="space-y-3">
                            <Label>Member fields</Label>
                            <div className="space-y-3">
                                {management.fields.map((field) => (
                                    <div key={field.key} className="space-y-1">
                                        <Label className="text-xs text-muted-foreground">{field.label}</Label>
                                        {field.type === 'select' ? (
                                            <SimpleSelect
                                                value={fields[field.key] || UNSET}
                                                onChange={(v) => setFields((prev) => ({ ...prev, [field.key]: v === UNSET ? '' : v }))}
                                                options={[
                                                    { value: UNSET, label: '—' },
                                                    ...field.options.map((o) => ({ value: o, label: o })),
                                                ]}
                                            />
                                        ) : (
                                            <Input
                                                value={fields[field.key] ?? ''}
                                                onChange={(e) => setFields((prev) => ({ ...prev, [field.key]: e.target.value }))}
                                            />
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

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
