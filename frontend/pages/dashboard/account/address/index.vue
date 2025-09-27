<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-24 -right-32 w-72 h-72 bg-green-400/10 dark:bg-green-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-24 -left-32 w-80 h-80 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-50 animate-pulse"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 space-y-8">

      <!-- Header -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl overflow-hidden relative">
        <!-- Header Background -->
        <div class="absolute inset-0 bg-gradient-to-r from-green-500/5 to-emerald-500/5 opacity-50"></div>

        <div class="relative z-10">
          <!-- Breadcrumb -->
          <div class="flex items-center gap-2 text-sm mb-6">
            <NuxtLink to="/dashboard" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
              <Icon name="mdi:view-dashboard" class="w-4 h-4" />
              <span>Dashboard</span>
            </NuxtLink>
            <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
            <NuxtLink to="/dashboard/account" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
              <Icon name="mdi:account" class="w-4 h-4" />
              <span>Account</span>
            </NuxtLink>
            <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
            <span class="text-slate-700 dark:text-slate-300 font-medium">Addresses</span>
          </div>

          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <!-- Title Section -->
            <div class="flex items-start gap-4 flex-1">
              <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg">
                <Icon name="mdi:map-marker-multiple" class="w-8 h-8 text-white" />
              </div>
              <div class="flex-1">
                <h1 class="text-3xl font-bold bg-gradient-to-r from-slate-900 via-green-600 to-emerald-600 dark:from-white dark:via-green-400 dark:to-emerald-400 bg-clip-text text-transparent mb-2">
                  My Addresses
                </h1>
                <p class="text-slate-600 dark:text-slate-400 mb-6 leading-relaxed">Manage your saved shipping and billing addresses for faster checkout</p>

                <!-- Stats -->
                <div class="flex gap-6">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-xl flex items-center justify-center">
                      <Icon name="mdi:map-marker" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                      <div class="text-sm text-slate-500 dark:text-slate-400">Total Addresses</div>
                      <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ addresses.length }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Section -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 flex-shrink-0">
              <!-- Search -->
              <div class="relative">
                <Icon name="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" />
                <input
                    v-model="searchPincode"
                    type="text"
                    placeholder="Search by pincode..."
                    class="w-full sm:w-80 pl-10 pr-10 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                />
                <button
                    v-if="searchPincode"
                    @click="searchPincode = ''"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors"
                >
                  <Icon name="mdi:close" class="w-5 h-5" />
                </button>
              </div>

              <!-- Add Button -->
              <button
                  @click="openModal('create')"
                  class="group relative inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden"
              >
                <div class="flex items-center gap-2 relative z-10">
                  <Icon name="mdi:plus" class="w-5 h-5" />
                  <span>Add Address</span>
                </div>
                <!-- Shimmer Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div v-if="isLoading" class="flex items-center justify-center py-16">
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl">
          <div class="flex flex-col items-center gap-4">
            <div class="w-12 h-12 border-4 border-green-200 dark:border-green-800 border-t-green-600 dark:border-t-green-400 rounded-full animate-spin"></div>
            <p class="text-slate-600 dark:text-slate-400 font-semibold">Loading addresses...</p>
          </div>
        </div>
      </div>

      <div v-else-if="addresses.length === 0" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-12 shadow-xl text-center">
        <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
          <Icon name="mdi:map-marker-off" class="w-12 h-12 text-slate-400 dark:text-slate-500" />
        </div>

        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No addresses found</h3>
        <p class="text-slate-600 dark:text-slate-400 mb-8 max-w-md mx-auto leading-relaxed">Create your first address to get started with faster checkout</p>

        <button
            @click="openModal('create')"
            class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
        >
          <Icon name="mdi:plus" class="w-5 h-5" />
          <span>Add Your First Address</span>
        </button>
      </div>

      <div v-else>
        <!-- Desktop Table -->
        <div class="hidden lg:block bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
          <div class="grid grid-cols-5 gap-4 p-6 bg-slate-50/80 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-600 text-sm font-semibold text-slate-600 dark:text-slate-400">
            <div class="flex items-center gap-2 col-span-2">
              <Icon name="mdi:card-text" class="w-4 h-4" />
              <span>Address Details</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="mdi:tag" class="w-4 h-4" />
              <span>Type</span>
            </div>
            <div class="flex items-center gap-2">
              <Icon name="mdi:account" class="w-4 h-4" />
              <span>Contact Info</span>
            </div>
            <div class="flex items-center gap-2 justify-center">
              <Icon name="mdi:cog" class="w-4 h-4" />
              <span>Actions</span>
            </div>
          </div>

          <div class="divide-y divide-slate-200 dark:divide-slate-700">
            <div
                v-for="address in filteredAddresses"
                :key="address.uuid"
                class="grid grid-cols-5 gap-4 p-6 hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors"
            >
              <!-- Address Details -->
              <div class="col-span-2 min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white mb-1">{{ address.title }}</h4>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">{{ address.address_1 }}, {{ address.landmark }}</p>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ address.city }}, {{ address.state?.name || address.state }}, {{ address.postal_code }}, India</p>
              </div>

              <!-- Type -->
              <div class="flex items-start pt-1">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold" :class="getTypeBadgeClass(address.type)">
                  <Icon :name="getTypeIcon(address.type)" class="w-3 h-3" />
                  <span>{{ getTypeLabel(address.type) }}</span>
                </span>
              </div>

              <!-- Contact -->
              <div class="min-w-0">
                <p class="font-medium text-slate-900 dark:text-white text-sm mb-1">{{ address.person_name }}</p>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-1">{{ address.person_mobile }}</p>
                <p v-if="address.person_email" class="text-sm text-slate-500 dark:text-slate-400">{{ address.person_email }}</p>
              </div>

              <!-- Actions -->
              <div class="flex items-start justify-center gap-1 pt-1">
                <button
                    @click="openModal('view', address)"
                    class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-400 rounded-lg flex items-center justify-center transition-colors"
                    title="View Address"
                >
                  <Icon name="mdi:eye" class="w-4 h-4" />
                </button>
                <button
                    @click="openModal('edit', address)"
                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center transition-colors"
                    title="Edit Address"
                >
                  <Icon name="mdi:pencil" class="w-4 h-4" />
                </button>
                <button
                    @click="deleteAddress(address.uuid)"
                    class="w-8 h-8 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg flex items-center justify-center transition-colors"
                    title="Delete Address"
                >
                  <Icon name="mdi:delete" class="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Mobile Cards -->
        <div class="lg:hidden space-y-4">
          <div
              v-for="address in filteredAddresses"
              :key="address.uuid"
              class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 hover:-translate-y-1"
          >
            <!-- Card Header -->
            <div class="flex items-start justify-between p-6 border-b border-slate-200 dark:border-slate-700">
              <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white mb-2">{{ address.title }}</h4>
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold" :class="getTypeBadgeClass(address.type)">
                  <Icon :name="getTypeIcon(address.type)" class="w-3 h-3" />
                  <span>{{ getTypeLabel(address.type) }}</span>
                </span>
              </div>
              <div class="flex gap-1 flex-shrink-0 ml-4">
                <button
                    @click="openModal('view', address)"
                    class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-600 dark:text-slate-400 rounded-lg flex items-center justify-center transition-colors"
                >
                  <Icon name="mdi:eye" class="w-4 h-4" />
                </button>
                <button
                    @click="openModal('edit', address)"
                    class="w-8 h-8 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center transition-colors"
                >
                  <Icon name="mdi:pencil" class="w-4 h-4" />
                </button>
                <button
                    @click="deleteAddress(address.uuid)"
                    class="w-8 h-8 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-lg flex items-center justify-center transition-colors"
                >
                  <Icon name="mdi:delete" class="w-4 h-4" />
                </button>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 space-y-4">
              <!-- Address -->
              <div class="flex gap-3">
                <Icon name="mdi:map-marker" class="w-4 h-4 text-slate-500 mt-0.5 flex-shrink-0" />
                <div class="min-w-0">
                  <p class="text-sm text-slate-700 dark:text-slate-300 mb-1">{{ address.address_1 }}, {{ address.landmark }}</p>
                  <p class="text-sm text-slate-500 dark:text-slate-400">{{ address.city }}, {{ address.state?.name || address.state }}, {{ address.postal_code }}, India</p>
                </div>
              </div>

              <!-- Contact -->
              <div class="flex gap-3">
                <Icon name="mdi:account" class="w-4 h-4 text-slate-500 mt-0.5 flex-shrink-0" />
                <div class="min-w-0">
                  <p class="font-medium text-slate-900 dark:text-white text-sm">{{ address.person_name }}</p>
                  <div class="flex flex-col gap-1 mt-1">
                    <span class="text-sm text-slate-600 dark:text-slate-400">{{ address.person_mobile }}</span>
                    <span v-if="address.person_email" class="text-sm text-slate-500 dark:text-slate-400">{{ address.person_email }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Address Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeModal">
      <div class="w-full max-w-4xl mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" :class="getModalIconClass()">
              <Icon :name="getModalIcon()" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ getModalTitle() }}</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">{{ getModalSubtitle() }}</p>
            </div>
          </div>
          <button @click="closeModal" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors">
            <Icon name="mdi:close" class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <!-- Form Mode -->
          <form v-if="modalMode !== 'view'" @submit.prevent="saveAddress" class="space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Basic Information -->
              <div>
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                  <Icon name="mdi:information" class="w-5 h-5 text-blue-600" />
                  <h4 class="text-lg font-semibold text-slate-900 dark:text-white">Basic Information</h4>
                </div>

                <div class="space-y-4">
                  <!-- Title -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:card-text" class="w-4 h-4" />
                      <span>Address Title</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="currentAddress.title"
                        type="text"
                        placeholder="e.g. My Home, Office"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.title }"
                        @input="clearError('title')"
                    />
                    <div v-if="errors.title" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.title }}</span>
                    </div>
                  </div>

                  <!-- Type -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:tag" class="w-4 h-4" />
                      <span>Address Type</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <select
                        v-model="currentAddress.type"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.type }"
                    >
                      <option value="">Select address type</option>
                      <option v-for="(badge, key) in typeBadges" :key="key" :value="key">{{ badge.label }}</option>
                    </select>
                    <div v-if="errors.type" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.type }}</span>
                    </div>
                  </div>

                  <!-- Address Line 1 -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:home" class="w-4 h-4" />
                      <span>Street Address</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="currentAddress.address_1"
                        type="text"
                        placeholder="House number, building, street"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.address_1 }"
                        @input="clearError('address_1')"
                    />
                    <div v-if="errors.address_1" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.address_1 }}</span>
                    </div>
                  </div>

                  <!-- Landmark -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:map-marker" class="w-4 h-4" />
                      <span>Landmark</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="currentAddress.landmark"
                        type="text"
                        placeholder="e.g. Near hospital, opposite mall"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.landmark }"
                        @input="clearError('landmark')"
                    />
                    <div v-if="errors.landmark" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.landmark }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Contact & Location -->
              <div>
                <div class="flex items-center gap-2 mb-6 pb-4 border-b border-slate-200 dark:border-slate-700">
                  <Icon name="mdi:account-circle" class="w-5 h-5 text-green-600" />
                  <h4 class="text-lg font-semibold text-slate-900 dark:text-white">Contact & Location</h4>
                </div>

                <div class="space-y-4">
                  <!-- Contact Name -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:account" class="w-4 h-4" />
                      <span>Contact Name</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="currentAddress.person_name"
                        type="text"
                        placeholder="Full name"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.person_name }"
                        @input="clearError('person_name')"
                    />
                    <div v-if="errors.person_name" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.person_name }}</span>
                    </div>
                  </div>

                  <!-- Mobile & Email -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <Icon name="mdi:phone" class="w-4 h-4" />
                        <span>Mobile</span>
                        <span class="text-red-500">*</span>
                      </label>
                      <input
                          v-model="currentAddress.person_mobile"
                          type="tel"
                          placeholder="+91 99999 99999"
                          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                          :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.person_mobile }"
                          @input="clearError('person_mobile')"
                      />
                      <div v-if="errors.person_mobile" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                        <Icon name="mdi:alert-circle" class="w-4 h-4" />
                        <span>{{ errors.person_mobile }}</span>
                      </div>
                    </div>

                    <div>
                      <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <Icon name="mdi:email" class="w-4 h-4" />
                        <span>Email</span>
                      </label>
                      <input
                          v-model="currentAddress.person_email"
                          type="email"
                          placeholder="you@example.com"
                          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                      />
                    </div>
                  </div>

                  <!-- State & City -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <Icon name="mdi:map" class="w-4 h-4" />
                        <span>State</span>
                        <span class="text-red-500">*</span>
                      </label>
                      <select
                          v-model="currentAddress.state_code"
                          @change="() => { fetchBlocks(); clearError('state_code') }"
                          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                          :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.state_code }"
                      >
                        <option value="">Select your state</option>
                        <option v-for="state in stateList" :key="state.code" :value="state.code">{{ state.name }}</option>
                      </select>
                      <div v-if="errors.state_code" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                        <Icon name="mdi:alert-circle" class="w-4 h-4" />
                        <span>{{ errors.state_code }}</span>
                      </div>
                    </div>

                    <div>
                      <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <Icon name="mdi:city" class="w-4 h-4" />
                        <span>District / City</span>
                        <span class="text-red-500">*</span>
                      </label>
                      <select
                          v-model="currentAddress.city"
                          class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                          :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.city }"
                          @change="clearError('city')"
                      >
                        <option value="">Select district</option>
                        <option v-for="district in districtList" :key="district" :value="district">{{ district }}</option>
                      </select>
                      <div v-if="errors.city" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                        <Icon name="mdi:alert-circle" class="w-4 h-4" />
                        <span>{{ errors.city }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Pincode & Block -->
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                      <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <Icon name="mdi:mailbox" class="w-4 h-4" />
                        <span>Pincode</span>
                        <span class="text-red-500">*</span>
                      </label>
                      <div class="relative">
                        <input
                            v-model="currentAddress.postal_code"
                            type="text"
                            maxlength="6"
                            placeholder="6-digit pincode"
                            class="w-full px-4 py-3 pr-12 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.postal_code }"
                            @input="clearError('postal_code')"
                        />
                        <button
                            type="button"
                            @click="fetchAddressFromPostalCode"
                            class="absolute right-3 top-1/2 -translate-y-1/2 w-8 h-8 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center transition-colors"
                        >
                          <Icon name="mdi:map-search" class="w-4 h-4" />
                        </button>
                      </div>
                      <div v-if="errors.postal_code" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                        <Icon name="mdi:alert-circle" class="w-4 h-4" />
                        <span>{{ errors.postal_code }}</span>
                      </div>
                    </div>

                    <div>
                      <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                        <Icon name="mdi:office-building" class="w-4 h-4" />
                        <span>Block</span>
                        <span class="text-red-500">*</span>
                      </label>
                      <template v-if="blockList && blockList.length">
                        <select
                            v-model="currentAddress.block_id"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.block_id }"
                            @change="clearError('block_id')"
                        >
                          <option value="">Select block</option>
                          <option v-for="b in blockList" :key="b.url" :value="b.url">{{ b.name }}</option>
                        </select>
                      </template>
                      <template v-else>
                        <input
                            v-model="currentAddress.block_id"
                            type="text"
                            placeholder="Enter block name"
                            class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all"
                            :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.block_id }"
                            @input="clearError('block_id')"
                        />
                      </template>
                      <div v-if="errors.block_id" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                        <Icon name="mdi:alert-circle" class="w-4 h-4" />
                        <span>{{ errors.block_id }}</span>
                      </div>
                    </div>
                  </div>

                  <!-- Country -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:earth" class="w-4 h-4" />
                      <span>Country</span>
                    </label>
                    <input
                        type="text"
                        value="India"
                        disabled
                        class="w-full px-4 py-3 bg-slate-100 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-500 dark:text-slate-400 cursor-not-allowed"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
              <button
                  type="button"
                  @click="closeModal"
                  class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-semibold"
              >
                Cancel
              </button>
              <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 text-white rounded-xl transition-colors font-semibold"
              >
                <Icon name="mdi:content-save" class="w-4 h-4" />
                <span>{{ isSubmitting ? 'Saving...' : 'Save Address' }}</span>
              </button>
            </div>
          </form>

          <!-- View Mode -->
          <div v-else class="space-y-6">
            <div class="space-y-6">
              <!-- Address Details -->
              <div class="p-6 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                <h4 class="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white mb-4">
                  <Icon name="mdi:information" class="w-5 h-5" />
                  Address Details
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Title</span>
                    <div class="font-medium text-slate-900 dark:text-white">{{ currentAddress.title }}</div>
                  </div>
                  <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Type</span>
                    <div>
                      <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold" :class="getTypeBadgeClass(currentAddress.type)">
                        <Icon :name="getTypeIcon(currentAddress.type)" class="w-3 h-3" />
                        <span>{{ getTypeLabel(currentAddress.type) }}</span>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Full Address</span>
                  <div class="font-medium text-slate-900 dark:text-white">
                    {{ currentAddress.address_1 }}, {{ currentAddress.landmark }}, {{ currentAddress.city }}, {{ currentAddress.state_code }}, {{ currentAddress.postal_code }}, India
                  </div>
                </div>
              </div>

              <!-- Contact Information -->
              <div class="p-6 bg-slate-50 dark:bg-slate-700/50 rounded-xl border border-slate-200 dark:border-slate-600">
                <h4 class="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white mb-4">
                  <Icon name="mdi:account" class="w-5 h-5" />
                  Contact Information
                </h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Name</span>
                    <div class="font-medium text-slate-900 dark:text-white">{{ currentAddress.person_name }}</div>
                  </div>
                  <div>
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Mobile</span>
                    <div class="font-medium text-slate-900 dark:text-white">{{ currentAddress.person_mobile }}</div>
                  </div>
                  <div v-if="currentAddress.person_email" class="sm:col-span-2">
                    <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase">Email</span>
                    <div class="font-medium text-slate-900 dark:text-white">{{ currentAddress.person_email }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- View Actions -->
            <div class="flex gap-4 pt-6 border-t border-slate-200 dark:border-slate-700">
              <button
                  @click="closeModal"
                  class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-semibold"
              >
                Close
              </button>
              <button
                  @click="openModal('edit', currentAddress)"
                  class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl transition-colors font-semibold"
              >
                <Icon name="mdi:pencil" class="w-4 h-4" />
                <span>Edit Address</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteConfirm" class="fixed inset-0 z-60 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showDeleteConfirm = false">
      <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl">
        <div class="p-6 text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <Icon name="mdi:delete-alert" class="w-8 h-8 text-white" />
          </div>
          <h3 class="text-xl font-bold text-red-600 dark:text-red-400 mb-2">Delete Address</h3>
          <p class="text-slate-500 dark:text-slate-400 mb-6">This action cannot be undone</p>
          <p class="text-slate-600 dark:text-slate-400 mb-6">
            Are you sure you want to delete this address? This will permanently remove it from your saved addresses.
          </p>

          <div class="flex gap-3">
            <button
                @click="showDeleteConfirm = false"
                class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-semibold"
            >
              Cancel
            </button>
            <button
                @click="confirmDelete"
                class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-xl transition-colors font-semibold"
            >
              <Icon name="mdi:delete" class="w-4 h-4" />
              Delete Address
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
import { useSanctumFetch, useRuntimeConfig, useRoute } from '#imports'

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
const route = useRoute()
const { $toast } = useNuxtApp()

/* State */
const addresses = ref<any[]>([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const showModal = ref(false)
const showDeleteConfirm = ref(false)
const deleteAddressId = ref('')
const modalMode = ref<'create' | 'edit' | 'view'>('create')
const searchPincode = ref('')

/* Location data */
const stateList = ref<any[]>([])
const blockList = ref<Array<{ name: string; url: string }>>([])
const districtList = ref<string[]>([])

/* Refs */
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

/* Form state */
const currentAddress = reactive<any>({
  uuid: '',
  title: '',
  type: 'home',
  person_name: '',
  person_email: '',
  person_mobile: '',
  address_1: '',
  landmark: '',
  city: '',
  state_code: '',
  postal_code: '',
  block_id: '',
  village: '',
  country: 'IN',
})

const errors = reactive<Record<string, string>>({})

/* Type badges configuration */
const typeBadges: Record<string, { label: string; class: string; icon: string }> = {
  home: {
    label: 'HOME',
    class: 'bg-gradient-to-r from-green-500 to-emerald-600 text-white',
    icon: 'mdi:home'
  },
  work: {
    label: 'WORK',
    class: 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white',
    icon: 'mdi:office-building'
  },
  delivery: {
    label: 'DELIVERY',
    class: 'bg-gradient-to-r from-orange-500 to-amber-600 text-white',
    icon: 'mdi:truck-delivery'
  },
  other: {
    label: 'OTHER',
    class: 'bg-gradient-to-r from-slate-500 to-slate-600 text-white',
    icon: 'mdi:map-marker'
  },
}

/* Computed */
const filteredAddresses = computed(() => {
  if (!searchPincode.value) return addresses.value
  return addresses.value.filter(addr =>
      addr.postal_code?.toString().includes(searchPincode.value.trim())
  )
})

/* Helper functions */
function getTypeBadgeClass(type: string) {
  return typeBadges[type?.toLowerCase()]?.class || typeBadges.other.class
}

function getTypeLabel(type: string) {
  return typeBadges[type?.toLowerCase()]?.label || type?.toUpperCase()
}

function getTypeIcon(type: string) {
  return typeBadges[type?.toLowerCase()]?.icon || 'mdi:map-marker'
}

function getModalTitle() {
  const titles = {
    create: 'Add New Address',
    edit: 'Edit Address',
    view: 'Address Details'
  }
  return titles[modalMode.value]
}

function getModalSubtitle() {
  const subtitles = {
    create: 'Create a new address for faster checkout',
    edit: 'Update your address information',
    view: 'View complete address details'
  }
  return subtitles[modalMode.value]
}

function getModalIcon() {
  const icons = {
    create: 'mdi:plus-circle',
    edit: 'mdi:pencil-circle',
    view: 'mdi:eye-circle'
  }
  return icons[modalMode.value]
}

function getModalIconClass() {
  const classes = {
    create: 'bg-gradient-to-br from-green-500 to-emerald-600',
    edit: 'bg-gradient-to-br from-blue-500 to-indigo-600',
    view: 'bg-gradient-to-br from-purple-500 to-pink-600'
  }
  return classes[modalMode.value]
}

function clearError(field: string) {
  if (errors[field]) {
    delete errors[field]
  }
}

/* Validation functions */
function validateField(field: string) {
  const raw = (currentAddress[field] ?? '').toString().trim()
  clearError(field)

  switch (field) {
    case 'title':
      if (!raw) errors.title = 'Title is required.'
      break
    case 'type':
      if (!raw) errors.type = 'Please select an address type.'
      break
    case 'address_1':
      if (!raw) errors.address_1 = 'Street address is required.'
      else if (raw.length < 3) errors.address_1 = 'Please enter a valid street address.'
      break
    case 'landmark':
      if (!raw) errors.landmark = 'Landmark is required.'
      break
    case 'person_name':
      if (!raw) errors.person_name = 'Contact name is required.'
      break
    case 'person_mobile': {
      const digits = (currentAddress.person_mobile || '').toString().replace(/\D/g, '')
      if (!digits) errors.person_mobile = 'Mobile number is required.'
      else if (digits.length < 10) errors.person_mobile = 'Enter a valid mobile number (min 10 digits).'
      break
    }
    case 'state_code':
      if (!raw) errors.state_code = 'State is required.'
      break
    case 'city':
      if (!raw) errors.city = 'City / district is required.'
      break
    case 'postal_code':
      if (!raw) errors.postal_code = 'Pincode is required.'
      else if (!/^\d{6}$/.test(currentAddress.postal_code || '')) errors.postal_code = 'Pincode must be 6 digits.'
      break
    case 'block_id':
      if (!raw) errors.block_id = 'Block is required.'
      break
  }
}

function validateAll(): boolean {
  Object.keys(errors).forEach(k => delete errors[k])
  const requiredFields = ['title', 'type', 'address_1', 'landmark', 'person_name', 'person_mobile', 'state_code', 'city', 'postal_code', 'block_id']
  requiredFields.forEach(f => validateField(f))
  return Object.keys(errors).length === 0
}

/* API functions */
async function fetchStates() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/states/IN`)
    stateList.value = res.data
  } catch (error) {
    console.error('Failed to fetch states:', error)
  }
}

async function fetchBlocks() {
  if (!currentAddress.state_code) {
    blockList.value = []
    districtList.value = []
    return
  }
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/state/${currentAddress.state_code}`)
    const blocks = res.data.blocks || []

    blockList.value = blocks.map((b: any) => {
      const name = b.name || b.block_name || b.title || ''
      const url = b.url || b.href || (b._links && b._links.self && b._links.self.href) || b.slug || b.id || name
      return { name, url: String(url) }
    })

    districtList.value = [...new Set(blocks.map((b: any) => b.district).filter(Boolean))]
  } catch {
    blockList.value = []
    districtList.value = []
  }
}

