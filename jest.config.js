export default {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.(ts|tsx)$': 'ts-jest',
    '^.+\\.(js|jsx)$': 'babel-jest'
  },
  moduleFileExtensions: ['vue', 'js', 'ts'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1'
  },
  testMatch: [
    '<rootDir>/resources/js/**/*.spec.(js|ts|vue)',
    '<rootDir>/tests/js/**/*.spec.(js|ts|vue)'
  ],
  transformIgnorePatterns: [
    '/node_modules/(?!(@vueuse|ziggy-js)/)'
  ],
  testPathIgnorePatterns: [
    '/node_modules/',
    '/vendor/'
  ],
  collectCoverage: false,
  collectCoverageFrom: [
    'resources/js/**/*.{js,ts,vue}',
    '!resources/js/**/*.d.ts',
    '!**/node_modules/**'
  ],
  coverageReporters: ['text', 'html'],
  coverageDirectory: '<rootDir>/coverage'
};
