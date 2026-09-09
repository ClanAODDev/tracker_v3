import { Check, ChevronsUpDown, Loader2 } from 'lucide-react';
import { useState } from 'react';

import { Button } from '@/components/ui/button';
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList,
} from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { useMemberSearch, type MemberSearchResult } from '@/hooks/use-member-search';
import { cn } from '@/lib/utils';

export type MemberResult = MemberSearchResult;

interface Props {
    value: MemberResult | null;
    onSelect: (member: MemberResult | null) => void;
    placeholder?: string;
}

export function MemberCombobox({ value, onSelect, placeholder = 'Search members…' }: Props) {
    const [open, setOpen] = useState(false);
    const [query, setQuery] = useState('');
    const { results, loading } = useMemberSearch(query);

    return (
        <Popover open={open} onOpenChange={setOpen}>
            <PopoverTrigger asChild>
                <Button variant="outline" role="combobox" aria-expanded={open} className="w-full justify-between font-normal">
                    {value ? (
                        <span>
                            {value.rankName} <span className="text-muted-foreground">· {value.division}</span>
                        </span>
                    ) : (
                        <span className="text-muted-foreground">{placeholder}</span>
                    )}
                    <ChevronsUpDown className="size-4 shrink-0 opacity-50" />
                </Button>
            </PopoverTrigger>
            <PopoverContent className="w-(--radix-popover-trigger-width) p-0" align="start">
                <Command shouldFilter={false}>
                    <CommandInput value={query} onValueChange={setQuery} placeholder={placeholder} />
                    <CommandList>
                        {loading && (
                            <div className="flex items-center justify-center gap-2 py-6 text-sm text-muted-foreground">
                                <Loader2 className="size-4 animate-spin" /> Searching…
                            </div>
                        )}
                        {!loading && query.trim().length >= 2 && results.length === 0 && (
                            <CommandEmpty>No members found.</CommandEmpty>
                        )}
                        {!loading && query.trim().length < 2 && (
                            <div className="py-6 text-center text-sm text-muted-foreground">Type at least 2 characters.</div>
                        )}
                        {!loading && results.length > 0 && (
                            <CommandGroup>
                                {results.map((member) => (
                                    <CommandItem
                                        key={member.clanId}
                                        value={String(member.clanId)}
                                        onSelect={() => {
                                            onSelect(member);
                                            setOpen(false);
                                        }}
                                    >
                                        <Check
                                            className={cn(
                                                'size-4',
                                                value?.clanId === member.clanId ? 'opacity-100' : 'opacity-0',
                                            )}
                                        />
                                        <span>
                                            {member.rankName}{' '}
                                            <span className="text-muted-foreground">· {member.division}</span>
                                        </span>
                                    </CommandItem>
                                ))}
                            </CommandGroup>
                        )}
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    );
}
