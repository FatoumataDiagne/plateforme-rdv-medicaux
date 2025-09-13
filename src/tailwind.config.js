/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,ts}",
  ],
  theme: {
    extend: {
      colors: {
        medical: {
          primary: '#2563eb',
          secondary: '#0ea5e9', 
          accent: '#22d3ee',
          dark: '#1e40af',
          light: '#dbeafe'
        }
      },
      fontFamily: {
        'sans': ['Inter', 'system-ui', 'sans-serif'],
      }
    },
  },
  plugins: [],
}