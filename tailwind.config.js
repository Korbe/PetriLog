import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';
import plugin from 'tailwindcss/plugin';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            colors: {
                primary: {
                    50:  '#E7F3FD',
                    100: '#CFE8FC',
                    200: '#9FD0F9',
                    300: '#6FB9F6',
                    400: '#3FA2F3',
                    500: '#118DF0',
                    600: '#0C6FC0',
                    700: '#095390',
                    800: '#063760',
                    900: '#031C30',
                    950: '#020E18',
                },
                gray: {
                    50: "#f9fafb",
                    100: "#f3f4f6",
                    200: "#e5e7eb",
                    300: "#cfd2d6",
                    400: "#a9adb3",
                    500: "#181818",
                    600: "#141414",
                    700: "#101010",
                    800: "#0c0c0c",
                    900: "#080808",
                    950: "#030303"
                },
                background: "#F8F8F8",
                text: "#333333"
            },
            fontFamily: {
                inter: ["Inter", "sans-serif"],
            },
            boxShadow: {
                sm: "0 1px 1px 0 rgb(0 0 0 / 0.05), 0 1px 2px 0 rgb(0 0 0 / 0.02)",
            },
            screens: {
                xs: "480px",
            },
        },
    },

    plugins: [forms, typography, plugin(function ({ addVariant }) {
        addVariant("dark", "&:is(.dark *)");
        addVariant("sidebar-expanded", "&:is(.sidebar-expanded *)");
    })],
};