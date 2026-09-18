<template>
  <div class="space-y-8 text-left">
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
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Active Staking Tiers</span>
        <span class="text-3xl font-extrabold text-white mt-2 font-mono text-cyan-400">
          {{ activeTiersCount }} / {{ tiers.length }} Active
        </span>
      </div>
    </div>

    <!-- Staking Tiers & Reward Rates Management Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h3 class="text-base font-bold text-gray-100">Staking Tiers & Reward Rates</h3>
          <p class="text-xs text-gray-400 mt-1">
            Dynamic APY rates and staking threshold brackets. Changes reflect immediately on the frontend staking page and subsequent reward payouts.
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="loadTiers" class="text-xs text-purple-400 hover:text-purple-300 font-semibold transition">
            Refresh Tiers
          </button>
          <button @click="openCreateTierModal"
            class="bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-black text-xs uppercase tracking-widest px-5 py-2.5 rounded-xl hover:opacity-95 active:scale-95 transition-all duration-200 shadow-md">
            + Add Tier
          </button>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th class="py-4 px-6">Tier Level</th>
              <th class="py-4 px-6">Tier Name</th>
              <th class="py-4 px-6">Staked Bracket (TKG)</th>
              <th class="py-4 px-6">APY Rate</th>
              <th class="py-4 px-6">Active Stakers</th>
              <th class="py-4 px-6">Status</th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="tier in tiers" :key="tier.id" class="hover:bg-gray-850/30 transition">
              <td class="py-4 px-6">
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold font-mono"
                  :class="tier.tier === 4 ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
                    : tier.tier === 3 ? 'bg-teal-500/10 text-teal-400 border border-teal-500/20'
                    : tier.tier === 2 ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20'
                    : 'bg-purple-500/10 text-purple-400 border border-purple-500/20'">
                  Tier {{ tier.tier }}
                </span>
              </td>
              <td class="py-4 px-6 font-semibold text-white">
                {{ tier.name }}
              </td>
              <td class="py-4 px-6 font-mono text-gray-300">
                {{ tier.range }} TKG
              </td>
              <td class="py-4 px-6 font-mono font-bold text-cyan-400">
                <span class="px-2.5 py-1 rounded-lg bg-cyan-500/10 border border-cyan-500/20">
                  {{ Number(tier.apy).toFixed(2) }}% APY
                </span>
              </td>
              <td class="py-4 px-6 font-mono">
                <span class="text-xs font-bold text-gray-300">
                  {{ tier.active_stakers || 0 }} positions
                </span>
              </td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border',
                  tier.is_active 
                    ? 'bg-green-500/10 text-green-400 border-green-500/20' 
                    : 'bg-red-500/10 text-red-400 border-red-500/20'
                ]">
                  {{ tier.is_active ? 'Active' : 'Disabled' }}
                </span>
              </td>
              <td class="py-4 px-6 text-right space-x-3">
                <button @click="openEditTierModal(tier)" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition">
                  Edit
                </button>
                <button @click="deleteTier(tier.id)" class="text-xs text-red-400 hover:text-red-300 font-semibold transition">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!tiers.length && !loadingTiers">
              <td colspan="7" class="py-12 text-center text-gray-500">
                No staking tiers found. Click "+ Add Tier" to define one.
              </td>
            </tr>
            <tr v-if="loadingTiers">
              <td colspan="7" class="py-12 text-center">
                <span class="w-6 h-6 border-2 border-cyan-500/30 border-t-cyan-500 rounded-full animate-spin inline-block"></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Active Staking Positions Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-100">Active Staking Positions</h3>
        <button @click="loadData(1)" class="text-xs text-purple-400 hover:text-purple-300 font-semibold transition">
          Refresh Data
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th @click="sortBy('address')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Staker Address <span v-if="sortKey === 'address'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('locked_amount')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Staked Amount <span v-if="sortKey === 'locked_amount'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('total_rewards')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Total Accrued Rewards <span v-if="sortKey === 'total_rewards'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('status')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Status <span v-if="sortKey === 'status'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
              <th @click="sortBy('unlock_date')" class="py-4 px-6 cursor-pointer select-none hover:text-white transition">
                Unlock Date <span v-if="sortKey === 'unlock_date'">{{ sortOrder === 'asc' ? '▲' : '▼' }}</span>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="stake in sortedItems" :key="stake.id" class="hover:bg-gray-850/30 transition">
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

    <!-- Add / Edit Staking Tier Modal -->
    <div v-if="showTierModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-sm overflow-y-auto">
      <div class="relative w-full max-w-lg bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-2xl p-6 text-left">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500 via-purple-500 to-pink-500" />
        
        <div class="flex items-center justify-between mb-5">
          <h3 class="text-lg font-bold text-white">
            {{ tierForm.id ? 'Edit Staking Tier & Reward' : 'Add Staking Tier' }}
          </h3>
          <button @click="showTierModal = false" class="text-gray-400 hover:text-white text-sm">✕</button>
        </div>

        <form @submit.prevent="saveTier" class="space-y-4">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                Tier Level <span class="text-red-400">*</span>
              </label>
              <input 
                v-model.number="tierForm.tier" 
                type="number" 
                min="1" 
                step="1" 
                required
                placeholder="e.g. 1" 
                class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white text-sm focus:border-purple-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                Tier Name <span class="text-red-400">*</span>
              </label>
              <input 
                v-model="tierForm.name" 
                type="text" 
                required
                placeholder="e.g. Tier 1" 
                class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white text-sm focus:border-purple-500 focus:outline-none"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                Min Amount (TKG) <span class="text-red-400">*</span>
              </label>
              <input 
                v-model.number="tierForm.min_amount" 
                type="number" 
                min="0" 
                step="any" 
                required
                placeholder="e.g. 1500" 
                class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white text-sm font-mono focus:border-purple-500 focus:outline-none"
              />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
                Max Amount (TKG)
              </label>
              <input 
                v-model.number="tierForm.max_amount" 
                type="number" 
                min="0" 
                step="any" 
                placeholder="Leave blank for uncapped" 
                class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white text-sm font-mono focus:border-purple-500 focus:outline-none"
              />
              <span class="text-[10px] text-gray-500">Leave blank for highest tier (e.g. 100,000+)</span>
            </div>
          </div>

          <div>
            <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1.5">
              Annual Reward Rate (APY %) <span class="text-red-400">*</span>
            </label>
            <div class="relative">
              <input 
                v-model.number="tierForm.apy" 
                type="number" 
                min="0" 
                max="100" 
                step="0.01" 
                required
                placeholder="e.g. 12.00" 
                class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white text-sm font-mono focus:border-purple-500 focus:outline-none pr-12"
              />
              <span class="absolute right-4 top-2.5 text-xs text-gray-400 font-bold">% APY</span>
            </div>
            <p class="text-[10px] text-gray-500 mt-1">
              Projected daily rate: {{ ((tierForm.apy || 0) / 365).toFixed(4) }}% per day
            </p>
          </div>

          <div class="flex items-center justify-between pt-2 border-t border-gray-800">
            <div>
              <span class="text-xs font-bold text-gray-300">Tier Status</span>
              <p class="text-[10px] text-gray-500">Inactive tiers are hidden from frontend calculations.</p>
            </div>
            <label class="relative inline-flex items-center cursor-pointer">
              <input type="checkbox" v-model="tierForm.is_active" class="sr-only peer">
              <div class="w-11 h-6 bg-gray-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
            </label>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4">
            <button 
              type="button" 
              @click="showTierModal = false"
              class="px-5 py-2.5 border border-gray-800 rounded-xl text-xs font-semibold text-gray-400 hover:text-white transition"
            >
              Cancel
            </button>
            <button 
              type="submit" 
              :disabled="tierSaving"
              class="px-6 py-2.5 bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-bold text-xs rounded-xl hover:opacity-95 active:scale-95 disabled:opacity-50 transition"
            >
              <span v-if="tierSaving">Saving…</span>
              <span v-else>{{ tierForm.id ? 'Update Tier' : 'Create Tier' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';

// ---------- Positions & Global Stats State ----------
const items = ref([]);
const sortKey = ref('');
const sortOrder = ref('asc');
const loading = ref(false);
const currentPage = ref(1);
const totalPages = ref(1);
const totalLockedAmount = ref(0);
const totalDistributedRewards = ref(0);

// ---------- Dynamic Staking Tiers State ----------
const tiers = ref([]);
const loadingTiers = ref(false);
const showTierModal = ref(false);
const tierSaving = ref(false);
const tierForm = ref({
  id: null,
  tier: 1,
  name: '',
  min_amount: 1500,
  max_amount: null,
  apy: 12.00,
  is_active: true,
});

const activeTiersCount = computed(() => tiers.value.filter(t => t.is_active).length);

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

// ---------- Fetch Dynamic Staking Tiers ----------
async function loadTiers() {
  loadingTiers.value = true;
  try {
    const { data } = await axios.get('/api/admin/staking/tiers');
    if (data.status === 'success') {
      tiers.value = data.data;
    }
  } catch (err) {
    console.error('Failed to load staking tiers:', err);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: err.response?.data?.message || 'Failed to fetch staking tiers.',
    });
  } finally {
    loadingTiers.value = false;
  }
}

