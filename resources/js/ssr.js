import { createSSRApp, h } from 'vue';
import { renderToString } from '@vue/server-renderer';
import { createInertiaApp } from '@inertiajs/vue3';
import createServer from '@inertiajs/vue3/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { translate } from './Composables/useI18n';

const appName = import.meta.env.VITE_APP_NAME || 'Movie®';

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        title: (title) => (title ? `${title} | ${appName}` : `${appName} - Your Ultimate Movie Experience`),
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ App, props, plugin }) {
            const app = createSSRApp({ render: () => h(App, props) });

            app.config.globalProperties.$t = function (key, replacements = {}) {
                // In SSR, currentLocale ref might not be available or stable across requests
                // Best to read it from props
                const locale = props.initialPage.props.locale || 'tr';
                return translate(key, locale, replacements);
            };

            return app.use(plugin);
        },
    })
);
