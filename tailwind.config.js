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
                navy: {
                    950: '#060B15',
                    900: '#0B1528',
                    850: '#101E38',
                    800: '#17284A',
                    700: '#223863',
                },
                fintech: {
                    navy: '#0B1528',
                    blue: '#2563EB',
                    bg: '#F1F5F9',
                    card: '#FFFFFF',
                    text: '#0F172A',
                    muted: '#64748B',
                    border: '#E2E8F0',
                },
            },
            boxShadow: {
                xs: '0 1px 2px 0 rgba(15, 23, 42, 0.05)',
                card: '0 1px 3px 0 rgba(15, 23, 42, 0.06), 0 1px 2px -1px rgba(15, 23, 42, 0.04)',
            },
        },
    },

    plugins: [forms],
};
