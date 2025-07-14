/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './index.php',
    './src/**/*.{html,js,php}',
    './templates/**/*.{html,js,php}',
    './**/*.php',
    './includes/Modules/**/*.{html,js,php}'
  ],
  theme: {
    extend: {},
  },
  plugins: [],
  prefix: 'shopspark-',
    corePlugins: {
    preflight: false,
    }
}
