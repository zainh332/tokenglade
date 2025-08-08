<template>
  <div>
    <Header @wallet-status="handleWalletStatus" />
    <div>
      <div class="container mx-auto py-10">
        <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
          <!-- Text Content -->
          <div class="text-center mx-auto lg:text-left max-w-3xl">
            <h1 class="text-[32px] sm:text-[48px] lg:text-[64px] font-normal leading-tight text-dark">
              Effortless Blockchain
              <span class="block font-semibold">Token Creation</span>
            </h1>
            <p class="text-[18px] sm:text-[20px] mt-4 text-dark max-w-xl mx-auto lg:mx-0">
              Easily generate blockchain tokens across multiple networks with our intuitive platform.
            </p>
            <div class="py-5">
              <!-- <button @click="openTokenModal" type="button" id="mintToken"
                class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
                Mint Token
              </button> -->
              <button @click="openTokenModal" type="button" id="mintToken"
              class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
              {{ isWalletConnected ? 'Mint Token' : 'Connect Wallet to Mint' }}
            </button>

            <DisclosurePanel class="lg:hidden">
              <div class="pt-6 space-y-1">
                <a href="https://lobstr.co/trade/native/TKG:GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
                  Buy TKG Tokens from Lobstr
                </a>
              </div>
            </DisclosurePanel>
            </div>
          </div>

          <!-- Image -->
          <div class="hidden lg:block flex-shrink-0">
            <img class="w-[340px] h-[400px] xl:w-[500px] object-contain" :src="flower" alt="Decorative Flower" />
          </div>
        </div>
      </div>
      <!-- Table -->

      <Table />

      <!-- <div
        class="grid max-w-6xl grid-cols-1 gap-12 px-4 py-10 mx-auto cards md:grid-cols-2 lg:grid-cols-3 sm:pt-24 sm:px-6 lg:px-8">
        <div
          class="drop-shadow bg-white hover:bg-purple-600 cursor-pointer group transition-all group duration-200 card-gradient h-[320px] rounded-[30px] flex flex-col px-10 items-center justify-center">
          <h1 class="font-semibold group-hover:text-white text-t34">{{ data.total_tokens }}</h1>
          <p class="text-center group-hover:text-white text-[20px] font-normal">
            Tokens created so far on TokenGlade
          </p>
        </div>
        <div
          class="drop-shadow bg-white hover:bg-purple-600 cursor-pointer group transition-all group duration-200 card-gradient h-[320px] rounded-[30px] flex flex-col px-10 items-center justify-center">
          <h1 class="font-semibold group-hover:text-white text-t34">{{ data.total_claimablebalance_users }}</h1>
          <p class="text-center group-hover:text-white text-[20px] font-normal">
            Total Claimable Balance Users
          </p>
        </div>
        <div
          class="drop-shadow bg-white hover:bg-purple-600 cursor-pointer group transition-all group duration-200 card-gradient h-[320px] rounded-[30px] flex flex-col px-10 items-center justify-center">
          <h1 class="font-semibold group-hover:text-white text-t34">{{ data.total_claimablebalance }}</h1>
          <p class="text-center group-hover:text-white text-[20px] font-normal">
            Claimable Balance to other wallets with TokenGlade
          </p>
        </div>
      </div> -->

      <div class="py-10 my-10 bg-white text-center">
        <h2 class="text-3xl font-bold mb-10">How It works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-6xl mx-auto">
          <!-- Step 1 -->
          <div class="flex flex-col items-center">
            <div class="bg-blue-100 p-4 rounded-full mb-4">
              <img :src="Phone" alt="Choose Blockchain" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">Choose Your Blockchain</h3>
            <p class="text-dark text-sm">
              Currently, TokenGlade supports the Stellar blockchain. More chains coming soon.
            </p>
          </div>

          <!-- Step 2 -->
          <div class="flex flex-col items-center">
            <div class="bg-green-100 p-4 rounded-full mb-4">
              <img :src="Wallet" alt="Connect Wallet" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">Connect Your Wallet</h3>
            <p class="text-dark text-sm">
              TokenGlade will automatically suggest the right wallet<br />
              based on the blockchain you select (e.g., Freighter for Stellar).
            </p>
          </div>

          <!-- Step 3 -->
          <div class="flex flex-col items-center">
            <div class="bg-yellow-100 p-4 rounded-full mb-4">
              <img :src="Coin" alt="Mint Token" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">Mint Your Token</h3>
            <p class="text-dark text-sm">
              Enter your token details and click “Create”.<br />
              Your token goes live instantly on Stellar.<br />
              Other blockchains coming soon!
            </p>
          </div>
        </div>
      </div>

      <div class="max-w-6xl gap-12 px-4 py-10 mx-auto cards sm:pt-24 sm:px-6 lg:px-8">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <div class="w-32 h-1 px-8 mx-auto rounded-full bg-gradient"></div>
            <h1 class="mt-2 font-semibold leading-relaxed text-center text-black text-t34 sm:leading-lh65">
              Frequent questions
            </h1>
            <!-- <p class="text-center text-t16">
              It is a long established fact that a reader will be distracted
            </p> -->
          </div>
        </div>
        <Faq />
      </div>
      <GenerateTokenModal :show="isTokenModalOpen" @close="isTokenModalOpen = false" />
      <Newsletter />
      <Footer />
    </div>
  </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Table from "@/components/Table.vue"; // Stellar Tokens Generated
