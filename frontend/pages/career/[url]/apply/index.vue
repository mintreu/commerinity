<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-800 dark:text-gray-100 overflow-x-hidden">
    <GlobalLoader />

    <!-- Guest Login Required Section -->
    <section v-if="!isLoggedIn" class="min-h-screen flex items-center justify-center px-6 py-20">
      <div class="login-required-card max-w-lg mx-auto bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 text-center transform hover:scale-105 transition-all duration-300">
        <!-- Icon -->
        <div class="login-icon w-20 h-20 mx-auto mb-6 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center">
          <Icon name="mdi:lock" class="text-3xl text-white" />
        </div>

        <!-- Content -->
        <h2 class="text-3xl font-bold mb-4 text-gray-900 dark:text-white">Login Required</h2>
        <p class="text-lg text-gray-600 dark:text-gray-300 mb-8 leading-relaxed">
          You need to be logged in to apply for this position. Please log in to your account or create a new one to continue with your application.
        </p>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <NuxtLink to="/auth/login"
                    class="login-btn group px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-bold rounded-2xl shadow-xl hover:shadow-blue-500/25 transition-all duration-300 transform hover:scale-105">
            <Icon name="mdi:login" class="inline w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" />
            Login to Account
          </NuxtLink>
          <NuxtLink to="/auth/register"
                    class="register-btn group px-8 py-4 bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 font-bold rounded-2xl border-2 border-blue-600 dark:border-blue-400 hover:bg-blue-50 dark:hover:bg-gray-600 transition-all duration-300 transform hover:scale-105">
            <Icon name="mdi:account-plus" class="inline w-5 h-5 mr-2 group-hover:rotate-12 transition-transform duration-300" />
            Create Account
          </NuxtLink>
        </div>
      </div>
    </section>

    <!-- Authenticated Application Form -->
    <section v-else class="application-section py-20 px-6">
      <div class="max-w-4xl mx-auto">

        <!-- Application Header -->
        <div v-if="job" class="application-header text-center mb-16">
          <div class="header-badge inline-flex items-center px-6 py-3 rounded-full bg-gradient-to-r from-green-500/10 to-blue-500/10 border border-green-200 dark:border-green-800 backdrop-blur-sm mb-6">
            <Icon name="mdi:file-document-edit" class="w-5 h-5 mr-3 text-green-600 dark:text-green-400" />
            <span class="font-semibold text-green-700 dark:text-green-300">Job Application</span>
          </div>

          <h1 class="application-title text-3xl sm:text-5xl font-black mb-4">
            <span class="text-gray-900 dark:text-white">Apply for</span><br>
            <span class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">{{ job.name }}</span>
          </h1>

          <p class="text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            Complete your application below. All required fields are marked with an asterisk (*).
            Take your time to provide accurate information.
          </p>

          <!-- Progress Indicator -->
          <div class="progress-container mt-8 max-w-2xl mx-auto">
            <div class="progress-bar bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
              <div class="progress-fill bg-gradient-to-r from-blue-500 to-purple-500 h-full rounded-full transition-all duration-300"
                   :style="{ width: `${formProgress}%` }"></div>
            </div>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">{{ formProgress }}% Complete</p>
          </div>
        </div>

        <!-- Application Form -->
        <form v-if="job" @submit.prevent="finalSubmit" class="application-form space-y-12">

          <!-- Profile Details Section -->
          <div class="form-section">
            <div class="section-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 transform hover:scale-[1.02] transition-all duration-300">

              <!-- Section Header -->
              <div class="section-header mb-8">
                <div class="flex items-center mb-4">
                  <div class="section-icon w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl flex items-center justify-center text-white text-xl mr-4">
                    <Icon name="mdi:account" />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Profile Details</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Personal information as per your ID proofs</p>
                  </div>
                </div>
                <div class="section-divider h-1 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full w-20"></div>
              </div>

              <!-- Form Fields -->
              <div class="form-grid space-y-6">
                <!-- Full Name -->
                <div class="form-group">
                  <label class="form-label flex items-center font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    <Icon name="mdi:account" class="w-4 h-4 mr-2 text-blue-500" />
                    Full Name *
                  </label>
                  <div class="form-input-container relative">
                    <input v-model="form.name"
                           type="text"
                           class="form-input locked-input"
                           placeholder="e.g. Rahul Sharma"
                           required
                           disabled />
                    <Icon name="mdi:lock" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
                  </div>
                  <p class="form-hint">As per official records. Locked to your profile information.</p>
                </div>

                <!-- Guardian Name -->
                <div class="form-group">
                  <label class="form-label flex items-center font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    <Icon name="mdi:account-supervisor" class="w-4 h-4 mr-2 text-green-500" />
                    Guardian / Parent Name *
                  </label>
                  <input v-model="form.guardian_name"
                         type="text"
                         class="form-input"
                         placeholder="e.g. Suresh Sharma"
                         required />
                  <p class="form-hint">Enter father, mother, or legal guardian's name for verification.</p>
                </div>

                <!-- Email & Mobile -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="form-group">
                    <label class="form-label flex items-center font-semibold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:email" class="w-4 h-4 mr-2 text-red-500" />
                      Email *
                    </label>
                    <div class="form-input-container relative">
                      <input v-model="form.email"
                             type="email"
                             class="form-input locked-input"
                             placeholder="e.g. rahul@gmail.com"
                             required
                             disabled />
                      <Icon name="mdi:lock" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                    <p class="form-hint">Primary email from your account. Locked for security.</p>
                  </div>

                  <div class="form-group">
                    <label class="form-label flex items-center font-semibold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:phone" class="w-4 h-4 mr-2 text-blue-500" />
                      Mobile *
                    </label>
                    <div class="form-input-container relative">
                      <input v-model="form.mobile"
                             type="tel"
                             class="form-input locked-input"
                             placeholder="10-digit mobile number"
                             required
                             disabled />
                      <Icon name="mdi:lock" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                    <p class="form-hint">Your registered mobile number. Update in profile if needed.</p>
                  </div>
                </div>

                <!-- Gender & DOB -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="form-group">
                    <label class="form-label flex items-center font-semibold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:gender-male-female" class="w-4 h-4 mr-2 text-purple-500" />
                      Gender *
                    </label>
                    <div class="form-input-container relative">
                      <select v-model="form.gender" class="form-input locked-input" required disabled>
                        <option disabled value="">Select gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                      </select>
                      <Icon name="mdi:lock" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                    <p class="form-hint">Gender as per your official records.</p>
                  </div>

                  <div class="form-group">
                    <label class="form-label flex items-center font-semibold text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:calendar" class="w-4 h-4 mr-2 text-orange-500" />
                      Date of Birth *
                    </label>
                    <div class="form-input-container relative">
                      <input v-model="form.dob"
                             type="date"
                             class="form-input locked-input"
                             required
                             disabled />
                      <Icon name="mdi:lock" class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400" />
                    </div>
                    <p class="form-hint">Date of birth as per your account profile.</p>
                  </div>
                </div>

                <!-- Edit Profile Link -->
                <div class="flex justify-end">
                  <NuxtLink to="/dashboard/my-account"
                            class="edit-profile-btn group inline-flex items-center px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-700 dark:hover:text-blue-300 transition-all duration-300 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-xl">
                    <Icon name="mdi:pencil" class="w-4 h-4 mr-2 group-hover:rotate-12 transition-transform duration-300" />
                    Edit Profile Information
                  </NuxtLink>
                </div>
              </div>
            </div>
          </div>

          <!-- Address Details Section -->
          <div class="form-section">
            <div class="section-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 transform hover:scale-[1.02] transition-all duration-300">

              <div class="section-header mb-8">
                <div class="flex items-center mb-4">
                  <div class="section-icon w-12 h-12 bg-gradient-to-r from-green-500 to-teal-500 rounded-2xl flex items-center justify-center text-white text-xl mr-4">
                    <Icon name="mdi:map-marker" />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Address Details</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Select your current residential address</p>
                  </div>
                </div>
                <div class="section-divider h-1 bg-gradient-to-r from-green-500 to-teal-500 rounded-full w-20"></div>
              </div>

              <div class="form-group">
                <label class="form-label flex items-center font-semibold text-gray-700 dark:text-gray-300 mb-3">
                  <Icon name="mdi:home" class="w-4 h-4 mr-2 text-green-500" />
                  Select Address *
                </label>
                <select v-model="form.address_uuid" class="form-input" required>
                  <option disabled value="">Choose your address</option>
                  <option v-for="addr in sortedUserAddresses" :key="addr.uuid" :value="addr.uuid">
                    {{ addr.address_1 }} ({{ addr.postal_code }})
                  </option>
                </select>
                <p class="form-hint">Select your preferred address from saved locations. Add new addresses in your profile.</p>
              </div>
            </div>
          </div>

          <!-- Skills Section -->
          <div class="form-section">
            <div class="section-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 transform hover:scale-[1.02] transition-all duration-300">

              <div class="section-header mb-8">
                <div class="flex items-center mb-4">
                  <div class="section-icon w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center text-white text-xl mr-4">
                    <Icon name="mdi:brain" />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Skills & Expertise</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">List your professional skills relevant to this position</p>
                  </div>
                </div>
                <div class="section-divider h-1 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full w-20"></div>
              </div>

              <div class="skills-container space-y-6">
                <div v-for="(skill, index) in form.skills" :key="`skill-${index}-${skillsKey}`"
                     class="skill-item bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-600">
                  <div class="skill-header flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                      <Icon name="mdi:star" class="w-4 h-4 mr-2 text-yellow-500" />
                      Skill {{ index + 1 }}
                    </h4>
                    <div class="skill-number w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                      {{ index + 1 }}
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group">
                      <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Skill Name</label>
                      <input type="text"
                             v-model="skill.skill"
                             placeholder="e.g. JavaScript, Excel, Communication"
                             class="form-input"
                             required />
                    </div>
                    <div class="md:col-span-2 form-group">
                      <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Description & Experience</label>
                      <textarea v-model="skill.description"
                                rows="3"
                                placeholder="Describe your proficiency and experience with this skill..."
                                class="form-input"
                                required></textarea>
                    </div>
                  </div>
                </div>
              </div>

              <div class="skills-actions flex flex-wrap justify-end gap-4 mt-8">
                <button type="button"
                        @click.stop.prevent="handleAddSkill"
                        class="action-btn add-btn group px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105 relative z-10">
                  <Icon name="mdi:plus" class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-300" />
                  Add Skill
                </button>
                <button type="button"
                        @click.stop.prevent="handleRemoveSkill"
                        :disabled="form.skills.length <= 1"
                        :class="[
                          'action-btn remove-btn group px-6 py-3 font-semibold rounded-xl transition-all duration-300 transform relative z-10',
                          form.skills.length <= 1
                            ? 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-50'
                            : 'bg-gradient-to-r from-red-500 to-pink-500 text-white hover:shadow-lg hover:scale-105'
                        ]">
                  <Icon name="mdi:minus" class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-300" />
                  Remove Last
                </button>
              </div>
            </div>
          </div>

          <!-- Education Section -->
          <div class="form-section">
            <div class="section-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 transform hover:scale-[1.02] transition-all duration-300">

              <div class="section-header mb-8">
                <div class="flex items-center mb-4">
                  <div class="section-icon w-12 h-12 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-2xl flex items-center justify-center text-white text-xl mr-4">
                    <Icon name="mdi:school" />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Education Background</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Your educational qualifications and certifications</p>
                  </div>
                </div>
                <div class="section-divider h-1 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full w-20"></div>
              </div>

              <div class="education-container space-y-6">
                <div v-for="(edu, index) in form.educations" :key="`education-${index}-${educationsKey}`"
                     class="education-item bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-600">
                  <div class="education-header flex items-center justify-between mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                      <Icon name="mdi:certificate" class="w-4 h-4 mr-2 text-blue-500" />
                      Education {{ index + 1 }}
                    </h4>
                    <div class="education-number w-8 h-8 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                      {{ index + 1 }}
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="form-group">
                      <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Degree/Level</label>
                      <select v-model="edu.degree" class="form-input" required>
                        <option value="">Select Degree</option>
                        <option v-for="d in degrees" :key="d" :value="d">{{ d }}</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Institution</label>
                      <input type="text"
                             v-model="edu.institution"
                             placeholder="e.g. Delhi Public School"
                             class="form-input"
                             required />
                    </div>
                    <div class="form-group">
                      <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Year</label>
                      <input type="number"
                             v-model.number="edu.year"
                             placeholder="e.g. 2023"
                             class="form-input"
                             required />
                    </div>
                  </div>
                </div>
              </div>

              <div class="education-actions flex flex-wrap justify-end gap-4 mt-8">
                <button type="button"
                        @click.stop.prevent="handleAddEducation"
                        class="action-btn add-btn group px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105 relative z-10">
                  <Icon name="mdi:plus" class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-300" />
                  Add Qualification
                </button>
                <button type="button"
                        @click.stop.prevent="handleRemoveEducation"
                        :disabled="form.educations.length <= 1"
                        :class="[
                          'action-btn remove-btn group px-6 py-3 font-semibold rounded-xl transition-all duration-300 transform relative z-10',
                          form.educations.length <= 1
                            ? 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-50'
                            : 'bg-gradient-to-r from-red-500 to-pink-500 text-white hover:shadow-lg hover:scale-105'
                        ]">
                  <Icon name="mdi:minus" class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-300" />
                  Remove Last
                </button>
              </div>
            </div>
          </div>

          <!-- Experience Section -->
          <div class="form-section">
            <div class="section-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 transform hover:scale-[1.02] transition-all duration-300">

              <div class="section-header mb-8">
                <div class="flex items-center mb-4">
                  <div class="section-icon w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl flex items-center justify-center text-white text-xl mr-4">
                    <Icon name="mdi:briefcase" />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Work Experience</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Professional and volunteer experience (optional)</p>
                  </div>
                </div>
                <div class="section-divider h-1 bg-gradient-to-r from-orange-500 to-red-500 rounded-full w-20"></div>
              </div>

              <!-- Experience Toggle -->
              <div class="experience-toggle mb-8">
                <label class="toggle-label flex items-center cursor-pointer group">
                  <div class="toggle-container relative mr-4">
                    <input type="checkbox"
                           v-model="hasExperience"
                           class="toggle-input sr-only" />
                    <div class="toggle-bg bg-gray-300 dark:bg-gray-600 w-14 h-7 rounded-full shadow-inner transition-colors duration-300"
                         :class="{ 'bg-gradient-to-r from-green-500 to-emerald-500': hasExperience }"></div>
                    <div class="toggle-dot absolute left-1 top-1 bg-white w-5 h-5 rounded-full shadow transition-transform duration-300"
                         :class="{ 'transform translate-x-7': hasExperience }"></div>
                  </div>
                  <span class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors duration-300">
                    I have work experience
                  </span>
                </label>
              </div>

              <!-- Experience Forms -->
              <div v-if="hasExperience" class="experience-container space-y-6">
                <div v-for="(experience, index) in form.experiences" :key="`experience-${index}-${experiencesKey}`"
                     class="experience-item bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-gray-200 dark:border-gray-600">
                  <div class="experience-header flex items-center justify-between mb-6">
                    <h4 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center">
                      <Icon name="mdi:office-building" class="w-4 h-4 mr-2 text-orange-500" />
                      Experience {{ index + 1 }}
                    </h4>
                    <div class="experience-number w-8 h-8 bg-gradient-to-r from-orange-500 to-red-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                      {{ index + 1 }}
                    </div>
                  </div>

                  <div class="space-y-4">
                    <div class="form-group">
                      <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Company/Organization</label>
                      <input v-model="experience.company_name"
                             type="text"
                             placeholder="e.g. Infosys, Local NGO, Freelance"
                             class="form-input" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div class="form-group">
                        <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Start Date</label>
                        <input v-model="experience.start"
                               type="date"
                               class="form-input" />
                      </div>
                      <div class="form-group">
                        <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">End Date</label>
                        <input v-model="experience.end"
                               type="date"
                               class="form-input" />
                      </div>
                    </div>

                    <div class="form-group">
                      <label class="form-label font-medium text-gray-700 dark:text-gray-300 mb-2">Role & Responsibilities</label>
                      <textarea v-model="experience.experience"
                                rows="4"
                                placeholder="Describe your role, responsibilities, and key achievements..."
                                class="form-input"></textarea>
                    </div>
                  </div>
                </div>

                <div class="experience-actions flex flex-wrap justify-end gap-4 mt-8">
                  <button type="button"
                          @click.stop.prevent="handleAddExperience"
                          class="action-btn add-btn group px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-500 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105 relative z-10">
                    <Icon name="mdi:plus" class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-300" />
                    Add Experience
                  </button>
                  <button type="button"
                          @click.stop.prevent="handleRemoveExperience"
                          :disabled="form.experiences.length <= 1"
                          :class="[
                            'action-btn remove-btn group px-6 py-3 font-semibold rounded-xl transition-all duration-300 transform relative z-10',
                            form.experiences.length <= 1
                              ? 'bg-gray-400 dark:bg-gray-600 cursor-not-allowed opacity-50'
                              : 'bg-gradient-to-r from-red-500 to-pink-500 text-white hover:shadow-lg hover:scale-105'
                          ]">
                    <Icon name="mdi:minus" class="w-4 h-4 mr-2 group-hover:rotate-90 transition-transform duration-300" />
                    Remove Last
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Agreement Section -->
          <div class="form-section">
            <div class="section-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 transform hover:scale-[1.02] transition-all duration-300">

              <div class="section-header mb-8">
                <div class="flex items-center mb-4">
                  <div class="section-icon w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-2xl flex items-center justify-center text-white text-xl mr-4">
                    <Icon name="mdi:file-check" />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Terms & Preferences</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Review and confirm your application preferences</p>
                  </div>
                </div>
                <div class="section-divider h-1 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-full w-20"></div>
              </div>

              <div class="agreements-container space-y-6">
                <!-- Relocation Preference -->
                <div class="agreement-item bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-2xl p-6 border border-blue-200 dark:border-blue-700">
                  <label class="checkbox-label flex items-start cursor-pointer group">
                    <div class="checkbox-container relative mr-4 mt-1">
                      <input type="checkbox"
                             v-model="form.relocation"
                             class="checkbox-input sr-only" />
                      <div class="checkbox-bg w-6 h-6 border-2 border-blue-300 dark:border-blue-600 rounded-md bg-white dark:bg-gray-700 transition-all duration-300 flex items-center justify-center"
                           :class="{ 'bg-gradient-to-r from-blue-500 to-indigo-500 border-blue-500': form.relocation }">
                        <Icon v-if="form.relocation" name="mdi:check" class="w-4 h-4 text-white" />
                      </div>
                    </div>
                    <div>
                      <span class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300">
                        Willing to Relocate
                      </span>
                      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        I am comfortable relocating for this position if required by the company.
                      </p>
                    </div>
                  </label>
                </div>

                <!-- Terms & Conditions -->
                <div class="agreement-item bg-gradient-to-r from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20 rounded-2xl p-6 border border-green-200 dark:border-green-700">
                  <label class="checkbox-label flex items-start cursor-pointer group">
                    <div class="checkbox-container relative mr-4 mt-1">
                      <input type="checkbox"
                             v-model="form.agree_terms"
                             class="checkbox-input sr-only" />
                      <div class="checkbox-bg w-6 h-6 border-2 border-green-300 dark:border-green-600 rounded-md bg-white dark:bg-gray-700 transition-all duration-300 flex items-center justify-center"
                           :class="{ 'bg-gradient-to-r from-green-500 to-emerald-500 border-green-500': form.agree_terms }">
                        <Icon v-if="form.agree_terms" name="mdi:check" class="w-4 h-4 text-white" />
                      </div>
                    </div>
                    <div>
                      <span class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-green-600 dark:group-hover:text-green-400 transition-colors duration-300">
                        I agree to the Terms & Conditions *
                      </span>
                      <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        I have read and agree to all terms and conditions of this application process. This is mandatory.
                      </p>
                    </div>
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Reference Section -->
          <div class="form-section">
            <div class="section-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-2xl border border-white/50 dark:border-gray-700/50 p-8 transform hover:scale-[1.02] transition-all duration-300">

              <div class="section-header mb-8">
                <div class="flex items-center mb-4">
                  <div class="section-icon w-12 h-12 bg-gradient-to-r from-pink-500 to-rose-500 rounded-2xl flex items-center justify-center text-white text-xl mr-4">
                    <Icon name="mdi:account-group" />
                  </div>
                  <div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Reference Information</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Professional reference who can verify your background (optional)</p>
                  </div>
                </div>
                <div class="section-divider h-1 bg-gradient-to-r from-pink-500 to-rose-500 rounded-full w-20"></div>
              </div>

              <div class="reference-container space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div class="form-group">
                    <label class="form-label flex items-center font-medium text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:account" class="w-4 h-4 mr-2 text-pink-500" />
                      Reference Name
                    </label>
                    <input type="text"
                           v-model="form.reference_name"
                           class="form-input"
                           placeholder="e.g. Dr. Neha Verma, Manager" />
                    <p class="form-hint">Full name and designation of your reference person.</p>
                  </div>

                  <div class="form-group">
                    <label class="form-label flex items-center font-medium text-gray-700 dark:text-gray-300 mb-3">
                      <Icon name="mdi:phone" class="w-4 h-4 mr-2 text-rose-500" />
                      Reference Contact
                    </label>
                    <input type="text"
                           v-model="form.reference_contact"
                           class="form-input"
                           placeholder="e.g. 9999912345 or neha@example.com" />
                    <p class="form-hint">Phone number or email for reference verification.</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Section -->
          <div class="submit-section">
            <div class="submit-card bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-3xl shadow-2xl p-8 text-white text-center">
              <div class="submit-content space-y-6">
                <div class="submit-icon w-16 h-16 mx-auto bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center">
                  <Icon name="mdi:send" class="text-3xl text-white" />
                </div>

                <div>
                  <h3 class="text-2xl font-bold mb-2">Ready to Submit?</h3>
                  <p class="text-blue-100">
                    Please review all information before submitting your application.
                    <span v-if="job.is_payable" class="block mt-2 font-semibold text-yellow-200">
                      Application fee: ₹{{ job.fees }} (non-refundable)
                    </span>
                  </p>
                </div>

                <button type="submit"
                        class="submit-btn group px-12 py-6 bg-white text-blue-600 font-black text-xl rounded-2xl shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105">
                  <Icon name="mdi:rocket-launch" class="inline w-6 h-6 mr-3 group-hover:rotate-12 transition-transform duration-300" />
                  <span v-if="!job.is_payable">Submit Application</span>
                  <span v-else>Submit & Pay ₹{{ job.fees }}</span>
                  <Icon name="mdi:arrow-right" class="inline w-6 h-6 ml-3 group-hover:translate-x-2 transition-transform duration-300" />
                </button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </section>

    <!-- Enhanced Success Modal -->
    <div v-if="showSuccessModal" class="success-modal fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm">
      <div class="modal-container bg-white dark:bg-gray-900 rounded-3xl shadow-2xl w-full max-w-lg mx-6 p-8 transform scale-100 transition-all duration-300">

        <!-- Success Icon -->
        <div class="success-icon w-20 h-20 mx-auto mb-6 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center animate-pulse">
          <Icon name="mdi:check-circle" class="text-4xl text-white" />
        </div>

        <!-- Success Content -->
        <div class="success-content text-center space-y-4">
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Application Submitted!</h3>
          <p class="text-gray-600 dark:text-gray-300">
            Your application has been successfully submitted. We'll review it and get back to you soon.
          </p>

          <div class="application-id bg-gray-50 dark:bg-gray-800 rounded-2xl p-4">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Application ID:</p>
            <p class="font-mono font-bold text-lg text-blue-600 dark:text-blue-400">{{ lastApplicationId }}</p>
          </div>

          <!-- Payment Processing -->
          <div v-if="pendingRedirectUrl" class="payment-processing flex items-center justify-center text-sm text-gray-600 dark:text-gray-400 space-x-2">
            <div class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
            <span>Redirecting to payment gateway...</span>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="modal-actions flex flex-col sm:flex-row gap-4 justify-center mt-8">
          <button @click="cancelAutoRedirect"
                  class="modal-btn-secondary px-6 py-3 text-gray-600 dark:text-gray-400 font-semibold rounded-xl border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-800 transition-all duration-300">
            Stay Here
          </button>
          <a v-if="pendingRedirectUrl"
             :href="pendingRedirectUrl"
             target="_blank"
             rel="noopener"
             class="modal-btn-primary px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-semibold rounded-xl hover:shadow-lg transition-all duration-300 transform hover:scale-105">
            Continue to Payment
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, watch, nextTick } from "vue";
import { useRoute } from "vue-router";
import {
  useSanctum,
  useSanctumFetch,
  useRuntimeConfig,
  useCurrentUser,
  useToast,
} from "#imports";
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Register GSAP plugins
if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

