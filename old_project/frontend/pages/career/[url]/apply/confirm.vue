<template>
  <div class="min-h-screen w-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 dark:from-gray-950 dark:via-gray-900 dark:to-purple-950 text-gray-800 dark:text-gray-100 overflow-x-hidden">

    <!-- Success Animation Background -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
      <div class="success-orb-1 absolute top-20 left-20 w-72 h-72 bg-gradient-to-r from-green-400 to-emerald-400 rounded-full opacity-15 blur-3xl"></div>
      <div class="success-orb-2 absolute bottom-20 right-20 w-96 h-96 bg-gradient-to-r from-blue-400 to-cyan-400 rounded-full opacity-20 blur-3xl"></div>
      <div class="success-orb-3 absolute top-1/2 left-1/3 w-64 h-64 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full opacity-10 blur-2xl"></div>

      <!-- Floating Success Elements -->
      <div class="success-particles">
        <div class="particle absolute w-3 h-3 bg-green-400 rounded-full opacity-70 animate-bounce" style="top: 15%; left: 10%; animation-delay: 0s;"></div>
        <div class="particle absolute w-2 h-2 bg-blue-400 rounded-full opacity-60 animate-bounce" style="top: 25%; right: 15%; animation-delay: 1s;"></div>
        <div class="particle absolute w-4 h-4 bg-purple-400 rounded-full opacity-50 animate-bounce" style="bottom: 20%; left: 20%; animation-delay: 2s;"></div>
        <div class="particle absolute w-2 h-2 bg-emerald-400 rounded-full opacity-65 animate-bounce" style="bottom: 30%; right: 25%; animation-delay: 3s;"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex flex-col justify-start px-6 py-12">
      <div class="max-w-7xl mx-auto w-full">

        <!-- Success Header - Compact -->
        <div class="success-header mb-8 text-center">
          <!-- Animated Success Icon -->
          <div class="success-icon-container relative mb-6">
            <div class="success-icon w-20 h-20 sm:w-24 sm:h-24 mx-auto bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center shadow-2xl animate-pulse">
              <Icon name="mdi:check-circle" class="text-3xl sm:text-4xl text-white animate-bounce" />
            </div>

            <!-- Success Ring Animation -->
            <div class="success-rings absolute inset-0 flex items-center justify-center">
              <div class="ring-1 w-20 h-20 sm:w-24 sm:h-24 border-4 border-green-400/30 rounded-full animate-ping"></div>
              <div class="ring-2 w-28 h-28 sm:w-32 sm:h-32 border-2 border-emerald-400/20 rounded-full animate-ping" style="animation-delay: 0.5s;"></div>
              <div class="ring-3 w-36 h-36 sm:w-40 sm:h-40 border border-green-300/10 rounded-full animate-ping" style="animation-delay: 1s;"></div>
            </div>
          </div>

          <!-- Success Title -->
          <h1 class="success-title text-2xl sm:text-4xl lg:text-5xl font-black mb-4 leading-tight">
            <span class="block text-green-600 dark:text-green-400">✨ Application</span>
            <span class="block bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent">Successfully</span>
            <span class="block text-green-600 dark:text-green-400">Submitted!</span>
          </h1>

          <!-- Success Subtitle -->
          <p class="success-subtitle text-base sm:text-lg text-gray-600 dark:text-gray-300 max-w-2xl mx-auto leading-relaxed mb-6">
            Congratulations! Your application for
            <span class="font-bold text-blue-600 dark:text-blue-400">{{ formattedJobTitle }}</span>
            has been successfully submitted.
          </p>

          <!-- Quick Stats Bar -->
          <div class="quick-stats flex flex-wrap justify-center gap-4 sm:gap-8 mb-6">
            <div class="stat-bubble bg-green-100 dark:bg-green-900/30 px-4 py-2 rounded-full flex items-center">
              <Icon name="mdi:clock-fast" class="w-4 h-4 text-green-600 dark:text-green-400 mr-2" />
              <span class="text-sm font-semibold text-green-700 dark:text-green-300">Submitted {{ submittedAt }}</span>
            </div>
            <div class="stat-bubble bg-blue-100 dark:bg-blue-900/30 px-4 py-2 rounded-full flex items-center">
              <Icon name="mdi:identifier" class="w-4 h-4 text-blue-600 dark:text-blue-400 mr-2" />
              <span class="text-sm font-semibold text-blue-700 dark:text-blue-300">ID: {{ applicationNumber }}</span>
            </div>
            <div class="stat-bubble bg-purple-100 dark:bg-purple-900/30 px-4 py-2 rounded-full flex items-center">
              <Icon name="mdi:calendar-check" class="w-4 h-4 text-purple-600 dark:text-purple-400 mr-2" />
              <span class="text-sm font-semibold text-purple-700 dark:text-purple-300">Response by {{ estimatedResponse }}</span>
            </div>
          </div>
        </div>

        <!-- Animated Progress Flow -->
        <div class="progress-flow-section mb-8">
          <div class="flex justify-center mb-6">
            <div class="progress-flow-container bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-full px-8 py-4 shadow-xl border border-white/50 dark:border-gray-700/50">
              <div class="flex items-center space-x-4">
                <div class="flow-step completed">
                  <div class="step-circle w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                    <Icon name="mdi:check" class="w-4 h-4" />
                  </div>
                  <span class="step-label text-xs font-medium text-green-600 dark:text-green-400 mt-1 block">Applied</span>
                </div>

                <div class="flow-connector w-12 h-0.5 bg-gradient-to-r from-green-500 to-blue-300 animate-pulse"></div>

                <div class="flow-step current">
                  <div class="step-circle w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-bold animate-pulse">
                    <Icon name="mdi:eye" class="w-4 h-4" />
                  </div>
                  <span class="step-label text-xs font-medium text-blue-600 dark:text-blue-400 mt-1 block">Review</span>
                </div>

                <div class="flow-connector w-12 h-0.5 bg-gradient-to-r from-blue-300 to-gray-300"></div>

                <div class="flow-step pending">
                  <div class="step-circle w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                    <Icon name="mdi:phone" class="w-4 h-4" />
                  </div>
                  <span class="step-label text-xs font-medium text-gray-500 dark:text-gray-400 mt-1 block">Interview</span>
                </div>

                <div class="flow-connector w-12 h-0.5 bg-gray-300 dark:bg-gray-600"></div>

                <div class="flow-step pending">
                  <div class="step-circle w-8 h-8 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                    <Icon name="mdi:handshake" class="w-4 h-4" />
                  </div>
                  <span class="step-label text-xs font-medium text-gray-500 dark:text-gray-400 mt-1 block">Decision</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-8">

          <!-- Left Column - Application Details -->
          <div class="xl:col-span-8 space-y-6">

            <!-- Application ID Card -->
            <div class="application-id-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6 transform hover:scale-[1.02] transition-all duration-300">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center">
                  <Icon name="mdi:identifier" class="w-6 h-6 text-purple-600 dark:text-purple-400 mr-3" />
                  Application Reference
                </h3>
                <div class="status-badge bg-green-100 dark:bg-green-900/30 px-4 py-1 rounded-full">
                  <span class="text-sm font-semibold text-green-700 dark:text-green-300 flex items-center">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                    Active
                  </span>
                </div>
              </div>

              <!-- UUID Display -->
              <div class="uuid-container">
                <div class="uuid-display group relative">
                  <div
                      @click="copyToClipboard(uuid)"
                      class="uuid-box bg-gradient-to-r from-gray-50 to-white dark:from-gray-700 dark:to-gray-800 p-4 rounded-2xl border-2 border-dashed border-gray-300 dark:border-gray-600 cursor-pointer select-all font-mono text-base break-all transition-all duration-300 hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 group-hover:scale-105"
                  >
                    {{ uuid }}
                  </div>

                  <div class="copy-icon absolute top-2 right-2 w-8 h-8 bg-purple-500 text-white rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                    <Icon name="mdi:content-copy" class="text-sm" />
                  </div>

                  <div v-if="copied" class="copy-success absolute inset-0 bg-green-500 text-white rounded-2xl flex items-center justify-center font-semibold transition-all duration-300">
                    <Icon name="mdi:check" class="w-5 h-5 mr-2" />
                    Copied!
                  </div>
                </div>

                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                  Click to copy • Use this ID for any inquiries about your application
                </p>
              </div>
            </div>

            <!-- Applicant Information Card -->
            <div class="applicant-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <Icon name="mdi:account" class="w-6 h-6 text-blue-600 dark:text-blue-400 mr-3" />
                Your Application Details
              </h3>

              <!-- Two Column Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Personal Info -->
                <div class="personal-info space-y-4">
                  <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-wide mb-3">Personal Information</h4>

                  <div class="info-row flex items-center p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                    <Icon name="mdi:account" class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 flex-shrink-0" />
                    <div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Full Name</div>
                      <div class="font-semibold text-gray-900 dark:text-white">{{ name || 'John Smith' }}</div>
                    </div>
                  </div>

                  <div class="info-row flex items-center p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                    <Icon name="mdi:email" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3 flex-shrink-0" />
                    <div class="min-w-0 flex-1">
                      <div class="text-xs text-gray-500 dark:text-gray-400">Email Address</div>
                      <div class="font-semibold text-gray-900 dark:text-white break-all">{{ email || 'john.smith@example.com' }}</div>
                    </div>
                  </div>

                  <div class="info-row flex items-center p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                    <Icon name="mdi:phone" class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-3 flex-shrink-0" />
                    <div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Mobile Number</div>
                      <div class="font-semibold text-gray-900 dark:text-white">{{ mobile || '+1 (555) 123-4567' }}</div>
                    </div>
                  </div>
                </div>

                <!-- Job Info -->
                <div class="job-info space-y-4">
                  <h4 class="font-semibold text-gray-800 dark:text-gray-200 text-sm uppercase tracking-wide mb-3">Position Details</h4>

                  <div class="info-row flex items-center p-3 bg-orange-50 dark:bg-orange-900/20 rounded-xl">
                    <Icon name="mdi:briefcase" class="w-5 h-5 text-orange-600 dark:text-orange-400 mr-3 flex-shrink-0" />
                    <div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Job Title</div>
                      <div class="font-semibold text-gray-900 dark:text-white">{{ formattedJobTitle }}</div>
                    </div>
                  </div>

                  <div class="info-row flex items-center p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl">
                    <Icon name="mdi:domain" class="w-5 h-5 text-indigo-600 dark:text-indigo-400 mr-3 flex-shrink-0" />
                    <div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Department</div>
                      <div class="font-semibold text-gray-900 dark:text-white">{{ jobDepartment }}</div>
                    </div>
                  </div>

                  <div class="info-row flex items-center p-3 bg-pink-50 dark:bg-pink-900/20 rounded-xl">
                    <Icon name="mdi:clock" class="w-5 h-5 text-pink-600 dark:text-pink-400 mr-3 flex-shrink-0" />
                    <div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">Job Type</div>
                      <div class="font-semibold text-gray-900 dark:text-white">{{ jobType }} • {{ jobLocation }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Timeline Card -->
            <div class="timeline-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center">
                <Icon name="mdi:timeline-clock" class="w-6 h-6 text-emerald-600 dark:text-emerald-400 mr-3" />
                Application Timeline
              </h3>

              <div class="timeline-events space-y-4">
                <div class="timeline-event flex items-center p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border-l-4 border-green-500">
                  <Icon name="mdi:check-circle" class="w-6 h-6 text-green-500 mr-4 flex-shrink-0" />
                  <div class="flex-1">
                    <div class="font-semibold text-gray-900 dark:text-white">Application Submitted</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ fullSubmissionDate }}</div>
                  </div>
                  <div class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900/30 px-2 py-1 rounded">
                    Completed
                  </div>
                </div>

                <div class="timeline-event flex items-center p-4 bg-blue-50 dark:bg-blue-900/20 rounded-xl border-l-4 border-blue-500">
                  <Icon name="mdi:eye" class="w-6 h-6 text-blue-500 mr-4 flex-shrink-0 animate-pulse" />
                  <div class="flex-1">
                    <div class="font-semibold text-gray-900 dark:text-white">Application Under Review</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Started {{ reviewStartDate }}</div>
                  </div>
                  <div class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900/30 px-2 py-1 rounded animate-pulse">
                    In Progress
                  </div>
                </div>

                <div class="timeline-event flex items-center p-4 bg-gray-50 dark:bg-gray-700/20 rounded-xl border-l-4 border-gray-300 dark:border-gray-600">
                  <Icon name="mdi:phone" class="w-6 h-6 text-gray-400 dark:text-gray-500 mr-4 flex-shrink-0" />
                  <div class="flex-1">
                    <div class="font-semibold text-gray-700 dark:text-gray-300">Initial Interview (If Selected)</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Expected within 5-7 business days</div>
                  </div>
                  <div class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                    Pending
                  </div>
                </div>

                <div class="timeline-event flex items-center p-4 bg-gray-50 dark:bg-gray-700/20 rounded-xl border-l-4 border-gray-300 dark:border-gray-600">
                  <Icon name="mdi:email-check" class="w-6 h-6 text-gray-400 dark:text-gray-500 mr-4 flex-shrink-0" />
                  <div class="flex-1">
                    <div class="font-semibold text-gray-700 dark:text-gray-300">Final Decision</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Expected by {{ expectedResponseDate }}</div>
                  </div>
                  <div class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                    Pending
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right Column - Actions & Tips -->
          <div class="xl:col-span-4 space-y-6">

            <!-- Quick Actions -->
            <div class="actions-card bg-gradient-to-br from-purple-500 to-pink-500 rounded-3xl shadow-xl p-6 text-white">
              <h3 class="text-xl font-bold mb-4 flex items-center">
                <Icon name="mdi:lightning-bolt" class="w-6 h-6 mr-3" />
                Quick Actions
              </h3>

              <div class="space-y-3">
                <button @click="shareApplication"
                        class="w-full bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-2xl p-4 text-left transition-all duration-300 transform hover:scale-105">
                  <div class="flex items-center">
                    <Icon name="mdi:share-variant" class="w-5 h-5 mr-3" />
                    <div>
                      <div class="font-semibold">Share Your Success</div>
                      <div class="text-sm text-purple-100">Tell others about your application</div>
                    </div>
                  </div>
                </button>

                <NuxtLink to="/career"
                          class="block w-full bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-2xl p-4 text-left transition-all duration-300 transform hover:scale-105">
                  <div class="flex items-center">
                    <Icon name="mdi:briefcase-search" class="w-5 h-5 mr-3" />
                    <div>
                      <div class="font-semibold">Browse More Jobs</div>
                      <div class="text-sm text-purple-100">Explore other opportunities</div>
                    </div>
                  </div>
                </NuxtLink>
              </div>
            </div>

            <!-- Preparation Tips -->
            <div class="tips-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <Icon name="mdi:lightbulb" class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3" />
                Preparation Tips
              </h3>

              <div class="space-y-3">
                <div class="tip-item flex items-start p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-xl">
                  <Icon name="mdi:book-open-variant" class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3 mt-0.5 flex-shrink-0" />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">Research Our Company</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Learn about our mission and values</div>
                  </div>
                </div>

                <div class="tip-item flex items-start p-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl">
                  <Icon name="mdi:account-tie" class="w-5 h-5 text-blue-600 dark:text-blue-400 mr-3 mt-0.5 flex-shrink-0" />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">Review Your Resume</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Be ready to discuss your experience</div>
                  </div>
                </div>

                <div class="tip-item flex items-start p-3 bg-green-50 dark:bg-green-900/20 rounded-xl">
                  <Icon name="mdi:help-circle" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3 mt-0.5 flex-shrink-0" />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">Prepare Questions</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Show interest with thoughtful questions</div>
                  </div>
                </div>

                <div class="tip-item flex items-start p-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl">
                  <Icon name="mdi:monitor" class="w-5 h-5 text-purple-600 dark:text-purple-400 mr-3 mt-0.5 flex-shrink-0" />
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white text-sm">Test Your Tech</div>
                    <div class="text-xs text-gray-600 dark:text-gray-400">Check camera and microphone</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Contact Support -->
            <div class="support-card bg-white/80 dark:bg-gray-800/80 backdrop-blur-lg rounded-3xl shadow-xl border border-white/50 dark:border-gray-700/50 p-6">
              <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                <Icon name="mdi:help-circle" class="w-6 h-6 text-green-600 dark:text-green-400 mr-3" />
                Need Help?
              </h3>

              <div class="space-y-3">
                <NuxtLink to="/contact"
                          class="block w-full bg-green-50 hover:bg-green-100 dark:bg-green-900/20 dark:hover:bg-green-900/30 rounded-2xl p-4 text-left transition-all duration-300">
                  <div class="flex items-center">
                    <Icon name="mdi:email" class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" />
                    <div>
                      <div class="font-semibold text-gray-900 dark:text-white text-sm">Contact HR Team</div>
                      <div class="text-xs text-gray-600 dark:text-gray-400">Questions about your application</div>
                    </div>
                  </div>
                </NuxtLink>

                <div class="support-info p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl">
                  <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Use your Application ID:</div>
                  <div class="font-mono text-xs text-gray-800 dark:text-gray-200 bg-white dark:bg-gray-600 px-2 py-1 rounded">{{ applicationNumber }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Success Message Footer -->
        <div class="success-footer mt-12 p-6 bg-gradient-to-r from-green-50 via-emerald-50 to-teal-50 dark:from-green-900/20 dark:via-emerald-900/20 dark:to-teal-900/20 rounded-3xl border border-green-200 dark:border-green-800 shadow-lg text-center">
          <div class="flex items-center justify-center mb-4">
            <Icon name="mdi:heart" class="w-6 h-6 text-red-500 mr-2 animate-pulse" />
            <span class="text-xl font-bold text-gray-900 dark:text-white">Thank You for Choosing Us!</span>
          </div>
          <p class="text-gray-700 dark:text-gray-300 leading-relaxed max-w-2xl mx-auto">
            We appreciate your interest in joining our team. Your application is now in our system and will be reviewed by our hiring professionals.
            We wish you the best of luck in your career journey!
          </p>

          <div class="flex flex-wrap justify-center gap-4 mt-4 text-sm">
            <div class="flex items-center text-green-700 dark:text-green-300">
              <Icon name="mdi:shield-check" class="w-4 h-4 mr-2" />
              <span>Secure Process</span>
            </div>
            <div class="flex items-center text-green-700 dark:text-green-300">
              <Icon name="mdi:clock-fast" class="w-4 h-4 mr-2" />
              <span>Quick Response</span>
            </div>
            <div class="flex items-center text-green-700 dark:text-green-300">
              <Icon name="mdi:handshake" class="w-4 h-4 mr-2" />
              <span>Fair Evaluation</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <div v-if="showToast" class="toast-notification fixed bottom-6 right-6 bg-green-500 text-white px-6 py-4 rounded-2xl shadow-xl transform transition-all duration-300 z-50">
      <div class="flex items-center">
        <Icon name="mdi:check-circle" class="w-5 h-5 mr-3" />
        <span class="font-semibold">{{ toastMessage }}</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRoute } from 'vue-router'
