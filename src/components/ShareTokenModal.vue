<template>
  <div v-if="modelValue" class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-md transition-all duration-300">
    <!-- Modal Card -->
    <div class="relative w-full max-w-2xl bg-theme-panel border border-theme-line rounded-3xl overflow-hidden shadow-2xl flex flex-col text-left max-h-[90vh]">
      
      <!-- Gradient Decorative Header line -->
      <div class="h-1.5 w-full bg-gradient-to-r from-purple-500 via-cyan-500 to-emerald-500 flex-shrink-0"></div>

      <!-- Modal Header -->
      <div class="p-4 sm:p-5 border-b border-theme-line bg-theme-panel2 flex items-center justify-between flex-shrink-0">
        <div class="flex items-center gap-2 sm:gap-3">
          <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400">
            <Share2 class="w-4 h-4" />
          </div>
          <div>
            <h3 class="text-sm sm:text-base font-bold text-theme-ink font-display">Share & Embed ${{ token.asset_code }}</h3>
            <p class="text-[11px] text-theme-dim">Spread the word or embed live data on your website</p>
          </div>
        </div>
        <button 
          @click="closeModal"
          class="p-2 rounded-xl bg-theme-panel hover:bg-theme-panel2 border border-theme-line text-theme-dim hover:text-theme-ink transition cursor-pointer"
          aria-label="Close modal"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Modal Tabs Navigation -->
      <div class="px-4 sm:px-5 pt-3 border-b border-theme-line bg-theme-panel flex items-center gap-2 flex-shrink-0 overflow-x-auto scrollbar-none">
        <button 
          @click="activeTab = 'card'"
          class="px-3.5 py-2 rounded-t-xl text-xs font-bold transition flex items-center gap-1.5 border-b-2 whitespace-nowrap cursor-pointer"
          :class="activeTab === 'card' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400 bg-cyan-500/5' : 'border-transparent text-theme-dim hover:text-theme-ink'"
        >
          <ImageIcon class="w-3.5 h-3.5" />
          <span>Price Card Image</span>
        </button>

        <button 
          @click="activeTab = 'socials'"
          class="px-3.5 py-2 rounded-t-xl text-xs font-bold transition flex items-center gap-1.5 border-b-2 whitespace-nowrap cursor-pointer"
          :class="activeTab === 'socials' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400 bg-cyan-500/5' : 'border-transparent text-theme-dim hover:text-theme-ink'"
        >
          <Send class="w-3.5 h-3.5" />
          <span>Quick Share</span>
        </button>

        <button 
          @click="activeTab = 'embed'"
          class="px-3.5 py-2 rounded-t-xl text-xs font-bold transition flex items-center gap-1.5 border-b-2 whitespace-nowrap cursor-pointer"
          :class="activeTab === 'embed' ? 'border-cyan-500 text-cyan-600 dark:text-cyan-400 bg-cyan-500/5' : 'border-transparent text-theme-dim hover:text-theme-ink'"
        >
          <Code2 class="w-3.5 h-3.5" />
          <span>Embed Widget</span>
        </button>
      </div>

      <!-- Modal Body (Scrollable) -->
      <div class="p-4 sm:p-6 overflow-y-auto space-y-5 flex-1 custom-scrollbar">
        
        <!-- TAB 1: SOCIAL PRICE CARD IMAGE (VIRAL LOOP) -->
        <div v-if="activeTab === 'card'" class="space-y-4">
          <div class="flex items-center justify-between">
            <span class="text-xs font-bold text-theme-dim uppercase tracking-wider">Social Share Card Preview</span>
          </div>

          <!-- Canvas Preview Container -->
          <div class="relative w-full aspect-[1.91/1] rounded-2xl overflow-hidden border border-theme-line shadow-lg bg-[#080C14] flex items-center justify-center group">
            <canvas ref="cardCanvas" class="w-full h-full object-contain"></canvas>
            
            <div v-if="renderingCanvas" class="absolute inset-0 bg-[#080C14]/80 flex flex-col items-center justify-center gap-2">
              <div class="animate-spin rounded-full h-6 w-6 border-2 border-cyan-500 border-t-transparent"></div>
              <span class="text-xs text-theme-dim font-medium">Generating HD Price Card...</span>
            </div>
          </div>

          <!-- Card Actions Bar -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-2">
            <!-- 1. Download PNG -->
            <button 
              @click="downloadImage" 
              class="px-4 py-2.5 bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 shadow-md cursor-pointer"
            >
              <Download class="w-4 h-4" />
              <span>Download Image</span>
            </button>

            <!-- 2. Copy Image -->
            <button 
              @click="copyImageToClipboard" 
              class="px-4 py-2.5 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line hover:border-theme-line2 text-theme-ink text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <Copy class="w-4 h-4 text-cyan-500" />
              <span>{{ imageCopied ? '✓ Copied Image' : 'Copy Image' }}</span>
            </button>

            <!-- 3. Share to X -->
            <a 
              :href="xShareUrl" 
              target="_blank" 
              class="px-4 py-2.5 bg-black hover:bg-slate-900 border border-slate-700 text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
              </svg>
              <span>Post to X</span>
            </a>
          </div>

          <p class="text-[11px] text-theme-faint text-center pt-1">
            💡 <b>Pro tip:</b> Click "Copy Image" and paste directly (<kbd class="px-1 py-0.5 rounded bg-theme-panel2 border border-theme-line font-mono text-[10px]">Ctrl+V</kbd>) into Telegram, Discord, Twitter, or WhatsApp!
          </p>
        </div>

        <!-- TAB 2: QUICK SOCIAL SHARE -->
        <div v-else-if="activeTab === 'socials'" class="space-y-4">
          <!-- Token Quick Preview Card -->
          <div class="bg-theme-panel2 border border-theme-line rounded-2xl p-4 space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-theme-ink font-bold select-none overflow-hidden flex-shrink-0">
                  <img v-if="token.image" :src="token.image" class="w-full h-full object-cover" />
                  <span v-else class="text-xs font-mono">{{ token.asset_code?.substring(0, 2).toUpperCase() }}</span>
                </div>
                <div>
                  <div class="flex items-center gap-1.5">
                    <span class="text-sm font-bold text-theme-ink uppercase">{{ token.asset_code }}</span>
                    <span v-if="token.token_verify === 1 || token.is_verified" class="text-[10px] font-bold text-emerald-500 bg-emerald-500/10 px-1.5 py-0.5 rounded border border-emerald-500/20">
                      ✓ Verified
                    </span>
                  </div>
                  <span class="text-xs text-theme-dim font-medium block truncate max-w-[240px]">{{ token.name || token.project?.org_name || 'Stellar Project' }}</span>
                </div>
              </div>

              <div class="text-right">
                <div class="text-sm font-mono font-bold text-theme-ink">${{ formatPrice(usdPrice) }}</div>
                <div class="text-xs font-mono font-bold" :class="priceChange >= 0 ? 'text-emerald-500' : 'text-rose-500'">
                  {{ priceChange >= 0 ? '▲ +' : '▼ ' }}{{ priceChange }}%
                </div>
              </div>
            </div>
          </div>

          <!-- Social Buttons Grid -->
          <div class="space-y-2">
            <label class="block text-xs font-bold text-theme-dim uppercase tracking-wider">Share directly to</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
              <!-- X (Twitter) -->
              <a 
                :href="xShareUrl" 
                target="_blank" 
                class="p-3 bg-black hover:bg-slate-900 border border-theme-line rounded-xl flex items-center justify-center gap-2 text-xs font-bold text-white transition"
              >
                <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24">
                  <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                </svg>
                <span>X / Twitter</span>
              </a>

              <!-- Telegram -->
              <a 
                :href="telegramShareUrl" 
                target="_blank" 
                class="p-3 bg-[#229ED9] hover:bg-[#1E8BC0] rounded-xl flex items-center justify-center gap-2 text-xs font-bold text-white transition"
              >
                <Send class="w-3.5 h-3.5" />
                <span>Telegram</span>
              </a>

              <!-- WhatsApp -->
              <a 
                :href="whatsAppShareUrl" 
                target="_blank" 
                class="p-3 bg-[#25D366] hover:bg-[#20BA5A] rounded-xl flex items-center justify-center gap-2 text-xs font-bold text-white transition"
              >
                <MessageSquare class="w-3.5 h-3.5" />
                <span>WhatsApp</span>
              </a>

              <!-- Reddit -->
              <a 
                :href="redditShareUrl" 
                target="_blank" 
                class="p-3 bg-[#FF4500] hover:bg-[#E03D00] rounded-xl flex items-center justify-center gap-2 text-xs font-bold text-white transition"
              >
                <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24">
                  <path d="M24 11.5c0-1.65-1.35-3-3-3-.96 0-1.86.48-2.42 1.24-1.64-1-3.85-1.64-6.23-1.72l1.41-4.48 4.61 1c.07.87.81 1.56 1.72 1.56 1.02 0 1.85-.83 1.85-1.85s-.83-1.85-1.85-1.85c-.94 0-1.7.7-1.82 1.62l-5.12-1.12c-.22-.05-.44.07-.51.29l-1.68 5.34c-2.41.04-4.66.68-6.33 1.69-.57-.75-1.48-1.23-2.51-1.23-1.65 0-3 1.35-3 3 0 1.13.62 2.11 1.55 2.62-.05.29-.08.57-.08.88 0 3.86 4.93 7 11 7s11-3.14 11-7c0-.3-.03-.59-.08-.88.93-.51 1.55-1.49 1.55-2.62zM7.5 13c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm9.15 4c-1.39 1.39-4.05 1.5-4.65 1.5-.61 0-3.27-.11-4.65-1.5-.14-.14-.14-.38 0-.53.15-.14.39-.14.53 0 1.11 1.11 3.39 1.22 4.12 1.22.73 0 3.01-.1 4.12-1.22.15-.14.39-.14.53 0 .15.15.15.39 0 .53zm-.65-2c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z" />
                </svg>
                <span>Reddit</span>
              </a>
            </div>
          </div>

          <!-- Copy Link Section -->
          <div class="space-y-1.5">
            <label class="block text-xs font-bold text-theme-dim uppercase tracking-wider">Direct Page Link</label>
            <div class="flex items-center gap-2">
              <input 
                type="text" 
                readonly 
                :value="tokenUrl" 
                class="flex-1 bg-theme-panel2 border border-theme-line rounded-xl px-3.5 py-2.5 text-xs font-mono text-theme-ink outline-none focus:border-cyan-500"
              />
              <button 
                @click="copyTokenUrl" 
                class="px-4 py-2.5 bg-theme-panel2 hover:bg-theme-panel3 border border-theme-line rounded-xl text-xs font-bold text-theme-ink transition flex items-center gap-1.5 cursor-pointer flex-shrink-0"
              >
                <Copy class="w-3.5 h-3.5 text-cyan-500" />
                <span>{{ linkCopied ? '✓ Copied' : 'Copy Link' }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- TAB 3: EMBED WIDGET -->
        <div v-else-if="activeTab === 'embed'" class="space-y-4">
          <!-- Widget Live Preview -->
          <div class="space-y-2">
            <span class="text-xs font-bold text-theme-dim uppercase tracking-wider">Widget Live Preview</span>
            <div class="p-4 bg-theme-panel2 border border-theme-line rounded-2xl flex items-center justify-center">
              <!-- Mini Badge Card Preview -->
              <div class="w-full max-w-sm bg-[#0B0F19] text-white p-4 rounded-xl border border-cyan-500/20 shadow-xl space-y-3">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-purple-500/20 flex items-center justify-center text-xs font-bold text-cyan-400">
                      <img v-if="token.image" :src="token.image" class="w-full h-full object-cover rounded-lg" />
                      <span v-else>{{ token.asset_code?.substring(0, 2) }}</span>
                    </div>
                    <div>
                      <div class="text-xs font-bold">{{ token.asset_code }}</div>
                      <div class="text-[10px] text-slate-400">Stellar Asset</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs font-bold font-mono text-cyan-400">${{ formatPrice(usdPrice) }}</div>
                    <div class="text-[10px] font-mono" :class="priceChange >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                      {{ priceChange >= 0 ? '+' : '' }}{{ priceChange }}%
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between text-[10px] text-slate-400 pt-2 border-t border-slate-800">
                  <span>Powered by TokenGlade</span>
                  <span class="text-cyan-400 hover:underline cursor-pointer">Live Analytics &rarr;</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Embed Code Snippet -->
          <div class="space-y-2">
            <div class="flex items-center justify-between">
              <label class="text-xs font-bold text-theme-dim uppercase tracking-wider">HTML iFrame Code</label>
              <button @click="copyEmbedCode" class="text-xs text-cyan-500 font-bold hover:underline cursor-pointer flex items-center gap-1">
                <Copy class="w-3 h-3" />
                <span>{{ embedCopied ? '✓ Copied' : 'Copy Embed Code' }}</span>
              </button>
            </div>
            <textarea 
              readonly 
              rows="3" 
              :value="iframeEmbedCode" 
              class="w-full bg-theme-panel2 border border-theme-line rounded-xl p-3 text-xs font-mono text-theme-ink outline-none focus:border-cyan-500 select-all"
            ></textarea>
          </div>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import tgLogo from '@/assets/token-glade-logo.png';
import { 
  Share2, 
  X, 
  Image as ImageIcon, 
  Send, 
  Code2, 
  Download, 
  Copy, 
  MessageSquare 
} from 'lucide-vue-next';

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  token: { type: Object, required: true },
  usdPrice: { type: [Number, String], default: 0 },
  xlmPrice: { type: [Number, String], default: 0 },
  priceChange: { type: [Number, String], default: 0 },
  liquidity: { type: [Number, String], default: 0 },
  holders: { type: [Number, String], default: 0 }
});

