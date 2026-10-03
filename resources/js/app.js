import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { translate, currentLocale } from './Composables/useI18n';

const appName = import.meta.env.VITE_APP_NAME || 'Movie®';

createInertiaApp({
    title: (title) => (title ? `${title} | ${appName}` : `${appName} - Your Ultimate Movie Experience`),
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.config.globalProperties.$t = function (key, replacements = {}) {
            return translate(key, currentLocale.value, replacements);
        };

        return app
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#e50914',
        showSpinner: true,
    },
});
