<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">
    <!-- Header Section -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-40">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Community</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
              Total Members: <span class="font-semibold text-indigo-600 dark:text-indigo-400">{{ totalMembers }}</span>
            </p>
          </div>

          <!-- View Toggle -->
          <div class="flex items-center gap-2 bg-gray-100 dark:bg-gray-700 p-1 rounded-lg">
            <button @click="currentView = 'chart'" :class="viewButtonClass(currentView === 'chart')">
              <Icon name="heroicons:chart-bar" class="w-4 h-4 sm:mr-2" />
              <span class="hidden sm:inline">Chart</span>
            </button>
            <button @click="currentView = 'list'" :class="viewButtonClass(currentView === 'list')">
              <Icon name="heroicons:list-bullet" class="w-4 h-4 sm:mr-2" />
              <span class="hidden sm:inline">List</span>
            </button>

            <!-- NEW MAP BUTTON -->
            <button @click="currentView = 'map'" :class="viewButtonClass(currentView === 'map')">
              <Icon name="heroicons:map" class="w-4 h-4 sm:mr-2" />
              <span class="hidden sm:inline">Map</span>
            </button>


          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <div class="grid grid-cols-1 gap-6">

        <!-- Affiliate Link Card -->
        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg p-6 text-white">
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <h3 class="text-lg font-semibold mb-2">Your Referral Link</h3>
              <div class="flex items-center gap-2 bg-white/20 backdrop-blur rounded-lg p-3">
                <input :value="affiliateLink" readonly class="flex-1 bg-transparent border-none outline-none text-sm font-mono" />
                <button @click="copyLink" class="px-4 py-2 bg-white text-indigo-600 rounded-lg hover:bg-gray-100 transition text-sm font-semibold">
                  {{ copied ? 'Copied!' : 'Copy' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
          <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Total Members</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ totalMembers }}</p>
              </div>
              <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/30 rounded-full flex items-center justify-center">
                <Icon name="heroicons:users" class="w-6 h-6 text-indigo-600 dark:text-indigo-400" />
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Active</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1">{{ stats.active }}</p>
              </div>
              <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center">
                <Icon name="heroicons:check-circle" class="w-6 h-6 text-green-600 dark:text-green-400" />
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Max Depth</p>
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">{{ stats.maxDepth }}</p>
              </div>
              <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center">
                <Icon name="heroicons:chart-bar-square" class="w-6 h-6 text-purple-600 dark:text-purple-400" />
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm text-gray-600 dark:text-gray-400">Viewing</p>
                <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1 truncate">{{ currentUserName }}</p>
              </div>
              <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                <Icon name="heroicons:eye" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-4 border border-gray-200 dark:border-gray-700">
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">View Member's Network</label>
              <div class="flex gap-2">
                <input v-model="filterReferralCode" type="text" placeholder="Enter referral code..."
                       class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       @keyup.enter="applyFilter" />
                <button @click="applyFilter" :disabled="loading"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition disabled:opacity-50">
                  <Icon v-if="loading" name="heroicons:arrow-path" class="w-5 h-5 animate-spin" />
                  <span v-else>Apply</span>
                </button>
                <button v-if="currentReferralCode" @click="resetFilter"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-medium transition">
                  Reset
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart View -->
        <div v-if="currentView === 'chart'" class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Organization Chart</h3>
          </div>
          <div class="relative" style="min-height: 600px;">
            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-gray-50 dark:bg-gray-900">
              <Icon name="heroicons:arrow-path" class="w-8 h-8 text-indigo-600 animate-spin" />
            </div>
            <div ref="chartRef" id="chart-container" class="overflow-auto"></div>
          </div>
        </div>

        <!-- Hierarchical List View -->
        <div v-if="currentView === 'list'" class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Members List (Hierarchical)</h3>
          </div>

          <div v-if="loading" class="p-8 text-center">
            <Icon name="heroicons:arrow-path" class="w-8 h-8 text-indigo-600 animate-spin mx-auto" />
          </div>

          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gray-50 dark:bg-gray-900">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Member</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Depth</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
              </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <template v-for="member in hierarchicalData" :key="member.userId">
                <!-- Parent Row -->
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition" :class="{ 'bg-indigo-50 dark:bg-indigo-900/20': member.depth === 0 }">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center" :style="{ paddingLeft: (member.depth * 20) + 'px' }">
                      <!-- Expand/Collapse Button -->
                      <button v-if="member.hasChildren" @click.stop="toggleExpand(member.userId)"
                              class="mr-2 w-6 h-6 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                        <Icon v-if="expandedNodes.has(member.userId)" name="heroicons:chevron-down" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                        <Icon v-else name="heroicons:chevron-right" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                      </button>
                      <div v-else class="w-6 mr-2"></div>

                      <img :src="member.image || getDefaultAvatar(member.name)" class="w-10 h-10 rounded-full cursor-pointer" @click="selectMember(member)" />
                      <div class="ml-4 cursor-pointer" @click="selectMember(member)">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ member.name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ member.referral_code }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                        {{ member.level }}
                      </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                      <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="getDepthColorClass(member.depth)">
                        Level {{ member.depth }}
                      </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                      <span v-if="member.hasChildren" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                        {{ member.childrenCount || 0 }} Members
                      </span>
                    <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">
                        No Downline
                      </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                    <button @click.stop="selectMember(member)"
                            class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                      View Info
                    </button>
                    <button v-if="member.hasChildren" @click.stop="viewMemberNetwork(member.referral_code)"
                            class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 font-medium">
                      View Network
                    </button>
                  </td>
                </tr>

                <!-- Children Rows (if expanded) -->
                <template v-if="expandedNodes.has(member.userId) && member.children && member.children.length > 0">
                  <template v-for="child in member.children" :key="child.userId">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center" :style="{ paddingLeft: (child.depth * 20) + 'px' }">
                          <button v-if="child.hasChildren" @click.stop="toggleExpand(child.userId)"
                                  class="mr-2 w-6 h-6 flex items-center justify-center rounded hover:bg-gray-200 dark:hover:bg-gray-600 transition">
                            <Icon v-if="expandedNodes.has(child.userId)" name="heroicons:chevron-down" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                            <Icon v-else name="heroicons:chevron-right" class="w-4 h-4 text-gray-600 dark:text-gray-400" />
                          </button>
                          <div v-else class="w-6 mr-2"></div>

                          <img :src="child.image || getDefaultAvatar(child.name)" class="w-10 h-10 rounded-full cursor-pointer" @click="selectMember(child)" />
                          <div class="ml-4 cursor-pointer" @click="selectMember(child)">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ child.name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ child.referral_code }}</div>
                          </div>
                        </div>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                          <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                            {{ child.level }}
                          </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                          <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="getDepthColorClass(child.depth)">
                            Level {{ child.depth }}
                          </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                          <span v-if="child.hasChildren" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            {{ child.childrenCount || 0 }} Members
                          </span>
                        <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">
                            No Downline
                          </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                        <button @click.stop="selectMember(child)"
                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                          View Info
                        </button>
                        <button v-if="child.hasChildren" @click.stop="viewMemberNetwork(child.referral_code)"
                                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 font-medium">
                          View Network
                        </button>
                      </td>
                    </tr>

                    <!-- Recursively render grandchildren if expanded -->
                    <template v-if="expandedNodes.has(child.userId) && child.children && child.children.length > 0">
                      <tr v-for="grandchild in child.children" :key="grandchild.userId" class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center" :style="{ paddingLeft: (grandchild.depth * 20) + 'px' }">
                            <div class="w-6 mr-2"></div>
                            <img :src="grandchild.image || getDefaultAvatar(grandchild.name)" class="w-10 h-10 rounded-full cursor-pointer" @click="selectMember(grandchild)" />
                            <div class="ml-4 cursor-pointer" @click="selectMember(grandchild)">
                              <div class="text-sm font-medium text-gray-900 dark:text-white">{{ grandchild.name }}</div>
                              <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ grandchild.referral_code }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                              {{ grandchild.level }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium" :class="getDepthColorClass(grandchild.depth)">
                              Level {{ grandchild.depth }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span v-if="grandchild.hasChildren" class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                              {{ grandchild.childrenCount || 0 }} Members
                            </span>
                          <span v-else class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-400">
                              No Downline
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm space-x-2">
                          <button @click.stop="selectMember(grandchild)"
                                  class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">
                            View Info
                          </button>
                          <button v-if="grandchild.hasChildren" @click.stop="viewMemberNetwork(grandchild.referral_code)"
                                  class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300 font-medium">
                            View Network
                          </button>
                        </td>
                      </tr>
                    </template>
                  </template>
                </template>
              </template>
              </tbody>
            </table>
          </div>
        </div>

        <!-- NEW: Map View -->
        <div v-if="currentView === 'map'" class="bg-white dark:bg-gray-800 rounded-xl shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
          <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kingdom Map View</h3>
              <div class="flex items-center gap-2">
                <span class="text-xs text-gray-500 dark:text-gray-400">🎮 Interactive Game Scene</span>
              </div>
            </div>
          </div>
          <div class="relative" style="height: 800px;">
            <UserVillage
                :tree-data="chartData"
                @select-member="selectMember"
            />
          </div>
        </div>


      </div>
    </div>

    <!-- Right Drawer -->
    <transition name="drawer">
      <div v-if="drawerOpen" class="fixed inset-0 z-50 overflow-hidden" @click="closeDrawer">
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
        <div class="absolute inset-y-0 right-0 max-w-md w-full bg-white dark:bg-gray-800 shadow-xl flex flex-col" @click.stop>
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-semibold text-white">Member Details</h3>
              <button @click="closeDrawer" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/20 hover:bg-white/30 transition">
                <Icon name="heroicons:x-mark" class="w-5 h-5 text-white" />
              </button>
            </div>
          </div>

          <div v-if="selectedMember" class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="text-center">
              <img :src="selectedMember.image || getDefaultAvatar(selectedMember.name)"
                   class="w-24 h-24 rounded-full mx-auto border-4 border-white dark:border-gray-700 shadow-lg" />
              <h4 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ selectedMember.name }}</h4>
              <p class="text-sm text-gray-600 dark:text-gray-400 font-mono mt-1">{{ selectedMember.referral_code }}</p>
              <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm font-semibold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-400">
                {{ selectedMember.level }}
              </span>
            </div>

            <div class="space-y-4">
              <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Email</span>
                  <Icon name="heroicons:envelope" class="w-4 h-4 text-gray-400" />
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedMember.email || 'N/A' }}</p>
              </div>

              <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Joined On</span>
                  <Icon name="heroicons:calendar" class="w-4 h-4 text-gray-400" />
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ selectedMember.joinedOn }}</p>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-center">
                  <Icon name="heroicons:chart-bar-square" class="w-6 h-6 text-purple-600 dark:text-purple-400 mx-auto mb-2" />
                  <p class="text-xs text-gray-600 dark:text-gray-400">Network Depth</p>
                  <p class="text-lg font-bold text-gray-900 dark:text-white">Level {{ selectedMember.depth }}</p>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-4 text-center">
                  <Icon name="heroicons:users" class="w-6 h-6 text-green-600 dark:text-green-400 mx-auto mb-2" />
                  <p class="text-xs text-gray-600 dark:text-gray-400">Downline</p>
                  <p class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ selectedMember.hasChildren ? (selectedMember.childrenCount || 'Active') : 'None' }}
                  </p>
                </div>
              </div>
            </div>

            <div class="space-y-3">
              <button @click="viewMemberNetwork(selectedMember.referral_code)"
                      class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg font-medium transition">
                <Icon name="heroicons:chart-bar" class="w-5 h-5" />
                <span>View Their Network</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed, watch, nextTick } from 'vue'
