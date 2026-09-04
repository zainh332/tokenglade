<template>
  <!-- FIXED/STICKY NAVIGATION HEADER (pins to top-0 when ticker scrolls away) -->
  <Disclosure as="nav"
    class="sticky top-0 z-[50] w-full border-b border-theme-line bg-theme-panel/90 backdrop-blur-md"
    v-slot="{ open, close }">

    <!-- MAIN NAVIGATION -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-[58px] items-center">
        
        <!-- Left: Logo & Links -->
        <div class="flex items-center gap-8">
          <router-link to="/" class="flex items-center gap-2 flex-shrink-0">
            <img class="w-8 h-8 object-contain" :src="logo" alt="TokenGlade Logo" />
            <span class="text-base font-black text-white tracking-tight uppercase">Token<span class="text-cyan-400">Glade</span></span>
          </router-link>
          
          <div class="hidden lg:flex items-center space-x-6">
            <router-link to="/stake" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors">Staking</router-link>
            <button @click="triggerLaunchToken" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors focus:outline-none">Launch Token</button>
          </div>
        </div>
        
        <!-- Right: Actions -->
        <div class="hidden lg:flex items-center gap-4">
          <!-- Search Box Input (Inline dropdown autocomplete) -->
          <div class="relative" ref="searchContainer">
            <div class="hsearch flex items-center gap-2">
              <MagnifyingGlassIcon class="w-3.5 h-3.5 text-slate-500 flex-shrink-0" />
              <input 
                v-model="searchQuery" 
                @focus="isFocused = true"
                @keydown.enter="handleEnterKey"
                type="text" 
                placeholder="Search token, wallet, or tx hash..." 
                class="bg-transparent border-0 outline-none text-xs text-white placeholder-slate-400 placeholder:font-sans font-sans font-medium w-[260px] p-0 focus:ring-0"
              />
              <span v-if="loading && searchQuery.trim() !== ''" class="animate-spin rounded-full h-3 w-3 border-b-2 border-cyan-400 flex-shrink-0"></span>
            </div>

            <!-- Autocomplete dropdown -->
            <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
              <div v-if="isFocused" class="hsearch-dropdown absolute right-0 mt-2 w-[380px] bg-theme-panel border border-theme-line rounded-xl shadow-2xl z-[99] max-h-[300px] overflow-y-auto custom-scrollbar divide-y divide-theme-line">
                <!-- If search input is empty -->
                <div v-if="searchQuery.trim() === ''" class="p-4 text-center text-xs text-theme-dim font-sans leading-relaxed">
                  Search by token (e.g. <span class="text-cyan-500 dark:text-cyan-400 font-bold">TKG</span>), wallet address (<span class="text-cyan-500 dark:text-cyan-400 font-mono">G...</span>), or transaction hash
                </div>
                <!-- If Loading -->
                <div v-else-if="loading && assets.length === 0" class="p-4 text-center text-xs text-theme-dim font-mono">
                  Searching Horizon ledger...
                </div>
                <!-- If Error/No results -->
                <div v-else-if="error" class="p-4 text-center text-xs text-rose-500 dark:text-rose-400 font-mono">
                  {{ error }}
                </div>
                <!-- Results -->
                <div v-else-if="assets.length > 0">
                  <div v-for="asset in assets" :key="`${asset.asset_code}_${asset.asset_issuer}`"
                      @click="() => { selectAsset(asset); close(); }"
                      class="p-3.5 cursor-pointer hover:bg-theme-panel2 transition duration-150 text-left">
                      <div v-if="asset.is_tx" class="flex flex-col gap-1">
                          <div class="font-sans font-bold text-xs text-cyan-500 dark:text-cyan-400 flex items-center gap-1.5">
                              <span>Explore Transaction</span>
                          </div>
                          <div class="text-[10px] font-mono text-theme-dim truncate">
                              Hash: {{ asset.tx_hash }}
                          </div>
                      </div>
                      <div v-else-if="asset.is_wallet" class="flex flex-col gap-1">
                          <div class="font-sans font-bold text-xs text-cyan-500 dark:text-cyan-400 flex items-center gap-1.5">
                              <span>Analyze Wallet Intelligence</span>
                          </div>
                          <div class="text-[10px] font-mono text-theme-dim truncate">
                              Address: {{ asset.asset_issuer }}
                          </div>
                      </div>
                      <div v-else>
                          <div class="flex items-center gap-1.5 font-bold text-xs text-theme-ink">
                              <span v-if="getAssetName(asset)" class="font-sans font-semibold text-theme-ink">
                                {{ getAssetName(asset) }} <span class="text-theme-dim font-mono font-normal text-[11px]">· {{ asset.asset_code }}</span>
                              </span>
                              <span v-else class="font-mono uppercase text-theme-ink">{{ asset.asset_code }}</span>
                              <img v-if="asset.is_verified" :src="verifiedImg" alt="Verified"
                                  class="flex-shrink-0 w-3.5 h-3.5" title="Verified Token" />
                          </div>

                          <div class="mt-1 text-[10px] font-mono break-all text-theme-dim flex flex-wrap gap-1 leading-normal">
                              <span class="text-theme-faint">Issuer:</span>
                              <span class="text-theme-dim select-all">{{ shorten(asset.asset_issuer) }}</span>
                          </div>

                          <div class="mt-1.5 text-[9.5px] font-mono text-cyan-500 dark:text-cyan-400 font-semibold flex items-center gap-1">
                              <span>●</span> Holders: {{ formatNumber(asset.accounts.authorized) }}
                          </div>
                      </div>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <!-- Connect Wallet -->
          <div class="flex items-center gap-3">
            <!-- Watchlist & Price Alerts combined dropdown with notification bell -->
            <HeaderWatchlistAlerts :wallet-key="walletPk" @open-wallet="OpenWalletModal" />

            <!-- Theme Toggle -->
            <button @click="toggleTheme" class="p-2 rounded-lg border border-theme-line bg-theme-panel hover:bg-theme-panel2 text-theme-dim hover:text-theme-ink transition-all duration-200 focus:outline-none flex items-center justify-center cursor-pointer shadow-sm hover:scale-[1.03] active:scale-[0.97]" aria-label="Toggle theme">
              <Sun v-if="theme === 'light'" class="w-4.5 h-4.5 text-amber-500" />
              <Moon v-else class="w-4.5 h-4.5 text-indigo-400" />
            </button>

            <button v-if="!isConnected" @click="OpenWalletModal" class="text-xs text-white font-bold tracking-wide px-5 py-[8px] rounded-[7px] bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 hover:scale-[1.02] active:scale-[0.98] transition-all">
              Connect Wallet
            </button>
            
            <Menu v-else as="div" class="relative inline-block text-left">
              <MenuButton class="text-xs text-theme-ink font-extrabold px-5 py-[7px] rounded-[7px] bg-theme-panel border border-theme-line hover:bg-theme-panel2 transition shadow-sm cursor-pointer">
                {{ shortMiddle(walletPk) }}
              </MenuButton>
              <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                <MenuItems class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-theme-panel border border-theme-line shadow-2xl focus:outline-none overflow-hidden">
                  <div class="px-4 py-3 border-b border-theme-line bg-theme-panel2">
                    <p class="text-[10px] text-theme-dim uppercase font-bold tracking-wider">Connected Wallet</p>
                    <p class="mt-1 text-xs font-mono text-theme-ink truncate" :title="walletPk">{{ walletPk }}</p>
                  </div>
                  <MenuItem v-slot="{ active }">
                    <router-link :to="`/wallet/${walletPk}`" :class="[active ? 'bg-theme-panel2' : '', 'block w-full px-4 py-2.5 text-left text-xs text-theme-ink font-extrabold uppercase tracking-wider transition']">
                      Wallet Intelligence
                    </router-link>
                  </MenuItem>
                  <MenuItem v-slot="{ active }">
                    <button type="button" @click="handleDisconnectWallet" :class="[active ? 'bg-rose-500/10 text-rose-500' : 'text-rose-500 dark:text-rose-400', 'block w-full px-4 py-2.5 text-left text-xs font-extrabold uppercase tracking-wider transition']">
                       Disconnect
                    </button>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>

        <!-- Mobile Toggle -->
        <div class="flex items-center lg:hidden gap-2">
          <!-- Watchlist & Price Alerts on mobile -->
          <HeaderWatchlistAlerts :wallet-key="walletPk" @open-wallet="OpenWalletModal" />

          <!-- Theme Toggle (mobile) -->
          <button @click="toggleTheme" class="p-1.5 rounded-lg border border-theme-line bg-theme-panel text-theme-dim hover:text-theme-ink focus:outline-none flex items-center justify-center cursor-pointer" aria-label="Toggle theme">
            <Sun v-if="theme === 'light'" class="w-4 h-4 text-amber-500" />
            <Moon v-else class="w-4 h-4 text-indigo-400" />
          </button>

          <!-- Connect Wallet (mini) -->
          <button v-if="!isConnected" @click="OpenWalletModal" class="text-[10px] text-white font-extrabold uppercase tracking-wider px-3 py-1.5 rounded-[6px] bg-gradient-to-r from-purple-600 to-cyan-500 focus:outline-none">
            Connect
          </button>
          <DisclosureButton class="p-2 text-slate-400 hover:text-white transition focus:outline-none">
            <Bars3Icon v-if="!open" class="block w-5 h-5" />
            <XMarkIcon v-else class="block w-5 h-5" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <DisclosurePanel class="lg:hidden bg-theme-bg border-b border-theme-line absolute top-full left-0 w-full z-50">
      <div class="px-4 py-4 space-y-3">
        <!-- Mobile Search Box Input (Inline dropdown autocomplete) -->
        <div class="relative search-container-mobile mb-3" ref="searchContainerMobile">
          <div class="bg-theme-panel2 border border-theme-line rounded-xl px-3 py-2.5 flex items-center gap-2">
            <MagnifyingGlassIcon class="w-4 h-4 text-slate-500 flex-shrink-0" />
            <input 
              v-model="searchQuery" 
              @focus="isFocusedMobile = true"
              @keydown.enter="handleEnterKey"
              type="text" 
              placeholder="Search token, wallet, or tx hash..." 
              class="bg-transparent border-0 outline-none text-sm text-theme-ink placeholder-slate-400 placeholder:font-sans font-sans font-medium w-full p-0 focus:ring-0"
            />
            <span v-if="loading && searchQuery.trim() !== ''" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-cyan-400 flex-shrink-0"></span>
          </div>

          <!-- Autocomplete dropdown mobile -->
          <div v-if="isFocusedMobile" class="hsearch-dropdown mt-1 bg-theme-panel border border-theme-line rounded-xl shadow-2xl max-h-[220px] overflow-y-auto custom-scrollbar divide-y divide-theme-line">
            <!-- If search input is empty -->
            <div v-if="searchQuery.trim() === ''" class="p-3 text-center text-xs text-theme-dim font-sans leading-relaxed">
              Search by token (e.g. <span class="text-cyan-500 dark:text-cyan-400 font-bold">TKG</span>), wallet address (<span class="text-cyan-500 dark:text-cyan-400 font-mono">G...</span>), or transaction hash
            </div>
            <!-- If Loading -->
            <div v-else-if="loading && assets.length === 0" class="p-3 text-center text-xs text-theme-dim font-mono">
              Searching Horizon ledger...
            </div>
            <!-- If Error/No results -->
            <div v-else-if="error" class="p-3 text-center text-xs text-rose-500 dark:text-rose-400 font-mono">
              {{ error }}
            </div>
            <!-- Results -->
            <div v-else-if="assets.length > 0">
              <div v-for="asset in assets" :key="`${asset.asset_code}_${asset.asset_issuer}`"
                  @click="() => { selectAsset(asset); close(); }"
                  class="p-3.5 cursor-pointer hover:bg-theme-panel2 transition duration-150 text-left">
                  <div v-if="asset.is_tx" class="flex flex-col gap-1">
                      <div class="font-sans font-bold text-xs text-cyan-500 dark:text-cyan-400 flex items-center gap-1.5">
                          <span>Explore Transaction</span>
                      </div>
                      <div class="text-[10px] font-mono text-theme-dim truncate">
                          Hash: {{ asset.tx_hash }}
                      </div>
                  </div>
                  <div v-else-if="asset.is_wallet" class="flex flex-col gap-1">
                      <div class="font-sans font-bold text-xs text-cyan-500 dark:text-cyan-400 flex items-center gap-1.5">
                          <span>Analyze Wallet Intelligence</span>
                      </div>
                      <div class="text-[10px] font-mono text-theme-dim truncate">
                          Address: {{ asset.asset_issuer }}
                      </div>
                  </div>
                  <div v-else>
                      <div class="flex items-center gap-1.5 font-bold text-xs text-theme-ink">
                          <span v-if="getAssetName(asset)" class="font-sans font-semibold text-theme-ink">
                            {{ getAssetName(asset) }} <span class="text-theme-dim font-mono font-normal text-[11px]">· {{ asset.asset_code }}</span>
                          </span>
                          <span v-else class="font-mono uppercase text-theme-ink">{{ asset.asset_code }}</span>
                          <img v-if="asset.is_verified" :src="verifiedImg" alt="Verified"
                              class="flex-shrink-0 w-3.5 h-3.5" title="Verified Token" />
                      </div>

                      <div class="mt-1 text-[10px] font-mono break-all text-theme-dim flex flex-wrap gap-1 leading-normal">
                          <span class="text-theme-faint">Issuer:</span>
                          <span class="text-theme-dim select-all">{{ shorten(asset.asset_issuer) }}</span>
                      </div>

                      <div class="mt-1.5 text-[9.5px] font-mono text-cyan-500 dark:text-cyan-400 font-semibold flex items-center gap-1">
                          <span>●</span> Holders: {{ formatNumber(asset.accounts.authorized) }}
                      </div>
                  </div>
              </div>
            </div>
          </div>
        </div>

        <router-link to="/stake" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-theme-ink hover:bg-theme-panel2 rounded-lg">Staking</router-link>
        <button @click="() => { triggerLaunchToken(); close(); }" class="block w-full text-left py-2.5 px-3 text-sm font-semibold text-theme-ink hover:bg-theme-panel2 rounded-lg focus:outline-none">Launch Token</button>
        
        <div class="pt-3 border-t border-theme-line">
          <button v-if="!isConnected" @click="() => { OpenWalletModal(); close(); }" class="w-full py-3 text-center text-sm font-bold tracking-wide text-white bg-gradient-to-r from-purple-600 to-cyan-500 rounded-xl">
            Connect Wallet
          </button>
          <div v-else class="space-y-2">
            <div class="px-3 py-2 bg-theme-panel border border-theme-line rounded-xl">
              <p class="text-[10px] text-theme-dim uppercase font-bold tracking-wider">Connected Wallet</p>
              <p class="text-xs font-mono text-theme-ink truncate">{{ walletPk }}</p>
            </div>
            <router-link :to="`/wallet/${walletPk}`" @click="close" class="block w-full py-2.5 px-3 text-center text-xs font-bold text-cyan-400 bg-slate-900 border border-slate-800 rounded-xl hover:text-white transition">
              View Wallet Intelligence
            </router-link>
            <button @click="() => { handleDisconnectWallet(); close(); }" class="w-full py-3 text-center text-sm font-extrabold uppercase tracking-wider text-red-500 dark:text-red-400 bg-red-500/10 border border-red-500/20 rounded-xl hover:bg-red-500/20 transition">
              Disconnect
            </button>
          </div>
        </div>
      </div>
    </DisclosurePanel>
  </Disclosure>

  <Modal :open="signInModal" />
  <ConnectWalletModal v-model="ConnectWalletModals" />
  <BuyTkgModal v-model="buyTkgModal" @open-wallet="OpenWalletModal" />
  <GenerateTokenModal :open="isTokenModalOpen" @close="isTokenModalOpen = false" />
