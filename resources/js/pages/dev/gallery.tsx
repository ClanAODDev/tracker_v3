import { Head } from '@inertiajs/react';
import { Activity, ArrowRight, Bell, Check, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';

import { CountUp } from '@/components/count-up';
import { TrackerLogo, TrackerMark } from '@/components/tracker-logo';
import { TronIdPlate, TronScanline, TronTrace } from '@/components/tron/flourishes';
import { TronSpinner } from '@/components/tron-spinner';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import { Skeleton } from '@/components/ui/skeleton';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { Sparkline, ThemedAreaChart, ThemedBarChart, ThemedLineChart } from '@/components/charts';
import AppLayout from '@/layouts/AppLayout';

const CENSUS = [
    { month: 'Mar', members: 1180, recruits: 42 },
    { month: 'Apr', members: 1210, recruits: 51 },
    { month: 'May', members: 1198, recruits: 33 },
    { month: 'Jun', members: 1244, recruits: 60 },
    { month: 'Jul', members: 1270, recruits: 47 },
    { month: 'Aug', members: 1284, recruits: 55 },
];

const RETENTION = [
    { month: 'Mar', recruits: 42, removals: 28 },
    { month: 'Apr', recruits: 51, removals: 21 },
    { month: 'May', recruits: 33, removals: 45 },
    { month: 'Jun', recruits: 60, removals: 14 },
    { month: 'Jul', recruits: 47, removals: 21 },
    { month: 'Aug', recruits: 55, removals: 41 },
];

const PROMOTIONS = [
    { rank: 'Pvt', q1: 24, q2: 31 },
    { rank: 'PFC', q1: 18, q2: 22 },
    { rank: 'Spec', q1: 12, q2: 9 },
    { rank: 'Cpl', q1: 7, q2: 11 },
    { rank: 'Sgt', q1: 3, q2: 4 },
];

const TOKENS = [
    'background',
    'card',
    'popover',
    'muted',
    'secondary',
    'accent',
    'primary',
    'destructive',
    'success',
    'warning',
    'info',
];

const RARITIES = ['mythic', 'legendary', 'epic', 'rare', 'common', 'unclaimed'];

function Section({ title, id, children }: { title: string; id?: string; children: React.ReactNode }) {
    return (
        <section id={id} className="space-y-4">
            <h2 className="tron-eyebrow">{title}</h2>
            {children}
        </section>
    );
}

export default function Gallery() {
    const [scanKey, setScanKey] = useState(0);
    const [countKey, setCountKey] = useState(0);

    return (
        <AppLayout header={{ title: 'Component Gallery', breadcrumbs: [{ label: 'Dev' }, { label: 'Gallery' }] }}>
            <Head title="Component Gallery" />

            <div className="space-y-12">
                <Section title="Tron flourishes">
                    <div className="grid gap-4 sm:grid-cols-3">
                        <div className="tron-corners rounded-md border border-border bg-card p-5">
                            <p className="text-sm font-medium">Corner brackets</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                <code className="font-mono text-xs">.tron-corners</code> — opt-in L-brackets for feature
                                panels.
                            </p>
                        </div>
                        <div className="tron-glow rounded-md border border-border bg-card p-5">
                            <p className="text-sm font-medium">Underglow</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                <code className="font-mono text-xs">.tron-glow</code> — crimson halo on focus / active
                                surfaces.
                            </p>
                        </div>
                        <div className="tron-hairline rounded-md border border-border bg-card p-5 pb-6">
                            <p className="text-sm font-medium">Hairline</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                <code className="font-mono text-xs">.tron-hairline</code> — fading accent rule under a
                                block.
                            </p>
                        </div>
                        <div className="tron-hatch rounded-md border border-border bg-card p-5">
                            <p className="text-sm font-medium">Diagonal hatch</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                <code className="font-mono text-xs">.tron-hatch</code> — fine 45° texture for empty
                                states and inert surfaces; <code className="font-mono text-xs">.tron-hatch-primary</code>{' '}
                                tints it crimson.
                            </p>
                        </div>
                        <div className="tron-hatch tron-hatch-primary rounded-md border border-primary/30 bg-card p-5">
                            <p className="text-sm font-medium">Hatch · primary</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                Same texture, crimson tint — for accented empty states or callouts.
                            </p>
                        </div>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        The page sits on a faint <code className="font-mono text-xs">.tron-grid</code> and every section
                        label is a <code className="font-mono text-xs">.tron-eyebrow</code> (tick + wide tracking).
                    </p>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="tron-corners relative rounded-md border border-border bg-card p-5">
                            <TronScanline key={scanKey} />
                            <TronIdPlate label="DIV · BF" live className="absolute -top-2 left-3" />
                            <p className="text-sm font-medium">Corner ID plate + scanline</p>
                            <p className="mt-1 text-sm text-muted-foreground">
                                <code className="font-mono text-xs">&lt;TronIdPlate&gt;</code> HUD tag with a live pip, and{' '}
                                <code className="font-mono text-xs">&lt;TronScanline&gt;</code> — a one-shot sweep on
                                mount.
                            </p>
                            <Button size="xs" variant="outline" className="mt-3" onClick={() => setScanKey((k) => k + 1)}>
                                Replay sweep
                            </Button>
                        </div>
                        <div className="rounded-md border border-border bg-card p-5">
                            <p className="text-sm font-medium">Circuit-trace rule</p>
                            <p className="mt-1 mb-4 text-sm text-muted-foreground">
                                <code className="font-mono text-xs">&lt;TronTrace&gt;</code> — bright lead + node + a slow
                                travelling pulse. Sits under every page header.
                            </p>
                            <TronTrace />
                        </div>
                    </div>
                </Section>

                <Section title="Count-up">
                    <div className="flex flex-wrap items-end gap-8">
                        <div>
                            <p className="numeric text-3xl font-semibold">
                                <CountUp key={`a${countKey}`} value={1284} />
                            </p>
                            <p className="text-xs text-muted-foreground">members</p>
                        </div>
                        <div>
                            <p className="numeric text-3xl font-semibold text-primary">
                                <CountUp key={`b${countKey}`} value={973} format={(n) => `${(n / 10).toFixed(1)}%`} />
                            </p>
                            <p className="text-xs text-muted-foreground">retention</p>
                        </div>
                        <Button size="sm" variant="outline" onClick={() => setCountKey((k) => k + 1)}>
                            Replay
                        </Button>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        <code className="font-mono text-xs">&lt;CountUp&gt;</code> / <code className="font-mono text-xs">useCountUp</code>{' '}
                        — eases figures up from zero on mount; honours the reduce-animations preference. Wired into stat
                        tiles, the dashboard, and division stats.
                    </p>
                </Section>

                <Section title="Surface tokens">
                    <div className="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-6">
                        {TOKENS.map((token) => (
                            <div key={token} className="overflow-hidden rounded-md border border-border">
                                <div className="h-16" style={{ background: `var(--${token})` }} />
                                <div className="bg-card px-2 py-1.5 font-mono text-[11px] text-muted-foreground">
                                    --{token}
                                </div>
                            </div>
                        ))}
                    </div>
                </Section>

                <Section title="Rarity">
                    <div className="flex flex-wrap gap-2">
                        {RARITIES.map((r) => (
                            <span
                                key={r}
                                className="rounded-full border px-3 py-1 text-xs font-medium capitalize"
                                style={{ color: `var(--rarity-${r})`, borderColor: `var(--rarity-${r})` }}
                            >
                                {r}
                            </span>
                        ))}
                    </div>
                </Section>

                <Section title="Brand">
                    <div className="flex flex-wrap items-center gap-8">
                        <TrackerMark className="size-16" />
                        <TrackerLogo />
                        <div className="rounded-md bg-foreground p-4">
                            <TrackerMark className="size-10" />
                        </div>
                    </div>
                </Section>

                <Section title="Typography">
                    <div className="space-y-2">
                        <p className="text-3xl font-semibold tracking-tight">Angels of Death Tracker</p>
                        <p className="text-base text-muted-foreground">
                            Body copy in Inter. The quick brown fox jumps over the lazy dog.
                        </p>
                        <p className="numeric text-2xl">1,284 members · 97.3% retention · +42 MTD</p>
                    </div>
                </Section>

                <Section title="Buttons">
                    <div className="flex flex-wrap items-center gap-3">
                        <Button>Primary</Button>
                        <Button variant="secondary">Secondary</Button>
                        <Button variant="outline">Outline</Button>
                        <Button variant="ghost">Ghost</Button>
                        <Button variant="destructive">
                            <Trash2 /> Delete
                        </Button>
                        <Button variant="link">Link</Button>
                        <Button size="sm">Small</Button>
                        <Button size="lg">
                            Large <ArrowRight />
                        </Button>
                        <Button className="tron-glow">Glow</Button>
                    </div>
                </Section>

                <Section title="Hover & interaction states">
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        <button className="rounded-md border border-border bg-card p-4 text-left text-sm transition-colors hover:border-primary/40 hover:bg-accent">
                            <span className="font-medium">Card / row</span>
                            <p className="mt-1 text-xs text-muted-foreground">
                                border → <code className="font-mono">primary/40</code>, bg → <code className="font-mono">accent</code>
                            </p>
                        </button>
                        <div className="rounded-md border border-border bg-card p-4 text-sm">
                            <a href="#" className="text-primary underline-offset-2 hover:underline">
                                Inline link
                            </a>
                            <p className="mt-1 text-xs text-muted-foreground">underline appears on hover</p>
                        </div>
                        <div className="rounded-md border border-border bg-card p-2 text-sm">
                            <div className="rounded-md px-3 py-2 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground">
                                Nav / menu item
                            </div>
                            <p className="mt-1 px-3 text-xs text-muted-foreground">bg → accent, text → foreground</p>
                        </div>
                        <div className="overflow-hidden rounded-md border border-border">
                            <table className="w-full text-sm">
                                <tbody>
                                    {['Reaper', 'Nomad', 'Vex'].map((n) => (
                                        <tr key={n} className="border-b border-border transition-colors last:border-0 hover:bg-muted/50">
                                            <td className="px-3 py-2">{n}</td>
                                            <td className="numeric px-3 py-2 text-right text-muted-foreground">
                                                {n.length}d ago
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <div className="flex items-center gap-2 rounded-md border border-border bg-card p-4">
                            <Button variant="ghost" size="sm">
                                Ghost hover
                            </Button>
                            <Button variant="outline" size="sm">
                                Outline hover
                            </Button>
                        </div>
                        <div className="rounded-md border border-border bg-card p-4 text-sm">
                            <input
                                placeholder="focus me"
                                className="w-full rounded-md border border-input bg-transparent px-3 py-1.5 text-sm outline-none transition-[box-shadow,border-color] focus:border-ring focus:ring-2 focus:ring-ring/40"
                            />
                            <p className="mt-1 text-xs text-muted-foreground">crimson ring on focus</p>
                        </div>
                    </div>
                </Section>

                <Section title="Charts" id="charts">
                    <div className="grid gap-6 lg:grid-cols-2">
                        <div className="rounded-md border border-border bg-card p-4">
                            <p className="mb-3 text-sm font-medium">Census — single series area</p>
                            <ThemedAreaChart
                                data={CENSUS}
                                x="month"
                                series={[{ key: 'members', label: 'Members' }]}
                            />
                        </div>
                        <div className="rounded-md border border-border bg-card p-4">
                            <p className="mb-3 text-sm font-medium">Retention — two-series line</p>
                            <ThemedLineChart
                                data={RETENTION}
                                x="month"
                                series={[
                                    { key: 'recruits', label: 'Recruits' },
                                    { key: 'removals', label: 'Removals' },
                                ]}
                            />
                        </div>
                        <div className="rounded-md border border-border bg-card p-4">
                            <p className="mb-3 text-sm font-medium">Promotions — grouped bars</p>
                            <ThemedBarChart
                                data={PROMOTIONS}
                                x="rank"
                                series={[
                                    { key: 'q1', label: 'Q1' },
                                    { key: 'q2', label: 'Q2' },
                                ]}
                            />
                        </div>
                        <div className="rounded-md border border-border bg-card p-4">
                            <p className="mb-3 text-sm font-medium">Retention — stacked bars</p>
                            <ThemedBarChart
                                stacked
                                data={RETENTION}
                                x="month"
                                series={[
                                    { key: 'recruits', label: 'Recruits' },
                                    { key: 'removals', label: 'Removals' },
                                ]}
                            />
                        </div>
                    </div>
                    <p className="text-sm text-muted-foreground">
                        Series colours come from the validated <code className="font-mono text-xs">--chart-1…5</code>{' '}
                        ramp (dataviz skill: passes lightness, chroma, CVD and normal-vision separation, and ≥3:1
                        contrast on the dark surface). Every chart ships a hover tooltip; ≥2 series always get a legend.
                    </p>

                    <div className="rounded-md border border-border bg-card p-4">
                        <p className="mb-3 text-sm font-medium">Sparklines — bare inline trend</p>
                        <div className="flex flex-wrap items-center gap-6 text-sm">
                            <span className="flex items-center gap-2">
                                <Sparkline data={[12, 15, 11, 18, 22, 20, 26]} tone="auto" />
                                <span className="numeric text-success">+18%</span>
                            </span>
                            <span className="flex items-center gap-2">
                                <Sparkline data={[40, 38, 42, 30, 28, 22, 19]} tone="auto" />
                                <span className="numeric text-destructive">−9%</span>
                            </span>
                            <span className="flex items-center gap-2">
                                <Sparkline data={[8, 9, 8, 10, 9, 11, 10]} tone="neutral" />
                                <span className="numeric text-muted-foreground">flat</span>
                            </span>
                            <span className="flex items-center gap-2">
                                <Sparkline data={[3, 6, 4, 8, 7, 12, 14]} tone={2} />
                                <span className="text-muted-foreground">fixed slot</span>
                            </span>
                        </div>
                    </div>
                </Section>

                <Section title="Badges">
                    <div className="flex flex-wrap gap-2">
                        <Badge>Default</Badge>
                        <Badge variant="secondary">Secondary</Badge>
                        <Badge variant="outline">Outline</Badge>
                        <Badge variant="destructive">Destructive</Badge>
                        <Badge className="bg-success/15 text-success">Active</Badge>
                        <Badge className="bg-warning/15 text-warning">Pending</Badge>
                    </div>
                </Section>

                <Section title="Form">
                    <div className="grid max-w-sm gap-3">
                        <div className="grid gap-1.5">
                            <Label htmlFor="handle">In-game handle</Label>
                            <Input id="handle" placeholder="e.g. Reaper" />
                        </div>
                        <div className="grid gap-1.5">
                            <Label htmlFor="err">With error</Label>
                            <Input id="err" aria-invalid defaultValue="bad value" />
                        </div>
                    </div>
                </Section>

                <Section title="Card + stats">
                    <div className="grid gap-4 sm:grid-cols-3">
                        <Card>
                            <CardHeader>
                                <CardDescription>Active members</CardDescription>
                                <CardTitle className="numeric text-3xl">1,284</CardTitle>
                            </CardHeader>
                            <CardContent className="flex items-center gap-1 text-sm text-success">
                                <Activity className="size-4" /> +42 this month
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader>
                                <CardDescription>Pending actions</CardDescription>
                                <CardTitle className="numeric text-3xl">7</CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm text-muted-foreground">3 promotions · 4 transfers</CardContent>
                        </Card>
                        <Card className="border-primary/30">
                            <CardHeader>
                                <CardDescription>Retention</CardDescription>
                                <CardTitle className="numeric text-3xl text-primary">97.3%</CardTitle>
                            </CardHeader>
                            <CardContent className="text-sm text-muted-foreground">Trailing 90 days</CardContent>
                        </Card>
                    </div>
                </Section>

                <Section title="Tabs">
                    <Tabs defaultValue="overview" className="max-w-md">
                        <TabsList>
                            <TabsTrigger value="overview">Overview</TabsTrigger>
                            <TabsTrigger value="notes">Notes</TabsTrigger>
                            <TabsTrigger value="awards">Awards</TabsTrigger>
                        </TabsList>
                        <TabsContent value="overview" className="pt-3 text-sm text-muted-foreground">
                            Division overview content.
                        </TabsContent>
                        <TabsContent value="notes" className="pt-3 text-sm text-muted-foreground">
                            Member notes content.
                        </TabsContent>
                        <TabsContent value="awards" className="pt-3 text-sm text-muted-foreground">
                            Awards content.
                        </TabsContent>
                    </Tabs>
                </Section>

                <Section title="Table">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Member</TableHead>
                                <TableHead>Rank</TableHead>
                                <TableHead>Division</TableHead>
                                <TableHead className="text-right">Last active</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {[
                                ['Reaper', 'Master Sergeant', 'Squad 44', '2h ago'],
                                ['Nomad', 'Sergeant', 'Squad 44', '1d ago'],
                                ['Vex', 'Corporal', 'Squad 12', '3d ago'],
                            ].map((row) => (
                                <TableRow key={row[0]}>
                                    <TableCell className="font-medium">{row[0]}</TableCell>
                                    <TableCell className="text-muted-foreground">{row[1]}</TableCell>
                                    <TableCell className="text-muted-foreground">{row[2]}</TableCell>
                                    <TableCell className="numeric text-right text-muted-foreground">{row[3]}</TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Section>

                <Section title="Overlays">
                    <div className="flex flex-wrap items-center gap-3">
                        <Dialog>
                            <DialogTrigger asChild>
                                <Button variant="outline">Open dialog</Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>Remove member</DialogTitle>
                                    <DialogDescription>
                                        This flags the member for removal from AOD. This cannot be undone.
                                    </DialogDescription>
                                </DialogHeader>
                                <DialogFooter>
                                    <Button variant="ghost">Cancel</Button>
                                    <Button variant="destructive">
                                        <Check /> Confirm
                                    </Button>
                                </DialogFooter>
                            </DialogContent>
                        </Dialog>

                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button variant="outline">Dropdown</Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent>
                                <DropdownMenuItem>View profile</DropdownMenuItem>
                                <DropdownMenuItem>Add note</DropdownMenuItem>
                                <DropdownMenuItem className="text-destructive">Flag inactive</DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>

                        <Tooltip>
                            <TooltipTrigger asChild>
                                <Button variant="outline">Hover me</Button>
                            </TooltipTrigger>
                            <TooltipContent>Tron tooltip</TooltipContent>
                        </Tooltip>

                        <Button variant="outline" onClick={() => toast.success('Saved', { description: 'Changes persisted.' })}>
                            <Bell /> Fire toast
                        </Button>
                    </div>
                </Section>

                <Section title="Loading">
                    <div className="flex items-end gap-8">
                        <div className="flex flex-col items-center gap-2">
                            <TronSpinner className="size-3.5" />
                            <span className="font-mono text-[10px] text-muted-foreground">3.5</span>
                        </div>
                        <div className="flex flex-col items-center gap-2">
                            <TronSpinner className="size-5" />
                            <span className="font-mono text-[10px] text-muted-foreground">5</span>
                        </div>
                        <div className="flex flex-col items-center gap-2">
                            <TronSpinner className="size-8" />
                            <span className="font-mono text-[10px] text-muted-foreground">8</span>
                        </div>
                        <div className="flex flex-col items-center gap-2">
                            <TronSpinner className="size-14" />
                            <span className="font-mono text-[10px] text-muted-foreground">14</span>
                        </div>
                        <span className="inline-flex items-center gap-2 rounded-md border border-border px-2.5 py-1.5 text-sm text-muted-foreground">
                            <TronSpinner /> Loading…
                        </span>
                    </div>
                    <div className="mt-6 flex items-center gap-4">
                        <Skeleton className="size-12 rounded-full" />
                        <div className="space-y-2">
                            <Skeleton className="h-4 w-48" />
                            <Skeleton className="h-4 w-32" />
                        </div>
                    </div>
                </Section>

                <Section title="Avatar + separator">
                    <div className="flex items-center gap-4">
                        <Avatar>
                            <AvatarFallback>RP</AvatarFallback>
                        </Avatar>
                        <Separator orientation="vertical" className="h-8" />
                        <span className="text-sm text-muted-foreground">Reaper — Master Sergeant</span>
                    </div>
                </Section>
            </div>
        </AppLayout>
    );
}
