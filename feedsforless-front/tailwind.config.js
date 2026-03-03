/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./index.html",
        "./src/**/*.{vue,js,ts,jsx,tsx}",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    DEFAULT: '#0f172a',
                    hover: '#1e293b',
                },
                secondary: '#3b82f6',
                surface: '#ffffff',
                background: '#f8fafc',
            }
        },
    },
    plugins: [],
}