const isLoading = useState("pageLoading", () => false);
isLoading.value = true;

const route = useRoute();
const toast = useToast();
const job = ref<Job | null>(null);

// Modal + redirect state
const showSuccessModal = ref(false);
const pendingRedirectUrl = ref<string | null>(null);
const lastApplicationId = ref<string | null>(null);
let redirectTimer: ReturnType<typeof setTimeout> | null = null;

// Keys for force re-render when needed
const skillsKey = ref(0);
const educationsKey = ref(0);
const experiencesKey = ref(0);

interface Job {
  name: string
  url: string
  role: string
  type: string
  location: string
  thumbnail: string
  pdf?: string | string[]
  description: string
  vacancy: number
  open_date: string
  close_date: string
  is_payable: boolean
  fees?: string
  uuid?: string
}

interface User {
  id: number
  email: string
  name: string
  mobile: string
  gender: string
  dob: string
}

const user = useCurrentUser<User>();
const { isLoggedIn } = useSanctum();
const config = useRuntimeConfig();

const hasExperience = ref(false);

const degrees = [
  "Class-8", "Class-9", "Class-10", "Class-12",
  "Diploma", "Bachelor", "Master", "Other",
];

// Form model
const form = reactive({
  name: "",
  email: "",
  mobile: "",
  gender: "",
  dob: "",
  guardian_name: "",
  address_uuid: "",
  educations: [{ degree: "", institution: "", year: "" as number | string }],
  skills: [{ skill: "", description: "" }],
  experiences: [] as Array<{ company_name: string; start: string; end: string; experience: string }>,
  relocation: false,
  agree_terms: false,
  reference_name: "",
  reference_contact: "",
});

