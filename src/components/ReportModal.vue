<template>
  <div v-if="modelValue"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-all duration-300">
    <!-- Modal Card -->
    <div
      class="relative w-full max-w-lg bg-theme-panel border border-theme-line rounded-3xl overflow-hidden shadow-2xl flex flex-col text-left">
      <!-- Gradient Decorative Header Line -->
      <div class="h-1.5 w-full bg-gradient-to-r from-purple-500 via-cyan-500 to-emerald-500"></div>

      <!-- Modal Header -->
      <div class="p-5 pb-4 border-b border-theme-line bg-theme-panel2 flex items-center justify-between">
        <div>
          <h3 class="text-base font-bold text-theme-ink font-display">Report Incorrect Info</h3>
          <p class="text-[10px] text-theme-dim uppercase mt-0.5 tracking-wider font-semibold">{{ token.asset_code }} —
            Support Inquiry</p>
        </div>
        <button @click="closeModal"
          class="p-1.5 rounded-xl bg-theme-panel hover:bg-theme-panel2 border border-theme-line text-theme-dim hover:text-theme-ink transition"
          aria-label="Close modal">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <form @submit.prevent="submitInquiry" class="p-5 space-y-4 text-xs" style="font-family: var(--disp), sans-serif;">
        <div v-if="submitError" class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
          {{ submitError }}
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-[10px] font-bold text-theme-dim uppercase mb-1.5">Your Name (Optional)</label>
            <input type="text" v-model="form.name" maxlength="100" placeholder="e.g. John Doe"
              class="w-full bg-theme-panel3 border border-theme-line rounded-xl px-3 py-2.5 text-theme-ink placeholder:text-theme-very-muted focus:outline-none focus:border-cyan-500/50 transition" />
          </div>
          <div>
            <label class="block text-[10px] font-bold text-theme-dim uppercase mb-1.5">Email Address</label>
            <input type="email" v-model="form.email" required maxlength="150" placeholder="e.g. contact@project.com"
              class="w-full bg-theme-panel3 border border-theme-line rounded-xl px-3 py-2.5 text-theme-ink placeholder:text-theme-very-muted focus:outline-none focus:border-cyan-500/50 transition" />
          </div>
        </div>

        <div>
          <label class="block text-[10px] font-bold text-theme-dim uppercase mb-1.5">Select Topic</label>
          <select v-model="form.topic" required
            class="w-full bg-theme-panel3 border border-theme-line rounded-xl px-3 py-2.5 text-theme-ink focus:outline-none focus:border-cyan-500/50 cursor-pointer transition">
            <option value="Incorrect About Details" class="bg-theme-panel text-theme-ink">Incorrect About Details</option>
            <option value="Stale/Incorrect Wallet Labels" class="bg-theme-panel text-theme-ink">Stale/Incorrect Wallet Labels</option>
            <option value="Broken Links or Socials" class="bg-theme-panel text-theme-ink">Broken Links or Socials</option>
            <option value="General Support/Feedback" class="bg-theme-panel text-theme-ink">General Support / Feedback</option>
            <option value="Others" class="bg-theme-panel text-theme-ink">Others</option>
          </select>
        </div>

        <div>
          <label class="block text-[10px] font-bold text-theme-dim uppercase mb-1.5">Message / Explanation</label>
          <textarea v-model="form.message" rows="4" required maxlength="2000"
            placeholder="Describe what is wrong or needs to be changed in detail (wallet addresses, correct links, descriptions, etc.)"
            class="w-full bg-theme-panel3 border border-theme-line rounded-xl p-3 text-theme-ink placeholder:text-theme-very-muted focus:outline-none focus:border-cyan-500/50 resize-none leading-relaxed transition"></textarea>
          <div class="flex justify-between items-center mt-1 px-1">
            <span class="text-[9px] text-theme-dim font-medium">Max 2,000 characters</span>
            <span class="text-[9px] font-medium"
              :class="form.message.length >= 1900 ? 'text-rose-400 font-bold' : 'text-theme-dim'">
              {{ form.message.length }} / 2,000
            </span>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-2">
          <button type="submit" :disabled="submitting || !isFormValid"
            class="w-full py-3 rounded-xl font-bold bg-gradient-to-r from-purple-600 to-cyan-600 text-white shadow-lg shadow-purple-500/10 hover:shadow-purple-500/20 active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none transition flex items-center justify-center gap-2">
            <svg v-if="submitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <span>{{ submitting ? 'Submitting Inquiry...' : 'Submit Inquiry' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const props = defineProps({
  modelValue: {
    type: Boolean,
    required: true
  },
  token: {
    type: Object,
    required: true
  }
});

const emit = defineEmits(['update:modelValue']);

const form = reactive({
  name: '',
  email: '',
  topic: 'Incorrect About Details',
  message: ''
});

const submitting = ref(false);
const submitError = ref('');

const isFormValid = computed(() => {
  return form.email.trim().length > 0 &&
    form.email.length <= 150 &&
    (form.name ? form.name.length <= 100 : true) &&
    form.message.trim().length > 0 &&
    form.message.length <= 2000 &&
    form.topic.trim().length > 0;
});

const closeModal = () => {
  emit('update:modelValue', false);
};

const submitInquiry = async () => {
  submitting.value = true;
  submitError.value = '';

  try {
    const res = await axios.post('/api/token/report-issue', {
      asset_code: props.token.asset_code || '',
      asset_issuer: props.token.issuer || '',
      name: form.name,
      email: form.email,
      topic: form.topic,
      message: form.message
    });

    if (res.data.status === 'success') {
      closeModal();
      Swal.fire({
        icon: 'success',
        title: 'Inquiry Submitted',
        text: res.data.message || 'Your inquiry has been received.',
        confirmButtonColor: '#7c3aed'
      });
      // Reset form
      form.name = '';
      form.email = '';
      form.topic = 'Incorrect About Details';
      form.message = '';
    } else {
      submitError.value = res.data.message || 'Failed to submit inquiry.';
    }
  } catch (err) {
    console.error(err);
    submitError.value = err.response?.data?.message || 'Connection error while submitting support inquiry.';
  } finally {
    submitting.value = false;
  }
};

watch(() => props.modelValue, (isOpen) => {
  if (isOpen) {
    submitError.value = '';
  }
});
</script>
