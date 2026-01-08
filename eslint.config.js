export default [
  {
    files: ["**/*.js"],
    languageOptions: {
      ecmaVersion: 2021,
      sourceType: "script",
      globals: {
        chrome: "readonly",
        API: "writable",
        Storage: "writable",
        window: "readonly",
        document: "readonly",
        console: "readonly",
        setTimeout: "readonly",
        fetch: "readonly",
        MutationObserver: "readonly",
        navigator: "readonly",
        confirm: "readonly",
        alert: "readonly"
      }
    },
    rules: {
      "no-unused-vars": "warn",
      "no-console": "off",
      "no-undef": "error",
      "semi": ["error", "always"],
      "quotes": ["warn", "single", { "avoidEscape": true }]
    }
  }
];