</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import logo from '@/assets/token-glade-logo.png';
import verifiedImg from '@/assets/verify.png';

import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import { Sun, Moon } from 'lucide-vue-next';

const theme = ref(localStorage.getItem('theme') || 'light');

const toggleTheme = () => {
  const newTheme = theme.value === 'dark' ? 'light' : 'dark';
  theme.value = newTheme;
  localStorage.setItem('theme', newTheme);
  if (newTheme === 'light') {
    document.documentElement.classList.add('light');
    document.documentElement.classList.remove('dark');
  } else {
    document.documentElement.classList.remove('light');
    document.documentElement.classList.add('dark');
  }
  window.dispatchEvent(new CustomEvent('theme-changed', { detail: newTheme }));
};
import { useRouter, useRoute } from "vue-router";
import axios from "axios";
import Modal from '@/components/Modal.vue';
import ConnectWalletModal from './ConnectWallet.vue';
import Swal from "sweetalert2";
import { getCookie, disconnectWalletSession } from "../utils/utils.js";
import BuyTkgModal from "@/components/BuyTkgModal.vue";
import GenerateTokenModal from '@/components/GenerateTokenModal.vue';
import HeaderWatchlistAlerts from '@/components/HeaderWatchlistAlerts.vue';

const router = useRouter();
const route = useRoute();