const emit = defineEmits(['update:modelValue']);

const activeTab = ref('card');
const cardCanvas = ref(null);
const renderingCanvas = ref(false);
const linkCopied = ref(false);
const imageCopied = ref(false);
const embedCopied = ref(false);
const resolvedLiquidity = ref(Number(props.liquidity) || 0);

// Sync resolvedLiquidity whenever props.liquidity provides a valid number
watch(
  () => props.liquidity,
  (newVal) => {
    const num = Number(newVal) || 0;
    if (num > 0 || resolvedLiquidity.value === 0) {
      resolvedLiquidity.value = num;
    }
  },
  { immediate: true }
);

// Standalone liquidity fetch if modal is opened before parent page finishes liquidity fetch
async function fetchModalLiquidity() {
  const issuer = props.token?.issuer || props.token?.asset_issuer;
  const code = props.token?.asset_code || props.token?.code;
  if (!issuer || !code) return;

  try {
    const res = await axios.get('/api/token/liquidity', {
      params: {
        issuer: issuer,
        code: code,
        usd_price: props.usdPrice || props.token?.usd_price || 0
      },
      timeout: 8000
    });
    if (res.data && (res.data.total_tvl !== undefined || res.data.total_tvl !== null)) {
      const tvl = Number(res.data.total_tvl) || 0;
      if (tvl > 0) {
        resolvedLiquidity.value = tvl;
      }
    }
  } catch (e) {
    // Silent catch
  }
}