import { useToast, useRuntimeConfig, useSanctumFetch, useSanctum } from '#imports'
import UserVillage from "~/components/games/UserVillage.vue";

definePageMeta({ layout: 'dashboard' })

const config = useRuntimeConfig()
const siteUrl = config.public.siteUrl
const toast = useToast()
const { user } = useSanctum()

const loading = ref(false)
const chartData = ref<any[]>([])
const currentView = ref('chart')
const selectedMember = ref<any>(null)
const drawerOpen = ref(false)
const chartRef = ref<HTMLElement | null>(null)
const filterReferralCode = ref('')
const currentReferralCode = ref('')
const copied = ref(false)
const expandedNodes = ref<Set<number>>(new Set())

let chartInstance: any = null

const totalMembers = computed(() => chartData.value.length)
const stats = computed(() => ({
  active: chartData.value.filter(m => m.hasChildren).length,
  maxDepth: Math.max(...chartData.value.map(m => m.depth), 0)
}))

const currentUserName = computed(() => {
  if (currentReferralCode.value) {
    const member = chartData.value.find(m => m.referral_code === currentReferralCode.value)
    return member?.name || 'Unknown'
  }
  return user.value?.name || 'You'
})

const affiliateLink = computed(() => {
  const baseUrl = config.public.siteUrl || 'https://yourapp.com'
  const code = user.value?.referral_code || 'XXXXX'
  return `${baseUrl}/register?ref=${code}`
})

