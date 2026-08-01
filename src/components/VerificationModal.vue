<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="handleClose">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 transition-opacity bg-slate-950/80 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild as="template" enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            <DialogPanel
              class="relative w-full max-w-2xl overflow-hidden text-left transition-all transform bg-[#111620] border border-[rgba(148,163,184,0.16)] rounded-[25px] shadow-2xl">
              
              <!-- Close Button (X) -->
              <button @click="handleClose"
                class="absolute top-5 right-5 text-slate-400 hover:text-white transition focus:outline-none z-10"
                aria-label="Close Modal">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor" class="w-5 h-5">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <div class="p-6 sm:p-8">
                <!-- Header with Icon & Multi-Step Title -->
                <div class="flex items-start gap-4">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-amber-500/20 bg-[#182235] shadow-inner flex-shrink-0">
                    <img :src="verified" class="h-7 w-7" />
                  </div>

                  <div class="flex-1 pr-8">
                    <h2 class="text-xl font-bold tracking-tight text-white font-display">
                      Claim & Verify Project
                    </h2>
                    <p class="mt-1 text-xs text-slate-400 leading-relaxed">
                      {{ stepTitles[currentStep - 1] }}
                    </p>
                  </div>
                </div>

                <!-- Stepper Progress Bar -->
                <div class="mt-6 mb-8 select-none">
                  <div class="flex items-center justify-between relative">
                    <div class="absolute left-0 right-0 top-1/2 h-0.5 bg-slate-800 -translate-y-1/2 z-0"></div>
                    <div class="absolute left-0 top-1/2 h-0.5 bg-gradient-to-r from-purple-500 to-cyan-500 -translate-y-1/2 z-0 transition-all duration-300"
                      :style="{ width: `${((currentStep - 1) / 5) * 100}%` }"></div>

                    <button v-for="step in 6" :key="step" @click="goToStep(step)"
                      :disabled="step > maxReachedStep"
                      class="relative z-10 w-8 h-8 rounded-full border flex items-center justify-center font-mono text-xs font-bold transition-all duration-300"
                      :class="[
                        step === currentStep 
                          ? 'border-cyan-400 bg-cyan-950 text-cyan-400 shadow-[0_0_12px_rgba(34,211,238,0.3)]'
                          : step < currentStep 
                            ? 'border-purple-500 bg-purple-950 text-purple-400' 
                            : 'border-slate-800 bg-slate-900 text-slate-500 disabled:opacity-40 disabled:cursor-not-allowed'
                      ]"
                    >
                      <span v-if="step < currentStep">✓</span>
                      <span v-else>{{ step }}</span>
                    </button>
                  </div>
                </div>

                <!-- Step Contents -->
                <div class="space-y-5 min-h-[300px]">

                  <!-- Step 1: Project Information -->
                  <div v-if="currentStep === 1" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Token Symbol</label>
                        <input type="text" :value="assetCode" readonly
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/50 px-4 py-2.5 text-slate-500 font-mono text-sm focus:outline-none" />
                      </div>
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Issuer Address</label>
                        <input type="text" :value="shorten(issuerAddress, 8, 8)" readonly
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/50 px-4 py-2.5 text-slate-500 font-mono text-sm focus:outline-none" />
                      </div>
                    </div>

                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Project Name <span class="text-rose-500">*</span></label>
                      <input type="text" v-model="form.name" placeholder="e.g. Aqua Network"
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/30 focus:outline-none transition-all" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Category</label>
                        <select v-model="form.category"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all">
                          <option value="">Select Category</option>
                          <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                      </div>
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Launch Date</label>
                        <input type="date" v-model="form.launch_date"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                      </div>
                    </div>

                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Short Description</label>
                      <input type="text" v-model="form.short_description" placeholder="A brief one-sentence pitch (max 120 chars)" maxlength="120"
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                    </div>

                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Full Description</label>
                      <textarea v-model="form.full_description" rows="4" placeholder="Detailed project overview, mission, roadmap, etc."
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all resize-none"></textarea>
                    </div>
                  </div>

                  <!-- Step 2: Branding -->
                  <div v-if="currentStep === 2" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                      <!-- Project Logo -->
                      <div class="space-y-3">
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase">Project Logo</label>
                        <div class="flex items-center gap-4">
                          <div class="w-16 h-16 rounded-2xl border border-slate-800 bg-slate-900/50 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                            <span v-else class="text-xs text-slate-600 font-mono">1:1 Image</span>
                          </div>
                          <div class="flex-1">
                            <input type="file" ref="logoInput" @change="onLogoChange" accept="image/*" class="hidden" />
                            <button @click="$refs.logoInput.click()"
                              class="px-4 py-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-xs font-bold text-white rounded-lg transition-all">
                              Upload Logo
                            </button>
                            <p class="text-[10px] text-slate-500 mt-1.5">Supported: JPG, PNG, WEBP. Max 2MB.</p>
                          </div>
                        </div>
                      </div>

                      <!-- Banner Image -->
                      <div class="space-y-3">
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase">Banner Image</label>
                        <div class="flex items-center gap-4">
                          <div class="w-24 h-16 rounded-2xl border border-slate-800 bg-slate-900/50 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img v-if="bannerPreview" :src="bannerPreview" class="w-full h-full object-cover" />
                            <span v-else class="text-xs text-slate-600 font-mono">16:9 Image</span>
                          </div>
                          <div class="flex-1">
                            <input type="file" ref="bannerInput" @change="onBannerChange" accept="image/*" class="hidden" />
                            <button @click="$refs.bannerInput.click()"
                              class="px-4 py-2 bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-xs font-bold text-white rounded-lg transition-all">
                              Upload Banner
                            </button>
                            <p class="text-[10px] text-slate-500 mt-1.5">Supported: JPG, PNG, WEBP. Max 4MB.</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Step 3: Official Links -->
                  <div v-if="currentStep === 3" class="space-y-4">
                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Website URL</label>
                      <input type="text" v-model="form.website_link" placeholder="https://example.com"
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                    </div>

                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Documentation Link</label>
                      <input type="text" v-model="form.documentation_link" placeholder="https://docs.example.com"
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                    </div>

                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Whitepaper Link</label>
                      <input type="text" v-model="form.whitepaper_link" placeholder="https://example.com/whitepaper.pdf"
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                    </div>

                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">GitHub Repository</label>
                      <input type="text" v-model="form.github_link" placeholder="https://github.com/org/repo"
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                    </div>

                    <div>
                      <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Medium / Blog</label>
                      <input type="text" v-model="form.medium_link" placeholder="https://medium.com/@username"
                        class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                    </div>
                  </div>

                  <!-- Step 4: Social Links -->
                  <div v-if="currentStep === 4" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">X (Twitter)</label>
                        <input type="text" v-model="form.twitter_link" placeholder="https://x.com/username"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                      </div>
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Telegram Community</label>
                        <input type="text" v-model="form.telegram_link" placeholder="https://t.me/community"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                      </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Discord Server</label>
                        <input type="text" v-model="form.discord_link" placeholder="https://discord.gg/invite"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                      </div>
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">LinkedIn Profile</label>
                        <input type="text" v-model="form.linkedin_link" placeholder="https://linkedin.com/company/name"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                      </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">Reddit Subreddit</label>
                        <input type="text" v-model="form.reddit_link" placeholder="https://reddit.com/r/subreddit"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                      </div>
                      <div>
                        <label class="block text-xs font-mono font-bold text-slate-400 uppercase mb-1.5">YouTube Channel</label>
                        <input type="text" v-model="form.youtube_link" placeholder="https://youtube.com/c/channel"
                          class="w-full rounded-xl border border-slate-800 bg-slate-900/30 px-4 py-2.5 text-white text-sm focus:border-cyan-500/50 focus:outline-none transition-all" />
                      </div>
                    </div>
                  </div>

                  <!-- Step 5: Official Wallets -->
                  <div v-if="currentStep === 5" class="space-y-4">
                    <div class="flex items-center justify-between">
                      <p class="text-xs text-slate-400 leading-relaxed">
                        Add official project addresses to list labels (e.g. Treasury, Team, Staking) on the explorer.
                      </p>
                      <button @click="addWallet"
                        class="px-3 py-1.5 bg-cyan-500/10 hover:bg-cyan-500/20 border border-cyan-500/20 hover:border-cyan-500/40 text-cyan-400 text-xs font-bold rounded-lg transition-all">
                        + Add Wallet
                      </button>
                    </div>

                    <div class="space-y-3.5 max-h-[250px] overflow-y-auto pr-1">
                      <div v-for="(w, index) in form.wallets" :key="index"
                        class="flex flex-col sm:flex-row items-center gap-3 p-3 bg-slate-900/40 border border-slate-800/80 rounded-xl">
                        <div class="w-full sm:w-1/3">
                          <select v-model="w.label"
                            class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-white text-xs focus:outline-none">
                            <option v-for="l in walletLabels" :key="l" :value="l">{{ l }}</option>
                          </select>
                        </div>
                        <div class="w-full sm:flex-1">
                          <input type="text" v-model="w.wallet_address" placeholder="Stellar Wallet Address (G...)"
                            class="w-full rounded-lg border border-slate-800 bg-slate-900 px-3 py-2 text-white font-mono text-xs focus:outline-none" />
                        </div>
                        <button @click="removeWallet(index)"
                          class="p-2 hover:bg-rose-500/10 text-rose-500 hover:text-rose-400 border border-transparent hover:border-rose-500/20 rounded-lg transition-all flex-shrink-0"
                          title="Remove Wallet">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                      <div v-if="form.wallets.length === 0" class="text-xs text-slate-500 py-8 text-center border border-dashed border-slate-800 rounded-xl">
                        No official wallets added yet. Click "+ Add Wallet" to configure official project addresses.
                      </div>
                    </div>
                  </div>

                  <!-- Step 6: Verification Payment -->
                  <div v-if="currentStep === 6" class="space-y-6">
                    <!-- Benefits List -->
                    <div class="p-4 bg-cyan-950/20 border border-cyan-500/20 rounded-2xl">
                      <h4 class="text-xs font-bold uppercase tracking-wider text-cyan-400 font-mono mb-3">Your Verified Project receives:</h4>
                      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs text-slate-300 font-medium">
                        <div class="flex items-center gap-2">⭐ Verified badge</div>
                        <div class="flex items-center gap-2">📄 Official About page</div>
                        <div class="flex items-center gap-2">🏷️ Official wallet labels</div>
                        <div class="flex items-center gap-2">🚀 Featured eligibility</div>
                        <div class="flex items-center gap-2">🔗 Social links</div>
                        <div class="flex items-center gap-2">👤 Premium project profile</div>
                      </div>
                    </div>

                    <!-- Payment Asset Select -->
                    <div class="space-y-2.5">
                      <p class="text-xs font-bold uppercase tracking-wider text-slate-400 font-mono">Select Payment Asset</p>
                      <div class="space-y-2">
                        <button v-for="asset in paymentAssets" :key="asset.id"
                          @click="$emit('select-asset', asset)"
                          class="w-full rounded-2xl border px-4 py-3.5 text-left transition-all duration-200"
                          :class="
                            selectedAsset?.id === asset.id
                              ? 'border-cyan-400 bg-[#182738] shadow-[0_0_20px_rgba(18,203,238,0.15)] ring-1 ring-cyan-400/50'
                              : 'border-[rgba(148,163,184,0.16)] bg-[#182235] hover:border-slate-600 hover:bg-[#1f2c44]'
                          ">
                          <div class="flex items-center justify-between">
                            <div>
                              <p class="text-sm font-bold text-white flex items-center gap-2">
                                <span>{{ asset.asset_code }}</span>
                                <span v-if="selectedAsset?.id === asset.id" class="text-cyan-400 text-xs font-semibold">✓ Selected</span>
                              </p>
                              <p class="mt-0.5 text-xs font-mono text-slate-400">
                                {{ asset.asset_issuer ? shorten(asset.asset_issuer, 6, 6) : 'Official Stellar network token' }}
                              </p>
                            </div>
                            <div class="text-right">
                              <p class="text-lg font-mono font-black text-white">
                                {{ formatAmount(asset.amount) }}
                                <span class="text-xs font-bold text-slate-400">{{ asset.asset_code }}</span>
                              </p>
                            </div>
                          </div>
                        </button>
                      </div>
                    </div>

                    <!-- Refund Disclaimer -->
                    <div class="rounded-2xl border border-amber-500/20 bg-amber-950/20 p-4">
                      <div class="text-xs leading-relaxed text-amber-300/90 font-medium">
                        Verification fees are fully refunded if your verification request is rejected. Approved projects are non-refundable.
                      </div>
                    </div>
                  </div>

                </div>

                <!-- Footer Navigation Buttons -->
                <div class="mt-8 flex justify-between gap-3 border-t border-slate-800/80 pt-5 select-none">
                  <button @click="prevStep" :disabled="currentStep === 1 || loading"
                    class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 border border-slate-800 hover:border-slate-700 text-xs font-bold text-white rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                    Back
                  </button>

                  <button v-if="currentStep < 6" @click="nextStep" :disabled="!isStepValid"
                    class="px-5 py-2.5 bg-cyan-500 hover:bg-cyan-400 text-xs font-bold text-[#08131a] rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed">
                    Next
                  </button>

                  <button v-else-if="!connected" @click="$emit('connect-wallet')"
                    class="px-6 py-2.5 text-[#08131a] text-xs font-extrabold uppercase tracking-wide rounded-xl hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove shadow-lg">
                    Connect Wallet
                  </button>

                  <button v-else @click="handleVerificationSubmit" :disabled="loading || !selectedAsset"
                    class="px-6 py-2.5 text-[#08131a] text-xs font-extrabold uppercase tracking-wide rounded-xl hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove shadow-lg disabled:cursor-not-allowed disabled:opacity-50">
                    <span v-if="loading">Processing...</span>
                    <span v-else>Pay {{ selectedAsset ? `${formatAmount(selectedAsset.amount)} ${selectedAsset.asset_code}` : 'Now' }}</span>
                  </button>
                </div>

              </div>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, reactive, computed, watch } from 'vue'
