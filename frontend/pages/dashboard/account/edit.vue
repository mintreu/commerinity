<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 via-blue-900 to-indigo-900">
    <!-- Floating Background Orbs -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div ref="orb1" class="absolute -top-32 -right-32 w-80 h-80 bg-gradient-to-r from-purple-400/10 to-pink-400/10 dark:from-purple-400/5 dark:to-pink-400/5 rounded-full blur-3xl opacity-60"></div>
      <div ref="orb2" class="absolute -bottom-32 -left-32 w-96 h-96 bg-gradient-to-r from-blue-400/10 to-indigo-400/10 dark:from-blue-400/5 dark:to-indigo-400/5 rounded-full blur-3xl opacity-50"></div>
      <div ref="orb3" class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-64 h-64 bg-gradient-to-r from-emerald-400/5 to-green-400/5 dark:from-emerald-400/3 dark:to-green-400/3 rounded-full blur-3xl opacity-40"></div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

      <!-- Page Header -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl p-8 shadow-2xl overflow-hidden relative hover:shadow-3xl transition-all duration-300 hover:-translate-y-1">
        <!-- Header Background Gradient -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-indigo-500/5 opacity-70"></div>

        <div class="relative z-10">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div class="flex items-start gap-6">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-3xl flex items-center justify-center shadow-2xl hover:scale-105 transition-transform duration-300">
                <Icon name="mdi:account-edit" class="w-8 h-8 text-white" />
              </div>
              <div>
                <h1 class="text-4xl font-bold bg-gradient-to-r from-slate-900 via-blue-600 to-indigo-600 dark:from-white dark:via-blue-400 dark:to-indigo-400 bg-clip-text text-transparent mb-3">
                  Edit Profile
                </h1>
                <p class="text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                  Manage your personal information and account settings
                </p>
              </div>
            </div>

            <div class="flex-shrink-0">
              <NuxtLink to="/dashboard/account" class="inline-flex items-center gap-3 px-6 py-3 bg-slate-100/80 dark:bg-slate-700/80 hover:bg-slate-200/80 dark:hover:bg-slate-600/80 text-slate-700 dark:text-slate-300 rounded-xl font-semibold backdrop-blur-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
                <Icon name="mdi:arrow-left" class="w-5 h-5" />
                <span>Back to Profile</span>
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="flex items-center justify-center py-24">
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl p-12 shadow-2xl">
          <div class="flex flex-col items-center gap-6">
            <Icon name="mdi:loading" class="w-12 h-12 text-blue-600 dark:text-blue-400 animate-spin" />
            <p class="text-slate-600 dark:text-slate-400 font-semibold text-lg">Loading your profile...</p>
          </div>
        </div>
      </div>

      <!-- Edit Forms -->
      <div v-else class="space-y-8">

        <!-- Avatar Section -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center gap-6 p-8 border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="w-12 h-12 bg-gradient-to-br from-green-600 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
              <Icon name="mdi:account-circle" class="w-6 h-6 text-white" />
            </div>
            <div class="flex-1">
              <h3 class="text-xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">Profile Picture</h3>
              <p class="text-slate-500 dark:text-slate-400 mt-1">Update your avatar and profile image</p>
            </div>
          </div>

          <div class="p-8">
            <AvatarUploader />
          </div>
        </div>

        <!-- Basic Information Form -->
        <form @submit.prevent="submitBasicInfo" class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between p-8 border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-6">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
                <Icon name="mdi:account-details" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h3 class="text-xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Basic Information</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Update your personal details</p>
              </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing || !hasBasicInfoChanges"
                class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
              <Icon name="mdi:content-save" class="w-5 h-5" />
              <span>{{ form.processing ? 'Saving...' : 'Save Changes' }}</span>
            </button>
          </div>

          <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
              <!-- Name Field -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:account" class="w-4 h-4" />
                  <span>Full Name</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <Icon name="mdi:account" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400" />
                  <input
                      v-model="form.name"
                      type="text"
                      required
                      minlength="3"
                      maxlength="100"
                      placeholder="Enter your full name"
                      class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': form.errors.name }"
                  />
                </div>
                <div v-if="form.errors.name" class="flex items-center gap-2 text-sm text-red-500">
                  <Icon name="mdi:alert-circle" class="w-4 h-4" />
                  <span>{{ form.errors.name }}</span>
                </div>
              </div>

              <!-- Gender Field -->
              <div class="space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:gender-male-female" class="w-4 h-4" />
                  <span>Gender</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <Icon name="mdi:gender-male-female" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400" />
                  <select
                      v-model="form.gender"
                      required
                      class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': form.errors.gender }"
                  >
                    <option value="">Select your gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                    <option value="prefer_not_to_say">Prefer not to say</option>
                  </select>
                </div>
                <div v-if="form.errors.gender" class="flex items-center gap-2 text-sm text-red-500">
                  <Icon name="mdi:alert-circle" class="w-4 h-4" />
                  <span>{{ form.errors.gender }}</span>
                </div>
              </div>

              <!-- Date of Birth Field -->
              <div class="md:col-span-2 space-y-3">
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-300">
                  <Icon name="mdi:calendar" class="w-4 h-4" />
                  <span>Date of Birth</span>
                  <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <Icon name="mdi:calendar" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-slate-400" />
                  <input
                      v-model="form.dob"
                      type="date"
                      required
                      :max="maxDate"
                      class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                      :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': form.errors.dob }"
                  />
                </div>
                <div v-if="form.errors.dob" class="flex items-center gap-2 text-sm text-red-500">
                  <Icon name="mdi:alert-circle" class="w-4 h-4" />
                  <span>{{ form.errors.dob }}</span>
                </div>
              </div>
            </div>

            <!-- Unsaved Changes Indicator -->
            <div v-if="hasBasicInfoChanges" class="mt-8 pt-6 border-t border-slate-200/50 dark:border-slate-700/50">
              <div class="flex items-center gap-3 px-4 py-3 bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 text-amber-700 dark:text-amber-400 rounded-xl text-sm font-semibold backdrop-blur-sm">
                <Icon name="mdi:information" class="w-5 h-5" />
                <span>You have unsaved changes</span>
              </div>
            </div>
          </div>
        </form>

        <!-- Bio Form -->
        <form @submit.prevent="submitBio" class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center justify-between p-8 border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="flex items-center gap-6">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-600 to-pink-600 rounded-xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
                <Icon name="mdi:text-account" class="w-6 h-6 text-white" />
              </div>
              <div>
                <h3 class="text-xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">Bio & About</h3>
                <p class="text-slate-500 dark:text-slate-400 mt-1">Tell others about yourself</p>
              </div>
            </div>

            <button
                type="submit"
                :disabled="form.processing || !hasBioChanges"
                class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5"
            >
              <Icon name="mdi:content-save" class="w-5 h-5" />
              <span>{{ form.processing ? 'Saving...' : 'Save Bio' }}</span>
            </button>
          </div>

          <div class="p-8">
            <div class="space-y-3">
              <label class="flex items-center justify-between text-sm font-semibold text-slate-700 dark:text-slate-300">
                <div class="flex items-center gap-2">
                  <Icon name="mdi:text-account" class="w-4 h-4" />
                  <span>Biography</span>
                </div>
                <span class="text-xs text-slate-500 dark:text-slate-400 font-normal px-3 py-1 bg-slate-100 dark:bg-slate-700 rounded-lg">
                  {{ form.bio.length }}/500
                </span>
              </label>

              <div class="relative">
                <textarea
                    ref="bioTextarea"
                    v-model="form.bio"
                    rows="4"
                    maxlength="500"
                    placeholder="Write something about yourself... (optional)"
                    class="w-full px-4 py-4 bg-slate-50 dark:bg-slate-700 border-2 border-slate-200 dark:border-slate-600 rounded-xl text-slate-900 dark:text-white placeholder-slate-500 dark:placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none"
                    :class="{ 'border-red-500 bg-red-50 dark:bg-red-900/20': form.errors.bio }"
                    @input="resizeBio"
                ></textarea>
                <div v-if="form.errors.bio" class="flex items-center gap-2 mt-2 text-sm text-red-500">
                  <Icon name="mdi:alert-circle" class="w-4 h-4" />
                  <span>{{ form.errors.bio }}</span>
                </div>
              </div>

              <!-- Bio Tips -->
              <div class="flex items-center gap-3 px-4 py-3 bg-amber-50/60 dark:bg-amber-900/10 border border-amber-200/40 dark:border-amber-800/40 text-amber-600 dark:text-amber-400 rounded-xl text-sm backdrop-blur-sm">
                <Icon name="mdi:lightbulb" class="w-5 h-5" />
                <span>Share your interests, skills, or what makes you unique</span>
              </div>
            </div>

            <!-- Unsaved Changes Indicator -->
            <div v-if="hasBioChanges" class="mt-8 pt-6 border-t border-slate-200/50 dark:border-slate-700/50">
              <div class="flex items-center gap-3 px-4 py-3 bg-amber-50/80 dark:bg-amber-900/20 border border-amber-200/60 dark:border-amber-800/60 text-amber-700 dark:text-amber-400 rounded-xl text-sm font-semibold backdrop-blur-sm">
                <Icon name="mdi:information" class="w-5 h-5" />
                <span>You have unsaved bio changes</span>
              </div>
            </div>
          </div>
        </form>

        <!-- Account Security Section -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center gap-6 p-8 border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="w-12 h-12 bg-gradient-to-br from-red-600 to-pink-600 rounded-xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
              <Icon name="mdi:security" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent">Account Security</h3>
              <p class="text-slate-500 dark:text-slate-400 mt-1">Manage your email, mobile, and password</p>
            </div>
          </div>

          <div class="p-8 space-y-6">
            <!-- Change Email Section -->
            <div class="group">
              <div class="p-6 bg-slate-50/80 dark:bg-slate-700/50 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 backdrop-blur-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-blue-100/80 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                      <Icon name="mdi:email" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 dark:text-white">Email Address</h4>
                      <p class="text-sm text-slate-500 dark:text-slate-400">Change your login email</p>
                    </div>
                  </div>
                  <button
                      @click="showEmailForm = !showEmailForm"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100/80 dark:bg-blue-900/30 hover:bg-blue-200/80 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
                  >
                    <Icon name="mdi:pencil" class="w-4 h-4" />
                    <span>{{ showEmailForm ? 'Hide' : 'Change' }}</span>
                  </button>
                </div>

                <!-- Email Change Component -->
                <div v-if="showEmailForm" class="mt-6 pt-6 border-t border-slate-200/50 dark:border-slate-600/50">
                  <ChangeEmail @success="showEmailForm = false" />
                </div>
              </div>
            </div>

            <!-- Change Mobile Section -->
            <div class="group">
              <div class="p-6 bg-slate-50/80 dark:bg-slate-700/50 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 hover:border-green-300 dark:hover:border-green-600 transition-all duration-300 backdrop-blur-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-green-100/80 dark:bg-green-900/30 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                      <Icon name="mdi:phone" class="w-5 h-5 text-green-600 dark:text-green-400" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 dark:text-white">Mobile Number</h4>
                      <p class="text-sm text-slate-500 dark:text-slate-400">Update your phone number</p>
                    </div>
                  </div>
                  <button
                      @click="showMobileForm = !showMobileForm"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-green-100/80 dark:bg-green-900/30 hover:bg-green-200/80 dark:hover:bg-green-900/50 text-green-600 dark:text-green-400 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
                  >
                    <Icon name="mdi:pencil" class="w-4 h-4" />
                    <span>{{ showMobileForm ? 'Hide' : 'Change' }}</span>
                  </button>
                </div>

                <!-- Mobile Change Component -->
                <div v-if="showMobileForm" class="mt-6 pt-6 border-t border-slate-200/50 dark:border-slate-600/50">
                  <ChangeMobile @success="showMobileForm = false" />
                </div>
              </div>
            </div>

            <!-- Change Password Section -->
            <div class="group">
              <div class="p-6 bg-slate-50/80 dark:bg-slate-700/50 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 hover:border-purple-300 dark:hover:border-purple-600 transition-all duration-300 backdrop-blur-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-purple-100/80 dark:bg-purple-900/30 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                      <Icon name="mdi:key" class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 dark:text-white">Password</h4>
                      <p class="text-sm text-slate-500 dark:text-slate-400">Update your account password</p>
                    </div>
                  </div>
                  <button
                      @click="showPasswordForm = !showPasswordForm"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-purple-100/80 dark:bg-purple-900/30 hover:bg-purple-200/80 dark:hover:bg-purple-900/50 text-purple-600 dark:text-purple-400 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
                  >
                    <Icon name="mdi:pencil" class="w-4 h-4" />
                    <span>{{ showPasswordForm ? 'Hide' : 'Change' }}</span>
                  </button>
                </div>

                <!-- Password Change Component -->
                <div v-if="showPasswordForm" class="mt-6 pt-6 border-t border-slate-200/50 dark:border-slate-600/50">
                  <ChangePassword @success="showPasswordForm = false" />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Advanced Settings -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-2xl hover:shadow-3xl transition-all duration-300 hover:-translate-y-1">
          <div class="flex items-center gap-6 p-8 border-b border-slate-200/50 dark:border-slate-700/50">
            <div class="w-12 h-12 bg-gradient-to-br from-slate-600 to-slate-700 rounded-xl flex items-center justify-center shadow-lg hover:scale-105 transition-transform duration-300">
              <Icon name="mdi:cog" class="w-6 h-6 text-white" />
            </div>
            <div>
              <h3 class="text-xl font-bold bg-gradient-to-r from-slate-600 to-slate-700 bg-clip-text text-transparent">Advanced Settings</h3>
              <p class="text-slate-500 dark:text-slate-400 mt-1">Data export and account management</p>
            </div>
          </div>

          <div class="p-8 space-y-6">
            <!-- Export Data Section -->
            <div class="group">
              <div class="p-6 bg-slate-50/80 dark:bg-slate-700/50 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 hover:border-blue-300 dark:hover:border-blue-600 transition-all duration-300 backdrop-blur-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-blue-100/80 dark:bg-blue-900/30 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                      <Icon name="mdi:download" class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 dark:text-white">Export Data</h4>
                      <p class="text-sm text-slate-500 dark:text-slate-400">Download your account information</p>
                    </div>
                  </div>
                  <button
                      @click="showExportForm = !showExportForm"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-blue-100/80 dark:bg-blue-900/30 hover:bg-blue-200/80 dark:hover:bg-blue-900/50 text-blue-600 dark:text-blue-400 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
                  >
                    <Icon name="mdi:download" class="w-4 h-4" />
                    <span>{{ showExportForm ? 'Hide' : 'Export' }}</span>
                  </button>
                </div>

                <!-- Export Data Component -->
                <div v-if="showExportForm" class="mt-6 pt-6 border-t border-slate-200/50 dark:border-slate-600/50">
                  <ExportData @success="showExportForm = false" @scrollToSecurity="scrollToSecuritySection" />
                </div>
              </div>
            </div>

            <!-- Delete Account Section -->
            <div class="group">
              <div class="p-6 bg-slate-50/80 dark:bg-slate-700/50 rounded-2xl border border-slate-200/60 dark:border-slate-600/60 hover:border-red-300 dark:hover:border-red-600 transition-all duration-300 backdrop-blur-sm hover:shadow-lg">
                <div class="flex items-center justify-between mb-4">
                  <div class="flex items-center gap-4">
                    <div class="w-10 h-10 bg-red-100/80 dark:bg-red-900/30 rounded-xl flex items-center justify-center group-hover:scale-105 transition-transform duration-300">
                      <Icon name="mdi:delete" class="w-5 h-5 text-red-600 dark:text-red-400" />
                    </div>
                    <div>
                      <h4 class="font-bold text-slate-900 dark:text-white">Delete Account</h4>
                      <p class="text-sm text-slate-500 dark:text-slate-400">Permanently remove your account</p>
                    </div>
                  </div>
                  <button
                      @click="showDeleteForm = !showDeleteForm"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-red-100/80 dark:bg-red-900/30 hover:bg-red-200/80 dark:hover:bg-red-900/50 text-red-600 dark:text-red-400 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
                  >
                    <Icon name="mdi:delete" class="w-4 h-4" />
                    <span>{{ showDeleteForm ? 'Hide' : 'Delete' }}</span>
                  </button>
                </div>

                <!-- Delete Account Component -->
                <div v-if="showDeleteForm" class="mt-6 pt-6 border-t border-slate-200/50 dark:border-slate-600/50">
                  <DeleteAccount />
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteConfirmation" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showDeleteConfirmation = false">
        <div class="w-full max-w-md mx-auto bg-white/95 dark:bg-slate-800/95 backdrop-blur-2xl border border-white/20 dark:border-slate-700/50 rounded-3xl shadow-3xl">
          <!-- Modal Header -->
          <div class="flex items-center gap-6 p-8 border-b border-red-200/50 dark:border-red-800/50">
            <div class="w-16 h-16 bg-gradient-to-br from-red-600 to-pink-600 rounded-3xl flex items-center justify-center shadow-2xl">
              <Icon name="mdi:alert" class="w-8 h-8 text-white" />
            </div>
            <div>
              <h3 class="text-2xl font-bold text-red-600 dark:text-red-400">Delete Account</h3>
              <p class="text-slate-500 dark:text-slate-400 mt-1">This action cannot be undone</p>
            </div>
          </div>

          <!-- Modal Body -->
          <div class="p-8">
            <p class="text-slate-600 dark:text-slate-400 mb-8 leading-relaxed">
              Are you sure you want to delete your account? This will permanently remove all your data and cannot be reversed.
            </p>

            <div class="flex gap-4">
              <button
                  @click="showDeleteConfirmation = false"
                  class="flex-1 px-8 py-4 bg-slate-100/80 dark:bg-slate-700/80 hover:bg-slate-200/80 dark:hover:bg-slate-600/80 text-slate-700 dark:text-slate-300 rounded-xl font-semibold transition-all duration-300 hover:-translate-y-0.5"
              >
                Cancel
              </button>
              <button class="flex-1 px-8 py-4 bg-gradient-to-r from-red-600 to-pink-600 hover:from-red-700 hover:to-pink-700 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
                Delete Account
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import { useSanctumForm, useRuntimeConfig, useSanctumFetch, useSanctum } from '#imports'
import AvatarUploader from "~/components/account/AvatarUploader.vue"
import ChangeEmail from "~/components/account/ChangeEmail.vue"
import ChangeMobile from "~/components/account/ChangeMobile.vue"
import ChangePassword from "~/components/account/ChangePassword.vue"
import DeleteAccount from "~/components/account/DeleteAccount.vue";
import ExportData from "~/components/account/ExportData.vue";

