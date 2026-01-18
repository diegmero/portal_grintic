import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import * as Sentry from '@sentry/vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        // Initialize Sentry for frontend monitoring
        if (import.meta.env.VITE_SENTRY_DSN) {
            Sentry.init({
                app,
                dsn: import.meta.env.VITE_SENTRY_DSN,
                environment: import.meta.env.VITE_APP_ENV || 'local',

                // Performance Monitoring
                integrations: [
                    Sentry.browserTracingIntegration(),
                    Sentry.replayIntegration({
                        maskAllText: false,
                        blockAllMedia: false,
                    }),
                ],

                // Tracing
                tracesSampleRate: parseFloat(import.meta.env.VITE_SENTRY_TRACES_SAMPLE_RATE || '0.1'),

                // Session Replay
                replaysSessionSampleRate: 0.1, // 10% of sessions
                replaysOnErrorSampleRate: 1.0, // 100% of sessions with errors

                // Set user context when available
                beforeSend(event) {
                    // Add user context from page props if available
                    const pageProps = props?.initialPage?.props;
                    if (pageProps?.auth?.user) {
                        event.user = {
                            id: pageProps.auth.user.id,
                            email: pageProps.auth.user.email,
                            username: pageProps.auth.user.name,
                        };
                    }
                    return event;
                },
            });
        }

        return app
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
