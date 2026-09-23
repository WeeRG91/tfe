import '../css/app.css';
import './bootstrap.ts';

import { createI18nInstance } from '@/i18n';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createPinia } from 'pinia';
import type { DefineComponent } from 'vue';
import { createApp, Fragment, h } from 'vue';
import { Toaster } from 'vue-sonner';
import 'vue-sonner/style.css';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

const pinia = createPinia();

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const i18n = createI18nInstance(
            props.initialPage.props.locale,
            props.initialPage.props.fallbackLocale,
        );

        initializeTheme(
            props.initialPage.props.theme.surface,
            props.initialPage.props.theme.restaurantDefaultKey,
            props.initialPage.props.theme.customThemes,
        );

        createApp({
            render: () =>
                h(Fragment, [
                    h(App, props),
                    h(Toaster, {
                        position: 'top-right',
                        closeButton: true,
                        closeButtonPosition: 'top-right',
                        richColors: true,
                    }),
                ]),
        })
            .use(plugin)
            .use(pinia)
            .use(i18n)
            .mount(el);
    },
    progress: {
        color: 'var(--primary)',
    },
});
