import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'primary': {
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
                    950: '#0A0A14'
                  },
            },
        },
    },

    plugins: [forms],
};
