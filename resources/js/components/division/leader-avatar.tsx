import { cn } from '@/lib/utils';
import type { MemberCard } from '@/types';

export type Leader = Partial<MemberCard>;

export function LeaderAvatar({ leader, size = 'sm' }: { leader: Leader; size?: 'sm' | 'md' }) {
    const dim = size === 'md' ? 'size-9' : 'size-5';
    if (leader.avatarUrl) {
        return <img src={leader.avatarUrl} alt="" className={cn(dim, 'shrink-0 rounded-full')} />;
    }
    return (
        <span
            className={cn(dim === 'size-9' ? 'size-2.5' : 'size-2', 'shrink-0 rounded-full')}
            style={{ background: leader.rankColor ?? 'var(--muted-foreground)' }}
        />
    );
}
