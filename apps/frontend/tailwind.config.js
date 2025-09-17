module.exports = {
  content: [
    './components/**/*.{vue,js}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './nuxt.config.{js,ts}',
    './app.html'
  ],
  theme: {
    extend: {
      colors: {
        indigo: { 500: '#6366f1' } // adjust if you want
      }
    }
  },
  plugins: []
}

