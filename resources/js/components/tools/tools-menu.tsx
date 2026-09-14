import { Wrench } from 'lucide-react';
import { useState } from 'react';

import { TOOLS } from '@/components/tools/tools-registry';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export function ToolsMenu() {
    const [activeToolId, setActiveToolId] = useState<string | null>(null);
    const activeTool = TOOLS.find((t) => t.id === activeToolId) ?? null;

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" aria-label="Tools">
                        <Wrench className="size-5" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>Tools</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {TOOLS.map((tool) => (
                        <DropdownMenuItem key={tool.id} onSelect={() => setActiveToolId(tool.id)}>
                            <tool.icon className="size-4" />
                            {tool.label}
                        </DropdownMenuItem>
                    ))}
                </DropdownMenuContent>
            </DropdownMenu>
            {activeTool && (
                <activeTool.component open onOpenChange={(v) => !v && setActiveToolId(null)} />
            )}
        </>
    );
}
