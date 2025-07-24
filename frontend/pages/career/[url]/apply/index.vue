<template>
  <div class="max-w-4xl mx-auto px-4 py-16 text-gray-800 dark:text-gray-200">
    <div v-if="job">
      <h1 class="text-3xl font-bold mb-8">Apply for {{ job.title }}</h1>

      <!-- Application Form -->
      <form @submit.prevent="openReviewModal" class="space-y-10">

        <!-- Personal Information -->
        <fieldset class="border p-6 rounded-md">
          <legend class="text-xl font-semibold mb-4">Personal Information</legend>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block font-medium mb-1">Full Name *</label>
              <input v-model="form.name" type="text" class="input" placeholder="Enter your full name" required />
            </div>

            <div>
              <label class="block font-medium mb-1">Email *</label>
              <input v-model="form.email" type="email" class="input" placeholder="example@email.com" required />
            </div>

            <div>
              <label class="block font-medium mb-1">Mobile *</label>
              <input v-model="form.mobile" type="tel" class="input" placeholder="10-digit mobile number" required />
            </div>

            <div>
              <label class="block font-medium mb-1">Gender *</label>
              <select v-model="form.gender" class="input" required>
                <option disabled value="">Select gender</option>
                <option value="male">Male</option>
                <option value="female">Female</option>
                <option value="other">Other</option>
              </select>
            </div>

            <div>
              <label class="block font-medium mb-1">Date of Birth *</label>
              <input v-model="form.dob" type="date" class="input" required />
            </div>

            <div>
              <label class="block font-medium mb-1">Guardian / Parent Name *</label>
              <input v-model="form.guardian_name" type="text" class="input" placeholder="Father or Mother name" required />
            </div>
          </div>
        </fieldset>

        <!-- Address -->
        <fieldset class="border p-6 rounded-md">
          <legend class="text-xl font-semibold mb-4">Present Address</legend>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <input v-model="form.address_1" type="text" class="input" placeholder="Street address *" required />
            <input v-model="form.landmark" type="text" class="input" placeholder="Landmark (optional)" />
            <input v-model="form.village" type="text" class="input" placeholder="Village/Town *" required />
            <input v-model="form.block" type="text" class="input" placeholder="Block (optional)" />
            <input v-model="form.city" type="text" class="input" placeholder="City / District *" required />
            <input v-model="form.state_code" type="text" class="input" placeholder="State *" required />
            <input v-model="form.postal_code" type="text" class="input" placeholder="Postal Code *" required />
          </div>
        </fieldset>

        <!-- Education -->
        <fieldset class="border p-6 rounded-md">
          <legend class="text-xl font-semibold mb-4">Educational Qualifications</legend>

          <div v-for="(edu, index) in form.educations" :key="index" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <select v-model="edu.degree" class="input" required>
              <option value="">Select Degree</option>
              <option v-for="d in degrees" :key="d" :value="d">{{ d }}</option>
            </select>
            <input type="text" v-model="edu.institution" placeholder="Institution" class="input" required />
            <input type="number" v-model="edu.year" placeholder="Year of Completion" class="input" required />
          </div>
          <button type="button" @click="addEducation" class="text-blue-600 hover:underline mt-2">+ Add Qualification</button>
        </fieldset>

        <!-- Skills -->
        <fieldset class="border p-6 rounded-md">
          <legend class="text-xl font-semibold mb-4">Skills</legend>

          <div v-for="(skill, index) in form.skills" :key="index" class="mb-4 space-y-2">
            <input type="text" v-model="skill.skill" placeholder="Skill Name" class="input w-full" required />
            <textarea v-model="skill.description" rows="2" placeholder="Skill Description" class="input w-full" required />
          </div>
          <button type="button" @click="addSkill" class="text-blue-600 hover:underline mt-2">+ Add Skill</button>
        </fieldset>

        <!-- Other Experience -->
        <div>
          <label class="block font-medium mb-1">Company Name (optional)</label>
          <input v-model="form.company_name" type="text" placeholder="Company Name" class="input w-full" />
        </div>

        <div>
          <label class="block font-medium mb-1">Experience (optional)</label>
          <textarea v-model="form.experience" rows="3" placeholder="Describe your experience..." class="input w-full" />
        </div>

        <!-- Preferences -->
        <div class="flex flex-col sm:flex-row gap-6">
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.relocation" /> Willing to Relocate
          </label>
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.traveling" /> Willing to Travel
          </label>
        </div>

        <!-- Reference -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block font-medium mb-1">Reference Name (optional)</label>
            <input type="text" v-model="form.reference_name" placeholder="Reference Name" class="input w-full" />
          </div>
          <div>
            <label class="block font-medium mb-1">Reference Contact (optional)</label>
            <input type="text" v-model="form.reference_contact" placeholder="Reference Contact" class="input w-full" />
          </div>
        </div>

        <!-- Submit -->
        <div class="text-center mt-8">
          <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded text-lg hover:bg-blue-700">
            Continue to Review
          </button>
        </div>
      </form>
    </div>

    <!-- Not Found -->
    <div v-else class="text-center py-24">
      <h2 class="text-2xl font-semibold mb-2">Job not found</h2>
      <NuxtLink to="/career" class="text-blue-600 hover:underline">← Back to Careers</NuxtLink>
    </div>

    <!-- Modal -->
    <!-- Modal -->
    <!-- Review Modal -->
    <!-- Fullscreen Scrollable Modal with Collapsible Sections -->
    <div v-if="showModal" class="fixed inset-0 z-50 bg-black bg-opacity-70 flex items-center justify-center">
      <div class="bg-white dark:bg-gray-900 w-full h-full flex flex-col">

      <!-- Header -->
        <div class="flex justify-between items-center px-6 py-4 border-b border-gray-300 dark:border-gray-700">
          <h2 class="text-xl font-bold">Review Your Application</h2>
          <button @click="showModal = false" class="text-3xl text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 leading-none">
            &times;
          </button>
        </div>

        <!-- Scrollable Content -->
        <div class="flex-1 overflow-y-auto p-6 space-y-4">


        <details open class="border rounded-md p-4">
            <summary class="font-semibold cursor-pointer">👤 Personal Information</summary>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
              <div><label class="font-medium">Full Name:</label> <div>{{ form.name }}</div></div>
              <div><label class="font-medium">Email:</label> <div>{{ form.email }}</div></div>
              <div><label class="font-medium">Mobile:</label> <div>{{ form.mobile }}</div></div>
              <div><label class="font-medium">Gender:</label> <div>{{ form.gender }}</div></div>
              <div><label class="font-medium">DOB:</label> <div>{{ form.dob }}</div></div>
              <div><label class="font-medium">Guardian:</label> <div>{{ form.guardian_name }}</div></div>
            </div>
          </details>

          <details open class="border rounded-md p-4">
            <summary class="font-semibold cursor-pointer">🏠 Present Address</summary>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
              <div><label class="font-medium">Street:</label> <div>{{ form.address_1 }}</div></div>
              <div><label class="font-medium">Landmark:</label> <div>{{ form.landmark }}</div></div>
              <div><label class="font-medium">Village:</label> <div>{{ form.village }}</div></div>
              <div><label class="font-medium">Block:</label> <div>{{ form.block }}</div></div>
              <div><label class="font-medium">City:</label> <div>{{ form.city }}</div></div>
              <div><label class="font-medium">State:</label> <div>{{ form.state_code }}</div></div>
              <div><label class="font-medium">Postal Code:</label> <div>{{ form.postal_code }}</div></div>
            </div>
          </details>

          <details open class="border rounded-md p-4">
            <summary class="font-semibold cursor-pointer">🎓 Education</summary>
            <div class="space-y-2 mt-3">
              <div v-for="(edu, index) in form.educations" :key="index">
                <p><strong>{{ edu.degree }}</strong> – {{ edu.institution }} ({{ edu.year }})</p>
              </div>
            </div>
          </details>

          <details open class="border rounded-md p-4">
            <summary class="font-semibold cursor-pointer">🧠 Skills</summary>
            <div class="space-y-2 mt-3">
              <div v-for="(skill, index) in form.skills" :key="index">
                <p><strong>{{ skill.skill }}</strong></p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ skill.description }}</p>
              </div>
            </div>
          </details>

          <details v-if="form.company_name || form.experience" open class="border rounded-md p-4">
            <summary class="font-semibold cursor-pointer">💼 Other Experience</summary>
            <div class="mt-3">
              <p v-if="form.company_name"><strong>Company:</strong> {{ form.company_name }}</p>
              <p v-if="form.experience"><strong>Experience:</strong> {{ form.experience }}</p>
            </div>
          </details>

          <details open class="border rounded-md p-4">
            <summary class="font-semibold cursor-pointer">⚙️ Preferences</summary>
            <div class="mt-3">
              <p><strong>Willing to Relocate:</strong> {{ form.relocation ? 'Yes' : 'No' }}</p>
              <p><strong>Willing to Travel:</strong> {{ form.traveling ? 'Yes' : 'No' }}</p>
            </div>
          </details>

          <details v-if="form.reference_name || form.reference_contact" open class="border rounded-md p-4">
            <summary class="font-semibold cursor-pointer">📇 Reference</summary>
            <div class="mt-3">
              <p><strong>Name:</strong> {{ form.reference_name }}</p>
              <p><strong>Contact:</strong> {{ form.reference_contact }}</p>
            </div>
          </details>

          <div v-if="job?.is_payable" class="bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300 text-sm p-3 rounded mt-3">
            ⚠️ This job requires a small application fee. You’ll be redirected to payment after submission.
          </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 px-6 py-4 border-t border-gray-300 dark:border-gray-700">
          <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" v-model="agreed" />
            I confirm all above information is accurate.
          </label>

          <div class="flex gap-3">
            <button @click="showModal = false" class="text-blue-600 hover:underline text-sm">Edit</button>
            <button
                :disabled="!agreed"
                @click="finalSubmit"
                class="bg-green-600 text-white px-5 py-2 rounded hover:bg-green-700 disabled:opacity-50 text-sm"
            >
              Submit Application
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->



    <!-- Demo Payment Modal -->
    <div v-if="showDemoPayModal" class="fixed inset-0 z-50 bg-black bg-opacity-70 flex items-center justify-center">
      <div class="bg-white dark:bg-gray-900 w-full max-w-md p-6 rounded shadow-lg text-center">
        <h2 class="text-2xl font-bold mb-4">💳 Demo Payment</h2>
        <p class="mb-6 text-gray-700 dark:text-gray-300">
          This is a temporary screen. You would be redirected to payment gateway here.
        </p>
        <button
            @click="redirectToConfirm"
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded text-lg"
        >
          Proceed & Confirm Application
        </button>
      </div>
    </div>





  </div>
