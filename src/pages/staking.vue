<template>
    <div>
        <Header />
        <div
            class="container-fluid mx-auto pt-[8rem] pb-[6rem] relative top-0 z-0 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove">
            <div class="flex flex-col lg:flex-row items-center justify-center gap-12">
                <!-- Text Content -->
                <div class="text-center lg:text-left max-w-3xl">
                    <h1 class="text-[32px] sm:text-[48px] lg:text-[64px] font-normal leading-tight text-white">
                        Stake Your
                        <span class="block font-semibold">
                            $TKG, Earn More
                        </span>
                    </h1>
                    <p class="text-[18px] sm:text-[20px] mt-4 text-white max-w-xl mx-auto lg:mx-0">
                        Put your tokens to work with TokenGlade’s staking module.
                        Secure, seamless, and built for long-term rewards — up to 18% APY.
                        The stronger you hold, the more you earn
                    </p>
                </div>

                <!-- Form -->
                <div class="flex-shrink-0 w-full max-w-md lg:max-w-lg bg-white rounded-[25px] shadow-lg">
                    <div class="bg-[#3A3A3A] text-white text-center py-5 rounded-t-[25px]">
                        <h2 class="card-header">
                            Stake with <span>TokenGlade</span>
                        </h2>
                    </div>

                    <form class="flex flex-col gap-4 p-6" @submit.prevent="onSubmit">
                        <!-- Current Balance (always visible) -->
                        <div>
                            <label for="current_balance" class="block text-sm font-medium text-gray-700">
                                Current balance
                            </label>

                            <!-- Input with skeleton while loading -->
                            <div class="mt-1 relative">
                                <input v-if="!loadingBalance" type="text" id="current_balance" name="current_balance"
                                    :value="tkgBalance"
                                    class="w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                    placeholder="Current balance" readonly required />
                                <div v-else class="w-full h-10 rounded-md relative overflow-hidden bg-gray-200"
                                    aria-busy="true">
                                    <div
                                        class="absolute inset-0 animate-pulse bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200">
                                    </div>
                                </div>
                            </div>
                            <p v-if="loadingBalance" class="mt-2 text-xs text-gray-500">Fetching your $TKG balance…</p>
                        </div>

                        <!-- If balance >= 1500: show staking fields -->
                        <template v-if="!loadingBalance && hasMinBalance">
                            <!-- Range -->
                            <div class="relative w-full">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="range_value" class="block text-sm font-medium text-gray-700">
                                        Select range
                                    </label>
                                    <span class="text-xs text-gray-500">
                                        Min: <strong>1,500</strong> • Max: <strong>{{ formattedMaxStake }}</strong>
                                    </span>
                                </div>

                                <!-- Range Input (0 → 100) -->
                                <input type="range" id="range_value" name="range_value" min="0" max="100"
                                    v-model="rangeValue" class="w-full h-2 rounded-lg appearance-none cursor-pointer"
                                    :style="{
                                        background:
                                            'linear-gradient(90deg, rgba(220,25,224,1), rgba(67,205,255,1), rgba(0,254,254,1))'
                                    }" />

                                <!-- Scale helper -->
                                <div class="flex justify-between text-[11px] text-gray-500 mt-1">
                                    <span>0%</span>
                                    <span>100%</span>
                                </div>
                            </div>

                            <!-- Selected stake amount -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">
                                    Stake amount
                                </label>
                                <input type="text" :value="selectedTokensFormatted"
                                    class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                    readonly />
                            </div>

                            <button type="submit"
                                class="w-full text-white py-2 rounded-[20px] hover:opacity-90 transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove"
                                :disabled="stakeLoading || !hasMinBalance">
                                <span v-if="!stakeLoading">Stake</span>
                                <span v-else>Staking…</span>
                            </button>
                        </template>

                        <!-- If balance < 1500: show notice + Lobstr link -->
                        <template v-else-if="!loadingBalance">
                            <div class="rounded-xl border border-amber-300 bg-amber-50 p-4">
                                <p class="text-sm text-amber-800">
                                    Your $TKG balance is less than <strong>1,500</strong>. You need at least 1,500 $TKG
                                    to start staking.
                                </p>
                                <a href="https://lobstr.co/" target="_blank" rel="noopener"
                                    class="mt-3 inline-block underline text-blue-600 hover:text-blue-800">
                                    Buy $TKG on LOBSTR
                                </a>
                            </div>
                        </template>
                    </form>
                </div>
            </div>
        </div>

        <div class="container mx-auto pt-20">
            <div class="mx-auto">
                <div class="table-section pb-10">
                    <h1>
                        Latest Staking Reward <br /> Transactions
                    </h1>
                </div>

                <div class="w-full max-w-[80%] mx-auto bg-white rounded-xl shadow-md overflow-hidden">
                    <!-- Table -->
                    <table class="min-w-full border-collapse">
                        <thead class="bg-[#43CDFF] text-white">
                            <tr>
                                <th class="py-4 px-4 text-center">Wallet Address</th>
                                <th class="py-4 px-4 text-center">Reward</th>
                                <th class="py-4 px-4 text-center">Transactions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in paginatedData" :key="index"
                                class="bg-white border-b border-[#EBEBEB]">
                                <td class="py-4 px-4 text-dark text-center">{{ row.wallet_address }}</td>
                                <td class="py-4 px-4">
                                    <span
                                        class="inline-block w-full px-4 py-1 text-center text-sm font-medium text-dark bg-[#DBFEF0] rounded-full">
                                        {{ row.reward }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-dark text-center">{{ row.transaction }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="flex justify-between items-center px-4 py-3">
                        <div class="text-sm text-gray-600">
                            Showing {{ startIndex + 1 }}–{{ endIndex }} of {{ tableData.length }}
                        </div>
                        <div class="flex gap-2">
                            <button @click="prevPage" :disabled="currentPage === 1"
                                class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50">
                                Prev
                            </button>
                            <button @click="nextPage" :disabled="currentPage === totalPages"
                                class="px-3 py-1 text-sm border rounded-lg disabled:opacity-50">
                                Next
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-20 pt-20 w-full max-w-[80%] mx-auto">
                    <div class="flex flex-col md:flex-row justify-center gap-10 md:gap-20">
                        <!-- Text Column -->
                        <div class="md:w-1/2 p-4 text-center md:text-left">
                            <h1
                                class="text-[32px] sm:text-[48px] lg:text-[64px] font-normal leading-tight text-dark mb-3">
                                <span class="block text-[36px] font-semibold bg-dark leading-[1.4]">
                                    Stake $TKG, Watch Your Rewards Multiply
                                </span>
                            </h1>
                            <p class="text-[18px] sm:text-[20px] mt-4 text-dark max-w-xl mx-auto lg:mx-0 leading-[1.7]">
                                Don’t just hold — make your tokens work. With TokenGlade’s staking module, earn
                                <strong>daily passive rewards</strong> and climb tiers for even higher APY.
                                Up to <strong>18% annually</strong>, designed to reward conviction and long-term
                                believers.
                                Backed by a <strong>35M $TKG reserve</strong>, staking is built to be sustainable and
                                rewarding for years to come.
                            </p>
                        </div>

                        <!-- Image Column -->
                        <div class="md:w-1/2 p-4 flex flex-col items-center md:items-end justify-center gap-6">
                            <img class="w-full max-w-md h-auto" :src="graph1" alt="graph-1" />
                            <img class="w-[220px] h-auto relative top-[-10rem] right-[20rem] animate-float"
                                :src="graphCard1" alt="graph-card-1" />
                        </div>
                    </div>
                </div>

                <div class="w-full max-w-[80%] mx-auto">
                    <div class="flex flex-col md:flex-row justify-center gap-10 md:gap-20">
                        <!-- Image Column -->
                        <div class="md:w-1/2 p-4 flex flex-col items-center md:items-start justify-start gap-6">
                            <img class="w-full max-w-md h-auto" :src="graph2" alt="graph-1" />
                            <img class="w-[90rem] h-auto relative top-[-15rem] right-[4rem] animate-float"
                                :src="graphCard2" alt="graph-card-1" />
                        </div>

                        <!-- Text Column -->
                        <div class="md:w-1/2 p-4 text-center md:text-left">
                            <h1
                                class="text-[32px] sm:text-[48px] lg:text-[64px] font-normal leading-tight text-dark mb-3">
                                <span class="block text-[36px] font-semibold bg-dark leading-[1.4]">
                                    TokenGlade — Built for Speed & Reliability
                                </span>
                            </h1>
                            <p class="text-[18px] sm:text-[20px] mt-4 text-dark max-w-xl mx-auto lg:mx-0 leading-[1.7]">
                                Powered by the <strong>Stellar Blockchain</strong>, TokenGlade delivers lightning-fast
                                transactions and near-zero fees.
                                From staking to transfers, every action is seamless, secure, and efficient.
                                Stellar’s proven infrastructure makes TokenGlade a <strong>future-ready
                                    platform</strong> built
                                to scale with global demand. 🚀✨
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Footer />
    </div>
</template>

<script setup>
import Header from "@/components/Header.vue";
import Footer from "@/components/Footer.vue";
import graph1 from '@/assets/graph-1.png';
import graphCard1 from '@/assets/graph-1-card.png';
import graph2 from '@/assets/graph-2.png';
import graphCard2 from '@/assets/graph-2-card.png';
import { ref, computed, onMounted } from "vue";
import { checkTkgBalance, getCookie, updateLoader, apiHeaders } from "../utils/utils.js";
import Swal from "sweetalert2";
import axios from "axios";
import { signTransaction } from "@stellar/freighter-api";

// ---------------------
// Slider state (0 → 100)
// ---------------------
const rangeValue = ref(0); // start at 0%

// ---------------------
// TKG Balance & staking bounds
// ---------------------
const tkgBalance = ref(0);
const loadingBalance = ref(true);
const publicKey = getCookie("public_key");
const hasMinBalance = computed(() => Number(tkgBalance.value) >= 1500);
const stakeLoading = ref(false);

// Staking min/max amounts
const stakeMin = 1500;
const stakeMax = computed(() => Number(tkgBalance.value));

// Map slider percent → token amount [1500 → tkgBalance]
const selectedTokens = computed(() => {
    const max = Math.max(stakeMin, stakeMax.value || 0);
    const fraction = Number(rangeValue.value) / 100; // 0..1
    const mapped = Math.round(stakeMin + (max - stakeMin) * fraction);
    return isNaN(mapped) ? stakeMin : mapped;
});

// Formatting helpers
const formattedMaxStake = computed(() => Number(stakeMax.value || 0).toLocaleString());
const selectedTokensFormatted = computed(() => `${selectedTokens.value.toLocaleString()} TKG`);

// ---------------------
// Table & Pagination
// ---------------------
const tableData = ref([]);
const currentPage = ref(1);
const itemsPerPage = 5;

const totalPages = computed(() => Math.ceil(tableData.value.length / itemsPerPage));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, tableData.value.length));
const paginatedData = computed(() => tableData.value.slice(startIndex.value, endIndex.value));

