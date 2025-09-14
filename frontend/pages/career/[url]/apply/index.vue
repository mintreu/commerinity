<template>
  <div class="max-w-7xl mx-auto px-4 py-12 sm:py-16 text-gray-800 dark:text-gray-200">
    <GlobalLoader />

    <div>
      <!-- Guest -->
      <section v-if="!isLoggedIn" class="text-center py-16 sm:py-24">
        <div class="max-w-md mx-auto bg-white dark:bg-gray-800 p-6 sm:p-8 rounded shadow">
          <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-100">Login Required</h2>
          <p class="mb-6 text-gray-600 dark:text-gray-300">
            You need to be logged in to apply for this job. Please log in or register to continue.
          </p>
          <div class="flex flex-col sm:flex-row justify-center gap-4">
            <NuxtLink to="/auth/login" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded text-sm">
              Login
            </NuxtLink>
            <NuxtLink to="/auth/register" class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-6 py-2 rounded text-sm">
              Register
            </NuxtLink>
          </div>
        </div>
      </section>

      <!-- Auth -->
      <section v-else class="w-full mx-auto container dark:text-white dark:bg-gray-800 px-5 py-10 dark:shadow-blue-500 dark:shadow-xl rounded-xl">
        <div v-if="job" class="w-full">

          <div class="text-center mx-auto mb-8">
            <h1 class="text-lg font-semibold">Apply for</h1>
            <h2 class="text-2xl sm:text-3xl font-bold">{{ job.name }}</h2>
          </div>

          <form class="space-y-10" @submit.prevent="finalSubmit">
            <!-- Profile Details -->
            <fieldset class="border rounded-2xl p-4 sm:p-6 flex flex-col gap-4">
              <legend class="px-2 text-lg font-semibold">Profile Details</legend>
              <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">Please enter your personal details as per your ID proofs. Fields marked * are mandatory.</div>

              <div class="w-full">
                <label class="block font-medium mb-1">Full Name *</label>
                <input v-model="form.name" type="text" class="input ring-2 ring-gray-700" placeholder="e.g. Rahul Sharma" required disabled />
                <div class="text-xs text-gray-500 mt-1 ml-1">As per official record or certificate. This field is locked to your profile.</div>
              </div>

              <div class="w-full">
                <label class="block font-medium mb-1">Guardian / Parent Name *</label>
                <input v-model="form.guardian_name" type="text" class="input" placeholder="e.g. Suresh Sharma" required />
                <div class="text-xs text-gray-500 mt-1 ml-1">Enter father, mother, or legal guardian's name for record verification.</div>
              </div>

              <div class="flex flex-col md:flex-row gap-3 w-full">
                <div class="w-full">
                  <label class="block font-medium mb-1">Email *</label>
                  <input v-model="form.email" type="email" class="input ring-2 ring-gray-700" placeholder="e.g. rahul@gmail.com" required disabled />
                  <div class="text-xs text-gray-500 mt-1 ml-1">Primary email registered with your account. Locked for editing.</div>
                </div>
                <div class="w-full">
                  <label class="block font-medium mb-1">Mobile *</label>
                  <input v-model="form.mobile" type="tel" class="input ring-2 ring-gray-700" placeholder="10-digit mobile number" required disabled />
                  <div class="text-xs text-gray-500 mt-1 ml-1">Your 10-digit mobile number. Locked, change in profile if needed.</div>
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
                  <div class="text-xs text-gray-500 mt-1 ml-1">Your gender, as per your records.</div>
                </div>
                <div class="w-full">
                  <label class="block font-medium mb-1">Date of Birth *</label>
                  <input v-model="form.dob" type="date" class="input ring-2 ring-gray-700" required disabled />
                  <div class="text-xs text-gray-500 mt-1 ml-1">Date of birth (DD/MM/YYYY), locked as per account.</div>
                </div>
              </div>

              <div class="flex flex-row gap-4">
                <div class="grow"></div>
                <NuxtLink to="/dashboard/my-account" class="flex items-center gap-1 text-sm text-blue-600 hover:text-blue-700 dark:hover:text-green-400 transition transform hover:scale-110">
                  <Icon name="solar:pen-new-square-outline" class="w-4 h-4" />
                  Change Info
                </NuxtLink>
              </div>
            </fieldset>

            <!-- Address Details -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">Address Details</legend>
              <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">Select your current residential address for communication or verification purposes.</div>

              <div class="w-full">
                <label class="block font-medium mb-1">Select Address *</label>
                <select v-model="form.address_uuid" class="input" required>
                  <option disabled value="">Select Address</option>
                  <option v-for="addr in sortedUserAddresses" :key="addr.uuid" :value="addr.uuid">
                    {{ addr.address_1 }} ({{ addr.postal_code }})
                  </option>
                </select>
                <div class="text-xs text-gray-500 mt-1 ml-1">Pick your default or preferred address from your saved list. Update or add more in your account page.</div>
              </div>
            </fieldset>

            <!-- Skills -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">Skill Details</legend>
              <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">List one or more core/professional skills related to this job application.</div>

              <div v-for="(skill, index) in form.skills" :key="index" class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                  <input type="text" v-model="skill.skill" placeholder="e.g. JavaScript, Excel, Typing" class="input w-full" required />
                  <textarea v-model="skill.description" rows="2" placeholder="Describe this skill and experience with it" class="input md:col-span-2" required></textarea>
                </div>
                <div class="text-xs text-gray-500 mb-2 ml-1">Be specific. State your role/level with this skill (e.g. '2 years development experience').</div>
              </div>

              <div class="flex flex-row gap-3 mt-2">
                <div class="grow"></div>
                <button type="button" @click="addSkill" class="text-blue-600 hover:cursor-pointer hover:font-semibold">+ Add Skill</button>
                <button type="button" @click="removeSkill" class="p-1 text-red-600 hover:cursor-pointer hover:font-semibold">− Remove Last</button>
              </div>
            </fieldset>

            <!-- Education -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">Education Details</legend>
              <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">Fill your highest or most relevant educational qualifications first.</div>

              <div v-for="(edu, index) in form.educations" :key="index" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
                <select v-model="edu.degree" class="input" required>
                  <option value="">Select Degree</option>
                  <option v-for="d in degrees" :key="d" :value="d">{{ d }}</option>
                </select>
                <input type="text" v-model="edu.institution" placeholder="e.g. Delhi Public School" class="input" required />
                <input type="number" v-model.number="edu.year" placeholder="Year (e.g. 2023)" class="input" required />
              </div>

              <div class="text-xs text-gray-500 mb-2 ml-1">Provide details as mentioned on your degree or certificate.</div>

              <div class="flex flex-row gap-3 mt-2">
                <div class="grow"></div>
                <button type="button" @click="addEducation" class="text-blue-600 hover:cursor-pointer hover:font-semibold">+ Add Qualification</button>
                <button type="button" @click="removeEducation" class="p-1 text-red-600 hover:cursor-pointer hover:font-semibold">− Remove Last</button>
              </div>
            </fieldset>

            <!-- Experience -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">Experiences You Have Any (optional)</legend>
              <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                If you have any relevant work/volunteer experience, you may share it here; this is optional but recommended.
              </div>

              <div class="mb-4">
                <label class="inline-flex items-center gap-2">
                  <input type="checkbox" v-model="hasExperience" />
                  <span>I have experience</span>
                </label>
              </div>

              <div v-if="hasExperience">
                <div v-for="(experience, index) in form.experiences" :key="index" class="mb-4">
                  <div>
                    <label class="block font-medium mb-1">Company/Org Name</label>
                    <input v-model="experience.company_name" type="text" placeholder="e.g. Infosys, Volunteer Group" class="input w-full" />
                    <div class="text-xs text-gray-500 mt-1 ml-1">Name of the company, institution or organization.</div>
                  </div>

                  <div class="flex flex-col md:flex-row gap-3 w-full mt-2">
                    <div class="w-full">
                      <label class="block font-medium mb-1">Start Date</label>
                      <input v-model="experience.start" type="date" class="input w-full" />
                    </div>
                    <div class="w-full">
                      <label class="block font-medium mb-1">End Date</label>
                      <input v-model="experience.end" type="date" class="input w-full" />
                    </div>
                  </div>

                  <div class="mt-2">
                    <label class="block font-medium mb-1">Experience Description</label>
                    <textarea v-model="experience.experience" rows="2" placeholder="e.g. Worked as sales executive managing a team of 4" class="input w-full"></textarea>
                    <div class="text-xs text-gray-500 mt-1 ml-1">Briefly explain your role and responsibilities.</div>
                  </div>
                </div>

                <div class="flex flex-row gap-3 mt-2">
                  <div class="grow"></div>
                  <button type="button" @click="addExperience" class="p-1 text-blue-600 hover:cursor-pointer hover:font-semibold">+ Add Experience</button>
                  <button type="button" @click="removeExperience" class="p-1 text-red-600 hover:cursor-pointer hover:font-semibold">− Remove Last</button>
                </div>
              </div>
            </fieldset>

            <!-- Agreement -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">Agreement & Preferences</legend>
              <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">Read all points carefully and check as per your preference.</div>

              <div class="mb-3">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="form.relocation" />
                  Willing to Relocate
                </label>
                <div class="text-xs text-gray-500 ml-1">Tick if you are comfortable relocating for the job if required.</div>
              </div>

              <div class="mb-3">
                <label class="flex items-center gap-2">
                  <input type="checkbox" v-model="form.agree_terms" />
                  I agree with the Terms & Conditions
                </label>
                <div class="text-xs text-gray-500 ml-1">Please read terms and conditions before submission. This is mandatory.</div>
              </div>
            </fieldset>

            <!-- Reference Info -->
            <fieldset class="border rounded-2xl p-4 sm:p-6">
              <legend class="px-2 text-lg font-semibold">Reference Information (optional)</legend>
              <div class="mb-2 text-sm text-gray-600 dark:text-gray-400">Provide a reference who can verify your background. Leave blank if not applicable.</div>

              <div class="mb-2">
                <label class="block font-medium mb-1">Reference Name</label>
                <input type="text" v-model="form.reference_name" class="input w-full" placeholder="e.g. Dr. Neha Verma" />
                <div class="text-xs text-gray-500 ml-1">Name and designation of person providing reference.</div>
              </div>

              <div class="mb-2">
                <label class="block font-medium mb-1">Reference Contact</label>
                <input type="text" v-model="form.reference_contact" class="input w-full" placeholder="e.g. 9999912345 or neha@example.com" />
                <div class="text-xs text-gray-500 ml-1">Contact number or email address for reference check.</div>
              </div>
            </fieldset>

            <!-- Submit -->
            <div class="pt-4 w-full flex flex-col items-center">
              <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2 rounded-2xl">
                <span v-if="!job.is_payable">Submit Application</span>
                <span v-else>Submit Application &amp; Proceed with Payment ({{ job.fees }})</span>
              </button>
              <p v-if="job.is_payable" class="mt-2 text-sm text-gray-600 text-center">
                An application fee of <strong>{{ job.fees }}</strong> is required. This fee is non-refundable.
              </p>
            </div>
          </form>
        </div>
      </section>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div class="bg-white dark:bg-gray-900 rounded-lg shadow-xl w-full max-w-md p-6">
        <div class="flex items-center gap-3">
          <div class="h-10 w-10 rounded-full bg-green-100 dark:bg-green-800 flex items-center justify-center">
            <svg class="h-6 w-6 text-green-600 dark:text-green-300" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <h3 class="text-lg font-semibold">Application submitted</h3>
        </div>

        <p class="mt-3 text-sm text-gray-700 dark:text-gray-300">
          Application ID:
          <span class="font-mono font-semibold">{{ lastApplicationId }}</span>
        </p>

        <div class="mt-4 flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
          <svg class="h-5 w-5 animate-spin text-indigo-600" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          Redirecting to checkout…
        </div>

        <div class="mt-6 flex justify-end">
          <button @click="cancelAutoRedirect" class="px-4 py-2 text-sm rounded border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
            Stay here
          </button>
          <a v-if="pendingRedirectUrl" :href="pendingRedirectUrl" target="_blank" rel="noopener" class="ml-2 px-4 py-2 text-sm rounded bg-indigo-600 text-white hover:bg-indigo-700">
            Open now
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted, computed, watch } from "vue";
import { useRoute } from "vue-router"; // Removed navigateTo import
import {
  useSanctum,
  useSanctumFetch,
  useRuntimeConfig,
  useCurrentUser,
  useToast,
} from "#imports";

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

