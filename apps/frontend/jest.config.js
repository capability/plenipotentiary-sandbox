module.exports = {
  testEnvironment: 'jsdom',           
  moduleFileExtensions: ['js', 'json', 'vue'],
  transform: {
    '^.+\\.js$': 'babel-jest',
    '.*\\.vue$': 'vue-jest',          // v3 for Vue 2
  },
  transformIgnorePatterns: ['/node_modules/'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/$1',
    '^~/(.*)$': '<rootDir>/$1',
  },
  setupFilesAfterEnv: ['<rootDir>/test/setup/jest.setup.js'],
  collectCoverage: true,
  collectCoverageFrom: [
    'components/**/*.{js,vue}',
    'pages/**/*.vue',
    '!**/node_modules/**',
  ],
};
