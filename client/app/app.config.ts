export default defineAppConfig({
  ui: {
    colors: {
      // Semantic color aliases - maps to Tailwind colors
      primary: 'violet',
      secondary: 'fuchsia',
      success: 'emerald',
      warning: 'amber',
      info: 'sky',
      error: 'rose',
      neutral: 'slate'
    },
    formField: {
      variants: {
        required: {
          true: {
            label: "after:content-['*'] after:ml-1.5 after:text-rose-500 after:font-bold"
          }
        }
      }
    }
  }
})
