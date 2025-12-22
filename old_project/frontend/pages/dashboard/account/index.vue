<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-400/10 dark:bg-purple-400/5 rounded-full blur-3xl opacity-50 animate-pulse"></div>

      <!-- Floating Elements -->
      <div v-for="i in 8" :key="i" class="absolute animate-bounce opacity-20" :style="getFloatingElementStyle(i)">
        <div class="w-1 h-1 bg-blue-500/30 dark:bg-blue-400/20 rounded-full"></div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen relative z-10">
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl">
        <div class="flex flex-col items-center gap-4">
          <div class="w-16 h-16 border-4 border-blue-200 dark:border-blue-800 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
          <p class="text-slate-600 dark:text-slate-400 font-semibold">Loading your profile...</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

      <!-- Header -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden relative">
        <!-- Header Background -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 opacity-50"></div>

        <div class="relative z-10 p-6">
          <!-- Breadcrumb -->
          <div class="flex items-center gap-2 text-sm mb-6">
            <NuxtLink to="/dashboard" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
              <Icon name="mdi:view-dashboard" class="w-4 h-4" />
              <span>Dashboard</span>
            </NuxtLink>
            <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
            <span class="text-slate-700 dark:text-slate-300 font-medium">Account Settings</span>
          </div>

          <!-- Profile Header -->
          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div class="flex items-start gap-6">
              <!-- Avatar -->
              <div class="relative flex-shrink-0">
                <div class="w-24 h-24 sm:w-32 sm:h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg overflow-hidden">
                  <img
                      v-if="currentUser?.avatar"
                      :src="currentUser.avatar"
                      :alt="currentUser.name"
                      class="w-full h-full object-cover"
                  />
                  <div v-else class="text-white text-2xl sm:text-4xl font-bold">
                    {{ getInitials(currentUser?.name) }}
                  </div>
                </div>
                <button
                    @click="openAvatarModal"
                    class="absolute -bottom-2 -right-2 w-8 h-8 bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-600 rounded-full flex items-center justify-center text-slate-600 dark:text-slate-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors shadow-lg"
                >
                  <Icon name="mdi:camera" class="w-4 h-4" />
                </button>
              </div>

              <!-- User Info -->
              <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-purple-600 dark:from-white dark:via-blue-400 dark:to-purple-400 bg-clip-text text-transparent mb-2">
                  {{ currentUser?.name || 'User' }}
                </h1>
                <div class="flex items-center gap-2 text-slate-600 dark:text-slate-400 mb-3">
                  <Icon name="mdi:email" class="w-4 h-4" />
                  <span>{{ currentUser?.email }}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-500 dark:text-slate-400 text-sm mb-4">
                  <Icon name="mdi:calendar" class="w-4 h-4" />
                  <span>Member since {{ formatDate(currentUser?.created_at) }}</span>
                </div>

                <!-- Status & Verification -->
                <div class="flex items-center gap-3 flex-wrap">
                  <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-full text-sm font-semibold">
                    <Icon name="mdi:check-circle" class="w-3 h-3" />
                    Active
                  </span>
                  <span v-if="currentUser?.email_verified_at" class="inline-flex items-center gap-1 px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-full text-sm font-semibold">
                    <Icon name="mdi:shield-check" class="w-3 h-3" />
                    Verified
                  </span>
                  <span v-else class="inline-flex items-center gap-1 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-sm font-semibold">
                    <Icon name="mdi:shield-alert" class="w-3 h-3" />
                    Unverified
                  </span>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-3 flex-shrink-0">
              <button
                  @click="openEditModal"
                  class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
              >
                <Icon name="mdi:pencil" class="w-4 h-4" />
                <span>Edit Profile</span>
              </button>

              <button
                  @click="openSettingsModal"
                  class="group relative inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 overflow-hidden"
              >
                <div class="flex items-center gap-2 relative z-10">
                  <Icon name="mdi:cog" class="w-4 h-4" />
                  <span>Settings</span>
                </div>
                <!-- Shimmer Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Account Overview Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        <!-- Personal Information -->
        <div class="lg:col-span-2 space-y-8">

          <!-- Profile Details -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center">
                  <Icon name="mdi:account-circle" class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 class="text-lg font-bold text-slate-900 dark:text-white">Personal Information</h3>
                  <p class="text-sm text-slate-500 dark:text-slate-400">Your basic profile details</p>
                </div>
              </div>
              <button
                  @click="openEditModal"
                  class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center transition-colors"
              >
                <Icon name="mdi:pencil" class="w-4 h-4" />
              </button>
            </div>

            <div class="space-y-6">
              <!-- Bio Section -->
              <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-3">
                  <Icon name="mdi:text-box" class="w-4 h-4" />
                  <span>Personal Bio</span>
                </label>
                <div class="p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                  <p v-if="currentUser?.bio" class="text-slate-700 dark:text-slate-300 leading-relaxed">
                    {{ currentUser.bio }}
                  </p>
                  <p v-else class="text-slate-500 dark:text-slate-400 italic">
                    No bio added yet. Click edit to add a personal bio.
                  </p>
                </div>
              </div>

              <!-- Contact Information -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                  <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <Icon name="mdi:phone" class="w-4 h-4" />
                    <span>Phone Number</span>
                  </label>
                  <div class="text-slate-900 dark:text-white font-medium">
                    {{ currentUser?.phone || '—' }}
                  </div>
                </div>

                <div>
                  <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <Icon name="mdi:calendar-account" class="w-4 h-4" />
                    <span>Date of Birth</span>
                  </label>
                  <div class="text-slate-900 dark:text-white font-medium">
                    {{ currentUser?.date_of_birth ? formatDate(currentUser.date_of_birth) : '—' }}
                  </div>
                </div>

                <div>
                  <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <Icon name="mdi:gender-male-female" class="w-4 h-4" />
                    <span>Gender</span>
                  </label>
                  <div class="text-slate-900 dark:text-white font-medium capitalize">
                    {{ currentUser?.gender || '—' }}
                  </div>
                </div>

                <div>
                  <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <Icon name="mdi:map-marker" class="w-4 h-4" />
                    <span>Location</span>
                  </label>
                  <div class="text-slate-900 dark:text-white font-medium">
                    {{ currentUser?.location || '—' }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
              <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center">
                <Icon name="mdi:history" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Recent Activity</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Latest actions and updates</p>
              </div>
            </div>

            <div class="space-y-4">
              <div v-if="recentActivity.length === 0" class="text-center py-8">
                <Icon name="mdi:timeline-clock-outline" class="w-12 h-12 text-slate-300 dark:text-slate-600 mx-auto mb-3" />
                <p class="text-slate-500 dark:text-slate-400">No recent activity available</p>
                <p class="text-sm text-slate-400 dark:text-slate-500">Activities will appear here when you start using the platform</p>
              </div>

              <div v-for="activity in recentActivity" :key="activity.id" class="flex items-start gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :class="getActivityIconClass(activity.type)">
                  <Icon :name="getActivityIcon(activity.type)" class="w-4 h-4" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm text-slate-900 dark:text-white font-medium">{{ activity.title }}</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ activity.description }}</p>
                  <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">{{ formatTimeAgo(activity.created_at) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">

          <!-- Quick Actions -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
              <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center">
                <Icon name="mdi:flash" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Quick Actions</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Manage your account</p>
              </div>
            </div>

            <div class="space-y-3">
              <NuxtLink to="/dashboard/account/edit" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl transition-colors group">
                <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 group-hover:bg-blue-200 dark:group-hover:bg-blue-900/50 rounded-lg flex items-center justify-center transition-colors">
                  <Icon name="mdi:account-edit" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                </div>
                <div class="flex-1">
                  <p class="font-medium text-slate-900 dark:text-white text-sm">Edit Profile</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Update your information</p>
                </div>
                <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" />
              </NuxtLink>

              <NuxtLink to="/dashboard/account/security" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-xl transition-colors group">
                <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 group-hover:bg-green-200 dark:group-hover:bg-green-900/50 rounded-lg flex items-center justify-center transition-colors">
                  <Icon name="mdi:shield-key" class="w-4 h-4 text-green-600 dark:text-green-400" />
                </div>
                <div class="flex-1">
                  <p class="font-medium text-slate-900 dark:text-white text-sm">Security Settings</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Password & 2FA</p>
                </div>
                <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors" />
              </NuxtLink>

              <NuxtLink to="/dashboard/account/address" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-purple-50 dark:hover:bg-purple-900/20 rounded-xl transition-colors group">
                <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 group-hover:bg-purple-200 dark:group-hover:bg-purple-900/50 rounded-lg flex items-center justify-center transition-colors">
                  <Icon name="mdi:map-marker-multiple" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                </div>
                <div class="flex-1">
                  <p class="font-medium text-slate-900 dark:text-white text-sm">Addresses</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Manage locations</p>
                </div>
                <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-purple-600 dark:group-hover:text-purple-400 transition-colors" />
              </NuxtLink>

              <NuxtLink to="/dashboard/account/preferences" class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-xl transition-colors group">
                <div class="w-8 h-8 bg-amber-100 dark:bg-amber-900/30 group-hover:bg-amber-200 dark:group-hover:bg-amber-900/50 rounded-lg flex items-center justify-center transition-colors">
                  <Icon name="mdi:tune" class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                </div>
                <div class="flex-1">
                  <p class="font-medium text-slate-900 dark:text-white text-sm">Preferences</p>
                  <p class="text-xs text-slate-500 dark:text-slate-400">Notifications & more</p>
                </div>
                <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors" />
              </NuxtLink>
            </div>
          </div>

          <!-- Account Stats -->
          <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
              <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-cyan-600 rounded-xl flex items-center justify-center">
                <Icon name="mdi:chart-line" class="w-5 h-5 text-white" />
              </div>
              <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Account Stats</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400">Your activity summary</p>
              </div>
            </div>

            <div class="space-y-4">
              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center">
                    <Icon name="mdi:login" class="w-4 h-4 text-blue-600 dark:text-blue-400" />
                  </div>
                  <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Total Logins</span>
                </div>
                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ userStats.totalLogins || 0 }}</span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center">
                    <Icon name="mdi:clock-check" class="w-4 h-4 text-green-600 dark:text-green-400" />
                  </div>
                  <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Last Login</span>
                </div>
                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ formatTimeAgo(currentUser?.last_login_at) || '—' }}</span>
              </div>

              <div class="flex items-center justify-between p-3 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center">
                    <Icon name="mdi:calendar-check" class="w-4 h-4 text-purple-600 dark:text-purple-400" />
                  </div>
                  <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Member Since</span>
                </div>
                <span class="text-sm font-bold text-slate-900 dark:text-white">{{ formatDate(currentUser?.created_at) || '—' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Edit Profile Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeEditModal">
      <div class="w-full max-w-2xl mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <Icon name="mdi:account-edit" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-900 dark:text-white">Edit Profile</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">Update your personal information</p>
            </div>
          </div>
          <button @click="closeEditModal" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors">
            <Icon name="mdi:close" class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <form @submit.prevent="updateProfile" class="p-6 space-y-6">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Name -->
            <div class="sm:col-span-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:account" class="w-4 h-4" />
                <span>Full Name</span>
              </label>
              <input
                  v-model="editForm.name"
                  type="text"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="Your full name"
              />
            </div>

            <!-- Phone -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:phone" class="w-4 h-4" />
                <span>Phone Number</span>
              </label>
              <input
                  v-model="editForm.phone"
                  type="tel"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="+1 (555) 000-0000"
              />
            </div>

            <!-- Date of Birth -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:calendar-account" class="w-4 h-4" />
                <span>Date of Birth</span>
              </label>
              <input
                  v-model="editForm.date_of_birth"
                  type="date"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              />
            </div>

            <!-- Gender -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:gender-male-female" class="w-4 h-4" />
                <span>Gender</span>
              </label>
              <select
                  v-model="editForm.gender"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
              >
                <option value="">Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
                <option value="prefer_not_to_say">Prefer not to say</option>
              </select>
            </div>

            <!-- Location -->
            <div>
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:map-marker" class="w-4 h-4" />
                <span>Location</span>
              </label>
              <input
                  v-model="editForm.location"
                  type="text"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                  placeholder="City, Country"
              />
            </div>

            <!-- Bio -->
            <div class="sm:col-span-2">
              <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                <Icon name="mdi:text-box" class="w-4 h-4" />
                <span>Bio</span>
              </label>
              <textarea
                  v-model="editForm.bio"
                  rows="4"
                  class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                  placeholder="Tell us about yourself..."
              ></textarea>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
            <button
                type="button"
                @click="closeEditModal"
                class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-semibold"
            >
              Cancel
            </button>
            <button
                type="submit"
                :disabled="updating"
                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl transition-colors font-semibold"
            >
              <Icon name="mdi:content-save" class="w-4 h-4" />
              <span>{{ updating ? 'Updating...' : 'Update Profile' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useSanctum, useSanctumFetch, useRuntimeConfig } from '#imports'

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

definePageMeta({ layout: 'dashboard' })

/* Core */
const config = useRuntimeConfig()
const { $toast } = useNuxtApp()
const { user: currentUser, refreshUser } = useSanctum()

/* State */
const isLoading = ref(true)
const updating = ref(false)
const showEditModal = ref(false)

const recentActivity = ref<any[]>([])
const userStats = ref<any>({})

/* Refs */
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

/* Edit Form */
const editForm = reactive({
  name: '',
  phone: '',
  date_of_birth: '',
  gender: '',
  location: '',
  bio: ''
})

/* Helper Functions */
function getFloatingElementStyle(index: number) {
  const delay = index * 0.8
  const duration = 8 + (index % 4) * 2

  return {
    left: `${15 + (index * 15) % 70}%`,
    top: `${20 + (index * 12) % 60}%`,
    animationDelay: `${delay}s`,
    animationDuration: `${duration}s`
  }
}

function getInitials(name?: string) {
  if (!name) return 'U'
  return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)
}

function formatDate(dateString?: string) {
  if (!dateString) return '—'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function formatTimeAgo(dateString?: string) {
  if (!dateString) return '—'

  const date = new Date(dateString)
  const now = new Date()
  const diff = now.getTime() - date.getTime()

  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor(diff / (1000 * 60))

  if (days > 0) return `${days}d ago`
  if (hours > 0) return `${hours}h ago`
  if (minutes > 0) return `${minutes}m ago`
  return 'Just now'
}

function getActivityIcon(type: string) {
  const icons = {
    login: 'mdi:login',
    profile_update: 'mdi:account-edit',
    password_change: 'mdi:key-change',
    email_verify: 'mdi:email-check',
    address_add: 'mdi:map-marker-plus'
  }
  return icons[type as keyof typeof icons] || 'mdi:information'
}

function getActivityIconClass(type: string) {
  const classes = {
    login: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    profile_update: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    password_change: 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
    email_verify: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
    address_add: 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400'
  }
  return classes[type as keyof typeof classes] || 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'
}

/* Modal Functions */
function openEditModal() {
  // Populate form with current user data
  editForm.name = currentUser.value?.name || ''
  editForm.phone = currentUser.value?.phone || ''
  editForm.date_of_birth = currentUser.value?.date_of_birth || ''
  editForm.gender = currentUser.value?.gender || ''
  editForm.location = currentUser.value?.location || ''
  editForm.bio = currentUser.value?.bio || ''

  showEditModal.value = true
}

function closeEditModal() {
  showEditModal.value = false
}

function openAvatarModal() {
  // TODO: Implement avatar upload modal
  if ($toast) {
    $toast.info('Avatar Upload', 'Avatar upload feature coming soon!')
  }
}

function openSettingsModal() {
  // TODO: Implement settings modal or navigate to settings page
  navigateTo('/dashboard/account/settings')
}

/* API Functions */
async function loadUserData() {
  try {
    isLoading.value = true

    // Load user stats and activity
    const [statsRes, activityRes] = await Promise.allSettled([
      useSanctumFetch(`${config.public.apiBase}/account/stats`),
      useSanctumFetch(`${config.public.apiBase}/account/activity`)
    ])

    if (statsRes.status === 'fulfilled') {
      userStats.value = statsRes.value?.data || {}
    }

    if (activityRes.status === 'fulfilled') {
      recentActivity.value = activityRes.value?.data || []
    }
  } catch (error) {
    console.error('Failed to load user data:', error)
  } finally {
    isLoading.value = false
  }
}

async function updateProfile() {
  try {
    updating.value = true

    await useSanctumFetch(`${config.public.apiBase}/account/profile`, {
      method: 'PUT',
      body: editForm
    })

    await refreshUser()
    closeEditModal()

    if ($toast) {
      $toast.success('Profile Updated', 'Your profile has been updated successfully!')
    }
  } catch (error: any) {
    console.error('Profile update failed:', error)
    if ($toast) {
      $toast.error('Update Failed', error?.data?.message || 'Failed to update profile')
    }
  } finally {
    updating.value = false
  }
}

/* Lifecycle */
onMounted(async () => {
  await loadUserData()

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})
</script>