async function fetchAddressFromPostalCode() {
  const code = currentAddress.postal_code
  if (!code || code.length !== 6) return

  try {
    const res = await $fetch(`https://api.postalpincode.in/pincode/${code}`)
    const data = res[0]
    if (data.Status !== 'Success') {
      if ($toast && $toast.error) {
        $toast.error('Error', 'Invalid postal code')
      }
      return
    }

    const po = data.PostOffice[0]
    currentAddress.city = po.District
    currentAddress.state_code = getStateCodeByName(po.State)
    currentAddress.village = po.Name
    currentAddress.block_id = ''

    await fetchBlocks()

    const poBlockName = (po.Block || '').trim()
    if (poBlockName && blockList.value && blockList.value.length) {
      const found = blockList.value.find(b => b.name.toLowerCase() === poBlockName.toLowerCase())
      if (found) currentAddress.block_id = found.url
      else currentAddress.block_id = poBlockName
    } else {
      currentAddress.block_id = poBlockName || ''
    }

    if ($toast && $toast.success) {
      $toast.success('Success', 'Address details auto-filled from pincode')
    }
  } catch {
    if ($toast && $toast.error) {
      $toast.error('Error', 'Failed to fetch address info')
    }
  }
}

function getStateCodeByName(name: string): string {
  const state = stateList.value.find((s) => s.name.toLowerCase() === name.toLowerCase())
  return state?.code || ''
}

