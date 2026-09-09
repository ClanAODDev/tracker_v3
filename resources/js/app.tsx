import '../css/app.css';

import { createInertiaApp } from '@inertiajs/react';
import type { ComponentType } from 'react';
import { createRoot } from 'react-dom/client';

import { TooltipProvider } from '@/components/ui/tooltip';

const pages = import.meta.glob<{ default: ComponentType }>('./pages/**/*.tsx');

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
