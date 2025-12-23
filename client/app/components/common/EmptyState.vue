<script setup lang="ts">
/**
 * EmptyState - Placeholder for empty data sections
 * Shows helpful message and optional action button
 */

interface Props {
  icon?: string
  title: string
  description?: string
  actionLabel?: string
  actionTo?: string
  actionVariant?: 'solid' | 'outline' | 'soft' | 'ghost'
  size?: 'sm' | 'md' | 'lg'
}

const props = withDefaults(defineProps<Props>(), {
  icon: 'i-lucide-inbox',
  actionVariant: 'soft',
  size: 'md'
})

const emit = defineEmits<{
  action: []
}>()

const sizeClasses = computed(() => {
  const sizes = {
    sm: {
      container: 'py-6',
      icon: 'w-10 h-10',
      iconBox: 'w-16 h-16',
      title: 'text-sm',
      description: 'text-xs'
    },
    md: {
      container: 'py-10',
      icon: 'w-12 h-12',
      iconBox: 'w-20 h-20',
      title: 'text-base',
      description: 'text-sm'
    },
    lg: {
      container: 'py-16',
      icon: 'w-16 h-16',
      iconBox: 'w-24 h-24',
      title: 'text-lg',
      description: 'text-base'
    }
  }
  return sizes[props.size]
})

const handleAction = () => {
  if (props.actionTo) {
    navigateTo(props.actionTo)
  } else {
    emit('action')
  }
}
</script>

<template>
  <div :class="['flex flex-col items-center justify-center text-center', sizeClasses.container]">
    <!-- Icon -->
    <div
      :class="[
        'rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4',
        sizeClasses.iconBox
      ]"
    >
      <UIcon
        :name="icon"
        :class="['text-slate-400 dark:text-slate-500', sizeClasses.icon]"
      />
    </div>

    <!-- Title -->
    <h3 :class="['font-semibold text-slate-900 dark:text-white mb-1', sizeClasses.title]">
      {{ title }}
    </h3>

    <!-- Description -->
    <p
      v-if="description"
      :class="['text-slate-600 dark:text-slate-400 max-w-sm', sizeClasses.description]"
    >
      {{ description }}
    </p>

    <!-- Action Button -->
    <UButton
      v-if="actionLabel"
      :variant="actionVariant"
      class="mt-4"
      @click="handleAction"
    >
      {{ actionLabel }}
    </UButton>
  </div>
</template>
