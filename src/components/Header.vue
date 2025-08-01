<template>
  <Disclosure as="nav" class="bg-white " v-slot="{ open  }">
    <div class="max-w-6xl px-4 mx-auto sm:px-6 lg:px-8">
      <div class="flex justify-between h-20">
        <div class="flex">
          <div class="flex items-center flex-shrink-0">
            <router-link to="/">
              <img class="w-auto h-8" :src="logo" alt="TokenGlade Logo" />
            </router-link>
          </div>
          <div class="hidden sm:ml-6 lg:flex sm:space-x-6">
            <router-link v-for="link in Links" :key="link.name" :to="link.to"
              class="inline-flex items-center px-1 pt-1 font-normal text-gray-900 text-t14">
              {{ link.name }}
            </router-link>
          </div>
        </div>
        <div class="hidden sm:ml-6 lg:flex sm:items-center">

          <!-- Profile dropdown -->
          <div class="flex items-center gap-2 ">
            <button id="walletConnected" @click="OpenWalletModal" type="submit" class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
              Connect Wallet
            </button>

            <ConnectWalletModal :open="ConnectWalletModals" @close="ConnectWalletModals = false" />
            
            <!-- <router-link v-if="isWalletConnected" id="dashboard" to="/token-generator" class="text-white rounded-full btn-padding text-t14 bg-gradient">
              Dashboard
            </router-link> -->

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
      <div class="pt-2 pb-3 space-y-1">
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

import { ref } from "vue";
import Modal from '@/components/Modal.vue';
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import { E, getCookie, hasLogin, saveToken } from "../utils/utils.js";
import { getPublicKey } from "@stellar/freighter-api";
const signInModal = ref(false);
const ConnectWalletModals = ref(false);
const emit = defineEmits(['wallet-status']);
const isWalletConnected = ref(false);

const setOpen = (e) => {
  e.preventDefault();
  signInModal.value = !signInModal.value;
};

const OpenWalletModal = (e) => {
  e.preventDefault();
  ConnectWalletModals.value = true;
};

const Links = [
  {
    name: 'About Us',
    to: '/about-us'
  },
  {
    name: 'Privacy Policy',
    to: '/privacy-policy'
  },
]


//create listener to listen for connected changes
hear('connected', async (status) => {
  if (status) {
    const walletKey = await getPublicKey()

    emit('wallet-status', {
      connected: true,
      walletKey,
    });

    //has been connected, do the needfull
    if (E('walletConnected')) {
      isWalletConnected.value = true;
      E('walletConnected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
    }
  }
  else {
    //has disconnected
    isWalletConnected.value = false;
    emit('wallet-status', {
      connected: false,
    });
    E('walletConnected').innerText = "Connect Wallet"
  }
})
</script>