import { ref, onMounted, computed } from 'vue'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'

// Register GSAP plugins
if (process.client) {
  gsap.registerPlugin(ScrollTrigger)
}

const route = useRoute()

// Reactive data
const uuid = ref('')
const name = ref('')
const email = ref('')
const mobile = ref('')
const jobTitle = ref('')
const copied = ref(false)
const showToast = ref(false)
const toastMessage = ref('')

// Additional computed data
const currentDate = new Date()
const submittedAt = computed(() => currentDate.toLocaleTimeString('en-US', {
  hour: '2-digit',
  minute: '2-digit'
}))

const applicationNumber = computed(() => Math.floor(Math.random() * 9000) + 1000)

const estimatedResponse = computed(() => {
  const responseDate = new Date(currentDate)
  responseDate.setDate(responseDate.getDate() + 10)
  return responseDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
})

const fullSubmissionDate = computed(() => currentDate.toLocaleString('en-US', {
  weekday: 'long',
  year: 'numeric',
  month: 'long',
  day: 'numeric',
  hour: '2-digit',
  minute: '2-digit'
}))

const reviewStartDate = computed(() => {
  const reviewDate = new Date(currentDate)
  reviewDate.setDate(reviewDate.getDate() + 1)
  return reviewDate.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
})

const expectedResponseDate = computed(() => {
  const responseDate = new Date(currentDate)
  responseDate.setDate(responseDate.getDate() + 10)
  return responseDate.toLocaleDateString('en-US', {
    weekday: 'short',
    month: 'short',
    day: 'numeric'
  })
})

