<template>
  <div class="max-w-4xl mx-auto px-4 py-12 sm:py-16 text-gray-800 dark:text-gray-200">
    <!-- ✅ Global Loader -->
    <GlobalLoader/>

    <div>
      <!-- ❌ Guest User -->
      <section v-if="!isLoggedIn" class="text-center py-16 sm:py-24">
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-6 sm:p-8 rounded shadow">
          <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">
            Login Required
          </h2>
          <p class="mb-6 text-gray-600 dark:text-gray-300">
            You need to be logged in to apply for this job. Please log in or register to continue.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <NuxtLink
                to="/auth/login"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm"
            >
              Login
            </NuxtLink>
            <NuxtLink
                to="/auth/register"
                class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-6 py-2 rounded text-sm"
            >
              Register
            </NuxtLink>
          </div>
        </div>
      </section>

      <!-- ✅ Auth User -->
      <section v-else class="w-full mx-auto container dark:text-white  dark:bg-gray-800 px-5 py-10 dark:shadow-blue-500 dark:shadow-xl rounded-xl">
        <div v-if="job" class="w-full">
          <div class="full text-center mx-auto  mb-8">
            <h1 class="text-lg font-semibold">
              Apply for
            </h1>
            <h2 class="text-2xl sm:text-3xl font-bold">
              {{ job.name }}
            </h2>
          </div>

          <!-- ========================= -->
          <!-- Job Application Form -->
          <!-- ========================= -->
          <form class="space-y-8" @submit.prevent="finalSubmit">
            <!-- Profile Details -->
            <fieldset class="border rounded-2xl p-4 sm:p-6 flex flex-col gap-4">
              <legend class="px-2 text-lg font-semibold">
                Profile Details
              </legend>

              <div class="w-full">
                <label class="block font-medium mb-1">Full Name *</label>
                <input v-model="form.name" type="text" class="input ring-2 ring-gray-700" placeholder="Enter your full name" required
                       disabled/>
              </div>

              <div class="w-full">
                <label class="block font-medium mb-1">Guardian / Parent Name *</label>
                <input v-model="form.guardian_name" type="text" class="input" placeholder="Father or Mother name"
                       required/>
              </div>


              <div class="flex flex-col md:flex-row gap-3 w-full">
                <div class="w-full">
                  <label class="block font-medium mb-1">Email *</label>
                  <input v-model="form.email" type="email" class="input ring-2 ring-gray-700" placeholder="example@email.com" required
                         disabled/>
                </div>

                <div class="w-full">
                  <label class="block font-medium mb-1">Mobile *</label>
                  <input v-model="form.mobile" type="tel" class="input ring-2 ring-gray-700" placeholder="10-digit mobile number" required
                         disabled/>
                </div>
              </div>

              <div class="flex flex-col md:flex-row gap-3 w-full">

                <div class="w-full">
                  <label class="block font-medium mb-1">Gender *</label>
                  <select v-model="form.gender" class="input ring-2 ring-gray-700" required disabled>
                    <option disabled value="">Select gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                  </select>
                </div>

                <div class="w-full">
                  <label class="block font-medium mb-1">Date of Birth *</label>
                  <input v-model="form.dob" type="date" class="input ring-2 ring-gray-700" required disabled/>
                </div>
              </div>

              <!--CTA-->
              <div class="flex flex-row gap-4 ">
                <div class="grow"></div>
                <NuxtLink
                    to="/dashboard/my-account"
                    class="flex items-center gap-1 text-sm text-blue-600
                    hover:text-blue-700 dark:hover:text-green-400
                    transition transform hover:scale-110"
                >
                  <Icon name="solar:pen-new-square-outline" class="w-4 h-4"/>
                  Change Info
                </NuxtLink>
              </div>


            </fieldset>

            <!-- Address Details -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">
                Address Details
              </legend>
              <!-- 👉 Input fields will go here -->
              <div class="w-full">
                <label class="block font-medium mb-1">Select Address *</label>
                <select v-model="form.address_uuid" class="input " required>
                  <option disabled value="">Select Address</option>
                  <option
                      v-for="addr in sortedUserAddresses"
                      :key="addr.uuid"
                      :value="addr.uuid"
                  >
                    {{ addr.address_1 }} ({{ addr.postal_code }})
                  </option>
                </select>
              </div>
              <!--CTA-->
              <div class="flex flex-row gap-4 mt-2">
                <div class="grow"></div>
                <NuxtLink
                    to="/dashboard/my-account"
                    class="flex items-center gap-1 text-sm text-blue-600
                    hover:text-blue-700 dark:hover:text-green-400
                    transition transform hover:scale-110"
                >
                  <Icon name="solar:pen-new-square-outline" class="w-4 h-4"/>
                  Change Info
                </NuxtLink>
              </div>

            </fieldset>

            <!-- Skills -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">
                Skill Details
              </legend>
              <div v-for="(skill, index) in form.skills" :key="index" class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                  <input type="text" v-model="skill.skill" placeholder="Skill Name" class="input w-full"  required/>
                  <textarea v-model="skill.description" rows="2" placeholder="Skill Description" class="input md:col-span-2"
                            required/>
                </div>
              </div>

              <div class="flex flex-row gap-3 mt-2">
                <div class="grow"></div>
                <button type="button" @click="addSkill" class="text-blue-600 hover:cursor-pointer hover:font-semibold ">+ Add Skill</button>
                <button type="button" @click="removeSkill" class="p-1 text-red-600 hover:cursor-pointer hover:font-semibold">
                  − Remove Last
                </button>
              </div>

            </fieldset>

            <!-- Education -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">
                Education Details
              </legend>
              <div v-for="(edu, index) in form.educations" :key="index"
                   class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <select v-model="edu.degree" class="input" required>
                  <option value="">Select Degree</option>
                  <option v-for="d in degrees" :key="d" :value="d">{{ d }}</option>
                </select>
                <input type="text" v-model="edu.institution" placeholder="Institution" class="input" required/>
                <input type="number" v-model="edu.year" placeholder="Year of Completion" class="input" required/>
              </div>
              <div class="flex flex-row gap-3  mt-2">
                <div class="grow"></div>
                <button type="button" @click="addEducation" class="text-blue-600 hover:cursor-pointer hover:font-semibold">+ Add
                  Qualification
                </button>
                <button type="button" @click="removeEducation" class="p-1 text-red-600 hover:cursor-pointer hover:font-semibold">
                  − Remove Last
                </button>
              </div>
            </fieldset>

            <!-- Experience -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">
                Experiences You Have Any (optional)
              </legend>
              <!-- Checkbox Toggle -->
              <div class="mb-4">
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" v-model="hasExperience"/>
                  <span>I have experience</span>
                </label>
              </div>
              <!-- Only show if checked -->
              <div v-if="hasExperience">
                <div v-for="(experience, index) in form.experiences" :key="index" class="mb-4 space-y-2">
                  <div>
                    <label class="block font-medium mb-1">Company Name</label>
                    <input v-model="experience.company_name" type="text" placeholder="Company Name"
                           class="input w-full"/>
                  </div>

                  <div class="flex flex-col md:flex-row gap-3 w-full">

                    <div class="w-full">
                      <label class="block font-medium mb-1">Start From</label>
                      <input v-model="experience.start" type="date" class="input" required/>
                    </div>
                    <div class="w-full">
                      <label class="block font-medium mb-1">End Upto</label>
                      <input v-model="experience.end" type="date" class="input" required/>
                    </div>
                  </div>

                  <div>
                    <label class="block font-medium mb-1">Experience </label>
                    <textarea v-model="experience.experience" rows="3" placeholder="Describe your experience..."
                              class="input w-full"/>
                  </div>
                </div>
                <div class="flex flex-row gap-3 mt-2">
                  <div class="grow"></div>
                  <button type="button" @click="addExperience" class="p-1 text-blue-600 hover:cursor-pointer hover:font-semibold ">+ Add
                    Experience
                  </button>
                  <button type="button" @click="removeExperience" class="p-1 text-red-600 hover:cursor-pointer hover:font-semibold">
                    − Remove Last
                  </button>
                </div>
              </div>
            </fieldset>

            <!-- Agreement -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">
                Agreement & Preferences
              </legend>

              <div class="mb-2">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="form.relocation" />
                  Willing to Relocate
                </label>
              </div>

              <div class="mb-2">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="form.agree_terms" />
                  I agree with the Terms & Conditions
                </label>
              </div>

              <div class="mb-2">
                <label class="block font-medium mb-1">Reference Name (optional)</label>
                <input
                    type="text"
                    v-model="form.reference_name"
                    placeholder="Reference Name"
                    class="input w-full"
                />
              </div>

              <div>
                <label class="block font-medium mb-1">Reference Contact (optional)</label>
                <input
                    type="text"
                    v-model="form.reference_contact"
                    placeholder="Reference Contact"
                    class="input w-full"
                />
              </div>
            </fieldset>


            <!-- Submit Button -->
            <!-- Submit Button -->
            <div class="pt-4 w-full flex flex-col items-center">
              <button
                  type="submit"
                  class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-2xl"
              >
    <span v-if="!job.is_payable">
      Submit Application
    </span>
                <span v-else>
      Submit Application &amp; Proceed with Payment ({{ job.fees }})
    </span>
              </button>

              <!-- Note about fees -->
              <p v-if="job.is_payable" class="mt-2 text-sm text-gray-600 text-center">
                An application fee of <strong>{{ job.fees }}</strong> is required.
                This fee is non-refundable.
              </p>
            </div>


          </form>
        </div>
      </section>
    </div>
  </div>
