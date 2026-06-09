import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                // Set Poppins as the default sans-serif font
                sans: ['Poppins', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Consolidate Bayswater brand colors based on Perplexity/Visuals
                'bayswater-blue': {
                    DEFAULT: '#002346', // Primary Dark Blue (Sidebar BG)
                    light: '#00AEF0',   // Accent Light Blue (Maybe for highlights?)
                    dark: '#001a33',    // Darker Shade (Sidebar Header, Hover/Active)
                },
                'bayswater-orange': {
                    DEFAULT: '#FF9900', // Primary CTA / Button color?
                    dark: '#E68A00',    // Darker orange for hover/active
                },
                'bayswater-yellow': {
                     DEFAULT: '#FFC62E', // Yellow accent from website
                },
                 'bayswater-cream': { // From Perplexity suggestion
                      DEFAULT: '#FFEC8F',
                 },
                'bayswater-gray': {
                    DEFAULT: '#717171', // Text Gray
                    light: '#F8F8F8',   // Light Background Gray
                    medium: '#D1D5DB',  // Medium Gray for borders?
                },
                // Keep default Tailwind colors accessible too
                ...defaultTheme.colors,
            },
        }, // Closes extend
    }, // Closes theme

    plugins: [forms],
};
