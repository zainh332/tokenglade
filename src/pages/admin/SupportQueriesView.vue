<template>
  <div class="space-y-6">
    <!-- Filters & Stats Header -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider block">Total
            Inquiries</span>
          <span class="text-2xl font-bold text-gray-100 mt-1 block">{{ inquiries.length }}</span>
        </div>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider block">Pending
            Review</span>
          <span class="text-2xl font-bold text-amber-400 mt-1 block">{{ pendingCount }}</span>
        </div>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex items-center justify-between shadow-sm">
        <div>
          <span class="text-[10px] font-mono font-bold text-gray-500 uppercase tracking-wider block">Resolved</span>
          <span class="text-2xl font-bold text-emerald-400 mt-1 block">{{ resolvedCount }}</span>
        </div>
      </div>
    </div>

    <!-- Main Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="px-6 py-5 border-b border-gray-850 flex items-center justify-between flex-wrap gap-4">
        <div>
          <h3 class="text-sm font-bold text-gray-100 uppercase tracking-wide">Inquiries & Issue Reports</h3>
          <p class="text-[10px] text-gray-500 font-mono mt-0.5">Direct reports from users about token details or wallet
            labels</p>
        </div>
      </div>

      <!-- Table / Loader -->
      <div class="overflow-x-auto">
        <div v-if="loading" class="py-16 text-center space-y-3">
          <svg class="animate-spin h-6 w-6 mx-auto text-purple-500" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
          </svg>
          <span class="text-xs text-gray-500 font-mono">Fetching support inquiries...</span>
        </div>

        <div v-else-if="inquiries.length === 0" class="py-16 text-center space-y-2">
          <span class="text-3xl block">✉</span>
          <p class="text-xs text-gray-500 font-mono">No support inquiries or reports found.</p>
        </div>

        <table v-else class="w-full text-left text-xs border-collapse">
          <thead>
            <tr
              class="border-b border-gray-850 bg-gray-950/20 text-gray-400 font-mono uppercase text-[9px] tracking-wider">
              <th class="py-4 px-6">Token</th>
              <th class="py-4 px-6">Sender Details</th>
              <th class="py-4 px-6">Topic / Subject</th>
              <th class="py-4 px-6">Date</th>
              <th class="py-4 px-6">Status</th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850">
            <tr v-for="item in inquiries" :key="item.id" class="hover:bg-gray-850/20 transition">
              <!-- Token info -->
              <td class="py-4 px-6 font-semibold">
                <a :href="`/token-insight?asset_code=${item.asset_code}&issuer=${item.asset_issuer}`" target="_blank"
                  class="text-cyan-400 hover:text-cyan-300 font-mono block hover:underline">
                  {{ item.asset_code }}
                </a>
                <span class="text-[9px] font-mono text-gray-600 block mt-0.5 truncate max-w-[120px]"
                  :title="item.asset_issuer">
                  {{ item.asset_issuer }}
                </span>
              </td>
              <!-- Sender details -->
              <td class="py-4 px-6 space-y-0.5">
                <span class="text-white block font-medium">{{ item.name || 'Anonymous' }}</span>
                <span class="text-gray-500 font-mono block text-[10px]">{{ item.email }}</span>
              </td>
              <!-- Topic -->
              <td class="py-4 px-6 text-gray-300">
                <span
                  class="px-2 py-0.5 text-[10px] rounded bg-gray-800 border border-gray-700/50 text-slate-300 inline-block font-mono">
                  {{ item.topic }}
                </span>
              </td>
              <!-- Date -->
              <td class="py-4 px-6 text-gray-400 font-mono text-[10px]">
                {{ formatDateTime(item.created_at) }}
              </td>
              <!-- Status -->
              <td class="py-4 px-6">
                <span
                  class="px-2.5 py-1 rounded-full text-[9px] font-mono font-bold uppercase tracking-wider inline-block"
                  :class="[
                    item.status === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '',
                    item.status === 'resolved' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '',
                    item.status === 'ignored' ? 'bg-gray-800 text-gray-500 border border-gray-700' : ''
                  ]">
                  {{ item.status }}
                </span>
              </td>
              <!-- Actions -->
              <td class="py-4 px-6 text-right">
                <button @click="openDetails(item)"
                  class="px-3.5 py-1.5 font-bold rounded-lg border border-purple-500/20 hover:border-purple-500/40 text-purple-400 hover:bg-purple-500/5 transition">
                  View details
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Inquiry Details Modal -->
    <div v-if="activeInquiry"
      class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
      <div
        class="relative w-full max-w-2xl max-h-[90vh] bg-[#0f172a]/95 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col text-left text-xs">
        <div class="h-1.5 w-full bg-gradient-to-r from-purple-500 via-cyan-500 to-emerald-500"></div>

        <!-- Modal Header -->
        <div class="p-5 pb-3 border-b border-slate-850 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-white uppercase tracking-wide">Inquiry Details</h3>
            <p class="text-[10px] text-slate-500 font-mono uppercase mt-0.5">Reference ID: #{{ activeInquiry.id }}</p>
          </div>
          <button @click="activeInquiry = null"
            class="p-1.5 rounded-xl bg-slate-900/60 hover:bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition">
            ✕
          </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6 space-y-6 overflow-y-auto flex-1">
          <!-- Card Info Grid -->
          <div class="grid grid-cols-2 gap-4 bg-slate-950/30 border border-slate-850 rounded-2xl p-4">
            <div>
              <span class="block text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">Associated
                Token</span>
              <a :href="`/token-insight?asset_code=${activeInquiry.asset_code}&issuer=${activeInquiry.asset_issuer}`"
                target="_blank" class="text-cyan-400 hover:underline font-mono font-bold mt-1 block">
                {{ activeInquiry.asset_code }}
              </a>
            </div>
            <div>
              <span class="block text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">Subject
                Topic</span>
              <span class="text-white font-medium block mt-1">{{ activeInquiry.topic }}</span>
            </div>
            <div>
              <span class="block text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">Sender
                Name</span>
              <span class="text-white font-medium block mt-1">{{ activeInquiry.name || 'Anonymous' }}</span>
            </div>
            <div>
              <span class="block text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">Sender
                Email</span>
              <a :href="`mailto:${activeInquiry.email}`" class="text-purple-400 hover:underline font-mono block mt-1">{{
                activeInquiry.email }}</a>
            </div>
          </div>

          <!-- Message Body -->
          <div class="space-y-1.5">
            <span class="block text-[10px] font-mono font-bold text-slate-500 uppercase tracking-wider">User
              Message</span>
            <div
              class="bg-slate-950/50 border border-slate-850 rounded-2xl p-4 text-gray-300 leading-relaxed whitespace-pre-wrap">
              {{ activeInquiry.message }}
            </div>
          </div>

          <!-- Response Form -->
          <div class="space-y-3 pt-4 border-t border-slate-850">
            <h4 class="font-bold text-white uppercase tracking-wide text-[10px]">Resolve Action</h4>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-[10px] font-mono font-bold text-slate-500 uppercase mb-1.5">Update
                  Status</label>
                <select v-model="editForm.status"
                  class="w-full bg-slate-950 border border-slate-800 rounded-xl px-3 py-2 text-white focus:outline-none focus:border-purple-500 cursor-pointer">
                  <option value="pending">Pending Review</option>
                  <option value="resolved">Mark Resolved</option>
                  <option value="ignored">Ignore Report</option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-[10px] font-mono font-bold text-slate-500 uppercase mb-1.5">Resolution Notes
                (Optional)</label>
              <textarea v-model="editForm.reply" rows="3"
                placeholder="Log internal resolution steps or reply message..."
                class="w-full bg-slate-950 border border-slate-800 rounded-xl p-3 text-white focus:outline-none focus:border-purple-500 resize-none leading-relaxed"></textarea>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-5 border-t border-slate-850 flex items-center justify-end gap-3 bg-slate-950/20">
          <button @click="activeInquiry = null"
            class="px-4 py-2 border border-slate-800 hover:bg-slate-900 rounded-xl font-semibold text-slate-400 hover:text-white transition">
            Cancel
          </button>
          <button @click="saveInquiry" :disabled="saving"
            class="px-4 py-2 bg-purple-600 hover:bg-purple-500 disabled:opacity-50 text-white rounded-xl font-bold transition flex items-center gap-1.5">
            <svg v-if="saving" class="animate-spin h-3.5 w-3.5 text-white" xmlns="http://www.w3.org/2000/svg"
              fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
              </path>
            </svg>
            <span>Save resolution</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const inquiries = ref([]);