const isTokenModalOpen = ref(false);

const triggerLaunchToken = () => {
  isTokenModalOpen.value = true;
};

const handleOpenLaunchToken = () => {
  isTokenModalOpen.value = true;
};

const signInModal = ref(false);
const ConnectWalletModals = ref(false);
const buyTkgModal = ref(false);
const walletPk = ref('');
const emit = defineEmits(['wallet-status']);

const isConnected = computed(() => !!walletPk.value);

// Auto-complete Search State
const searchQuery = ref("");
const isFocused = ref(false);
const isFocusedMobile = ref(false);
const assets = ref([]);
const loading = ref(false);
const error = ref("");
let searchRequestId = 0;
let debounceTimeout = null;

const searchContainer = ref(null);
const searchContainerMobile = ref(null);

const handleClickOutside = (e) => {
  if (searchContainer.value && !searchContainer.value.contains(e.target)) {
    isFocused.value = false;
  }
  if (searchContainerMobile.value && !searchContainerMobile.value.contains(e.target)) {
    isFocusedMobile.value = false;
  }
};

function formatNumber(value) {
  return new Intl.NumberFormat("en-US", {
    maximumFractionDigits: 0
  }).format(value || 0);
}

function shorten(str) {
  if (!str) return "-";
  return str.slice(0, 5) + "..." + str.slice(-4);
}

