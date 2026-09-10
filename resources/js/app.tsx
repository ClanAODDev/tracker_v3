import '../css/app.css';

import { createInertiaApp, router } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

import { TooltipProvider } from '@/components/ui/tooltip';

const pages = import.meta.glob<{ default: ComponentType }>('./pages/**/*.tsx');

// A dropped session surfaces as a non-Inertia 401/419. Send the whole window
// to a fresh login page rather than letting Inertia show its raw-response modal.
router.on('httpException', (event) => {
    const status = event.detail.response.status;
    if (status === 401 || status === 419) {
        window.location.assign('/login?expired=1');
        return false;
    }
});

// A plain link nav on a dead session silently swaps to the login page. If we were
// authenticated a moment ago, reload with ?expired=1 so the reason is shown.
router.on('success', (event) => {
    const page = event.detail.page as { component: string; props: { auth?: { user?: unknown } } };
    const authed = Boolean(page.props.auth?.user);
    try {
        if (!authed && page.component === 'auth/login' && sessionStorage.getItem('was-authed') === '1') {
            sessionStorage.removeItem('was-authed');
            window.location.assign('/login?expired=1');
            return;
        }
        sessionStorage.setItem('was-authed', authed ? '1' : '0');
    } catch {
        /* private mode */
    }
});

createInertiaApp({
    resolve: (name) => {
        const page = pages[`./pages/${name}.tsx`];
        if (!page) {
            throw new Error(`Inertia page not found: ${name}`);
        }
        return page().then((module) => module.default);
    },
    setup({ el, App, props }) {
        if (!el) {
            throw new Error('Inertia root element not found');
        }
        createRoot(el).render(
            <TooltipProvider delayDuration={200}>
                <App {...props} />
            </TooltipProvider>,
        );
    },
    progress: {
        color: '#e11d2e',
    },
});
