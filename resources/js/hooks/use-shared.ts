import { usePage } from '@inertiajs/react';

import type { SharedProps } from '@/types';

export function useShared() {
    return usePage<SharedProps>().props;
}

export function useAuth() {
    return useShared().auth;
}
