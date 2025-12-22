<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800">
    <!-- Floating Background Elements -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-96 h-96 bg-blue-400/10 dark:bg-blue-400/5 rounded-full blur-3xl opacity-60 animate-pulse"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-80 h-80 bg-purple-400/10 dark:bg-purple-400/5 rounded-full blur-3xl opacity-50 animate-pulse"></div>

      <!-- Floating Elements -->
      <div v-for="i in 6" :key="i" class="absolute animate-bounce opacity-20" :style="getFloatingElementStyle(i)">
        <div class="w-1 h-1 bg-blue-500/30 dark:bg-blue-400/20 rounded-full"></div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen relative z-10">
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-8 shadow-xl">
        <div class="flex flex-col items-center gap-4">
          <div class="w-16 h-16 border-4 border-blue-200 dark:border-blue-800 border-t-blue-600 dark:border-t-blue-400 rounded-full animate-spin"></div>
          <p class="text-slate-600 dark:text-slate-400 font-semibold">Loading beneficiaries...</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div v-else class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

      <!-- Header -->
      <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl overflow-hidden relative">
        <!-- Header Background -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 opacity-50"></div>

        <div class="relative z-10">
          <!-- Breadcrumb -->
          <div class="flex items-center gap-2 text-sm mb-6">
            <NuxtLink to="/dashboard/wallet" class="flex items-center gap-1 text-slate-500 hover:text-blue-600 dark:text-slate-400 dark:hover:text-blue-400 transition-colors">
              <Icon name="mdi:wallet" class="w-4 h-4" />
              <span>Wallet</span>
            </NuxtLink>
            <Icon name="mdi:chevron-right" class="w-4 h-4 text-slate-400" />
            <span class="text-slate-700 dark:text-slate-300 font-medium">Beneficiaries</span>
          </div>

          <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <!-- Title Section -->
            <div class="flex-1">
              <h1 class="text-3xl font-bold text-slate-900 dark:text-white mb-2">Manage Beneficiaries</h1>
              <p class="text-slate-600 dark:text-slate-400 mb-6 leading-relaxed">Add and manage your bank accounts and UPI handles for easy withdrawals</p>

              <!-- Stats -->
              <div class="flex gap-6 flex-wrap">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-indigo-100 dark:from-blue-900/30 dark:to-indigo-900/30 rounded-xl flex items-center justify-center">
                    <Icon name="mdi:bank" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                  </div>
                  <div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">Total Accounts</div>
                    <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ list.length }}</div>
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900/30 dark:to-emerald-900/30 rounded-xl flex items-center justify-center">
                    <Icon name="mdi:check-circle" class="w-5 h-5 text-green-600 dark:text-green-400" />
                  </div>
                  <div>
                    <div class="text-sm text-slate-500 dark:text-slate-400">Default Set</div>
                    <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ list.some(b => b.default) ? '1' : '0' }}</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Action Button -->
            <div class="flex-shrink-0">
              <button
                  @click="openCreateForm"
                  :disabled="processing"
                  class="group relative inline-flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1 overflow-hidden"
              >
                <div class="flex items-center gap-2 relative z-10">
                  <Icon name="mdi:plus-circle" class="w-5 h-5" />
                  <span>Add Beneficiary</span>
                </div>
                <!-- Shimmer Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-700"></div>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div v-if="list.length === 0">
        <!-- Empty State -->
        <div class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-12 shadow-xl text-center">
          <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-200 dark:from-slate-700 dark:to-slate-600 rounded-2xl flex items-center justify-center mx-auto mb-6">
            <Icon name="mdi:bank-plus" class="w-12 h-12 text-slate-400 dark:text-slate-500" />
          </div>

          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">No Beneficiaries Added</h3>
          <p class="text-slate-600 dark:text-slate-400 mb-8 max-w-md mx-auto leading-relaxed">Add your bank accounts or UPI handles to start making withdrawals from your wallet.</p>

          <div class="flex items-center justify-center gap-8 mb-8 flex-wrap">
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:shield-check" class="w-5 h-5 text-green-500" />
              <span class="text-slate-600 dark:text-slate-400">Secure & Encrypted</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:flash" class="w-5 h-5 text-blue-500" />
              <span class="text-slate-600 dark:text-slate-400">Instant Withdrawals</span>
            </div>
            <div class="flex items-center gap-2 text-sm">
              <Icon name="mdi:bank" class="w-5 h-5 text-purple-500" />
              <span class="text-slate-600 dark:text-slate-400">Multiple Banks Supported</span>
            </div>
          </div>

          <button
              @click="openCreateForm"
              :disabled="processing"
              class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
          >
            <Icon name="mdi:plus" class="w-5 h-5" />
            <span>Add Your First Beneficiary</span>
          </button>
        </div>
      </div>

      <div v-else>
        <!-- Beneficiaries Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
          <div
              v-for="(beneficiary, index) in list"
              :key="beneficiary.uuid"
              class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl p-6 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 relative overflow-hidden"
              :class="{ 'ring-2 ring-amber-400 dark:ring-amber-500': beneficiary.default }"
          >
            <!-- Default Badge -->
            <div v-if="beneficiary.default" class="absolute -top-1 -right-1 w-6 h-6 bg-gradient-to-br from-amber-400 to-orange-500 rounded-full flex items-center justify-center">
              <Icon name="mdi:star" class="w-3 h-3 text-white" />
            </div>

            <!-- Card Header -->
            <div class="flex items-start justify-between mb-6">
              <div class="flex items-start gap-4 flex-1">
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" :class="getBeneficiaryTypeClass(beneficiary.type)">
                  <Icon :name="getBeneficiaryTypeIcon(beneficiary.type)" class="w-6 h-6" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-slate-900 dark:text-white mb-1">{{ getBeneficiaryTypeLabel(beneficiary.type) }}</div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span v-if="beneficiary.default" class="inline-flex items-center gap-1 px-2 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-xs font-semibold">
                      <Icon name="mdi:star" class="w-3 h-3" />
                      <span>Default</span>
                    </span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold" :class="getStatusBadgeClass(beneficiary.status)">
                      {{ beneficiary.status || 'Active' }}
                    </span>
                  </div>
                </div>
              </div>

              <!-- Dropdown Menu -->
              <div class="relative">
                <button
                    @click.stop="toggleMenu(beneficiary.uuid)"
                    class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors"
                    :class="{ 'bg-slate-200 dark:bg-slate-600': activeMenu === beneficiary.uuid }"
                >
                  <Icon name="mdi:dots-vertical" class="w-4 h-4" />
                </button>

                <div
                    v-if="activeMenu === beneficiary.uuid"
                    @click.stop
                    class="absolute top-full right-0 mt-1 min-w-40 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xl z-30 overflow-hidden"
                >
                  <button
                      @click.stop="openEditForm(beneficiary)"
                      :disabled="processing"
                      class="w-full flex items-center gap-3 px-4 py-3 hover:bg-amber-50 dark:hover:bg-amber-900/20 text-amber-700 dark:text-amber-400 transition-colors disabled:opacity-50"
                  >
                    <Icon name="mdi:pencil" class="w-4 h-4" />
                    <span>Edit</span>
                  </button>

                  <button
                      v-if="!beneficiary.default"
                      @click.stop="makeDefault(beneficiary)"
                      :disabled="processing"
                      class="w-full flex items-center gap-3 px-4 py-3 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-blue-700 dark:text-blue-400 transition-colors disabled:opacity-50"
                  >
                    <Icon name="mdi:star" class="w-4 h-4" />
                    <span>Set as Default</span>
                  </button>

                  <button
                      @click.stop="confirmDelete(beneficiary)"
                      :disabled="processing || beneficiary.default"
                      class="w-full flex items-center gap-3 px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/20 text-red-700 dark:text-red-400 transition-colors disabled:opacity-50"
                  >
                    <Icon name="mdi:delete" class="w-4 h-4" />
                    <span>Delete</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Card Content -->
            <div class="space-y-4">
              <!-- UPI Details -->
              <div v-if="beneficiary.type === 'upi'">
                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">UPI Handle</div>
                <div class="flex items-center gap-2 text-purple-600 dark:text-purple-400 font-medium">
                  <Icon name="mdi:at" class="w-4 h-4" />
                  <span class="font-mono">{{ beneficiary.upi_handle }}</span>
                </div>
              </div>

              <!-- Bank Details -->
              <div v-else class="space-y-3">
                <div>
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Account Holder</div>
                  <div class="font-medium text-slate-900 dark:text-white">{{ beneficiary.account_name || '—' }}</div>
                </div>

                <div>
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Bank Details</div>
                  <div>
                    <div class="font-semibold text-slate-900 dark:text-white">{{ beneficiary.bank_name || '—' }}</div>
                    <div v-if="beneficiary.bank_branch" class="text-sm text-slate-500 dark:text-slate-400">{{ beneficiary.bank_branch }}</div>
                  </div>
                </div>

                <div>
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">Account Number</div>
                  <div class="flex items-center gap-2 text-blue-600 dark:text-blue-400">
                    <Icon name="mdi:bank" class="w-4 h-4" />
                    <span class="font-mono">{{ beneficiary.account_number ? `•••• •••• •••• ${String(beneficiary.account_number).slice(-4)}` : '—' }}</span>
                  </div>
                </div>

                <div>
                  <div class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wide mb-1">IFSC Code</div>
                  <div class="flex items-center justify-between">
                    <span class="font-mono text-slate-900 dark:text-white">{{ beneficiary.ifsc || '—' }}</span>
                    <button
                        v-if="beneficiary.ifsc"
                        @click.stop="copyToClipboard(beneficiary.ifsc)"
                        class="w-6 h-6 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 rounded text-blue-600 dark:text-blue-400 flex items-center justify-center transition-colors"
                        title="Copy IFSC"
                    >
                      <Icon name="mdi:content-copy" class="w-3 h-3" />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <!-- Card Actions -->
            <div class="flex items-center gap-2 mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
              <button
                  @click.stop="openEditForm(beneficiary)"
                  :disabled="processing"
                  class="flex items-center gap-2 px-3 py-2 bg-amber-100 dark:bg-amber-900/30 hover:bg-amber-200 dark:hover:bg-amber-900/50 text-amber-700 dark:text-amber-400 rounded-lg transition-colors text-sm font-medium disabled:opacity-50"
              >
                <Icon name="mdi:pencil-outline" class="w-4 h-4" />
                <span>Edit</span>
              </button>

              <button
                  v-if="!beneficiary.default"
                  @click.stop="makeDefault(beneficiary)"
                  :disabled="processing"
                  class="flex items-center gap-2 px-3 py-2 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-700 dark:text-blue-400 rounded-lg transition-colors text-sm font-medium disabled:opacity-50"
              >
                <Icon name="mdi:star-outline" class="w-4 h-4" />
                <span>Make Default</span>
              </button>

              <div class="flex-1"></div>

              <button
                  @click.stop="confirmDelete(beneficiary)"
                  :disabled="processing || beneficiary.default"
                  :title="beneficiary.default ? 'Cannot delete default beneficiary' : 'Delete beneficiary'"
                  class="w-8 h-8 bg-red-100 dark:bg-red-900/30 hover:bg-red-200 dark:hover:bg-red-900/50 disabled:opacity-50 text-red-600 dark:text-red-400 rounded-lg flex items-center justify-center transition-colors"
              >
                <Icon name="mdi:delete-outline" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Load More Card -->
          <div v-if="hasMore" class="bg-white/90 dark:bg-slate-800/90 backdrop-blur-xl border-2 border-dashed border-slate-300 dark:border-slate-600 rounded-2xl p-8 shadow-xl flex flex-col items-center justify-center text-center hover:border-blue-400 dark:hover:border-blue-500 transition-colors">
            <Icon name="mdi:plus-circle-outline" class="w-12 h-12 text-slate-400 mb-4" />
            <p class="text-slate-600 dark:text-slate-400 mb-4">More beneficiaries available</p>
            <button
                @click="loadBeneficiaries()"
                :disabled="processing"
                class="flex items-center gap-2 px-4 py-2 bg-blue-100 dark:bg-blue-900/30 hover:bg-blue-200 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-lg transition-colors font-medium disabled:opacity-50"
            >
              <Icon :name="processing ? 'mdi:loading' : 'mdi:refresh'" class="w-4 h-4" :class="{ 'animate-spin': processing }" />
              <span>{{ processing ? 'Loading...' : 'Load More' }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Modal -->
    <div v-if="showForm" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="closeForm">
      <div class="w-full max-w-2xl mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-slate-200 dark:border-slate-700">
          <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-lg" :class="isEdit ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gradient-to-br from-blue-500 to-indigo-600'">
              <Icon :name="isEdit ? 'mdi:pencil' : 'mdi:bank-plus'" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold text-slate-900 dark:text-white">{{ isEdit ? 'Edit Beneficiary' : 'Add New Beneficiary' }}</h3>
              <p class="text-sm text-slate-500 dark:text-slate-400">{{ isEdit ? 'Update your beneficiary details' : 'Add a bank account or UPI handle for withdrawals' }}</p>
            </div>
          </div>
          <button @click="closeForm" class="w-8 h-8 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 rounded-lg flex items-center justify-center text-slate-500 dark:text-slate-400 transition-colors">
            <Icon name="mdi:close" class="w-5 h-5" />
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
          <!-- Account Type Selection (Create Mode) -->
          <div v-if="!isEdit" class="mb-6">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
              <Icon name="mdi:account-outline" class="w-4 h-4" />
              <span>Choose Account Type</span>
              <span class="text-red-500">*</span>
            </label>

            <div class="space-y-3">
              <div
                  v-for="option in typeOptions"
                  :key="option.value"
                  @click="selectType(option.value)"
                  class="flex items-center gap-4 p-4 border-2 rounded-xl cursor-pointer transition-all"
                  :class="selectedType === option.value
                  ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                  : 'border-slate-200 dark:border-slate-600 hover:border-blue-300 dark:hover:border-blue-500 hover:bg-blue-50/50 dark:hover:bg-blue-900/10'"
              >
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="getBeneficiaryTypeClass(option.value)">
                  <Icon :name="getBeneficiaryTypeIcon(option.value)" class="w-6 h-6" />
                </div>
                <div class="flex-1">
                  <div class="font-semibold text-slate-900 dark:text-white">{{ option.label }}</div>
                  <div class="text-sm text-slate-500 dark:text-slate-400">{{ getTypeDescription(option.value) }}</div>
                </div>
                <div v-if="selectedType === option.value" class="text-blue-600 dark:text-blue-400">
                  <Icon name="mdi:check-circle" class="w-6 h-6" />
                </div>
              </div>
            </div>
          </div>

          <!-- Read-only Type Display (Edit Mode) -->
          <div v-else class="mb-6">
            <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-4">
              <Icon name="mdi:account-outline" class="w-4 h-4" />
              <span>Account Type</span>
            </label>
            <div class="flex items-center gap-4 p-4 border-2 border-slate-200 dark:border-slate-600 rounded-xl bg-slate-50 dark:bg-slate-700/50">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="getBeneficiaryTypeClass(selectedType)">
                <Icon :name="getBeneficiaryTypeIcon(selectedType)" class="w-6 h-6" />
              </div>
              <div class="flex-1">
                <div class="font-semibold text-slate-900 dark:text-white">{{ getBeneficiaryTypeLabel(selectedType) }}</div>
                <div class="text-sm text-slate-500 dark:text-slate-400">Account type cannot be changed</div>
              </div>
              <div class="text-slate-400">
                <Icon name="mdi:lock" class="w-5 h-5" />
              </div>
            </div>
          </div>

          <!-- Form Content -->
          <div v-if="selectedType">
            <!-- UPI Form -->
            <div v-if="selectedType === 'upi'" class="space-y-6">
              <div>
                <h4 class="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-200 dark:border-slate-700">
                  <Icon name="mdi:cellphone" class="w-5 h-5 text-purple-600" />
                  <span>UPI Payment Details</span>
                </h4>

                <div>
                  <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                    <Icon name="mdi:at" class="w-4 h-4" />
                    <span>UPI Handle / UPI ID</span>
                    <span class="text-red-500">*</span>
                  </label>
                  <input
                      v-model="upiForm.upi_handle"
                      type="text"
                      placeholder="yourname@paytm, mobile@okaxis, etc."
                      class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-mono"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.upi_handle }"
                      @input="clearError('upi_handle')"
                  />
                  <div class="flex items-center gap-1 mt-2 text-xs text-blue-600 dark:text-blue-400">
                    <Icon name="mdi:information" class="w-4 h-4" />
                    <span>Enter your UPI ID exactly as it appears in your UPI app</span>
                  </div>
                  <div v-if="errors.upi_handle" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                    <Icon name="mdi:alert-circle" class="w-4 h-4" />
                    <span>{{ errors.upi_handle }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Bank Form -->
            <div v-else class="space-y-6">
              <div>
                <h4 class="flex items-center gap-2 text-lg font-semibold text-slate-900 dark:text-white mb-4 pb-2 border-b border-slate-200 dark:border-slate-700">
                  <Icon name="mdi:bank" class="w-5 h-5 text-blue-600" />
                  <span>Bank Account Details</span>
                </h4>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <!-- Account Holder Name -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:account" class="w-4 h-4" />
                      <span>Account Holder Name</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="bankForm.account_name"
                        type="text"
                        placeholder="Full name as per bank records"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.account_name }"
                        @input="clearError('account_name')"
                    />
                    <div v-if="errors.account_name" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.account_name }}</span>
                    </div>
                  </div>

                  <!-- Account Number -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:credit-card-outline" class="w-4 h-4" />
                      <span>Account Number</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="bankForm.account_number"
                        type="text"
                        placeholder="Enter account number"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-mono"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.account_number }"
                        @input="clearError('account_number')"
                    />
                    <div v-if="errors.account_number" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.account_number }}</span>
                    </div>
                  </div>

                  <!-- IFSC Code -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:bank-transfer" class="w-4 h-4" />
                      <span>IFSC Code</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="bankForm.ifsc"
                        type="text"
                        placeholder="e.g., SBIN0001234"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all font-mono uppercase"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.ifsc }"
                        @input="handleIfscInput"
                    />
                    <div v-if="errors.ifsc" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.ifsc }}</span>
                    </div>
                  </div>

                  <!-- Bank Name -->
                  <div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:bank" class="w-4 h-4" />
                      <span>Bank Name</span>
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                        v-model="bankForm.bank_name"
                        type="text"
                        placeholder="Enter bank name"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                        :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': errors.bank_name }"
                        @input="clearError('bank_name')"
                    />
                    <div v-if="errors.bank_name" class="flex items-center gap-1 mt-2 text-sm text-red-500">
                      <Icon name="mdi:alert-circle" class="w-4 h-4" />
                      <span>{{ errors.bank_name }}</span>
                    </div>
                  </div>

                  <!-- Bank Branch -->
                  <div class="md:col-span-2">
                    <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                      <Icon name="mdi:map-marker" class="w-4 h-4" />
                      <span>Bank Branch</span>
                      <span class="text-slate-500 font-normal">(Optional)</span>
                    </label>
                    <input
                        v-model="bankForm.bank_branch"
                        type="text"
                        placeholder="Branch name or location"
                        class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Default Setting -->
            <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl">
              <div class="flex items-center gap-4">
                <label class="relative inline-flex items-center cursor-pointer">
                  <input v-model="defaultSetting" type="checkbox" class="sr-only peer" />
                  <div class="relative w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
                <div class="flex-1">
                  <div class="font-semibold text-slate-900 dark:text-white">Set as Default Account</div>
                  <div class="text-sm text-slate-600 dark:text-slate-400">This will be your primary withdrawal method</div>
                </div>
              </div>
              <div class="flex items-center gap-1 mt-3 text-xs text-blue-600 dark:text-blue-400">
                <Icon name="mdi:information-outline" class="w-4 h-4" />
                <span>You can change the default account anytime</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex gap-4 p-6 border-t border-slate-200 dark:border-slate-700">
          <button
              @click="closeForm"
              :disabled="processing"
              class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-50 text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-semibold"
          >
            Cancel
          </button>
          <button
              @click="submit"
              :disabled="processing || (!isEdit && !selectedType)"
              class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 text-white rounded-xl transition-colors font-semibold"
          >
            <Icon
                :name="processing ? 'mdi:loading' : (isEdit ? 'mdi:content-save' : 'mdi:plus')"
                class="w-4 h-4"
                :class="{ 'animate-spin': processing }"
            />
            <span>{{ processing ? (isEdit ? 'Updating...' : 'Saving...') : (isEdit ? 'Update Beneficiary' : 'Save Beneficiary') }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="confirmDeleteOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="confirmDeleteOpen = false">
      <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-xl border border-white/20 dark:border-slate-700/50 rounded-2xl shadow-2xl">
        <!-- Delete Header -->
        <div class="text-center p-6">
          <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
            <Icon name="mdi:delete-alert" class="w-8 h-8 text-white" />
          </div>
          <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Delete Beneficiary</h3>
          <p class="text-slate-500 dark:text-slate-400">This action cannot be undone</p>
        </div>

        <!-- Delete Target Info -->
        <div v-if="deleteTarget" class="px-6 pb-4">
          <div class="flex items-center gap-4 p-4 bg-slate-50 dark:bg-slate-700/50 rounded-xl">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="getBeneficiaryTypeClass(deleteTarget.type)">
              <Icon :name="getBeneficiaryTypeIcon(deleteTarget.type)" class="w-5 h-5" />
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-semibold text-slate-900 dark:text-white">{{ getBeneficiaryTypeLabel(deleteTarget.type) }}</div>
              <div class="text-sm text-slate-500 dark:text-slate-400 truncate">
                {{ deleteTarget.type === 'upi' ? deleteTarget.upi_handle : `${deleteTarget.bank_name} • •••${String(deleteTarget.account_number).slice(-4)}` }}
              </div>
            </div>
          </div>
        </div>

        <!-- Delete Actions -->
        <div class="flex gap-4 p-6 border-t border-slate-200 dark:border-slate-700">
          <button
              @click="confirmDeleteOpen = false"
              :disabled="processing"
              class="flex-1 px-6 py-3 bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 disabled:opacity-50 text-slate-700 dark:text-slate-300 rounded-xl transition-colors font-semibold"
          >
            Keep It
          </button>
          <button
              @click="doDelete"
              :disabled="processing || (deleteTarget && deleteTarget.default)"
              class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 disabled:opacity-50 text-white rounded-xl transition-colors font-semibold"
          >
            <Icon
                :name="processing ? 'mdi:loading' : 'mdi:delete'"
                class="w-4 h-4"
                :class="{ 'animate-spin': processing }"
            />
            <span>{{ processing ? 'Deleting...' : 'Delete' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, onUnmounted, nextTick } from 'vue'
import { useRuntimeConfig, useToast, useSanctumFetch } from '#imports'

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
const toast = useToast()
const isLoading = useState('pageLoading', () => true)
const processing = ref(false)

/* Types */
type Beneficiary = {
  uuid: string
  type: 'savings' | 'current' | 'upi'
  bank_name?: string | null
  bank_branch?: string | null
  account_name?: string | null
  account_number?: string | null
  ifsc?: string | null
  upi_handle?: string | null
  default?: boolean
  status?: string
}

/* State */
const list = ref<Beneficiary[]>([])
const page = ref(1)
const hasMore = ref(true)
const perPage = ref(10)

/* UI State */
const showForm = ref(false)
const isEdit = ref(false)
const editingUuid = ref<string | null>(null)
const activeMenu = ref<string | null>(null)

// Form state
const selectedType = ref<'savings' | 'current' | 'upi' | null>(null)
const defaultSetting = ref(false)

// Form objects
const bankForm = reactive({
  bank_name: '',
  bank_branch: '',
  account_name: '',
  account_number: '',
  ifsc: '',
})

const upiForm = reactive({
  upi_handle: '',
})

const errors = reactive<Record<string, string>>({})
const confirmDeleteOpen = ref(false)
const deleteTarget = ref<Beneficiary | null>(null)

// Refs
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()

/* Helpers */
const typeOptions = [
  { value: 'savings', label: 'Savings Account' },
  { value: 'current', label: 'Current Account' },
  { value: 'upi', label: 'UPI Handle' },
]

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

function getBeneficiaryTypeIcon(type: string) {
  const icons = {
    savings: 'mdi:bank',
    current: 'mdi:bank-outline',
    upi: 'mdi:cellphone'
  }
  return icons[type as keyof typeof icons] || 'mdi:bank'
}

function getBeneficiaryTypeClass(type: string) {
  const classes = {
    savings: 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    current: 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
    upi: 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400'
  }
  return classes[type as keyof typeof classes] || classes.savings
}

function getBeneficiaryTypeLabel(type: string) {
  const labels = {
    savings: 'Savings Account',
    current: 'Current Account',
    upi: 'UPI Handle'
  }
  return labels[type as keyof typeof labels] || 'Bank Account'
}

function getTypeDescription(type: string) {
  const descriptions = {
    savings: 'Personal savings bank account',
    current: 'Business current account',
    upi: 'Unified Payments Interface'
  }
  return descriptions[type as keyof typeof descriptions] || ''
}

function getStatusBadgeClass(status?: string) {
  const classes = {
    active: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400',
    pending: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    inactive: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'
  }
  return classes[status as keyof typeof classes] || 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
}

function toggleMenu(uuid: string) {
  activeMenu.value = activeMenu.value === uuid ? null : uuid
}

function closeMenu() {
  activeMenu.value = null
}

function clearError(field: string) {
  if (errors[field]) {
    delete errors[field]
  }
}

function selectType(type: string) {
  selectedType.value = type as 'savings' | 'current' | 'upi'
  clearAllErrors()
}

function clearAllErrors() {
  Object.keys(errors).forEach(k => delete errors[k])
}

function handleIfscInput(event: Event) {
  const target = event.target as HTMLInputElement
  bankForm.ifsc = target.value.toUpperCase()
  clearError('ifsc')
}

async function copyToClipboard(text: string) {
  try {
    await navigator.clipboard.writeText(text)
    toast.success({ title: 'Copied', message: 'IFSC code copied to clipboard' })
  } catch {
    toast.error({ title: 'Error', message: 'Failed to copy to clipboard' })
  }
}

// Reset functions
function resetBankForm() {
  bankForm.bank_name = ''
  bankForm.bank_branch = ''
  bankForm.account_name = ''
  bankForm.account_number = ''
  bankForm.ifsc = ''
}

function resetUpiForm() {
  upiForm.upi_handle = ''
}

function resetForm() {
  selectedType.value = null
  defaultSetting.value = false
  resetBankForm()
  resetUpiForm()
  clearAllErrors()
  editingUuid.value = null
  isEdit.value = false
}

function openCreateForm() {
  resetForm()
  showForm.value = true
  closeMenu()
}

function openEditForm(beneficiary: Beneficiary) {
  resetForm()
  isEdit.value = true
  editingUuid.value = beneficiary.uuid
  selectedType.value = beneficiary.type
  defaultSetting.value = beneficiary.default || false

  // Populate the correct form based on type
  if (beneficiary.type === 'upi') {
    upiForm.upi_handle = beneficiary.upi_handle || ''
  } else {
    bankForm.bank_name = beneficiary.bank_name || ''
    bankForm.bank_branch = beneficiary.bank_branch || ''
    bankForm.account_name = beneficiary.account_name || ''
    bankForm.account_number = beneficiary.account_number || ''
    bankForm.ifsc = beneficiary.ifsc || ''
  }

  showForm.value = true
  closeMenu()
}

function closeForm() {
  showForm.value = false
  resetForm()
}

/* API Functions */
function parsePagePayload(res: any) {
  const data = res?.data
  if (Array.isArray(data)) return { items: data as Beneficiary[], next: null }
  const items = Array.isArray(data?.data) ? (data.data as Beneficiary[]) : (Array.isArray(data) ? (data as Beneficiary[]) : [])
  const next = data?.next_page_url ?? null
  return { items, next }
}

async function loadBeneficiaries(reset = false) {
  try {
    if (reset) {
      list.value = []
      page.value = 1
      hasMore.value = true
    }
    if (!hasMore.value || processing.value) return

    const url = new URL(`${config.public.apiBase}/beneficiaries`)
    url.searchParams.set('page', String(page.value))
    url.searchParams.set('per_page', String(perPage.value))

    processing.value = true
    const res = await useSanctumFetch(url.toString())
    const { items, next } = parsePagePayload(res)

    list.value.push(...items)
    hasMore.value = !!next
    page.value++
  } catch (e: any) {
    toast.error({ title: 'Beneficiaries', message: e?.data?.message || 'Failed to fetch beneficiaries.' })
  } finally {
    processing.value = false
  }
}

/* Form validation and submission */
function validate() {
  clearAllErrors()

  if (!selectedType.value) {
    errors.type = 'Please select an account type.'
    return false
  }

  if (selectedType.value === 'upi') {
    if (!upiForm.upi_handle?.trim()) {
      errors.upi_handle = 'UPI handle is required.'
    } else if (!upiForm.upi_handle.includes('@')) {
      errors.upi_handle = 'Invalid UPI handle format (e.g., name@bank).'
    }
  } else {
    if (!bankForm.account_name?.trim()) errors.account_name = 'Account holder name is required.'
    if (!bankForm.account_number?.trim()) errors.account_number = 'Account number is required.'
    if (!bankForm.ifsc?.trim()) errors.ifsc = 'IFSC code is required.'
    if (!bankForm.bank_name?.trim()) errors.bank_name = 'Bank name is required.'

    // Validate IFSC format
    if (bankForm.ifsc && !/^[A-Z]{4}0[A-Z0-9]{6}$/.test(bankForm.ifsc)) {
      errors.ifsc = 'Invalid IFSC format (e.g., SBIN0001234).'
    }
  }

  return Object.keys(errors).length === 0
}

async function submit() {
  if (!validate()) {
    toast.error({ title: 'Validation Error', message: 'Please fix the highlighted errors.' })
    return
  }

  processing.value = true
  try {
    const payload: Record<string, any> = {
      type: selectedType.value,
      default: defaultSetting.value,
    }

    if (selectedType.value === 'upi') {
      payload.upi_handle = upiForm.upi_handle.trim()
    } else {
      payload.bank_name = bankForm.bank_name.trim()
      payload.bank_branch = bankForm.bank_branch?.trim() || null
      payload.account_name = bankForm.account_name.trim()
      payload.account_number = bankForm.account_number.trim()
      payload.ifsc = bankForm.ifsc.trim().toUpperCase()
    }

    let res: any

    if (isEdit.value && editingUuid.value) {
      // Edit functionality
      res = await useSanctumFetch(`${config.public.apiBase}/beneficiaries/${editingUuid.value}`, {
        method: 'PUT',
        body: payload,
      })

      const updated = res?.data || null
      if (updated) {
        const index = list.value.findIndex(b => b.uuid === editingUuid.value)
        if (index !== -1) {
          list.value[index] = { ...list.value[index], ...updated }
        }
      } else {
        await loadBeneficiaries(true)
      }

      toast.success({ title: 'Success', message: 'Beneficiary updated successfully!' })
    } else {
      // Create functionality
      res = await useSanctumFetch(`${config.public.apiBase}/beneficiaries`, {
        method: 'POST',
        body: payload,
      })

      const created = res?.data || null
      if (created) {
        list.value.unshift(created)
      } else {
        await loadBeneficiaries(true)
      }

      toast.success({ title: 'Success', message: 'Beneficiary added successfully!' })
    }

    closeForm()
  } catch (e: any) {
    const serverErrors = e?.data?.errors ?? e?.errors
    if (serverErrors && typeof serverErrors === 'object') {
      Object.entries(serverErrors).forEach(([k, v]) => {
        errors[k] = Array.isArray(v) ? (v as any).join(', ') : (v as any)
      })
    }
    toast.error({
      title: 'Error',
      message: e?.data?.message || (isEdit.value ? 'Failed to update beneficiary.' : 'Failed to add beneficiary.')
    })
  } finally {
    processing.value = false
  }
}

/* Delete operations */
function confirmDelete(b: Beneficiary) {
  deleteTarget.value = b
  confirmDeleteOpen.value = true
  closeMenu()
}

async function doDelete() {
  if (!deleteTarget.value) return

  processing.value = true
  try {
    await useSanctumFetch(`${config.public.apiBase}/beneficiaries/${deleteTarget.value.uuid}`, {
      method: 'DELETE'
    })

    list.value = list.value.filter(x => x.uuid !== deleteTarget.value!.uuid)
    toast.success({ title: 'Success', message: 'Beneficiary deleted successfully!' })

    confirmDeleteOpen.value = false
    deleteTarget.value = null
  } catch (e: any) {
    toast.error({ title: 'Error', message: e?.data?.message || 'Failed to delete beneficiary.' })
  } finally {
    processing.value = false
  }
}

/* Make default */
async function makeDefault(b: Beneficiary) {
  processing.value = true
  closeMenu()

  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/beneficiaries/${b.uuid}/default`, {
      method: 'POST'
    })

    const updated = res?.data || null
    list.value = list.value.map(item => ({
      ...item,
      default: item.uuid === b.uuid,
    }))

    if (updated) {
      const idx = list.value.findIndex(x => x.uuid === updated.uuid)
      if (idx !== -1) list.value[idx] = { ...list.value[idx], ...updated, default: true }
    }

    toast.success({ title: 'Success', message: 'Default account updated successfully!' })
  } catch (e: any) {
    toast.error({ title: 'Error', message: e?.data?.message || 'Failed to set default account.' })
  } finally {
    processing.value = false
  }
}

// Event handlers
function handleEsc(e: KeyboardEvent) {
  if (e.key === 'Escape') {
    if (showForm.value) closeForm()
    if (confirmDeleteOpen.value) confirmDeleteOpen.value = false
    if (activeMenu.value) closeMenu()
  }
}

function handleClickOutside(e: Event) {
  if (activeMenu.value) {
    const target = e.target as HTMLElement
    if (!target.closest('.relative')) {
      closeMenu()
    }
  }
}

// Lifecycle
onMounted(async () => {
  document.addEventListener('keydown', handleEsc)
  document.addEventListener('click', handleClickOutside)

  isLoading.value = true
  await loadBeneficiaries(true)
  isLoading.value = false

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEsc)
  document.removeEventListener('click', handleClickOutside)
})
</script>
