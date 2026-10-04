import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
|
| Dark is the identity. Light mode is an explicit, remembered opt-in. The
| inline script in the layout head applies the stored preference before first
| paint, so the wrong theme is never flashed.
|
*/
const THEME_KEY = 'srj-theme';

function applyTheme(theme) {
    const root = document.documentElement;

    if (theme === 'light') {
        root.classList.remove('dark');
    } else {
        root.classList.add('dark');
    }

    root.dataset.theme = theme === 'light' ? 'light' : 'dark';
}

Alpine.store('theme', {
    current: document.documentElement.dataset.theme || 'dark',

    init() {
        this.toggle = () => {
            this.current = this.current === 'light' ? 'dark' : 'light';

            applyTheme(this.current);

            try {
                window.localStorage.setItem(THEME_KEY, this.current);
            } catch (error) {
                /* Storage can be unavailable in private mode; the toggle still works. */
            }
        };
    },
});

/*
|--------------------------------------------------------------------------
| Scroll reveal
|--------------------------------------------------------------------------
|
| A single IntersectionObserver drives every [data-reveal] element. No
| animation library, no layout thrash, and content stays visible when
| JavaScript is unavailable or reduced motion is requested.
|
*/
function initReveal() {
    const targets = Array.from(document.querySelectorAll('[data-reveal]'));

    if (targets.length === 0) {
        return;
    }

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || typeof IntersectionObserver === 'undefined') {
        targets.forEach((element) => element.setAttribute('data-reveal', 'visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.setAttribute('data-reveal', 'visible');
                observer.unobserve(entry.target);
            });
        },
        { rootMargin: '0px 0px -10% 0px', threshold: 0.08 }
    );

    targets.forEach((element) => observer.observe(element));
}

Alpine.start();

initReveal();