</template>




<script setup lang="ts">
import { ref, reactive } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const route = useRoute()
const router = useRouter()

// Static job listings
const jobs = [
  {
    id: 'frontend-dev',
    url: 'frontend-developer',
    title: 'Frontend Developer',
    location: 'Remote / Kolkata',
    team: 'Engineering',
    is_payable: true,
  },
  {
    id: 'backend-engineer',
    url: 'backend-engineer',
    title: 'Backend Engineer',
    location: 'Remote / Bengaluru',
    team: 'Engineering',
    is_payable: false,
  },
  {
    id: 'product-designer',
    url: 'product-designer',
    title: 'Product Designer',
    location: 'Remote / Mumbai',
    team: 'Design',
    is_payable: false,
  }
]

const job = jobs.find(j => j.url === route.params.url)

const degrees = [
  'Class-8', 'Class-9', 'Class-10', 'Class-12',
  'Diploma', 'Bachelor', 'Master', 'Other'
]

const form = reactive({
  name: '',
  email: '',
  mobile: '',
  gender: '',
  dob: '',
  guardian_name: '',

  address_1: '',
  landmark: '',
  village: '',
  block: '',
  city: '',
  state_code: '',
  postal_code: '',

  educations: [
    { degree: '', institution: '', year: '' }
  ],
  skills: [
    { skill: '', description: '' }
  ],

  company_name: '',
  experience: '',
  relocation: false,
  traveling: false,

  reference_name: '',
  reference_contact: '',
})

const showModal = ref(false)
const showDemoPayModal = ref(false)
const agreed = ref(false)

const addEducation = () => {
  form.educations.push({ degree: '', institution: '', year: '' })
}
const addSkill = () => {
  form.skills.push({ skill: '', description: '' })
}

const openReviewModal = () => {
  if (!form.name || !form.email || !form.mobile || !form.gender || !form.dob) {
    alert('Please fill all required fields before review.')
    return
  }
  showModal.value = true
}

const finalSubmit = () => {
  showModal.value = false
  console.log('Application data:', { job_id: job?.id, ...form })

  if (job?.is_payable) {
    showDemoPayModal.value = true
  } else {
    redirectToConfirm()
  }
}

const redirectToConfirm = () => {
  const uuid = Math.random().toString(36).substring(2, 10).toUpperCase()

  router.push({
    path: `/career/${route.params.url}/apply/confirm`,
    query: {
      uuid,
      name: form.name,
      email: form.email,
      mobile: form.mobile,
    }
  })
}
</script>




<style scoped>
.input {
  @apply border border-gray-300 dark:border-gray-700 rounded px-3 py-2 w-full bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100;
}
</style>