// GSAP imports (client-side only)
let gsap: any = null
if (process.client) {
  import('gsap').then(({ default: gsapModule }) => {
    gsap = gsapModule
  })
}

definePageMeta({ layout: 'dashboard' })

// Composables
const config = useRuntimeConfig()
const { refreshUser } = useSanctum()
const { $toast } = useNuxtApp()

// State
const isLoading = ref(true)
const showDeleteConfirmation = ref(false)
const showEmailForm = ref(false)
const showMobileForm = ref(false)
const showPasswordForm = ref(false)
const originalData = ref<any>({})

// Refs for animations
const orb1 = ref<HTMLElement>()
const orb2 = ref<HTMLElement>()
const orb3 = ref<HTMLElement>()
const bioTextarea = ref<HTMLTextAreaElement>()

// Form setup
const form = useSanctumForm('PUT', `${config.public.apiBase}/account/profile`, {
  name: '',
  bio: '',
  gender: '',
  dob: '',
})

// Computed properties
const maxDate = computed(() => {
  const today = new Date()
  const maxDate = new Date(today.getFullYear() - 13, today.getMonth(), today.getDate())
  return maxDate.toISOString().split('T')[0]
})

const hasBasicInfoChanges = computed(() => {
  return form.name !== originalData.value.name ||
      form.gender !== originalData.value.gender ||
      form.dob !== originalData.value.dob
})