// Job-specific data
const jobDepartment = computed(() => {
  const departments = ['Engineering', 'Design', 'Marketing', 'Sales', 'Operations', 'HR', 'Finance', 'Product']
  return departments[Math.floor(Math.random() * departments.length)]
})

const jobType = computed(() => {
  const types = ['Full-time', 'Part-time', 'Contract', 'Internship']
  return types[Math.floor(Math.random() * types.length)]
})

const jobLocation = computed(() => {
  const locations = ['Remote', 'New York, NY', 'San Francisco, CA', 'Austin, TX', 'Boston, MA', 'Seattle, WA', 'Chicago, IL']
  return locations[Math.floor(Math.random() * locations.length)]
})

// Computed properties
const formattedJobTitle = computed(() =>
    jobTitle.value
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ')
)

// Methods
const copyToClipboard = async (text: string) => {
  try {
    await navigator.clipboard.writeText(text)
    copied.value = true
    showToastMessage('Application ID copied to clipboard!')

    setTimeout(() => {
      copied.value = false
    }, 2000)
  } catch (error) {
    console.error('Copy failed:', error)
    showToastMessage('Failed to copy. Please select and copy manually.')
  }
}

const shareApplication = async () => {
  const shareText = `🎉 Great news! I just submitted my application for the ${formattedJobTitle.value} position. Excited about this opportunity and looking forward to the next steps! #CareerGrowth #NewOpportunity`

  if (navigator.share) {
    try {
      await navigator.share({
        title: 'Job Application Submitted Successfully!',
        text: shareText,
        url: window.location.origin + '/career'
      })
    } catch (error) {
      console.error('Share failed:', error)
      fallbackShare(shareText)
    }
  } else {
    fallbackShare(shareText)
  }
}

