// @ts-check
import withNuxt from './.nuxt/eslint.config.mjs'

export default withNuxt(
  {
    rules: {
      '@typescript-eslint/no-explicit-any': 'off',
      '@typescript-eslint/no-unused-vars': 'off',
      '@typescript-eslint/no-dynamic-delete': 'off',
      '@typescript-eslint/unified-signatures': 'off',
      'no-case-declarations': 'off',
      'no-empty': 'off',
      'vue/no-v-html': 'off',
      'vue/no-v-text-v-html-on-component': 'off',
      'vue/no-multiple-template-root': 'off',
      'vue/no-required-prop-with-default': 'off',
      '@stylistic/max-statements-per-line': 'off'
    }
  }
)
