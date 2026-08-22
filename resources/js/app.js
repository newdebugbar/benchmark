import { createInertiaApp } from '@inertiajs/vue3';
import { createApp, h } from 'vue';

const setTheme = (theme) => {
    document.documentElement.dataset.theme = theme;
    localStorage.setItem('morrow-theme', theme);
};

document.addEventListener('click', (event) => {
    const themeToggle = event.target.closest('[data-theme-toggle]');

    if (themeToggle) {
        setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark');
    }

    const menuToggle = event.target.closest('.mobile-menu');

    if (menuToggle) {
        const sidebar = menuToggle.closest('.sidebar');
        const isOpen = sidebar.classList.toggle('is-open');
        menuToggle.setAttribute('aria-expanded', String(isOpen));
    }
});

if (document.querySelector('#app')) {
    const pages = import.meta.glob('./Pages/**/*.vue');

    createInertiaApp({
        resolve: (name) => pages[`./Pages/${name}.vue`](),
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) })
                .use(plugin)
                .mount(el);
        },
    });
}
