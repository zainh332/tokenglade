<template>
  <Disclosure as="nav"
    class="fixed top-[1rem] left-0 w-full z-[1] transition-transform duration-700 ease-[cubic-bezier(0.68,-0.55,0.27,1.55)] origin-top"
    :class="{
      '-translate-y-[120%] scale-y-90': hideHeader,
      'translate-y-0 scale-y-100': !hideHeader
    }" v-slot="{ open }">


    <div class="max-w-6xl px-4 mx-auto sm:px-6 lg:px-8 rounded-[10rem] shadow-lg bg-white">
      <div class="flex justify-between h-20">
        <div class="flex">
          <div class="flex items-center flex-shrink-0">
            <router-link to="/">
              <img class="w-auto h-8" :src="logo" alt="TokenGlade Logo" />
            </router-link>
          </div>
          <div class="hidden sm:ml-6 lg:flex sm:space-x-6">

            <!-- <a v-for="link in Links" :key="link.name" href="#" class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">{{link.name}}</a> -->
            <router-link v-for="link in Links" :key="link.name" :to="link.to"
              class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">
              {{ link.name }}
            </router-link>

          </div>
        </div>
        <div class="hidden sm:ml-6 lg:flex sm:items-center">


          <!-- Profile dropdown -->
          <div class="flex items-center gap-2 ">
            <!-- <router-link to="/register" class="px-5 py-1.5 rounded-full hover-gradient hover:text-white hover:border-white text-t14 leading-[20px] border border-black/50">Sign up</router-link> -->
            <!-- <router-link class="px-5 py-1.5 rounded-full text-t14 overflow-hidden leading-[20px] text-white bg-gradient  bg-cover" to="#">Sign in</router-link> -->


            <button id="walletConnected" @click="OpenWalletModal" type="submit" class="text-xs text-white rounded-full btn-padding sm:text-t14 
         bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
         bg-[length:200%_200%] bg-no-repeat animate-gradientMove">
              Connect Wallet
            </button>

            <ConnectWalletModal :open="ConnectWalletModals" @close="ConnectWalletModals = false" />

            <!-- <button @click="setOpen" type="submit"
              class="px-5 py-1.5 rounded-full text-t14 overflow-hidden leading-[20px] text-white bg-gradient  bg-cover">
              Sign In
            </button> -->

            <router-link v-if="isWalletConnected" id="dashboard" to="/token-generator"
              class="text-white rounded-full btn-padding text-t14 bg-gradient">
              Dashboard
            </router-link>

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

    <DisclosurePanel class="lg:hidden">
      <div class="pt-5 pb-20 space-y-1 bg-white ">
        <router-link v-for="link in Links" :key="link.name" :to="link.to"
          class="block py-2 pl-3 pr-4 text-base font-medium text-gray-500 border-l-4 border-transparent hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700">
          {{ link.name }}
        </router-link>
        <!-- Add Connect Wallet button for mobile -->
        <button id="walletConnected" @click="OpenWalletModal" type="submit"
          class="w-full px-4 py-2 mt-2 text-base font-medium text-white rounded-md bg-gradient hover:bg-blue-700">
          Connect Wallet
        </button>
      </div>
    </DisclosurePanel>
  </Disclosure>

  <Modal :open="signInModal" />
  <ConnectWalletModal :open="ConnectWalletModals" />

</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, BellIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import logo from '@/assets/logo.png';

import { ref, onMounted, onUnmounted } from "vue";
import Modal from '@/components/Modal.vue';
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import { E, getCookie, hasLogin, saveToken } from "../utils/utils.js";
import { getPublicKey } from "@stellar/freighter-api";

const signInModal = ref(false);
const ConnectWalletModals = ref(false);
const isWalletConnected = ref(false);

// Scroll hide/show functionality
const hideHeader = ref(false);
let lastScrollY = 0;

const handleScroll = () => {
  if (window.scrollY > lastScrollY && window.scrollY > 20) {
    hideHeader.value = true; // scrolling down → hide
  } else {
    hideHeader.value = false; // scrolling up → show
  }
  lastScrollY = window.scrollY;
};

onMounted(() => {
  window.addEventListener("scroll", handleScroll);
});

onUnmounted(() => {
  window.removeEventListener("scroll", handleScroll);
});

const setOpen = (e) => {
  e.preventDefault();
  signInModal.value = !signInModal.value;
};

const OpenWalletModal = (e) => {
  e.preventDefault();
  ConnectWalletModals.value = true;
};

// const OpenWalletModal = (e) => {
//   e.preventDefault();
//   if (ConnectWalletModals.value) { ConnectWalletModals.value = false }
//   setTimeout(() => {
//     ConnectWalletModals.value = !ConnectWalletModals.value;
//   }, 100)
// };

const Links = [
  {
    name: 'About Us',
    to: '/about-us'
  },
  {
    name: 'Privacy Policy',
    to: '/privacy-policy'
  },
  {
    name: 'Buy TKG Tokens',
    to: '/buy-stake'
  },
]

//create listener to listen for connected changes
hear('connected', async (status) => {
  if (status) {
    //has been connected, do the needfull
    if (E('walletConnected')) {
      isWalletConnected.value = true;
      const walletKey = await getPublicKey()
      E('walletConnected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
    }
  }
  else {
    //has disconnected
    isWalletConnected.value = false;
    E('walletConnected').innerText = "Connect Wallet"
  }
})
</script>
