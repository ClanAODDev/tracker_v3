import { Label } from '@/components/ui/label';
import { SimpleSelect } from '@/components/ui/simple-select';
import { cn } from '@/lib/utils';

export interface UnitOption {
    id: number;
    name: string;
    levelLabel: string;
    membersCount: number;
    leaderName: string | null;
    children: UnitOption[];
}

export function unitLevels(units: UnitOption[], path: string[]) {
    const levels: Array<{ options: UnitOption[]; selected: string }> = [];
    let options = units;

    for (let depth = 0; options.length > 0; depth++) {
        const selected = path[depth] ?? '';
        levels.push({ options, selected });
        options = options.find((unit) => String(unit.id) === selected)?.children ?? [];
    }

    return levels;
}

export function findUnitByPath(units: UnitOption[], path: string[]): UnitOption | null {
    let unit: UnitOption | null = null;
    let options = units;

    for (const id of path) {
        unit = options.find((option) => String(option.id) === id) ?? null;
        if (!unit) return null;
        options = unit.children;
    }

    return unit;
}

export function unitPathIsComplete(units: UnitOption[], path: string[]): boolean {
    const unit = findUnitByPath(units, path);
    return unit !== null && unit.children.length === 0;
}

export function unitPathLabel(units: UnitOption[], path: string[]): string {
    const names: string[] = [];
    let options = units;

    for (const id of path) {
        const unit = options.find((option) => String(option.id) === id);
        if (!unit) break;
        names.push(unit.name);
        options = unit.children;
    }

    return names.join(' › ');
}

const NONE = '__none';

export function UnitCascadeSelect({
    units,
    path,
    onChange,
    requireLeaf = false,
    showDetails = false,
    className,
}: {
    units: UnitOption[];
    path: string[];
    onChange: (path: string[]) => void;
    requireLeaf?: boolean;
    showDetails?: boolean;
    className?: string;
}) {
    const levels = unitLevels(units, path);

    function choose(depth: number, value: string) {
        onChange([...path.slice(0, depth), ...(value === NONE ? [] : [value])]);
    }

    function optionLabel(unit: UnitOption) {
        if (!showDetails) return unit.name;
        return `${unit.name} (${unit.membersCount})${unit.leaderName ? ` — ${unit.leaderName}` : ''}`;
    }

    return (
        <div className={cn('grid gap-4', className)}>
            {levels.map((level, depth) => {
                const label = level.options[0].levelLabel;
                const required = depth === 0 || requireLeaf;

                return (
                    <div key={depth} className="grid gap-1.5">
                        <Label>
                            {label}
                            {required ? ' *' : <span className="text-muted-foreground"> (optional)</span>}
                        </Label>
                        <SimpleSelect
                            value={level.selected || NONE}
                            onChange={(value) => choose(depth, value)}
                            placeholder={`Select ${label}…`}
                            options={[
                                {
                                    value: NONE,
                                    label: depth === 0 || requireLeaf ? `Select ${label}…` : `No ${label} assignment`,
                                },
                                ...level.options.map((unit) => ({ value: String(unit.id), label: optionLabel(unit) })),
                            ]}
                        />
                    </div>
                );
            })}
        </div>
    );
}