const user = useCurrentUser<User>();
const { isLoggedIn } = useSanctum();
const config = useRuntimeConfig();

const hasExperience = ref(false);

const degrees = [
  "Class-8", "Class-9", "Class-10", "Class-12",
  "Diploma", "Bachelor", "Master", "Other",
];

// Form model: experiences starts empty for backend compatibility
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

// Data fetching
async function fetchJob() {
  try {
    const apiUrl = `${config.public.apiBase}/recruitment/${route.params.url}`;
    const response = await useSanctumFetch(apiUrl);
    job.value = response?.data || null;
  } catch {
    toast.error({ title: "Error", message: "❌ Error fetching job", timeout: 3000, position: "topRight" });
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
  }
}

onMounted(async () => {
  await initializePage();
});

// Experience toggle behavior
watch(hasExperience, (enabled) => {
  if (!enabled) {
    form.experiences = [];
  } else if (enabled && form.experiences.length === 0) {
    form.experiences.push({ company_name: "", start: "", end: "", experience: "" });
  }
});

// Actions
const addEducation = () => form.educations.push({ degree: "", institution: "", year: "" });
const removeEducation = () => {
  if (form.educations.length > 1) form.educations.pop();
  else toast.error({ title: "Error", message: "⚠️ At least one education is required", timeout: 3000, position: "topRight" });
};

