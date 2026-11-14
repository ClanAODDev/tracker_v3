import { router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { SimpleSelect } from '@/components/ui/simple-select';
import { ApiError, postJson } from '@/lib/api';

export interface DetailsManagement {
    saveUrl: string;
    canEditHandles: boolean;
    handles: Array<{ id: number; handleId: number; value: string; primary: boolean }>;
    availableHandleTypes: Array<{ value: number; label: string; hint: string | null }>;
}

interface HandleRow {
    id: number | null;
    handleId: number | null;
    value: string;
    primary: boolean;
}

export function HandleEditor({
    management,
    open,
    onOpenChange,
}: {
    management: DetailsManagement | null;
    open: boolean;
    onOpenChange: (open: boolean) => void;
}) {
    const [saving, setSaving] = useState(false);
    const [handles, setHandles] = useState<HandleRow[]>([]);
    const [rowErrors, setRowErrors] = useState<Record<number, string>>({});

    useEffect(() => {
        if (open && management) {
            setHandles(management.handles.map((h) => ({ id: h.id, handleId: h.handleId, value: h.value, primary: h.primary })));
            setRowErrors({});
        }
    }, [open, management]);

    if (!management || !management.canEditHandles) {
        return null;
    }

    const addHandleRow = () => {
        setHandles((prev) => [...prev, { id: null, handleId: null, value: '', primary: prev.length === 0 }]);
        setRowErrors({});
    };
    const removeHandleRow = (index: number) => {
        setHandles((prev) => prev.filter((_, i) => i !== index));
        setRowErrors({});
    };
    const updateHandleRow = (index: number, patch: Partial<HandleRow>) => {
        setHandles((prev) => prev.map((row, i) => (i === index ? { ...row, ...patch } : row)));
        setRowErrors(({ [index]: _cleared, ...rest }) => rest);
    };

    const hintFor = (handleId: number | null) =>
        management.availableHandleTypes.find((type) => type.value === handleId)?.hint ?? null;

    const save = async () => {
        setSaving(true);
        setRowErrors({});
        const kept = handles.filter((h) => h.handleId && h.value.trim() !== '');
        try {
            const payload = {
                handles: kept
                    .map((h) => ({ id: h.id ?? undefined, handle_id: h.handleId, value: h.value, primary: h.primary })),
            };
            await postJson(management.saveUrl, payload);
            toast.success('Handles updated');
            onOpenChange(false);
            router.reload({ only: ['handles', 'detailsManagement'] });
        } catch (e) {
            const errors = e instanceof ApiError ? rowErrorsFrom(e, kept, handles) : {};
            setRowErrors(errors);
            toast.error(
                Object.keys(errors).length > 0
                    ? 'One or more handles are not in the expected format.'
                    : e instanceof Error
                      ? e.message
                      : 'Failed to save handles',
            );
        } finally {
            setSaving(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-lg">
                <DialogHeader>
                    <DialogTitle>Edit handles</DialogTitle>
                    <DialogDescription className="sr-only">Manage this member's in-game handles</DialogDescription>
                </DialogHeader>

                <div className="space-y-2">
                    {handles.map((row, i) => (
                        <div key={i} className="flex items-start gap-2">
                            <SimpleSelect
                                value={row.handleId ? String(row.handleId) : ''}
                                onChange={(v) => updateHandleRow(i, { handleId: Number(v) })}
                                options={management.availableHandleTypes.map((t) => ({
                                    value: String(t.value),
                                    label: t.label,
                                }))}
                                placeholder="Type..."
                            />
                            <div className="min-w-0 flex-1">
                                <Input
                                    value={row.value}
                                    onChange={(e) => updateHandleRow(i, { value: e.target.value })}
                                    placeholder="Value"
                                    aria-invalid={rowErrors[i] ? true : undefined}
                                />
                                {rowErrors[i] ? (
                                    <p className="mt-1 text-xs text-destructive">{rowErrors[i]}</p>
                                ) : (
                                    hintFor(row.handleId) && (
                                        <p className="mt-1 text-xs text-muted-foreground">{hintFor(row.handleId)}</p>
                                    )
                                )}
                            </div>
                            <label className="flex h-9 items-center gap-1 text-xs whitespace-nowrap text-muted-foreground">
                                <Checkbox checked={row.primary} onCheckedChange={(v) => updateHandleRow(i, { primary: !!v })} />
                                Primary
                            </label>
                            <Button size="icon-xs" variant="ghost" className="mt-1.5" onClick={() => removeHandleRow(i)}>
                                <Trash2 />
                            </Button>
                        </div>
                    ))}
                </div>
                <Button size="xs" variant="outline" onClick={addHandleRow}>
                    <Plus /> Add handle
                </Button>

                <DialogFooter>
                    <Button variant="outline" onClick={() => onOpenChange(false)}>
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

function rowErrorsFrom(error: ApiError, kept: HandleRow[], rows: HandleRow[]): Record<number, string> {
    const errors = (error.data.errors ?? {}) as Record<string, string[]>;

    return Object.fromEntries(
        Object.entries(errors).flatMap(([key, messages]) => {
            const match = key.match(/^handles\.(\d+)\.value$/);
            const rowIndex = match ? rows.indexOf(kept[Number(match[1])]) : -1;
            return rowIndex !== -1 && messages[0] ? [[rowIndex, messages[0]]] : [];
        }),
    );
}
