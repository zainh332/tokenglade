<template>
  <footer class="bg-[#070A13] text-slate-400 border-t border-slate-900 py-5 relative overflow-hidden">
    <div class="max-w-[1440px] px-4 mx-auto sm:px-6 lg:px-8 relative z-10">
      
      <!-- Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-8 border-b border-slate-900">
        <!-- Logo & Tagline -->
        <div class="space-y-4 md:col-span-1 text-center md:text-left">
          <div class="flex items-center justify-center md:justify-start gap-2.5">
            <img :src="footerLogo" alt="TokenGlade Logo" class="w-8 h-8 object-contain" />
            <span class="text-base font-black text-white tracking-tight uppercase">Token<span class="text-cyan-400">Glade</span></span>
          </div>
          <p class="text-xs text-slate-500 max-w-xs leading-relaxed mx-auto md:mx-0">
            Real-time analytics, staking, and explorer utilities for the Stellar network.
          </p>
        </div>

        <!-- Links -->
        <div class="grid grid-cols-2 md:grid-cols-3 md:col-span-3 gap-6 text-center md:text-left">
          <!-- Spacer column to preserve spacing after Ecosystem removal on desktop, hidden on mobile -->
          <div class="hidden md:block"></div>
          
          <!-- Legal -->
          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Legal</h4>
            <div class="flex flex-col gap-2 text-xs text-slate-500">
              <router-link to="/privacy-policy" class="hover:text-white transition-colors">Privacy Policy</router-link>
              <router-link to="/terms-service" class="hover:text-white transition-colors">Terms of Service</router-link>
            </div>
          </div>

          <!-- Contact -->
          <div>
            <h4 class="text-xs font-bold text-white uppercase tracking-wider mb-3">Support</h4>
            <div class="flex flex-col gap-2 text-xs text-slate-500">
              <button @click="contactModalOpen = true" class="hover:text-white transition-colors text-left w-full focus:outline-none">
                Contact Us
              </button>
              <a href="/whitepaper.pdf" target="_blank" class="hover:text-white transition-colors">Whitepaper</a>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom -->
      <div class="pt-8 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs">
        <p class="text-slate-600">
          © 2026 TokenGlade Terminal. All rights reserved.
        </p>
        
        <!-- Socials -->
        <div class="flex items-center gap-4">
          <a href="https://x.com/TokenGlade" target="_blank" class="text-slate-500 hover:text-white transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M18.244 2.25H21.552L14.325 10.51L22.827 21.75H16.171L10.956 14.933L4.99 21.75H1.68L9.41 12.915L1.254 2.25H8.079L12.793 8.481L18.244 2.25ZM17.083 19.77H18.916L7.084 4.126H5.117L17.083 19.77Z" />
            </svg>
          </a>
          <a href="https://medium.com/@tokenglade" target="_blank" class="text-slate-500 hover:text-white transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M13.54 12a6.8 6.8 0 01-6.77 6.82A6.8 6.8 0 010 12a6.8 6.8 0 016.77-6.82A6.8 6.8 0 0113.54 12zm5.72 0c0 3.42-1.33 6.2-3 6.2s-3-2.78-3-6.2 1.33-6.2 3-6.2 3 2.78 3 6.2zm4.74 0c0 3.14-.38 5.7-1 5.7s-.86-2.56-.86-5.7.38-5.7.86-5.7 1 2.56 1 5.7z" />
            </svg>
          </a>
        </div>
      </div>

    </div>
    <ContactModal v-model="contactModalOpen" />
  </footer>
</template>

<script setup>
import { ref } from "vue";
import ContactModal from "@/components/ContactModal.vue";
import footerLogo from "@/assets/token-glade-logo.png";

const contactModalOpen = ref(false);
</script>
