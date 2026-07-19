<template>
  <!-- FIXED/STICKY NAVIGATION HEADER (pins to top-0 when ticker scrolls away) -->
  <Disclosure as="nav"
    class="sticky top-0 z-[50] w-full border-b border-slate-900/60 bg-[#070A13]/90 backdrop-blur-md"
    v-slot="{ open, close }">

    <!-- MAIN NAVIGATION -->
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between h-16 items-center">
        
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
                type="text" 
                placeholder="Search symbol..." 
                class="bg-transparent border-0 outline-none text-xs text-white placeholder-slate-500 font-mono w-[180px] p-0 focus:ring-0"
              />
              <span v-if="loading && searchQuery.trim() !== ''" class="animate-spin rounded-full h-3 w-3 border-b-2 border-cyan-400 flex-shrink-0"></span>
            </div>

            <!-- Autocomplete dropdown -->
            <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
              <div v-if="isFocused" class="absolute right-0 mt-2 w-[340px] bg-[#111827] border border-[rgba(148,163,184,0.16)] rounded-xl shadow-2xl z-[99] max-h-[300px] overflow-y-auto custom-scrollbar divide-y divide-[rgba(148,163,184,0.12)]">
                <!-- If search input is empty -->
                <div v-if="searchQuery.trim() === ''" class="p-4 text-center text-xs text-slate-400 font-mono">
                  Type a token symbol (e.g. TKG, XLM) to search
                </div>
                <!-- If Loading -->
                <div v-else-if="loading && assets.length === 0" class="p-4 text-center text-xs text-slate-400 font-mono">
                  Searching Horizon ledger...
                </div>
                <!-- If Error/No results -->
                <div v-else-if="error" class="p-4 text-center text-xs text-rose-400 font-mono">
                  {{ error }}
                </div>
                <!-- Results -->
                <div v-else-if="assets.length > 0">
                  <div v-for="asset in assets" :key="`${asset.asset_code}_${asset.asset_issuer}`"
                      @click="selectAsset(asset)"
                      class="p-3.5 cursor-pointer hover:bg-[#182235]/70 transition duration-150 text-left">
                      <div class="flex items-center gap-1.5 font-bold text-xs text-white">
                          <span class="font-mono uppercase">{{ asset.asset_code }}</span>
                          <img v-if="asset.is_verified" :src="verifiedImg" alt="Verified"
                              class="flex-shrink-0 w-3.5 h-3.5" title="Verified Token" />
                      </div>

                      <div class="mt-1 text-[10px] font-mono break-all text-slate-400 flex flex-wrap gap-1 leading-normal">
                          <span class="text-slate-500">Issuer:</span>
                          <span class="text-slate-400 select-all">{{ shorten(asset.asset_issuer) }}</span>
                      </div>

                      <div class="mt-1.5 text-[9.5px] font-mono text-cyan-400 font-semibold flex items-center gap-1">
                          <span>●</span> Holders: {{ formatNumber(asset.accounts.authorized) }}
                      </div>
                  </div>
                </div>
              </div>
            </transition>
          </div>

          <!-- Connect Wallet -->
          <div class="flex items-center">
            <button v-if="!isConnected" @click="OpenWalletModal" class="text-xs text-white font-extrabold uppercase tracking-wider px-5 py-[8px] rounded-[7px] bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 hover:scale-[1.02] active:scale-[0.98] transition-all">
              Connect Wallet
            </button>
            
            <Menu v-else as="div" class="relative inline-block text-left">
              <MenuButton class="text-xs text-white font-extrabold px-5 py-[7px] rounded-[7px] bg-slate-900 border border-slate-800 hover:border-slate-700 transition">
                {{ shortMiddle(walletPk) }}
              </MenuButton>
              <transition enter-active-class="transition duration-100 ease-out" enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100" leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100" leave-to-class="transform scale-95 opacity-0">
                <MenuItems class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-slate-950 border border-slate-850 shadow-2xl focus:outline-none">
                  <div class="px-4 py-3 border-b border-slate-900">
                    <p class="text-[10px] text-slate-500">Connected Wallet</p>
                    <p class="mt-1 text-xs font-mono text-white truncate" :title="walletPk">{{ walletPk }}</p>
                  </div>
                  <MenuItem v-slot="{ active }">
                    <button type="button" @click="handleDisconnectWallet" :class="[active ? 'bg-red-500/10' : '', 'block w-full px-4 py-2.5 text-left text-xs text-red-400 font-extrabold uppercase tracking-wider']">
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
    <DisclosurePanel class="lg:hidden bg-[#070A13] border-b border-slate-900 absolute top-full left-0 w-full z-50">
      <div class="px-4 py-4 space-y-3">
        <!-- Mobile Search Box Input (Inline dropdown autocomplete) -->
        <div class="relative search-container-mobile mb-3" ref="searchContainerMobile">
          <div class="bg-[#182235] border border-[rgba(148,163,184,0.16)] rounded-xl px-3 py-2.5 flex items-center gap-2">
            <MagnifyingGlassIcon class="w-4 h-4 text-slate-500 flex-shrink-0" />
            <input 
              v-model="searchQuery" 
              @focus="isFocusedMobile = true"
              type="text" 
              placeholder="Search symbol..." 
              class="bg-transparent border-0 outline-none text-xs text-white placeholder-slate-500 font-mono w-full p-0 focus:ring-0"
            />
            <span v-if="loading && searchQuery.trim() !== ''" class="animate-spin rounded-full h-3.5 w-3.5 border-b-2 border-cyan-400 flex-shrink-0"></span>
          </div>

          <!-- Autocomplete dropdown mobile -->
          <div v-if="isFocusedMobile" class="mt-1 bg-[#111827] border border-[rgba(148,163,184,0.16)] rounded-xl shadow-2xl max-h-[220px] overflow-y-auto custom-scrollbar divide-y divide-[rgba(148,163,184,0.12)]">
            <!-- If search input is empty -->
            <div v-if="searchQuery.trim() === ''" class="p-3 text-center text-xs text-slate-400 font-mono">
              Type a token symbol (e.g. TKG, XLM) to search
            </div>
            <!-- If Loading -->
            <div v-else-if="loading && assets.length === 0" class="p-3 text-center text-xs text-slate-400 font-mono">
              Searching Horizon ledger...
            </div>
            <!-- If Error/No results -->
            <div v-else-if="error" class="p-3 text-center text-xs text-rose-400 font-mono">
              {{ error }}
            </div>
            <!-- Results -->
            <div v-else-if="assets.length > 0">
              <div v-for="asset in assets" :key="`${asset.asset_code}_${asset.asset_issuer}`"
                  @click="() => { selectAsset(asset); close(); }"
                  class="p-3.5 cursor-pointer hover:bg-[#182235]/70 transition duration-150 text-left">
                  <div class="flex items-center gap-1.5 font-bold text-xs text-white">
                      <span class="font-mono uppercase">{{ asset.asset_code }}</span>
                      <img v-if="asset.is_verified" :src="verifiedImg" alt="Verified"
                          class="flex-shrink-0 w-3.5 h-3.5" title="Verified Token" />
                  </div>

                  <div class="mt-1 text-[10px] font-mono break-all text-slate-400 flex flex-wrap gap-1 leading-normal">
                      <span class="text-slate-500">Issuer:</span>
                      <span class="text-slate-400 select-all">{{ shorten(asset.asset_issuer) }}</span>
                  </div>

                  <div class="mt-1.5 text-[9.5px] font-mono text-cyan-400 font-semibold flex items-center gap-1">
                      <span>●</span> Holders: {{ formatNumber(asset.accounts.authorized) }}
                  </div>
              </div>
            </div>
          </div>
        </div>

        <router-link to="/stake" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Staking</router-link>
        <button @click="() => { triggerLaunchToken(); close(); }" class="block w-full text-left py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg focus:outline-none">Launch Token</button>
        
        <div class="pt-3 border-t border-slate-900">
          <button v-if="!isConnected" @click="() => { OpenWalletModal(); close(); }" class="w-full py-3 text-center text-sm font-extrabold uppercase tracking-wider text-white bg-gradient-to-r from-purple-600 to-cyan-500 rounded-xl">
            Connect Wallet
          </button>
          <div v-else class="space-y-2">
            <div class="px-3 py-2 bg-slate-950 border border-slate-900 rounded-xl">
              <p class="text-[10px] text-slate-500">Connected Wallet</p>
              <p class="text-xs font-mono text-white truncate">{{ walletPk }}</p>
            </div>
            <button @click="() => { handleDisconnectWallet(); close(); }" class="w-full py-3 text-center text-sm font-extrabold uppercase tracking-wider text-red-400 bg-red-950/20 border border-red-900/30 rounded-xl">
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
</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import logo from '@/assets/token-glade-logo.png';
import verifiedImg from '@/assets/verify.png';

