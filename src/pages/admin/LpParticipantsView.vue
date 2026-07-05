<template>
  <div class="space-y-6">
    <!-- Summary Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total LP Participants</span>
        <span class="text-3xl font-extrabold text-white mt-2">{{ stats.total_participants }}</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total TKG in Pool</span>
        <span class="text-3xl font-extrabold text-cyan-400 mt-2">{{ formatNumber(stats.total_tkg) }} TKG</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total XLM in Pool</span>
        <span class="text-3xl font-extrabold text-yellow-400 mt-2">{{ formatNumber(stats.total_xlm) }} XLM</span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Pool Shares</span>
        <span class="text-3xl font-extrabold text-purple-400 mt-2">{{ formatNumber(stats.total_shares) }}</span>
      </div>
    </div>

    <!-- LP Reward Configuration Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h4 class="text-sm font-bold text-gray-100">Weekly Reward Pool Configuration</h4>
        <p class="text-xs text-gray-500 mt-1">Configure the weekly token amount distributed to active LP participants.</p>
      </div>
      <div class="flex items-center gap-3">
        <div class="relative">
          <input 
            v-model="rewardAmount" 
            type="number" 
            min="0" 
            step="any"
            placeholder="e.g. 16000"
            class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-sm font-mono text-cyan-400 focus:outline-none focus:border-purple-500 transition w-48"
          />
          <span class="absolute right-4 top-2.5 text-xs text-gray-500 font-bold">TKG</span>
        </div>
        <button 
          @click="saveSettings" 
          :disabled="savingSettings"
          class="text-xs px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 disabled:bg-cyan-800 text-white font-bold rounded-xl transition flex items-center gap-2"
        >
          <span v-if="savingSettings" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block"></span>
          {{ savingSettings ? 'Saving...' : 'Save Configuration' }}
        </button>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-100">Liquidity Pool Participants</h3>
        <div class="flex items-center gap-3">
          <button 
            @click="syncData" 
            :disabled="syncing"
            class="text-xs px-4 py-2 bg-purple-600 hover:bg-purple-500 disabled:bg-purple-800 text-white font-bold rounded-xl transition flex items-center gap-2"
          >
            <span v-if="syncing" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin inline-block"></span>
            {{ syncing ? 'Syncing...' : 'Force Sync' }}
          </button>
          <button @click="loadData(1)" :disabled="loading" class="text-xs text-purple-400 hover:text-purple-300 font-semibold disabled:opacity-50">
            Refresh Table
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th @click="sortBy('wallet_address')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Wallet Address <span v-if="sortKey === 'wallet_address'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('wallet_status')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Stellar Status <span v-if="sortKey === 'wallet_status'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('is_active')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                App Status <span v-if="sortKey === 'is_active'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('pool_shares')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Shares <span v-if="sortKey === 'pool_shares'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('tkg_amount')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                TKG Added <span v-if="sortKey === 'tkg_amount'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('xlm_amount')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                XLM Added <span v-if="sortKey === 'xlm_amount'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('updated_at')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Last Synced <span v-if="sortKey === 'updated_at'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="user in sortedItems" :key="user.id" class="hover:bg-gray-850/30 transition">
              <td class="py-4 px-6 font-mono select-all text-xs" :title="user.wallet_address">{{ shortAddress(user.wallet_address) }}</td>
              <td class="py-4 px-6">
                <span 
                  class="px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="user.wallet_status === 'active' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-red-500/10 text-red-400 border border-red-500/20'"
                >
                  {{ user.wallet_status === 'active' ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="py-4 px-6">
                <span 
                  class="px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="user.is_active ? 'bg-purple-500/10 text-purple-400 border border-purple-500/20' : 'bg-gray-500/10 text-gray-400 border border-gray-500/20'"
                >
                  {{ user.is_active ? 'Connected' : 'Disconnected' }}
                </span>
              </td>
              <td class="py-4 px-6 font-mono text-purple-400">{{ formatNumber(user.pool_shares, 7) }}</td>
              <td class="py-4 px-6 text-cyan-400 font-mono font-bold">{{ formatNumber(user.tkg_amount, 7) }} TKG</td>
              <td class="py-4 px-6 text-yellow-400 font-mono font-bold">{{ formatNumber(user.xlm_amount, 7) }} XLM</td>
              <td class="py-4 px-6 text-xs text-gray-500">{{ formatDate(user.updated_at) }}</td>
            </tr>
            <tr v-if="!items.length && !loading">
              <td colspan="7" class="py-12 text-center text-gray-500">
                No liquidity pool participants found in database. Please click "Force Sync".
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

const items = ref([]);
const sortKey = ref('');
const sortOrder = ref('asc');

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
const syncing = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

const rewardAmount = ref(16000);
const savingSettings = ref(false);

const stats = ref({
  total_tkg: 0,
  total_xlm: 0,
  total_shares: 0,
  total_participants: 0,
});

async function loadData(page = 1) {
  if (page < 1 || (page > totalPages.value && totalPages.value > 0)) return;
  loading.value = true;
  try {
    const { data } = await axios.get(`/api/admin/lp-participants?page=${page}`);
    if (data.status === 'success') {
      items.value = data.data;
      stats.value = data.stats;
      currentPage.value = data.meta.current_page;
      totalPages.value = data.meta.last_page;
    }
  } catch (err) {
    console.error('Failed to load LP participants:', err);
  } finally {
    loading.value = false;
  }
}

async function syncData() {
  syncing.value = true;
  try {
    const { data } = await axios.post('/api/admin/lp-participants/sync');
    if (data.status === 'success') {
      alert(data.message);
      await loadData(1);
    }
  } catch (err) {
    console.error('Failed to sync LP participants:', err);
    alert('Sync failed. Please check backend logs.');
  } finally {
    syncing.value = false;
  }
}

function formatNumber(num, decimals = 2) {
  if (num === undefined || num === null) return '0.00';
  return parseFloat(num).toLocaleString(undefined, {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  });
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleString();
}

function shortAddress(addr) {
  if (!addr || addr.length < 12) return addr;
  return `${addr.slice(0, 6)}...${addr.slice(-6)}`;
}

async function loadSettings() {
  try {
    const { data } = await axios.get('/api/admin/settings');
    if (data.status === 'success') {
      rewardAmount.value = data.settings.lp_weekly_reward_amount;
    }
  } catch (err) {
    console.error('Failed to load settings:', err);
  }
}

async function saveSettings() {
  if (rewardAmount.value < 0) {
    alert('Reward amount must be a positive number.');
    return;
  }
  savingSettings.value = true;
  try {
    const { data } = await axios.post('/api/admin/settings', {
      lp_weekly_reward_amount: rewardAmount.value
    });
    if (data.status === 'success') {
      alert(data.message);
    }
  } catch (err) {
    console.error('Failed to save settings:', err);
    alert('Failed to save settings.');
  } finally {
    savingSettings.value = false;
  }
}

onMounted(() => {
  loadData();
  loadSettings();
});
</script>