// Build hierarchical structure
const hierarchicalData = computed(() => {
  if (!chartData.value.length) return []

  const buildTree = (parentId: number | null, depth: number = 0): any[] => {
    const children = chartData.value.filter(m => m.parentId === parentId)
    return children.map(child => ({
      ...child,
      children: buildTree(child.userId, depth + 1),
      childrenCount: chartData.value.filter(m => m.parentId === child.userId).length
    }))
  }

  // Get root node
  const root = chartData.value.find(m => m.parentId === null || m.depth === 0)
  if (!root) return []

  return [{
    ...root,
    children: buildTree(root.userId, 1),
    childrenCount: chartData.value.filter(m => m.parentId === root.userId).length
  }]
})

const viewButtonClass = (isActive: boolean) => {
  return isActive
      ? 'px-4 py-2 bg-white dark:bg-gray-600 text-indigo-600 dark:text-white rounded-md font-medium shadow-sm transition'
      : 'px-4 py-2 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white rounded-md font-medium transition'
}

const getDefaultAvatar = (name: string) => {
  return `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&size=80&background=6366F1&color=fff&bold=true`
}

const getDepthColorClass = (depth: number) => {
  const colors = [
    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
    'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
    'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-400',
    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'
  ]
  return colors[depth % colors.length]
}