function openCreateTierModal() {
  const nextTierNum = tiers.value.length > 0 ? Math.max(...tiers.value.map(t => t.tier)) + 1 : 1;
  tierForm.value = {
    id: null,
    tier: nextTierNum,
    name: `Tier ${nextTierNum}`,
    min_amount: 1000,
    max_amount: null,
    apy: 12.00,
    is_active: true,
  };
  showTierModal.value = true;
}

function openEditTierModal(tier) {
  tierForm.value = {
    id: tier.id,
    tier: tier.tier,
    name: tier.name,
    min_amount: tier.min_amount,
    max_amount: tier.max_amount,
    apy: tier.apy,
    is_active: Boolean(tier.is_active),
  };
  showTierModal.value = true;
}

async function saveTier() {
  if (tierForm.value.max_amount !== null && tierForm.value.max_amount !== '' && tierForm.value.max_amount !== undefined) {
    if (Number(tierForm.value.max_amount) < Number(tierForm.value.min_amount)) {
      Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        text: 'Max amount must be greater than or equal to min amount.',
      });
      return;
    }
  } else {
    tierForm.value.max_amount = null;
  }

  tierSaving.value = true;
  try {
    if (tierForm.value.id) {
      const { data } = await axios.put(`/api/admin/staking/tiers/${tierForm.value.id}`, tierForm.value);
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Updated',
          text: data.message || 'Staking tier updated successfully.',
          timer: 1500,
          showConfirmButton: false,
        });
      }
    } else {
      const { data } = await axios.post('/api/admin/staking/tiers', tierForm.value);
      if (data.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'Created',
          text: data.message || 'Staking tier created successfully.',
          timer: 1500,
          showConfirmButton: false,
        });
      }
    }
    try {
      localStorage.removeItem('tokenglade_staking_tiers');
    } catch (e) {}
    showTierModal.value = false;
    await loadTiers();
  } catch (err) {
    console.error('Failed to save tier:', err);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: err.response?.data?.message || 'Failed to save staking tier.',
    });
  } finally {
    tierSaving.value = false;
  }
}

async function deleteTier(id) {
  const result = await Swal.fire({
    icon: 'warning',
    title: 'Delete Staking Tier?',
    text: 'Are you sure you want to remove this tier? Make sure to adjust remaining tiers so staking ranges remain clear.',
    showCancelButton: true,
    confirmButtonText: 'Yes, Delete',
    confirmButtonColor: '#ef4444',
  });

  if (!result.isConfirmed) return;

  try {
    const { data } = await axios.delete(`/api/admin/staking/tiers/${id}`);
    if (data.status === 'success') {
      try {
        localStorage.removeItem('tokenglade_staking_tiers');
      } catch (e) {}
      Swal.fire({
        icon: 'success',
        title: 'Deleted',
        text: data.message || 'Staking tier deleted.',
        timer: 1500,
        showConfirmButton: false,
      });
      await loadTiers();
    }
  } catch (err) {
    console.error('Failed to delete tier:', err);
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: err.response?.data?.message || 'Failed to delete staking tier.',
    });
  }
}

// ---------- Fetch Active Staking Positions ----------
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
  loadTiers();
  loadData();
});
</script>