const hasBioChanges = computed(() => {
  return form.bio !== originalData.value.bio
})


// Add to state
const showExportForm = ref(false)
const showDeleteForm = ref(false)

// Add method for scrolling to security section
function scrollToSecuritySection() {
  const securitySection = document.querySelector('[data-section="security"]')
  if (securitySection) {
    securitySection.scrollIntoView({ behavior: 'smooth' })
  }
  showEmailForm.value = true
}

// Methods
function resizeBio() {
  nextTick(() => {
    if (bioTextarea.value) {
      bioTextarea.value.style.height = 'auto'
      bioTextarea.value.style.height = bioTextarea.value.scrollHeight + 'px'
    }
  })
}

async function loadProfile() {
  try {
    isLoading.value = true
    const res = await useSanctumFetch(`${config.public.apiBase}/account/profile`)
    const data = res.data

    form.name = data.name ?? ''
    form.gender = data.gender ?? ''
    form.dob = data.dob ?? ''
    form.bio = data.bio ?? ''

    // Store original data for change detection
    originalData.value = {
      name: data.name ?? '',
      gender: data.gender ?? '',
      dob: data.dob ?? '',
      bio: data.bio ?? ''
    }

    nextTick(() => {
      resizeBio()
    })
  } catch (err: any) {
    if ($toast && $toast.error) {
      $toast.error('Error', 'Failed to load profile data')
    }
  } finally {
    isLoading.value = false
  }
}

