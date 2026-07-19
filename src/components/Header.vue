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
            <a href="/#explore" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors">Markets</a>
            <a href="/#pools" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors">Pools</a>
            <a href="/#wallet-explorer" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors">Wallets</a>
            <a href="/#featured-projects" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors">Projects</a>
            <a href="/#latest-tokens" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors">Launches</a>
            <a href="/#portfolio" class="text-xs font-black uppercase tracking-wider text-slate-400 hover:text-white transition-colors">Portfolio</a>
          </div>
        </div>
        
        <!-- Right: Actions -->
        <div class="hidden lg:flex items-center gap-4">
          <!-- Search Button -->
          <button @click="showSearchModal = true" class="hsearch focus:outline-none">
            <MagnifyingGlassIcon class="w-3.5 h-3.5 text-slate-500" />
            <span>Search...</span>
          </button>
          
          <router-link to="/stake" class="text-xs font-black uppercase tracking-wider text-slate-300 hover:text-white hover:bg-slate-900 px-4 py-2 rounded-xl transition-colors border border-slate-800 bg-slate-950/40">
            Stake
          </router-link>
          
          <!-- Connect Wallet -->
          <div class="flex items-center">
            <button v-if="!isConnected" @click="OpenWalletModal" class="text-xs text-white font-extrabold uppercase tracking-wider px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 hover:scale-[1.02] active:scale-[0.98] transition-all">
              Connect Wallet
            </button>
            
            <Menu v-else as="div" class="relative inline-block text-left">
              <MenuButton class="text-xs text-white font-extrabold px-5 py-2.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition">
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
          <button type="button" @click="showSearchModal = true" class="p-2 text-slate-400 hover:text-white transition">
            <MagnifyingGlassIcon class="w-5 h-5" />
          </button>
          
          <DisclosureButton class="p-2 text-slate-400 hover:text-white transition">
            <Bars3Icon v-if="!open" class="block w-5 h-5" />
            <XMarkIcon v-else class="block w-5 h-5" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <!-- Mobile Navigation Drawer -->
    <DisclosurePanel class="lg:hidden bg-[#070A13] border-b border-slate-900 absolute top-full left-0 w-full z-50">
      <div class="px-4 py-4 space-y-3">
        <a href="/#explore" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Markets</a>
        <a href="/#pools" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Pools</a>
        <a href="/#wallet-explorer" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Wallets</a>
        <a href="/#featured-projects" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Projects</a>
        <a href="/#latest-tokens" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Launches</a>
        <a href="/#portfolio" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Portfolio</a>
        <router-link to="/stake" @click="close" class="block py-2.5 px-3 text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 rounded-lg">Stake</router-link>
        
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
  <TokenSearchModal v-model="showSearchModal" />
</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import logo from '@/assets/token-glade-logo.png';

import { ref, onMounted, onUnmounted, computed } from "vue";
import Modal from '@/components/Modal.vue';
import ConnectWalletModal from './ConnectWallet.vue';
import Swal from "sweetalert2";
import { getCookie, disconnectWalletSession } from "../utils/utils.js";
import BuyTkgModal from "@/components/BuyTkgModal.vue"
import TokenSearchModal from "./TokenSearchModal.vue"

const signInModal = ref(false);
const ConnectWalletModals = ref(false);
const buyTkgModal = ref(false);
const showSearchModal = ref(false);
const walletPk = ref('')
const emit = defineEmits(['wallet-status']);

const isConnected = computed(() => !!walletPk.value)



const handleKeyDown = (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    showSearchModal.value = !showSearchModal.value;
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
