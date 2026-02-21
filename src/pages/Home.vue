<template>
  <div>
    <Header @wallet-status="handleWalletStatus" />
    <div class="container mx-auto pt-[8rem] pb-10 relative top-0 z-0">
      <div class="flex flex-col lg:flex-row items-center justify-between gap-12">
        <!-- Text Content -->
        <div class="text-center mx-auto lg:text-left max-w-3xl">
          <h1 class="text-[32px] sm:text-[48px] lg:text-[64px] font-semibold leading-tight text-dark">
            Launch Your Token
            <span class="block">
              Like a
              <span
                class="bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-clip-text text-transparent bg-[length:200%_200%] animate-gradientMove">
                Real Builder
              </span>
            </span>
          </h1>
          <p class="text-[18px] sm:text-[20px] mt-4 text-dark max-w-xl mx-auto lg:mx-0">
            Launch on Stellar today. XRP Ledger support coming next.
          </p>
          <div class="py-5">
            <button type="button" id="mintToken"
              class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient"
              @click="isWalletConnected ? openTokenModal() : openConnectWalletModal()">
              {{ isWalletConnected ? 'Launch Token' : 'Start Minting Now' }}
            </button>

            <div class="lg:hidden">
              <div class="pt-6 space-y-1">
                <a href="https://lobstr.co/trade/TKG:GAM3PID2IOBTNCBMJXHIAS4EO3GQXAGRX4UB6HTQY2DUOVL3AQRB4UKQ"
                  target="_blank" rel="noopener noreferrer"
                  class="text-xs text-white rounded-full btn-padding sm:text-t14 bg-gradient">
                  Buy TKG Tokens
                </a>
              </div>
            </div>
          </div>
        </div>

        <!-- Image -->
        <div class="hidden lg:block flex-shrink-0 animate-float">
          <img class="w-[280px] h-[340px] xl:w-[420px] object-contain" :src="logo" alt="TokenGlade Logo" />
        </div>
      </div>
    </div>
    <!-- Table -->

    <!-- Why TokenGlade -->
    <div class="py-14 bg-white">
      <div class="max-w-6xl mx-auto px-6">

        <h2 class="text-3xl font-semibold text-center text-gray-900 mb-12">
          Why TokenGlade
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

          <!-- Card 1 -->
          <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100">
            <div class="text-3xl mb-3">⚡</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              Mint in under 60 seconds
            </h3>
            <p class="text-dark text-sm">
              Launch your token quickly without complex setup or coding.
            </p>
          </div>

          <!-- Card 2 -->
          <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100">
            <div class="text-3xl mb-3">🔐</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              Your wallet, your control
            </h3>
            <p class="text-dark text-sm">
              Fully non-custodial. TokenGlade never holds your funds.
            </p>
          </div>

          <!-- Card 3 -->
          <div class="bg-gray-50 rounded-xl p-6 text-center border border-gray-100">
            <div class="text-3xl mb-3">🌍</div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">
              Multi-chain ready
            </h3>
            <p class="text-dark text-sm">
              Launching on Stellar first. XRP Ledger support coming next.
            </p>
          </div>

        </div>

      </div>
    </div>

    <Table :network="network" />

    <div class="py-10 my-10 bg-white text-center">
      <h2 class="text-3xl font-semibold text-center text-gray-900 mb-14">How It works</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-10 max-w-6xl mx-auto">
        <!-- Step 1 -->
        <div class="flex flex-col items-center">
          <div class="bg-blue-100 p-4 rounded-full mb-4 animate-pulseSoft">
            <img :src="Phone" alt="Choose Blockchain" class="w-12 h-12" />
          </div>
          <h3 class="text-xl font-semibold mb-2">Choose Your Blockchain</h3>
          <p class="text-dark text-sm">
            Currently, TokenGlade supports the Stellar blockchain. More chains coming soon.
          </p>
        </div>

        <!-- Step 2 -->
        <div class="flex flex-col items-center">
          <div class="bg-green-100 p-4 rounded-full mb-4 animate-pulseSoft">
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
          <div class="bg-yellow-100 p-4 rounded-full mb-4 animate-pulseSoft">
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

    <!-- Perfect For Section -->
    <div class="py-10 my-10 text-center">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="text-3xl font-semibold text-center text-gray-900 mb-14">Perfect For:</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-8">

          <!-- Item -->
          <div class="flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-3">
              <img :src="community" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">Community Tokens</h3>
            <p class="text-dark text-sm">Opportunities for communities</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center mb-3">
              <img :src="startups" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">Startup Utility Tokens</h3>
            <p class="text-dark text-sm">Bootstrap early ecosystems</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-yellow-50 flex items-center justify-center mb-3">
              <img :src="reward" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">Loyalty & Rewards</h3>
            <p class="text-dark text-sm">Reward users instantly</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-cyan-50 flex items-center justify-center mb-3">
              <img :src="DaoIcon" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">DAO Experiments</h3>
            <p class="text-dark text-sm">Test governance ideas</p>
          </div>

          <div class="flex flex-col items-center">
            <div class="w-16 h-16 rounded-full bg-purple-50 flex items-center justify-center mb-3">
              <img :src="PrototypeIcon" class="w-12 h-12" />
            </div>
            <h3 class="text-xl font-semibold mb-2">Testing & Prototypes</h3>
            <p class="text-dark text-sm">Launch fast, iterate faster</p>
          </div>

        </div>
      </div>
    </div>

    <!-- Ripple Coming Soon Section -->
    <div class="py-16 bg-white text-center">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        <h2 class="text-3xl sm:text-4xl font-semibold text-dark mb-4">
          <span
            class="bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-clip-text text-transparent bg-[length:200%_200%] animate-gradientMove">Ripple</span>
          Minting Coming Soon
        </h2>

        <p class="text-[20px] font-normal leading-7">
          TokenGlade is expanding to XRP Ledger. Be among the first to mint on Stellar.
        </p>

      </div>
    </div>

    <!-- <Newsletter /> -->
    
    <div class="max-w-6xl gap-12 px-4 py-10 mx-auto cards sm:pt-24 sm:px-6 lg:px-8">
      <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
          <div class="w-32 h-1 px-8 mx-auto rounded-full bg-gradient"></div>
          <h1 class="mt-2 font-semibold leading-relaxed text-center text-black text-t34 sm:leading-lh65">
            Frequent questions
          </h1>
        </div>
      </div>
      <Faq />
    </div>
    
    <div class="py-24 bg-white text-center">
      <div class="max-w-4xl mx-auto px-6">

        <h2 class="text-4xl sm:text-5xl font-semibold text-gray-900 mb-4">
          Ready to launch your token?
        </h2>

        <p class="text-dark-600 text-lg mb-6">
          Mint instantly with full on-chain control
        </p>

        <!-- Trust strip -->
        <p class="text-sm text-dark-500 mb-8">
          Non-custodial • Fully on-chain • Built on Stellar (XRP Ledger coming next)
        </p>


        <button type="button" id="mintToken"
          class="text-white rounded-full px-8 py-3 bg-gradient hover:opacity-90 transition"
          @click="isWalletConnected ? openTokenModal() : openConnectWalletModal()">
          {{ isWalletConnected ? 'Launch Token' : 'Start Minting Now' }}
        </button>

      </div>
    </div>
    <GenerateTokenModal :open="isTokenModalOpen" @close="isTokenModalOpen = false" :network="network" />
    <ConnectWalletModal v-model="ConnectWalletModals" :connected="isWalletConnected" :walletKey="walletKey"
      @status="handleWalletStatus" @close="ConnectWalletModals = false" />
    
    <Footer />
  </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Table from "@/components/Table.vue"; // Stellar Tokens Generated