</template>


<script setup lang="ts">
import {ref, reactive, onMounted} from "vue"
import {useRoute} from "vue-router"
import {
  useSanctum,
  useSanctumFetch,
  useRuntimeConfig,
  useCurrentUser,
} from "#imports"

// ✅ Global loader
const isLoading = useState("pageLoading", () => false)
isLoading.value = true

const route = useRoute()
const toast = useToast()
const job = ref<Job | null>(null)

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
}

interface User {
  id: number
  email: string
  name: string
  mobile: string
  gender: string
  dob: string
}

const user = useCurrentUser<User>()
const {isLoggedIn} = useSanctum()
const config = useRuntimeConfig()
const hasExperience = ref(false)
const degrees = [
  'Class-8', 'Class-9', 'Class-10', 'Class-12',
  'Diploma', 'Bachelor', 'Master', 'Other'
]

// ✅ Form model
const form = reactive({
  name: "",
  email: "",
  mobile: "",
  gender: "",
  dob: "",

  guardian_name: "",
  address_uuid: "",

  educations: [{degree: "", institution: "", year: ""}],
  skills: [{skill: "", description: ""}],

  company_name: "",
  experiences: [{company_name: "", start: "", end: "", experience: ""}],
  relocation: false,
  agree_terms: false,

  reference_name: "",
  reference_contact: "",
})

