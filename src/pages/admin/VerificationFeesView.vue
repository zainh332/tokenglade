<template>
  <div class="space-y-6 text-left">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex flex-col justify-between w-full sm:w-80">
        <span class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Active Fee Assets</span>
        <span class="text-3xl font-extrabold text-white mt-2">{{ activeCount }}</span>
      </div>
      <button @click="openCreateModal"
        class="bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-black text-xs uppercase tracking-widest px-6 py-3.5 rounded-xl hover:opacity-95 active:scale-95 transition-all duration-200 shadow-md">
        Add Payment Asset
      </button>
    </div>

    <!-- Data Table Card -->
    <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl">
      <div class="p-6 border-b border-gray-800 flex items-center justify-between">
        <h3 class="text-base font-bold text-gray-100">Verification Payment Assets</h3>
        <button @click="loadData" class="text-xs text-purple-400 hover:text-purple-300 font-semibold">
          Refresh List
        </button>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-gray-950/40 text-xs font-bold text-gray-500 uppercase tracking-wider border-b border-gray-850">
              <th class="py-4 px-6">Asset Code</th>
              <th class="py-4 px-6">Issuer Account</th>
              <th class="py-4 px-6">Fee Amount</th>
              <th class="py-4 px-6">Sort Position</th>
              <th class="py-4 px-6">Status</th>
              <th class="py-4 px-6 text-right">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-850 text-sm text-gray-300">
            <tr v-for="asset in items" :key="asset.id" class="hover:bg-gray-850/30 transition">
              <td class="py-4 px-6">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                  {{ asset.asset_code }}
                </span>
              </td>
              <td class="py-4 px-6 font-mono text-xs text-gray-400" :title="asset.asset_issuer || 'Native Stellar Asset'">
                {{ asset.asset_issuer ? shortAddr(asset.asset_issuer) : 'Official XLM (Native)' }}
              </td>
              <td class="py-4 px-6 font-mono font-bold text-white">
                {{ formatAmount(asset.amount) }} {{ asset.asset_code }}
              </td>
              <td class="py-4 px-6 font-mono">{{ asset.position }}</td>
              <td class="py-4 px-6">
                <span :class="[
                  'px-2 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border',
                  asset.is_active 
                    ? 'bg-green-500/10 text-green-400 border-green-500/20' 
                    : 'bg-red-500/10 text-red-400 border-red-500/20'
                ]">
                  {{ asset.is_active ? 'Active' : 'Disabled' }}
                </span>
              </td>
              <td class="py-4 px-6 text-right space-x-3">
                <button @click="openEditModal(asset)" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold">
                  Edit
                </button>
                <button @click="deleteAsset(asset.id)" class="text-xs text-red-400 hover:text-red-300 font-semibold">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="!items.length && !loading">
              <td colspan="6" class="py-12 text-center text-gray-500">
                No verification payment assets defined.
              </td>
            </tr>
            <tr v-if="loading">
              <td colspan="6" class="py-12 text-center">
                <span class="w-6 h-6 border-2 border-purple-500/30 border-t-purple-500 rounded-full animate-spin inline-block"></span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Edit/Create Modal (Standard Tailwind overlay layout) -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/70 overflow-y-auto">
      <div class="relative w-full max-w-md bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-2xl p-6 text-left">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500 to-purple-500" />
        
        <h3 class="text-lg font-bold text-white mb-4">
          {{ form.id ? 'Edit Payment Asset' : 'Add Verification Payment Asset' }}
        </h3>

        <form @submit.prevent="saveAsset" class="space-y-4">
          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
              Asset Code
            </label>
            <input type="text" v-model="form.asset_code" required placeholder="e.g. TKG"
              class="w-full rounded-xl bg-gray-950 border border-gray-800 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500" />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
              Issuer Public Key (Optional for native XLM)
            </label>
            <input type="text" v-model="form.asset_issuer" placeholder="e.g. GC3L..."
              class="w-full rounded-xl bg-gray-950 border border-gray-800 px-4 py-2.5 text-sm font-mono text-white focus:outline-none focus:border-cyan-500" />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
              Fee Amount
            </label>
            <input type="number" step="any" v-model="form.amount" required placeholder="e.g. 500"
              class="w-full rounded-xl bg-gray-950 border border-gray-800 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500" />
          </div>

          <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-gray-400 mb-1">
              Sort Position
            </label>
            <input type="number" v-model="form.position" required placeholder="e.g. 0"
              class="w-full rounded-xl bg-gray-950 border border-gray-800 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-cyan-500" />
          </div>

          <div class="flex items-center gap-2 pt-2">
            <input type="checkbox" id="is_active" v-model="form.is_active"
              class="w-4 h-4 rounded border-gray-800 text-purple-600 focus:ring-purple-500" />
            <label for="is_active" class="text-sm text-gray-300 select-none">
              Asset is active and shown to users
            </label>
          </div>

          <!-- Buttons -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-800">
            <button type="button" @click="showModal = false"
              class="px-4 py-2.5 border border-gray-800 hover:bg-gray-800 rounded-xl text-xs font-bold text-gray-300 transition">
              CANCEL
            </button>
            <button type="submit" :disabled="saving"
              class="bg-gradient-to-r from-cyan-500 to-purple-500 text-white font-black text-xs uppercase tracking-widest px-6 py-2.5 rounded-xl hover:opacity-95 active:scale-95 transition disabled:opacity-50">
              {{ saving ? 'SAVING...' : 'SAVE ASSET' }}
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

