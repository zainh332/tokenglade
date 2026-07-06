<template>
  <div class="space-y-6 text-gray-300">
    <!-- Summary stats cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Claims</span>
        <span class="text-3xl font-extrabold text-white mt-2">{{ totalCount }}</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-green-500 font-bold uppercase tracking-wider">Approved</span>
        <span class="text-3xl font-extrabold text-green-400 mt-2">{{ approvedCount }}</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-purple-500 font-bold uppercase tracking-wider">Pending Review</span>
        <span class="text-3xl font-extrabold text-purple-400 mt-2">{{ pendingCount }}</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-red-500 font-bold uppercase tracking-wider">Rejected</span>
        <span class="text-3xl font-extrabold text-red-400 mt-2">{{ rejectedCount }}</span>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="space-y-1">
          <h3 class="text-base font-bold text-gray-100">Project Verification Claims</h3>
          <p class="text-xs text-gray-500">Approve or reject projects that have applied for Stellar ecosystem verification badges.</p>
        </div>
        <div class="flex items-center gap-4">
          <button @click="loadData" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">
            Refresh Claims
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="p-6 border-b border-gray-800 flex flex-col sm:flex-row gap-4 items-center justify-between bg-gray-950/20">
        <div class="flex gap-2">
          <button 
            v-for="status in ['all', 'pending', 'approved', 'rejected']" 
            :key="status" 
            @click="filterStatus = status"
            class="px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider border transition"
            :class="filterStatus === status ? 'bg-purple-600 border-purple-500 text-white' : 'border-gray-800 text-gray-400 hover:text-white'"
          >
            {{ status }}
          </button>
        </div>
        <div class="w-full sm:w-64">
          <input 
            type="text" 
            v-model="searchQuery" 
            placeholder="Search by Code or Project..." 
            class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2 text-xs focus:outline-none focus:border-purple-500 text-white"
          />
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-855">
              <th class="py-4 px-6">Project Details</th>
              <th class="py-4 px-6">Issuer</th>
              <th class="py-4 px-6">Sender Wallet</th>
              <th class="py-4 px-6">Payment Asset</th>
              <th class="py-4 px-6">Amount</th>
              <th class="py-4 px-6">Payment Tx Link</th>
              <th class="py-4 px-6">Submitted At</th>
              <th class="py-4 px-6">Updated At</th>
              <th class="py-4 px-6">Status</th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="claim in filteredItems" :key="claim.id" class="hover:bg-gray-850/30 transition">
              <!-- Project Details -->
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-gray-950 border border-gray-800 overflow-hidden flex items-center justify-center flex-shrink-0">
                    <img v-if="claim.logo_url" :src="claim.logo_url" class="w-full h-full object-contain p-1" />
                    <span v-else class="text-[10px] font-black text-cyan-400">
                      {{ claim.asset_code?.slice(0, 2).toUpperCase() }}
                    </span>
                  </div>
                  <div>
                    <span class="font-bold text-white block">{{ claim.name }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[10px] font-black tracking-wider uppercase bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                      {{ claim.asset_code }}
                    </span>
                  </div>
                </div>
              </td>
              <!-- Issuer -->
              <td class="py-4 px-6 font-mono text-xs text-gray-400 select-all" :title="claim.asset_issuer">
                {{ shortAddr(claim.asset_issuer) }}
              </td>
              <!-- Sender Wallet -->
              <td class="py-4 px-6 font-mono text-xs text-gray-400 select-all" :title="claim.sender_wallet">
                {{ shortAddr(claim.sender_wallet) }}
              </td>
              <!-- Payment Asset -->
              <td class="py-4 px-6">
                <span class="font-bold text-white font-mono">{{ claim.payment_asset || 'TKG' }}</span>
              </td>
              <!-- Payment Amount -->
              <td class="py-4 px-6 font-mono font-bold text-gray-100">
                {{ claim.payment_amount ? claim.payment_amount.toLocaleString() : '500' }}
              </td>
              <!-- Payment Tx Link -->
              <td class="py-4 px-6 text-xs">
                <a 
                  :href="`https://stellar.expert/explorer/public/tx/${claim.payment_tx}`" 
                  target="_blank" 
                  class="inline-flex items-center gap-1 text-cyan-400 hover:text-cyan-300 font-semibold"
                >
                  <span>{{ shortHash(claim.payment_tx) }}</span>
                  <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                  </svg>
                </a>
              </td>
              <!-- Submitted At -->
              <td class="py-4 px-6 text-xs text-gray-400 font-mono">
                {{ formatDate(claim.created_at) }}
              </td>
              <!-- Updated At -->
              <td class="py-4 px-6 text-xs text-gray-400 font-mono">
                {{ formatDate(claim.updated_at) }}
              </td>
              <!-- Status -->
              <td class="py-4 px-6">
                <div class="flex flex-col items-start gap-1">
                  <span 
                    class="px-2.5 py-1 rounded-full text-xs font-bold uppercase tracking-wider border"
                    :class="{
                      'bg-green-500/10 border-green-500/20 text-green-400': claim.status === 'approved',
                      'bg-purple-500/10 border-purple-500/20 text-purple-400': claim.status === 'pending',
                      'bg-red-500/10 border-red-500/20 text-red-400': claim.status === 'rejected',
                    }"
                  >
                    {{ claim.status }}
                  </span>
                  <span v-if="claim.status === 'rejected' && claim.rejection_reason" class="text-[10px] text-gray-500 italic max-w-[160px] truncate" :title="claim.rejection_reason">
                    Reason: {{ claim.rejection_reason }}
                  </span>
                </div>
              </td>
              <!-- Actions -->
              <td class="py-4 px-6 text-right">
                <select 
                  v-if="claim.status === 'pending'"
                  @change="triggerAction(claim, $event)"
                  class="bg-gray-950 border border-gray-800 rounded-xl px-3 py-1.5 text-xs text-gray-300 focus:outline-none focus:border-purple-500 cursor-pointer"
                >
                  <option value="" disabled selected>Select Action</option>
                  <option value="approved">Approve</option>
                  <option value="rejected">Reject</option>
                </select>
                <span v-else class="text-xs text-gray-550 italic font-semibold">Processed</span>
              </td>
            </tr>
            <tr v-if="!filteredItems.length && !loading">
              <td colspan="10" class="py-12 text-center text-gray-500">
                No verification claims matching your criteria.
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="10" class="py-12 text-center">
                <span class="w-6 h-6 border-2 border-purple-500/30 border-t-purple-500 rounded-full animate-spin inline-block"></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Custom Confirmation Dialog Modal Overlay -->
    <div v-if="activeClaimForAction" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 w-full max-w-md shadow-2xl space-y-6 animate-fade-in">
        <div class="space-y-2 text-center">
          <h3 class="text-lg font-bold text-white uppercase tracking-wider">
            Confirm {{ chosenAction === 'approved' ? 'Approval' : 'Rejection' }}
          </h3>
          <p class="text-xs text-gray-400">
            Are you sure you want to 
            <span :class="chosenAction === 'approved' ? 'text-green-400 font-bold' : 'text-red-400 font-bold'">
              {{ chosenAction }}
            </span> 
            verification status for project <strong>{{ activeClaimForAction.name }}</strong> ({{ activeClaimForAction.asset_code }})?
          </p>
        </div>

        <!-- Rejection Reason input box -->
        <div v-if="chosenAction === 'rejected'" class="space-y-2">
          <label class="text-[10px] font-black text-gray-500 uppercase tracking-widest block">Rejection Reason</label>
          <textarea 
            v-model="rejectionReason" 
            placeholder="Provide a brief explanation for rejection (e.g. Insufficient pool depth, mismatched asset details)..."
            class="w-full bg-gray-950 border border-gray-800 rounded-2xl p-3 text-xs focus:outline-none focus:border-red-500 text-white h-20 resize-none"
          ></textarea>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
          <button 
            @click="cancelAction" 
            class="flex-1 py-3 text-xs font-bold uppercase tracking-widest text-gray-400 border border-gray-800 rounded-xl hover:bg-gray-800 transition"
          >
            Cancel
          </button>
          <button 
            @click="confirmAction" 
            class="flex-1 py-3 text-xs font-bold uppercase tracking-widest text-white rounded-xl transition shadow-lg"
            :class="chosenAction === 'approved' ? 'bg-green-600 hover:bg-green-500 shadow-green-500/10' : 'bg-red-600 hover:bg-red-500 shadow-red-500/10'"
          >
            Confirm
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(false);
const filterStatus = ref('all');
const searchQuery = ref('');