// ---------------------------
// Fetch Job
// ---------------------------
async function fetchJob() {
  try {
    const apiUrl = `${config.public.apiBase}/recruitment/${route.params.url}`
    const response = await useSanctumFetch(apiUrl)
    job.value = response?.data || null
  } catch (error) {
    // console.error("❌ Error fetching job:", error)
    toast.error({
      title: 'Error',
      message: '❌ Error fetching job',
      timeout: 3000,
      position: 'topRight',
    })
    job.value = null
  }
}

const userAddresses = ref<any[]>([])

// Computed → sort by priority ascending
const sortedUserAddresses = computed(() => {
  return [...(userAddresses.value || [])].sort((a, b) => a.priority - b.priority)
})

// Fetch user addresses
async function fetchUserAddress() {
  try {
    const apiUrl = `${config.public.apiBase}/user/address-all`
    const response = await useSanctumFetch(apiUrl)
    userAddresses.value = response?.data || []

    // Auto-select default HOME address
    const defaultHome = userAddresses.value.find(
        (a) =>
            (a.type?.toLowerCase() === "home") &&
            a.default === true
    )
    if (defaultHome) {
      form.address_uuid = defaultHome.uuid
    }
  } catch (error) {
    toast.error({
      title: 'Error',
      message: '❌ Error fetching addresses',
      timeout: 3000,
      position: 'topRight',
    })
    userAddresses.value = []
  }
}

// ---------------------------
// Populate Form with User Data
// ---------------------------
function populateFormWithUser() {
  if (isLoggedIn.value && user.value) {
    form.name = user.value.name || ""
    form.email = user.value.email || ""
    form.mobile = user.value.mobile || ""
    form.gender = user.value.gender || ""
    form.dob = user.value.dob || ""
  }
}

// ---------------------------
// Init Page
// ---------------------------
async function initializePage() {
  try {
    await fetchJob()
    await fetchUserAddress()
    populateFormWithUser()
  } catch (error) {
    toast.error({
      title: 'Error',
      message: '❌ Error during initialization',
      timeout: 3000,
      position: 'topRight',
    })
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  await initializePage()
})



// Form Actions
const addEducation = () => {
  form.educations.push({ degree: '', institution: '', year: '' })
}
const removeEducation = () => {
  if (form.educations.length > 1) {
    form.educations.pop()
  } else {
    toast.error({
      title: 'Error',
      message: '⚠️ At least one education is required',
      timeout: 3000,
      position: 'topRight',
    })
  }
}

const addSkill = () => {
  form.skills.push({ skill: '', description: '' })
}
const removeSkill = () => {
  if (form.skills.length > 1) {
    form.skills.pop()
  } else {
    toast.error({
      title: 'Error',
      message: '⚠️ At least one skill is required',
      timeout: 3000,
      position: 'topRight',
    })
  }
}

const addExperience = () => {
  form.experiences.push({ company_name: "", start: "", end: "", experience: "" })
}
const removeExperience = () => {
  if (form.experiences.length > 1) {
    form.experiences.pop()
  } else {
    toast.error({
      title: 'Error',
      message: '⚠️ At least one experience is required',
      timeout: 3000,
      position: 'topRight',
    })
  }
}



// Form Sumission
const finalSubmit = async () => {
  const payload = {
    job_uuid: job?.uuid,
    ...form,
  }

  try {
    // ✅ POST to API
    const apiUrl = `${config.public.apiBase}/recruitment/${route.params.url}/apply`
    const response = await useSanctumFetch(apiUrl, {
      method: 'POST',
      body: payload,
    })

    console.log(response)
    // Optional: handle success
    // console.log('Form submitted successfully:', response)
    toast.success({
      title: 'Error',
      message: 'Form submitted successfully.',
      timeout: 3000,
      position: 'topRight',
    })

  } catch (err) {
    // console.error('Form submission failed:', err)
    toast.error({
      title: 'Error',
      message: 'Something went wrong. Please try again.',
      timeout: 3000,
      position: 'topRight',
    })
  }


}


</script>


<style scoped>
.input {
  @apply border border-gray-300 dark:border-gray-700 rounded px-3 py-2 w-full bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100;
}
</style>
