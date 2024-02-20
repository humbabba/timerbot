/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/views/**/*.php',
    './resources/css/*.css',
    './resources/js/components/*.js'
  ],
  theme: {
    extend: {
      animation: {
        'spin-once': 'spin 1s linear 1',
      }
    },
  },
  plugins: [],
}
