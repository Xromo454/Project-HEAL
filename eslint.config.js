// @ts-check
import js from '@eslint/js';
import { defineConfig, globalIgnores } from 'eslint/config';
import prettier from 'eslint-config-prettier';
import astro from 'eslint-plugin-astro';
import globals from 'globals';
import tseslint from 'typescript-eslint';

export default defineConfig([
  // 1. Folders ESLint should never look at
  globalIgnores(['dist/', '.astro/', 'node_modules/', 'public/']),

  // 2. Rule sets we switch on
  js.configs.recommended, //      basic JavaScript mistakes
  tseslint.configs.strict, //     TypeScript mistakes (strict = catches more)
  astro.configs.recommended, //   mistakes specific to .astro files

  // 3. Our own settings
  {
    languageOptions: {
      globals: { ...globals.browser, ...globals.node },
    },
    rules: {
      // Warn if someone leaves console.log() debugging in the code
      'no-console': ['warn', { allow: ['warn', 'error'] }],
      // Force === instead of == (== has confusing behaviour in JavaScript)
      eqeqeq: ['error', 'always'],
    },
  },

  // 4. Must be LAST: switch off any rule that would fight with Prettier
  prettier,
]);
