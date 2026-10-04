import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

/*
|--------------------------------------------------------------------------
| Scroll reveal
|--------------------------------------------------------------------------
|
| A single IntersectionObserver drives every [data-reveal] element. No
| animation library, no layout thrash, and content stays visible when
| JavaScript is unavailable or reduced motion is requested.
|
|
| There is no theme store: Portfolio V2 is intentionally dark-only. See the
| comment in tailwind.config.js.
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