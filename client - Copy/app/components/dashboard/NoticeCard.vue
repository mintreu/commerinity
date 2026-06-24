<script setup lang="ts">
/**
 * NoticeCard - Dashboard promotional message card
 * Displays admin notices with optional CTA and dismiss functionality
 */

interface Notice {
  uuid: string
  title: string
  content: string
  type: 'info' | 'warning' | 'success' | 'promo'
  type_color: string
  type_icon: string
  cta_text?: string
  cta_link?: string
  icon?: string
  color?: string
  image_url?: string
}

interface Props {
  notice: Notice
}

defineProps<Props>()

const emit = defineEmits<{
  dismiss: [uuid: string]
  click: [uuid: string]
}>()

const router = useRouter()

const typeStyles = {
  info: {
    bg: 'bg-blue-50 dark:bg-blue-900/20',
    border: 'border-blue-200 dark:border-blue-800',
    icon: 'text-blue-600 dark:text-blue-400',
    button: 'primary' as const
  },
  warning: {
    bg: 'bg-amber-50 dark:bg-amber-900/20',
    border: 'border-amber-200 dark:border-amber-800',
    icon: 'text-amber-600 dark:text-amber-400',
    button: 'warning' as const
  },
  success: {
    bg: 'bg-emerald-50 dark:bg-emerald-900/20',
    border: 'border-emerald-200 dark:border-emerald-800',
    icon: 'text-emerald-600 dark:text-emerald-400',
    button: 'success' as const
  },
  promo: {
    bg: 'bg-purple-50 dark:bg-purple-900/20',
    border: 'border-purple-200 dark:border-purple-800',
    icon: 'text-purple-600 dark:text-purple-400',
    button: 'primary' as const
  }
}

function handleCtaClick(notice: Notice) {
  emit('click', notice.uuid)

  if (notice.cta_link) {
    // Check if internal or external link
    if (notice.cta_link.startsWith('/')) {
      router.push(notice.cta_link)
    } else {
      window.open(notice.cta_link, '_blank')
    }
  }
}
</script>

<template>
  <div
    :class="[
      'relative rounded-xl border p-4 transition-all duration-200',
      notice?.type && typeStyles[notice.type] ? typeStyles[notice.type].bg : 'bg-slate-50 dark:bg-slate-900/20',
      notice?.type && typeStyles[notice.type] ? typeStyles[notice.type].border : 'border-slate-200 dark:border-slate-800'
    ]"
  >
    <!-- Dismiss button -->
    <button
      type="button"
      class="absolute top-2 right-2 p-1 rounded-full hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
      @click="emit('dismiss', notice?.uuid)"
    >
      <UIcon
        name="i-lucide-x"
        class="w-4 h-4 text-slate-500 dark:text-slate-400"
      />
    </button>

    <div class="flex gap-4">
      <!-- Icon -->
      <div class="shrink-0">
        <div
          :class="[
            'w-10 h-10 rounded-xl flex items-center justify-center',
            notice?.type && typeStyles[notice.type] ? typeStyles[notice.type].bg : 'bg-slate-50 dark:bg-slate-900/20'
          ]"
        >
          <UIcon
            :name="notice?.icon || notice?.type_icon || 'i-lucide-info'"
            :class="['w-5 h-5', notice?.type && typeStyles[notice.type] ? typeStyles[notice.type].icon : 'text-slate-500']"
          />
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0 pr-6">
        <h3 class="font-semibold text-slate-900 dark:text-white">
          {{ notice?.title }}
        </h3>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
          {{ notice?.content }}
        </p>

        <!-- CTA Button -->
        <UButton
          v-if="notice?.cta_text"
          size="sm"
          :color="notice?.type && typeStyles[notice.type] ? typeStyles[notice.type].button : ('primary' as any)"
          variant="soft"
          class="mt-3"
          @click="handleCtaClick(notice)"
        >
          {{ notice?.cta_text }}
          <UIcon
            name="i-lucide-arrow-right"
            class="w-4 h-4 ml-1"
          />
        </UButton>
      </div>

      <!-- Image (if provided) -->
      <div
        v-if="notice.image_url"
        class="hidden sm:block shrink-0"
      >
        <img
          :src="notice.image_url"
          :alt="notice.title"
          class="w-20 h-20 rounded-lg object-cover"
        >
      </div>
    </div>
  </div>
</template>
