<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm transition-all duration-300">
    <!-- Modal Card -->
    <div class="relative w-full max-w-xl bg-[#0f172a]/95 border border-slate-800 rounded-3xl overflow-hidden shadow-2xl flex flex-col text-left">
      
      <!-- Gradient Decorative Header line -->
      <div class="h-1.5 w-full bg-gradient-to-r from-purple-500 via-cyan-500 to-emerald-500"></div>

      <!-- Modal Header -->
      <div class="p-5 pb-3 border-b border-slate-850 flex items-center justify-between">
        <h3 class="text-base font-bold text-white font-display">Share Token</h3>
        <button 
          @click="closeModal"
          class="p-1.5 rounded-xl bg-slate-900/60 hover:bg-slate-900 border border-slate-800 text-slate-400 hover:text-white transition"
        >
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Modal Body -->
      <div class="p-5 space-y-5">
        
        <!-- Premium Stats Showcase Display -->
        <div class="bg-slate-950/40 border border-slate-850 rounded-2xl p-5 space-y-4">
          <!-- Token Header inside Stats Display -->
          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-white font-bold select-none overflow-hidden flex-shrink-0">
              <img v-if="token.image" :src="token.image" class="w-full h-full object-cover" />
              <span v-else class="text-sm font-mono">{{ token.asset_code?.substring(0, 2).toUpperCase() }}</span>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="text-sm font-bold text-white uppercase">{{ token.asset_code }}</span>
                <span v-if="token.token_verify === 1 || token.is_verified" class="text-[10px] font-bold text-emerald-400 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">
                  ✓ Verified
                </span>
              </div>
              <span class="text-xs text-slate-500 font-medium block truncate max-w-[280px]">{{ token.name || token.project?.org_name || 'Stellar Project' }}</span>
            </div>
          </div>

          <!-- Stats Grid -->
          <div class="grid grid-cols-2 gap-x-6 gap-y-4 pt-2 border-t border-slate-900">
            <!-- 1. Price USD -->
            <div class="space-y-0.5">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Price (USD)</span>
              <span class="text-sm font-mono font-bold text-white block">≈ ${{ formatPrice(usdPrice) }}</span>
            </div>
            <!-- 2. Price XLM -->
            <div class="space-y-0.5">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Price (XLM)</span>
              <span class="text-sm font-mono font-bold text-cyan-400 block">{{ formatXlmPrice(xlmPrice) }} XLM</span>
            </div>
            <!-- 3. 24H Change -->
            <div class="space-y-0.5">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">24H Change</span>
              <span class="text-sm font-mono font-bold block" :class="priceChange >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                {{ priceChange >= 0 ? '▲ +' : '▼ ' }}{{ priceChange }}%
              </span>
            </div>
            <!-- 4. Liquidity -->
            <div class="space-y-0.5">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Liquidity</span>
              <span class="text-sm font-mono font-bold text-white block">${{ formatNumber(liquidity) }}</span>
            </div>
            <!-- 5. Holders -->
            <div class="space-y-0.5">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Holders</span>
              <span class="text-sm font-mono font-bold text-white block">{{ formatNumber(holders) }}</span>
            </div>
            <!-- 6. Trust Score -->
            <div class="space-y-0.5">
              <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wide">Trust Score</span>
              <span 
                class="text-sm font-mono font-bold block"
                :class="(token.rating?.average ?? 7.5) >= 8 ? 'text-emerald-400' : ((token.rating?.average ?? 7.5) >= 5 ? 'text-amber-400' : 'text-rose-400')"
              >
                {{ (token.rating?.average ?? 7.5).toFixed(1) }} / 10
              </span>
            </div>
          </div>
        </div>

        <!-- Sharing Row / Grid -->
        <div class="space-y-2">
          <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider">Share to Channels</label>
          <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
            <!-- X (Twitter) -->
            <a 
              :href="xShareUrl" 
              target="_blank" 
              class="px-3 py-3 bg-black hover:bg-slate-900 border border-slate-850 hover:border-slate-800 rounded-xl flex items-center justify-center gap-1.5 text-xs font-bold text-white transition"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
              </svg>
              <span>X</span>
            </a>

            <!-- Facebook -->
            <a 
              :href="fbShareUrl" 
              target="_blank" 
              class="px-3 py-3 bg-[#1877F2] hover:bg-[#166FE5] rounded-xl flex items-center justify-center gap-1.5 text-xs font-bold text-white transition"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M9.101 23.656V12.234H4.897V7.618h4.204V4.228c0-4.171 2.548-6.443 6.268-6.443 1.782 0 3.315.133 3.762.192v4.36h-2.58c-2.023 0-2.415.961-2.415 2.372v3.109h4.828l-.628 4.616h-4.2V23.656H9.101z" />
              </svg>
              <span>Facebook</span>
            </a>

            <!-- Reddit -->
            <a 
              :href="redditShareUrl" 
              target="_blank" 
              class="px-3 py-3 bg-[#FF4500] hover:bg-[#E03D00] rounded-xl flex items-center justify-center gap-1.5 text-xs font-bold text-white transition"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M24 11.5c0-1.65-1.35-3-3-3-.96 0-1.86.48-2.42 1.24-1.64-1-3.85-1.64-6.23-1.72l1.41-4.48 4.61 1c.07.87.81 1.56 1.72 1.56 1.02 0 1.85-.83 1.85-1.85s-.83-1.85-1.85-1.85c-.94 0-1.7.7-1.82 1.62l-5.12-1.12c-.22-.05-.44.07-.51.29l-1.68 5.34c-2.41.04-4.66.68-6.33 1.69-.57-.75-1.48-1.23-2.51-1.23-1.65 0-3 1.35-3 3 0 1.13.62 2.11 1.55 2.62-.05.29-.08.57-.08.88 0 3.86 4.93 7 11 7s11-3.14 11-7c0-.3-.03-.59-.08-.88.93-.51 1.55-1.49 1.55-2.62zM7.5 13c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm9.15 4c-1.39 1.39-4.05 1.5-4.65 1.5-.61 0-3.27-.11-4.65-1.5-.14-.14-.14-.38 0-.53.15-.14.39-.14.53 0 1.11 1.11 3.39 1.22 4.12 1.22.73 0 3.01-.1 4.12-1.22.15-.14.39-.14.53 0 .15.15.15.39 0 .53zm-.65-2c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" />
              </svg>
              <span>Reddit</span>
            </a>

            <!-- LinkedIn -->
            <a 
              :href="linkedInShareUrl" 
              target="_blank" 
              class="px-3 py-3 bg-[#0A66C2] hover:bg-[#004182] rounded-xl flex items-center justify-center gap-1.5 text-xs font-bold text-white transition"
            >
              <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.779-1.75-1.75s.784-1.75 1.75-1.75 1.75.779 1.75 1.75-.784 1.75-1.75 1.75zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
              </svg>
              <span>LinkedIn</span>
            </a>

            <!-- Copy Link (Beside LinkedIn Button) -->
            <button 
              @click="copyTokenUrl"
              class="px-3 py-3 bg-purple-600/10 hover:bg-purple-600/25 border border-purple-500/20 hover:border-purple-500/40 rounded-xl text-xs font-bold text-purple-400 hover:text-purple-300 transition flex items-center justify-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5z" />
              </svg>
              <span>Copy Link</span>
            </button>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import Swal from 'sweetalert2';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  token: { type: Object, required: true },
  usdPrice: { type: [Number, String], default: 0 },
  xlmPrice: { type: [Number, String], default: 0 },
  priceChange: { type: Number, default: 0 },
  liquidity: { type: Number, default: 0 },
  holders: { type: Number, default: 0 }
});

