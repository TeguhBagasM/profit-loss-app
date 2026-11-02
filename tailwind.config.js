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
            colors: {
                paper: '#F6F7F9',
                surface: '#FFFFFF',
                ink: '#1A2330',
                muted: '#5B6675',
                line: '#E2E6EC',
                accent: '#0F766E',
                loss: '#B3354F',
            },
            fontFamily: {
                sans: ['"Hanken Grotesk Variable"', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