async function enrichVerificationStatus(assetList) {
  const issuers = assetList.map((asset) => asset.asset_issuer);
  if (!issuers.length) return;

  try {
    const { data } = await axios.post("/api/token/check-verification", {
      issuers,
    });
    for (const asset of assetList) {
      asset.is_verified = data.verified?.[asset.asset_issuer] === true;
      if (data.names?.[asset.asset_issuer]) {
        asset.name = data.names[asset.asset_issuer];
      }
    }
  } catch {
    for (const asset of assetList) {
      asset.is_verified = false;
    }
  }
}

function getAssetName(asset) {
  if (asset.name) return asset.name;
  if (asset.asset_name) return asset.asset_name;
  if (asset.toml_info?.name) return asset.toml_info.name;
  if (asset.toml_info?.orgName) return asset.toml_info.orgName;
  if (asset.org_name) return asset.org_name;
  return null;
}

async function fetchMissingAssetNames(assetList) {
  const missing = assetList.filter((a) => !a.name);
  if (!missing.length) return;

  await Promise.all(
    missing.slice(0, 15).map(async (asset) => {
      try {
        const res = await fetch(
          `https://api.stellar.expert/explorer/public/asset/${asset.asset_code}-${asset.asset_issuer}`
        );
        const data = await res.json();
        if (data.toml_info?.name) {
          asset.name = data.toml_info.name;
        } else if (data.toml_info?.orgName) {
          asset.name = data.toml_info.orgName;
        }
      } catch {}
    })
  );
}

