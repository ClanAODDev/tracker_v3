import {
    ArrowUp,
    CalendarCheck,
    CalendarClock,
    CalendarX,
    ClipboardList,
    Globe,
    Headset,
    Ticket,
    Trophy,
    UserMinus,
    UserPlus,
    UsersRound,
    type LucideIcon,
} from 'lucide-react';

const MAP: Record<string, LucideIcon> = {
    'fa-user-plus': UserPlus,
    'fa-clipboard-list': ClipboardList,
    'fa-arrow-up': ArrowUp,
    'fa-user-clock': CalendarClock,
    'fa-trophy': Trophy,
    'fa-globe': Globe,
    'fa-exchange-alt': ArrowUp,
    'fa-calendar-alt': CalendarClock,
    'fa-calendar-check': CalendarCheck,
    'fa-calendar-times': CalendarX,
    'fa-headset': Headset,
    'fa-user-slash': UserMinus,
    'fa-users-slash': UsersRound,
    'fa-ticket-alt': Ticket,
};

export function PendingActionIcon({ icon, className }: { icon: string; className?: string }) {
    const Icon = MAP[icon] ?? ClipboardList;
    return <Icon className={className} />;
}
