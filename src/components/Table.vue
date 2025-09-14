<template>
  <div class="table-wrapper">
    <div class="relative">
      
      <div class="max-w-6xl px-4 pt-8 pb-8 mx-auto mt-8 bg-white sm:px-6 sm:mt-16 lg:px-8 rounded-3xl sm:drop-shadow">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <div class="w-32 h-1 px-8 mx-auto rounded-full bg-gradient"></div>
            <h1 class="mt-4 font-semibold leading-relaxed text-center text-black text-t24 sm:leading-lh65">
            Latest Tokens Generated with TokenGlade
            </h1>
          </div>
     
        </div>
        <div class="flow-root mt-4">
          <div class="-mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle">
              <div class="table-container">
                <table class="min-w-full ">
                  <thead>
                    <tr class="bg-white divide-gray-200 sm:divide-x">
                      <th scope="col" class="pb-3.5 pl-4 text-left pr-4 text-[20px] font-semibold text-gray-900 sm:pl-6 lg:pl-20 ">Distributor Address</th>
                      <th scope="col" class="pb-3.5 pl-4 text-left pr-4 text-[20px] font-semibold text-gray-900 sm:pl-6 lg:pl-20 ">Issuer Address</th>
                      <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900">Symbol</th>
                      <th scope="col" class="pl-4 sm:pl-6 lg:pl-20 pb-3.5 pr-4 text-left text-[20px] font-semibold text-gray-900">Blockchain</th>
                    </tr>
                  </thead>
                  <tbody>
                      <tr v-for="token in fetched_tokens" :key="token.id" class="bg-white divide-gray-200 sm:divide-x">
                        <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap text-t16 sm:pl-6 lg:pl-20">
                          <template v-if="token.blockchain?.name === 'Stellar'">
                            <a
                              :href="`https://stellar.expert/explorer/public/account/${token.stellar_token?.user_wallet_address}`"
                              target="_blank"
                              class="text-blue-500 underline"
                            >
                              {{ formatAddress(token.stellar_token?.user_wallet_address) }}
                            </a>
                          </template>
                        </td>
                        <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap text-t16 sm:pl-6 lg:pl-20">
                          <template v-if="token.blockchain?.name === 'Stellar'">
                            <a :href="`https://stellar.expert/explorer/public/account/${token.stellar_token.issuer_public_key}`" target="_blank" class="text-blue-500 underline">
                              {{ formatAddress(token.stellar_token.issuer_public_key) }}
                            </a>
                          </template>
                        </td>
                        <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap sm:pl-6 lg:pl-20 text-t16">
                          <template v-if="token.blockchain?.name === 'Stellar'">
                            {{ token.stellar_token.asset_code }}
                          </template>
                        </td>
                        <td class="py-4 pl-4 pr-4 text-black whitespace-nowrap sm:pl-6 lg:pl-20 text-t16">{{ token.blockchain.name }}</td>
                      </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import axios from 'axios'
import { ref, computed, defineProps, onMounted, watch } from "vue";
import Swal from 'sweetalert2';

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const fetched_tokens = ref([]);

function formatAddress(address) {
    if (!address || typeof address !== 'string') return '—';
    if (address.length > 9) {
        return `${address.slice(0, 3)}...${address.slice(6, 9)}..${address.slice(-3)}`;
    }
    return address; // return as-is if the address is too short
}

async function fetchWallets() {
    try {
        const response = await axios.get('/api/global/generated_tokens', {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        if (response.data.status === "success") {
          fetched_tokens.value = response.data.tokens;
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
  fetchWallets()
})
</script>

<style lang="scss" scoped>
@media screen and (max-width: 600px) {
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch; /* For smoother scrolling on iOS */
    position: relative;
    z-index: 1; /* Keep it below the modal */
  }
  .table-wrapper {
    position: relative;
    z-index: 1; /* Lower than modal */
  }

  /* Adjust other styles as needed */
}
</style>