async function searchAssets() {
  error.value = "";
  const rawInput = searchQuery.value.trim();
  if (!rawInput) {
    assets.value = [];
    return;
  }

  // Detect Stellar Transaction Hash Search (64 hex characters)
  if (/^[a-f0-9]{64}$/i.test(rawInput)) {
    assets.value = [{
      is_tx: true,
      tx_hash: rawInput.toLowerCase()
    }];
    loading.value = false;
    return;
  }

  // Detect Stellar Wallet Address Search
  if (/^G[A-Z2-7]{55}$/.test(rawInput)) {
    assets.value = [{
      asset_code: 'Analyze Wallet',
      asset_issuer: rawInput,
      is_wallet: true,
      accounts: { authorized: 0 }
    }];
    loading.value = false;
    return;
  }

  const requestId = ++searchRequestId;
  loading.value = true;

  try {
    const { data } = await axios.get(`/api/token/search?q=${encodeURIComponent(rawInput)}`);

    if (requestId !== searchRequestId) return;

    if (!data?.tokens?.length) {
      assets.value = [];
      error.value = "No token found";
      return;
    }

    // Sort by verified status first, then by number of active holders for unverified tokens
    const sortedAssets = data.tokens.sort((a, b) => {
      // 1. Verified tokens top
      if (a.is_verified !== b.is_verified) {
        return (b.is_verified ? 1 : 0) - (a.is_verified ? 1 : 0);
      }
      // 2. Sort according to active holders (descending)
      const holdersA = a.accounts?.authorized || 0;
      const holdersB = b.accounts?.authorized || 0;
      return holdersB - holdersA;
    });

    assets.value = sortedAssets;

    await enrichVerificationStatus(sortedAssets);
    await fetchMissingAssetNames(sortedAssets);

    error.value = "";
  } catch (e) {
    if (requestId !== searchRequestId) return;
    error.value = "Search connection error";
    assets.value = [];
  } finally {
    if (requestId === searchRequestId) {
      loading.value = false;
    }
  }
}

