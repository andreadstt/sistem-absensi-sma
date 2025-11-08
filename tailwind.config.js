import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms, require('daisyui')],

    daisyui: {
        themes: [
            {
                light: {
                    primary: "#6366f1",
                    secondary: "#ec4899",
                    accent: "#14b8a6",
                    neutral: "#1f2937",
                    "base-100": "#ffffff",
                    "base-200": "#f3f4f6",
                    "base-300": "#e5e7eb",
                    "base-content": "#1f2937",
                    info: "#3b82f6",
                    success: "#10b981",
                    warning: "#f59e0b",
                    error: "#ef4444",
                },
                dark: {
                    primary: "#818cf8",
                    secondary: "#f472b6",
                    accent: "#2dd4bf",
                    neutral: "#1e293b",
                    "base-100": "#0f172a", // Slate-900 - elegant dark blue
                    "base-200": "#1e293b", // Slate-800
                    "base-300": "#334155", // Slate-700
                    "base-content": "#f1f5f9", // Slate-100 - softer white
                    info: "#60a5fa",
                    success: "#34d399",
                    warning: "#fbbf24",
                    error: "#f87171",
                },
            },
        ],
    },
};