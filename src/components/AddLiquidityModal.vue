<template>
  <TransitionRoot as="template" :show="props.modelValue">
    <Dialog as="div" class="relative z-50" @close="closeModal">
      <!-- Premium Dark Glassmorphic Backdrop overlay -->
      <TransitionChild 
        as="template" 
        enter="ease-out duration-300" 
        enter-from="opacity-0" 
        enter-to="opacity-100"
        leave="ease-in duration-200" 
        leave-from="opacity-100" 
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/80 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
        <div class="flex items-center justify-center min-h-full p-4 text-center">
          <TransitionChild 
            as="template" 
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100" 
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative w-full max-w-md overflow-hidden rounded-[2rem] border border-gray-800 bg-[#0b0c10]/95 p-8 text-left shadow-2xl backdrop-blur-xl transition-all">
              
              <!-- Subtle Neon Glow Accents inside modal -->
              <div class="absolute -top-32 -left-32 w-64 h-64 bg-purple-600/20 rounded-full blur-[100px] pointer-events-none"></div>
              <div class="absolute -bottom-32 -right-32 w-64 h-64 bg-cyan-500/20 rounded-full blur-[100px] pointer-events-none"></div>

              <!-- Close Button -->
              <button 
                @click="closeModal"
                class="absolute top-4 right-4 text-gray-500 hover:text-white transition duration-200"
                aria-label="Close modal"
              >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>

              <!-- Header -->
              <div class="mb-6 relative z-10">
                <div class="flex items-center gap-2 mb-2">
                  <span class="px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20 text-[10px] font-extrabold uppercase tracking-wider">
                    DEFI LP POOL
                  </span>
                </div>
                <DialogTitle as="h3" class="text-2xl font-black text-white">
                  Add Liquidity
                </DialogTitle>
                <p class="text-gray-400 text-xs mt-1">
                  Deposit XLM & TKG to earn a share of weekly LP reward pool.
                </p>
              </div>

              <!-- Content Form -->
              <div class="space-y-5 relative z-10">
                
                <!-- XLM Input Field -->
                <div>
                  <div class="flex justify-between items-center mb-1.5 text-xs text-gray-400">
                    <label for="xlmAmount" class="font-bold">Deposit Amount (XLM)</label>
                    <span v-if="props.isWalletConnected" class="opacity-80">
                      Balance: <span class="font-mono text-gray-300">{{ xlmBalance.toLocaleString() }} XLM</span>
                    </span>
                  </div>
                  <div class="relative group">
                    <input 
                      id="xlmAmount" 
                      type="number" 
                      step="any"
                      placeholder="0.0" 
                      v-model="xlmInput"
                      @input="handleInput('xlm')"
                      class="w-full bg-gray-950/80 border border-gray-800 rounded-xl py-3.5 pl-4 pr-16 text-white font-mono text-lg focus:outline-none focus:border-cyan-500/50 focus:ring-1 focus:ring-cyan-500/50 transition duration-200"
                    />
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                      <span class="text-gray-500 font-bold text-sm">XLM</span>
                    </div>
                  </div>
                </div>

                <!-- Link/Ratio Icon Indicator -->
                <div class="flex justify-center -my-3">
                  <div class="w-8 h-8 rounded-full bg-gray-900 border border-gray-800 flex items-center justify-center text-gray-400 shadow-lg">
                    <span class="text-sm">⇄</span>
                  </div>
                </div>

                <!-- TKG Input Field -->
                <div>
                  <div class="flex justify-between items-center mb-1.5 text-xs text-gray-400">
                    <label for="tkgAmount" class="font-bold">Deposit Amount (TKG)</label>
                    <span v-if="props.isWalletConnected" class="opacity-80">
                      Balance: <span class="font-mono text-gray-300">{{ tkgBalance.toLocaleString() }} TKG</span>
                    </span>
                  </div>
                  <div class="relative group">
                    <input 
                      id="tkgAmount" 
                      type="number" 
                      step="any"
                      placeholder="0.0" 
                      v-model="tkgInput"
                      @input="handleInput('tkg')"
                      class="w-full bg-gray-950/80 border border-gray-800 rounded-xl py-3.5 pl-4 pr-16 text-white font-mono text-lg focus:outline-none focus:border-purple-500/50 focus:ring-1 focus:ring-purple-500/50 transition duration-200"
                    />
                    <div class="absolute inset-y-0 right-4 flex items-center pointer-events-none">
                      <span class="text-gray-500 font-bold text-sm">TKG</span>
                    </div>
                  </div>
                </div>

                <!-- Current Conversion Ratio Status -->
                <div class="bg-gray-950/60 border border-gray-900 rounded-xl p-4 text-xs space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-500">Current Pool Ratio</span>
                    <span v-if="isReservesLoading" class="text-yellow-500 animate-pulse font-mono">Loading ratio...</span>
                    <span v-else class="text-gray-300 font-mono">1 XLM ≈ {{ poolRatio.toFixed(4) }} TKG</span>
                  </div>
                  <div class="flex justify-between" v-if="isReservesLoading">
                    <span class="text-gray-500">Reserves status</span>
                    <span class="text-yellow-500 animate-pulse">Loading reserves...</span>
                  </div>
                  <div class="flex justify-between" v-else>
                    <span class="text-gray-500">LP Reserves</span>
                    <span class="text-gray-400 font-mono">
                      {{ Math.round(reservesXlm).toLocaleString() }} XLM / {{ Math.round(reservesTkg).toLocaleString() }} TKG
                    </span>
                  </div>
                </div>

                <!-- Action Button -->
                <div class="mt-2">
                  <!-- Case 1: Wallet not connected -->
                  <button 
                    v-if="!props.isWalletConnected"
                    type="button" 
                    @click="triggerConnectWallet"
                    class="w-full py-4 rounded-xl font-bold uppercase tracking-wider text-sm text-white bg-gradient-to-r from-purple-500 to-pink-500 hover:opacity-90 active:scale-[0.98] transition transform duration-200"
                  >
                    Connect Wallet to Deposit
                  </button>

                  <!-- Case 2: Wallet connected -->
                  <button 
                    v-else
                    type="button" 
                    @click="handleSubmit"
                    :disabled="isSubmitting || !isValid"
                    class="w-full py-4 rounded-xl font-bold uppercase tracking-wider text-sm text-white bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove hover:opacity-90 active:scale-[0.98] disabled:opacity-50 disabled:scale-100 disabled:pointer-events-none transition transform duration-200"
                  >
                    {{ submitButtonText }}
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
import { ref, computed, onMounted, watch } from 'vue';
import { 
  Dialog, 
  DialogPanel, 
  DialogTitle, 
  TransitionChild, 
  TransitionRoot 
} from '@headlessui/vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import { signTransaction } from '@stellar/freighter-api';
import { signTransaction as lobstrSignTx } from '@lobstrco/signer-extension-api';
import { apiHeaders, getCookie, getNetwork, updateLoader } from '../utils/utils.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  isWalletConnected: { type: Boolean, default: false },
  walletKey: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'close', 'open-connect-wallet']);

