/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.{js,ts,jsx,tsx}",
    "./public/**/*.html",
    "./src/**/*.php"
  ],
  theme: {
    extend: {
      colors: {
        primary: '#e41e5b',
        secondary: '#9a0864',
        neutral: '#2c2c2c',
        accent: '#746354',
        highlight: '#a67c00',
      },
      fontFamily: {
        sans: ['Inter', 'system-ui', 'sans-serif'],
      },
      screens: {
        'xs': '475px',
      }
    },
  },
  plugins: [],
}