import {
  Dialog,
  DialogPanel,
  TransitionRoot,
  TransitionChild
} from '@headlessui/vue'
import verified from "@/assets/verify.png";

const props = defineProps({
  open: {
    type: Boolean,
    default: false
  },
  connected: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  },
  paymentAssets: {
    type: Array,
    default: () => []
  },
  selectedAsset: {
    type: Object,
    default: null
  },
  assetCode: {
    type: String,
    required: true
  },
  issuerAddress: {
    type: String,
    required: true
  }
})

const emit = defineEmits([
  'close',
  'connect-wallet',
  'pay',
  'select-asset'
])

// Stepper management
const currentStep = ref(1)
const maxReachedStep = ref(1)

const stepTitles = [
  'Step 1 – Project Information',
  'Step 2 – Branding & Identity',
  'Step 3 – Official Links',
  'Step 4 – Social Community Channels',
  'Step 5 – Official Wallets config',
  'Step 6 – Complete Verification Request'
]

const categories = [
  'DeFi',
  'Payments',
  'Infrastructure',
  'Meme',
  'Gaming',
  'Stablecoin',
  'AI',
  'NFT',
  'Community',
  'Utility',
  'Other'
]

const walletLabels = [
  'Treasury',
  'Foundation',
  'Team',
  'Marketing',
  'Liquidity',
  'Liquidity Rewards',
  'Staking',
  'Community',
  'DAO',
  'Reserve',
  'Other'
]