const emit = defineEmits(['update:modelValue']);

// Get absolute URL of the token insight page
const tokenUrl = computed(() => {
  if (typeof window === 'undefined') return '';
  return `${window.location.origin}/t/${props.token.issuer}`;
});

// Post contents calculations
const xShareUrl = computed(() => {
  const priceUsd = formatPrice(props.usdPrice);
  const priceXlm = formatXlmPrice(props.xlmPrice);
  const change = props.priceChange >= 0 ? `+${props.priceChange}` : props.priceChange;
  const liq = formatPrice(props.liquidity);
  const holdersFormatted = formatNumber(props.holders);
  const trustScore = (props.token.rating?.average ?? 7.5).toFixed(1);

  const text = `Check out $${props.token.asset_code} on TokenGlade 👀\n\n💰 Price: $${priceUsd} USD (${priceXlm} $XLM)\n📊 24H Change: ${change}%\n💧 Liquidity: $${liq}\n👥 Holders: ${holdersFormatted}\n🛡️ Trust Score: ${trustScore}/10\n\nExplore the full Token Insight:\n${tokenUrl.value}`;
  return `https://x.com/intent/tweet?text=${encodeURIComponent(text)}`;
});

const fbShareUrl = computed(() => {
  return `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(tokenUrl.value)}`;
});

const redditShareUrl = computed(() => {
  const title = `${props.token.asset_code} market data and on-chain insights on Stellar`;
  const body = `TokenGlade provides an overview of ${props.token.asset_code}, including its price, liquidity, holders, recent trades, order book, and security parameters.\n\nFull Token Insight: ${tokenUrl.value}`;
  return `https://www.reddit.com/submit?title=${encodeURIComponent(title)}&url=${encodeURIComponent(tokenUrl.value)}&text=${encodeURIComponent(body)}`;
});

const linkedInShareUrl = computed(() => {
  return `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(tokenUrl.value)}`;
});

function closeModal() {
  emit('update:modelValue', false);
}

// Copy URL Action
async function copyTokenUrl() {
  try {
    await navigator.clipboard.writeText(tokenUrl.value);
    Swal.fire({
      icon: 'success',
      title: 'Link Copied',
      text: 'Token Insight link copied to clipboard.',
      timer: 1500,
      showConfirmButton: false
    });
  } catch (err) {
    console.error('Failed to copy text: ', err);
  }
}

// Utility formatting helpers
function formatPrice(val) {
  if (!val) return '0.00';
  const num = Number(val);
  return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
}

function formatXlmPrice(val) {
  if (!val) return '0.00';
  const num = Number(val);
  return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 6 });
}

function formatNumber(val) {
  if (!val) return '0';
  return Number(val).toLocaleString(undefined);
}
</script>
