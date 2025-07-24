<template>
  <div class="px-4 py-6 space-y-6">
    <!-- Welcome Card -->
    <div class="bg-blue-50 dark:bg-blue-900 border border-blue-300 dark:border-blue-700 p-4 rounded shadow">
      <h3 class="text-lg font-semibold dark:text-white">Welcome, {{ loggedInUser.name }}</h3>
      <p class="text-sm text-gray-600 dark:text-gray-300">
        Your affiliate link:
        <span
            @click="copyAffiliateLink"
            class="text-blue-600 dark:text-blue-300 cursor-pointer underline ml-1"
        >
          {{ loggedInUser.affiliateLink }}
        </span>
        <span v-if="copied" class="ml-2 text-green-500">Copied!</span>
      </p>
    </div>

    <!-- Header & Tabs -->
    <div class="flex items-center justify-between border-b pb-2 dark:border-gray-600">
      <h2 class="text-xl font-semibold dark:text-white">
        Total Downline Members: {{ totalMembers }}
      </h2>
      <div class="flex space-x-2">
        <button
            @click="currentView = 'chart'"
            :class="tabClass(currentView === 'chart')"
        >
          Org Chart
        </button>
        <button
            @click="currentView = 'list'"
            :class="tabClass(currentView === 'list')"
        >
          List View
        </button>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Left Panel -->
      <div class="md:col-span-2 space-y-4">
        <!-- Org Chart -->
        <div
            v-if="currentView === 'chart'"
            class="overflow-auto border bg-white dark:bg-gray-800 rounded shadow-md h-[600px]"
        >
          <div ref="chartRef" id="chart-container" class="min-w-[800px]"></div>
        </div>

        <!-- List View -->
        <div
            v-if="currentView === 'list'"
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
        >
          <div
              v-for="member in chartData"
              :key="member.id"
              @click="selectMember(member)"
              class="cursor-pointer p-5 border rounded-lg hover:shadow-md transition-all bg-white dark:bg-gray-800 dark:text-white"
          >
            <div class="flex items-center gap-4">
              <img :src="member.image" class="w-14 h-14 rounded-full object-cover" />
              <div>
                <div class="font-semibold text-lg">{{ member.name }} {{ member.lastName }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ member.position }}</div>
                <div class="text-xs text-gray-400">{{ member.email }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Panel -->
      <div class="border rounded shadow bg-white dark:bg-gray-900 dark:text-white p-4 min-h-[250px]">
        <template v-if="selectedMember">
          <div class="flex items-center gap-4 mb-4">
            <img :src="selectedMember.image" class="w-16 h-16 rounded-full object-cover" />
            <div>
              <h3 class="text-lg font-semibold">
                {{ selectedMember.name }} {{ selectedMember.lastName }}
              </h3>
              <p class="text-sm text-gray-500 dark:text-gray-400">
                {{ selectedMember.position }}
              </p>
            </div>
          </div>
          <div class="text-sm space-y-1">
            <p><strong>Email:</strong> {{ selectedMember.email }}</p>
            <p><strong>ID:</strong> {{ selectedMember.id }}</p>
            <p><strong>Manager ID:</strong> {{ selectedMember.parentId || '—' }}</p>
          </div>
        </template>
        <template v-else>
          <p class="text-gray-500 dark:text-gray-400">Select a member to view details.</p>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
definePageMeta({
  layout: 'dashboard'
})
// Sample user
const loggedInUser = {
  name: 'John Doe',
  affiliateLink: 'https://yourapp.com/ref/johndoe'
}

const copied = ref(false)
const copyAffiliateLink = () => {
  navigator.clipboard.writeText(loggedInUser.affiliateLink)
  copied.value = true
  setTimeout(() => (copied.value = false), 2000)
}

// State
const currentView = ref('chart')
const chartRef = ref(null)
const chartData = ref([])
const totalMembers = ref(0)
const selectedMember = ref(null)
const lastCenteredId = ref(null)
const isDarkMode = ref(false)

const tabClass = (isActive) =>
    `px-4 py-2 rounded-md text-sm font-medium transition-colors ${
        isActive
            ? 'bg-blue-600 text-white'
            : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600'
    }`

const selectMember = (member) => {
  selectedMember.value = member
  lastCenteredId.value = member.id
  if (currentView.value === 'chart') {
    centerNodeById(member.id)
  }
}

const centerNodeById = async (id) => {
  await nextTick()
  if (chartInstance?.setCentered) {
    chartInstance.setCentered(id).render()
  }
}

let chartInstance = null

const initChart = async () => {
  if (!chartRef.value) return
  const { OrgChart } = await import('d3-org-chart')

  isDarkMode.value = document.documentElement.classList.contains('dark')

  chartInstance = new OrgChart()
      .container(chartRef.value)
      .data(chartData.value)
      .nodeHeight(() => 120)
      .nodeWidth(() => 240)
      .childrenMargin(() => 50)
      .compactMarginBetween(() => 35)
      .compactMarginPair(() => 30)
      .neighbourMargin(() => 20)
      .nodeContent((d) => {
        const user = d.data
        const bg = isDarkMode.value ? '#1F2937' : '#ffffff'
        const text = isDarkMode.value ? '#F9FAFB' : '#111827'
        const subText = isDarkMode.value ? '#9CA3AF' : '#6B7280'
        const border = isDarkMode.value ? '#374151' : '#D1D5DB'

        return `
        <div style="width:${d.width}px;height:${d.height}px;padding:10px;font-family:sans-serif;
                    border-radius:8px;border:1px solid ${border};background:${bg};color:${text}">
          <div style="display:flex;align-items:center;">
            <img src="${user.image}" style="border-radius:50%;width:50px;height:50px;margin-right:10px;" />
            <div>
              <div style="font-weight:bold;font-size:16px;">${user.name} ${user.lastName}</div>
              <div style="color:${subText};font-size:13px;">${user.position}</div>
              <div style="color:${subText};font-size:12px;">${user.email}</div>
            </div>
          </div>
        </div>`
      })
      .onNodeClick((d) => {
        selectMember(d.data)
      })

  await nextTick()
  chartInstance.render()

  if (lastCenteredId.value) {
    chartInstance.setCentered(lastCenteredId.value).render()
  }
}

onMounted(async () => {
  chartData.value = [
    {
      id: 100,
      parentId: null,
      name: 'Steven',
      lastName: 'King',
      position: 'Chief Operating Officer',
      image: 'https://bumbeishvili.github.io/avatars/avatars/portrait12.png',
      email: 'SKING',
    },
    {
      id: 101,
      parentId: 100,
      name: 'Neena',
      lastName: 'Kochhar',
      position: 'Administration VP',
      image: 'https://bumbeishvili.github.io/avatars/avatars/portrait85.png',
      email: 'NKOCHHAR',
    },
    {
      id: 102,
      parentId: 100,
      name: 'Lex',
      lastName: 'De Haan',
      position: 'Finance Manager',
      image: 'https://bumbeishvili.github.io/avatars/avatars/portrait7.png',
      email: 'LDEHAAN',
    }
  ]

  totalMembers.value = chartData.value.length
  selectedMember.value = chartData.value[0]
  lastCenteredId.value = selectedMember.value.id

  await nextTick()
  initChart()
})

// Re-render chart when returning to chart view
watch(currentView, async (newVal) => {
  if (newVal === 'chart') {
    await nextTick()
    isDarkMode.value = document.documentElement.classList.contains('dark')
    if (chartInstance) {
      chartInstance.container(chartRef.value)
      if (lastCenteredId.value) {
        chartInstance.setCentered(lastCenteredId.value)
      }
      chartInstance.render()
    } else {
      initChart()
    }
  }
})
</script>