// Form progress calculation
const formProgress = computed(() => {
  let completed = 0;
  let total = 0;

  // Profile fields (required, locked)
  total += 5;
  if (form.name) completed++;
  if (form.email) completed++;
  if (form.mobile) completed++;
  if (form.gender) completed++;
  if (form.dob) completed++;

  // Guardian name
  total += 1;
  if (form.guardian_name) completed++;

  // Address
  total += 1;
  if (form.address_uuid) completed++;

  // Skills
  total += 1;
  if (form.skills.some(skill => skill.skill && skill.description)) completed++;

  // Education
  total += 1;
  if (form.educations.some(edu => edu.degree && edu.institution && edu.year)) completed++;

  // Terms agreement
  total += 1;
  if (form.agree_terms) completed++;

  return Math.round((completed / total) * 100);
});

// Data fetching functions
async function fetchJob() {
  try {
    const apiUrl = `${config.public.apiBase}/recruitment/${route.params.url}`;
    const response = await useSanctumFetch(apiUrl);
    job.value = response?.data || null;
  } catch {
    toast.error({ title: "Error", message: "❌ Error fetching job details", timeout: 3000, position: "topRight" });
    job.value = null;
  }
}

const userAddresses = ref<any[]>([]);
const sortedUserAddresses = computed(() => [...(userAddresses.value || [])].sort((a, b) => a.priority - b.priority));

