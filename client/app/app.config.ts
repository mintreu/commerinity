export default defineAppConfig({
  ui: {
    colors: {
      primary: 'violet',
      secondary: 'fuchsia',
      neutral: 'slate'
    },
    button: {
      default: {
        size: 'md',
        variant: 'solid'
      },
      rounded: 'rounded-xl'
    },
    input: {
      default: {
        size: 'md'
      },
      rounded: 'rounded-xl',
      variant: {
        outline: 'bg-white dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all'
      }
    },
    card: {
      rounded: 'rounded-2xl',
      shadow: 'shadow-xl shadow-slate-200/50 dark:shadow-none',
      background: 'bg-white/95 dark:bg-slate-900/95 backdrop-blur-xl',
      border: 'border-slate-200/60 dark:border-slate-800/60'
    }
  }
})
