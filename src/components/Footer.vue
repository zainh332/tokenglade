<template>
  <div class="mx-auto max-w-6xl pt-40 sm:pt-64 px-4 sm:px-6 lg:px-8">
    <!-- Footer -->
    <div class="max-w-xs flex items-center mx-auto justify-center">
      <img :src="footerLogo" alt="" />
    </div>

    <!-- Footer Links -->

    <div
      class="max-w-sm mx-auto flex my-8 justify-center flex-wrap items-center gap-6"
    >
      <router-link class="text-t14 font-normal" to="/">Home</router-link>
      <router-link class="text-t14 font-normal" to="/about-us">About us</router-link>
      <router-link class="text-t14 font-normal" to="/token-generator">Generator</router-link
      >
      <!-- <router-link class="text-t14 font-normal" to="#"
        >Dashboard</router-link
      > -->
      <!-- <router-link class="text-t14 font-normal" to="#">Docs</router-link> -->
    </div>

    <!-- Logos -->
    <div class="max-w-sm mx-auto flex gap-4 items-center justify-center flex-wrap">

    <!-- Telegram -->
      <div class="h-12 relative w-12 rounded-full shadow drop-shadow-sm flex items-center justify-center">
        <a href="https://t.me/tokenglade" target="_blank" rel="noopener noreferrer" 
        class="absolute w-full h-full top-0 left-0"></a>
        <svg
        xmlns="http://www.w3.org/2000/svg"
        width="19"
        height="16"
        viewBox="0 0 19 16"
        fill="none"
        >
          <g clip-path="url(#clip0_131_377)">
            <path
              d="M17.1703 0.108255C17.1703 0.108255 18.8703 -0.591745 18.6703 1.00826C18.6703 1.70826 18.1703 4.00826 17.8703 6.50826L16.7703 13.9083C16.7703 13.9083 16.6703 15.0083 15.8703 15.2083C15.0703 15.4083 13.7703 14.5083 13.5703 14.4083C13.3703 14.3083 10.0703 12.1083 8.87033 11.1083C8.57033 10.8083 8.17033 10.3083 8.87033 9.60826L13.7703 4.90826C14.3703 4.30826 14.8703 3.00826 12.5703 4.60825L5.97033 9.00826C5.97033 9.00826 5.27033 9.50826 3.77033 9.00826L0.77033 8.10826C0.77033 8.10826 -0.32967 7.40825 1.57033 6.70825C6.27033 4.50826 12.0703 2.20825 17.1703 0.108255Z"
              fill="#28A8EA"
              />
          </g>
        </svg>
      </div>
      <!-- End Telegram -->
      
      <!-- Medium -->
      <div class="h-12 w-12 relative rounded-full shadow drop-shadow-sm flex items-center justify-center">
        <!-- <router-link
        to="#"
        class="absolute w-full h-full top-0 left-0"
        ></router-link> -->
        
        <a href="https://medium.com/@tokenglade" target="_blank" rel="noopener noreferrer" 
        class="absolute w-full h-full top-0 left-0"></a>
        <svg
        xmlns="http://www.w3.org/2000/svg"
          width="21"
          height="16"
          viewBox="0 0 21 16"
          fill="none"
        >
          <g clip-path="url(#clip0_131_383)">
            <path
              d="M0.317993 14.8083L2.71799 12.1083V2.7083L0.617993 0.408301V0.00830078H6.21799L10.718 9.5083L14.618 0.00830078H20.018V0.408301L18.218 2.0083V13.2083L20.018 14.8083V15.2083H12.218V14.8083L14.018 12.7083V4.3083L9.61799 15.2083H9.01799L3.81799 4.5083V12.0083L6.21799 14.8083V15.2083H0.317993V14.8083Z"
              fill="black"
            />
          </g>
          <defs>
            <clipPath id="clip0_131_383">
              <rect
                width="19.7"
                height="15.2"
                fill="white"
                transform="translate(0.317993 0.00830078)"
              />
            </clipPath>
          </defs>
        </svg>
      </div>
      <!-- End Medium -->

      <!-- Twitter -->
      <div class="h-12 w-12 relative rounded-full shadow drop-shadow-sm flex items-center justify-center" >
        <a href="https://twitter.com/TokenGlade" target="_blank" rel="noopener noreferrer" 
        class="absolute w-full h-full top-0 left-0"></a>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="20"
          height="16"
          viewBox="0 0 20 16"
          fill="none"
        >
          <g clip-path="url(#clip0_131_379)">
            <path
              d="M17.2156 3.8083C17.2156 4.0083 17.2156 4.1083 17.2156 4.3083C17.2156 9.4083 13.3156 15.2083 6.31565 15.2083C4.11565 15.2083 2.11565 14.6083 0.415649 13.5083C0.715649 13.5083 1.01565 13.6083 1.31565 13.6083C3.11565 13.6083 4.81565 13.0083 6.11565 12.0083C4.41565 12.0083 3.01565 10.9083 2.51565 9.3083C2.71565 9.3083 3.01565 9.4083 3.21565 9.4083C3.51565 9.4083 3.91565 9.4083 4.21565 9.3083C2.51565 8.8083 1.21565 7.3083 1.21565 5.4083C1.71565 5.7083 2.31565 5.9083 2.91565 5.9083C1.91565 5.1083 1.21565 4.0083 1.21565 2.6083C1.21565 1.9083 1.41565 1.2083 1.71565 0.708301C3.61565 3.0083 6.41565 4.6083 9.61565 4.7083C9.51565 4.4083 9.51565 4.1083 9.51565 3.8083C9.51565 1.7083 11.2156 0.00830078 13.3156 0.00830078C14.4156 0.00830078 15.4156 0.508301 16.1156 1.2083C17.0156 1.0083 17.8156 0.708301 18.5156 0.308301C18.2157 1.2083 17.6156 1.9083 16.8156 2.4083C17.6156 2.3083 18.3156 2.1083 19.0156 1.8083C18.6157 2.6083 17.9156 3.2083 17.2156 3.8083Z"
              fill="#1D9BF0"
            />
          </g>
          <defs>
            <clipPath id="clip0_131_379">
              <rect
                width="18.7"
                height="15.2"
                fill="white"
                transform="translate(0.415649 0.00830078)"
              />
            </clipPath>
          </defs>
        </svg>
      </div>
      <!-- End Twitter -->

    </div>

    <div class="flex items-center gap-6 mt-24 mb-8 justify-center flex-col md:flex-row md:justify-between">
      <p class="text-t16 font-normal">© Copyright by 2023 - TokenGlade</p>
    
      <div class=" flex items-center sm:flex-row flex-col gap-4 ">
        <router-link class="text-t16 font-normal" to="/privacy-policy">Privacy Policy</router-link>
        <router-link class="text-t16 font-normal" to="/terms-service">Terms of Service</router-link>
        <router-link class="text-t16 font-normal" to="#">Contact Us</router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import footerLogo from "@/assets/footer-logo.png";
</script>

<style lang="scss" scoped></style>