const items = ref([]);
const loading = ref(false);
const saving = ref(false);
const showModal = ref(false);

const form = ref({
  id: null,
  asset_code: '',
  asset_issuer: '',
  amount: 0.0,
  position: 0,
  is_active: true
});

const activeCount = computed(() => {
  return items.value.filter(a => a.is_active).length;
});

async function loadData() {
  loading.value = true;
  try {
    const { data } = await axios.get('/api/admin/verification-fees');
    if (data.status === 'success') {
      items.value = data.data;
    }
  } catch (err) {
    console.error('Failed to load verification fees list:', err);
  } finally {
    loading.value = false;
  }
}

function openCreateModal() {
  form.value = {
    id: null,
    asset_code: '',
    asset_issuer: '',
    amount: 0.0,
    position: items.value.length,
    is_active: true
  };
  showModal.value = true;
}

function openEditModal(asset) {
  form.value = {
    id: asset.id,
    asset_code: asset.asset_code,
    asset_issuer: asset.asset_issuer || '',
    amount: parseFloat(asset.amount),
    position: asset.position,
    is_active: !!asset.is_active
  };
  showModal.value = true;
}

async function saveAsset() {
  saving.value = true;
  try {
    const { data } = await axios.post('/api/admin/verification-fees', form.value);
    if (data.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Success',
        text: data.message || 'Asset fee settings saved.'
      });
      showModal.value = false;
      await loadData();
    }
  } catch (err) {
    console.error(err);
    Swal.fire({
      icon: 'error',
      title: 'Save Failed',
      text: err.response?.data?.message || 'Failed to save verification asset fee.'
    });
  } finally {
    saving.value = false;
  }
}

async function deleteAsset(id) {
  const result = await Swal.fire({
    title: 'Are you sure?',
    text: "You won't be able to revert this!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  });

  if (result.isConfirmed) {
    try {
      const { data } = await axios.delete(`/api/admin/verification-fees/${id}`);
      if (data.status === 'success') {
        Swal.fire(
          'Deleted!',
          'Payment asset has been deleted.',
          'success'
        );
        await loadData();
      }
    } catch (err) {
      console.error(err);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: err.response?.data?.message || 'Failed to delete payment asset.'
      });
    }
  }
}

function formatAmount(amount) {
  return Number(amount).toLocaleString(undefined, {
    minimumFractionDigits: 0,
    maximumFractionDigits: 7
  });
}

function shortAddr(addr) {
  if (!addr) return '—';
  return addr.length > 12 ? `${addr.slice(0, 8)}...${addr.slice(-8)}` : addr;
}

onMounted(() => {
  loadData();
});
</script>
