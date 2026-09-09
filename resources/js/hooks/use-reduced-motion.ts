import { useSyncExternalStore } from 'react';

/**
 * True when the viewer has asked for less motion — either via the OS
 * `prefers-reduced-motion` setting or the in-app "reduce animations" preference
 * (a `data-reduce-motion="true"` attribute on the document element).
 */
export function useReducedMotion(): boolean {
    return useSyncExternalStore(subscribe, getSnapshot, () => false);
}

function getSnapshot(): boolean {
    if (typeof window === 'undefined') return false;
    if (document.documentElement.dataset.reduceMotion === 'true') return true;
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function subscribe(callback: () => void): () => void {
    const media = window.matchMedia('(prefers-reduced-motion: reduce)');
    media.addEventListener('change', callback);

    const observer = new MutationObserver(callback);
    observer.observe(document.documentElement, { attributes: true, attributeFilter: ['data-reduce-motion'] });

    return () => {
        media.removeEventListener('change', callback);
        observer.disconnect();
    };
}
