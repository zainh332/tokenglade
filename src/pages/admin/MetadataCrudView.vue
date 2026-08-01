<template>
  <div class="space-y-8 text-left">
    <!-- Quick Analytics Stats Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex items-center justify-between shadow-xl">
        <div>
          <span class="text-xs text-gray-500 font-bold uppercase tracking-wider block">Project Categories</span>
          <span class="text-3xl font-extrabold text-white mt-2 block">{{ categories.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
      </div>

      <div class="bg-gray-900 border border-gray-800 rounded-3xl p-6 flex items-center justify-between shadow-xl">
        <div>
          <span class="text-xs text-gray-500 font-bold uppercase tracking-wider block">Wallet Labels</span>
          <span class="text-3xl font-extrabold text-white mt-2 block">{{ labels.length }}</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
      </div>
    </div>

    <!-- Main Dual Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      
      <!-- 1. Categories Card -->
      <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl flex flex-col h-[600px]">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between bg-gray-950/20 flex-shrink-0">
          <div>
            <h3 class="text-base font-bold text-gray-100">Project Categories</h3>
            <p class="text-xs text-gray-500 mt-1">Manage dynamic options for verification categories</p>
          </div>
          <button @click="openCreateCat" class="px-4 py-2 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 active:scale-95">
            + Add New
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4">
          <div v-if="catLoading" class="flex justify-center py-12">
            <span class="w-8 h-8 border-2 border-cyan-500/30 border-t-cyan-500 rounded-full animate-spin"></span>
          </div>
          <div v-else-if="!categories.length" class="text-center py-12 text-gray-500 text-sm">
            No categories defined yet.
          </div>
          <div v-else class="divide-y divide-gray-800/60">
            <div v-for="cat in categories" :key="cat.id" class="py-3 flex items-center justify-between group">
              <div class="flex-1 pr-4">
                <span class="text-sm text-gray-200 font-semibold tracking-wide">{{ cat.name }}</span>
              </div>
              <div class="flex items-center gap-3">
                <button @click="openEditCat(cat)" class="text-xs text-cyan-400 hover:text-cyan-300 font-semibold transition">
                  Edit
                </button>
                <span class="text-gray-700">|</span>
                <button @click="deleteCategory(cat.id)" class="text-xs text-rose-500 hover:text-rose-400 font-semibold transition">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Wallet Labels Card -->
      <div class="bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-xl flex flex-col h-[600px]">
        <div class="p-6 border-b border-gray-800 flex items-center justify-between bg-gray-950/20 flex-shrink-0">
          <div>
            <h3 class="text-base font-bold text-gray-100">Wallet Labels</h3>
            <p class="text-xs text-gray-500 mt-1">Manage custody and project wallet label categories</p>
          </div>
          <button @click="openCreateLabel" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition duration-150 active:scale-95">
            + Add New
          </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-4">
          <div v-if="labelLoading" class="flex justify-center py-12">
            <span class="w-8 h-8 border-2 border-purple-500/30 border-t-purple-500 rounded-full animate-spin"></span>
          </div>
          <div v-else-if="!labels.length" class="text-center py-12 text-gray-500 text-sm">
            No labels defined yet.
          </div>
          <div v-else class="divide-y divide-gray-800/60">
            <div v-for="label in labels" :key="label.id" class="py-3 flex items-center justify-between group">
              <div class="flex-1 pr-4">
                <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-purple-500/10 text-purple-400 border border-purple-500/20">
                  {{ label.name }}
                </span>
              </div>
              <div class="flex items-center gap-3">
                <button @click="openEditLabel(label)" class="text-xs text-purple-400 hover:text-purple-300 font-semibold transition">
                  Edit
                </button>
                <span class="text-gray-700">|</span>
                <button @click="deleteLabel(label.id)" class="text-xs text-rose-500 hover:text-rose-400 font-semibold transition">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Category Modal -->
    <div v-if="showCatModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-sm overflow-y-auto">
      <div class="relative w-full max-w-md bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-2xl p-6 text-left">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-cyan-500 to-purple-500" />
        
        <h3 class="text-lg font-bold text-white mb-4">
          {{ catForm.id ? 'Edit Category' : 'Create Category' }}
        </h3>

        <div v-if="catError" class="mb-4 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl font-medium">
          {{ catError }}
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-2">Category Name</label>
            <input type="text" v-model="catForm.name" placeholder="e.g. Real World Assets (RWA)"
              class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-cyan-500 transition" />
          </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
          <button @click="showCatModal = false" class="px-4 py-2 text-xs font-bold text-gray-400 hover:text-white transition">
            CANCEL
          </button>
          <button @click="saveCategory" :disabled="catSaving"
            class="px-5 py-2.5 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition disabled:opacity-50">
            {{ catSaving ? 'SAVING...' : 'SAVE' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Label Modal -->
    <div v-if="showLabelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-950/80 backdrop-blur-sm overflow-y-auto">
      <div class="relative w-full max-w-md bg-gray-900 border border-gray-800 rounded-3xl overflow-hidden shadow-2xl p-6 text-left">
        <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-purple-500 to-pink-500" />
        
        <h3 class="text-lg font-bold text-white mb-4">
          {{ labelForm.id ? 'Edit Wallet Label' : 'Create Wallet Label' }}
        </h3>

        <div v-if="labelError" class="mb-4 p-3 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl font-medium">
          {{ labelError }}
        </div>

        <div class="space-y-4">
          <div>
            <label class="block text-[10px] font-mono font-bold text-gray-500 uppercase mb-2">Label Name</label>
            <input type="text" v-model="labelForm.name" placeholder="e.g. Liquidity Rewards"
              class="w-full bg-gray-950 border border-gray-800 rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-purple-500 transition" />
          </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-3">
          <button @click="showLabelModal = false" class="px-4 py-2 text-xs font-bold text-gray-400 hover:text-white transition">
            CANCEL
          </button>
          <button @click="saveLabel" :disabled="labelSaving"
            class="px-5 py-2.5 bg-purple-600 hover:bg-purple-500 text-white font-bold text-xs uppercase tracking-wider rounded-xl transition disabled:opacity-50">
            {{ labelSaving ? 'SAVING...' : 'SAVE' }}
          </button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

// 1. Category state & logic
const categories = ref([])
const catLoading = ref(false)
const catSaving = ref(false)
const showCatModal = ref(false)
const catError = ref('')
const catForm = ref({ id: null, name: '' })

async function loadCategories() {
  catLoading.value = true
  try {
    const res = await axios.get('/api/admin/categories')
    if (res.data.status === 'success') {
      categories.value = res.data.data
    }
  } catch (err) {
    console.error('Failed to load categories', err)
  } finally {
    catLoading.value = false
  }
}

function openCreateCat() {
  catForm.value = { id: null, name: '' }
  catError.value = ''
  showCatModal.value = true
}

function openEditCat(cat) {
  catForm.value = { id: cat.id, name: cat.name }
  catError.value = ''
  showCatModal.value = true
}

async function saveCategory() {
  if (!catForm.value.name.trim()) {
    catError.value = 'Category name is required'
    return
  }

  catSaving.value = true
  catError.value = ''
  try {
    if (catForm.value.id) {
      await axios.put(`/api/admin/categories/${catForm.value.id}`, { name: catForm.value.name })
    } else {
      await axios.post('/api/admin/categories', { name: catForm.value.name })
    }
    showCatModal.value = false
    await loadCategories()
  } catch (err) {
    console.error(err)
    catError.value = err.response?.data?.message || 'Failed to save category.'
  } finally {
    catSaving.value = false
  }
}

async function deleteCategory(id) {
  if (!confirm('Are you sure you want to delete this category?')) return
  try {
    await axios.delete(`/api/admin/categories/${id}`)
    await loadCategories()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete category.')
  }
}

// 2. Label state & logic
const labels = ref([])
const labelLoading = ref(false)
const labelSaving = ref(false)
const showLabelModal = ref(false)
const labelError = ref('')
const labelForm = ref({ id: null, name: '' })

async function loadLabels() {
  labelLoading.value = true
  try {
    const res = await axios.get('/api/admin/wallet-labels')
    if (res.data.status === 'success') {
      labels.value = res.data.data
    }
  } catch (err) {
    console.error('Failed to load labels', err)
  } finally {
    labelLoading.value = false
  }
}

function openCreateLabel() {
  labelForm.value = { id: null, name: '' }
  labelError.value = ''
  showLabelModal.value = true
}

function openEditLabel(label) {
  labelForm.value = { id: label.id, name: label.name }
  labelError.value = ''
  showLabelModal.value = true
}

async function saveLabel() {
  if (!labelForm.value.name.trim()) {
    labelError.value = 'Label name is required'
    return
  }

  labelSaving.value = true
  labelError.value = ''
  try {
    if (labelForm.value.id) {
      await axios.put(`/api/admin/wallet-labels/${labelForm.value.id}`, { name: labelForm.value.name })
    } else {
      await axios.post('/api/admin/wallet-labels', { name: labelForm.value.name })
    }
    showLabelModal.value = false
    await loadLabels()
  } catch (err) {
    console.error(err)
    labelError.value = err.response?.data?.message || 'Failed to save label.'
  } finally {
    labelSaving.value = false
  }
}

async function deleteLabel(id) {
  if (!confirm('Are you sure you want to delete this wallet label?')) return
  try {
    await axios.delete(`/api/admin/wallet-labels/${id}`)
    await loadLabels()
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete label.')
  }
}

// Init
onMounted(() => {
  loadCategories()
  loadLabels()
})
</script>
