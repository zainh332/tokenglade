/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontSize: {
        t14: '14px',
        t16: '16px',
        t24: '24px',
        t34: '34px',
      },
      lineHeight: {
        lh65: '65px'
      },
      keyframes: {
        bounceFour: {
          '0%, 100%': { transform: 'translateY(0)' },
          '12%': { transform: 'translateY(-35%)' },
          '25%': { transform: 'translateY(60%)' },
          '38%': { transform: 'translateY(-35%)' },
          '50%': { transform: 'translateY(15%)' },
          '65%': { transform: 'translateY(-15%)' },
          '80%': { transform: 'translateY(8%)' },
          '92%': { transform: 'translateY(-4%)' },
        },
        float: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-20px)' },
        },
        pulseSoft: {
          '0%, 100%': { transform: 'scale(1)' },
          '50%': { transform: 'scale(1.1)' },
        },
        footerFloat: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-4px)' },
        },
        gradientMove: {
          '0%': { 'background-position': '0% 50%' },
          '50%': { 'background-position': '100% 0%' },
          '100%': { 'background-position': '0% 50%' },
        },
        floatCard: {
          '0%, 100%': { transform: 'translateY(0)' },
          '50%': { transform: 'translateY(-40px)' },
        },

      },
      animation: {
        bounceFour: 'bounceFour 1.8s cubic-bezier(0.77, 0, 0.175, 1) both',
        float: 'float 3s ease-in-out infinite',
        pulseSoft: 'pulseSoft 2.5s ease-in-out infinite',
        footerFloat: 'footerFloat 3s ease-in-out infinite',
        gradientMove: 'gradientMove 3s linear infinite',
        float: 'float 3s ease-in-out infinite',
      },
    },
  },
  plugins: [],
}
