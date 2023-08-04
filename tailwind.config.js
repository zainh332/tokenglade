/** @type {import('tailwindcss').Config} */
export default {
  content: [
    // "./resources/views/welcome.blade.php",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      fontSize:{
        t14:'14px',
        t16:'16px',
        t24:'24px',
        t34:'34px',
      },
      lineHeight:{
        lh65:'65px'
      }
    },
  },
  plugins: [
   
  ],
}