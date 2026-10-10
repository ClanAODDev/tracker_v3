import { useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { type FormEvent, useState } from 'react';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

export function CreateChildUnit({ url, label, className }: { url: string; label: string; className?: string }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ name: '' });

    function submit(e: FormEvent) {
        e.preventDefault();
        form.post(url, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setOpen(false);
            },
        });
    }

    return (
        <>
            <Button variant="outline" size="sm" className={className} onClick={() => setOpen(true)}>
                <Plus /> {`New ${label}`}
            </Button>
            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>New {label}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="grid gap-1.5">
                            <Label htmlFor="child-unit-name">Name</Label>
                            <Input
                                id="child-unit-name"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                                maxLength={255}
                                autoFocus
                            />
                            {form.errors.name && <p className="text-xs text-destructive">{form.errors.name}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="button" variant="outline" onClick={() => setOpen(false)}>
                                Cancel
                            </Button>
                            <Button type="submit" disabled={!form.data.name.trim() || form.processing}>
                                <Plus /> Create {label}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
