<template>
  <div class="space-y-6">
    <!-- Summary Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Registered users</span>
        <span class="text-3xl font-extrabold text-white mt-2">{{ totalCount }}</span>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-100">Wallet Registry</h3>
        <button @click="loadData(1)" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">
          Refresh Table
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th class="py-4 px-6">Public Key (Wallet)</th>
              <th class="py-4 px-6">Native Balance (XLM)</th>
              <th class="py-4 px-6">Connected date</th>
              <th class="py-4 px-6">Last active</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="user in items" :key="user.id" class="hover:bg-gray-850/30 transition">
              <td class="py-4 px-6 font-mono select-all">{{ user.address }}</td>
              <td class="py-4 px-6 text-yellow-400 font-mono font-bold">{{ user.balance.toLocaleString(undefined, {minimumFractionDigits:2}) }} XLM</td>
              <td class="py-4 px-6">{{ formatDate(user.created_at) }}</td>
              <td class="py-4 px-6 text-xs text-gray-500">{{ formatDate(user.last_active) }}</td>
            </tr>
            <tr v-if="!items.length && !loading">
              <td colspan="4" class="py-12 text-center text-gray-500">
                No connected wallets found in system base.
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="4" class="py-12 text-center">
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
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(false);
const totalCount = ref(0);
const currentPage = ref(1);
const totalPages = ref(1);

async function loadData(page = 1) {
  if (page < 1 || page > totalPages.value) return;
  loading.value = true;
  try {
    const { data } = await axios.get(`/api/admin/wallets?page=${page}`);
    if (data.status === 'success') {
      items.value = data.data;
      currentPage.value = data.meta.current_page;
      totalPages.value = data.meta.last_page;
      totalCount.value = data.meta.total;
    }
  } catch (err) {
    console.error('Failed to load wallets data:', err);
  } finally {
    loading.value = false;
  }
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleString();
}

onMounted(() => {
  loadData();
});
</script>