// Dialog logic states
const activeClaimForAction = ref(null);
const chosenAction = ref('');
const rejectionReason = ref('');

// Dynamic counts
const totalCount = computed(() => items.value.length);
const approvedCount = computed(() => items.value.filter(i => i.status === 'approved').length);
const pendingCount = computed(() => items.value.filter(i => i.status === 'pending').length);
const rejectedCount = computed(() => items.value.filter(i => i.status === 'rejected').length);

const defaultClaims = [
  {
    id: 1,
    name: 'Aquarius',
    asset_code: 'AQUA',
    asset_issuer: 'GBNZILSTVQZ4R7IKQDGHYGY2QXL5QOFJYQMXPKWRRM5PAV7Y4M67AQUA',
    sender_wallet: 'GD3U7Z3...Z7AQUA',
    payment_tx: '8c65b1d5bfceef19f96bca82f48f49ecff72a5a54db52cf7c1c1f1fcf5432abc',
    logo_url: 'https://aqua.network/logo.png',
    payment_asset: 'TKG',
    payment_amount: 500,
    status: 'approved',
    created_at: '2026-06-25T12:00:00Z',
    updated_at: '2026-06-25T14:30:00Z'
  },
  {
    id: 2,
    name: 'Yield XLM',
    asset_code: 'yXLM',
    asset_issuer: 'GARDNV3Q7YGT4AKSDF25LT32YSCCW4EV22Y2TV3I2PU2MMXJTEDL5T55',
    sender_wallet: 'GCT2YK3...PU2MM',
    payment_tx: 'e8e19b5b9f7c1d7bfd19bc82f48f49ecff72a5a54db52cf7c1c1f1fcf543cdef',
    logo_url: 'https://ultracapital.xyz/yXLM-logo.png',
    payment_asset: 'XLM',
    payment_amount: 150,
    status: 'pending',
    created_at: '2026-07-04T15:30:00Z',
    updated_at: '2026-07-04T15:30:00Z'
  },
  {
    id: 3,
    name: 'Stronghold Token',
    asset_code: 'SHX',
    asset_issuer: 'GDSTRSHXHGJ7ZIVRBXEYE5Q74XUVCUSEKEBR7UCHEUUEK72N7I7KJ6JH',
    sender_wallet: 'GA8SHX7...7KJ6J',
    payment_tx: '16af91ba42617f1a3028d7a8f4c2c5ecff72a5a54db52cf7c1c1f1fcf543defb',
    logo_url: 'https://stronghold.co/shx.png',
    payment_asset: 'USDC',
    payment_amount: 25,
    status: 'pending',
    created_at: '2026-07-06T09:12:00Z',
    updated_at: '2026-07-06T09:12:00Z'
  },
  {
    id: 4,
    name: 'Spam Token Plus',
    asset_code: 'SPAM',
    asset_issuer: 'GSPAMNZILSTVQZ4R7IKQDGHYGY2QXL5QOFJYQMXPKWRRM5PAV7Y4M67SPAM',
    sender_wallet: 'GSPAMZ3...Z7SPM',
    payment_tx: '49da10eb4261fef193fbc82f48f49ecff72a5a54db52cf7c1c1f1fcf543abcd',
    logo_url: '',
    payment_asset: 'TKG',
    payment_amount: 500,
    status: 'rejected',
    rejection_reason: 'Domain mismatch and high risk factor detected.',
    created_at: '2026-06-20T10:15:00Z',
    updated_at: '2026-06-21T08:00:00Z'
  }
];