function clearFormErrors() {
  form.errors = {}
}

async function submitBasicInfo() {
  clearFormErrors()
  try {
    await form.submit({ only: ['name', 'gender', 'dob'] })

    // Update original data
    originalData.value.name = form.name
    originalData.value.gender = form.gender
    originalData.value.dob = form.dob

    if ($toast && $toast.success) {
      $toast.success('Success', 'Profile information updated successfully')
    }

    await refreshUser()
  } catch (err: any) {
    if ($toast && $toast.error) {
      $toast.error('Error', err?.data?.message || 'Failed to update profile')
    }
  }
}

async function submitBio() {
  clearFormErrors()
  try {
    await form.submit({ only: ['bio'] })

    // Update original data
    originalData.value.bio = form.bio

    if ($toast && $toast.success) {
      $toast.success('Success', 'Bio updated successfully')
    }

    await refreshUser()
  } catch (err: any) {
    if ($toast && $toast.error) {
      $toast.error('Error', err?.data?.message || 'Failed to update bio')
    }
  }
}

// Watchers
watch(() => form.bio, resizeBio)

// Lifecycle
onMounted(async () => {
  await loadProfile()

  if (process.client && gsap) {
    gsap.to([orb1.value, orb2.value, orb3.value], {
      rotation: 360,
      duration: 20,
      repeat: -1,
      ease: 'none'
    })
  }
})
</script>