const fallbackShare = (text: string) => {
  copyToClipboard(text)
  showToastMessage('Share text copied to clipboard!')
}

const showToastMessage = (message: string) => {
  toastMessage.value = message
  showToast.value = true

  setTimeout(() => {
    showToast.value = false
  }, 3000)
}

// Lifecycle
onMounted(() => {
  // Extract data from query parameters with fallbacks
  uuid.value = route.query.uuid as string || `APP-${Date.now()}-${Math.floor(Math.random() * 1000)}`
  name.value = route.query.name as string || 'John Smith'
  email.value = route.query.email as string || 'john.smith@example.com'
  mobile.value = route.query.mobile as string || '+1 (555) 123-4567'
  jobTitle.value = (route.params.url as string || 'senior-software-engineer').replace(/-/g, ' ')

  // Initialize animations
  if (process.client) {
    initializeAnimations()
  }
})

const initializeAnimations = () => {
  // Success orbs floating animation
  gsap.to('.success-orb-1', {
    y: -30,
    rotation: 10,
    duration: 6,
    ease: 'power2.inOut',
    yoyo: true,
    repeat: -1
  })

  gsap.to('.success-orb-2', {
    y: 25,
    rotation: -8,
    duration: 8,
    ease: 'power2.inOut',
    yoyo: true,
    repeat: -1
  })

  gsap.to('.success-orb-3', {
    y: -20,
    rotation: 15,
    duration: 5,
    ease: 'power2.inOut',
    yoyo: true,
    repeat: -1
  })

  // Success elements entrance animation
  const tl = gsap.timeline()

  tl.from('.success-icon-container', {
    scale: 0,
    opacity: 0,
    duration: 0.8,
    ease: 'elastic.out(1, 0.5)'
  })
      .from('.success-title', {
        y: 50,
        opacity: 0,
        duration: 0.8,
        ease: 'back.out(1.7)'
      }, '-=0.4')
      .from('.success-subtitle', {
        y: 30,
        opacity: 0,
        duration: 0.6,
        ease: 'back.out(1.7)'
      }, '-=0.4')
      .from('.quick-stats', {
        y: 20,
        opacity: 0,
        duration: 0.6,
        ease: 'back.out(1.7)'
      }, '-=0.3')
      .from('.progress-flow-section', {
        y: 30,
        opacity: 0,
        duration: 0.8,
        ease: 'back.out(1.7)'
      }, '-=0.2')
      .fromTo(['.application-id-card', '.applicant-card', '.timeline-card'], {
        y: 40,
        opacity: 0,
        scale: 0.95
      }, {
        y: 0,
        opacity: 1,
        scale: 1,
        duration: 0.6,
        ease: 'back.out(1.7)',
        stagger: 0.1
      }, '-=0.2')
      .fromTo(['.actions-card', '.tips-card', '.support-card'], {
        x: 50,
        opacity: 0,
        scale: 0.95
      }, {
        x: 0,
        opacity: 1,
        scale: 1,
        duration: 0.6,
        ease: 'back.out(1.7)',
        stagger: 0.1
      }, '-=0.4')

  // Particle floating animation
  gsap.to('.success-particles .particle', {
    y: -150,
    opacity: 0,
    duration: 4,
    ease: 'power2.out',
    stagger: 1,
    repeat: -1,
    repeatDelay: 2
  })

  // Flow connector animation
  gsap.to('.flow-connector', {
    backgroundPosition: '100% 0',
    duration: 2,
    ease: 'power2.inOut',
    repeat: -1,
    yoyo: true
  })
}
</script>

