import { createInertiaApp, router } from '@inertiajs/vue3';

import { showError } from '@/lib/alerts';

router.on('error', ({ detail: { errors } }) => {
    const messages = [...new Set(Object.values(errors).flat())].filter(Boolean);
    if (messages.length) showError(messages.join('\n'));
});

const appName = import.meta.env.VITE_APP_NAME || 'Hospia';

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#1d4ed8',
    },
});