watch(searchQuery, (newValue) => {
  clearTimeout(debounceTimeout);
  if (!newValue.trim()) {
    assets.value = [];
    error.value = "";
    return;
  }
  debounceTimeout = setTimeout(() => {
    searchAssets();
  }, 200);
});

function selectAsset(asset) {
  document.activeElement?.blur();
  if (asset.is_tx) {
    router.push(`/tx/${asset.tx_hash}`);
  } else if (asset.is_wallet) {
    router.push(`/wallet/${asset.asset_issuer}`);
  } else {
    router.push({
      path: "/token-insight",
      query: {
        asset_code: asset.asset_code,
        issuer: asset.asset_issuer
      }
    });
  }
  searchQuery.value = "";
  assets.value = [];
  isFocused.value = false;
  isFocusedMobile.value = false;
}

function handleEnterKey() {
  const rawInput = searchQuery.value.trim();
  if (/^[a-f0-9]{64}$/i.test(rawInput)) {
    selectAsset({
      is_tx: true,
      tx_hash: rawInput.toLowerCase()
    });
  } else if (/^G[A-Z2-7]{55}$/.test(rawInput)) {
    selectAsset({
      asset_code: 'Analyze Wallet',
      asset_issuer: rawInput,
      is_wallet: true,
      accounts: { authorized: 0 }
    });
  } else if (assets.value.length > 0) {
    selectAsset(assets.value[0]);
  }
}

const handleKeyDown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    isFocused.value = true;
    const input = searchContainer.value?.querySelector('input');
    if (input) input.focus();
  }
};

function refreshWalletPk() {
  walletPk.value =
    getCookie('public_key') ||
    localStorage.getItem('public_key') ||
    '';
}

const openBuyTkgModal = () => {
  buyTkgModal.value = true;
};

const handleWalletChanged = (event) => {
  refreshWalletPk();
  emit('wallet-status', { connected: isConnected.value });
};

onMounted(() => {
  if (theme.value === 'light') {
    document.documentElement.classList.add('light');
  } else {
    document.documentElement.classList.remove('light');
  }
  window.addEventListener("tokenglade-open-buy-tkg", openBuyTkgModal);
  window.addEventListener("tokenglade-open-launch-token", handleOpenLaunchToken);
  window.addEventListener("tokenglade-wallet-changed", handleWalletChanged);
  window.addEventListener("keydown", handleKeyDown);
  window.addEventListener("click", handleClickOutside);
  refreshWalletPk();
  if (route.query && route.query.launch === 'true') {
    isTokenModalOpen.value = true;
  }
});

async function handleDisconnectWallet() {
  try {
    await disconnectWalletSession();
    walletPk.value = '';
    if (typeof speak === 'function') speak('connected', false);
    emit('wallet-status', { connected: false });
    window.dispatchEvent(new CustomEvent("tokenglade-wallet-changed", {
      detail: { connected: false, publicKey: "" }
    }));
  } catch (error) {
    console.error("Error disconnecting wallet:", error);
    Swal.fire({
      icon: "error",
      title: "Error!",
      text: error.message || "An error occurred while disconnecting the wallet.",
    });
  }
}

function shortMiddle(str, head = 4, tail = 4) {
  if (!str) return '—'
  return str.length > head + tail ? `${str.slice(0, head)}…${str.slice(-tail)}` : str
}

