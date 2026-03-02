<template>
  <Disclosure as="nav"
    class="fixed top-[1rem] left-0 w-full z-[9999] transition-transform duration-700 ease-[cubic-bezier(0.68,-0.55,0.27,1.55)] origin-top"
    :class="{
      '-translate-y-[120%] scale-y-90': hideHeader,
      'translate-y-0 scale-y-100': !hideHeader
    }" v-slot="{ open }">


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

              <!-- TOKEN INSIGHT DROPDOWN -->
              <div v-if="link.name === 'Token Insight'" ref="insightDropdown"
                class="relative inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">

                <button @click="showInsightDropdown = !showInsightDropdown">
                  {{ link.name }}
                </button>

                <Transition enter-active-class="transition duration-200 ease-out"
                  enter-from-class="opacity-0 scale-95 translate-y-2"
                  enter-to-class="opacity-100 scale-100 translate-y-0"
                  leave-active-class="transition duration-300 ease-in"
                  leave-from-class="opacity-100 scale-100 translate-y-0"
                  leave-to-class="opacity-0 scale-95 translate-y-2">
                  <div v-if="showInsightDropdown"
                    class="absolute top-full mt-3 w-[600px] max-w-[95vw] bg-white border rounded-xl shadow-lg p-4 z-50">
                    <p class="text-sm text-slate-600 mb-2">
                      Enter issuer address
                    </p>

                    <input v-model="issuerInput" type="text" placeholder="G...."
                      class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-cyan-400" />

                    <button @click.stop="goToInsight"
                      class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">
                      Open Insight
                    </button>
                  </div>
                </Transition>
              </div>

              <router-link v-else-if="link.to" :to="link.to"
                class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">
                {{ link.name }}
              </router-link>

              <a v-else :href="link.href" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">
                {{ link.name }}
              </a>
            </template>
          </div>

        </div>
        <div class="hidden sm:ml-6 lg:flex sm:items-center">

          <!-- Connect Wallet Button -->
          <div class="flex items-center gap-2 ">

            <button v-if="!isConnected" @click="OpenWalletModal" class="text-xs text-white rounded-full btn-padding sm:text-t14
           bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
           bg-[length:200%_200%] bg-no-repeat animate-gradientMove">
              Connect Wallet
            </button>

            <!-- When connected: show short address + menu -->
            <button v-else @click="ConnectWalletModals = !ConnectWalletModals" class="text-xs text-white rounded-full btn-padding sm:text-t14
           bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
           bg-[length:200%_200%] bg-no-repeat animate-gradientMove" :aria-expanded="ConnectWalletModals">
              {{ shortMiddle(walletPk) }}
            </button>
          </div>
        </div>
        <div class="flex items-center -mr-2 lg:hidden">
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
          <!-- render both internal + external links -->
          <template v-for="link in Links" :key="link.name">
            <router-link v-if="link.to" :to="link.to"
              class="block py-3 px-3 text-base font-medium text-gray-800 hover:bg-gray-50 rounded-lg">
              {{ link.name }}
            </router-link>
            <a v-else :href="link.href" target="_blank" rel="noopener noreferrer"
              class="block py-3 px-3 text-base font-medium text-gray-800 hover:bg-gray-50 rounded-lg">
              {{ link.name }}
            </a>
          </template>

          <button id="walletConnected" @click="OpenWalletModal" type="button" class="w-full py-3 mt-2 text-base font-medium text-white rounded-lg
               bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]">
            Connect Wallet
          </button>
        </div>
      </div>
    </DisclosurePanel>
  </Disclosure>

  <Modal :open="signInModal" />
  <ConnectWalletModal v-model="ConnectWalletModals" />

</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, BellIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import logo from '@/assets/header-logo.png';

import { ref, onMounted, onUnmounted, computed } from "vue";
import Modal from '@/components/Modal.vue';
import ConnectWalletModal from './ConnectWallet.vue';
import { E, getCookie, hasLogin, saveToken } from "../utils/utils.js";

const signInModal = ref(false);
const ConnectWalletModals = ref(false);
const walletPk = ref('')
const emit = defineEmits(['wallet-status']);

const showInsightDropdown = ref(false)
const issuerInput = ref("")
const insightDropdown = ref(null)

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

onMounted(() => {
  document.addEventListener("mousedown", handleOutsideClick)
  window.addEventListener("scroll", handleScroll);
  walletPk.value =
    getCookie('public_key') ||
    ''
});

function shortMiddle(str, head = 4, tail = 4) {
  if (!str) return '—'
  return str.length > head + tail ? `${str.slice(0, head)}…${str.slice(-tail)}` : str
}

onUnmounted(() => {
  document.removeEventListener("mousedown", handleOutsideClick)
  window.removeEventListener("scroll", handleScroll);
});

const OpenWalletModal = () => { ConnectWalletModals.value = true; };

const Links = [
  {
    name: 'Stake',
    to: '/stake'
  },
  {
    name: 'Buy TKG Tokens',
    href: 'https://lobstr.co/trade/TKG:GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ',
  },
  {
    name: 'Token Insight',
    to: '/token-insight'
  },
]

import { useRouter } from "vue-router"

const router = useRouter()

function goToInsight() {
  if (!issuerInput.value) return

  router.push({
    path: "/token-insight",
    query: { issuer: issuerInput.value }
  })

  showInsightDropdown.value = false
}

function handleOutsideClick(e) {
  if (!insightDropdown.value) return

  const el = Array.isArray(insightDropdown.value)
    ? insightDropdown.value[0]
    : insightDropdown.value

  if (!el.contains(e.target)) {
    showInsightDropdown.value = false
  }
}
</script>
