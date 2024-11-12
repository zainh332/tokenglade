<template>
  <div>
    <TransitionRoot as="template" :show="sidebarOpen">
      <Dialog as="div" class="relative z-40 lg:hidden" @close="sidebarOpen = false">
        <TransitionChild as="template" enter="transition-opacity ease-linear duration-300" enter-from="opacity-0"
          enter-to="opacity-100" leave="transition-opacity ease-linear duration-300" leave-from="opacity-100"
          leave-to="opacity-0">
          <div class="fixed inset-0 bg-[#242E3E]" />
        </TransitionChild>

        <div class="fixed inset-0 flex">
          <TransitionChild as="template" enter="transition ease-in-out duration-300 transform"
            enter-from="-translate-x-full" enter-to="translate-x-0"
            leave="transition ease-in-out duration-300 transform" leave-from="translate-x-0"
            leave-to="-translate-x-full">
            <DialogPanel class="relative flex flex-1 w-full max-w-xs mr-16">
              <TransitionChild as="template" enter="ease-in-out duration-300" enter-from="opacity-0"
                enter-to="opacity-100" leave="ease-in-out duration-300" leave-from="opacity-100" leave-to="opacity-0">
                <div class="absolute top-0 flex justify-center w-16 pt-5 left-full">
                  <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                    <span class="sr-only">Close sidebar</span>
                    <XMarkIcon class="w-6 h-6 text-white" aria-hidden="true" />
                  </button>
                </div>
              </TransitionChild>
              <!-- Sidebar component, swap this element with another sidebar if you like -->
              <div class="flex flex-col px-6 pb-4 overflow-y-auto bg-gray-900 grow gap-y-5 ring-1 ring-white/10">
                <div class="flex items-center h-16 shrink-0">
                  <router-link to="/">
                    <img class="w-auto h-8" :src="logo" alt="Your Company" />
                  </router-link>
                </div>
                <nav class="flex flex-col flex-1">
                  <ul role="list" class="flex flex-col flex-1 gap-y-7">
                    <li>
                      <ul role="list" class="-mx-2 space-y-3">
                        <li v-for="item in stellar_navigation" :key="item.name">
                          <a v-if="!item.comingSoon" :href="item.href" :class="[
                            item.current
                              ? 'bg-gray-800 text-white'
                              : 'text-gray-400 hover:text-white hover:bg-gray-800',
                            'group flex gap-x-3 rounded-md p-2 text-[14px] leading-6 font-normal',
                          ]">
                            <component :is="item.icon" class="w-6 h-6 shrink-0" aria-hidden="true" />
                            {{ item.name }}
                          </a>
                          <span v-else
                            class="group flex gap-x-3 rounded-md p-2 text-[14px] leading-6 font-normal text-gray-400">
                            <component :is="item.icon" class="w-6 h-6 shrink-0" aria-hidden="true" />
                            {{ item.name }} (Coming Soon)
                          </span>
                        </li>
                      </ul>
                    </li>

                    <!-- <li>
                      <div class="flex flex-col gap-4">
                        <router-link
                          to="#"
                          class="text-xs bg-white border rounded-full btn-padding sm:text-t14 border-black/50 text-black/50"
                          >GA424GAVZIOMZ...
                        </router-link>
                        <button @click="OpenWalletModal" type="submit"
                          class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
                          Connect Walletssdf
                        </button>
                      </div>
                    </li> -->
                  </ul>
                </nav>
              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Static sidebar for desktop -->
    <div :class="desktopSidebar
        ? 'hidden translate-x-[0%] transition-all lg:fixed lg:inset-y-0 lg:z-40 lg:flex lg:w-72 lg:flex-col'
        : '-translate-x-[100%] fixed inset-0 transition-all'
      ">
      <!-- Sidebar component, swap this element with another sidebar if you like -->
      <div :class="desktopSidebar
      ? 'desk flex grow translate-x-[0%] items-center py-6  flex-col gap-y-5 overflow-y-auto bg-gray-900  px-0 '
      : 'desk items-center  -translate-x-[100%] flex grow flex-col gap-y-5 overflow-y-auto bg-gray-900 px-0 pb-4'
      ">
        <div class="flex items-center h-16 shrink-0">
          <router-link to="/">
            <img class="w-auto h-10" :src="logo" alt="Your Company" />
          </router-link>
        </div>
        <nav class="flex flex-col flex-1 mt-8">
          <!-- <h1 class="text-gray-400">Stellar</h1> -->
          <ul role="list" class="flex flex-col justify-between flex-1 gap-y-7">
            <li>
              <ul role="list" class="-mx-2 space-y-3">
                <li v-for="item in stellar_navigation" :key="item.name">
                  <a v-if="!item.comingSoon" :href="item.href" :class="[
                    item.current
                      ? 'bg-gray-800 text-white'
                      : 'text-gray-400 hover:text-white hover:bg-gray-800',
                    'group flex gap-x-3 rounded-md p-2 text-[14px] leading-6 font-normal',
                  ]">
                    <component :is="item.icon" class="w-6 h-6 shrink-0" aria-hidden="true" />
                    {{ item.name }}
                  </a>
                  <span v-else
                    class="group flex gap-x-3 rounded-md p-2 text-[14px] leading-6 font-normal text-gray-400">
                    <component :is="item.icon" class="w-6 h-6 shrink-0" aria-hidden="true" />
                    {{ item.name }} (Coming Soon)
                  </span>
                </li>
              </ul>
            </li>

            <li>
              <div class="flex flex-col items-center justify-center gap-2 px-4 mt-24 mb-4 md:justify-between">
                <div class="flex flex-col flex-wrap items-center gap-4 divide-x sm:flex-row">
                  <router-link class="text-xs font-normal text-center text-gray-300" to="/privacy-policy">Privacy
                    Policy</router-link>
                  <router-link class="pl-2 text-xs font-normal text-center text-gray-300" to="#">Contact
                    us</router-link>
                </div>
                <p class="text-xs font-normal text-gray-300">© Copyright by 2024 | TokenGlade</p>
              </div>
            </li>
          </ul>
        </nav>
      </div>
    </div>

    <div :class="desktopSidebar ? 'lg:pl-72' : 'lg:pl-0'">
      <div
        class="sticky top-0 z-10 flex items-center h-16 px-4 bg-white border-b border-gray-200 shadow-sm shrink-0 gap-x-4 sm:gap-x-6 sm:px-6 lg:px-8">
        <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
          <span class="sr-only">Open sidebar</span>
          <Bars3Icon class="w-6 h-6" aria-hidden="true" />
        </button>

        <!-- Separator -->
        <div class="w-px h-6 bg-gray-900/10 lg:hidden" aria-hidden="true" />


        <div class="flex self-stretch flex-1 gap-x-4 lg:gap-x-6">
          <div class="relative flex items-center flex-1" action="#" method="GET">
            <svg xmlns="http://www.w3.org/2000/svg" width="26" height="14" class="hidden cursor-pointer lg:block"
              viewBox="0 0 26 14" fill="none" @click="setDesktop">
              <rect x="0.720215" width="25" height="2" fill="#242E3E" />
              <rect x="0.720215" y="6" width="18" height="2" fill="#242E3E" />
              <rect x="0.720215" y="12" width="25" height="2" fill="#242E3E" />
              <path
                d="M23.6472 9.80786L24.7251 8.72385L23.0425 6.99993L24.7251 5.2876L23.6472 4.192L20.8684 6.99993L23.6472 9.80786Z"
                fill="#242E3E" />
            </svg>
            <p class="sm:pl-6" @click="sidebarOpen = true">Dashboard</p>
          </div>
          <div class="items-center hidden sm:flex gap-x-4 lg:gap-x-6">
            <button id="walletConnected" @click="OpenWalletModal" type="submit"
              class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
              Connect Wallet
            </button>

            <!-- Separator -->
            <!-- <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-900/10" aria-hidden="true" /> -->

            <!-- Profile dropdown -->
            <!-- <Menu as="div" class="relative">
              <MenuButton class="-m-1.5 flex items-center p-1.5">
                <span class="sr-only">Open user menu</span>
                <img class="w-8 h-8 rounded-full bg-gray-50"
                  src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=facearea&facepad=2&w=256&h=256&q=80"
                  alt="" />
              </MenuButton>
            </Menu> -->
          </div>
        </div>
      </div>

      <main class="py-10">
        <div class="px-4 sm:px-6 lg:px-8">
          <slot />
        </div>
      </main>
    </div>
  </div>
  <ConnectWalletModal :open="isModalOpen" @close="isModalOpen = false" />
