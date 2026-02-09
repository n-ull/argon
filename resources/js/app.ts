import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h, watch } from 'vue';
import { NMessageProvider } from 'naive-ui';
import { i18nVue, loadLanguageAsync } from 'laravel-vue-i18n';

// import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ 
            render: () => h(NMessageProvider, null, { default: () => h(App, props) }),
            setup() {
                // Watch for locale changes in Inertia props and load the new language
                watch(
                    () => props.initialPage.props.locale,
                    (newLocale) => {
                        if (newLocale && typeof newLocale === 'string') {
                            loadLanguageAsync(newLocale);
                        }
                    },
                    { immediate: true }
                );
            }
        });
        
        app.use(plugin)
            .use(i18nVue, {
                lang: props.initialPage.props.locale,
                fallbackLang: 'es',
                resolve: (lang: string) => {
                    const langs = import.meta.glob('../../lang/*.json', { eager: true });
                    const langModule = langs[`../../lang/php_${lang}.json`] as any;
                    return langModule?.default || langModule;
                },
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
// initializeTheme();
