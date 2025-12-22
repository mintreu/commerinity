<script setup lang="ts">
import { ref } from 'vue'

const activeTab = ref<'shopping'|'team'|'business'|'system'>('shopping')

const tabs = [
  { key: 'shopping', label: '🛍 Shopping Rewards' },
  { key: 'team', label: '👥 Team Rewards' },
  { key: 'business', label: '🏢 Business Rewards' },
  { key: 'system', label: '⚡ System Rewards' }
]
</script>

<template>
  <div class="min-h-screen flex flex-col items-center py-10 mt-8">

    <!-- Tabs -->
    <div class="flex space-x-4 mb-10">
      <button
          v-for="tab in tabs"
          :key="tab.key"
          @click="activeTab = tab.key"
          class="px-6 py-3 rounded-full font-semibold transition-all duration-300 text-white"
          :class="activeTab === tab.key
          ? 'bg-indigo-600 text-white shadow-lg scale-105'
          : 'bg-gray-400 text-gray-400 hover:bg-gray-700 hover:text-white'"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Content Area -->
    <div class="w-full max-w-7xl rind-2 ring-gray-800 rounded-xl shadow-xl p-10 text-center">
      <div v-if="activeTab === 'shopping'">
        <h2 class="text-2xl font-bold mb-4">🛍 Shopping Rewards</h2>
        <p class="text-gray-400">List of shopping reward cards will show here...</p>
      </div>

      <div v-else-if="activeTab === 'team'">
        <h2 class="text-2xl font-bold mb-4">👥 Team Rewards</h2>
        <p class="text-gray-400">Team reward cards and details will appear here...</p>
      </div>

      <div v-else-if="activeTab === 'business'">
        <h2 class="text-2xl font-bold mb-4">🏢 Business Rewards</h2>
        <p class="text-gray-400">Business-related rewards will be listed here...</p>
      </div>

      <div v-else-if="activeTab === 'system'">
        <h2 class="text-2xl font-bold mb-4">⚡ System Rewards</h2>
        <p class="text-gray-400">System level rewards and milestones appear here...</p>
      </div>
    </div>

  </div>
</template>
