export default {
  testEnvironment: 'jsdom',
  transform: {
    '^.+\\.vue$': '@vue/vue3-jest',
    '^.+\\.js$': 'babel-jest'
  },
  moduleFileExtensions: ['vue', 'js', 'json', 'jsx'],
  moduleNameMapper: {
    '^@/(.*)$': '<rootDir>/resources/js/$1'
  },
  testMatch: [
    '**/resources/js/**/*.spec.js'
  ],
  testEnvironmentOptions: {
    customExportConditions: ['node', 'node-addons']
  }
}
