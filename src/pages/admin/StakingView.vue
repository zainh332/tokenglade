<template>
  <div class="space-y-6">
    <!-- Summary KPI Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Staked Amount</span>
        <span class="text-3xl font-extrabold text-white mt-2 font-mono text-purple-400">
          {{ totalLockedAmount.toLocaleString() }} TKG
        </span>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Distributed Rewards</span>
        <span class="text-3xl font-extrabold text-white mt-2 font-mono text-pink-400">
          {{ totalDistributedRewards.toLocaleString() }} TKG
        </span>
      </div>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-100">Active Staking Positions</h3>
        <button @click="loadData(1)" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">
          Refresh Data
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th class="py-4 px-6">Staker Address</th>
              <th class="py-4 px-6">Staked Amount</th>
              <th class="py-4 px-6">Total Accrued Rewards</th>
              <th class="py-4 px-6">Status</th>
              <th class="py-4 px-6">Unlock Date</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="stake in items" :key="stake.id" class="hover:bg-gray-850/30 transition">
              <td class="py-4 px-6 font-mono text-xs select-all" :title="stake.address">{{ shortAddr(stake.address) }}</td>
              <td class="py-4 px-6 font-mono font-bold text-purple-400">{{ stake.locked_amount.toLocaleString(undefined, {minimumFractionDigits:2}) }} TKG</td>
              <td class="py-4 px-6 font-mono font-bold text-pink-400">{{ stake.total_rewards.toLocaleString(undefined, {minimumFractionDigits:7}) }} TKG</td>
              <td class="py-4 px-6">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold"
                  :class="stake.status === 'Active' ? 'bg-green-500/10 text-green-400 border border-green-500/20' : 'bg-gray-500/10 text-gray-400 border border-gray-500/20'"
                >
                  {{ stake.status }}
                </span>
              </td>
              <td class="py-4 px-6 font-mono text-xs text-gray-400">{{ formatDate(stake.unlock_date) }}</td>
            </tr>
            <tr v-if="!items.length && !loading">
              <td colspan="5" class="py-12 text-center text-gray-500">
                No active staking operations found.
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
import { ref, onMounted } from 'vue';
import axios from 'axios';

const items = ref([]);
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const totalLockedAmount = ref(0);
const totalDistributedRewards = ref(0);

async function loadData(page = 1) {
  if (page < 1 || page > totalPages.value) return;
  loading.value = true;
  try {
    const { data } = await axios.get(`/api/admin/staking?page=${page}`);
    if (data.status === 'success') {
      items.value = data.data;
      currentPage.value = data.meta.current_page;
      totalPages.value = data.meta.last_page;
      
      // Calculate totals
      totalLockedAmount.value = data.data.reduce((sum, item) => sum + item.locked_amount, 0);
      totalDistributedRewards.value = data.data.reduce((sum, item) => sum + item.total_rewards, 0);
    }
  } catch (err) {
    console.error('Failed to load staking snapshot analytics:', err);
  } finally {
    loading.value = false;
  }
}

function formatDate(isoStr) {
  if (!isoStr) return '—';
  return new Date(isoStr).toLocaleString();
}

function shortAddr(addr) {
  if (!addr) return '—';
  return addr.length > 12 ? `${addr.slice(0, 8)}...${addr.slice(-8)}` : addr;
}

onMounted(() => {
  loadData();
});
</script>