// Get absolute URL of the token insight page
const tokenUrl = computed(() => {
  if (typeof window === 'undefined') return '';
  return `${window.location.origin}/token-insight?asset_code=${props.token.asset_code}&issuer=${props.token.issuer}`;
});

// Viral formatted post text
const shareText = computed(() => {
  const code = props.token.asset_code || 'TOKEN';
  const price = formatPrice(props.usdPrice);
  const xlm = formatXlmPrice(props.xlmPrice);
  const change = (props.priceChange || 0) >= 0 ? `+${props.priceChange}%` : `${props.priceChange}%`;
  const effectiveLiq = resolvedLiquidity.value > 0 ? resolvedLiquidity.value : (Number(props.liquidity) || 0);
  const liq = formatNumber(effectiveLiq);
  const hld = formatNumber(props.holders);

  return `$${code} on @TokenGlade: $${price} (${xlm} $XLM) | 24h: ${change}\nLiquidity: $${liq} • Holders: ${hld}\n\nTrack real-time Stellar DEX charts & orderbook:`;
});

// Social Share URLs
const xShareUrl = computed(() => {
  return `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText.value)}&url=${encodeURIComponent(tokenUrl.value)}`;
});

const telegramShareUrl = computed(() => {
  return `https://t.me/share/url?url=${encodeURIComponent(tokenUrl.value)}&text=${encodeURIComponent(shareText.value)}`;
});