// Inputs & state
const xlmInput = ref('');
const tkgInput = ref('');
const isReservesLoading = ref(false);
const isSubmitting = ref(false);

const reservesXlm = ref(0);
const reservesTkg = ref(0);
const poolRatio = ref(16); // default fallback ratio

const xlmBalance = ref(0);
const tkgBalance = ref(0);

// Close modal helper
function closeModal() {
  emit('update:modelValue', false);
  emit('close');
}

// Request parent to open the wallet connector modal
function triggerConnectWallet() {
  closeModal();
  emit('open-connect-wallet');
}

// Calculation ratio management
function handleInput(type) {
  const xVal = parseFloat(xlmInput.value);
  const tVal = parseFloat(tkgInput.value);

  if (type === 'xlm') {
    if (isNaN(xVal) || xVal <= 0) {
      tkgInput.value = '';
    } else {
      tkgInput.value = (xVal * poolRatio.value).toFixed(7);
    }
  } else {
    if (isNaN(tVal) || tVal <= 0) {
      xlmInput.value = '';
    } else {
      xlmInput.value = (tVal / poolRatio.value).toFixed(7);
    }
  }
}

// Validation helper
const isValid = computed(() => {
  const xVal = parseFloat(xlmInput.value);
  const tVal = parseFloat(tkgInput.value);
  
  if (isNaN(xVal) || xVal <= 0 || isNaN(tVal) || tVal <= 0) {
    return false;
  }
  
  if (xVal > xlmBalance.value || tVal > tkgBalance.value) {
    return false;
  }
  
  return true;
});

// Dynamic button text helper
const submitButtonText = computed(() => {
  if (isSubmitting.value) return 'Processing...';
  
  const xVal = parseFloat(xlmInput.value);
  const tVal = parseFloat(tkgInput.value);
  
  if (isNaN(xVal) || xVal <= 0 || isNaN(tVal) || tVal <= 0) {
    return 'Enter amounts';
  }
  
  if (xVal > xlmBalance.value) {
    return 'Insufficient XLM balance';
  }
  
  if (tVal > tkgBalance.value) {
    return 'Insufficient TKG balance';
  }
  
  return 'Add Liquidity';
});

