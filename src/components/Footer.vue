<template>
  <div class="max-w-6xl px-4 pt-40 mx-auto sm:pt-24 sm:px-6 lg:px-8">
    <!-- Logo -->
    <div class="flex items-center justify-center max-w-xs mx-auto">
      <img :src="footerLogo" alt="" />
    </div>

    <!-- Footer Links -->
    <div class="flex flex-wrap items-center justify-center max-w-sm gap-6 mx-auto my-8">
      <router-link class="font-normal text-t14" to="/">Home</router-link>
      <router-link class="font-normal text-t14" to="/about-us">About us</router-link>
      <a href="/whitepaper.pdf" target="_blank" rel="noopener" class="font-normal text-t14">
        Whitepaper
      </a>
    </div>

    <!-- Logos -->
    <div class="flex flex-wrap items-center justify-center max-w-sm gap-4 mx-auto">

      <!-- Telegram -->
      <!-- <div class="relative flex items-center justify-center w-12 h-12 rounded-full shadow drop-shadow-sm">
        <a href="https://t.me/tokenglade" target="_blank" rel="noopener noreferrer"
          class="absolute top-0 left-0 w-full h-full"></a>
        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="16" viewBox="0 0 19 16" fill="none">
          <g clip-path="url(#clip0_131_377)">
            <path
              d="M17.1703 0.108255C17.1703 0.108255 18.8703 -0.591745 18.6703 1.00826C18.6703 1.70826 18.1703 4.00826 17.8703 6.50826L16.7703 13.9083C16.7703 13.9083 16.6703 15.0083 15.8703 15.2083C15.0703 15.4083 13.7703 14.5083 13.5703 14.4083C13.3703 14.3083 10.0703 12.1083 8.87033 11.1083C8.57033 10.8083 8.17033 10.3083 8.87033 9.60826L13.7703 4.90826C14.3703 4.30826 14.8703 3.00826 12.5703 4.60825L5.97033 9.00826C5.97033 9.00826 5.27033 9.50826 3.77033 9.00826L0.77033 8.10826C0.77033 8.10826 -0.32967 7.40825 1.57033 6.70825C6.27033 4.50826 12.0703 2.20825 17.1703 0.108255Z"
              fill="#28A8EA" />
          </g>
        </svg>
      </div> -->
      <!-- End Telegram -->

      <!-- Medium -->
      <div class="relative flex items-center justify-center w-12 h-12 rounded-full shadow drop-shadow-sm">

        <a href="https://medium.com/@tokenglade" target="_blank" rel="noopener noreferrer"
          class="absolute top-0 left-0 w-full h-full"></a>
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
      </div>
      <!-- End Medium -->

      <!-- X -->
      <div class="relative flex items-center justify-center w-12 h-12 rounded-full shadow drop-shadow-sm">
        <a href="https://x.com/TokenGlade" target="_blank" rel="noopener noreferrer"
          class="absolute top-0 left-0 w-full h-full"></a>
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none">
          <path
            d="M18.244 2.25H21.552L14.325 10.51L22.827 21.75H16.171L10.956 14.933L4.99 21.75H1.68L9.41 12.915L1.254 2.25H8.079L12.793 8.481L18.244 2.25ZM17.083 19.77H18.916L7.084 4.126H5.117L17.083 19.77Z"
            fill="black" />
        </svg>
      </div>
      <!-- End X -->

    </div>

    <div class="flex flex-col items-center justify-center gap-6 mt-24 mb-8 pb-4 md:flex-row md:justify-between">
      <p class="font-normal text-t16">© Copyright by 2026 - TokenGlade</p>
      <div class="flex flex-col items-center gap-4 sm:flex-row">
        <router-link class="font-normal text-t16" to="/privacy-policy">Privacy Policy</router-link>
        <router-link class="font-normal text-t16" to="/terms-service">Terms of Service</router-link>
        <router-link class="font-normal text-t16" to="#">Contact Us</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import footerLogo from "@/assets/token-glade-logo.png";
</script>

<style lang="scss" scoped></style>
