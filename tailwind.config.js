/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
            },
            colors: {
                brand: {
                    50:  '#eef4ff',
                    100: '#d9e6ff',
                    500: '#3b6dff',
                    600: '#2454ec',
                    700: '#1a40bf',
                },
            },
        },
    },
    plugins: [],
};
