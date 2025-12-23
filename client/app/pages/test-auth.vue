<template>
  <div class="p-8">
    <h1 class="text-2xl font-bold mb-4">
      Auth Debug
    </h1>

    <div class="space-y-4">
      <div>
        <h2 class="font-bold">
          Config:
        </h2>
        <pre class="bg-gray-100 p-4 rounded text-xs">{{ JSON.stringify(options, null, 2) }}</pre>
      </div>

      <div>
        <h2 class="font-bold">
          Is Logged In:
        </h2>
        <p>{{ isLoggedIn }}</p>
      </div>

      <div>
        <h2 class="font-bold">
          User:
        </h2>
        <pre class="bg-gray-100 p-4 rounded text-xs">{{ JSON.stringify(user, null, 2) }}</pre>
      </div>

      <div>
        <h2 class="font-bold">
          Cookies:
        </h2>
        <pre class="bg-gray-100 p-4 rounded text-xs">{{ cookies }}</pre>
      </div>

      <button
        class="bg-blue-500 text-white px-4 py-2 rounded"
        @click="testLogin"
      >
        Test Login
      </button>

      <button
        class="bg-green-500 text-white px-4 py-2 rounded ml-2"
        @click="testUser"
      >
        Test /api/user
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
const { login, user, isLoggedIn, options } = useSanctum()
const config = useRuntimeConfig()

const cookies = ref('')

onMounted(() => {
  if (import.meta.client) {
    cookies.value = document.cookie
  }
})

const testLogin = async () => {
  try {
    await login({
      email: 'regular@demo.com',
      password: 'password'
    })

    // Refresh cookies display
    if (import.meta.client) {
      cookies.value = document.cookie
    }

    alert('Login successful! Check console and cookies.')
    console.log('User after login:', user.value)
    console.log('Token cookie:', document.cookie)
  } catch (error) {
    console.error('Login error:', error)
    alert('Login failed: ' + JSON.stringify(error))
  }
}

const testUser = async () => {
  try {
    const response = await useSanctumFetch(`${config.public.apiBase}/api/user`)
    alert('User fetch successful!')
    console.log('User response:', response)
  } catch (error: unknown) {
    const err = error as Error
    console.error('User fetch error:', error)
    alert('User fetch failed: ' + (err.message || JSON.stringify(error)))
  }
}
</script>
