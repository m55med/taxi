module.exports = {
  env: {
    browser: true,
    es2021: true,
    jquery: true
  },
  extends: [
    'eslint:recommended'
  ],
  parserOptions: {
    ecmaVersion: 'latest',
    sourceType: 'module'
  },
  rules: {
    // Allow console.log for debugging
    'no-console': 'off',

    // Allow unused variables that start with underscore
    'no-unused-vars': ['error', {
      'argsIgnorePattern': '^_',
      'varsIgnorePattern': '^_'
    }],

    // Allow == instead of === for jQuery operations
    'eqeqeq': ['error', 'allow-null'],

    // Allow function declarations
    'func-names': 'off',

    // Allow reassigning function parameters
    'no-param-reassign': ['error', {
      'props': false
    }],

    // Allow ++ and --
    'no-plusplus': 'off',

    // Allow for-in loops
    'no-restricted-syntax': ['error', {
      'selector': 'ForInStatement',
      'message': 'for..in loops iterate over the entire prototype chain, which is virtually never what you want. Use Object.{keys,values,entries}, and iterate over the resulting array.'
    }],

    // Allow alert, confirm, prompt
    'no-alert': 'off',

    // Allow bitwise operators
    'no-bitwise': 'off',

    // Allow continue statement
    'no-continue': 'off',

    // Allow nested ternary operators
    'no-nested-ternary': 'off',

    // Allow reassignment of function parameters
    'no-shadow': 'off',

    // Allow undefined variables (for globals)
    'no-undef': 'off',

    // Allow dangling underscores in identifiers
    'no-underscore-dangle': 'off',

    // Allow ternary operators
    'no-unused-expressions': ['error', {
      'allowTernary': true
    }],

    // Allow object shorthand
    'object-shorthand': 'off',

    // Prefer const over let
    'prefer-const': 'error',

    // Allow var declarations
    'no-var': 'off'
  },
  globals: {
    // jQuery globals
    '$': 'readonly',
    'jQuery': 'readonly',

    // Common browser globals
    'window': 'readonly',
    'document': 'readonly',
    'navigator': 'readonly',
    'location': 'readonly',
    'localStorage': 'readonly',
    'sessionStorage': 'readonly',

    // Project specific globals
    'BASE_URL': 'readonly',
    'URLROOT': 'readonly',
    'APPROOT': 'readonly',

    // Libraries
    'Papa': 'readonly', // papaparse
    'XLSX': 'readonly', // xlsx
    'Quill': 'readonly' // quill editor
  },
  ignorePatterns: [
    'node_modules/',
    'vendor/',
    'public/uploads/',
    '*.min.js',
    'public/js/xlsx.full.min.js',
    'public/js/papaparse.min.js'
  ]
};
