<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Minted Assets</span>
        <span class="text-3xl font-extrabold text-white mt-2">{{ totalCount }}</span>
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
            </tr>
            <tr v-if="!items.length && !loading">
              <td colspan="5" class="py-12 text-center text-gray-500">
                No no-code minted assets found in database.
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="5" class="py-12 text-center">
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

onMounted(() => {
  loadData();
});
</script>
