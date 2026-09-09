import { type ReactNode } from 'react';
import {
    Area,
    AreaChart,
    Bar,
    BarChart,
    CartesianGrid,
    Legend,
    Line,
    LineChart,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';

import { ChartTooltip } from '@/components/charts/chart-tooltip';
import { useReducedMotion } from '@/hooks/use-reduced-motion';

const ANIM_MS = 700;

export const SERIES = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];

const AXIS = { stroke: 'var(--border-strong)', fontSize: 11, tickLine: false } as const;
const X_AXIS = { ...AXIS, minTickGap: 28, interval: 'preserveStartEnd' } as const;
const GRID = { stroke: 'var(--border)', strokeDasharray: '0' } as const;
const TICK = { fill: 'var(--muted-foreground)', fontSize: 11 } as const;
const MARGIN = { top: 8, right: 12, bottom: 0, left: -8 } as const;

interface SeriesDef {
    key: string;
    label: string;
}

interface BaseProps {
    data: Record<string, number | string>[];
    x: string;
    series: SeriesDef[];
    height?: number;
}

function Frame({ height = 240, children }: { height?: number; children: ReactNode }) {
    return (
        <ResponsiveContainer width="100%" height={height} debounce={50}>
            {children as never}
        </ResponsiveContainer>
    );
}

/* Legend labels wear a text token; only the swatch carries series identity. */
const legendText = (value: string) => <span className="text-muted-foreground">{value}</span>;
const legend = (
    <Legend iconType="square" iconSize={10} formatter={legendText} wrapperStyle={{ fontSize: 12, paddingTop: 8 }} />
);

const tooltip = (
    <Tooltip
        content={(props) => <ChartTooltip {...(props as Record<string, unknown>)} />}
        cursor={{ stroke: 'var(--border-strong)', strokeWidth: 1 }}
    />
);

const barTooltip = (
    <Tooltip
        content={(props) => <ChartTooltip {...(props as Record<string, unknown>)} />}
        cursor={{ fill: 'var(--foreground)', fillOpacity: 0.04 }}
    />
);

export function ThemedLineChart({ data, x, series, height }: BaseProps) {
    const animate = !useReducedMotion();
    return (
        <Frame height={height}>
            <LineChart data={data} margin={MARGIN}>
                <CartesianGrid {...GRID} vertical={false} />
                <XAxis dataKey={x} {...X_AXIS} tick={TICK} />
                <YAxis {...AXIS} tick={TICK} width={40} />
                {tooltip}
                {series.length > 1 && legend}
                {series.map((s, i) => (
                    <Line
                        key={s.key}
                        type="monotone"
                        dataKey={s.key}
                        name={s.label}
                        stroke={SERIES[i]}
                        strokeWidth={2}
                        dot={false}
                        activeDot={{ r: 3.5, strokeWidth: 2, stroke: 'var(--card)' }}
                        isAnimationActive={animate}
                        animationDuration={ANIM_MS}
                    />
                ))}
            </LineChart>
        </Frame>
    );
}

export function ThemedAreaChart({ data, x, series, height }: BaseProps) {
    const animate = !useReducedMotion();
    return (
        <Frame height={height}>
            <AreaChart data={data} margin={MARGIN}>
                <defs>
                    {series.map((s, i) => (
                        <linearGradient key={s.key} id={`fill-${s.key}`} x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stopColor={SERIES[i]} stopOpacity={0.22} />
                            <stop offset="100%" stopColor={SERIES[i]} stopOpacity={0.02} />
                        </linearGradient>
                    ))}
                </defs>
                <CartesianGrid {...GRID} vertical={false} />
                <XAxis dataKey={x} {...X_AXIS} tick={TICK} />
                <YAxis {...AXIS} tick={TICK} width={40} domain={[0, 'auto']} />
                {tooltip}
                {series.length > 1 && legend}
                {series.map((s, i) => (
                    <Area
                        key={s.key}
                        type="monotone"
                        dataKey={s.key}
                        name={s.label}
                        stroke={SERIES[i]}
                        strokeWidth={2}
                        fill={`url(#fill-${s.key})`}
                        activeDot={{ r: 3.5, strokeWidth: 2, stroke: 'var(--card)' }}
                        isAnimationActive={animate}
                        animationDuration={ANIM_MS}
                    />
                ))}
            </AreaChart>
        </Frame>
    );
}

export function ThemedBarChart({ data, x, series, height, stacked }: BaseProps & { stacked?: boolean }) {
    const animate = !useReducedMotion();
    return (
        <Frame height={height}>
            <BarChart data={data} margin={MARGIN} barGap={2} barCategoryGap="24%">
                <CartesianGrid {...GRID} vertical={false} />
                <XAxis dataKey={x} {...X_AXIS} tick={TICK} />
                <YAxis {...AXIS} tick={TICK} width={40} domain={[0, 'auto']} />
                {barTooltip}
                {series.length > 1 && legend}
                {series.map((s, i) => (
                    <Bar
                        key={s.key}
                        dataKey={s.key}
                        name={s.label}
                        stackId={stacked ? 'a' : undefined}
                        fill={SERIES[i]}
                        radius={stacked ? 0 : [3, 3, 0, 0]}
                        stroke={stacked ? 'var(--card)' : undefined}
                        strokeWidth={stacked ? 2 : 0}
                        maxBarSize={48}
                        activeBar={{ fillOpacity: 0.82 }}
                        isAnimationActive={animate}
                        animationDuration={ANIM_MS}
                    />
                ))}
            </BarChart>
        </Frame>
    );
}

/**
 * Bare inline trend — no axes, grid, or tooltip. For table cells and stat tiles.
 * `tone`: 'auto' colours by trend direction, otherwise a fixed series slot.
 */
export function Sparkline({
    data,
    tone = 'neutral',
    width = 96,
    height = 28,
}: {
    data: number[];
    tone?: 'auto' | 'neutral' | 1 | 2 | 3 | 4 | 5;
    width?: number;
    height?: number;
}) {
    const points = data.map((value, i) => ({ i, value }));
    const color =
        tone === 'auto'
            ? data[data.length - 1] >= data[0]
                ? 'var(--success)'
                : 'var(--destructive)'
            : tone === 'neutral'
              ? 'var(--muted-foreground)'
              : SERIES[tone - 1];

    return (
        <ResponsiveContainer width={width} height={height}>
            <LineChart data={points} margin={{ top: 3, right: 2, bottom: 3, left: 2 }}>
                <Line type="monotone" dataKey="value" stroke={color} strokeWidth={1.5} dot={false} isAnimationActive={false} />
            </LineChart>
        </ResponsiveContainer>
    );
}
