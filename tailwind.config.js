
module.exports = {
  darkMode: 'class',
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
    './storage/framework/views/*.php',
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    './vendor/laravel/jetstream/**/*.blade.php',
  ],
  theme: {
    extend: {},
  },
  safelist: [
    // classes you build dynamically or often add/remove in Blade
    'ring-2','ring-blue-400',
    'bg-gray-800','bg-gray-800/90','bg-gray-700','bg-gray-700/70','bg-gray-900',
    'border-gray-700',
    'text-gray-100','text-gray-200','text-gray-300',
    'bg-blue-600','hover:bg-blue-700'
  ],
  plugins: [],
}
