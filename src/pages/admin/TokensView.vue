<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Minted Assets</span>
        <span class="text-3xl font-extrabold text-white mt-2">{{ totalCount }}</span>
      </div>
    </div>

    <!-- Token Minting & Creation Fees Configuration Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl text-left">
      <div class="mb-4 border-b border-gray-800 pb-3">
        <h4 class="text-sm font-bold text-gray-100">Token Minting Fee Configuration</h4>
        <p class="text-xs text-gray-500 mt-1">Configure the XLM fees and LP percentage allocation for minting new assets.</p>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-end">
        <!-- 1. Token Creation Fee -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide">Token Creation Fee</label>
          <div class="relative">
            <input 
              v-model="tokenCreationFee" 
              type="number" 
              min="0" 
              step="any"
              placeholder="e.g. 20"
              class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm font-mono text-cyan-400 focus:outline-none focus:border-purple-500 transition w-full"
            />
            <span class="absolute right-4 top-2.5 text-xs text-gray-500 font-bold">XLM</span>
          </div>
        </div>

        <!-- 2. Issuer Wallet Funding Amount -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide">Issuer Wallet Reserve</label>
          <div class="relative">
            <input 
              v-model="issuerWalletAmount" 
              type="number" 
              min="0" 
              step="any"
              placeholder="e.g. 1.2"
              class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm font-mono text-yellow-400 focus:outline-none focus:border-purple-500 transition w-full"
            />
            <span class="absolute right-4 top-2.5 text-xs text-gray-500 font-bold">XLM</span>
          </div>
        </div>

        <!-- 3. LP Allocation Percentage -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide">TKG/XLM LP Reward Allocation</label>
          <div class="relative">
            <input 
              v-model="feePercentageForLpPercent" 
              type="number" 
              min="0" 
              max="100" 
              step="any"
              placeholder="e.g. 70"
              class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm font-mono text-purple-400 focus:outline-none focus:border-purple-500 transition w-full"
            />
            <span class="absolute right-4 top-2.5 text-xs text-gray-500 font-bold">%</span>
          </div>
        </div>

        <!-- 4. Whale Activity Threshold -->
        <div class="space-y-2">
          <label class="block text-xs font-bold text-gray-400 uppercase tracking-wide">Whale Activity Threshold</label>
          <div class="relative">
            <input 
              v-model="whaleActivityThresholdXlm" 
              type="number" 
              min="0" 
              step="any"
              placeholder="e.g. 100"
              class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm font-mono text-cyan-400 focus:outline-none focus:border-purple-500 transition w-full"
            />
            <span class="absolute right-4 top-2.5 text-xs text-gray-500 font-bold">XLM</span>
          </div>
        </div>
      </div>

      <div class="mt-4 flex justify-end">
        <button 
          @click="saveSettings" 
          :disabled="savingSettings"
          class="text-xs px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-purple-500 hover:opacity-95 text-white font-bold rounded-xl transition flex items-center gap-2"
        >
          <span v-if="savingSettings" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block"></span>
          {{ savingSettings ? 'Saving...' : 'Save Configuration' }}
        </button>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-100">Minted Asset Inventory</h3>
        <button @click="loadData(1)" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">
          Refresh Inventory
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th @click="sortBy('code')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Asset Code <span v-if="sortKey === 'code'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('supply')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Total Supply <span v-if="sortKey === 'supply'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('issuer')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Issuer Public Key <span v-if="sortKey === 'issuer'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('creator')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Creator Wallet <span v-if="sortKey === 'creator'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('created_at')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Minted Date <span v-if="sortKey === 'created_at'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th class="py-4 px-6">Creation Fee</th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="token in sortedItems" :key="token.id" class="hover:bg-gray-850/30 transition">
              <td class="py-4 px-6">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">
                  {{ token.code }}
                </span>
              </td>
              <td class="py-4 px-6 font-mono font-bold">{{ token.supply.toLocaleString(undefined, {maximumFractionDigits:7}) }}</td>
              <td class="py-4 px-6 font-mono text-xs text-gray-400 select-all" :title="token.issuer">{{ shortAddr(token.issuer) }}</td>
              <td class="py-4 px-6 font-mono text-xs text-gray-400 select-all" :title="token.creator">{{ shortAddr(token.creator) }}</td>
              <td class="py-4 px-6">{{ formatDate(token.created_at) }}</td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-2">
                  <span 
                    v-if="token.fee_tx_status === 1" 
                    class="px-2 py-0.5 rounded text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20"
                  >
                    Paid
                  </span>
                  <span 
                    v-else 
                    class="px-2 py-0.5 rounded text-[11px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20"
                  >
                    Unpaid
                  </span>
                  
                  <a 
                    v-if="token.fee_tx_hash" 
                    :href="'https://stellar.expert/explorer/' + (stellarNetwork === 'public' ? 'public' : 'testnet') + '/tx/' + token.fee_tx_hash" 
                    target="_blank" 
                    class="text-xs text-cyan-400 hover:text-cyan-300 underline font-mono flex items-center gap-1"
                    title="View transaction on Stellar.Expert"
                  >
                    {{ token.fee_tx_hash.slice(0, 6) }}...{{ token.fee_tx_hash.slice(-6) }} ↗
                  </a>
                </div>
              </td>
              <td class="py-4 px-6 text-right">
                <button 
                  @click="confirmDeleteToken(token)" 
                  class="px-3 py-1.5 bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/20 hover:border-rose-500/30 rounded-lg text-xs font-bold transition flex items-center gap-1.5 ml-auto"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!items.length && !loading">
              <td colspan="7" class="py-12 text-center text-gray-500">
                No no-code minted assets found in database.
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="7" class="py-12 text-center">
                <span class="w-6 h-6 border-2 border-purple-500/30 border-t-purple-500 rounded-full animate-spin inline-block"></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="totalPages > 1" class="p-6 border-t border-gray-800 flex items-center justify-between bg-gray-950/20">
        <button 
          @click="loadData(currentPage - 1)" 
          :disabled="currentPage === 1"
          class="px-4 py-2 border border-gray-800 rounded-xl text-xs hover:bg-gray-800 disabled:opacity-30 disabled:hover:bg-transparent transition text-gray-300 font-semibold"
        >
          Previous
        </button>
        <span class="text-xs text-gray-500">Page {{ currentPage }} of {{ totalPages }}</span>
        <button 
          @click="loadData(currentPage + 1)" 
          :disabled="currentPage === totalPages"
          class="px-4 py-2 border border-gray-800 rounded-xl text-xs hover:bg-gray-800 disabled:opacity-30 disabled:hover:bg-transparent transition text-gray-300 font-semibold"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