// Form State
const form = reactive({
  name: '',
  category: '',
  launch_date: '',
  short_description: '',
  full_description: '',
  logo: null,
  banner: null,
  website_link: '',
  documentation_link: '',
  whitepaper_link: '',
  github_link: '',
  medium_link: '',
  twitter_link: '',
  telegram_link: '',
  discord_link: '',
  linkedin_link: '',
  reddit_link: '',
  youtube_link: '',
  wallets: []
})

// Logo and Banner Previews
const logoPreview = ref(null)
const bannerPreview = ref(null)

const onLogoChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.logo = file
    const reader = new FileReader()
    reader.onload = (event) => {
      logoPreview.value = event.target.result
    }
    reader.readAsDataURL(file)
  }
}

const onBannerChange = (e) => {
  const file = e.target.files[0]
  if (file) {
    form.banner = file
    const reader = new FileReader()
    reader.onload = (event) => {
      bannerPreview.value = event.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Dynamic wallets
const addWallet = () => {
  form.wallets.push({
    wallet_address: '',
    label: 'Treasury'
  })
}

const removeWallet = (index) => {
  form.wallets.splice(index, 1)
}

// Dynamic Step Validator
const isStepValid = computed(() => {
  if (currentStep.value === 1) {
    return form.name.trim().length > 0
  }
  return true
})

const nextStep = () => {
  if (currentStep.value < 6 && isStepValid.value) {
    currentStep.value++
    if (currentStep.value > maxReachedStep.value) {
      maxReachedStep.value = currentStep.value
    }
  }
}

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--
  }
}

const goToStep = (step) => {
  if (step <= maxReachedStep.value) {
    currentStep.value = step
  }
}

const handleClose = () => {
  if (!props.loading) {
    emit('close')
  }
}

const handleVerificationSubmit = () => {
  // Emit form state and files back to TokenInsight page for final startVerification API request
  emit('pay', { ...form })
}

// Watch modal state to reset form when opened/closed
watch(() => props.open, (isOpen) => {
  if (isOpen) {
    currentStep.value = 1
    maxReachedStep.value = 1
    form.name = ''
    form.category = ''
    form.launch_date = ''
    form.short_description = ''
    form.full_description = ''
    form.logo = null
    form.banner = null
    form.website_link = ''
    form.documentation_link = ''
    form.whitepaper_link = ''
    form.github_link = ''
    form.medium_link = ''
    form.twitter_link = ''
    form.telegram_link = ''
    form.discord_link = ''
    form.linkedin_link = ''
    form.reddit_link = ''
    form.youtube_link = ''
    form.wallets = []
    logoPreview.value = null
    bannerPreview.value = null
  }
})

// Utility functions
function shorten(str, head = 4, tail = 4) {
  if (str == null) return ''
  return str.length > head + tail
    ? `${str.slice(0, head)}...${str.slice(-tail)}`
    : str
}

function formatAmount(amount) {
  return Number(amount).toLocaleString(
    undefined,
    {
      maximumFractionDigits: 2
    }
  )
}
</script>

<style scoped>
@keyframes gradientMove {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.animate-gradientMove {
  animation: gradientMove 6s ease infinite;
}
</style>