async function loadAddresses() {
  isLoading.value = true
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/account/addresses`)
    addresses.value = res?.data || []
  } catch (error) {
    console.error('Failed to load addresses:', error)
    if ($toast && $toast.error) {
      $toast.error('Error', 'Failed to load addresses')
    }
  } finally {
    isLoading.value = false
  }
}

/* Modal functions */
function openModal(mode: 'create' | 'edit' | 'view', address: any | null = null) {
  modalMode.value = mode
  Object.keys(errors).forEach(k => delete errors[k])

  if (address) {
    Object.assign(currentAddress, {
      ...address,
      country: 'IN',
      state_code: address.state?.code || address.state_code || '',
      city: address.city || '',
      block_id: address.block?.url || address.block_id || address.block || '',
    })
  } else {
    Object.assign(currentAddress, {
      uuid: '',
      title: '',
      type: '',
      person_name: '',
      person_email: '',
      person_mobile: '',
      address_1: '',
      landmark: '',
      city: '',
      state_code: '',
      postal_code: '',
      block_id: '',
      village: '',
      country: 'IN',
    })

    // Auto-fill from query string only in create mode
    const queryType = (route.query.type || '').toString().toLowerCase()
    if (queryType && typeBadges[queryType]) {
      currentAddress.type = queryType
    }
  }

  showModal.value = true
}

function closeModal() {
  showModal.value = false
}

async function saveAddress() {
  if (!validateAll()) {
    return
  }

  isSubmitting.value = true
  try {
    const payload = { ...currentAddress }

    if (modalMode.value === 'edit') {
      await useSanctumFetch(`${config.public.apiBase}/account/addresses/${payload.uuid}`, {
        method: 'PUT',
        body: payload,
      })
      if ($toast && $toast.success) {
        $toast.success('Success', 'Address updated successfully')
      }
    } else {
      await useSanctumFetch(`${config.public.apiBase}/account/addresses`, {
        method: 'POST',
        body: payload,
      })
      if ($toast && $toast.success) {
        $toast.success('Success', 'Address created successfully')
      }
    }

    showModal.value = false
    await loadAddresses()
  } catch (error: any) {
    if ($toast && $toast.error) {
      $toast.error('Error', error?.data?.message || 'Failed to save address')
    }
  } finally {
    isSubmitting.value = false
  }
}

function deleteAddress(uuid: string) {
  deleteAddressId.value = uuid
  showDeleteConfirm.value = true
}

async function confirmDelete() {
  try {
    await useSanctumFetch(`${config.public.apiBase}/account/addresses/${deleteAddressId.value}`, {
      method: 'DELETE'
    })

    if ($toast && $toast.success) {
      $toast.success('Success', 'Address deleted successfully')
    }

    showDeleteConfirm.value = false
    deleteAddressId.value = ''
    await loadAddresses()
  } catch (error: any) {
    if ($toast && $toast.error) {
      $toast.error('Error', error?.data?.message || 'Failed to delete address')
    }
  }
}

/* Lifecycle */
onMounted(async () => {
  await Promise.all([
    fetchStates(),
    loadAddresses()
  ])

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