function nextPage() {
    if (currentPage.value < totalPages.value) currentPage.value++;
}
function prevPage() {
    if (currentPage.value > 1) currentPage.value--;
}

// ---------------------
// Initialize
// ---------------------
onMounted(async () => {
    tableData.value = [
        { wallet_address: "GCJW......JSH", reward: "100 TKG", transaction: "Link" },
        { wallet_address: "GIJW......KKQ", reward: "120 TKG", transaction: "Link" },
        { wallet_address: "GAPK......SKSI", reward: "320 TKG", transaction: "Link" },
        { wallet_address: "GAAS......VEYP", reward: "50 TKG", transaction: "Link" },
        { wallet_address: "GLOK......UUYD", reward: "543 TKG", transaction: "Link" },
    ];

    try {
        const pk = publicKey;
        if (pk) {
            const bal = await checkTkgBalance(pk);
            tkgBalance.value = Number(bal) || 0;
        }
    } catch (e) {
        tkgBalance.value = 0;
    } finally {
        loadingBalance.value = false;
    }
});

async function onSubmit() {
  const network = (import.meta.env.VITE_STELLAR_ENVIRONMENT || "public").toLowerCase();
  const isTestnet = network === "testnet";
  const stakingAssetId = isTestnet ? 2 : 1;
  if (!hasMinBalance.value) {
    Swal.fire({ icon: "warning", title: "Insufficient Balance", text: "You need at least 1,500 TKG to stake." });
    return;
  }

  const amount = Number(selectedTokens.value);
  const max = Number(stakeMax.value || 0);

  if (isNaN(amount) || amount < stakeMin || amount > max) {
    Swal.fire({
      icon: "error",
      title: "Invalid Amount",
      text: `Stake amount must be between 1,500 and ${max.toLocaleString()} TKG.`,
    });
    return;
  }

  const pk = publicKey || localStorage.getItem("public_key");
  const bearer = localStorage.getItem("token");

  if (!pk || !bearer) {
    Swal.fire({ icon: "info", title: "Connect Wallet", text: "Please connect your wallet and sign in before staking." });
    return;
  }

  stakeLoading.value = true;

  try {
    updateLoader("Preparing Staking", "Creating staking payment XDR…");

    const res = await axios.post(
      "/api/staking/start",
            {
                public_key: pk,
                amount,
                staking_asset_id: stakingAssetId,
            },
      {
        headers: apiHeaders(),
        withCredentials: true,
      }
    );

    if (res.data.status === "success") {
      
      updateLoader(
        "Sign in Wallet",
        `Please approve the staking transaction for <b>${amount.toLocaleString()}</b> TKG…`
      );
      
      signXdr(res.data.xdr, res.data.staking_id, isTestnet);
    } else {
      const msg = res?.data?.message || res?.data?.error || "Failed to stake tokens.";
      Swal.close();
      Swal.fire({ icon: "error", title: "Error!", text: msg });
    }
  } catch (error) {
    const msg =
      error?.response?.data?.message ||
      (error?.response?.status === 422
        ? "Validation error. Please check your input."
        : "An error occurred while staking tokens.");
    Swal.close();
    Swal.fire({ icon: "error", title: "Error!", text: msg });
    console.error("Error staking tokens:", error);
  } finally {
    stakeLoading.value = false;
  }
}

