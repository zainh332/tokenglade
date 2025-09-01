<template>
  <div class="bg-[#1B1B1B] max-w-full px-4 pt-40 mx-auto sm:pt-24 sm:px-6 lg:px-8 ">
    <div class="w-full max-w-[80%] mx-auto bg-[#282826] p-4 rounded-md mb-20">
      <div class="flex flex-col md:flex-row items-end justify-center gap-10 md:gap-20">
        <!-- Text Column -->
        <div class="md:w-1/2 p-4 text-center md:text-left">
          <h1 class="text-[32px] sm:text-[48px] lg:text-[64px] font-normal leading-tight text-white mb-3">
            <span class="block text-[36px] font-semibold leading-[1.4]">
              Join Us
            </span>
          </h1>
          <p class="text-[18px] sm:text-[20px] mt-4 text-white max-w-xl mx-auto lg:mx-0 leading-[1.2]">
            Be part of the future with TokenGlade <br> Stake, earn, and grow with us
          </p>
        </div>
        <!-- Image Column -->
        <div class="md:w-1/2 p-4 flex flex-col items-center md:items-start justify-start gap-6">
          <div class="flex w-full items-center justify-end gap-10">
            <button class="flex items-center gap-2 bg-[#282826] text-white p-2 rounded-[20px] border border-white">
              <GlobeAltIcon class="w-5 h-5" />
              Tokenglade.com
            </button>
            <button class="flex items-center gap-2 bg-[#282826] text-white p-2 rounded-[20px] border border-white"> <svg
                xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path
                  d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4.36a9.1 9.1 0 01-2.88 1.1A4.52 4.52 0 0016 0c-2.5 0-4.5 2.02-4.5 4.5 0 .35.04.69.1 1A12.94 12.94 0 013 1.1a4.5 4.5 0 001.4 6A4.4 4.4 0 012 6v.05c0 2.2 1.57 4.03 3.67 4.45a4.52 4.52 0 01-2.03.08 4.5 4.5 0 004.2 3.12A9.06 9.06 0 010 19.54 12.8 12.8 0 006.92 21c8.3 0 12.84-6.88 12.84-12.85 0-.2 0-.4-.01-.6A9.22 9.22 0 0023 3z" />
              </svg> @Tokenglade </button>
          </div>
        </div>
      </div>
    </div>
    <div class="w-full max-w-[80%] mx-auto rounded-md">
      <div class="flex flex-col md:flex-row items-end justify-center gap-10 md:gap-20 border-b-[1px] border-white">
        <!-- Text Column -->
        <div class="md:w-1/2 py-4 text-center md:text-left">
          <div class="flex">
            <div class="hidden lg:flex sm:space-x-6">
              <router-link v-for="link in Links" :key="link.name" :to="link.to"
                class="inline-flex items-center px-1 pt-1 font-normal text-white text-t14">
                {{ link.name }}
              </router-link>
            </div>
          </div>
        </div>
        <!-- Image Column -->
        <div class="md:w-1/2 py-4 flex flex-col items-center md:items-start justify-start gap-6">
          <div class="flex w-full items-center justify-end gap-6 text-white">
            <!-- Twitter -->
            <a href="https://twitter.com/yourhandle" target="_blank" class="hover:opacity-80">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path
                  d="M23 3a10.9 10.9 0 01-3.14 1.53A4.48 4.48 0 0022.4.36a9.1 9.1 0 01-2.88 1.1A4.52 4.52 0 0016 0c-2.5 0-4.5 2.02-4.5 4.5 0 .35.04.69.1 1A12.94 12.94 0 013 1.1a4.5 4.5 0 001.4 6A4.4 4.4 0 012 6v.05c0 2.2 1.57 4.03 3.67 4.45a4.52 4.52 0 01-2.03.08 4.5 4.5 0 004.2 3.12A9.06 9.06 0 010 19.54 12.8 12.8 0 006.92 21c8.3 0 12.84-6.88 12.84-12.85 0-.2 0-.4-.01-.6A9.22 9.22 0 0023 3z" />
              </svg>
            </a>
            <!-- Facebook -->
            <a href="https://facebook.com/yourhandle" target="_blank" class="hover:opacity-80">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path
                  d="M22.675 0H1.325C.593 0 0 .593 0 1.325v21.351C0 23.407.593 24 1.325 24h11.495v-9.294H9.691V11.01h3.129V8.309c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.464.099 2.797.143v3.243h-1.922c-1.51 0-1.804.718-1.804 1.77v2.333h3.606l-.47 3.696h-3.136V24h6.147C23.407 24 24 23.407 24 22.676V1.325C24 .593 23.407 0 22.675 0z" />
              </svg>
            </a>
            <!-- Instagram -->
            <a href="https://instagram.com/yourhandle" target="_blank" class="hover:opacity-80">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path
                  d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5A4.25 4.25 0 0 0 20.5 16.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zm5.25-.88a1.12 1.12 0 1 1-2.25 0 1.12 1.12 0 0 1 2.25 0z" />
              </svg>
            </a>
            <!-- GitHub -->
            <a href="https://github.com/yourhandle" target="_blank" class="hover:opacity-80">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
                <path
                  d="M12 .297a12 12 0 00-3.8 23.41c.6.11.82-.26.82-.58v-2.04c-3.34.73-4.04-1.61-4.04-1.61-.55-1.4-1.34-1.78-1.34-1.78-1.1-.74.08-.73.08-.73 1.22.09 1.87 1.25 1.87 1.25 1.08 1.86 2.83 1.32 3.52 1 .11-.78.42-1.32.76-1.63-2.66-.3-5.46-1.33-5.46-5.93 0-1.31.47-2.38 1.24-3.22-.12-.3-.54-1.51.12-3.15 0 0 1-.32 3.3 1.23a11.5 11.5 0 016 0c2.3-1.55 3.3-1.23 3.3-1.23.66 1.64.24 2.85.12 3.15.77.84 1.24 1.91 1.24 3.22 0 4.61-2.8 5.63-5.47 5.93.43.38.81 1.12.81 2.26v3.35c0 .32.22.69.82.58A12 12 0 0012 .297z" />
              </svg>
            </a>
          </div>
        </div>
      </div>
      <div class="flex flex-col md:flex-row items-end justify-center gap-10 md:gap-20">
        <!-- Text Column -->
        <div class="md:w-1/2 py-4 text-center md:text-left">
          <div class="hidden lg:flex sm:space-x-6">
            <p class="inline-flex items-center px-1 pt-1 font-normal text-white text-t14">
              Copyright by 2025 - TokenGlade, All Rights Reserved
            </p>
          </div>
        </div>
        <!-- Image Column -->
        <div class="md:w-1/2 py-4 flex flex-col items-center md:items-start justify-start gap-6">
          <div class="flex w-full items-center justify-end gap-6 text-white">
            <router-link v-for="link in FooterLinks" :key="link.name" :to="link.to">
              {{ link.name }}
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { GlobeAltIcon } from "@heroicons/vue/24/outline";

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
const FooterLinks = [
  {
    name: 'Privacy Policy',
    to: '/privacy-policy'
  },
  {
    name: 'Terms And Condition',
    to: '#'
  },
]
</script>

<style lang="scss" scoped></style>
