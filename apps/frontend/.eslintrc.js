module.exports = {
  root: true,
  env: { browser: true, node: true, jest: true },
  extends: [
    'eslint:recommended',
    'plugin:vue/recommended',
    'plugin:nuxt/recommended',
    'prettier'
  ],
  plugins: ['vue', 'nuxt'],
  rules: {
    'vue/multi-word-component-names': 'off'
  }
};
