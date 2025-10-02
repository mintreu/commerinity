<template>
  <!-- Global Loader until data populated -->
  <GlobalLoader v-if="isLoading" />

  <!-- Page Wrapper -->
  <div v-else class="min-h-screen w-full bg-gradient-to-br from-gray-50 via-blue-50/30 to-purple-50/30 dark:from-gray-950 dark:via-blue-950/30 dark:to-purple-950/30">

    <!-- Background Effects -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="pageOrb1" class="absolute -top-20 -right-20 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-purple-400/10 rounded-full blur-3xl opacity-60 animate-float"></div>
      <div ref="pageOrb2" class="absolute -bottom-20 -left-20 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 rounded-full blur-3xl opacity-70 animate-float-reverse"></div>
    </div>

    <div class="relative z-10 mx-auto w-full max-w-7xl px-3 sm:px-4 md:px-6 lg:px-8 py-6 md:py-10">

      <!-- ✅ Breadcrumb -->
      <nav class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mb-6 animate-slideInDown" aria-label="Breadcrumb">
        <NuxtLink to="/dashboard" class="flex items-center hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
          <Icon name="mdi:view-dashboard" class="w-4 h-4 mr-1" />
          Dashboard
        </NuxtLink>
        <Icon name="mdi:chevron-right" class="w-4 h-4" />
        <NuxtLink to="/dashboard/account" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200">
          Account
        </NuxtLink>
        <Icon name="mdi:chevron-right" class="w-4 h-4" />
        <span class="text-gray-900 dark:text-white font-semibold">KYC</span>
      </nav>

      <!-- Enhanced Card Shell -->
      <div ref="mainCard" class="w-full bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl rounded-2xl shadow-2xl ring-1 ring-gray-200/50 dark:ring-gray-700/50 transition-all duration-300 hover:shadow-3xl">

        <!-- Enhanced Header -->
        <div class="relative overflow-hidden rounded-t-2xl">
          <div class="absolute inset-0 bg-gradient-to-r from-blue-600/5 via-purple-600/5 to-pink-600/5 dark:from-blue-400/10 dark:via-purple-400/10 dark:to-pink-400/10"></div>

          <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 p-6 md:p-8 border-b border-gray-200/50 dark:border-gray-700/50">
            <div class="flex-1">
              <!-- Status Badge -->
              <div class="flex items-center gap-3 mb-3">
                <div class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider transition-all duration-200"
                     :class="hasExistingKyc
                       ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                       : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400'">
                  {{ hasExistingKyc ? 'KYC Active' : 'KYC Pending' }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400">
                  Verification Level: {{ hasExistingKyc ? 'Complete' : 'Incomplete' }}
                </div>
              </div>

              <h1 class="text-2xl sm:text-3xl font-black mb-2 bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 dark:from-white dark:via-blue-200 dark:to-purple-200 bg-clip-text text-transparent">
                {{ hasExistingKyc ? 'Update KYC Record' : 'Complete KYC Verification' }}
              </h1>
              <p class="text-gray-600 dark:text-gray-400 max-w-2xl">
                Secure your account with government-issued identification. Aadhaar and PAN with clear images are required.
                GST and utility bills are optional for enhanced verification.
              </p>
            </div>

            <!-- Enhanced Actions -->
            <div class="flex items-center gap-3">
              <button
                  v-if="hasExistingKyc && !isEditing"
                  type="button"
                  @click="enableEdit"
                  class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105"
                  aria-label="Edit KYC"
              >
                <Icon name="mdi:pencil" class="w-4 h-4" />
                Edit KYC
              </button>

              <template v-if="isEditing">
                <button
                    type="button"
                    @click="cancelEdit"
                    :disabled="processing"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg shadow-md transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Icon name="mdi:close" class="w-4 h-4" />
                  Cancel
                </button>
                <button
                    type="button"
                    @click="submitIfValid"
                    :disabled="processing"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Icon v-if="processing" name="mdi:loading" class="w-4 h-4 animate-spin" />
                  <Icon v-else name="mdi:check" class="w-4 h-4" />
                  <span>{{ processing ? 'Saving...' : 'Save Changes' }}</span>
                </button>
              </template>
            </div>
          </div>
        </div>

        <!-- ✅ NEW: Vertical Tabs Layout -->
        <div class="flex flex-col lg:flex-row min-h-[600px]">

          <!-- ✅ Vertical Sidebar Tabs -->
          <div class="w-full lg:w-80 bg-gradient-to-br from-gray-50/80 to-gray-100/80 dark:from-gray-800/80 dark:to-gray-900/80 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-700 backdrop-blur-sm">
            <div class="p-6">
              <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <Icon name="mdi:format-list-checks" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                KYC Sections
              </h3>

              <div class="space-y-3" role="tablist" aria-label="KYC sections">
                <button
                    v-for="t in tabs"
                    :key="t.key"
                    role="tab"
                    type="button"
                    :class="[
                      'w-full flex items-center gap-3 p-3 rounded-xl font-medium transition-all duration-200 text-left relative group',
                      activeTab === t.key
                        ? 'bg-gradient-to-r from-blue-500 to-purple-500 text-white shadow-lg scale-105'
                        : 'bg-white/50 dark:bg-gray-700/50 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-gray-600 hover:text-blue-700 dark:hover:text-blue-300 hover:scale-102'
                    ]"
                    :aria-selected="activeTab === t.key"
                    :aria-controls="`panel-${t.key}`"
                    :id="`tab-${t.key}`"
                    @click="activeTab = t.key"
                >
                  <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center transition-all duration-200"
                       :class="activeTab === t.key
                         ? 'bg-white/20 text-white'
                         : 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300 group-hover:bg-blue-100 dark:group-hover:bg-gray-500'">
                    <Icon :name="t.icon" class="w-5 h-5" />
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="font-semibold text-sm">{{ t.label }}</div>
                    <div class="text-xs opacity-75">{{ getTabDescription(t.key) }}</div>
                  </div>
                  <div v-if="getTabStatus(t.key)"
                       class="flex-shrink-0 w-5 h-5 rounded-full flex items-center justify-center transition-all duration-200"
                       :class="getTabStatus(t.key) === 'complete'
                         ? 'bg-green-500 text-white'
                         : 'bg-yellow-500 text-white'">
                    <Icon :name="getTabStatus(t.key) === 'complete' ? 'mdi:check' : 'mdi:alert'" class="w-3 h-3" />
                  </div>
                </button>
              </div>

              <!-- Progress Summary -->
              <div class="mt-6 p-4 bg-white/70 dark:bg-gray-700/70 rounded-xl border border-gray-200/50 dark:border-gray-600/50 backdrop-blur-sm">
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Progress</span>
                  <span class="text-sm font-bold text-gray-900 dark:text-white">{{ progressPercentage }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2 overflow-hidden">
                  <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 rounded-full transition-all duration-500 ease-out transform origin-left animate-progress"
                       :style="{ width: `${progressPercentage}%` }"></div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                  {{ completedSections }}/{{ tabs.length }} sections completed
                </p>
              </div>
            </div>
          </div>

          <!-- Main Form Content -->
          <div class="flex-1">
            <form @submit.prevent="handleSubmit" class="p-6 md:p-8 space-y-8" enctype="multipart/form-data" ref="kycForm">

              <!-- ✅ Aadhaar Section -->
              <section
                  v-show="activeTab === 'aadhaar'"
                  role="tabpanel"
                  :id="'panel-aadhaar'"
                  aria-labelledby="tab-aadhaar"
                  class="space-y-6 animate-fadeIn"
              >
                <div class="flex items-center gap-4 mb-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-xl border border-blue-200/50 dark:border-blue-700/50">
                  <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Icon name="mdi:card-account-details" class="w-6 h-6 text-white" />
                  </div>
                  <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Aadhaar Verification</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Government-issued unique identification</p>
                  </div>
                  <div class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 rounded-full text-xs font-bold uppercase tracking-wider">
                    Required
                  </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  <!-- ✅ Aadhaar Input with Masking -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Aadhaar Number</label>
                    <div class="relative">
                      <input
                          v-model="aadhaarDisplayValue"
                          @input="handleAadhaarInput"
                          @blur="handleAadhaarBlur"
                          @focus="handleAadhaarFocus"
                          maxlength="14"
                          inputmode="numeric"
                          placeholder="Enter 12-digit Aadhaar number"
                          :disabled="!isEditing"
                          :class="[
                            'w-full pl-4 pr-10 py-3 border rounded-xl font-medium transition-all duration-200 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500',
                            errors.aadhaar
                              ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20 text-red-900 dark:text-red-300'
                              : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
                            !isEditing && 'opacity-60 cursor-not-allowed'
                          ]"
                      />
                      <Icon name="mdi:card-account-details" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Enter without spaces or special characters</p>
                    <p v-if="errors.aadhaar" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      {{ errors.aadhaar }}
                    </p>
                    <p v-if="form.aadhaar && !errors.aadhaar" class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                      <Icon name="mdi:check-circle" class="w-4 h-4" />
                      Verified: {{ maskedAadhaar }}
                    </p>
                  </div>

                  <!-- ✅ Aadhaar Upload -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">Aadhaar Document</label>
                    <div
                        :class="[
                          'relative border-2 border-dashed rounded-xl p-6 transition-all duration-300 cursor-pointer',
                          aadhaarDragOver
                            ? 'border-blue-400 bg-blue-50 dark:bg-blue-900/20 scale-105'
                            : 'border-gray-300 dark:border-gray-600 hover:border-blue-400 dark:hover:border-blue-500',
                          !isEditing && 'opacity-50 cursor-not-allowed',
                          preview.aadhaar && 'bg-gray-50 dark:bg-gray-800'
                        ]"
                        @dragover.prevent="onDragOver('aadhaar')"
                        @dragleave.prevent="onDragLeave('aadhaar')"
                        @drop.prevent="onDrop($event, 'aadhaar')"
                    >
                      <input
                          class="sr-only"
                          id="aadhaar_file"
                          name="aadhaar_file"
                          type="file"
                          accept="image/*,application/pdf"
                          :disabled="!isEditing"
                          @change="onFileChange($event, 'aadhaar')"
                      />

                      <div v-if="!preview.aadhaar" class="text-center">
                        <label for="aadhaar_file" class="cursor-pointer">
                          <Icon name="mdi:cloud-upload" class="w-12 h-12 text-blue-500 dark:text-blue-400 mx-auto mb-4 animate-bounce" />
                          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Drop your file here or click to browse
                          </div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            JPG, PNG, PDF up to 10MB
                          </div>
                        </label>
                      </div>

                      <!-- Upload Progress -->
                      <div v-if="uploadProgress.aadhaar > 0 && uploadProgress.aadhaar < 100" class="absolute bottom-0 left-0 right-0 bg-gray-200 dark:bg-gray-600 rounded-b-xl overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-300 ease-out animate-pulse"
                             :style="{ width: uploadProgress.aadhaar + '%' }"></div>
                      </div>

                      <!-- Preview -->
                      <div v-if="preview.aadhaar" class="relative group">
                        <img :src="preview.aadhaar" class="w-full h-32 object-cover rounded-lg transition-transform duration-200 group-hover:scale-105" alt="Aadhaar preview" />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg flex items-center justify-center">
                          <button
                              type="button"
                              @click="removePreview('aadhaar')"
                              :disabled="!isEditing"
                              class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-full transition-all duration-200 transform hover:scale-110 disabled:opacity-50"
                          >
                            <Icon name="mdi:delete" class="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Ensure text is clear and readable</p>
                  </div>
                </div>
              </section>

              <!-- ✅ PAN Section -->
              <section
                  v-show="activeTab === 'pan'"
                  role="tabpanel"
                  :id="'panel-pan'"
                  aria-labelledby="tab-pan"
                  class="space-y-6 animate-fadeIn"
              >
                <div class="flex items-center gap-4 mb-6 p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-200/50 dark:border-purple-700/50">
                  <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Icon name="mdi:credit-card" class="w-6 h-6 text-white" />
                  </div>
                  <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">PAN Verification</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Permanent Account Number for tax purposes</p>
                  </div>
                  <div class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 rounded-full text-xs font-bold uppercase tracking-wider">
                    Required
                  </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                  <!-- ✅ PAN Input with Masking -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">PAN Number</label>
                    <div class="relative">
                      <input
                          v-model="panDisplayValue"
                          @input="handlePanInput"
                          @blur="handlePanBlur"
                          @focus="handlePanFocus"
                          maxlength="10"
                          placeholder="Enter PAN (e.g. ABCDE1234F)"
                          :disabled="!isEditing"
                          style="text-transform: uppercase"
                          :class="[
                            'w-full pl-4 pr-10 py-3 border rounded-xl font-medium transition-all duration-200 focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 uppercase',
                            errors.pan
                              ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20 text-red-900 dark:text-red-300'
                              : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
                            !isEditing && 'opacity-60 cursor-not-allowed'
                          ]"
                      />
                      <Icon name="mdi:credit-card" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">10 characters: 5 letters + 4 digits + 1 letter</p>
                    <p v-if="errors.pan" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      {{ errors.pan }}
                    </p>
                    <p v-if="form.pan && !errors.pan" class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                      <Icon name="mdi:check-circle" class="w-4 h-4" />
                      Verified: {{ maskedPan }}
                    </p>
                  </div>

                  <!-- PAN Upload -->
                  <div class="space-y-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">PAN Document</label>
                    <div
                        :class="[
                          'relative border-2 border-dashed rounded-xl p-6 transition-all duration-300 cursor-pointer',
                          panDragOver
                            ? 'border-purple-400 bg-purple-50 dark:bg-purple-900/20 scale-105'
                            : 'border-gray-300 dark:border-gray-600 hover:border-purple-400 dark:hover:border-purple-500',
                          !isEditing && 'opacity-50 cursor-not-allowed',
                          preview.pan && 'bg-gray-50 dark:bg-gray-800'
                        ]"
                        @dragover.prevent="onDragOver('pan')"
                        @dragleave.prevent="onDragLeave('pan')"
                        @drop.prevent="onDrop($event, 'pan')"
                    >
                      <input
                          class="sr-only"
                          id="pan_file"
                          name="pan_file"
                          type="file"
                          accept="image/*,application/pdf"
                          :disabled="!isEditing"
                          @change="onFileChange($event, 'pan')"
                      />

                      <div v-if="!preview.pan" class="text-center">
                        <label for="pan_file" class="cursor-pointer">
                          <Icon name="mdi:cloud-upload" class="w-12 h-12 text-purple-500 dark:text-purple-400 mx-auto mb-4 animate-bounce" />
                          <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Drop your file here or click to browse
                          </div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            JPG, PNG, PDF up to 10MB
                          </div>
                        </label>
                      </div>

                      <!-- Upload Progress -->
                      <div v-if="uploadProgress.pan > 0 && uploadProgress.pan < 100" class="absolute bottom-0 left-0 right-0 bg-gray-200 dark:bg-gray-600 rounded-b-xl overflow-hidden">
                        <div class="h-1 bg-gradient-to-r from-purple-500 to-pink-500 transition-all duration-300 ease-out animate-pulse"
                             :style="{ width: uploadProgress.pan + '%' }"></div>
                      </div>

                      <!-- Preview -->
                      <div v-if="preview.pan" class="relative group">
                        <img :src="preview.pan" class="w-full h-32 object-cover rounded-lg transition-transform duration-200 group-hover:scale-105" alt="PAN preview" />
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg flex items-center justify-center">
                          <button
                              type="button"
                              @click="removePreview('pan')"
                              :disabled="!isEditing"
                              class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-full transition-all duration-200 transform hover:scale-110 disabled:opacity-50"
                          >
                            <Icon name="mdi:delete" class="w-4 h-4" />
                          </button>
                        </div>
                      </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Ensure card details are clearly visible</p>
                  </div>
                </div>
              </section>

              <!-- ✅ GST Section -->
              <section
                  v-show="activeTab === 'gst'"
                  role="tabpanel"
                  :id="'panel-gst'"
                  aria-labelledby="tab-gst"
                  class="space-y-6 animate-fadeIn"
              >
                <div class="flex items-center gap-4 mb-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-xl border border-green-200/50 dark:border-green-700/50">
                  <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Icon name="mdi:receipt" class="w-6 h-6 text-white" />
                  </div>
                  <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">GST Registration</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Goods and Services Tax registration</p>
                  </div>
                  <div class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider">
                    Optional
                  </div>
                </div>

                <!-- GST Toggle -->
                <div class="p-4 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl border border-gray-200/50 dark:border-gray-700/50">
                  <label class="flex items-center gap-4 cursor-pointer">
                    <div class="relative">
                      <input
                          type="checkbox"
                          v-model="form.hasTax"
                          class="sr-only peer"
                          :disabled="!isEditing"
                      />
                      <div class="w-14 h-8 bg-gray-200 dark:bg-gray-600 rounded-full transition-all duration-200 peer-checked:bg-gradient-to-r peer-checked:from-blue-500 peer-checked:to-purple-500 peer-checked:shadow-lg">
                        <div class="absolute top-1 left-1 w-6 h-6 bg-white rounded-full shadow-md transition-transform duration-200 peer-checked:translate-x-6"></div>
                      </div>
                    </div>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">This business has GST registration</span>
                  </label>
                </div>

                <!-- GST Fields (when enabled) -->
                <Transition
                    enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="opacity-0 transform scale-95"
                    enter-to-class="opacity-100 transform scale-100"
                    leave-active-class="transition-all duration-200 ease-in"
                    leave-from-class="opacity-100 transform scale-100"
                    leave-to-class="opacity-0 transform scale-95"
                >
                  <div v-if="form.hasTax" class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- ✅ GST Input with Masking -->
                    <div class="space-y-2">
                      <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">GSTIN Number</label>
                      <div class="relative">
                        <input
                            v-model="gstDisplayValue"
                            @input="handleGstInput"
                            @blur="handleGstBlur"
                            @focus="handleGstFocus"
                            maxlength="15"
                            placeholder="Enter 15-character GSTIN"
                            :disabled="!isEditing"
                            style="text-transform: uppercase"
                            :class="[
                              'w-full pl-4 pr-10 py-3 border rounded-xl font-medium transition-all duration-200 focus:ring-2 focus:ring-green-500/20 focus:border-green-500 uppercase',
                              errors.gst
                                ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20 text-red-900 dark:text-red-300'
                                : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
                              !isEditing && 'opacity-60 cursor-not-allowed'
                            ]"
                        />
                        <Icon name="mdi:receipt" class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" />
                      </div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">15 alphanumeric characters</p>
                      <p v-if="errors.gst" class="text-sm text-red-600 dark:text-red-400 flex items-center gap-1">
                        <Icon name="mdi:alert-circle" class="w-4 h-4" />
                        {{ errors.gst }}
                      </p>
                      <p v-if="form.gst && !errors.gst" class="text-sm text-green-600 dark:text-green-400 flex items-center gap-1">
                        <Icon name="mdi:check-circle" class="w-4 h-4" />
                        Verified: {{ maskedGst }}
                      </p>
                    </div>

                    <!-- GST Upload -->
                    <div class="space-y-2">
                      <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">GST Certificate</label>
                      <div
                          :class="[
                            'relative border-2 border-dashed rounded-xl p-6 transition-all duration-300 cursor-pointer',
                            gstDragOver
                              ? 'border-green-400 bg-green-50 dark:bg-green-900/20 scale-105'
                              : 'border-gray-300 dark:border-gray-600 hover:border-green-400 dark:hover:border-green-500',
                            !isEditing && 'opacity-50 cursor-not-allowed',
                            preview.gst && 'bg-gray-50 dark:bg-gray-800'
                          ]"
                          @dragover.prevent="onDragOver('gst')"
                          @dragleave.prevent="onDragLeave('gst')"
                          @drop.prevent="onDrop($event, 'gst')"
                      >
                        <input
                            class="sr-only"
                            id="gst_file"
                            name="gst_file"
                            type="file"
                            accept="image/*,application/pdf"
                            :disabled="!isEditing"
                            @change="onFileChange($event, 'gst')"
                        />

                        <div v-if="!preview.gst" class="text-center">
                          <label for="gst_file" class="cursor-pointer">
                            <Icon name="mdi:cloud-upload" class="w-12 h-12 text-green-500 dark:text-green-400 mx-auto mb-4 animate-bounce" />
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                              Drop your file here or click to browse
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                              JPG, PNG, PDF up to 10MB
                            </div>
                          </label>
                        </div>

                        <!-- Upload Progress -->
                        <div v-if="uploadProgress.gst > 0 && uploadProgress.gst < 100" class="absolute bottom-0 left-0 right-0 bg-gray-200 dark:bg-gray-600 rounded-b-xl overflow-hidden">
                          <div class="h-1 bg-gradient-to-r from-green-500 to-emerald-500 transition-all duration-300 ease-out animate-pulse"
                               :style="{ width: uploadProgress.gst + '%' }"></div>
                        </div>

                        <!-- Preview -->
                        <div v-if="preview.gst" class="relative group">
                          <img :src="preview.gst" class="w-full h-32 object-cover rounded-lg transition-transform duration-200 group-hover:scale-105" alt="GST preview" />
                          <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity duration-200 rounded-lg flex items-center justify-center">
                            <button
                                type="button"
                                @click="removePreview('gst')"
                                :disabled="!isEditing"
                                class="p-2 bg-red-500 hover:bg-red-600 text-white rounded-full transition-all duration-200 transform hover:scale-110 disabled:opacity-50"
                            >
                              <Icon name="mdi:delete" class="w-4 h-4" />
                            </button>
                          </div>
                        </div>
                      </div>
                      <p class="text-xs text-gray-500 dark:text-gray-400">Upload GST registration certificate</p>
                    </div>
                  </div>
                </Transition>
              </section>

              <!-- ✅ Utilities Section -->
              <section
                  v-show="activeTab === 'utilities'"
                  role="tabpanel"
                  :id="'panel-utilities'"
                  aria-labelledby="tab-utilities"
                  class="space-y-6 animate-fadeIn"
              >
                <div class="flex items-center gap-4 mb-6 p-4 bg-gradient-to-r from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 rounded-xl border border-orange-200/50 dark:border-orange-700/50">
                  <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                    <Icon name="mdi:home-lightning-bolt" class="w-6 h-6 text-white" />
                  </div>
                  <div class="flex-1">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Utility Bills</h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Additional verification documents</p>
                  </div>
                  <div class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 rounded-full text-xs font-bold uppercase tracking-wider">
                    Optional
                  </div>
                </div>

                <div class="space-y-4">
                  <!-- Utility Rows -->
                  <TransitionGroup
                      name="list"
                      tag="div"
                      class="space-y-4"
                  >
                    <div
                        v-for="(row, idx) in form.utility_bills"
                        :key="row.id"
                        class="p-4 bg-white dark:bg-gray-700 rounded-xl border border-gray-200 dark:border-gray-600 shadow-sm transition-all duration-200 hover:shadow-md"
                    >
                      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                          <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Utility Type</label>
                          <select
                              v-model="row.type"
                              @change="onUtilityTypeChange(idx)"
                              :disabled="!isEditing"
                              :class="[
                                'w-full px-3 py-2 border rounded-lg text-sm font-medium transition-all duration-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500',
                                errors[`utility_${idx}`]
                                  ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20'
                                  : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
                                !isEditing && 'opacity-60 cursor-not-allowed'
                              ]"
                          >
                            <option value="">Select type</option>
                            <option v-for="opt in availableUtilityOptions(idx)" :key="opt.value" :value="opt.value">
                              {{ opt.label }}
                            </option>
                          </select>
                        </div>

                        <div>
                          <label class="block text-xs font-semibold text-gray-600 dark:text-gray-400 mb-1">Account/Consumer Number</label>
                          <input
                              v-model="row.identifier"
                              type="text"
                              placeholder="Enter account number"
                              :disabled="!isEditing"
                              :class="[
                                'w-full px-3 py-2 border rounded-lg text-sm font-medium transition-all duration-200 focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500',
                                errors[`utility_${idx}`]
                                  ? 'border-red-300 dark:border-red-600 bg-red-50 dark:bg-red-900/20'
                                  : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white',
                                !isEditing && 'opacity-60 cursor-not-allowed'
                              ]"
                          />
                        </div>

                        <div class="flex items-end">
                          <button
                              type="button"
                              @click="removeUtility(idx)"
                              :disabled="!isEditing"
                              class="w-full md:w-auto px-4 py-2 bg-gradient-to-r from-red-500 to-pink-500 hover:from-red-600 hover:to-pink-600 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                          >
                            <Icon name="mdi:delete" class="w-4 h-4" />
                            Remove
                          </button>
                        </div>
                      </div>
                      <p v-if="errors[`utility_${idx}`]" class="text-sm text-red-600 dark:text-red-400 mt-2 flex items-center gap-1">
                        <Icon name="mdi:alert-circle" class="w-4 h-4" />
                        {{ errors[`utility_${idx}`] }}
                      </p>
                    </div>
                  </TransitionGroup>

                  <!-- Add Utility Button -->
                  <div class="text-center">
                    <button
                        type="button"
                        @click="addUtility"
                        :disabled="!isEditing || form.utility_bills.length >= utilityOptions.length"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-red-500 hover:from-orange-600 hover:to-red-600 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                    >
                      <Icon name="mdi:plus" class="w-5 h-5" />
                      Add Utility Bill
                    </button>
                    <p v-if="utilityAddHint" class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ utilityAddHint }}</p>
                  </div>
                </div>
              </section>

              <!-- ✅ Form Actions -->
              <div class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4 pt-8 border-t border-gray-200/50 dark:border-gray-700/50">
                <div class="text-center md:text-left">
                  <p class="text-sm text-gray-600 dark:text-gray-400 flex items-center justify-center md:justify-start gap-1">
                    <Icon name="mdi:information" class="w-4 h-4" />
                    Aadhaar and PAN documents are mandatory for verification
                  </p>
                  <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    GST and utility bills are optional and enhance your verification level
                  </p>
                </div>

                <button
                    type="submit"
                    :disabled="processing || !isEditing"
                    class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 hover:from-blue-700 hover:via-purple-700 hover:to-pink-700 text-white font-bold text-lg rounded-xl shadow-2xl hover:shadow-3xl transition-all duration-300 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                >
                  <Icon v-if="processing" name="mdi:loading" class="w-6 h-6 animate-spin" />
                  <Icon v-else name="mdi:shield-check" class="w-6 h-6" />
                  <span>{{ processing ? 'Submitting...' : 'Submit KYC' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Create KYC Modal -->
    <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        enter-to-class="opacity-100 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 scale-100"
        leave-to-class="opacity-0 scale-95"
    >
      <div v-if="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click="createModalOpen = false">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 transform transition-all duration-300" @click.stop>
          <div class="flex items-center gap-4 p-6 border-b border-gray-200 dark:border-gray-700">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center shadow-lg animate-pulse">
              <Icon name="mdi:shield-plus" class="w-8 h-8 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-gray-900 dark:text-white">Complete KYC Verification</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">Secure your account with identity verification</p>
            </div>
          </div>

          <div class="p-6">
            <div class="space-y-4">
              <div class="flex items-center gap-3 text-sm">
                <Icon name="mdi:shield-check" class="w-5 h-5 text-green-600" />
                <span class="text-gray-700 dark:text-gray-300">Enhanced account security</span>
              </div>
              <div class="flex items-center gap-3 text-sm">
                <Icon name="mdi:bank" class="w-5 h-5 text-blue-600" />
                <span class="text-gray-700 dark:text-gray-300">Access to all financial features</span>
              </div>
              <div class="flex items-center gap-3 text-sm">
                <Icon name="mdi:account-check" class="w-5 h-5 text-purple-600" />
                <span class="text-gray-700 dark:text-gray-300">Verified account badge</span>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-3 p-6 bg-gray-50 dark:bg-gray-700/50 rounded-b-2xl">
            <button
                type="button"
                @click="createModalOpen = false"
                class="flex-1 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors duration-200"
            >
              Maybe Later
            </button>
            <button
                type="button"
                @click="goToCreateForm"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 text-white font-semibold rounded-lg transition-all duration-200 transform hover:scale-105"
            >
              <Icon name="mdi:shield-plus" class="w-4 h-4" />
              Start Verification
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, toRaw, onBeforeUnmount } from 'vue'
import { useSanctumFetch, useRuntimeConfig } from '#imports'
import GlobalLoader from '~/components/GlobalLoader.vue'

definePageMeta({ layout: 'dashboard' })

/* GSAP imports (client-side only) */
let gsap: any = null

if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

// ✅ Use proper toast system
const toast = useToast()

/* Loading */
const isLoading = useState('pageLoading', () => true)

/* Core state */
const config = useRuntimeConfig()
const processing = ref(false)
const isEditing = ref(false)
const hasExistingKyc = ref(false)
const createModalOpen = ref(false)

/* Simple ID counter for utility bills */
let utilityIdCounter = 1

/* Refs for animations */
const pageOrb1 = ref<HTMLElement>()
const pageOrb2 = ref<HTMLElement>()
const mainCard = ref<HTMLElement>()
const kycForm = ref<HTMLElement>()

let gsapContext: any = null

/* Tabs */
const tabs = [
  { key: 'aadhaar', label: 'Aadhaar', icon: 'mdi:card-account-details' },
  { key: 'pan', label: 'PAN', icon: 'mdi:credit-card' },
  { key: 'gst', label: 'GST', icon: 'mdi:receipt' },
  { key: 'utilities', label: 'Utilities', icon: 'mdi:home-lightning-bolt' }
]
const activeTab = ref<'aadhaar'|'pan'|'gst'|'utilities'>('aadhaar')

/* Options */
const utilityOptions = [
  { value: 'electricity', label: 'Electricity Bill' },
  { value: 'gas', label: 'Gas Bill' },
  { value: 'telephone', label: 'Telephone / Landline' },
  { value: 'water', label: 'Water Bill' },
  { value: 'other', label: 'Other Utility' }
]

/* Form */
const form = reactive({
  aadhaar: '',
  pan: '',
  aadhaarFile: null as File | null,
  panFile: null as File | null,
  hasTax: false,
  gst: '',
  gstFile: null as File | null,
  utility_bills: [] as Array<{ id: number, type: string, identifier: string }>,
})

/* ✅ Display values for inputs (with masking) */
const aadhaarDisplayValue = ref('')
const panDisplayValue = ref('')
const gstDisplayValue = ref('')
const isAadhaarFocused = ref(false)
const isPanFocused = ref(false)
const isGstFocused = ref(false)

/* Snapshot */
let initialSnapshot: any = null

/* Previews */
const preview = reactive({ aadhaar: '', pan: '', gst: '' })
function safeRevoke(url?: string) {
  try {
    if (url && url.startsWith('blob:')) URL.revokeObjectURL(url)
  } catch {}
}
onBeforeUnmount(() => {
  safeRevoke(preview.aadhaar)
  safeRevoke(preview.pan)
  safeRevoke(preview.gst)
})

/* Upload progress per field */
const uploadProgress = reactive({ aadhaar: 0, pan: 0, gst: 0 })
const aadhaarDragOver = ref(false)
const panDragOver = ref(false)
const gstDragOver = ref(false)

/* ✅ Input Masking Functions */
function formatAadhaar(value: string): string {
  const digits = value.replace(/\D/g, '').slice(0, 12)
  if (digits.length <= 4) return digits
  if (digits.length <= 8) return `${digits.slice(0, 4)}-${digits.slice(4)}`
  return `${digits.slice(0, 4)}-${digits.slice(4, 8)}-${digits.slice(8)}`
}

function handleAadhaarInput(e: Event) {
  const target = e.target as HTMLInputElement
  const rawValue = target.value.replace(/\D/g, '')
  form.aadhaar = rawValue
  aadhaarDisplayValue.value = isAadhaarFocused.value ? rawValue : formatAadhaar(rawValue)
  clearError('aadhaar')
}

function handleAadhaarFocus() {
  isAadhaarFocused.value = true
  aadhaarDisplayValue.value = form.aadhaar
}

function handleAadhaarBlur() {
  isAadhaarFocused.value = false
  aadhaarDisplayValue.value = formatAadhaar(form.aadhaar)
}

function handlePanInput(e: Event) {
  const target = e.target as HTMLInputElement
  const value = target.value.toUpperCase().slice(0, 10)
  form.pan = value
  panDisplayValue.value = value
  clearError('pan')
}

function handlePanFocus() {
  isPanFocused.value = true
  panDisplayValue.value = form.pan
}

function handlePanBlur() {
  isPanFocused.value = false
  panDisplayValue.value = form.pan
}

function handleGstInput(e: Event) {
  const target = e.target as HTMLInputElement
  const value = target.value.toUpperCase().slice(0, 15)
  form.gst = value
  gstDisplayValue.value = value
  clearError('gst')
}

function handleGstFocus() {
  isGstFocused.value = true
  gstDisplayValue.value = form.gst
}

function handleGstBlur() {
  isGstFocused.value = false
  gstDisplayValue.value = form.gst
}

/* Drag & drop handlers */
function onDragOver(type: 'aadhaar'|'pan'|'gst') {
  if (!isEditing.value) return
  if (type === 'aadhaar') aadhaarDragOver.value = true
  if (type === 'pan') panDragOver.value = true
  if (type === 'gst') gstDragOver.value = true
}

function onDragLeave(type: 'aadhaar'|'pan'|'gst') {
  if (type === 'aadhaar') aadhaarDragOver.value = false
  if (type === 'pan') panDragOver.value = false
  if (type === 'gst') gstDragOver.value = false
}

function onDrop(e: DragEvent, type: 'aadhaar'|'pan'|'gst') {
  if (!isEditing.value) return
  const file = e.dataTransfer?.files?.[0] ?? null
  if (!file) return
  handleFileAssign(type, file)
  onDragLeave(type)
}

/* File selection */
function onFileChange(e: Event, type: 'aadhaar'|'pan'|'gst') {
  const target = e.target as HTMLInputElement
  const file = target.files?.[0] ?? null
  if (!file) return
  handleFileAssign(type, file)
}

/* Assign file, create preview URL, simulate upload progress bar */
function handleFileAssign(type: 'aadhaar'|'pan'|'gst', file: File) {
  // Attach file
  if (type === 'aadhaar') form.aadhaarFile = file
  if (type === 'pan') form.panFile = file
  if (type === 'gst') form.gstFile = file

  // Preview for images (PDF won't preview as img)
  const isImage = file.type.startsWith('image/')
  const url = isImage ? URL.createObjectURL(file) : ''
  // Revoke previous
  safeRevoke(preview[type])
  preview[type] = url

  // Simulate progress locally
  uploadProgress[type] = 0
  const step = () => {
    if (uploadProgress[type] >= 100) return
    uploadProgress[type] += 10
    setTimeout(step, 60)
  }
  step()
}

/* Remove preview */
function removePreview(type: 'aadhaar'|'pan'|'gst') {
  if (!isEditing.value) return
  safeRevoke(preview[type])
  preview[type] = ''
  if (type === 'aadhaar') form.aadhaarFile = null
  if (type === 'pan') form.panFile = null
  if (type === 'gst') form.gstFile = null
  uploadProgress[type] = 0
}

/* Errors */
const errors = reactive<Record<string, string>>({})
function clearError(field: string) {
  errors[field] = ''
}

/* Utilities helpers */
function availableUtilityOptions(index: number) {
  const used = form.utility_bills.map((b, i) => (i === index ? null : b.type)).filter(Boolean) as string[]
  return utilityOptions.filter(o => !used.includes(o.value))
}

const utilityAddHint = computed(() => {
  const left = utilityOptions.length - form.utility_bills.length
  return left <= 0 ? 'All utility types added.' : `${left} types remaining`
})

function addUtility() {
  if (form.utility_bills.length < utilityOptions.length && isEditing.value) {
    form.utility_bills.push({ id: utilityIdCounter++, type: '', identifier: '' })
  }
}

function removeUtility(idx: number) {
  if (!isEditing.value) return
  form.utility_bills.splice(idx, 1)
  delete errors[`utility_${idx}`]
}

function onUtilityTypeChange(idx: number) {
  const seen = new Set<string>()
  for (let i = 0; i < form.utility_bills.length; i++) {
    const t = form.utility_bills[i].type
    if (!t) continue
    if (seen.has(t) && i !== idx) {
      form.utility_bills[i].type = ''
      form.utility_bills[i].identifier = ''
    } else {
      seen.add(t)
    }
  }
  clearError(`utility_${idx}`)
}

/* Load existing */
async function loadExisting() {
  try {
    isLoading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/account/kyc`)
    const k = res?.data ?? null
    hasExistingKyc.value = !!k

    if (k) {
      // Load existing data
      form.aadhaar = (k.aadhaar || '').replace(/\D/g, '')
      form.pan = (k.pan || '').toUpperCase()
      form.hasTax = !!k.has_tax
      form.gst = (k.gst || '').toUpperCase()

      // Set display values
      aadhaarDisplayValue.value = formatAadhaar(form.aadhaar)
      panDisplayValue.value = form.pan
      gstDisplayValue.value = form.gst

      if (k.aadhaarImage) {
        safeRevoke(preview.aadhaar)
        preview.aadhaar = k.aadhaarImage
      }
      if (k.panImage) {
        safeRevoke(preview.pan)
        preview.pan = k.panImage
      }
      if (k.gstImage) {
        safeRevoke(preview.gst)
        preview.gst = k.gstImage
      }
      if (Array.isArray(k.utility_bills)) {
        form.utility_bills = k.utility_bills.map((b: any, index: number) => ({
          id: index + 1,
          type: b.type || '',
          identifier: b.value || ''
        }))
        utilityIdCounter = form.utility_bills.length + 1
      }
      initialSnapshot = deepCloneForm()
    } else {
      isEditing.value = true
      createModalOpen.value = true
    }
  } catch (e) {
    console.error('loadExisting KYC error', e)
    toast.error('Load Error', 'Failed to load existing KYC data')
  } finally {
    isLoading.value = false
  }
}

/* Masking */
const maskedAadhaar = computed(() => {
  const v = (form.aadhaar || '').replace(/\D/g, '')
  return v.length === 12 ? `${v.slice(0,4)}-${'•'.repeat(4)}-${v.slice(8)}` : (v || '—')
})

const maskedPan = computed(() => {
  const v = (form.pan || '').toUpperCase().trim()
  return v.length === 10 ? `${v.slice(0,3)}${'•'.repeat(4)}${v.slice(7)}` : (v || '—')
})

const maskedGst = computed(() => {
  const v = (form.gst || '').toUpperCase().trim()
  return v.length === 15 ? `${v.slice(0,4)}${'•'.repeat(7)}${v.slice(11)}` : (v || '—')
})

/* ✅ Progress tracking */
const completedSections = computed(() => {
  let completed = 0
  if (form.aadhaar && preview.aadhaar) completed++
  if (form.pan && preview.pan) completed++
  if (!form.hasTax || (form.gst && preview.gst)) completed++
  if (form.utility_bills.length > 0) completed++
  return completed
})

const progressPercentage = computed(() => {
  return Math.round((completedSections.value / tabs.length) * 100)
})

/* Validation */
function validateAadhaar() {
  const v = (form.aadhaar || '').trim()
  if (!v) {
    errors.aadhaar = 'Aadhaar is required.'
    return false
  }
  if (!/^\d{12}$/.test(v)) {
    errors.aadhaar = 'Aadhaar must be 12 digits.'
    return false
  }
  errors.aadhaar = ''
  return true
}

function validatePan() {
  const v = (form.pan || '').toUpperCase().trim()
  if (!v) {
    errors.pan = 'PAN is required.'
    return false
  }
  if (!/^[A-Z]{5}[0-9]{4}[A-Z]$/.test(v)) {
    errors.pan = 'PAN format invalid (e.g. ABCDE1234F).'
    return false
  }
  errors.pan = ''
  return true
}

function validateGst() {
  if (!form.hasTax) {
    errors.gst = ''
    return true
  }
  const v = (form.gst || '').toUpperCase().trim()
  if (!v) {
    errors.gst = 'GST number is required when GST is enabled.'
    return false
  }
  if (!/^[0-9A-Z]{15}$/.test(v)) {
    errors.gst = 'GST must be 15 alphanumeric characters.'
    return false
  }
  errors.gst = ''
  return true
}

function validateUtilities() {
  let ok = true
  const used = new Set<string>()
  form.utility_bills.forEach((row, i) => {
    if (!row.type && !row.identifier) {
      errors[`utility_${i}`] = ''
      return
    }
    if (!row.type) {
      errors[`utility_${i}`] = 'Select utility type or remove this row.'
      ok = false
      return
    }
    if (!row.identifier || !row.identifier.trim()) {
      errors[`utility_${i}`] = 'Enter identifier / account number.'
      ok = false
      return
    }
    if (used.has(row.type)) {
      errors[`utility_${i}`] = 'This type is already chosen.'
      ok = false
      return
    }
    used.add(row.type)
    errors[`utility_${i}`] = ''
  })
  return ok
}

function validateImages() {
  if (!form.aadhaarFile && !preview.aadhaar) {
    errors.aadhaar = (errors.aadhaar || '') + ' Image required.'
    return false
  }
  if (!form.panFile && !preview.pan) {
    errors.pan = (errors.pan || '') + ' Image required.'
    return false
  }
  return true
}

function validateAll() {
  const a = validateAadhaar()
  const p = validatePan()
  const g = validateGst()
  const u = validateUtilities()
  const i = validateImages()
  return a && p && g && u && i
}

/* UI helpers */
function getTabStatus(tabKey: string): 'complete' | 'incomplete' | null {
  switch (tabKey) {
    case 'aadhaar':
      return form.aadhaar && preview.aadhaar ? 'complete' : 'incomplete'
    case 'pan':
      return form.pan && preview.pan ? 'complete' : 'incomplete'
    case 'gst':
      return !form.hasTax ? null : (form.gst && preview.gst ? 'complete' : 'incomplete')
    case 'utilities':
      return form.utility_bills.length > 0 ? 'complete' : null
    default:
      return null
  }
}

function getTabDescription(tabKey: string) {
  const descriptions = {
    aadhaar: 'Required document',
    pan: 'Required document',
    gst: 'Optional registration',
    utilities: 'Optional verification'
  }
  return descriptions[tabKey] || ''
}

/* Edit controls */
function enableEdit() {
  isEditing.value = true
  Object.keys(errors).forEach(k => delete errors[k])
  toast.info('Edit Mode Enabled', 'You can now modify your KYC information')
}

function cancelEdit() {
  if (initialSnapshot) applySnapshot(initialSnapshot)
  form.aadhaarFile = null
  form.panFile = null
  form.gstFile = null
  isEditing.value = false
  toast.info('Changes Cancelled', 'All changes have been reverted')
}

function submitIfValid() {
  handleSubmit()
}

/* Submit */
async function handleSubmit() {
  if (!isEditing.value) return
  if (!validateAll()) {
    toast.error('Validation Error', 'Please fix the highlighted errors before submitting.')
    return
  }

  processing.value = true
  try {
    const payload = new FormData()
    payload.append('aadhaar', form.aadhaar || '')
    payload.append('pan', form.pan || '')
    payload.append('has_tax', form.hasTax ? '1' : '0')
    if (form.hasTax && form.gst) payload.append('gst', form.gst)
    if (form.aadhaarFile instanceof File) payload.append('aadhaar_file', form.aadhaarFile)
    if (form.panFile instanceof File) payload.append('pan_file', form.panFile)
    if (form.gstFile instanceof File) payload.append('gst_file', form.gstFile)

    const utilityPayload = form.utility_bills
        .filter(b => b.type && b.identifier && b.identifier.trim())
        .map(b => ({ type: b.type, value: b.identifier.trim() }))
    payload.append('utility_bills', JSON.stringify(utilityPayload))

    const isUpdate = hasExistingKyc.value
    if (isUpdate) {
      payload.append('_method', 'PUT')
    }

    const url = `${config.public.apiBase}/account/kyc`
    const res = await useSanctumFetch(url, {
      method: 'POST',
      body: payload,
      headers: {
        'Accept': 'application/json',
      }
    })

    const ok = res?.data?.success || res?.success || (res?.status === 'ok') || res?.message?.includes('success')
    if (ok) {
      toast.success('Success!', `KYC ${isUpdate ? 'updated' : 'created'} successfully.`)
      await loadExisting()
      initialSnapshot = deepCloneForm()
      isEditing.value = false
      hasExistingKyc.value = true
      createModalOpen.value = false
    } else {
      const serverErrors = res?.data?.errors ?? res?.errors ?? null
      if (serverErrors && typeof serverErrors === 'object') {
        Object.entries(serverErrors).forEach(([k, v]) => {
          errors[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
        })
        toast.error('Validation Failed', 'Server validation failed. Please check highlighted fields.')
      } else {
        const message = res?.data?.message || res?.message || 'Unable to submit KYC. Please try again.'
        toast.error('Submission Failed', message)
      }
    }
  } catch (err: any) {
    console.error('Submit error:', err)
    toast.error('Error', err?.message || 'An error occurred during submission')
  } finally {
    processing.value = false
  }
}

/* Modal controls */
function goToCreateForm() {
  createModalOpen.value = false
  isEditing.value = true
  toast.success('Welcome!', 'Please fill in your details to complete verification')
}

/* Snapshot helpers */
function deepCloneForm() {
  return {
    aadhaar: form.aadhaar,
    pan: form.pan,
    hasTax: form.hasTax,
    gst: form.gst,
    utility_bills: form.utility_bills.map(b => ({
      id: b.id,
      type: b.type,
      identifier: b.identifier
    })),
    preview: { ...toRaw(preview) }
  }
}

function applySnapshot(s: any) {
  form.aadhaar = s.aadhaar || ''
  form.pan = s.pan || ''
  form.hasTax = !!s.hasTax
  form.gst = s.gst || ''
  form.utility_bills = Array.isArray(s.utility_bills)
      ? s.utility_bills.map((b: any) => ({
        id: b.id || utilityIdCounter++,
        type: b.type || '',
        identifier: b.identifier || ''
      }))
      : []
  preview.aadhaar = s.preview?.aadhaar || ''
  preview.pan = s.preview?.pan || ''
  preview.gst = s.preview?.gst || ''

  // Update display values
  aadhaarDisplayValue.value = formatAadhaar(form.aadhaar)
  panDisplayValue.value = form.pan
  gstDisplayValue.value = form.gst
}

/* Animations */
function initializeAnimations() {
  if (!process.client || !gsap) return

  gsapContext = gsap.context(() => {
    // Floating orbs animation
    if (pageOrb1.value && pageOrb2.value) {
      gsap.to(pageOrb1.value, {
        x: -100,
        y: 100,
        rotation: 360,
        duration: 25,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })

      gsap.to(pageOrb2.value, {
        x: 100,
        y: -80,
        rotation: -360,
        duration: 30,
        repeat: -1,
        yoyo: true,
        ease: 'none'
      })
    }

    // Card entrance animation
    if (mainCard.value) {
      gsap.fromTo(mainCard.value,
          { opacity: 0, y: 50, scale: 0.95 },
          {
            opacity: 1,
            y: 0,
            scale: 1,
            duration: 0.8,
            ease: 'back.out(1.7)'
          }
      )
    }
  })
}

/* Lifecycle */
onMounted(() => {
  loadExisting()

  // Initialize animations
  setTimeout(() => {
    initializeAnimations()
  }, 100)
})

onUnmounted(() => {
  if (gsapContext) {
    gsapContext.kill()
  }
})
</script>

<style scoped>
/* ✅ Custom animations that can't be done with Tailwind */
@keyframes slideInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes float {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(-10px) rotate(5deg);
  }
}

@keyframes float-reverse {
  0%, 100% {
    transform: translateY(0px) rotate(0deg);
  }
  50% {
    transform: translateY(10px) rotate(-5deg);
  }
}

@keyframes progress {
  from {
    transform: scaleX(0);
  }
  to {
    transform: scaleX(1);
  }
}

.animate-slideInDown {
  animation: slideInDown 0.6s ease-out;
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease-out;
}

.animate-float {
  animation: float 20s infinite ease-in-out;
}

.animate-float-reverse {
  animation: float-reverse 25s infinite ease-in-out;
}

.animate-progress {
  animation: progress 0.5s ease-out;
}

/* Custom hover scale utilities */
.hover\:scale-102:hover {
  transform: scale(1.02);
}

/* List transition animations */
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}

.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}

.list-move {
  transition: transform 0.3s ease;
}

/* Focus ring for accessibility */
.focus\:ring-2:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Remove default transitions for reduced motion */
@media (prefers-reduced-motion: reduce) {
  *,
  ::before,
  ::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  .bg-gradient-to-r {
    background: currentColor !important;
  }
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  @apply bg-gray-100 dark:bg-gray-800 rounded-lg;
}

::-webkit-scrollbar-thumb {
  @apply bg-gray-300 dark:bg-gray-600 rounded-lg hover:bg-gray-400 dark:hover:bg-gray-500;
}
</style>