import { ref, onMounted, onUnmounted, computed, watch } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";
import Modal from '@/components/Modal.vue';
import ConnectWalletModal from './ConnectWallet.vue';
import Swal from "sweetalert2";
import { getCookie, disconnectWalletSession } from "../utils/utils.js";
import BuyTkgModal from "@/components/BuyTkgModal.vue"

const router = useRouter();

const triggerLaunchToken = () => {
  if (router.currentRoute.value.path !== '/') {
    router.push({ path: '/', query: { launch: 'true' } });
  } else {
    window.dispatchEvent(new CustomEvent("tokenglade-open-launch-token"));
  }
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
    }
  } catch {
    for (const asset of assetList) {
      asset.is_verified = false;
    }
  }
}

async function searchAssets() {
  error.value = "";
  const rawInput = searchQuery.value.trim();
  if (!rawInput) {
    assets.value = [];
    return;
  }

  const requestId = ++searchRequestId;
  loading.value = true;

  try {
    const queries = [rawInput];
    if (rawInput !== rawInput.toUpperCase()) {
      queries.push(rawInput.toUpperCase());
    }

    let allRecords = [];
    for (const code of queries) {
      const res = await fetch(
        `https://horizon.stellar.org/assets?asset_code=${encodeURIComponent(code)}&limit=10`
      );
      const data = await res.json();
      if (data._embedded?.records?.length) {
        allRecords = [...allRecords, ...data._embedded.records];
      }
    }

    if (requestId !== searchRequestId) return;

    if (!allRecords.length) {
      assets.value = [];
      error.value = "No token found";
      return;
    }

    const uniqueAssets = Object.values(
      allRecords.reduce((acc, asset) => {
        const key = `${asset.asset_code}_${asset.asset_issuer}`;
        acc[key] = asset;
        return acc
      }, {})
    );

    const sortedAssets = uniqueAssets.sort((a, b) => {
      if (b.num_liquidity_pools !== a.num_liquidity_pools) {
        return b.num_liquidity_pools - a.num_liquidity_pools;
      }
      return b.accounts.authorized - a.accounts.authorized;
    });

    await enrichVerificationStatus(sortedAssets);

    if (requestId !== searchRequestId) return;

    assets.value = sortedAssets;
    error.value = "";
  } catch (e) {
    if (requestId !== searchRequestId) return;
    error.value = "Horizon connection error";
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
  }, 500);
});