// Load reserves and balances
async function fetchReservesAndBalances() {
  isReservesLoading.value = true;
  try {
    const params = {};
    if (props.isWalletConnected && props.walletKey) {
      params.wallet_address = props.walletKey;
    }

    const response = await axios.get('/api/global/lp/reserves', { 
      params,
      headers: apiHeaders() 
    });

    if (response.data.status === 'success') {
      reservesXlm.value = response.data.xlm_reserve;
      reservesTkg.value = response.data.tkg_reserve;
      poolRatio.value = response.data.ratio;

      if (props.isWalletConnected) {
        xlmBalance.value = response.data.user_xlm;
        tkgBalance.value = response.data.user_tkg;
      }
    }
  } catch (error) {
    console.error('Error loading LP reserves:', error);
  } finally {
    isReservesLoading.value = false;
  }
}

// Handle transaction signing and submission
async function handleSubmit() {
  if (!isValid.value || isSubmitting.value) return;

  isSubmitting.value = true;
  updateLoader('Preparing Transaction', 'Constructing Stellar operations...');

  try {
    // 1. Get Unsigned XDR from Backend
    const response = await axios.post('/api/global/lp/deposit', {
      wallet_address: props.walletKey,
      xlm_amount: parseFloat(xlmInput.value),
      tkg_amount: parseFloat(tkgInput.value)
    }, {
      headers: apiHeaders()
    });

    if (response.data.status !== 'success') {
      throw new Error(response.data.message || 'Failed to prepare deposit transaction.');
    }

    const unsignedXdr = response.data.unsigned_xdr;

    // 2. Sign XDR using user's wallet
    updateLoader('Awaiting Signature', 'Please approve the transaction in your wallet...');
    const signedXdr = await walletSign(unsignedXdr);

    // 3. Submit Signed XDR to Backend
    updateLoader('Depositing Liquidity', 'Submitting signed transaction to Horizon...');
    const submitResponse = await axios.post('/api/global/lp/submit', {
      signedXdr
    }, {
      headers: apiHeaders()
    });

    Swal.close();

    if (submitResponse.data.status === 'success') {
      Swal.fire({
        icon: 'success',
        title: 'Liquidity Added!',
        text: 'Your XLM and TKG tokens have been deposited into the liquidity pool.',
        confirmButtonColor: '#10B981',
      });
      
      closeModal();
      // Emit callback or reload page to sync lists
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Deposit Failed',
        text: submitResponse.data.message || 'Stellar network rejected the transaction.'
      });
    }

  } catch (error) {
    Swal.close();
    console.error('LP deposit submit error:', error);
    const apiError = error.response?.data?.message;
    Swal.fire({
      icon: 'error',
      title: 'Transaction Error',
      text: apiError || error.message || 'An unexpected error occurred during deposit.'
    });
  } finally {
    isSubmitting.value = false;
  }
}

// Universal wallet signer loader
async function walletSign(xdr) {
  const activeWallet = (localStorage.getItem('wallet_key') || '').toLowerCase();
  const net = await getNetwork();
  
  const freighterNet = net === 'testnet' ? 'TESTNET' : 'PUBLIC';
  const rabetNet = net === 'testnet' ? 'testnet' : 'mainnet';
  const albedoNet = net === 'testnet' ? 'testnet' : 'public';

  switch (activeWallet) {
    case 'freighter':
      return await signTransaction(xdr, freighterNet);
      
    case 'rabet':
      if (typeof window !== 'undefined' && window.rabet) {
        const result = await window.rabet.sign(xdr, rabetNet);
        return result.xdr;
      }
      throw new Error('Rabet extension not found.');
      
    case 'albedo':
      if (typeof window !== 'undefined' && window.albedo) {
        const res = await window.albedo.tx({ xdr, network: albedoNet });
        if (!res.signed_envelope_xdr) throw new Error('Albedo signature empty.');
        return res.signed_envelope_xdr;
      }
      throw new Error('Albedo API not found.');

    case 'xbull':
      const xbull = window.xBullSDK || window.xBull;
      if (!xbull) throw new Error('xBull extension not found.');
      await xbull.connect({ canRequestPublicKey: true, canRequestSign: true });
      return await xbull.signXDR(xdr);

    case 'lobstr':
      if (typeof lobstrSignTx === 'function') {
        return await lobstrSignTx(xdr);
      }
      if (typeof window !== 'undefined' && window.lobstrSignerExtensionApi) {
        return await window.lobstrSignerExtensionApi.signTransaction(xdr);
      }
      throw new Error('LOBSTR extension not found.');

    default:
      throw new Error('No connected wallet detected. Please reconnect.');
  }
}

// Fetch on mount / when modelValue becomes true
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    fetchReservesAndBalances();
    xlmInput.value = '';
    tkgInput.value = '';
  }
});

onMounted(() => {
  if (props.modelValue) {
    fetchReservesAndBalances();
  }
});
</script>
