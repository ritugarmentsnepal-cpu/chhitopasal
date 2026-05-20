import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                mango: '#FFB627',
                'mango-soft': '#FFF3D6',
                'mango-hover': '#FFA600',
                wildOrchid: '#FF3366',
                softPearl: '#FAFBFD',
                ink: '#0A0F1E',
                'ink-light': '#1a1f35',
                'txt-secondary': '#6B7A90',
                'txt-tertiary': '#9CA8B8',
                success: '#00C48C',
                'success-soft': '#E6FAF3',
                border: '#E8ECF1',
                divider: '#F2F4F7',
            },
            borderRadius: {
                'sm-custom': '8px',
                'md-custom': '12px',
                'lg-custom': '16px',
                'xl-custom': '20px',
            },
            boxShadow: {
                'card': '0 1px 3px rgba(10,15,30,0.04), 0 4px 12px rgba(10,15,30,0.03)',
                'card-hover': '0 8px 30px rgba(10,15,30,0.10)',
                'sheet': '0 -4px 30px rgba(10,15,30,0.12)',
                'nav': '0 -1px 12px rgba(10,15,30,0.06)',
                'btn': '0 4px 14px rgba(10,15,30,0.18)',
            },
            spacing: {
                'header-mobile': '52px',
                'header-desktop': '72px',
                'bottom-nav': '60px',
            },
        },
    },

    plugins: [forms],
};
