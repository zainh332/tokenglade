<template>
  <div class="h-screen flex bg-[#0b0c10] text-white overflow-hidden">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col z-20">
      <!-- Logo Header -->
      <div class="h-20 flex items-center px-6 border-b border-gray-800">
        <router-link to="/admin" class="flex items-center gap-2">
          <span class="text-xl font-bold bg-gradient-to-r from-cyan-400 via-purple-400 to-pink-400 bg-clip-text text-transparent">
            TokenGlade Admin
          </span>
        </router-link>
      </div>

      <!-- Nav Links -->
      <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in navItems"
          :key="item.name"
          :to="item.to"
          class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition"
          :class="isRouteActive(item.to) ? 'bg-purple-600 text-white shadow-lg shadow-purple-500/20' : 'text-gray-400 hover:text-white hover:bg-gray-800/50'"
        >
          <component :is="item.icon" class="w-5 h-5" />
          {{ item.name }}
        </router-link>
      </nav>

      <div class="p-4 border-t border-gray-800 bg-gray-950/40">
        <form action="/admin/logout" method="POST" class="w-full">
          <!-- Fetch csrf token for form validation -->
          <input type="hidden" name="_token" :value="csrfToken">
          <button 
            type="submit" 
            class="w-full text-center py-2.5 px-4 rounded-xl text-xs font-bold bg-red-600/10 text-red-400 border border-red-500/20 hover:bg-red-600 hover:text-white transition"
          >
            LOG OUT
          </button>
        </form>
      </div>
    </aside>

    <!-- Content Area Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <!-- Top header bar -->
      <header class="h-20 bg-gray-900 border-b border-gray-800 flex items-center justify-between px-8 z-10">
        <h2 class="text-xl font-bold text-gray-100">{{ currentPageTitle }}</h2>
        <div class="flex items-center gap-4">
          <a href="/" class="text-xs px-4 py-2 border border-gray-800 rounded-full hover:bg-gray-800 transition text-gray-300">
            View Homepage
          </a>
        </div>
      </header>

      <!-- Main Body -->
      <main class="flex-1 overflow-y-auto p-8 bg-[#0b0c10]">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import { getCookie } from './utils/utils.js';

// Icons represent placeholders as inline components to avoid import dependency limits
const WalletIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9-9c1.657 0 3 1.343 3 3v4c0 1.657-1.343 3-3 3m0-10a9 9 0 01-9 9" />
    </svg>
  `
};

const TokenIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
  `
};

const StakingIcon = {
  template: `
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewbox="0 0 24 24" stroke-width="2" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
    </svg>
  `
};

const route = useRoute();
const adminPk = ref(getCookie('public_key') || localStorage.getItem('public_key') || 'Not Connected');
const csrfToken = window.Laravel?.csrfToken || '';

const navItems = [
  { name: 'Connected Wallets', to: '/admin/wallets', icon: WalletIcon },
  { name: 'Minted Tokens', to: '/admin/tokens', icon: TokenIcon },
  { name: 'Staking Analytics', to: '/admin/staking', icon: StakingIcon },
];

const currentPageTitle = computed(() => {
  if (route.path.includes('/wallets')) return 'Wallets Base Registry';
  if (route.path.includes('/tokens')) return 'Minted Assets Inventory';
  if (route.path.includes('/staking')) return 'Staking Analytics Snapshot';
  return 'Admin dashboard';
});

function isRouteActive(path) {
  return route.path === path;
}

function shortAddress(addr) {
  if (!addr || addr.length < 10) return addr;
  return `${addr.slice(0, 6)}...${addr.slice(-6)}`;
}
</script>