async function fetchUserAddress() {
  try {
    const apiUrl = `${config.public.apiBase}/account/addresses`;
    const response = await useSanctumFetch(apiUrl);
    userAddresses.value = response?.data || [];

    const defaultHome = userAddresses.value.find((a) => a.type?.toLowerCase() === "home" && a.default === true);
    if (defaultHome) {
      form.address_uuid = defaultHome.uuid;
    }
  } catch {
    toast.error({ title: "Error", message: "❌ Error fetching addresses", timeout: 3000, position: "topRight" });
    userAddresses.value = [];
  }
}

function populateFormWithUser() {
  if (isLoggedIn.value && user.value) {
    form.name = user.value.name || "";
    form.email = user.value.email || "";
    form.mobile = user.value.mobile || "";
    form.gender = user.value.gender || "";
    form.dob = user.value.dob || "";
  }
}

async function initializePage() {
  try {
    await fetchJob();
    await fetchUserAddress();
    populateFormWithUser();
  } catch {
    toast.error({ title: "Error", message: "❌ Error during initialization", timeout: 3000, position: "topRight" });
  } finally {
    isLoading.value = false;

    // Initialize animations after data loads
    if (process.client) {
      initializeAnimations();
    }
  }
}

onMounted(async () => {
  await initializePage();
});

