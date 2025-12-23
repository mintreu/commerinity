<template>
  <UContainer class="py-8">
    <UCard>
      <template #header>
        <h1 class="text-2xl font-bold">Create Support Ticket</h1>
      </template>

      <UForm :state="form" @submit="handleSubmit" class="space-y-6">
        <UFormGroup label="Title" required>
          <UInput v-model="form.title" placeholder="Brief summary of your issue" />
        </UFormGroup>

        <UFormGroup label="Description" required>
          <UTextarea v-model="form.description" :rows="6" placeholder="Detailed description..." />
        </UFormGroup>

        <UFormGroup label="Priority" required>
          <USelectMenu v-model="form.priority" :options="[{label:'Low',value:'low'},{label:'Medium',value:'medium'},{label:'High',value:'high'},{label:'Urgent',value:'urgent'}]" />
        </UFormGroup>

        <UFormGroup label="Topic" required>
          <USelectMenu v-model="form.topic_slug" :options="topics" option-attribute="name" value-attribute="slug" :loading="loadingTopics" />
        </UFormGroup>

        <UFormGroup label="Screenshot (Optional)">
          <UInput type="file" accept="image/*" @change="handleFile" />
          <p v-if="form.screenshot" class="text-sm text-gray-600 mt-2">{{ form.screenshot.name }}</p>
        </UFormGroup>

        <div class="flex gap-3">
          <UButton to="/helpdesk" variant="outline">Cancel</UButton>
          <UButton type="submit" :loading="submitting" color="primary">Create Ticket</UButton>
        </div>
      </UForm>
    </UCard>
  </UContainer>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth' })

const { fetchTopics, createTicket } = useHelpdesk()
const toast = useToast()
const router = useRouter()

const form = reactive({ title: '', description: '', priority: 'medium', topic_slug: '', screenshot: null })
const topics = ref([])
const loadingTopics = ref(false)
const submitting = ref(false)

const handleFile = (e: any) => {
  form.screenshot = e.target.files?.[0] || null
}

const handleSubmit = async () => {
  submitting.value = true
  try {
    const formData = new FormData()
    formData.append('title', form.title)
    formData.append('description', form.description)
    formData.append('priority', form.priority)
    formData.append('topic_slug', form.topic_slug)
    if (form.screenshot) formData.append('screenshot', form.screenshot)

    await createTicket(formData)
    toast.add({ title: 'Success', description: 'Ticket created successfully', color: 'green' })
    router.push('/helpdesk')
  } catch (e: any) {
    toast.add({ title: 'Error', description: e.data?.message || 'Failed to create ticket', color: 'red' })
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  loadingTopics.value = true
  topics.value = await fetchTopics()
  loadingTopics.value = false
})
</script>
