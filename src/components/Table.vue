<template>
  <div class="max-w-6xl mx-auto px-6 mt-24">

    <h2 class="text-3xl font-semibold text-center text-gray-900 mb-14">
      Early Tokens Minted on TokenGlade
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

      <div v-for="token in fetched_tokens" :key="token.id"
        class="relative bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition">

        <!-- Header -->
        <div class="flex items-center gap-3 mb-4">

          <!-- Smaller Logo -->
          <div
            class="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center overflow-hidden border border-gray-100">
            <img v-if="token.logo_url" :src="token.logo_url" class="w-full h-full object-contain p-1" />
            <span v-else class="text-xs font-semibold text-gray-600">
              {{ token.asset_code?.slice(0, 2).toUpperCase() }}
            </span>
          </div>

          <div class="min-w-0">

            <div class="flex items-center gap-1">
              <p class="text-base font-semibold text-gray-900 truncate">
                {{ token.name }}
              </p>

              <img v-if="Number(token.token_verify) === 1" :src="verified" alt="Verified" class="w-4 h-4 flex-shrink-0"
                title="Verified Token" />
            </div>

            <p class="text-xs text-gray-500">
              {{ token.asset_code?.toUpperCase() }}
            </p>
          </div>

        </div>

        <!-- Stats (More Compact) -->
        <div class="flex justify-between items-center text-sm text-gray-600 mb-4">

          <div>
            <p class="text-[11px] text-gray-400 uppercase tracking-wide">
              Supply
            </p>
            <p class="font-medium text-gray-800">
              {{ formatNumber(token.total_supply) }}
            </p>
          </div>

          <div class="text-right">
            <p class="text-[11px] text-gray-400 uppercase tracking-wide">
              Chain
            </p>
            <p class="font-medium text-gray-800">
              {{ token.blockchain.name }}
            </p>
          </div>

        </div>

        <!-- Slim Button -->
        <a v-if="token.tx_hash" :href="`https://stellar.expert/explorer/${explorerNetwork}/tx/${token.tx_hash}`"
          target="_blank"
          class="text-sm text-center py-2 rounded-lg bg-gradient-to-r from-cyan-500 to-purple-500 text-white hover:opacity-90 transition">
          View on Explorer →
        </a>

      </div>
    </div>

  </div>
</template>


<script setup>
import axios from 'axios'
import { ref, computed, defineProps, onMounted, watch } from "vue";
import Swal from 'sweetalert2';
import verified from "@/assets/verify.png";

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const props = defineProps({
  network: { type: String, default: 'public' }
})

const fetched_tokens = ref([]);

const explorerNetwork = computed(() =>
  (props.network || 'public').toLowerCase() === 'testnet' ? 'testnet' : 'public'
)


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

function formatNumber(value) {
  if (!value) return '—'
  return new Intl.NumberFormat().format(value)
}
</script>

<style lang="scss" scoped>
@media screen and (max-width: 600px) {
  .table-container {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    /* For smoother scrolling on iOS */
    position: relative;
    z-index: 1;
    /* Keep it below the modal */
  }

  .table-wrapper {
    position: relative;
    z-index: 1;
    /* Lower than modal */
  }

  /* Adjust other styles as needed */
}
</style>