onUnmounted(() => {
  window.removeEventListener("tokenglade-open-buy-tkg", openBuyTkgModal);
  window.removeEventListener("tokenglade-open-launch-token", handleOpenLaunchToken);
  window.removeEventListener("tokenglade-wallet-changed", handleWalletChanged);
  window.removeEventListener("keydown", handleKeyDown);
  window.removeEventListener("click", handleClickOutside);
});

const OpenWalletModal = () => {
  if (isConnected.value) return;
  ConnectWalletModals.value = true;
};
</script>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
  display: none;
}
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.hsearch {
  --panel: #111620;
  --line: #1D2531;
  --faint: #586172;
  --sans: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--panel);
  border: 1px solid var(--line);
  border-radius: 9px;
  padding: 7px 12px;
  min-width: 290px;
  color: var(--faint);
  font-family: var(--sans);
  font-size: 13px;
  transition: all 0.2s ease;
}

.hsearch input {
  font-family: var(--sans);
}

.hsearch input::placeholder,
.search-container-mobile input::placeholder {
  font-family: var(--sans);
  font-size: 12.5px;
  font-weight: 400;
  color: #64748b;
  letter-spacing: normal;
}

.hsearch:hover {
  border-color: #38bdf8;
  color: #fff;
}

@media(max-width:820px){
  nav.main,.hsearch{display:none}
  .stats{grid-template-columns:repeat(2,1fr)}
  .expo-stats{grid-template-columns:1fr 1fr}
  .health{grid-template-columns:1fr}
  .trust{margin-left:0}
}

/* SIGNATURE SCROLLING TICKER TAPE */
.tape {
  border-bottom: 1px solid var(--line);
  background: var(--panel2);
  overflow: hidden;
  white-space: nowrap;
  height: 34px;
  display: flex;
  align-items: center;
  width: 100%;
  
  /* Mockup theme variables inherited globally */
  --mono: "JetBrains Mono", ui-monospace, monospace;
}
.tape-track {
  display: inline-flex;
  gap: 34px;
  padding-left: 34px;
  animation: scroll 42s linear infinite;
  will-change: transform;
}
.tape:hover .tape-track {
  animation-play-state: paused;
}
.tape .t {
  font-family: var(--mono);
  font-size: 11.5px;
  letter-spacing: 0.02em;
  color: var(--faint);
  display: inline-flex;
  align-items: center;
}
.tape .t b {
  color: var(--ink);
  font-weight: 600;
  margin-right: 8px;
}
.tape .up {
  color: var(--up);
  margin-left: 4px;
}
.tape .down {
  color: var(--down);
  margin-left: 4px;
}
.tape .cyan {
  color: var(--cyan);
  margin-left: 4px;
}
.tape .dim {
  color: var(--dim);
  margin-left: 4px;
}
.tape .green {
  color: var(--up);
}

@keyframes scroll {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-33.33%);
  }
}

/* Light Theme overrides for Search Box */
html.light .hsearch {
  --panel: #f1f5f9;
  --line: #cbd5e1;
  --faint: #64748b;
}

html.light .hsearch input {
  color: #0f172a !important;
}

html.light .hsearch input::placeholder {
  color: #94a3b8 !important;
}

html.light .hsearch:hover {
  border-color: #0284c7 !important;
}

/* Light Theme overrides for Autocomplete Dropdown */
html.light .hsearch-dropdown {
  background: #ffffff !important;
  border-color: #e2e8f0 !important;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
  color: #0f172a !important;
}

html.light .hsearch-dropdown .text-white,
html.light .hsearch-dropdown .text-theme-ink {
  color: #0f172a !important;
}

html.light .hsearch-dropdown .text-slate-400,
html.light .hsearch-dropdown .text-theme-dim {
  color: #475569 !important;
}

html.light .hsearch-dropdown .text-slate-500,
html.light .hsearch-dropdown .text-theme-faint {
  color: #94a3b8 !important;
}

html.light .hsearch-dropdown .hover\:bg-theme-panel2:hover,
html.light .hsearch-dropdown .hover\:bg-\[\#182235\]\/70:hover {
  background-color: #f8fafc !important;
}

html.light .hsearch-dropdown .text-cyan-400,
html.light .hsearch-dropdown .text-cyan-500 {
  color: #0891b2 !important;
}

html.light .hsearch-dropdown .text-rose-400,
html.light .hsearch-dropdown .text-rose-500 {
  color: #e11d48 !important;
}
</style>
