export default {
  modules: ['@nuxtjs/axios', '@nuxtjs/tailwindcss'],

  css: ['~/assets/tailwind.css', 'aos/dist/aos.css'], // keep AOS if you use it

  build: {
    postcss: {
      plugins: {
        tailwindcss: {},
        autoprefixer: {}
      }
    }
  },

  // keep your mock API etc.
  // serverMiddleware: [{ path: '/api', handler: '~/server-middleware/mocks.js' }],
  server: { host: '0.0.0.0', port: 3000 }
}