const addSkill = () => form.skills.push({ skill: "", description: "" });
const removeSkill = () => {
  if (form.skills.length > 1) form.skills.pop();
  else toast.error({ title: "Error", message: "⚠️ At least one skill is required", timeout: 3000, position: "topRight" });
};

const addExperience = () => form.experiences.push({ company_name: "", start: "", end: "", experience: "" });
const removeExperience = () => {
  if (form.experiences.length > 1) form.experiences.pop();
  else toast.error({ title: "Error", message: "⚠️ At least one experience is required", timeout: 3000, position: "topRight" });
};

// Submit with strict backend compatibility + modal redirect
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
      toast.success({ title: "Success", message: rsp?.message || "Form submitted successfully.", timeout: 2000, position: "topRight" });
    }

    if (rsp?.redirect && rsp?.redirect_url) {
      lastApplicationId.value = rsp?.application_id || null;
      pendingRedirectUrl.value = rsp.redirect_url;
      showSuccessModal.value = true;

      // auto-redirect after ~2.5s
      redirectTimer && clearTimeout(redirectTimer);
      redirectTimer = setTimeout(() => {
        if (pendingRedirectUrl.value) {
          window.location.assign(pendingRedirectUrl.value);
        }
      }, 2500);
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
</script>

<style scoped>
.input {
  @apply border border-gray-300 dark:border-gray-700 rounded px-3 py-2 w-full bg-white dark:bg-gray-800 text-gray-800 dark:text-gray-100;
}
</style>