async function signXdr(xdr, staking_id, testnet) {
    let active = localStorage.getItem("wallet_key");

    switch (active) {
        case "rabet":
            rabet
                .sign(xdr, testnet ? "testnet" : "mainnet")
                .then(function (result) {
                    const xdr = result.xdr;
                    submitStakingXdr(xdr, staking_id);
                })
                .catch(function (error) {
                    // toastr.error("Rejected!", "Rabbet Wallet");
                });
            break;

        case "freighter":
            await signTransaction(xdr, testnet ? "TESTNET" : "PUBLIC")
                .then(function (result) {
                    const xdr = result;
                    submitStakingXdr(xdr, staking_id);
                })
                .catch(function (error) {
                    // toastr.error("Rejected!", "Freighter Wallet");
                });
            break;

        case "albeto":
            albedo
                .tx({
                    xdr: xdr,
                    network: "public",
                })
                .then(function (result) {
                    const xdr = result.signed_envelope_xdr;
                    if (!xdr) {
                        console.error("Albedo did not return a signed XDR");
                        return;
                    }
                    submitStakingXdr(xdr, staking_id);
                })
                .catch(function (error) {
                    console.error("Albedo Wallet Error:", error);
                    // toastr.error("Rejected!", "Albeto Wallet");
                });

            break;
        case "xbull":
            xBullSDK
                .signXDR(xdr)
                .then(function (result) {
                    const xdr = result;
                    submitStakingXdr(xdr, staking_id);
                })
                .catch(function (error) {
                    // toastr.error("Rejected!", "xBull Wallet");
                });
            break;
        default:
            throw new Error("No wallet selected. Connect a wallet first.");
    }
}

