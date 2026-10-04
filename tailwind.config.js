import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    // Portfolio V2 is intentionally dark-only.
    //
    // There is deliberately no `darkMode` strategy here. The design tokens (ink-*, bone-*)
    // are literal colours rather than semantic variables, so `dark:` variants have no
    // light counterpart to switch to. Enabling `darkMode: 'class'` previously exposed a
    // theme toggle that could not actually change the page, which is worse than having
    // no toggle at all. `color-scheme: dark` in app.css keeps native form controls,
    // scrollbars and select popovers dark instead.

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'InterVar',
                    'Inter',
                    ...defaultTheme.fontFamily.sans,
                ],
            },

            colors: {
                // Neutral, near-black surfaces. Used only by the admin dashboard.
                primary: {
                    DEFAULT: '#25274D',
                    50: '#C2C4E2',
                    100: '#B4B6DB',
                    200: '#999CCE',
                    300: '#7D81C1',
                    400: '#6266B3',
                    500: '#4D51A0',
                    600: '#3F4384',
                    700: '#323569',
                    800: '#25274D',
                    900: '#131427',
                    950: '#0A0A14',
                },

                // Portfolio V2 design tokens.
                ink: {
                    950: '#08090b',
                    900: '#0b0d10',
                    850: '#0f1216',
                    800: '#14181d',
                    700: '#1c2127',
                    600: '#272d35',
                    500: '#3a424c',
                },

                bone: {
                    50: '#f7f6f3',
                    100: '#ecebe6',
                    200: '#d8d6cf',
                    300: '#b6b3aa',
                    400: '#8d8a82',
                    500: '#6d6a64',
                },

                // Single restrained accent.
                accent: {
                    300: '#a5f3d0',
                    400: '#6ee7b7',
                    500: '#34d399',
                    600: '#10b981',
                    700: '#059669',
                },
            },

            maxWidth: {
                prose: '68ch',
            },

            letterSpacing: {
                tightest: '-0.035em',
            },

            boxShadow: {
                card: '0 1px 2px 0 rgb(0 0 0 / 0.35), 0 8px 24px -12px rgb(0 0 0 / 0.55)',
                'card-hover': '0 2px 4px 0 rgb(0 0 0 / 0.4), 0 18px 40px -16px rgb(0 0 0 / 0.65)',
                ring: '0 0 0 1px rgb(52 211 153 / 0.35)',
            },

            keyframes: {
                'fade-rise': {
                    from: { opacity: '0', transform: 'translate3d(0, 14px, 0)' },
                    to: { opacity: '1', transform: 'translate3d(0, 0, 0)' },
                },
                'fade-in': {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
            },

            animation: {
                'fade-rise': 'fade-rise 0.55s cubic-bezier(0.22, 1, 0.36, 1) both',
                'fade-in': 'fade-in 0.4s ease-out both',
            },
        },
    },

    plugins: [forms],
};