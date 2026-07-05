<template>
  <div class="space-y-6">
    <!-- Filter Panel -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 shadow-xl flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex-1 w-full md:w-auto">
        <h4 class="text-sm font-bold text-gray-100">LP Payout History Filters</h4>
        <p class="text-xs text-gray-500 mt-1 font-medium">Search by wallet address and filter by week number/cycle.</p>
      </div>

      <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
        <!-- Search Input -->
        <input 
          v-model="searchQuery" 
          @input="onSearch"
          type="text" 
          placeholder="Search Wallet Address..." 
          class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-300 placeholder-gray-600 focus:outline-none focus:border-purple-500 transition w-full sm:w-64"
        />

        <!-- Cycle Dropdown -->
        <select 
          v-model="selectedCycle" 
          @change="onFilterChange"
          class="bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-xs text-gray-300 focus:outline-none focus:border-purple-500 transition w-full sm:w-48 cursor-pointer"
        >
          <option value="">All Reward Cycles</option>
          <option v-for="cycle in cycles" :key="cycle.id" :value="cycle.id">
            Week {{ cycle.week_number }} ({{ formatDateShort(cycle.snapshot_date) }})
          </option>
        </select>

        <!-- Clear Button -->
        <button 
          v-if="searchQuery || selectedCycle"
          @click="clearFilters"
          class="text-xs font-bold text-red-400 hover:text-red-300 px-3 py-2 transition"
        >
          Clear Filters
        </button>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-100">Reward Distribution Logs</h3>
        <button @click="loadData(1)" :disabled="loading" class="text-xs text-purple-400 hover:text-purple-300 font-semibold disabled:opacity-50">
          Refresh List
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th @click="sortBy('wallet_address')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Wallet Address <span v-if="sortKey === 'wallet_address'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('cycle.week_number')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Cycle / Week <span v-if="sortKey === 'cycle.week_number'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('pool_share_percentage')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Pool Share <span v-if="sortKey === 'pool_share_percentage'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('reward_amount')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Amount Distributed <span v-if="sortKey === 'reward_amount'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('tx_hash')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                TX Hash <span v-if="sortKey === 'tx_hash'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('status')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Status <span v-if="sortKey === 'status'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('created_at')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Timestamp <span v-if="sortKey === 'created_at'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="log in sortedItems" :key="log.id" class="hover:bg-gray-850/30 transition">
              <td class="py-4 px-6 font-mono text-xs select-all" :title="log.wallet_address">
                {{ shortAddress(log.wallet_address) }}
              </td>
              <td class="py-4 px-6">
                <div v-if="log.cycle">
                  <span class="font-bold text-gray-200">Week {{ log.cycle.week_number }}</span>
                  <div class="text-[10px] text-gray-500 mt-0.5">Pool: {{ log.cycle.total_reward_pool.toLocaleString() }} TKG</div>
                </div>
                <span v-else class="text-gray-500">—</span>
              </td>
              <td class="py-4 px-6 font-mono text-gray-400">
                {{ log.pool_share_percentage.toFixed(4) }}%
              </td>
              <td class="py-4 px-6 font-mono font-bold text-cyan-400">
                {{ log.reward_amount.toLocaleString(undefined, {minimumFractionDigits: 4}) }} TKG
              </td>
              <td class="py-4 px-6 font-mono text-xs">
                <a 
                  v-if="log.tx_hash" 
                  :href="explorerUrl + log.tx_hash" 
                  target="_blank" 
                  class="text-purple-400 hover:text-purple-300 border-b border-dashed border-purple-500/30 hover:border-purple-300 transition"
                  title="View on Stellar.expert"
                >
                  {{ shortHash(log.tx_hash) }}
                </a>
                <span v-else class="text-gray-600">N/A</span>
              </td>
              <td class="py-4 px-6">
                <span 
                  class="px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="statusClasses(log.status)"
                >
                  {{ log.status }}
                </span>
              </td>
              <td class="py-4 px-6 text-xs text-gray-500">
                {{ formatDate(log.created_at) }}
              </td>
            </tr>
            <tr v-if="!items.length && !loading">
              <td colspan="7" class="py-12 text-center text-gray-500 font-medium">
                No distribution records found.
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
const cycles = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);

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

const searchQuery = ref('');
const selectedCycle = ref('');
const explorerUrl = ref('https://stellar.expert/explorer/public/tx/');

let searchTimeout = null;

async function loadData(page = 1) {
  if (page < 1 || (page > totalPages.value && totalPages.value > 0)) return;
  loading.value = true;
  try {
    const params = {
      page,
      search: searchQuery.value,
      cycle_id: selectedCycle.value,
    };
    const { data } = await axios.get('/api/admin/lp-history', { params });
    if (data.status === 'success') {
      items.value = data.data;
      cycles.value = data.cycles;
      currentPage.value = data.meta.current_page;
      totalPages.value = data.meta.last_page;
    }
  } catch (err) {
    console.error('Failed to load LP distribution history:', err);
  } finally {
    loading.value = false;
  }
}

async function loadEnvironment() {
  try {
    const { data } = await axios.get('/api/env');
    const net = data.stellar_env === 'public' ? 'public' : 'testnet';
    explorerUrl.value = `https://stellar.expert/explorer/${net}/tx/`;
  } catch (err) {
    console.error('Failed to load environment variables:', err);
  }
}

function onSearch() {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    loadData(1);
  }, 400);
}

function onFilterChange() {
  loadData(1);
}

function clearFilters() {
  searchQuery.value = '';
  selectedCycle.value = '';
  loadData(1);
}

function shortAddress(addr) {
  if (!addr || addr.length < 12) return addr;
  return `${addr.slice(0, 6)}...${addr.slice(-6)}`;
}

function shortHash(hash) {
  if (!hash || hash.length < 10) return hash;
  return `${hash.slice(0, 4)}...${hash.slice(-4)}`;
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleString();
}

function formatDateShort(isoStr) {
  if (!isoStr) return '—';
  const d = new Date(isoStr);
  return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
}

function statusClasses(status) {
  switch (status.toLowerCase()) {
    case 'sent':
      return 'bg-green-500/10 text-green-400 border border-green-500/20';
    case 'failed':
      return 'bg-red-500/10 text-red-400 border border-red-500/20';
    case 'pending':
    default:
      return 'bg-yellow-500/10 text-yellow-400 border border-yellow-500/20';
  }
}

onMounted(() => {
  loadData();
  loadEnvironment();
});
</script>