const whatsAppShareUrl = computed(() => {
  return `https://api.whatsapp.com/send?text=${encodeURIComponent(shareText.value + ' ' + tokenUrl.value)}`;
});

const redditShareUrl = computed(() => {
  const title = `$${props.token.asset_code} live price and on-chain analytics on Stellar`;
  return `https://www.reddit.com/submit?title=${encodeURIComponent(title)}&url=${encodeURIComponent(tokenUrl.value)}`;
});

const iframeEmbedCode = computed(() => {
  return `<iframe src="${tokenUrl.value}" width="100%" height="450" frameborder="0" style="border-radius:16px;border:1px solid #1e293b;overflow:hidden;" title="${props.token.asset_code} on TokenGlade"></iframe>`;
});

function closeModal() {
  emit('update:modelValue', false);
}

let currentRenderId = 0;
let renderDebounceTimer = null;

function triggerRender(delay = 50) {
  if (renderDebounceTimer) clearTimeout(renderDebounceTimer);
  renderDebounceTimer = setTimeout(() => {
    renderCard();
  }, delay);
}

// Draw Viral Price Card on HTML5 Canvas
async function renderCard() {
  const renderId = ++currentRenderId;
  await nextTick();
  if (renderId !== currentRenderId) return;

  const canvas = cardCanvas.value;
  if (!canvas) return;

  renderingCanvas.value = true;
  const ctx = canvas.getContext('2d');
  const w = 1200;
  const h = 630;
  canvas.width = w;
  canvas.height = h;

  // 1. Deep Obsidian Gradient Background
  const bg = ctx.createLinearGradient(0, 0, w, h);
  bg.addColorStop(0, '#0B0F19');
  bg.addColorStop(0.5, '#070A10');
  bg.addColorStop(1, '#04060A');
  ctx.fillStyle = bg;
  ctx.fillRect(0, 0, w, h);

  // 2. Radiant Ambient Glow Orbs
  const glow1 = ctx.createRadialGradient(180, 140, 0, 180, 140, 420);
  glow1.addColorStop(0, 'rgba(18, 203, 238, 0.22)');
  glow1.addColorStop(1, 'rgba(18, 203, 238, 0)');
  ctx.fillStyle = glow1;
  ctx.fillRect(0, 0, w, h);

  const glow2 = ctx.createRadialGradient(1040, 480, 0, 1040, 480, 420);
  glow2.addColorStop(0, 'rgba(240, 24, 156, 0.18)');
  glow2.addColorStop(1, 'rgba(240, 24, 156, 0)');
  ctx.fillStyle = glow2;
  ctx.fillRect(0, 0, w, h);

  // 3. Card Outer Border
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
  ctx.lineWidth = 3;
  ctx.strokeRect(20, 20, w - 40, h - 40);

  // Load official TokenGlade logo
  let brandLogoLoaded = false;
  const brandImg = new Image();
  brandImg.src = tgLogo;
  await new Promise((resolve) => {
    if (brandImg.complete) {
      brandLogoLoaded = true;
      return resolve();
    }
    brandImg.onload = () => { brandLogoLoaded = true; resolve(); };
    brandImg.onerror = () => resolve();
  });

  if (renderId !== currentRenderId) return;

  // 4. Header Bar with Official TokenGlade Logo & Lockup
  if (brandLogoLoaded) {
    ctx.drawImage(brandImg, 60, 48, 36, 36);
  }

  const logoTextX = brandLogoLoaded ? 106 : 60;
  ctx.font = '900 24px "Space Grotesk", sans-serif';
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText('TOKEN', logoTextX, 74);

  const tokenTextW = ctx.measureText('TOKEN').width;
  ctx.fillStyle = '#12CBEE';
  ctx.fillText('GLADE', logoTextX + tokenTextW, 74);

  const gladeTextW = ctx.measureText('GLADE').width;
  ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
  ctx.font = '600 14px "JetBrains Mono", monospace';
  ctx.fillText('•  STELLAR NETWORK', logoTextX + tokenTextW + gladeTextW + 14, 73);

  // Right pill in header
  ctx.fillStyle = 'rgba(255, 255, 255, 0.06)';
  ctx.beginPath();
  ctx.roundRect(w - 280, 48, 220, 36, 18);
  ctx.fill();
  ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
  ctx.stroke();

  ctx.fillStyle = '#E2E8F0';
  ctx.font = '600 13px "JetBrains Mono", monospace';
  ctx.fillText('LIVE INSIGHT CARD', w - 245, 71);

  // 5. Token Avatar & Identity
  const avatarX = 60;
  const avatarY = 130;
  const avatarSize = 100;

  const rawImageUrl = props.token.image || props.token.logo || props.token.project?.logo || props.token.toml_info?.image;
  let imgLoaded = false;
  if (rawImageUrl) {
    try {
      const proxiedUrl = rawImageUrl.startsWith('data:') || rawImageUrl.startsWith('blob:')
        ? rawImageUrl
        : `/api/token/image-proxy?url=${encodeURIComponent(rawImageUrl)}`;
      
      const img = new Image();
      img.crossOrigin = 'anonymous';
      await new Promise((resolve) => {
        img.onload = () => {
          ctx.save();
          ctx.fillStyle = '#0f172a';
          ctx.beginPath();
          ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 24);
          ctx.fill();

          ctx.beginPath();
          ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 24);
          ctx.clip();
          ctx.drawImage(img, avatarX, avatarY, avatarSize, avatarSize);
          ctx.restore();

          ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
          ctx.lineWidth = 2;
          ctx.beginPath();
          ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 24);
          ctx.stroke();

          imgLoaded = true;
          resolve();
        };
        img.onerror = () => {
          const fallbackImg = new Image();
          fallbackImg.crossOrigin = 'anonymous';
          fallbackImg.onload = () => {
            ctx.save();
            ctx.fillStyle = '#0f172a';
            ctx.beginPath();
            ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 24);
            ctx.fill();

            ctx.beginPath();
            ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 24);
            ctx.clip();
            ctx.drawImage(fallbackImg, avatarX, avatarY, avatarSize, avatarSize);
            ctx.restore();

            ctx.strokeStyle = 'rgba(255, 255, 255, 0.15)';
            ctx.lineWidth = 2;
            ctx.beginPath();
            ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 24);
            ctx.stroke();

            imgLoaded = true;
            resolve();
          };
          fallbackImg.onerror = () => resolve();
          fallbackImg.src = rawImageUrl;
        };
        img.src = proxiedUrl;
      });
    } catch (e) {}
  }

  if (renderId !== currentRenderId) return;

  if (!imgLoaded) {
    ctx.fillStyle = 'rgba(18, 203, 238, 0.15)';
    ctx.beginPath();
    ctx.roundRect(avatarX, avatarY, avatarSize, avatarSize, 24);
    ctx.fill();
    ctx.strokeStyle = 'rgba(18, 203, 238, 0.35)';
    ctx.stroke();

    ctx.fillStyle = '#12CBEE';
    ctx.font = 'bold 36px "Space Grotesk", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(props.token.asset_code?.substring(0, 2).toUpperCase() || 'TK', avatarX + avatarSize / 2, avatarY + 63);
    ctx.textAlign = 'left';
  }

  // Token Symbol & Verified Badge
  ctx.fillStyle = '#FFFFFF';
  ctx.font = 'bold 44px "Space Grotesk", sans-serif';
  ctx.fillText(props.token.asset_code || 'TOKEN', 180, 180);

  // Verified Badge
  if (props.token.token_verify === 1 || props.token.is_verified) {
    const symbolWidth = ctx.measureText(props.token.asset_code || 'TOKEN').width;
    const badgeX = 195 + symbolWidth;
    ctx.fillStyle = 'rgba(46, 212, 122, 0.15)';
    ctx.beginPath();
    ctx.roundRect(badgeX, 145, 115, 36, 10);
    ctx.fill();
    ctx.strokeStyle = 'rgba(46, 212, 122, 0.4)';
    ctx.stroke();

    ctx.fillStyle = '#2ED47A';
    ctx.font = 'bold 14px "Space Grotesk", sans-serif';
    ctx.fillText('✓ VERIFIED', badgeX + 12, 168);
  }

  // Token Name / Project Name
  ctx.fillStyle = '#94A3B8';
  ctx.font = '500 20px "Inter", sans-serif';
  const nameText = props.token.name || props.token.project?.org_name || 'Stellar On-Chain Asset';
  ctx.fillText(nameText.length > 35 ? nameText.substring(0, 32) + '...' : nameText, 180, 218);

  // 6. Price Showcase Section
  const priceY = 285;
  ctx.fillStyle = '#94A3B8';
  ctx.font = 'bold 15px "JetBrains Mono", monospace';
  ctx.fillText('CURRENT PRICE (USD)', 60, priceY);

  ctx.fillStyle = '#FFFFFF';
  ctx.font = 'bold 56px "JetBrains Mono", monospace';
  ctx.fillText('$' + formatPrice(props.usdPrice), 60, priceY + 65);

  const priceW = ctx.measureText('$' + formatPrice(props.usdPrice)).width;

  // 24H Change Pill
  const isUp = (props.priceChange || 0) >= 0;
  const changeText = (isUp ? '▲ +' : '▼ ') + (props.priceChange || 0) + '% (24h)';
  const pillX = 85 + priceW;
  const pillY = priceY + 18;

  ctx.fillStyle = isUp ? 'rgba(46, 212, 122, 0.15)' : 'rgba(240, 97, 109, 0.15)';
  ctx.beginPath();
  ctx.roundRect(pillX, pillY, 190, 48, 12);
  ctx.fill();
  ctx.strokeStyle = isUp ? 'rgba(46, 212, 122, 0.4)' : 'rgba(240, 97, 109, 0.4)';
  ctx.stroke();

  ctx.fillStyle = isUp ? '#2ED47A' : '#F0616D';
  ctx.font = 'bold 18px "JetBrains Mono", monospace';
  ctx.fillText(changeText, pillX + 18, pillY + 31);

  // Price in XLM
  ctx.fillStyle = '#12CBEE';
  ctx.font = '600 22px "JetBrains Mono", monospace';
  ctx.fillText('≈ ' + formatXlmPrice(props.xlmPrice) + ' XLM', 60, priceY + 110);

  // 7. Stats Tiles (3 Bottom Cards)
  const tileY = 430;
  const tileH = 105;
  const tileW = 340;
  const gap = 30;

  const effectiveLiquidity = resolvedLiquidity.value > 0 ? resolvedLiquidity.value : (Number(props.liquidity) || 0);

  const stats = [
    { label: 'TOTAL LIQUIDITY', val: '$' + formatNumber(effectiveLiquidity), color: '#FFFFFF' },
    { label: 'TOTAL HOLDERS', val: formatNumber(props.holders), color: '#FFFFFF' },
    { label: 'TRUST SCORE', val: ((props.token.rating?.average ?? 7.5)).toFixed(1) + ' / 10', color: (props.token.rating?.average ?? 7.5) >= 8 ? '#2ED47A' : '#12CBEE' }
  ];

  stats.forEach((st, idx) => {
    const x = 60 + idx * (tileW + gap);
    
    ctx.fillStyle = 'rgba(255, 255, 255, 0.03)';
    ctx.beginPath();
    ctx.roundRect(x, tileY, tileW, tileH, 16);
    ctx.fill();
    ctx.strokeStyle = 'rgba(255, 255, 255, 0.08)';
    ctx.stroke();

    ctx.fillStyle = '#64748B';
    ctx.font = 'bold 13px "JetBrains Mono", monospace';
    ctx.fillText(st.label, x + 20, tileY + 35);

    ctx.fillStyle = st.color;
    ctx.font = 'bold 28px "JetBrains Mono", monospace';
    ctx.fillText(st.val, x + 20, tileY + 75);
  });

  // 8. Footer Bar
  ctx.fillStyle = '#64748B';
  ctx.font = '500 14px "Inter", sans-serif';
  ctx.fillText('Real-time Order Books, Candlesticks & Whale Tracking', 60, 580);

  if (brandLogoLoaded) {
    ctx.drawImage(brandImg, w - 215, 562, 22, 22);
  }
  const footerLogoX = brandLogoLoaded ? w - 186 : w - 180;
  ctx.font = 'bold 15px "JetBrains Mono", monospace';
  ctx.fillStyle = '#FFFFFF';
  ctx.fillText('token', footerLogoX, 578);

  const tW = ctx.measureText('token').width;
  ctx.fillStyle = '#12CBEE';
  ctx.fillText('glade.com', footerLogoX + tW, 578);

  if (renderId === currentRenderId) {
    renderingCanvas.value = false;
  }
}

