/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#eff6ff',
                    100: '#dbeafe',
                    200: '#bfdbfe',
                    300: '#93c5fd',
                    400: '#60a5fa',
                    500: '#3b82f6',
                    600: '#2563eb',
                    700: '#1d4ed8',
                    800: '#1e40af',
                    900: '#1e3a8a',
                },
                secondary: {
                    50: '#eef2ff',
                    100: '#e0e7ff',
                    200: '#c7d2fe',
                    300: '#a5b4fc',
                    400: '#818cf8',
                    500: '#6366f1',
                    600: '#4f46e5',
                    700: '#4338ca',
                    800: '#3730a3',
                    900: '#312e81',
                },
            },
            fontFamily: {
                sans: ['Figtree', 'sans-serif'],
            },
            spacing: {
                'sidebar': '280px',
                'header': '64px',
            },
            boxShadow: {
                'card': '0 1px 3px rgba(0, 0, 0, 0.06)',
                'card-hover': '0 4px 20px rgba(0, 0, 0, 0.08)',
                'dropdown': '0 10px 40px rgba(0, 0, 0, 0.1)',
            },
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
};