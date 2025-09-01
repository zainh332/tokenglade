<template>
    <div>
        <Header />
        <div
            class="container-fluid mx-auto pt-[8rem] pb-[6rem] relative top-0 z-0  bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove">

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
                    <form class="flex flex-col gap-4 p-6">
                        <div>
                            <label for="current_balance" class="block text-sm font-medium text-gray-700">Current
                                balance</label>
                            <input type="text" id="current_balance" name="current_balance"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                placeholder="Current balance" required>
                        </div>
                        <div>
                            <label for="min_token" class="block text-sm font-medium text-gray-700">Min $TKG Token</label>
                            <input type="text" id="min_token" name="min_token"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                placeholder="50" required>
                        </div>
                        <div>
                            <label for="max_token" class="block text-sm font-medium text-gray-700">Max $TKG Token</label>
                            <input type="text" id="max_token" name="max_token"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                placeholder="200" required>
                        </div>
                        <div class="relative w-full">
                            <label for="range_value" class="block text-sm font-medium text-gray-700 mb-2">
                                Select Range
                            </label>

                            <!-- Tooltip -->
                            <div class="absolute -top-[0] transform -translate-x-1/2 text-xs bg-[#43CDFF] text-white px-2 py-1 rounded-[5px] transition-all duration-200"
                                :style="{ left: `calc(${percentage}% - 1px)` }">
                                {{ rangeValue }}%
                            </div>

                            <!-- Range Input -->
                            <input type="range" id="range_value" name="range_value" min="0" max="100"
                                v-model="rangeValue" class="w-full h-2 rounded-lg appearance-none cursor-pointer"
                                :style="{
                                    background:
                                        'linear-gradient(90deg, rgba(220,25,224,1), rgba(67,205,255,1), rgba(0,254,254,1))'
                                }" />
                        </div>
                        <!-- <div>
                            <label for="after_days" class="block text-sm font-medium text-gray-700">After 30
                                Days</label>
                            <input type="text" id="after_days" name="after_days"
                                class="mt-1 w-full p-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#3A3A3A]"
                                placeholder="After 30 Days" required>
                        </div> -->
                        <button type="submit"
                            class="w-full text-white py-2 rounded-[20px] hover:opacity-90 transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove">
                            Submit
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="container mx-auto pt-20">
            <div class="mx-auto">
                <div class="table-section pb-10">
                    <h1>
                        Latest Staking Reward <br> Transactions
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
                                <td class="py-4 px-4">
                                    <td class="py-4 px-4 text-dark text-center">{{ row.wallet_address }}</td>
                                </td>
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
                                Don’t just hold — make your tokens work. With TokenGlade’s staking module, earn <strong>daily passive rewards</strong> and climb tiers for even higher APY.  
                                Up to <strong>18% annually</strong>, designed to reward conviction and long-term believers.  
                                Backed by a <strong>35M $TKG reserve</strong>, staking is built to be sustainable and rewarding for years to come.
                            </p>
                            <!-- <button class="text-dark p-2 rounded-[20px] bg-white border border-[#43CDFF]">
                                APY <span class="font-bold">Up to 18%</span>
                            </button> -->
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
                                Powered by the <strong>Stellar Blockchain</strong>, TokenGlade delivers lightning-fast transactions and near-zero fees.  
                                From staking to transfers, every action is seamless, secure, and efficient.  
                                Stellar’s proven infrastructure makes TokenGlade a <strong>future-ready platform</strong> built to scale with global demand. 🚀✨
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

// ---------------------
// Range Slider State
// ---------------------
const rangeValue = ref(50); // Default slider value
const min = 0;
const max = 100;

// Tooltip percentage for slider
const percentage = computed(() => {
    return ((rangeValue.value - min) / (max - min)) * 100;
});

// ---------------------
// Table & Pagination
// ---------------------
const tableData = ref([]); // Table data
const currentPage = ref(1);
const itemsPerPage = 5;

// Computed properties for pagination
const totalPages = computed(() => Math.ceil(tableData.value.length / itemsPerPage));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, tableData.value.length));
const paginatedData = computed(() => tableData.value.slice(startIndex.value, endIndex.value));

// Pagination functions
function nextPage() {
    if (currentPage.value < totalPages.value) currentPage.value++;
}

function prevPage() {
    if (currentPage.value > 1) currentPage.value--;
}

// ---------------------
// Initialize Data
// ---------------------
onMounted(() => {
    // Example table data
    tableData.value = [
        { wallet_address: "GCJW......JSH", reward: "100 TKG", transaction: "Link" },
        { wallet_address: "GIJW......KKQ", reward: "120 TKG", transaction: "Link" },
        { wallet_address: "GAPK......SKSI", reward: "320 TKG", transaction: "Link"  },
        { wallet_address: "GAAS......VEYP", reward: "50 TKG", transaction:  "Link"  },
        { wallet_address: "GLOK......UUYD", reward: "543 TKG", transaction:  "Link"  },
    ];

    // Initialize slider value (optional)
    rangeValue.value = 50;
});
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
