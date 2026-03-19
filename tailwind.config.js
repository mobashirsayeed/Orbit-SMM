/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './resources/js/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                orbit: {
                    50: '#f0f0ff',
                    100: '#e0e0ff',
                    200: '#c7c7fe',
                    300: '#a4a4fc',
                    400: '#8181f7',
                    500: '#6c63ff',
                    600: '#5a4ef0',
                    700: '#4c3fd4',
                    800: '#3e34ab',
                    900: '#352f87',
                },
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
        },
    },
    plugins: [],
};
