import './bootstrap';
import '../css/app.css';

import { createApp, h, DefineComponent } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from 'ziggy-js';
import PerformanceMetrics from './lib/performance-metrics';

// Add Ziggy type declaration
declare global {
  interface Window {
    Ziggy: any;
  }
}

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, window.Ziggy)
            .mount(el);
            
        // Initialize performance metrics collection
        PerformanceMetrics.init({
            debug: import.meta.env.DEV, // Enable debug logging in development
            samplingRate: 100, // Measure 100% of page loads during development
        });
    },
    progress: {
        color: '#4B5563',
    },
});