// Download image file
function downloadImage() {
  const canvas = cardCanvas.value;
  if (!canvas) return;

  const link = document.createElement('a');
  link.download = `${props.token.asset_code || 'token'}-price-card.png`;
  link.href = canvas.toDataURL('image/png');
  link.click();

  Swal.fire({
    icon: 'success',
    title: 'Image Downloaded!',
    text: 'Share it on Twitter, Telegram, or Discord.',
    timer: 1800,
    showConfirmButton: false,
    background: document.documentElement.classList.contains('light') ? '#ffffff' : '#0e131c',
    color: document.documentElement.classList.contains('light') ? '#0f172a' : '#ffffff'
  });
}

// Copy Image Blob to Clipboard
async function copyImageToClipboard() {
  const canvas = cardCanvas.value;
  if (!canvas) return;

  try {
    canvas.toBlob(async (blob) => {
      if (!blob) return;
      try {
        await navigator.clipboard.write([
          new ClipboardItem({ 'image/png': blob })
        ]);
        imageCopied.value = true;
        setTimeout(() => imageCopied.value = false, 2500);

        Swal.fire({
          icon: 'success',
          title: 'Image Copied!',
          text: 'Paste directly (Ctrl+V) into Telegram, Twitter, or Discord.',
          timer: 2000,
          showConfirmButton: false,
          background: document.documentElement.classList.contains('light') ? '#ffffff' : '#0e131c',
          color: document.documentElement.classList.contains('light') ? '#0f172a' : '#ffffff'
        });
      } catch (err) {
        downloadImage();
      }
    });
  } catch (err) {
    downloadImage();
  }
}