const loading = ref(true);
const saving = ref(false);

const activeInquiry = ref(null);
const editForm = ref({
  status: 'pending',
  reply: ''
});

const pendingCount = computed(() => inquiries.value.filter(i => i.status === 'pending').length);
const resolvedCount = computed(() => inquiries.value.filter(i => i.status === 'resolved').length);

const fetchInquiries = async () => {
  loading.value = true;
  try {
    const res = await axios.get('/api/admin/inquiries');
    if (res.data.status === 'success') {
      inquiries.value = res.data.inquiries || [];
    }
  } catch (err) {
    console.error("Error fetching support inquiries:", err);
  } finally {
    loading.value = false;
  }
};

const openDetails = (item) => {
  activeInquiry.value = item;
  editForm.value = {
    status: item.status || 'pending',
    reply: item.reply || ''
  };
};

const saveInquiry = async () => {
  saving.value = true;
  try {
    const res = await axios.post(`/api/admin/inquiries/${activeInquiry.value.id}/resolve`, editForm.value);
    if (res.data.status === 'success') {
      const idx = inquiries.value.findIndex(i => i.id === activeInquiry.value.id);
      if (idx !== -1) {
        inquiries.value[idx] = res.data.inquiry;
      }
      activeInquiry.value = null;
      window.dispatchEvent(new CustomEvent('admin-counts-updated'));
    }
  } catch (err) {
    console.error("Error resolving support inquiry:", err);
  } finally {
    saving.value = false;
  }
};

const formatDateTime = (str) => {
  if (!str) return '—';
  const d = new Date(str);
  return d.toLocaleString();
};

onMounted(() => {
  fetchInquiries();
});
</script>