const toggleExpand = (userId: number) => {
  if (expandedNodes.value.has(userId)) {
    expandedNodes.value.delete(userId)
  } else {
    expandedNodes.value.add(userId)
  }
}

const copyLink = async () => {
  try {
    await navigator.clipboard.writeText(affiliateLink.value)
    copied.value = true
    toast.success('Referral link copied!')
    setTimeout(() => (copied.value = false), 2000)
  } catch (e) {
    toast.error('Failed to copy link')
  }
}

const fetchTree = async (referralCode: string | null = null) => {
  try {
    loading.value = true
    const url = referralCode
        ? `${config.public.apiBase}/account/tree?referral_code=${referralCode}`
        : `${config.public.apiBase}/account/tree`

    const res: any = await useSanctumFetch(url, { method: 'GET' })

    if (res?.status && res?.data) {
      const parsedData = typeof res.data === 'string' ? JSON.parse(res.data) : res.data
      chartData.value = parsedData

      if (chartData.value.length > 0 && !selectedMember.value) {
        selectedMember.value = chartData.value[0]
        expandedNodes.value.add(chartData.value[0].userId) // Auto-expand root
      }

      await nextTick()
      if (currentView.value === 'chart') {
        initChart()
      }
    } else {
      toast.warning('No data found for this member')
      chartData.value = []
    }
  } catch (e: any) {
    console.error('Failed to fetch tree:', e)
    toast.error(e.message || 'Failed to load community data')
    chartData.value = []
  } finally {
    loading.value = false
  }
}

