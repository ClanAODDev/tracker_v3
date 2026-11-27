import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { type ComponentProps, type FormEvent, useId, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface CreateUnitDialogProps {
    url: string;
    label: string;
    open: boolean;
    onOpenChange: (open: boolean) => void;
    description?: string;
}

export function CreateUnitDialog({ url, label, open, onOpenChange, description }: CreateUnitDialogProps) {
    const form = useForm({ name: '' });
    const inputId = useId();

    function close(next: boolean) {
        if (!next) {
            form.reset();
            form.clearErrors();
        }
        onOpenChange(next);
    }

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post(url, {
            preserveScroll: true,
            onSuccess: () => close(false),
        });
    }

    return (
        <Dialog open={open} onOpenChange={close}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>New {label}</DialogTitle>
                    {description && <DialogDescription>{description}</DialogDescription>}
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-1.5">
                        <Label htmlFor={inputId}>Name</Label>
                        <Input id={inputId} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} maxLength={255} autoFocus />
                        {form.errors.name && <p className="text-xs text-destructive">{form.errors.name}</p>}
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => close(false)}>
                            Cancel
                        </Button>
                        <Button type="submit" disabled={!form.data.name.trim() || form.processing}>
                            <Plus /> Create {label}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

interface CreateUnitButtonProps extends Pick<ComponentProps<typeof Button>, 'variant' | 'size' | 'className'> {
    url: string;
    label: string;
    description?: string;
}

export function CreateUnitButton({ url, label, description, variant = 'outline', size = 'sm', className }: CreateUnitButtonProps) {
    const [open, setOpen] = useState(false);

    return (
        <>
            <Button variant={variant} size={size} className={className} onClick={() => setOpen(true)}>
                <Plus /> {`New ${label}`}
            </Button>
            <CreateUnitDialog url={url} label={label} description={description} open={open} onOpenChange={setOpen} />
        </>
    );
}
