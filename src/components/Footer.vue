<template>
  <footer class="border-t border-gray-200">
    <div class="max-w-6xl px-4 py-12 mx-auto sm:px-6 lg:px-8">

      <!-- Top -->
      <div class="flex flex-col gap-10 md:flex-row md:justify-between">

        <!-- Left -->
        <div class="max-w-sm mx-auto text-center md:mx-0 md:text-left">
          <img :src="footerLogo" alt="TokenGlade" class="w-32 mx-auto md:mx-0" />

          <p class="mt-4 text-sm leading-relaxed text-gray-600">
            Simplifying token creation, analytics, staking, and multi-chain
            blockchain tools for everyone.
          </p>

          <!-- Social -->
          <div class="flex items-center justify-center gap-4 mt-6 md:justify-start">

            <!-- Medium -->
            <a href="https://medium.com/@tokenglade" target="_blank"
              class="flex items-center justify-center w-10 h-10 transition border rounded-full hover:bg-gray-100">

              <svg xmlns="http://www.w3.org/2000/svg" width="21" height="16" viewBox="0 0 21 16" fill="none">
                <g clip-path="url(#clip0_131_383)">
                  <path
                    d="M0.317993 14.8083L2.71799 12.1083V2.7083L0.617993 0.408301V0.00830078H6.21799L10.718 9.5083L14.618 0.00830078H20.018V0.408301L18.218 2.0083V13.2083L20.018 14.8083V15.2083H12.218V14.8083L14.018 12.7083V4.3083L9.61799 15.2083H9.01799L3.81799 4.5083V12.0083L6.21799 14.8083V15.2083H0.317993V14.8083Z"
                    fill="black" />
                </g>
                <defs>
                  <clipPath id="clip0_131_383">
                    <rect width="19.7" height="15.2" fill="white" transform="translate(0.317993 0.00830078)" />
                  </clipPath>
                </defs>
              </svg>
            </a>

            <!-- X -->
            <a href="https://x.com/TokenGlade" target="_blank"
              class="flex items-center justify-center w-10 h-10 transition border rounded-full hover:bg-gray-100">

              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path
                  d="M18.244 2.25H21.552L14.325 10.51L22.827 21.75H16.171L10.956 14.933L4.99 21.75H1.68L9.41 12.915L1.254 2.25H8.079L12.793 8.481L18.244 2.25ZM17.083 19.77H18.916L7.084 4.126H5.117L17.083 19.77Z"
                  fill="black" />
              </svg>
            </a>

          </div>
        </div>

        <!-- Right -->
        <div class="grid grid-cols-2 gap-10 sm:grid-cols-3">

          <!-- Navigation -->
          <div>
            <h3 class="mb-4 text-sm font-semibold text-white">
              Navigation
            </h3>

            <div class="flex flex-col gap-3">
              <router-link to="/">Home</router-link>
              <router-link to="/about-us">About us</router-link>

              <a href="/whitepaper.pdf" target="_blank">
                Whitepaper
              </a>
            </div>
          </div>

          <!-- Legal -->
          <div>
            <h3 class="mb-4 text-sm font-semibold text-white">
              Legal
            </h3>

            <div class="flex flex-col gap-3">
              <router-link to="/privacy-policy">
                Privacy Policy
              </router-link>

              <router-link to="/terms-service">
                Terms of Service
              </router-link>
            </div>
          </div>

          <!-- Contact -->
          <div>
            <h3 class="mb-4 text-sm font-semibold text-white">
              Contact
            </h3>

            <button @click="contactModalOpen = true">
              Contact Us
            </button>
          </div>

        </div>
      </div>

      <!-- Bottom -->
      <div class="pt-6 mt-10 border-t border-gray-200">
        <p class="text-sm text-center text-gray-500 md:text-left">
          © Copyright 2026 TokenGlade. All rights reserved.
        </p>
      </div>

      <ContactModal v-model="contactModalOpen" />
    </div>
  </footer>
</template>

<script setup>
import { ref } from "vue";
import ContactModal from "@/components/ContactModal.vue";
import footerLogo from "@/assets/token-glade-logo.png";

const contactModalOpen = ref(false);
</script>

<style lang="scss" scoped></style>
