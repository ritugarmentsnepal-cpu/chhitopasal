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
                sans: ['Poppins', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Red Mockup Palette
                primary: '#FF4C4C',
                'primary-dark': '#E53935',
                'primary-light': '#FF7A7A',
                'banner-pink': '#FFD1DC',
                'banner-yellow': '#FDE68A',
                'banner-gray': '#E2E8F0',
                'banner-brown': '#D9A05B',
                'text-dark': '#1F2937',
                'text-light': '#6B7280',
                
                // Backward compatibility during transition
                mango: '#FF4C4C', 
                'mango-soft': 'rgba(255, 76, 76, 0.1)',
                'mango-hover': '#E53935',
                wildOrchid: '#E53935',
                softPearl: '#F9FAFB',
                ink: '#1F2937',
                'ink-light': '#4B5563',
                'txt-secondary': '#6B7280',
                'txt-tertiary': '#9CA3AF',
                success: '#10B981',
                'success-soft': 'rgba(16, 185, 129, 0.1)',
                border: '#E5E7EB',
                divider: '#F3F4F6',
            },
            borderRadius: {
                'sm-custom': '4px',
                'md-custom': '8px',
                'lg-custom': '16px',
                'xl-custom': '24px',
                'hero': '4rem',
            },
            boxShadow: {
                'card': '0 4px 15px rgba(0, 0, 0, 0.05)',
                'card-hover': '0 10px 25px rgba(255, 76, 76, 0.2)',
                'sheet': '0 -4px 30px rgba(0, 0, 0, 0.1)',
                'nav': '0 -1px 20px rgba(0, 0, 0, 0.05)',
                'btn': '0 4px 10px rgba(255, 76, 76, 0.3)',
            },
            spacing: {
                'header-mobile': '60px',
                'header-desktop': '80px',
                'bottom-nav': '64px',
            }
        },
    },

    plugins: [forms],
};