// Experience toggle behavior
watch(hasExperience, (enabled) => {
  if (!enabled) {
    form.experiences = [];
    experiencesKey.value++;
  } else if (enabled && form.experiences.length === 0) {
    form.experiences.push({ company_name: "", start: "", end: "", experience: "" });
    experiencesKey.value++;
  }
});

// Enhanced Form Action Handlers with proper event handling and debouncing
let actionTimeout: ReturnType<typeof setTimeout> | null = null;

const handleAddSkill = async () => {
  // Clear any pending timeout
  if (actionTimeout) {
    clearTimeout(actionTimeout);
  }

  // Debounce the action
  actionTimeout = setTimeout(() => {
    try {
      form.skills.push({ skill: "", description: "" });
      skillsKey.value++; // Force re-render

      toast.success({
        title: "Skill Added",
        message: `Skill ${form.skills.length} added successfully`,
        timeout: 2000,
        position: "topRight"
      });

      // Scroll to the new skill after DOM update
      nextTick(() => {
        const skillItems = document.querySelectorAll('.skill-item');
        const lastItem = skillItems[skillItems.length - 1];
        if (lastItem) {
          lastItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    } catch (error) {
      console.error('Error adding skill:', error);
      toast.error({ title: "Error", message: "Failed to add skill", timeout: 3000, position: "topRight" });
    }
  }, 100);
};

const handleRemoveSkill = async () => {
  if (actionTimeout) {
    clearTimeout(actionTimeout);
  }

  actionTimeout = setTimeout(() => {
    try {
      if (form.skills.length > 1) {
        form.skills.pop();
        skillsKey.value++; // Force re-render
        toast.success({
          title: "Skill Removed",
          message: "Last skill removed successfully",
          timeout: 2000,
          position: "topRight"
        });
      } else {
        toast.error({ title: "Cannot Remove", message: "⚠️ At least one skill is required", timeout: 3000, position: "topRight" });
      }
    } catch (error) {
      console.error('Error removing skill:', error);
      toast.error({ title: "Error", message: "Failed to remove skill", timeout: 3000, position: "topRight" });
    }
  }, 100);
};

const handleAddEducation = async () => {
  if (actionTimeout) {
    clearTimeout(actionTimeout);
  }

  actionTimeout = setTimeout(() => {
    try {
      form.educations.push({ degree: "", institution: "", year: "" });
      educationsKey.value++; // Force re-render

      toast.success({
        title: "Education Added",
        message: `Education ${form.educations.length} added successfully`,
        timeout: 2000,
        position: "topRight"
      });

      nextTick(() => {
        const educationItems = document.querySelectorAll('.education-item');
        const lastItem = educationItems[educationItems.length - 1];
        if (lastItem) {
          lastItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    } catch (error) {
      console.error('Error adding education:', error);
      toast.error({ title: "Error", message: "Failed to add education", timeout: 3000, position: "topRight" });
    }
  }, 100);
};

const handleRemoveEducation = async () => {
  if (actionTimeout) {
    clearTimeout(actionTimeout);
  }

  actionTimeout = setTimeout(() => {
    try {
      if (form.educations.length > 1) {
        form.educations.pop();
        educationsKey.value++; // Force re-render
        toast.success({
          title: "Education Removed",
          message: "Last education removed successfully",
          timeout: 2000,
          position: "topRight"
        });
      } else {
        toast.error({ title: "Cannot Remove", message: "⚠️ At least one education is required", timeout: 3000, position: "topRight" });
      }
    } catch (error) {
      console.error('Error removing education:', error);
      toast.error({ title: "Error", message: "Failed to remove education", timeout: 3000, position: "topRight" });
    }
  }, 100);
};

const handleAddExperience = async () => {
  if (actionTimeout) {
    clearTimeout(actionTimeout);
  }

  actionTimeout = setTimeout(() => {
    try {
      form.experiences.push({ company_name: "", start: "", end: "", experience: "" });
      experiencesKey.value++; // Force re-render

      toast.success({
        title: "Experience Added",
        message: `Experience ${form.experiences.length} added successfully`,
        timeout: 2000,
        position: "topRight"
      });

      nextTick(() => {
        const experienceItems = document.querySelectorAll('.experience-item');
        const lastItem = experienceItems[experienceItems.length - 1];
        if (lastItem) {
          lastItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
      });
    } catch (error) {
      console.error('Error adding experience:', error);
      toast.error({ title: "Error", message: "Failed to add experience", timeout: 3000, position: "topRight" });
    }
  }, 100);
};

const handleRemoveExperience = async () => {
  if (actionTimeout) {
    clearTimeout(actionTimeout);
  }

  actionTimeout = setTimeout(() => {
    try {
      if (form.experiences.length > 1) {
        form.experiences.pop();
        experiencesKey.value++; // Force re-render
        toast.success({
          title: "Experience Removed",
          message: "Last experience removed successfully",
          timeout: 2000,
          position: "topRight"
        });
      } else {
        toast.error({ title: "Cannot Remove", message: "⚠️ At least one experience is required", timeout: 3000, position: "topRight" });
      }
    } catch (error) {
      console.error('Error removing experience:', error);
      toast.error({ title: "Error", message: "Failed to remove experience", timeout: 3000, position: "topRight" });
    }
  }, 100);
};

// Submit functionality
const finalSubmit = async () => {
  // Validate educations year numeric
  for (const e of form.educations) {
    if (e.year === "" || Number.isNaN(Number(e.year))) {
      toast.error({ title: "Invalid Education Year", message: "Education year must be a valid number.", timeout: 3000, position: "topRight" });
      return;
    }
  }

  // Experiences handling
  let validExperiences: typeof form.experiences = [];
  if (hasExperience.value) {
    validExperiences = form.experiences.filter((exp) =>
        exp.company_name?.trim() &&
        exp.start?.trim() &&
        exp.end?.trim() &&
        exp.experience?.trim()
    );

    if (form.experiences.length > 0 && validExperiences.length === 0) {
      toast.error({ title: "Experience Incomplete", message: "Please complete all fields in at least one experience or turn off the experience section.", timeout: 3000, position: "topRight" });
      return;
    }

    const invalidDate = validExperiences.some((exp) => new Date(exp.end) < new Date(exp.start));
    if (invalidDate) {
      toast.error({ title: "Experience Dates", message: "Experience end date must be after or equal to start date.", timeout: 3000, position: "topRight" });
      return;
    }
  }

  const payload: Record<string, any> = {
    job_uuid: job.value?.uuid,
    name: form.name,
    email: form.email,
    mobile: form.mobile,
    gender: form.gender,
    dob: form.dob,
    guardian_name: form.guardian_name || null,
    address_uuid: form.address_uuid,
    educations: form.educations.map((e) => ({
      degree: e.degree,
      institution: e.institution,
      year: Number(e.year),
    })),
    skills: form.skills.map((s) => ({
      skill: s.skill,
      description: s.description,
    })),
    relocation: !!form.relocation,
    agree_terms: form.agree_terms,
    reference_name: form.reference_name || null,
    reference_contact: form.reference_contact || null,
  };

  if (hasExperience.value && validExperiences.length > 0) {
    payload.experiences = validExperiences;
  }

  try {
    const apiUrl = `${config.public.apiBase}/recruitment/${route.params.url}/apply`;
    const res: any = await useSanctumFetch(apiUrl, {
      method: "POST",
      body: payload,
    });

    const rsp = res?.data || {};
    if (rsp?.status) {
      toast.success({ title: "Success", message: rsp?.message || "Application submitted successfully.", timeout: 2000, position: "topRight" });
    }

    if (rsp?.redirect && rsp?.redirect_url) {
      lastApplicationId.value = rsp?.application_id || null;
      pendingRedirectUrl.value = rsp.redirect_url;
      showSuccessModal.value = true;

      // auto-redirect after ~3s
      redirectTimer && clearTimeout(redirectTimer);
      redirectTimer = setTimeout(() => {
        if (pendingRedirectUrl.value) {
          window.location.assign(pendingRedirectUrl.value);
        }
      }, 3000);
    }
  } catch (err: any) {
    const firstError =
        err?.data?.errors
            ? String(Object.values(err.data.errors).flat()[0])
            : (err?.data?.message || "Something went wrong. Please try again.");
    toast.error({ title: "Error", message: firstError, timeout: 5000, position: "topRight" });
  }
};

function cancelAutoRedirect() {
  if (redirectTimer) clearTimeout(redirectTimer);
  redirectTimer = null;
  showSuccessModal.value = false;
}

// Animations
const initializeAnimations = () => {
  // Header animation
  gsap.fromTo('.application-header',
      { y: 50, opacity: 0 },
      { y: 0, opacity: 1, duration: 1, ease: 'back.out(1.7)' }
  )

  // Section animations
  gsap.fromTo('.form-section',
      { y: 80, opacity: 0, scale: 0.95 },
      {
        y: 0,
        opacity: 1,
        scale: 1,
        duration: 0.8,
        ease: 'back.out(1.7)',
        stagger: 0.2,
        scrollTrigger: {
          trigger: '.application-form',
          start: 'top 80%',
          toggleActions: 'play none none reverse'
        }
      }
  )

  // Progress bar animation
  gsap.fromTo('.progress-fill',
      { width: '0%' },
      {
        width: `${formProgress.value}%`,
        duration: 1.5,
        ease: 'power2.out',
        delay: 0.5
      }
  )
}
</script>

<style scoped>
/* Form Styling */
.form-input {
  @apply w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-4 focus:ring-blue-500/20 transition-all duration-300;
}

.form-input:focus {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(59, 130, 246, 0.15);
}

.locked-input {
  @apply bg-gray-100 dark:bg-gray-600 cursor-not-allowed opacity-75;
}

.form-label {
  @apply block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2;
}

.form-hint {
  @apply text-xs text-gray-500 dark:text-gray-400 mt-2 ml-1 leading-relaxed;
}

/* Section Card Effects */
.section-card {
  position: relative;
}

.section-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
  transition: left 0.8s;
  pointer-events: none;
  z-index: 0;
}

.section-card:hover::before {
  left: 100%;
}

/* Action Button Effects - Fixed positioning */
.action-btn {
  position: relative;
  z-index: 10;
  cursor: pointer;
  user-select: none;
  -webkit-tap-highlight-color: transparent;
}

.action-btn:disabled {
  cursor: not-allowed;
  transform: none !important;
}

.action-btn:not(:disabled)::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
  transition: left 0.5s;
  pointer-events: none;
  z-index: -1;
}

.action-btn:not(:disabled):hover::before {
  left: 100%;
}

/* Ensure buttons are clickable */
.skills-actions, .education-actions, .experience-actions {
  position: relative;
  z-index: 20;
}

.skills-actions button,
.education-actions button,
.experience-actions button {
  position: relative;
  z-index: 21;
  pointer-events: auto;
}

/* Toggle Switch Styling */
.toggle-input:checked + .toggle-bg {
  @apply bg-gradient-to-r from-green-500 to-emerald-500;
}

.toggle-input:checked + .toggle-bg + .toggle-dot {
  @apply transform translate-x-7;
}

/* Checkbox Styling */
.checkbox-input:checked + .checkbox-bg {
  @apply bg-gradient-to-r border-current;
}

/* Submit Button Effects */
.submit-btn {
  position: relative;
}

.submit-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.2), transparent);
  transition: left 0.6s;
  pointer-events: none;
}

.submit-btn:hover::before {
  left: 100%;
}

/* Modal Animation */
.success-modal {
  animation: modalFadeIn 0.3s ease-out;
}

.modal-container {
  animation: modalSlideIn 0.4s ease-out;
}

@keyframes modalFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: scale(0.9) translateY(-20px);
  }
  to {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

/* Skill/Education/Experience Item Effects */
.skill-item,
.education-item,
.experience-item {
  position: relative;
  overflow: hidden;
}

.skill-item::after,
.education-item::after,
.experience-item::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  background: linear-gradient(90deg, #8b5cf6, #ec4899, #06b6d4);
  transform: scaleX(0);
  transition: transform 0.4s ease;
  border-radius: 1rem 1rem 0 0;
  pointer-events: none;
}

.skill-item:hover::after,
.education-item:hover::after,
.experience-item:hover::after {
  transform: scaleX(1);
}

/* Progress Bar Animation */
.progress-fill {
  transition: width 0.5s ease-in-out;
}

/* Mobile Responsiveness */
@media (max-width: 640px) {
  .application-title {
    font-size: 2.5rem;
    line-height: 1.2;
  }

  .section-card {
    padding: 1.5rem;
  }

  .form-input {
    padding: 0.75rem 1rem;
  }

  .action-btn {
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
  }
}

/* Dark Mode Enhancements */
@media (prefers-color-scheme: dark) {
  .section-card, .modal-container {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Fix for button interaction issues */
.section-card * {
  pointer-events: auto;
}

/* Smooth Animations */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Fix hover transform conflicts */
.section-card:hover {
  transform: scale(1.02);
}

.section-card:hover .action-btn:hover {
  transform: scale(1.05);
}
</style>
