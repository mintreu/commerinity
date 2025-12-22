<template>
  <div class="min-h-screen p-6 ">
    <!-- Loader -->
    <div v-if="isLoading" class="flex flex-col items-center justify-center min-h-[60vh]">
      <div class="w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
      <p class="mt-4 text-blue-600 font-medium">Please wait... Loading profile & data</p>
    </div>

    <!-- Form -->
    <div v-else class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded shadow p-6">
      <h1 class="text-2xl font-semibold mb-6 text-gray-800 dark:text-white">Complete Onboarding</h1>

      <form @submit.prevent="handleSubmit" novalidate class="space-y-8">

        <!-- Profile -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded">
          <h2 class="text-lg font-medium mb-4 text-gray-700 dark:text-gray-200">Profile Information</h2>


          <fieldset class="border border-gray-300 dark:border-gray-600 rounded-md p-4">
            <legend class="px-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Your Profile Info</legend>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full name</label>
                <input
                    v-model.trim="form.profile.name"
                    @blur="validateField('name')"
                    :class="inputClass(errors.profile.name)"
                    placeholder="Your full name"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                />
                <span v-if="errors.profile.name" class="text-red-600 text-sm mt-1 block">{{ errors.profile.name }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input
                    v-model.trim="form.profile.email"
                    @blur="validateField('email')"
                    :class="inputClass(errors.profile.email)"
                    placeholder="you@mail.com"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                />
                <span v-if="errors.profile.email" class="text-red-600 text-sm mt-1 block">{{ errors.profile.email }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Mobile</label>
                <input
                    v-model.trim="form.profile.mobile"
                    @blur="validateField('mobile')"
                    :class="inputClass(errors.profile.mobile)"
                    placeholder="9800555600"
                    disabled
                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"

                />
                <span v-if="errors.profile.mobile" class="text-red-600 text-sm mt-1 block">{{ errors.profile.mobile }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gender</label>
                <select
                    v-model="form.profile.gender"
                    @change="validateField('gender')"
                    :class="inputClass(errors.profile.gender)"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                >
                  <option value="" disabled>Select gender</option>
                  <option value="female">Female</option>
                  <option value="male">Male</option>
                  <option value="other">Other</option>
                </select>
                <span v-if="errors.profile.gender" class="text-red-600 text-sm mt-1 block">{{ errors.profile.gender }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date of Birth</label>
                <input
                    type="date"
                    v-model="form.profile.dob"
                    @blur="validateField('dob')"
                    :class="inputClass(errors.profile.dob)"
                    class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                />
                <span v-if="errors.profile.dob" class="text-red-600 text-sm mt-1 block">{{ errors.profile.dob }}</span>
              </div>
            </div>
          </fieldset>



        </section>

        <!-- Address -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded">
          <h2 class="text-lg font-medium mb-4 text-gray-700 dark:text-gray-200">Address Information</h2>

          <!-- Postal Code Fieldset -->
          <fieldset class="border border-gray-300 dark:border-gray-600 rounded-md p-4 mb-6">
            <legend class="px-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Postal Code</legend>

            <div class="flex items-center space-x-4 mt-2">
              <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>
                <div class="flex gap-1 items-center flex-row h-12">
                  <input
                      v-model.trim="form.address.postal_code"
                      maxlength="6"
                      minlength="6"
                      @input="validateField('postal_code')"
                      class="grow h-full w-full border rounded px-3 py-2 bg-white dark:bg-gray-700 dark:text-white"
                      placeholder="6-digit PIN"
                  />
                  <button
                      type="button"
                      @click="fetchAddressFromPostalCode"
                      :disabled="form.address.postal_code.length !== 6"
                      class="p-1 h-full rounded bg-blue-600 text-white disabled:opacity-50"
                  >
                    🔍
                  </button>
                </div>
                <span v-if="errors.address.postal_code" class="text-red-600 text-sm mt-1 block">{{ errors.address.postal_code }}</span>
                <p class="text-xs text-gray-500 mt-1">Use postal code to autofill city/state.</p>
              </div>
            </div>
          </fieldset>

          <!-- Address Details Fieldset -->
          <fieldset class="border border-gray-300 dark:border-gray-600 rounded-md p-4">
            <legend class="px-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Address Details</legend>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Street Address</label>
                <input
                    v-model.trim="form.address.address_1"
                    @blur="validateField('address_1')"
                    :class="inputClass(errors.address.address_1)"
                    placeholder="Address line 1"
                    class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700 dark:text-white"
                />
                <span v-if="errors.address.address_1" class="text-red-600 text-sm mt-1 block">{{ errors.address.address_1 }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Landmark</label>
                <input
                    v-model.trim="form.address.landmark"
                    placeholder="Near..."
                    class="border rounded px-3 py-2 w-full bg-white dark:bg-gray-700 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Village / Town</label>
                <input
                    v-model.trim="form.address.village"
                    placeholder="Village / Town"
                    class="border rounded px-3 py-2 w-full bg-white dark:bg-gray-700 dark:text-white"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">District / City</label>
                <select
                    v-if="districtList.length"
                    v-model="form.address.city"
                    @change="validateField('city')"
                    :class="inputClass(errors.address.city)"
                    class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700 dark:text-white"
                >
                  <option value="" disabled>Select district</option>
                  <option v-for="d in districtList" :key="d" :value="d">{{ d }}</option>
                </select>
                <div v-else class="text-sm text-gray-500">Districts loading...</div>
                <span v-if="errors.address.city" class="text-red-600 text-sm mt-1 block">{{ errors.address.city }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>
                <select
                    v-if="stateList.length"
                    id="state_code"
                    v-model="form.address.state_code"
                    @change="onStateSelect"
                    :class="inputClass(errors.address.state_code)"
                    class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700 dark:text-white"
                >
                  <option value="" disabled>Select your state</option>
                  <option v-for="s in stateList" :key="s.code" :value="s.code">{{ s.name }}</option>
                </select>
                <div v-else class="text-sm text-gray-500">States loading...</div>
                <span v-if="errors.address.state_code" class="text-red-600 text-sm mt-1 block">{{ errors.address.state_code }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Block / Municipality</label>
                <select
                    v-if="blockList.length"
                    v-model="form.address.block_id"
                    @change="validateField('block_id')"
                    :class="inputClass(errors.address.block_id)"
                    class="w-full border rounded px-3 py-2 bg-white dark:bg-gray-700 dark:text-white"
                >
                  <option value="" disabled>Select block</option>
                  <option v-for="b in blockList" :key="b" :value="b">{{ b }}</option>
                </select>
                <div v-else class="text-sm text-gray-500">Blocks loading...</div>
                <span v-if="errors.address.block_id" class="text-red-600 text-sm mt-1 block">{{ errors.address.block_id }}</span>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
                <input
                    type="text"
                    :value="country?.name || 'India'"
                    disabled
                    class="w-full border rounded px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-500"
                />
              </div>
            </div>
          </fieldset>




<!--          <div class="md:flex md:items-center md:space-x-4 mb-4">-->
<!--            <div class="flex-1">-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Postal Code</label>-->

<!--              <div class="flex gap-1 items-center flex-row h-12">-->
<!--                <input v-model.trim="form.address.postal_code" maxlength="6" minlength="6"-->
<!--                       @input="validateField('postal_code')"-->

<!--                       class="grow h-full w-full border rounded px-3 py-2 bg-white dark:bg-gray-700 dark:text-white"-->
<!--                       placeholder="6-digit PIN" />-->

<!--                <button type="button" @click="fetchAddressFromPostalCode"-->
<!--                        :disabled="form.address.postal_code.length !== 6"-->
<!--                        class="p-1 h-full rounded bg-blue-600 text-white disabled:opacity-50">-->
<!--                  🔍-->
<!--                </button>-->
<!--              </div>-->

<!--              <span v-if="errors.address.postal_code" class="text-red-600 text-sm mt-1 block">{{ errors.address.postal_code }}</span>-->
<!--              <p class="text-xs text-gray-500 mt-1">Use postal code to autofill city/state.</p>-->
<!--            </div>-->


<!--          </div>-->
<!--          &lt;!&ndash; show address fields when states/blocks ready or after autofill &ndash;&gt;-->
<!--          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">-->
<!--            <div class="md:col-span-2">-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Street Address</label>-->
<!--              <input v-model.trim="form.address.address_1" @blur="validateField('address_1')"-->
<!--                     :class="inputClass(errors.address.address_1)" placeholder="Address line 1" />-->
<!--              <span v-if="errors.address.address_1" class="text-red-600 text-sm mt-1 block">{{ errors.address.address_1 }}</span>-->
<!--            </div>-->

<!--            <div class="md:col-span-1">-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 ">Landmark</label>-->
<!--              <input v-model.trim="form.address.landmark" placeholder="Near..." class="border rounded px-3 py-2 w-full bg-white dark:bg-gray-700 dark:text-white" />-->
<!--            </div>-->

<!--            <div class="md:col-span-1">-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Village / Town</label>-->
<!--              <input v-model.trim="form.address.village" placeholder="Village / Town" class="border rounded px-3 py-2 w-full bg-white dark:bg-gray-700 dark:text-white" />-->
<!--            </div>-->

<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">District / City</label>-->
<!--              <select v-if="districtList.length" v-model="form.address.city" @change="validateField('city')" :class="inputClass(errors.address.city)">-->
<!--                <option value="" disabled>Select district</option>-->
<!--                <option v-for="d in districtList" :key="d" :value="d">{{ d }}</option>-->
<!--              </select>-->
<!--              <div v-else class="text-sm text-gray-500">Districts loading...</div>-->
<!--              <span v-if="errors.address.city" class="text-red-600 text-sm mt-1 block">{{ errors.address.city }}</span>-->
<!--            </div>-->

<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">State</label>-->

<!--              &lt;!&ndash; render select only when states are loaded &ndash;&gt;-->
<!--              <select v-if="stateList.length" id="state_code" v-model="form.address.state_code" @change="onStateSelect"-->
<!--                      :class="inputClass(errors.address.state_code)">-->
<!--                <option value="" disabled>Select your state</option>-->
<!--                <option v-for="s in stateList" :key="s.code" :value="s.code">{{ s.name }}</option>-->
<!--              </select>-->

<!--              <div v-else class="text-sm text-gray-500">States loading...</div>-->
<!--              <span v-if="errors.address.state_code" class="text-red-600 text-sm mt-1 block">{{ errors.address.state_code }}</span>-->
<!--            </div>-->

<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Block / Municipality</label>-->
<!--              <select v-if="blockList.length" v-model="form.address.block_id" @change="validateField('block_id')" :class="inputClass(errors.address.block_id)">-->
<!--                <option value="" disabled>Select block</option>-->
<!--                <option v-for="b in blockList" :key="b" :value="b">{{ b }}</option>-->
<!--              </select>-->
<!--              <div v-else class="text-sm text-gray-500">Blocks loading...</div>-->
<!--              <span v-if="errors.address.block_id" class="text-red-600 text-sm mt-1 block">{{ errors.address.block_id }}</span>-->
<!--            </div>-->

<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>-->
<!--              <input type="text" :value="country?.name || 'India'" disabled class="w-full border rounded px-3 py-2 bg-gray-200 dark:bg-gray-700 text-gray-500" />-->
<!--            </div>-->
<!--          </div>-->



        </section>

        <!-- KYC -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded">
          <h2 class="text-lg font-medium mb-4 text-gray-700 dark:text-gray-200">KYC</h2>

          <fieldset class="border border-gray-300 dark:border-gray-600 rounded-md p-4">
            <legend class="px-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Your Aadhaar Info</legend>

            <div class="md:flex md:space-x-6 items-start">
              <!-- Left side: Inputs -->
              <div class="flex-1 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Aadhaar</label>
                  <input
                      v-model.trim="form.kyc.aadhaar"
                      @blur="validateField('aadhaar')"
                      :class="inputClass(errors.kyc.aadhaar)"
                      maxlength="12"
                      class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                  />
                  <span v-if="errors.kyc.aadhaar" class="text-red-600 text-sm mt-1 block">{{ errors.kyc.aadhaar }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload Aadhaar (image/pdf)</label>
                  <input
                      type="file"
                      accept="image/*,application/pdf"
                      @change="onFileChange($event,'aadhaar')"
                      class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2 file:px-4
                 file:rounded file:border-0
                 file:text-sm file:font-semibold
                 file:bg-blue-100 file:text-blue-700
                 hover:file:bg-blue-200
                 dark:file:bg-blue-900 dark:file:text-blue-300 dark:hover:file:bg-blue-800"
                  />
                </div>
              </div>

              <!-- Right side: Preview image -->
              <div class="flex-shrink-0 mt-6 md:mt-0 w-40 h-40 border border-gray-300 dark:border-gray-600 rounded overflow-hidden bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                <template v-if="form.kyc.aadhaarImageUrl">
                  <img
                      :src="form.kyc.aadhaarImageUrl"
                      alt="aadhaar preview"
                      class="max-h-full max-w-full object-contain"
                  />
                </template>
                <template v-else>
                  <span class="text-gray-400 text-sm">No preview available</span>
                </template>
              </div>
            </div>
          </fieldset>

          <fieldset class="border border-gray-300 dark:border-gray-600 rounded-md p-4 mt-6">
            <legend class="px-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Your Pan info</legend>

            <div class="md:flex md:space-x-6 items-start">
              <!-- Left side: PAN inputs -->
              <div class="flex-1 space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">PAN</label>
                  <input
                      v-model.trim="form.kyc.pan"
                      @blur="validateField('pan')"
                      :class="inputClass(errors.kyc.pan)"
                      maxlength="10"
                      class="w-full border border-gray-300 dark:border-gray-600 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-100"
                  />
                  <span v-if="errors.kyc.pan" class="text-red-600 text-sm mt-1 block">{{ errors.kyc.pan }}</span>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Upload PAN (image/pdf)</label>
                  <input
                      type="file"
                      accept="image/*,application/pdf"
                      @change="onFileChange($event,'pan')"
                      class="block w-full text-sm text-gray-600 dark:text-gray-400 file:mr-4 file:py-2 file:px-4
                 file:rounded file:border-0
                 file:text-sm file:font-semibold
                 file:bg-blue-100 file:text-blue-700
                 hover:file:bg-blue-200
                 dark:file:bg-blue-900 dark:file:text-blue-300 dark:hover:file:bg-blue-800"
                  />
                </div>
              </div>

              <!-- Right side: PAN image preview -->
              <div class="flex-shrink-0 mt-6 md:mt-0 w-40 h-40 border border-gray-300 dark:border-gray-600 rounded overflow-hidden bg-gray-50 dark:bg-gray-800 flex items-center justify-center">
                <template v-if="form.kyc.panImageUrl">
                  <img
                      :src="form.kyc.panImageUrl"
                      alt="pan preview"
                      class="max-h-full max-w-full object-contain"
                  />
                </template>
                <template v-else>
                  <span class="text-gray-400 text-sm">No preview available</span>
                </template>
              </div>
            </div>
          </fieldset>




          <!--          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">-->
<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Aadhaar</label>-->
<!--              <input v-model.trim="form.kyc.aadhaar" @blur="validateField('aadhaar')" :class="inputClass(errors.kyc.aadhaar)" maxlength="12" />-->
<!--              <span v-if="errors.kyc.aadhaar" class="text-red-600 text-sm mt-1 block">{{ errors.kyc.aadhaar }}</span>-->
<!--            </div>-->

<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">PAN</label>-->
<!--              <input v-model.trim="form.kyc.pan" @blur="validateField('pan')" :class="inputClass(errors.kyc.pan)" maxlength="10" />-->
<!--              <span v-if="errors.kyc.pan" class="text-red-600 text-sm mt-1 block">{{ errors.kyc.pan }}</span>-->
<!--            </div>-->

<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload Aadhaar (image/pdf)</label>-->
<!--              <input type="file" accept="image/*,application/pdf" @change="onFileChange($event,'aadhaar')" />-->
<!--              <div v-if="form.kyc.aadhaarImageUrl" class="mt-2">-->
<!--                <img :src="form.kyc.aadhaarImageUrl" class="max-h-32 object-contain rounded" alt="aadhaar preview" />-->
<!--              </div>-->
<!--            </div>-->

<!--            <div>-->
<!--              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Upload PAN (image/pdf)</label>-->
<!--              <input type="file" accept="image/*,application/pdf" @change="onFileChange($event,'pan')" />-->
<!--              <div v-if="form.kyc.panImageUrl" class="mt-2">-->
<!--                <img :src="form.kyc.panImageUrl" class="max-h-32 object-contain rounded" alt="pan preview" />-->
<!--              </div>-->
<!--            </div>-->
<!--          </div>-->


        </section>

        <!-- Finish -->
        <section class="bg-gray-50 dark:bg-gray-900 p-4 rounded">
          <h2 class="text-lg font-medium mb-4 text-gray-700 dark:text-gray-200">Subscription & Terms</h2>
          <fieldset>
            <legend class="px-2 text-lg font-semibold text-gray-700 dark:text-gray-300">Subscription Options</legend>

            <!-- Radios: side-by-side on md+, stacked on mobile -->
            <div class="mt-4 flex flex-col md:flex-row items-center gap-3 md:space-x-6">
              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                    type="radio"
                    value="subscribe"
                    v-model="form.finish.subscription_type"
                    @change="validateField('subscription_type')"
                    class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-gray-700 dark:text-gray-300">Subscribe Now</span>
              </label>

              <label class="flex items-center space-x-2 cursor-pointer">
                <input
                    type="radio"
                    value="not_interested"
                    v-model="form.finish.subscription_type"
                    @change="validateField('subscription_type')"
                    class="text-blue-600 focus:ring-blue-500"
                />
                <span class="text-gray-700 dark:text-gray-300">Not Interested</span>
              </label>
            </div>
            <span v-if="errors.finish.subscription_type" class="text-red-600 text-sm mt-1 block ml-1">{{ errors.finish.subscription_type }}</span>

            <!-- Subscription details shown only if subscribe selected -->
            <div v-if="form.finish.subscription_type === 'subscribe'" class="mt-6 p-4 bg-white dark:bg-gray-800 rounded shadow-sm">
              <h3 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-2">{{ subscriptionData.name }}</h3>
              <p class="text-gray-600 dark:text-gray-300 mb-4">{{ subscriptionData.description }}</p>

              <div class="flex flex-wrap gap-4 mb-4">
                <div>
                  <span class="font-semibold text-gray-700 dark:text-gray-200">Price:</span>
                  <span class="ml-1 text-green-600 dark:text-green-400">{{ subscriptionData.price }}</span>
                </div>
                <div>
                  <span class="font-semibold text-gray-700 dark:text-gray-200">Max Team Capacity:</span>
                  <span class="ml-1">{{ subscriptionData.max_team_capacity }}</span>
                </div>
              </div>

              <div class="mb-4">
                <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-1">Benefits:</h4>
                <ul class="list-disc list-inside text-gray-600 dark:text-gray-300">
                  <li v-for="(status, benefit) in subscriptionData.benefits" :key="benefit" :class="status === 'Active' ? 'text-green-600 dark:text-green-400' : 'line-through text-red-600 dark:text-red-400'">
                    {{ benefit }} - {{ status }}
                  </li>
                </ul>
              </div>

              <div>
                <h4 class="font-semibold text-gray-700 dark:text-gray-200 mb-1">Levels:</h4>
                <ul class="list-disc list-inside text-gray-600 dark:text-gray-300">
                  <li v-for="level in subscriptionData.levels" :key="level.id">{{ level.name }}</li>
                </ul>
              </div>
            </div>

            <!-- Checkbox: below radios/details -->
            <div class="mt-6">
              <label class="inline-flex items-center cursor-pointer">
                <input
                    type="checkbox"
                    v-model="form.finish.tnc"
                    @change="validateField('tnc')"
                    class="form-checkbox h-5 w-5 text-blue-600 focus:ring-blue-500"
                />
                <span class="ml-2 text-gray-700 dark:text-gray-300">
          I agree to the <a href="/terms" class="text-blue-600 underline hover:text-blue-700">Terms</a>
        </span>
              </label>
              <span v-if="errors.finish.tnc" class="text-red-600 text-sm mt-1 block ml-1">{{ errors.finish.tnc }}</span>
            </div>
          </fieldset>
        </section>



        <!-- Submit -->
        <div class="text-center">
          <button :disabled="!isFormValid" class="px-6 py-2 rounded bg-green-600 text-white disabled:opacity-50">
            Complete Onboarding
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref, computed, onMounted, nextTick } from 'vue'
import { useSanctumFetch, useRuntimeConfig } from '#imports'

definePageMeta({ layout: 'dashboard' })

const config = useRuntimeConfig()
const isLoading = ref(true)

// form state
const form = reactive({
  profile: { name: '', email: '', mobile: '', gender: '', dob: '' },
  address: { postal_code: '', address_1: '', landmark: '', village: '', city: '', state_code: '', block_id: '' },
  kyc: { aadhaar: '', pan: '', aadhaarFile: null as File | null, aadhaarImageUrl: '', panFile: null as File | null, panImageUrl: '' },
  finish: { subscription_type: '', tnc: false }
})

// errors
const errors = reactive({
  profile: { name: '', email: '', mobile: '', gender: '', dob: '' },
  address: { postal_code: '', address_1: '', city: '', state_code: '', block_id: '' },
  kyc: { aadhaar: '', pan: '' },
  finish: { subscription_type: '', tnc: '' }
})

const subscriptionData = reactive({
  name: '',
  url: '',
  description: '',
  price: '',
  max_team_capacity: 0,
  benefits: {},
  accessibility: {},
  levels: []
})

// geo lists
const country = ref<any>(null)
const stateList = ref<any[]>([])
const blockList = ref<string[]>([])
const districtList = ref<string[]>([])
const addressLoaded = ref(false)

// lifecycle: load states first, then profile so selects can match existing values
onMounted(async () => {
  try {
    await fetchCountry()
    await fetchStates()        // ensure states available
    await fetchSubscriptionData()
    await fetchUserProfile()   // now safe to set state_code and block
    // If profile contained a state, ensure blocks loaded & selection preserved
    if (form.address.state_code) {
      await fetchBlocks()
      // ensure DOM has processed options so selected value binds
      await nextTick()
      addressLoaded.value = true
    }
  } catch (e) {
    console.error('Init error', e)
  } finally {
    isLoading.value = false
  }
})

/* ---------- API / fetch helpers ---------- */

async function fetchUserProfile() {
  try {
    const url = `${config.public.apiBase}/user/my-profile`
    const res = await useSanctumFetch(url, { method: 'GET' })
    if (res?.data) {
      const user = res.data
      form.profile.name = user.name || ''
      form.profile.email = user.email || ''
      form.profile.mobile = user.mobile || ''
      form.profile.gender = user.gender || ''
      form.profile.dob = user.dob || ''

      if (user.address) {
        // If states not loaded yet, we'll set after states load in onMounted
        form.address.postal_code = user.address.postal_code || ''
        form.address.address_1 = user.address.address_1 || ''
        form.address.landmark = user.address.landmark || ''
        form.address.village = user.address.village || ''
        form.address.city = user.address.city || ''
        form.address.state_code = user.address.state_code || ''
        form.address.block_id = user.address.block_id || ''
      }

      if (user.kyc) {
        form.kyc.aadhaar = user.kyc.aadhaar || ''
        form.kyc.pan = user.kyc.pan || ''
        // images not auto-filled from API since they are files
        form.kyc.aadhaarImageUrl = user.kyc?.aadhaarImage || ''
        form.kyc.panImageUrl = user.kyc?.panImage || ''
      }

      if (user.finish) {
        form.finish.subscription_type = user.finish.subscription_type || ''
        form.finish.tnc = !!user.finish.tnc
      }
    }
  } catch (err) {
    console.error('fetchUserProfile error', err)
  }
}

async function fetchCountry() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/countries`, { method: 'GET' })
    if (res?.data) {
      country.value = res.data.find((c: any) => c.iso_code_2 === 'IN') || res.data[0]
    }
  } catch {
    country.value = { name: 'India', iso_code_2: 'IN' }
  }
}

async function fetchStates() {
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/states/IN`, { method: 'GET' })
    // expect res.data = [{ code, name }, ...]
    if (res?.data) stateList.value = res.data
  } catch (err) {
    console.error('fetchStates error', err)
    stateList.value = []
  }
}

async function fetchBlocks() {
  if (!form.address.state_code) {
    blockList.value = []
    districtList.value = []
    return
  }
  try {
    const res = await useSanctumFetch(`${config.public.apiBase}/geo/state/${form.address.state_code}`, { method: 'GET' })
    if (res?.data?.blocks) {
      blockList.value = res.data.blocks.map((b: any) => b.name)
      districtList.value = [...new Set(res.data.blocks.map((b: any) => b.district))]
    } else {
      blockList.value = []
      districtList.value = []
    }
  } catch (err) {
    console.error('fetchBlocks error', err)
    blockList.value = []
    districtList.value = []
  }
}

async function fetchSubscriptionData() {
  try {
    const url = `${config.public.apiBase}/lifecycle/subscribable`
    const res = await useSanctumFetch(url, { method: 'GET' })
    if (res?.data) {
      Object.assign(subscriptionData, res.data)
    }
  } catch (e) {
    console.error('Failed to fetch subscription data:', e)
  }
}

/* ---------- postal autofill ---------- */
async function fetchAddressFromPostalCode() {
  const code = form.address.postal_code
  if (!code || code.length !== 6) return
  try {
    // public API for India PIN
    const res = await fetch(`https://api.postalpincode.in/pincode/${code}`)
    const data = await res.json()
    if (!data || data[0].Status !== 'Success') {
      alert('Invalid postal code')
      return
    }
    const po = data[0].PostOffice[0]
    form.address.city = po.District
    // map state name to state code (if available)
    const codeFromName = getStateCodeByName(po.State)
    if (codeFromName) {
      form.address.state_code = codeFromName
      await fetchBlocks()
      // try to set block if matches
      const blocks = [...new Set(data[0].PostOffice.map((p: any) => p.Block || ''))].filter(Boolean)
      if (blocks.length && blockList.value.length) {
        const matched = blockList.value.find(b => blocks.includes(b))
        if (matched) form.address.block_id = matched
      }
    }
    form.address.address_1 = po.Name || form.address.address_1
    addressLoaded.value = true
  } catch (err) {
    console.error('postal fetch error', err)
    alert('Failed to fetch address from postal code')
  }
}

function getStateCodeByName(name = '') {
  if (!name) return ''
  const found = stateList.value.find(s => (s.name || '').toLowerCase() === name.toLowerCase())
  return found?.code || ''
}

/* ---------- validation ---------- */
function validateField(field: string) {
  switch (field) {
    case 'name':
      errors.profile.name = form.profile.name.trim().length >= 3 ? '' : 'Name must be at least 3 characters.'
      break
    case 'email':
      errors.profile.email = /^\S+@\S+\.\S+$/.test(form.profile.email) ? '' : 'Enter a valid email.'
      break
    case 'mobile':
      errors.profile.mobile = /^[0-9]{10,15}$/.test(form.profile.mobile) ? '' : 'Enter a valid mobile number.'
      break
    case 'gender':
      errors.profile.gender = form.profile.gender ? '' : 'Select gender.'
      break
    case 'dob':
      if (!form.profile.dob) errors.profile.dob = 'Date of birth required.'
      else errors.profile.dob = new Date(form.profile.dob) < new Date() ? '' : 'DOB must be in past.'
      break

    case 'postal_code':
      errors.address.postal_code = /^\d{6}$/.test(form.address.postal_code) ? '' : 'Postal must be 6 digits.'
      break
    case 'address_1':
      errors.address.address_1 = form.address.address_1.trim() ? '' : 'Street address required.'
      break
    case 'city':
      errors.address.city = form.address.city ? '' : 'Select city.'
      break
    case 'state_code':
      errors.address.state_code = form.address.state_code ? '' : 'Select state.'
      break
    case 'block_id':
      errors.address.block_id = form.address.block_id ? '' : 'Select block.'
      break

    case 'aadhaar':
      errors.kyc.aadhaar = /^\d{12}$/.test(form.kyc.aadhaar) ? '' : 'Aadhaar must be 12 digits.'
      break
    case 'pan':
      errors.kyc.pan = /^[A-Z]{5}[0-9]{4}[A-Z]$/.test((form.kyc.pan || '').toUpperCase()) ? '' : 'Invalid PAN.'
      break

    case 'subscription_type':
      errors.finish.subscription_type = form.finish.subscription_type ? '' : 'Select subscription.'
      break
    case 'tnc':
      errors.finish.tnc = form.finish.tnc ? '' : 'You must accept Terms.'
      break
  }
}

function validateAll() {
  validateField('name'); validateField('email'); validateField('mobile'); validateField('gender'); validateField('dob')
  validateField('postal_code'); validateField('address_1'); validateField('city'); validateField('state_code'); validateField('block_id')
  validateField('aadhaar'); validateField('pan'); validateField('subscription_type'); validateField('tnc')

  return !(
      errors.profile.name || errors.profile.email || errors.profile.mobile || errors.profile.gender || errors.profile.dob ||
      errors.address.postal_code || errors.address.address_1 || errors.address.city || errors.address.state_code || errors.address.block_id ||
      errors.kyc.aadhaar || errors.kyc.pan || errors.finish.subscription_type || errors.finish.tnc
  )
}

const isFormValid = computed(() => validateAll())

/* ---------- file handling ---------- */
function onFileChange(e: Event, type: 'aadhaar' | 'pan') {
  const target = e.target as HTMLInputElement
  const f = target.files ? target.files[0] : null
  if (!f) return
  if (type === 'aadhaar') {
    form.kyc.aadhaarFile = f
    if (f.type.startsWith('image/')) form.kyc.aadhaarImageUrl = URL.createObjectURL(f)
  } else {
    form.kyc.panFile = f
    if (f.type.startsWith('image/')) form.kyc.panImageUrl = URL.createObjectURL(f)
  }
}

/* ---------- select handlers ---------- */
async function onStateSelect() {
  validateField('state_code')
  await fetchBlocks()
  // keep block selection if exists
  if (form.address.block_id && blockList.value.includes(form.address.block_id)) {
    // already valid
  } else {
    form.address.block_id = ''
  }
}

/* ---------- styles helper ---------- */
function inputClass(error = '') {
  return [
    'w-full border rounded px-3 py-2 bg-white dark:bg-gray-700 dark:text-white',
    error ? 'border-red-600 focus:ring-red-500' : 'border-gray-300 focus:ring-blue-500'
  ].join(' ')
}

/* ---------- submit ---------- */
async function handleSubmit() {
  if (!validateAll()) {
    alert('Please fix errors before submitting.')
    return
  }

  try {
    const payload = new FormData()
    // profile
    Object.entries(form.profile).forEach(([k, v]) => payload.append(`profile[${k}]`, v as string))
    // address
    Object.entries(form.address).forEach(([k, v]) => payload.append(`address[${k}]`, v as string))
    // kyc
    payload.append('kyc[aadhaar]', form.kyc.aadhaar)
    payload.append('kyc[pan]', form.kyc.pan)
    if (form.kyc.aadhaarFile) payload.append('kyc[aadhaar_file]', form.kyc.aadhaarFile)
    if (form.kyc.panFile) payload.append('kyc[pan_file]', form.kyc.panFile)
    // finish
    payload.append('finish[subscription_type]', form.finish.subscription_type)
    payload.append('finish[tnc]', form.finish.tnc ? '1' : '0')

    const url = `${config.public.apiBase}/user/onboarding`
    const res = await useSanctumFetch(url, { method: 'POST', body: payload })
    if (res?.data?.success) {
      alert('Onboarding completed successfully.')
    } else {
      console.error('submit response', res)
      alert('Submission failed. See console.')
    }
  } catch (err) {
    console.error('submit error', err)
    alert('Submission error, check console.')
  }
}
</script>

<style scoped>
/* nothing special; tailwind handles styling */
</style>
