// @ts-check
/** @type {import('@commitlint/types').UserConfig} */
export default {
  extends: ['@commitlint/config-conventional'],
  rules: {
    'type-enum': [
      2,
      'always',
      [
        'feat',
        'fix',
        'docs',
        'style',
        'refactor',
        'test',
        'chore',
        'perf',
        'build',
        'ci',
        'revert',
      ],
    ],
    'scope-enum': [
      1,
      'always',
      [
        'config',
        'deps',
        'content',
        'schema',
        'i18n',
        'cms',
        'ui',
        'layout',
        'a11y',
        'seo',
        'security',
        'contact',
        'results',
        'ci',
        'release',
      ],
    ],
  },
};
