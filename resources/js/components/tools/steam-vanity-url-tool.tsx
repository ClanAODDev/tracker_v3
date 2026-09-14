import { Copy, Loader2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { postJson } from '@/lib/api';

export function SteamVanityUrlTool({ open, onOpenChange }: { open: boolean; onOpenChange: (open: boolean) => void }) {
    const [input, setInput] = useState('');
    const [result, setResult] = useState<string | null>(null);
    const [loading, setLoading] = useState(false);

    async function resolve() {
        if (!input.trim() || loading) return;
        setLoading(true);
        setResult(null);
        try {
            const res = await postJson<{ steamId: string }>('/tools/steam/resolve-vanity-url', {
                input: input.trim(),
            });
            setResult(res.steamId);
        } catch (e) {
            toast.error(e instanceof Error ? e.message : 'Failed to resolve');
        } finally {
            setLoading(false);
        }
    }

    function copy() {
        if (!result) return;
        navigator.clipboard.writeText(result);
        toast.success('Copied to clipboard');
    }

    return (
        <Dialog
            open={open}
            onOpenChange={(v) => {
                onOpenChange(v);
                if (!v) {
                    setInput('');
                    setResult(null);
                }
            }}
        >
            <DialogContent className="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Steam Vanity URL → SteamID64</DialogTitle>
                    <DialogDescription>
                        Paste a Steam profile URL, vanity name, or ID to get the numeric SteamID64.
                    </DialogDescription>
                </DialogHeader>
                <div className="grid gap-1.5">
                    <Label htmlFor="steam-vanity-input">Vanity URL, name, or profile link</Label>
                    <div className="flex gap-2">
                        <Input
                            id="steam-vanity-input"
                            value={input}
                            onChange={(e) => setInput(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && resolve()}
                            placeholder="e.g. gaben or steamcommunity.com/id/gabelogannewell"
                            autoFocus
                        />
                        <Button onClick={resolve} disabled={loading || !input.trim()}>
                            {loading ? <Loader2 className="size-4 animate-spin" /> : 'Resolve'}
                        </Button>
                    </div>
                </div>
                {result && (
                    <div className="flex items-center gap-2 rounded-md border border-border bg-card p-3">
                        <span className="flex-1 font-mono text-sm">{result}</span>
                        <Button size="icon" variant="ghost" onClick={copy} aria-label="Copy SteamID64">
                            <Copy className="size-4" />
                        </Button>
                    </div>
                )}
            </DialogContent>
        </Dialog>
    );
}
