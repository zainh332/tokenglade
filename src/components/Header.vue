<template>
  <Disclosure as="nav" class="bg-white " v-slot="{ open }">
    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-20 justify-between">
        <div class="flex">
          <div class="flex flex-shrink-0  items-center">
            <img class="h-8 w-auto" :src="logo" alt="Your Company" />
          </div>
          <div class="hidden sm:ml-6 lg:flex sm:space-x-6">
           
            <!-- <a v-for="link in Links" :key="link.name" href="#" class="inline-flex items-center px-1 pt-1 text-t14 text-gray-900 font-normal">{{link.name}}</a> -->
            <router-link
            v-for="link in Links" 
            :key="link.name" 
            :to="link.to"
             class="inline-flex items-center px-1 pt-1 text-t14 text-gray-900 font-normal"
             >
             {{link.name}}
            </router-link>

          </div>
        </div>
        <div class="hidden sm:ml-6 lg:flex sm:items-center">
           

          <!-- Profile dropdown -->
          <div class="items-center flex gap-2 ">
          <!-- <router-link to="/register" class="px-5 py-1.5 rounded-full hover-gradient hover:text-white hover:border-white text-t14 leading-[20px] border border-black/50">Sign up</router-link> -->
          <!-- <router-link class="px-5 py-1.5 rounded-full text-t14 overflow-hidden leading-[20px] text-white bg-gradient  bg-cover" to="#">Sign in</router-link> -->
          <!-- <router-link
              to="#"
              class="btn-padding text-xs sm:text-t14 rounded-full text-white bg-gradient"
              >Connect Wallet
            </router-link> -->
          <button
          @click="setOpen"
            type="submit"
            class="px-5 py-1.5 rounded-full text-t14 overflow-hidden leading-[20px] text-white bg-gradient  bg-cover"
          >
            Sign In
          </button>
      </div>
        </div>
        <div class="-mr-2 flex items-center lg:hidden">
          <!-- Mobile menu button -->
          <DisclosureButton class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
            <span class="sr-only">Open main menu</span>
            <Bars3Icon v-if="!open" class="block h-6 w-6" aria-hidden="true" />
            <XMarkIcon v-else class="block h-6 w-6" aria-hidden="true" />
          </DisclosureButton>
        </div>
      </div>
    </div>

    <DisclosurePanel class="lg:hidden">
      <div class="space-y-1 pb-3 pt-2">
        <!-- Current: "bg-indigo-50 border-indigo-500 text-indigo-700", Default: "border-transparent text-gray-500 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-700" --> 
        <DisclosureButton v-for="link in Links" :key="link.name" as="a" href="#" class="block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-gray-500 hover:border-gray-300 hover:bg-gray-50 hover:text-gray-700">{{link.name}}</DisclosureButton>
        
      </div>
      <div class="border-t border-gray-200 pb-3 pt-4">
        
        <div class="mt-3 space-y-1">
          <!-- <DisclosureButton as="a" href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">Sign up</DisclosureButton> -->
          <DisclosureButton as="a" href="#" class="block px-4 py-2 text-base font-medium text-gray-500 hover:bg-gray-100 hover:text-gray-800">Sign in</DisclosureButton>
          
        </div>
      </div>
    </DisclosurePanel>
  </Disclosure>
  <Modal :open="open"  />
</template>

<script setup>
import { Disclosure, DisclosureButton, DisclosurePanel, Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue'
import { Bars3Icon, BellIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import logo from '@/assets/logo.png';

import { ref } from "vue";
import Modal from '@/components/Modal.vue';
const open  = ref(false);

const setOpen = (e) => {
  e.preventDefault();
  open.value = !open.value;
  if (open.value) {
    Modal.open();
  } else {
    Modal.close();
  }
};

const Links = [
  {
    name:'Generator',
    to:'/token-generator'
  },
  {
    name:'About Us',
    to:'/about-us'
  },
]
</script>