// Copy URL Action
async function copyTokenUrl() {
  try {
    await navigator.clipboard.writeText(tokenUrl.value);
    linkCopied.value = true;
    setTimeout(() => linkCopied.value = false, 2000);
  } catch (err) {}
}

// Copy Embed Code
async function copyEmbedCode() {
  try {
    await navigator.clipboard.writeText(iframeEmbedCode.value);
    embedCopied.value = true;
    setTimeout(() => embedCopied.value = false, 2000);
  } catch (err) {}
}

// Watch modal state & re-render canvas when opened
watch(() => props.modelValue, (open) => {
  if (open) {
    if (resolvedLiquidity.value <= 0) {
      fetchModalLiquidity();
    }
    triggerRender(100);
    const issuer = props.token?.issuer || props.token?.asset_issuer;
    if (issuer) {
      fetch(`/t/${issuer}/card.png`).catch(() => {});
    }
  }
});

watch(activeTab, (tab) => {
  if (tab === 'card' && props.modelValue) {
    if (resolvedLiquidity.value <= 0) {
      fetchModalLiquidity();
    }
    triggerRender(80);
  }
});

// Auto re-render canvas when liquidity, price, or holders data arrives asynchronously
watch(
  [
    resolvedLiquidity,
    () => props.liquidity,
    () => props.usdPrice,
    () => props.xlmPrice,
    () => props.holders,
    () => props.priceChange,
    () => props.token
  ],
  () => {
    if (props.modelValue && activeTab.value === 'card') {
      triggerRender(50);
    }
  },
  { deep: true }
);

// Format helpers
function formatPrice(val) {
  if (!val) return '0.00';
  const num = Number(val);
  if (num < 0.0001) return num.toFixed(7);
  if (num < 0.01) return num.toFixed(5);
  return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 4 });
}

function formatXlmPrice(val) {
  if (!val) return '0.00';
  const num = Number(val);
  if (num < 0.0001) return num.toFixed(7);
  return num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 6 });
}

function formatNumber(val) {
  if (!val) return '0';
  return new Intl.NumberFormat("en-US", {
    maximumFractionDigits: 0
  }).format(val);
}
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: var(--line, rgba(255, 255, 255, 0.1));
  border-radius: 4px;
}
</style>