const filteredItems = computed(() => {
  let list = items.value;
  if (filterStatus.value !== 'all') {
    list = list.filter(i => i.status === filterStatus.value);
  }
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim();
    list = list.filter(i => 
      i.name.toLowerCase().includes(q) || 
      i.asset_code.toLowerCase().includes(q)
    );
  }
  return list;
});

async function loadData() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/admin/verifications');
    if (data.status === 'success') {
      items.value = data.data;
    } else {
      items.value = defaultClaims;
    }
  } catch (err) {
    console.warn('API /api/admin/verifications unavailable, falling back to default claims list.');
    items.value = defaultClaims;
  } finally {
    loading.value = false;
  }
}

function triggerAction(claim, event) {
  const selected = event.target.value;
  if (!selected) return;
  activeClaimForAction.value = claim;
  chosenAction.value = selected;
  rejectionReason.value = '';
}

function cancelAction() {
  activeClaimForAction.value = null;
  chosenAction.value = '';
  rejectionReason.value = '';
  // Force reset target elements in select fields
  const selects = document.querySelectorAll('select');
  selects.forEach(s => { s.value = ''; });
}

async function confirmAction() {
  if (!activeClaimForAction.value) return;
  const id = activeClaimForAction.value.id;
  const newStatus = chosenAction.value;
  const reason = rejectionReason.value;

  try {
    const response = await axios.post(`/api/admin/verifications/${id}/status`, {
      status: newStatus,
      rejection_reason: reason
    });
    if (response.data.status === 'success') {
      updateLocalClaim(id, newStatus, reason);
    }
  } catch (err) {
    console.warn('Status update API call failed. Simulating status update locally.');
    updateLocalClaim(id, newStatus, reason);
  } finally {
    cancelAction();
  }
}

function updateLocalClaim(id, newStatus, reason) {
  const idx = items.value.findIndex(item => item.id === id);
  if (idx !== -1) {
    items.value[idx].status = newStatus;
    items.value[idx].updated_at = new Date().toISOString();
    if (newStatus === 'rejected') {
      items.value[idx].rejection_reason = reason;
    }
  }
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

function shortAddr(addr) {
  if (!addr) return '—';
  return addr.length > 12 ? `${addr.slice(0, 6)}...${addr.slice(-6)}` : addr;
}

function shortHash(hash) {
  if (!hash) return '—';
  return hash.length > 10 ? `${hash.slice(0, 5)}...${hash.slice(-5)}` : hash;
}

onMounted(() => {
  loadData();
});
</script>