async function submitStakingXdr(signedXdr, staking_id) {
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  // Optional: update the loader text before submitting
  if (typeof updateLoader === "function") {
    updateLoader("Submitting", "Broadcasting signed transaction to Stellar…");
  }

  try {
    const { data } = await axios.post(
      '/api/staking/submit-xdr',
      { signedXdr, staking_id },
      {
        headers: apiHeaders(),
        withCredentials: true,
      }
    );

    if (data?.status === "success" || data?.status === 1) {
      if (typeof updateLoader === "function") {
        updateLoader("Finalizing", "Saving staking record…");
      }
      // Close loader and show success (or just reload)
      if (Swal.isVisible()) Swal.close();

      // Optional success toast/modal here
      Swal.fire({ icon: 'success', title: 'Staked!', text: 'Your TKG were staked successfully.' });

      setTimeout(() => location.reload(), 800);
    } else {
      if (Swal.isVisible()) Swal.close();
      Swal.fire({
        icon: 'error',
        title: 'Staking failed',
        text: data?.message || data?.error || 'Something went wrong while submitting your transaction.',
      });
    }
  } catch (err) {
    if (Swal.isVisible()) Swal.close();
    Swal.fire({
      icon: 'error',
      title: 'Network error',
      text: err?.response?.data?.message || err?.message || 'Failed to submit the transaction.',
    });
    console.error('submitStakingXdr error:', err);
  }
}
</script>

<style lang="scss" scoped>
.responsive-container {
    max-width: 100%;
    margin-left: auto;
    margin-right: auto;
    padding: 2rem;

    @media screen and (min-width: 640px) {
        padding: 4rem;
    }

    @media screen and (min-width: 1024px) {
        padding: 6rem;
    }
}

.card-header {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-style: normal;
    font-size: 30px;
    line-height: 100%;
    text-align: center;
}

.card-header span {
    color: #43CDFF;
}

.table-section {
    font-family: 'DM Sans', sans-serif;
    font-weight: 700;
    font-style: Bold;
    font-size: 35px;
    line-height: 50px;
    text-align: center;
    text-transform: capitalize;
}
</style>