const applyFilter = () => {
  if (filterReferralCode.value.trim()) {
    currentReferralCode.value = filterReferralCode.value.trim()
    fetchTree(currentReferralCode.value)
  }
}

const resetFilter = () => {
  filterReferralCode.value = ''
  currentReferralCode.value = ''
  fetchTree()
}

const viewMemberNetwork = (referralCode: string) => {
  filterReferralCode.value = referralCode
  currentReferralCode.value = referralCode
  closeDrawer()
  fetchTree(referralCode)
}

const selectMember = (member: any) => {
  selectedMember.value = member
  drawerOpen.value = true
}

const closeDrawer = () => {
  drawerOpen.value = false
}

const initChart = async () => {
  if (!chartRef.value || chartData.value.length === 0) return

  const { OrgChart } = await import('d3-org-chart')
  const isDark = document.documentElement.classList.contains('dark')

  chartInstance = new OrgChart()
      .container(chartRef.value)
      .data(chartData.value)
      .nodeHeight(() => 120)
      .nodeWidth(() => 220)
      .childrenMargin(() => 50)
      .compactMarginBetween(() => 35)
      .compactMarginPair(() => 30)
      .neighbourMargin(() => 20)
      .nodeContent((d: any) => {
        const user = d.data
        const bg = isDark ? '#1F2937' : '#ffffff'
        const text = isDark ? '#F9FAFB' : '#111827'
        const border = isDark ? '#374151' : '#E5E7EB'

        return `
        <div style="width:${d.width}px;height:${d.height}px;padding:10px;font-family:system-ui;
                    border-radius:12px;border:1px solid ${border};background:${bg};color:${text};
                    box-shadow:0 2px 4px rgba(0,0,0,0.1);">
          <div style="display:flex;align-items:center;gap:10px;">
            <img src="${user.image || getDefaultAvatar(user.name)}"
                 style="border-radius:50%;width:50px;height:50px;object-fit:cover;border:2px solid ${border};" />
            <div style="flex:1;min-width:0;">
              <div style="font-weight:600;font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${user.name}</div>
              <div style="color:#9CA3AF;font-size:11px;margin-top:2px;">${user.level}</div>
              <div style="color:#9CA3AF;font-size:10px;font-family:monospace;margin-top:2px;">${user.referral_code}</div>
            </div>
          </div>
          ${user.depth > 0 ? `<div style="position:absolute;top:8px;right:8px;background:#EEF2FF;color:#4F46E5;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">L${user.depth}</div>` : ''}
        </div>
      `
      })
      .onNodeClick((d: any) => {
        selectMember(d.data)
      })

  await nextTick()
  chartInstance.render()
}

onMounted(async () => {
  await fetchTree()
})

watch(currentView, async (newVal) => {
  if (newVal === 'chart') {
    await nextTick()
    if (chartData.value.length > 0) {
      initChart()
    }
  }
})
</script>

<style scoped>
.drawer-enter-active,
.drawer-leave-active {
  transition: all 0.3s ease;
}

.drawer-enter-from,
.drawer-leave-to {
  opacity: 0;
}

.drawer-enter-from .absolute.inset-y-0,
.drawer-leave-to .absolute.inset-y-0 {
  transform: translateX(100%);
}

* {
  transition-property: background-color, border-color, color;
  transition-duration: 200ms;
  transition-timing-function: ease-in-out;
}
</style>
