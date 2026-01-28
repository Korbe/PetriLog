import './bootstrap';
import './pwa.js';
import '../css/app.css';
import 'vue-multiselect/dist/vue-multiselect.min.css'

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Link } from '@inertiajs/vue3';
import Layout from './Layouts/Dashboard/Layout.vue';
import { InertiaProgress } from '@inertiajs/progress';

const appName = import.meta.env.VITE_APP_NAME || 'PetriLog';

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    pwa.deferredPrompt = e;
    pwa.installable = true;
    alert("1 beforeinstallprompt fired");
});

window.addEventListener('appinstalled', () => {
    pwa.deferredPrompt = null;
    pwa.installable = false;
    alert("1 appinstalled fired");
});

createInertiaApp({
    title: title => `${title} - ${appName}`,
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        let page = pages[`./Pages/${name}.vue`]

        if (!name.includes('Auth') && !name.includes('Public'))
            page.default.layout ??= Layout;

        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('Link', Link)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#118DF0',
    },
});

InertiaProgress.init({
  color: '#118DF0',
  
  showSpinner: true,
  delay: 250,
});