</template>

<script setup>
import { ref } from "vue";
import {
  Dialog,
  DialogPanel,
  Menu,
  MenuButton,
  MenuItem,
  MenuItems,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import {
  Bars3Icon,
  BellIcon,
  CalendarIcon,
  ChartPieIcon,
  Cog6ToothIcon,
  DocumentDuplicateIcon,
  FolderIcon,
  HomeIcon,
  UsersIcon,
  XMarkIcon,
} from "@heroicons/vue/24/outline";
import { ChevronDownIcon, MagnifyingGlassIcon } from "@heroicons/vue/20/solid";
import logo from "@/assets/lights.png";
import generatorIcon from "@/components/icons/generatorIcon.vue";
import transfer from "@/components/icons/transfer.vue";
import BalanceIIcon from "@/components/icons/BalanceIIcon.vue";
import docIcon from "@/components/icons/docIcon.vue";
import Tabs from "@/components/Tabs.vue";
import { useRoute } from "vue-router";
import ConnectWalletModal from '@/components/ConnectWallet.vue';

const desktopSidebar = ref(true);

import { E, getCookie, hasLogin, saveToken } from "../utils/utils.js";
import { getPublicKey } from "@stellar/freighter-api";

const isModalOpen = ref(false);

// Function to toggle the wallet modal open/close state
const OpenWalletModal = (e) => {
  e.preventDefault(); // Prevent default behavior if this is called from a button or form event
  isModalOpen.value = !isModalOpen.value;  // Toggle the modal's state
};

const stellar_navigation = [
  { name: "Token Generator", href: "/token-generator", icon: generatorIcon, current: false },
  { name: "Claimable Balance", href: "/claimable-balance", icon: BalanceIIcon, current: false },
  { name: "Claim Claimable Balance", href: "/claim-claimable-balance", icon: transfer, current: false }
];

//create listener to listen for connected changes
hear('connected', async (status) => {
  if (status) {
    //has been connected, do the needfull
    if (E('walletConnected')) {
      const walletKey = await getPublicKey()
      E('walletConnected').innerText = walletKey.substring(0, 6) + '...' + walletKey.substring(walletKey.length - 4)
    }
  }
  else {
    //has disconnected
    E('walletConnected').innerText = "Connect Wallet"
  }
})


const route = useRoute();
stellar_navigation.forEach(item => {
  item.current = item.href === route.path;
});

const teams = [
  { id: 1, name: "Heroicons", href: "#", initial: "H", current: false },
  { id: 2, name: "Tailwind Labs", href: "#", initial: "T", current: false },
  { id: 3, name: "Workcation", href: "#", initial: "W", current: false },
];

const userNavigation = [
  { name: "Your profile", href: "#" },
  { name: "Sign out", href: "#" },
];

const sidebarOpen = ref(false);
const setDesktop = () => {
  desktopSidebar.value = !desktopSidebar.value;
};
</script>