const items = ref([]);
const sortKey = ref('');
const sortOrder = ref('asc');

const tokenCreationFee = ref(20);
const issuerWalletAmount = ref(1.2);
const feePercentageForLpPercent = ref(70);
const whaleActivityThresholdXlm = ref(100);
const savingSettings = ref(false);
const stellarNetwork = ref('public');

async function loadSettings() {
  try {
    const { data } = await axios.get('/api/admin/settings');
    if (data.status === 'success') {
      tokenCreationFee.value = data.settings.token_creation_fee;
      issuerWalletAmount.value = data.settings.issuer_wallet_amount;
      feePercentageForLpPercent.value = Math.round(data.settings.fee_percentage_for_lp * 100);
      whaleActivityThresholdXlm.value = data.settings.whale_activity_threshold_xlm;
    }
  } catch (err) {
    console.error('Failed to load minting settings:', err);
  }
}

async function saveSettings() {
  savingSettings.value = true;
  try {
    const { data } = await axios.post('/api/admin/settings', {
      token_creation_fee: tokenCreationFee.value,
      issuer_wallet_amount: issuerWalletAmount.value,
      fee_percentage_for_lp: feePercentageForLpPercent.value * 0.01,
      whale_activity_threshold_xlm: whaleActivityThresholdXlm.value
    });

    if (data.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Saved',
        text: data.message || 'Token creation fee configuration saved.'
      });
    }
  } catch (err) {
    console.error('Failed to save settings:', err);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: err.response?.data?.message || 'Failed to save settings.'
    });
  } finally {
    savingSettings.value = false;
  }
}

function sortBy(key) {
  if (sortKey.value === key) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    sortKey.value = key;
    sortOrder.value = 'asc';
  }
}

function getVal(obj, path) {
  if (!path) return '';
  return path.split('.').reduce((acc, part) => acc && acc[part], obj);
}

const sortedItems = computed(() => {
  if (!sortKey.value) return items.value;
  return [...items.value].sort((a, b) => {
    let aVal = getVal(a, sortKey.value);
    let bVal = getVal(b, sortKey.value);
    if (aVal === undefined || aVal === null) aVal = '';
    if (bVal === undefined || bVal === null) bVal = '';
    if (typeof aVal === 'string') aVal = aVal.toLowerCase();
    if (typeof bVal === 'string') bVal = bVal.toLowerCase();
    if (aVal < bVal) return sortOrder.value === 'asc' ? -1 : 1;
    if (aVal > bVal) return sortOrder.value === 'asc' ? 1 : -1;
    return 0;
  });
});
const loading = ref(false);
const totalCount = ref(0);
const currentPage = ref(1);
const totalPages = ref(1);

async function loadData(page = 1) {
  if (page < 1 || page > totalPages.value) return;
  loading.value = true;
  try {
    const { data } = await axios.get(`/api/admin/tokens?page=${page}`);
    if (data.status === 'success') {
      items.value = data.data;
      currentPage.value = data.meta.current_page;
      totalPages.value = data.meta.last_page;
      totalCount.value = data.meta.total;
    }
  } catch (err) {
    console.error('Failed to load minted tokens inventory:', err);
  } finally {
    loading.value = false;
  }
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleDateString();
}

function shortAddr(addr) {
  if (!addr) return '—';
  return addr.length > 12 ? `${addr.slice(0, 8)}...${addr.slice(-8)}` : addr;
}

async function confirmDeleteToken(token) {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: `This will permanently delete the token ${token.code} and all associated platform records from the database. This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ef4444',
    cancelButtonColor: '#374151',
    confirmButtonText: 'Yes, delete it!',
    cancelButtonText: 'Cancel'
  });

  if (result.isConfirmed) {
    try {
      const { data } = await axios.delete(`/api/admin/tokens/${token.id}`);
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Deleted!',
          text: data.message || 'Token deleted successfully.',
          timer: 1500,
          showConfirmButton: false
        });
        loadData(currentPage.value);
      }
    } catch (err) {
      console.error('Failed to delete token:', err);
      Swal.fire({
        icon: 'error',
        title: 'Delete Failed',
        text: err.response?.data?.message || 'An error occurred while deleting the token.'
      });
    }
  }
}

async function loadNetwork() {
  try {
    const { data } = await axios.get('/api/env');
    stellarNetwork.value = data?.stellar_env || 'public';
  } catch (err) {
    console.error('Failed to load network environment:', err);
  }
}

onMounted(() => {
  loadData();
  loadSettings();
  loadNetwork();
});
</script>