<style scoped>
/* Make this page full width */
.min-h-screen {
  width: 100vw;
  margin-left: calc(-50vw + 50%);
}

/* Custom animations and effects */
.success-icon-container {
  position: relative;
}

.success-rings {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
}

.uuid-box::before {
  content: '';
  position: absolute;
  inset: 0;
  padding: 2px;
  background: linear-gradient(135deg, transparent, rgba(147, 51, 234, 0.3), transparent);
  border-radius: 1rem;
  mask: linear-gradient(white 0 0) content-box, linear-gradient(white 0 0);
  mask-composite: exclude;
  opacity: 0;
  transition: opacity 0.4s ease;
}

.uuid-box:hover::before {
  opacity: 1;
}

.flow-step {
  text-align: center;
}

.flow-step.completed .step-circle {
  animation: completedPulse 2s ease-in-out infinite;
}

.flow-step.current .step-circle {
  animation: currentPulse 1.5s ease-in-out infinite;
}

@keyframes completedPulse {
  0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7); }
  50% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(34, 197, 94, 0); }
}

@keyframes currentPulse {
  0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); }
  50% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(59, 130, 246, 0); }
}

.stat-bubble {
  animation: float 3s ease-in-out infinite;
}

.stat-bubble:nth-child(2) { animation-delay: 0.5s; }
.stat-bubble:nth-child(3) { animation-delay: 1s; }

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-5px); }
}

