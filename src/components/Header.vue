<template>
  <Disclosure as="nav"
    class="fixed top-[1rem] left-0 w-full z-[50] transition-transform duration-700 ease-[cubic-bezier(0.68,-0.55,0.27,1.55)] origin-top"
    :class="{
      '-translate-y-[120%] scale-y-90': hideHeader,
      'translate-y-0 scale-y-100': !hideHeader
    }" v-slot="{ open, close }">


    <div class="max-w-6xl px-4 mx-auto sm:px-6 lg:px-8 rounded-[10rem] shadow-lg bg-white">
      <div class="flex justify-between h-20">
        <div class="flex">
          <div class="flex items-center flex-shrink-0">
            <router-link to="/">
              <img class="w-auto h-8 mt-2" :src="logo" alt="TokenGlade Logo" />
            </router-link>
          </div>
          <div class="hidden sm:ml-6 lg:flex sm:space-x-6">
            <template v-for="link in Links" :key="link.name">
              <router-link v-if="link.to" :to="link.to"
                class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">
                {{ link.name }}
              </router-link>

              <button v-else-if="link.openBuy" type="button" @click="openBuyTkgModal"
                class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">
                {{ link.name }}
              </button>
            </template>
          </div>

        </div>
        <div class="hidden sm:ml-6 lg:flex sm:items-center gap-3">
          <!-- Search Token Button -->
          <button @click="showSearchModal = true" class="flex items-center gap-2 px-3.5 py-2 text-t14 text-gray-500 hover:text-gray-900 border border-gray-200 rounded-full bg-gray-50 hover:bg-gray-100 transition duration-150 focus:outline-none">
            <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
            <span>Search tokens</span>
          </button>

          <!-- Connect Wallet Button -->
          <div class="flex items-center gap-2 ">
            <button v-if="!isConnected" @click="OpenWalletModal" class="text-xs text-white rounded-full btn-padding sm:text-t14
           bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
           bg-[length:200%_200%] bg-no-repeat animate-gradientMove">
              Connect Wallet
            </button>

            <!-- When connected: show short address + dropdown menu -->
            <Menu v-else as="div" class="relative inline-block text-left">
              <MenuButton class="text-xs text-white rounded-full btn-padding sm:text-t14
           bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
           bg-[length:200%_200%] bg-no-repeat animate-gradientMove">
                {{ shortMiddle(walletPk) }}
              </MenuButton>

              <transition enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0">
                <MenuItems
                  class="absolute right-0 z-50 mt-2 w-56 origin-top-right rounded-xl bg-white shadow-lg ring-1 ring-black/5 focus:outline-none">
                  <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-xs text-gray-500">Connected wallet</p>
                    <p class="mt-1 text-sm font-medium text-gray-900 truncate">{{ walletPk }}</p>
                  </div>
                  <MenuItem v-slot="{ active }">
                    <button type="button" @click="handleDisconnectWallet"
                      :class="[active ? 'bg-red-50' : '', 'block w-full px-4 py-2.5 text-left text-sm text-red-600']">
                      Disconnect wallet
                    </button>
                  </MenuItem>
                </MenuItems>
              </transition>
            </Menu>
          </div>
        </div>
        <div class="flex items-center -mr-2 lg:hidden gap-1">
          <!-- Mobile Search Button -->
          <button type="button" @click="showSearchModal = true"
            class="inline-flex items-center justify-center p-2 text-gray-400 rounded-md hover:bg-gray-100 hover:text-gray-500 focus:outline-none">
            <span class="sr-only">Search tokens</span>
            <MagnifyingGlassIcon class="w-6 h-6" aria-hidden="true" />
          </button>

          <!-- Mobile menu button -->
          <DisclosureButton
            class="inline-flex items-center justify-center p-2 text-gray-400 rounded-md hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
            <span class="sr-only">Open main menu</span>
            <Bars3Icon v-if="!signInModal" class="block w-6 h-6" aria-hidden="true" />
            <XMarkIcon v-else class="block w-6 h-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <DisclosurePanel class="lg:hidden fixed inset-x-0 top-[calc(1rem+5rem)] z-50 w-screen">
      <div class="bg-white shadow-xl">
        <div class="max-w-6xl mx-auto px-4 py-3">
          <!-- Search Option -->
          <button type="button" @click="() => { showSearchModal = true; close(); }"
            class="flex items-center gap-2 w-full py-3 px-3 text-base font-medium text-gray-800 hover:bg-gray-50 rounded-lg text-left">
            <MagnifyingGlassIcon class="w-5 h-5 text-gray-400" aria-hidden="true" />
            <span>Search tokens</span>
          </button>

          <!-- render both internal + external links -->
          <template v-for="link in Links" :key="link.name">
            <router-link v-if="link.to" :to="link.to" @click="close"
              class="block py-3 px-3 text-base font-medium text-gray-800 hover:bg-gray-50 rounded-lg">
              {{ link.name }}
            </router-link>
            <button v-else-if="link.openBuy" type="button" @click="() => { openBuyTkgModal(); close(); }"
              class="block w-full py-3 px-3 text-base font-medium text-gray-800 hover:bg-gray-50 rounded-lg text-left">
              {{ link.name }}
            </button>
          </template>



          <button v-if="!isConnected" id="walletConnected" @click="() => { OpenWalletModal(); close(); }" type="button" class="w-full py-3 mt-2 text-base font-medium text-white rounded-lg
             bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]">
            Connect Wallet
          </button>

          <div v-else class="mt-2 rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-3 py-3 bg-gray-50">
              <p class="text-xs text-gray-500">Connected wallet</p>
              <p class="mt-1 text-sm font-medium text-gray-900 truncate">{{ shortMiddle(walletPk, 6, 6) }}</p>
            </div>
            <button type="button" @click="handleDisconnectWallet"
              class="w-full py-3 px-3 text-base font-medium text-red-600 hover:bg-red-50 text-left">
              Disconnect wallet
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
import { Bars3Icon, BellIcon, XMarkIcon, MagnifyingGlassIcon } from '@heroicons/vue/24/outline';
import logo from '@/assets/header-logo.png';

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

const hideHeader = ref(false);
let lastScrollY = 0;

const handleScroll = () => {
  if (window.scrollY > lastScrollY && window.scrollY > 20) {
    hideHeader.value = true;
  } else {
    hideHeader.value = false;
  }
  lastScrollY = window.scrollY;
};

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

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
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
  window.removeEventListener("scroll", handleScroll);
  window.removeEventListener("tokenglade-open-buy-tkg", openBuyTkgModal);
  window.removeEventListener("keydown", handleKeyDown);
});

const OpenWalletModal = () => {
  if (isConnected.value) return;
  ConnectWalletModals.value = true;
};
const openBuyTkgModal = () => { buyTkgModal.value = true; };


// const Links = [
//   {
//     name: 'Home',
//     to: '/'
//   },
//   {
//     name: 'Explore',
//     to: '/#explore'
//   },
//   {
//     name: 'Tokens',
//     to: '/#tokens'
//   },
//   {
//     name: 'Pools',
//     to: '/#pools'
//   },
//   {
//     name: 'Rewards',
//     to: '/#lp-rewards'
//   },
//   {
//     name: 'Portfolio',
//     to: '/#portfolio'
//   }
// ]

const Links = [
  {
    name: 'Stake',
    to: '/stake'
  },
  {
    name: 'Buy TKG Tokens',
    openBuy: true,
  }
]

</script>