import Card from "@/components/Card.vue";
import flower from "@/assets/flower.png";
import Faq from "@/components/Faq.vue";
import Newsletter from "@/components/Newsletter.vue";
import GenerateTokenModal from '@/components/GenerateTokenModal.vue'
import Footer from "@/components/Footer.vue";
import Coin from "@/assets/coin.png";
import Phone from "@/assets/phone.png";
import Wallet from "@/assets/wallet.jpg";
import axios from 'axios'
import { ref, computed, defineProps, onMounted, watch } from "vue";
import Swal from 'sweetalert2';

const isWalletConnected = ref(false);
const walletKey = ref('');

function handleWalletStatus(event) {
  isWalletConnected.value = event.connected;
  walletKey.value = event.walletKey || '';
}

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const isTokenModalOpen = ref(false)

const openTokenModal = (e) => {
  e.preventDefault()
  isTokenModalOpen.value = true
}
const closeTokenModal = () => {
  isTokenModalOpen  .value = false
}

const data = ref({
  total_tokens: 0,
  total_claimablebalance: 0,
  total_claimablebalance_users: 0
});

async function fetchdata() {
  try {
    const response = await axios.get('/api/count_data', {
      headers: {
        'X-CSRF-TOKEN': csrfToken
      }
    });
    if (response.data.status === "success") {
      data.value = {
        total_tokens: response.data.total_tokens,
        total_claimablebalance: response.data.total_claimablebalance,
        total_claimablebalance_users: response.data.total_claimablebalance_users
      };
    } else {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: response.data.message || "An unexpected error occurred.",
      });
    }
  } catch (error) {
    console.error("Error:", error);
    Swal.fire({
      icon: "error",
      title: "Error",
      text: error.response?.data?.message || "Failed to fetch wallet types. Please try again later.",
    });
  }
}

onMounted(() => {
  fetchdata()
})


</script>

<style lang="scss" scoped>
.responsive-container {
  /* Default styles for all screen sizes */
  max-width: 100%;
  margin-left: auto;
  margin-right: auto;
  padding: 2rem;
  /* Adjust padding as needed */

  /* Responsive adjustments */
  @media screen and (min-width: 640px) {
    padding: 4rem;
    /* Adjust padding for medium screens and above */
  }

  @media screen and (min-width: 1024px) {
    padding: 6rem;
    /* Adjust padding for large screens and above */
  }
}
</style>
