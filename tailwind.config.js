import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    safelist: [
        'border-green-500', 'bg-green-100', 'dark:bg-green-900',
        'border-orange-400', 'bg-orange-100', 'dark:bg-orange-900',
        'border-yellow-400', 'bg-yellow-100', 'dark:bg-yellow-900',
        'border-blue-400', 'bg-blue-100', 'dark:bg-blue-900',
        'border-red-500', 'bg-red-100', 'dark:bg-red-900',
        'border-gray-300', 'bg-gray-50', 'dark:bg-gray-700',
        {
            pattern: /(bg|text|border|ring|from|to|via|shadow)-(primary|secondary|gray)-(50|100|200|300|400|500|600|700|800|900|950)(\/\d+)?/,
            variants: ['hover', 'dark', 'dark:hover', 'dark:focus'],
        },
    ],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./node_modules/flowbite/**/*.js",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: "#fdf5fe",
                    100: "#fae9fe",
                    200: "#f5d3fb",
                    300: "#f0b0f7",
                    400: "#e880f2",
                    500: "#d850e5",
                    600: "#be30c9",
                    700: "#9f24a5",
                    800: "#842088",
                    900: "#6e1f70",
                    950: "#49084a",
                },
                secondary: {
                    '50': '#fffbeb',
                    '100': '#fff5c6',
                    '200': '#ffe988',
                    '300': '#ffd94a',
                    '400': '#ffc214',
                    '500': '#f9a407',
                    '600': '#dd7b02',
                    '700': '#b75606',
                    '800': '#94420c',
                    '900': '#7a360d',
                    '950': '#461b02',
                },
            },
        },
    },

    plugins: [forms, typography, require('flowbite/plugin')],
};