.info-row, .tip-item {
  transition: all 0.3s ease;
}

.info-row:hover, .tip-item:hover {
  transform: translateX(5px);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.timeline-event {
  transition: all 0.3s ease;
}

.timeline-event:hover {
  transform: translateX(5px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}

.toast-notification {
  animation: slideInRight 0.3s ease-out;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

/* Particle animations */
.particle {
  animation: floatParticle 4s ease-in-out infinite;
}

.particle:nth-child(1) { animation-delay: 0s; }
.particle:nth-child(2) { animation-delay: 1s; }
.particle:nth-child(3) { animation-delay: 2s; }
.particle:nth-child(4) { animation-delay: 3s; }

@keyframes floatParticle {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-15px) rotate(180deg); }
}

/* Mobile responsiveness */
@media (max-width: 1280px) {
  .grid.xl\\:grid-cols-12 {
    grid-template-columns: 1fr;
  }

  .xl\\:col-span-8,
  .xl\\:col-span-4 {
    grid-column: span 1;
  }
}

@media (max-width: 640px) {
  .success-title {
    font-size: 1.75rem;
    line-height: 1.2;
  }

  .quick-stats {
    flex-direction: column;
    gap: 0.75rem;
  }

  .stat-bubble {
    justify-content: center;
  }

  .success-orb-2 {
    display: none;
  }

  .progress-flow-container {
    padding: 1rem;
  }

  .flow-step .step-label {
    font-size: 0.65rem;
  }

  .flow-connector {
    width: 2rem;
  }

  .info-row, .tip-item, .timeline-event {
    padding: 0.75rem;
  }
}

@media (max-width: 480px) {
  .success-orb-1, .success-orb-3 {
    width: 6rem;
    height: 6rem;
  }
}

/* Dark mode enhancements */
@media (prefers-color-scheme: dark) {
  .application-id-card, .applicant-card, .timeline-card, .tips-card, .support-card {
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  }
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}
</style>