import Card from "@/components/Card.vue";
import logo from "@/assets/Xl-logo.png";
import community from "@/assets/community.png";
import startups from "@/assets/startups.png";
import reward from "@/assets/rewards.png";
import DaoIcon from "@/assets/dao.png";
import PrototypeIcon from "@/assets/prototype.png";
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
import ConnectWalletModal from '@/components/ConnectWallet.vue';
import { getCookie, getNetwork } from "../utils/utils.js";

const isWalletConnected = ref(false);
const walletKey = ref('');
const ConnectWalletModals = ref(false);
const network = ref('public')

function handleWalletStatus(e) {
  // Expect { connected: boolean, walletKey?: string }
  if (e && typeof e.connected === "boolean") {
    isWalletConnected.value = e.connected;
    if (e.walletKey) walletKey.value = e.walletKey;
    // If connected from modal, you can auto-close:
    if (e.connected) ConnectWalletModals.value = false;
  }
  // Always re-sync from storage/cookie just in case
  refreshWalletState();
}

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const isTokenModalOpen = ref(false)
const openTokenModal = () => { isTokenModalOpen.value = true }
const openConnectWalletModal = () => { ConnectWalletModals.value = true }

const data = ref({
  total_tokens: 0,
});

async function fetchdata() {
  try {
    const response = await axios.get('/api/global/count_data', {
      headers: {
        'X-CSRF-TOKEN': csrfToken
      }
    });
    if (response.data.status === "success") {
      data.value = {
        total_tokens: response.data.total_tokens,
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

function readPk() {
  const pk = getCookie("public_key") || localStorage.getItem("public_key");
  if (!pk) return "";
  const s = String(pk).trim();
  return (s === "null" || s === "undefined") ? "" : s;
}
function refreshWalletState() {
  const pk = readPk();
  walletKey.value = pk || "";
  isWalletConnected.value = !!(pk && pk.startsWith("G") && pk.length === 56);
}

onMounted(async () => {
  refreshWalletState();
  fetchdata()
  try {
    network.value = await getNetwork()
  } catch (e) {
    console.error('getNetwork failed:', e)
  }
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
