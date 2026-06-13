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
                sans: ['Outfit', 'Inter', ...defaultTheme.fontFamily.sans],
                display: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // ===== Bold & Vibrant Palette =====
                primary: '#EC8028',
                'primary-dark': '#D37123',
                'primary-light': '#E8CCAD',
                
                // Accent colors
                accent: {
                    pink: '#EDC55B',
                    cyan: '#65BCB4',
                    amber: '#F59E0B',
                    emerald: '#10B981',
                    rose: '#F43F5E',
                },

                // Surface colors
                surface: {
                    50: '#FAFAFE',
                    100: '#F1F0FB',
                    200: '#E8E5F5',
                },

                // Backward compatibility
                mango: '#EC8028',
                'mango-soft': 'rgba(236, 128, 40, 0.1)',
                'mango-hover': '#D37123',
                wildOrchid: '#EDC55B',
                softPearl: '#FAFAFE',
                ink: '#0F172A',
                'ink-light': '#334155',
                'txt-secondary': '#64748B',
                'txt-tertiary': '#94A3B8',
                success: '#10B981',
                'success-soft': 'rgba(16, 185, 129, 0.1)',
                border: '#E2E8F0',
                divider: '#F1F5F9',
                
                'text-dark': '#0F172A',
                'text-light': '#64748B',
                'banner-pink': '#FDF2F8',
                'banner-yellow': '#FFFBEB',
                'banner-gray': '#F1F5F9',
                'banner-brown': '#FEF3C7',
            },
            borderRadius: {
                'sm-custom': '6px',
                'md-custom': '12px',
                'lg-custom': '20px',
                'xl-custom': '28px',
                'hero': '4rem',
            },
            boxShadow: {
                'card': '0 4px 20px rgba(0, 0, 0, 0.04)',
                'card-hover': '0 20px 40px rgba(236, 128, 40, 0.15)',
                'sheet': '0 -4px 40px rgba(0, 0, 0, 0.12)',
                'nav': '0 -1px 30px rgba(0, 0, 0, 0.08)',
                'btn': '0 4px 15px rgba(236, 128, 40, 0.4)',
                'btn-pink': '0 4px 15px rgba(237, 197, 91, 0.4)',
                'glow': '0 0 30px rgba(236, 128, 40, 0.3)',
                'glow-pink': '0 0 30px rgba(237, 197, 91, 0.3)',
                'glow-cyan': '0 0 30px rgba(101, 188, 180, 0.3)',
                'glass': '0 8px 32px rgba(0, 0, 0, 0.08)',
                'glass-lg': '0 16px 48px rgba(0, 0, 0, 0.12)',
            },
            spacing: {
                'header-mobile': '60px',
                'header-desktop': '80px',
                'bottom-nav': '64px',
            },
            backgroundImage: {
                'gradient-vibrant': 'linear-gradient(135deg, #EC8028, #EDC55B)',
                'gradient-ocean': 'linear-gradient(135deg, #65BCB4, #EC8028)',
                'gradient-sunset': 'linear-gradient(135deg, #EDC55B, #E8CCAD)',
                'gradient-dark': 'linear-gradient(135deg, #0F172A, #1E1B4B)',
                'gradient-surface': 'linear-gradient(180deg, #FAFAFE 0%, #F1F0FB 100%)',
                'gradient-card': 'linear-gradient(135deg, rgba(236, 128, 40, 0.05), rgba(237, 197, 91, 0.05))',
                'gradient-mesh': 'radial-gradient(at 40% 20%, rgba(236, 128, 40, 0.08) 0px, transparent 50%), radial-gradient(at 80% 0%, rgba(237, 197, 91, 0.06) 0px, transparent 50%), radial-gradient(at 0% 50%, rgba(101, 188, 180, 0.06) 0px, transparent 50%)',
            },
            animation: {
                'shimmer': 'shimmer 2s linear infinite',
                'float': 'float 6s ease-in-out infinite',
                'glow-pulse': 'glowPulse 2s ease-in-out infinite',
                'gradient-x': 'gradientX 3s ease infinite',
                'fade-up': 'fadeUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'fade-in': 'fadeIn 0.3s ease-out forwards',
                'scale-in': 'scaleIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards',
                'slide-up': 'slideUp 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards',
            },
            keyframes: {
                shimmer: {
                    '0%': { transform: 'translateX(-100%)' },
                    '100%': { transform: 'translateX(100%)' },
                },
                float: {
                    '0%, 100%': { transform: 'translateY(0)' },
                    '50%': { transform: 'translateY(-10px)' },
                },
                glowPulse: {
                    '0%, 100%': { opacity: '0.6' },
                    '50%': { opacity: '1' },
                },
                gradientX: {
                    '0%, 100%': { backgroundPosition: '0% 50%' },
                    '50%': { backgroundPosition: '100% 50%' },
                },
                fadeUp: {
                    from: { opacity: '0', transform: 'translateY(20px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
                fadeIn: {
                    from: { opacity: '0' },
                    to: { opacity: '1' },
                },
                scaleIn: {
                    from: { opacity: '0', transform: 'scale(0.95)' },
                    to: { opacity: '1', transform: 'scale(1)' },
                },
                slideUp: {
                    from: { opacity: '0', transform: 'translateY(10px)' },
                    to: { opacity: '1', transform: 'translateY(0)' },
                },
            },
        },
    },

    plugins: [forms],
};
