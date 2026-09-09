import {
    BookOpen,
    ExternalLink,
    GraduationCap,
    Inbox,
    LayoutDashboard,
    LifeBuoy,
    MessageSquare,
    Radio,
    ScrollText,
    Shield,
    SlidersHorizontal,
    User,
    UserPlus,
    type LucideIcon,
} from 'lucide-react';

const ICONS: Record<string, LucideIcon> = {
    'book-open': BookOpen,
    'external-link': ExternalLink,
    'graduation-cap': GraduationCap,
    inbox: Inbox,
    'layout-dashboard': LayoutDashboard,
    'life-buoy': LifeBuoy,
    'message-square': MessageSquare,
    radio: Radio,
    'scroll-text': ScrollText,
    shield: Shield,
    'sliders-horizontal': SlidersHorizontal,
    user: User,
    'user-plus': UserPlus,
};

export function NavIcon({ name, className }: { name?: string; className?: string }) {
    const Icon = name ? ICONS[name] : undefined;
    if (!Icon) return null;
    return <Icon className={className} />;
}
