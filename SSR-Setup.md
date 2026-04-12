# 🚀 Laravel + Inertia.js 2 + Vue 3 SSR Setup (Vite)

Diese Anleitung beschreibt ein funktionierendes SSR-Setup für:
- Laravel
- Inertia.js (Vue 3 Adapter)
- Vite 6+
- Vue 3 SSR (`@vue/server-renderer`)

---

# 1. NPM Dependencies

    npm install vue @vue/server-renderer

## 2. Vite Config (SSR aktivieren)

import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            ssr: 'resources/js/ssr.js', // 👈 wichtig
            refresh: true,
        }),
        vue(),
    ],
})

## 3. SSR Entry File (resources/js/ssr.js)

import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import { Link } from '@inertiajs/vue3'

createServer(page =>
    createInertiaApp({
        page,
        render: renderToString,

        resolve: name => {
            const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
            return pages[`./Pages/${name}.vue`]
        },

        setup({ App, props, plugin }) {
            return createSSRApp({
                render: () => h(App, props),
            })
                .use(plugin)
                .component('Link', Link)
                .use(ZiggyVue)
        },
    })
)

## 4. NPM Scripts
{
  "scripts": {
    "dev": "vite",
    
    "build": "vite build && vite build --ssr",
    
    "ssr": "php artisan inertia:start-ssr",
    
    "devssr": "npm run build && npm run ssr"
  }
}

## 5. WICHTIG: SSR-safe Code
❌ Falsch (bricht SSR) window gibt es in nodejs nicht
window.location.pathname
✅ Richtig (Inertia SSR-safe)
import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

const page = usePage()

const isHomePage = computed(() => page.url === '/')

# SSR am Server via Supervisor

### 1 Supervisor installieren

	sudo apt-get install supervisor -y

### 2 Supervisor Config anlegen

	sudo nano /etc/supervisor/conf.d/inertia-ssr.conf

	Inhalt:
	[program:inertia-ssr]
	directory=/var/www/petrilog.com/current
	command=php artisan inertia:start-ssr
	autostart=true
	autorestart=true
	user=deployer
	redirect_stderr=true
	stdout_logfile=/var/www/petrilog.com/shared/logs/storage/inertia-ssr.log

### 3 Log-Verzeichnis erstellen

	mkdir -p /var/www/petrilog.com/shared/logs

### 4 Supervisor starten

	sudo supervisorctl reread
	sudo supervisorctl update
	sudo supervisorctl start inertia-ssr

# Deploy Script anpassen

task('nginx:reload', function () {
    run('sudo systemctl reload nginx');
});

task('ssr:restart', function () {
    run('sudo supervisorctl restart inertia-ssr');
});

after('deploy:success', 'nginx:reload');
after('deploy:success', 'ssr:restart');


## Deployer user Rechte geben

    sudo visudo

Ganz unten im file folgende Zeilen eingeben
    
    deployer ALL=(ALL) NOPASSWD: /bin/systemctl restart php8.3-fpm
    deployer ALL=NOPASSWD: /bin/systemctl reload nginx