function selectAsset(asset) {
  router.push({
    path: "/token-insight",
    query: {
      asset_code: asset.asset_code,
      issuer: asset.asset_issuer
    }
  });
  searchQuery.value = "";
  assets.value = [];
  isFocused.value = false;
  isFocusedMobile.value = false;
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

onMounted(() => {
  window.addEventListener("tokenglade-open-buy-tkg", openBuyTkgModal);
  window.addEventListener("keydown", handleKeyDown);
  window.addEventListener("click", handleClickOutside);
  refreshWalletPk();
});

async function handleDisconnectWallet() {
  try {
    await disconnectWalletSession();
    walletPk.value = '';
    emit('wallet-status', { connected: false });
    window.location.reload();
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
  --mono: "JetBrains Mono", ui-monospace, monospace;
  
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--panel);
  border: 1px solid var(--line);
  border-radius: 7px;
  padding: 7px 11px;
  min-width: 220px;
  color: var(--faint);
  font-family: var(--mono);
  font-size: 12.5px;
  transition: all 0.2s ease;
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
  
  /* Mockup theme variables */
  --panel2: #0E131C;
  --line: #1D2531;
  --ink: #D5DBE5;
  --faint: #586172;
  --up: #2ED47A;
  --down: #F0616D;
  --cyan: #12CBEE;
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
</style>
