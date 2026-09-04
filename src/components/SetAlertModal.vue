<template>
  <TransitionRoot appear :show="modelValue" as="template">
    <Dialog as="div" @close="closeModal" class="relative z-[90]">
      <!-- Backdrop -->
      <TransitionChild
        as="template"
        enter="duration-200 ease-out"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="duration-150 ease-in"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/70 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center">
          <TransitionChild
            as="template"
            enter="duration-200 ease-out"
            enter-from="opacity-0 scale-95"
            enter-to="opacity-100 scale-100"
            leave="duration-150 ease-in"
            leave-from="opacity-100 scale-100"
            leave-to="opacity-0 scale-95"
          >
            <DialogPanel class="set-alert-dialog w-full max-w-md transform overflow-hidden rounded-2xl bg-theme-panel border border-theme-line p-6 text-left align-middle shadow-2xl transition-all relative">
              <!-- Header -->
              <div class="flex items-center justify-between pb-4 border-b border-theme-line">
                <div>
                  <DialogTitle as="h3" class="alert-modal-title text-base font-bold text-theme-ink tracking-tight">
                    <span>Set Price Alert</span>
                  </DialogTitle>
                  <p class="alert-modal-desc text-xs text-theme-dim font-sans mt-0.5">
                    Get notified when price or volatility triggers hit your targets.
                  </p>
                </div>

                <button @click="closeModal" class="p-1.5 rounded-lg text-theme-dim hover:text-theme-ink hover:bg-theme-panel2 transition focus:outline-none cursor-pointer">
                  <X class="w-4 h-4" />
                </button>
              </div>

              <!-- Unconnected Wallet Guard -->
              <div v-if="!walletKey" class="py-8 text-center space-y-4">
                <div class="w-12 h-12 mx-auto rounded-full bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-500 dark:text-cyan-400">
                  <Wallet class="w-6 h-6" />
                </div>
                <div class="space-y-1">
                  <h4 class="text-sm font-bold text-theme-ink">Wallet Connection Required</h4>
                  <p class="text-xs text-theme-dim max-w-xs mx-auto">
                    Price alerts are tied to your Stellar wallet address to sync across devices and trigger web push alerts.
                  </p>
                </div>
                <button
                  @click="handleConnectWallet"
                  class="px-5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 hover:scale-[1.02] active:scale-[0.98] transition-all shadow-lg shadow-cyan-500/10 inline-flex items-center gap-2 cursor-pointer"
                >
                  <Wallet class="w-4 h-4" />
                  <span>Connect Wallet</span>
                </button>
              </div>

              <!-- Alert Creation Form -->
              <div v-else class="mt-4 space-y-4">
                <!-- Current Asset Live Ref -->
                <div class="p-3 bg-theme-panel2 border border-theme-line rounded-xl flex items-center justify-between">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-theme-panel flex items-center justify-center font-bold text-xs text-cyan-600 dark:text-cyan-400 font-mono border border-theme-line overflow-hidden flex-shrink-0">
                      <img v-if="token?.image || token?.logo || token?.token_image" :src="token.image || token.logo || token.token_image" class="w-full h-full object-cover" />
                      <span v-else>{{ (token?.asset_code || '?').substring(0, 2) }}</span>
                    </div>
                    <div>
                      <div class="alert-token-name text-xs font-bold text-theme-ink tracking-tight">{{ token?.name || token?.asset_code }}</div>
                      <div class="alert-market-label text-[10px] font-sans font-medium text-theme-dim uppercase tracking-wider mt-0.5">Current Market Price</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="text-xs font-mono font-bold text-theme-ink flex items-center justify-end gap-1">
                      <span class="alert-live-xlm">{{ formatReferencePrice(currentXlmPrice) }}</span>
                      <span class="text-[10px] text-cyan-600 dark:text-cyan-400 font-bold uppercase">XLM</span>
                    </div>
                    <div class="text-[10px] font-mono text-theme-dim flex items-center justify-end gap-1 mt-0.5">
                      <span class="alert-live-usd">${{ formatReferencePrice(currentPrice) }}</span>
                      <span :class="currentChange >= 0 ? 'text-emerald-500 dark:text-emerald-400 font-bold' : 'text-rose-500 dark:text-rose-400 font-bold'">
                        ({{ currentChange >= 0 ? '+' : '' }}{{ Number(currentChange).toFixed(2) }}%)
                      </span>
                    </div>
                  </div>
                </div>

                <!-- Condition Type Selector Tabs -->
                <div class="space-y-1.5">
                  <label class="alert-label alert-condition-label text-[11px] font-extrabold text-slate-950 dark:text-slate-200 uppercase tracking-wider">Alert Condition</label>
                  <div class="grid grid-cols-2 gap-2">
                    <button
                      type="button"
                      @click="setConditionType('price_above')"
                      :class="[
                        conditionType === 'price_above'
                          ? 'is-active bg-emerald-500/15 border-emerald-500/50 text-slate-950 dark:text-white dark:bg-emerald-500/20 dark:border-emerald-500/50 shadow-sm font-black'
                          : 'bg-slate-100/90 dark:bg-theme-panel2 border-slate-200 dark:border-theme-line text-slate-900 dark:text-slate-200 hover:text-black dark:hover:text-white font-extrabold',
                        'alert-condition-btn p-2.5 rounded-xl border text-xs flex items-center gap-2 transition text-left cursor-pointer'
                      ]"
                    >
                      <TrendingUp class="w-3.5 h-3.5 flex-shrink-0 text-emerald-600 dark:text-emerald-400" />
                      <span class="truncate">Price Rises Above</span>
                    </button>

                    <button
                      type="button"
                      @click="setConditionType('price_below')"
                      :class="[
                        conditionType === 'price_below'
                          ? 'is-active bg-rose-500/15 border-rose-500/50 text-slate-950 dark:text-white dark:bg-rose-500/20 dark:border-rose-500/50 shadow-sm font-black'
                          : 'bg-slate-100/90 dark:bg-theme-panel2 border-slate-200 dark:border-theme-line text-slate-900 dark:text-slate-200 hover:text-black dark:hover:text-white font-extrabold',
                        'alert-condition-btn p-2.5 rounded-xl border text-xs flex items-center gap-2 transition text-left cursor-pointer'
                      ]"
                    >
                      <TrendingDown class="w-3.5 h-3.5 flex-shrink-0 text-rose-600 dark:text-rose-400" />
                      <span class="truncate">Price Drops Below</span>
                    </button>

                    <button
                      type="button"
                      @click="setConditionType('pct_change_up')"
                      :class="[
                        conditionType === 'pct_change_up'
                          ? 'is-active bg-cyan-500/15 border-cyan-500/50 text-slate-950 dark:text-white dark:bg-cyan-500/20 dark:border-cyan-500/50 shadow-sm font-black'
                          : 'bg-slate-100/90 dark:bg-theme-panel2 border-slate-200 dark:border-theme-line text-slate-900 dark:text-slate-200 hover:text-black dark:hover:text-white font-extrabold',
                        'alert-condition-btn p-2.5 rounded-xl border text-xs flex items-center gap-2 transition text-left cursor-pointer'
                      ]"
                    >
                      <Percent class="w-3.5 h-3.5 flex-shrink-0 text-cyan-600 dark:text-cyan-400" />
                      <span class="truncate">24h Gain Above +%</span>
                    </button>

                    <button
                      type="button"
                      @click="setConditionType('pct_change_down')"
                      :class="[
                        conditionType === 'pct_change_down'
                          ? 'is-active bg-purple-500/15 border-purple-500/50 text-slate-950 dark:text-white dark:bg-purple-500/20 dark:border-purple-500/50 shadow-sm font-black'
                          : 'bg-slate-100/90 dark:bg-theme-panel2 border-slate-200 dark:border-theme-line text-slate-900 dark:text-slate-200 hover:text-black dark:hover:text-white font-extrabold',
                        'alert-condition-btn p-2.5 rounded-xl border text-xs flex items-center gap-2 transition text-left cursor-pointer'
                      ]"
                    >
                      <Percent class="w-3.5 h-3.5 flex-shrink-0 text-purple-600 dark:text-purple-400" />
                      <span class="truncate">24h Drop Below -%</span>
                    </button>
                  </div>
                </div>

                <!-- Price Denomination Selector (XLM vs USD) -->
                <div v-if="isPriceCondition" class="p-2.5 rounded-xl bg-slate-50 dark:bg-theme-panel2 border border-slate-200 dark:border-theme-line flex items-center justify-between gap-2">
                  <div>
                    <div class="alert-denom-title text-xs font-black text-slate-950 dark:text-slate-100">Price Denomination</div>
                    <div class="alert-denom-desc text-[10.5px] text-slate-700 dark:text-slate-400 font-semibold mt-0.5">Set your price target in XLM or USD</div>
                  </div>
                  <div class="flex items-center p-0.5 bg-white dark:bg-theme-panel border border-slate-200 dark:border-theme-line rounded-lg gap-1">
                    <button
                      type="button"
                      @click="setCurrency('xlm')"
                      :class="[
                        selectedCurrency === 'xlm'
                          ? 'is-active bg-cyan-500/20 text-slate-950 dark:text-white dark:bg-cyan-500/25 border-cyan-500/50 dark:border-cyan-500/60 shadow-xs font-black'
                          : 'text-slate-800 dark:text-slate-300 hover:text-black dark:hover:text-white border-transparent font-extrabold',
                        'alert-currency-btn px-2.5 py-1 rounded-md text-xs font-mono border transition cursor-pointer'
                      ]"
                    >
                      XLM
                    </button>
                    <button
                      type="button"
                      @click="setCurrency('usd')"
                      :class="[
                        selectedCurrency === 'usd'
                          ? 'is-active bg-cyan-500/20 text-slate-950 dark:text-white dark:bg-cyan-500/25 border-cyan-500/50 dark:border-cyan-500/60 shadow-xs font-black'
                          : 'text-slate-800 dark:text-slate-300 hover:text-black dark:hover:text-white border-transparent font-extrabold',
                        'alert-currency-btn px-2.5 py-1 rounded-md text-xs font-mono border transition cursor-pointer'
                      ]"
                    >
                      USD ($)
                    </button>
                  </div>
                </div>

                <!-- Target Input & Quick Percent Selectors -->
                <div class="space-y-1.5">
                  <div class="flex items-center justify-between">
                    <label class="alert-label alert-target-label text-[11px] font-extrabold text-slate-950 dark:text-slate-200 uppercase tracking-wider">
                      {{ isPriceCondition ? `Target Price (${selectedCurrency.toUpperCase()})` : 'Target % Threshold' }}
                    </label>
                    <div class="text-[10.5px] font-mono flex items-center gap-1">
                      <span class="alert-current-label text-slate-800 dark:text-slate-300 font-bold">Current:</span>
                      <span v-if="isPriceCondition" class="alert-current-val font-black text-slate-950 dark:text-cyan-400">
                        {{ selectedCurrency === 'xlm' ? `${formatReferencePrice(currentXlmPrice)} XLM` : `$${formatReferencePrice(currentPrice)}` }}
                      </span>
                      <span v-else class="alert-current-val font-black" :class="Number(currentChange) >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'">
                        {{ Number(currentChange) >= 0 ? '+' : '' }}{{ Number(currentChange).toFixed(2) }}%
                      </span>
                    </div>
                  </div>

                  <div class="relative flex items-center bg-slate-50 dark:bg-theme-panel2 border border-slate-200 dark:border-theme-line focus-within:border-cyan-500/50 focus-within:ring-1 focus-within:ring-cyan-500/30 rounded-xl px-3 py-2.5 transition">
                    <span class="alert-input-prefix text-xs font-mono font-black text-slate-950 dark:text-slate-200 mr-1.5 select-none uppercase">
                      {{ isPriceCondition ? (selectedCurrency === 'usd' ? '$' : 'XLM') : '%' }}
                    </span>
                    <input
                      v-model="targetValue"
                      type="number"
                      step="any"
                      placeholder="0.00"
                      class="alert-target-input bg-transparent border-0 outline-none text-sm font-mono font-black text-slate-950 dark:text-white placeholder-slate-400 w-full p-0 focus:ring-0"
                    />
                  </div>

                  <!-- Quick Preset Buttons -->
                  <div class="flex items-center gap-1.5 pt-1 overflow-x-auto custom-scrollbar">
                    <button
                      v-for="preset in availablePresets"
                      :key="preset.label"
                      type="button"
                      @click="applyPreset(preset.multiplier)"
                      class="alert-preset-btn px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-theme-panel2 dark:hover:bg-cyan-500/10 border border-slate-200 dark:border-theme-line hover:border-slate-300 dark:hover:border-cyan-500/30 text-[10.5px] font-mono font-black text-slate-950 dark:text-slate-100 hover:text-black dark:hover:text-cyan-400 transition flex-shrink-0 cursor-pointer shadow-2xs"
                    >
                      {{ preset.label }}
                    </button>
                  </div>
                </div>

                <!-- Notification Delivery Channels -->
                <div class="space-y-2 pt-2 border-t border-slate-200 dark:border-theme-line">
                  <label class="alert-label alert-channels-label text-[11px] font-extrabold text-slate-950 dark:text-slate-200 uppercase tracking-wider">Delivery Channels</label>
                  
                  <div class="space-y-2">
                    <!-- Browser Web Push -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-theme-panel2 border border-slate-200 dark:border-theme-line">
                      <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-cyan-500/10 flex items-center justify-center text-cyan-600 dark:text-cyan-400 flex-shrink-0">
                          <Globe class="w-3.5 h-3.5" />
                        </div>
                        <div>
                          <div class="alert-channel-title text-xs font-black text-slate-950 dark:text-slate-100">Browser Push Notifications</div>
                          <p class="alert-channel-desc text-[10px] text-slate-700 dark:text-slate-400 font-medium">Receive desktop/mobile system popup alerts when price hits target</p>
                        </div>
                      </div>

                      <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                        <input
                          type="checkbox"
                          v-model="channelPush"
                          @change="handlePushToggle"
                          class="sr-only peer"
                        />
                        <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:shadow-sm after:transition-all peer-checked:bg-cyan-500 peer-checked:border-cyan-500"></div>
                      </label>
                    </div>

                    <!-- On-site Notification Bell -->
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 dark:bg-theme-panel2 border border-slate-200 dark:border-theme-line">
                      <div class="flex items-center gap-2.5">
                        <div class="w-7 h-7 rounded-lg bg-purple-500/10 flex items-center justify-center text-purple-600 dark:text-purple-400">
                          <Bell class="w-3.5 h-3.5" />
                        </div>
                        <div>
                          <div class="alert-channel-title text-xs font-black text-slate-950 dark:text-slate-100">On-site Notification Bell</div>
                          <p class="alert-channel-desc text-[10px] text-slate-700 dark:text-slate-400 font-medium">Unread badge and alert message inside TokenGlade header</p>
                        </div>
                      </div>

                      <label class="relative inline-flex items-center cursor-pointer">
                        <input
                          type="checkbox"
                          v-model="channelOnsite"
                          class="sr-only peer"
                        />
                        <div class="w-9 h-5 bg-slate-300 dark:bg-slate-700 border border-slate-300 dark:border-slate-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:shadow-sm after:transition-all peer-checked:bg-purple-600 peer-checked:border-purple-600"></div>
                      </label>
                    </div>
                  </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-3">
                  <button
                    type="button"
                    :disabled="isSubmitting"
                    @click="handleSubmitAlert"
                    class="w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] disabled:opacity-50 disabled:pointer-events-none transition-all shadow-lg shadow-cyan-500/15 flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <span v-if="isSubmitting" class="animate-spin rounded-full h-4 w-4 border-2 border-white border-t-transparent"></span>
                    <span v-else>Create Price Alert</span>
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
import { ref, computed, watch, nextTick } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import {
  BellRing,
  Bell,
  X,
  Wallet,
  TrendingUp,
  TrendingDown,
  Percent,
  Globe
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import { createAlert, registerBrowserPush, isPushSupported } from '@/utils/alerts.js';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  token: {
    type: Object,
    default: () => ({})
  },
  walletKey: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['update:modelValue', 'open-wallet', 'alert-created']);

const conditionType = ref('price_above');
const selectedCurrency = ref('xlm');
const pushPermissionStatus = ref(typeof Notification !== 'undefined' ? Notification.permission : 'default');
const channelPush = ref(isPushSupported && (typeof Notification !== 'undefined' ? Notification.permission === 'granted' : false));
const channelOnsite = ref(true);
const targetValue = ref('');
const isSubmitting = ref(false);

const currentPrice = computed(() => {
  return parseFloat(props.token?.usd_price || 0);
});

const currentXlmPrice = computed(() => {
  return parseFloat(props.token?.xlm_price || 0);
});

const activeReferencePrice = computed(() => {
  return selectedCurrency.value === 'xlm'
    ? (currentXlmPrice.value > 0 ? currentXlmPrice.value : (currentPrice.value > 0 ? currentPrice.value / 0.18 : 0))
    : currentPrice.value;
});

const currentChange = computed(() => {
  return parseFloat(props.token?.price_change_24h || 0);
});

const isPriceCondition = computed(() => {
  return conditionType.value === 'price_above' || conditionType.value === 'price_below';
});

const availablePresets = computed(() => {
  if (conditionType.value === 'price_above') {
    return [
      { label: '+5%', multiplier: 1.05 },
      { label: '+10%', multiplier: 1.10 },
      { label: '+25%', multiplier: 1.25 },
      { label: '+50%', multiplier: 1.50 },
      { label: '+100%', multiplier: 2.00 }
    ];
  } else if (conditionType.value === 'price_below') {
    return [
      { label: '-5%', multiplier: 0.95 },
      { label: '-10%', multiplier: 0.90 },
      { label: '-25%', multiplier: 0.75 },
      { label: '-50%', multiplier: 0.50 }
    ];
  } else if (conditionType.value === 'pct_change_up') {
    return [
      { label: '+5%', multiplier: 5 },
      { label: '+10%', multiplier: 10 },
      { label: '+20%', multiplier: 20 },
      { label: '+50%', multiplier: 50 }
    ];
  } else {
    return [
      { label: '-5%', multiplier: -5 },
      { label: '-10%', multiplier: -10 },
      { label: '-20%', multiplier: -20 },
      { label: '-50%', multiplier: -50 }
    ];
  }
});

function formatReferencePrice(val) {
  const p = parseFloat(val || 0);
  if (p === 0) return '0.00';
  if (p < 0.0001) return p.toFixed(7);
  if (p < 0.01) return p.toFixed(6);
  if (p < 1) return p.toFixed(4);
  return p.toFixed(2);
}

function setCurrency(curr) {
  selectedCurrency.value = curr;
  if (conditionType.value === 'price_above') {
    applyPreset(1.05);
  } else if (conditionType.value === 'price_below') {
    applyPreset(0.95);
  }
}

function setConditionType(type) {
  conditionType.value = type;
  // Initialize default suggested target based on type
  if (type === 'price_above') {
    applyPreset(1.05);
  } else if (type === 'price_below') {
    applyPreset(0.95);
  } else if (type === 'pct_change_up') {
    targetValue.value = '10';
  } else if (type === 'pct_change_down') {
    targetValue.value = '-10';
  }
}

function applyPreset(multiplier) {
  if (isPriceCondition.value) {
    const base = activeReferencePrice.value;
    if (base > 0) {
      const calc = base * multiplier;
      targetValue.value = calc < 0.0001 ? calc.toFixed(7) : (calc < 1 ? calc.toFixed(6) : calc.toFixed(4));
    } else {
      targetValue.value = '';
    }
  } else {
    targetValue.value = String(multiplier);
  }
}

async function handlePushToggle() {
  if (!isPushSupported) return;
  if (channelPush.value && props.walletKey) {
    if (typeof Notification !== 'undefined' && Notification.permission !== 'granted') {
      const registered = await registerBrowserPush(props.walletKey);
      pushPermissionStatus.value = Notification.permission;
      if (!registered && Notification.permission === 'denied') {
        channelPush.value = false;
        Swal.fire({
          icon: 'warning',
          title: 'Notifications Blocked',
          text: 'Browser push notifications are currently blocked in your browser settings. Please allow notifications in site permissions.',
          confirmButtonColor: '#06b6d4'
        });
      }
    }
  }
}

function closeModal() {
  emit('update:modelValue', false);
}

function handleConnectWallet() {
  closeModal();
  emit('open-wallet');
}

async function handleSubmitAlert() {
  const target = parseFloat(targetValue.value);
  if (isNaN(target) || (isPriceCondition.value && target <= 0)) {
    Swal.fire({
      icon: 'error',
      title: 'Invalid Target',
      text: 'Please enter a valid numeric target for this price alert.',
      confirmButtonColor: '#06b6d4'
    });
    return;
  }

  const channels = [];
  if (channelPush.value && isPushSupported) channels.push('push');
  if (channelOnsite.value) channels.push('onsite');

  if (channels.length === 0) {
    Swal.fire({
      icon: 'warning',
      title: 'Channel Required',
      text: 'Please enable at least one notification delivery channel (Browser Push or On-site Bell).',
      confirmButtonColor: '#06b6d4'
    });
    return;
  }

  // If push is selected and supported, attempt subscription safely
  if (channelPush.value && isPushSupported && props.walletKey) {
    try {
      await registerBrowserPush(props.walletKey);
    } catch (pushErr) {
      console.warn('Non-fatal: Push subscription failed:', pushErr);
    }
  }

  isSubmitting.value = true;
  try {
    const res = await createAlert({
      wallet_address: props.walletKey,
      asset_code: props.token.asset_code,
      asset_issuer: props.token.issuer || props.token.asset_issuer,
      condition_type: conditionType.value,
      condition_value: target,
      currency: selectedCurrency.value,
      channels: channels,
      reference_price_usd: currentPrice.value,
      reference_price_xlm: currentXlmPrice.value
    });

    if (res.status === 'success') {
      const isLight = document.documentElement.classList.contains('light');
      Swal.fire({
        icon: 'success',
        title: '<span style="font-family: inherit; font-weight: 800; font-size: 1.15rem; letter-spacing: -0.02em;">Price Alert Created!</span>',
        html: `
          <div style="font-family: inherit; font-size: 0.875rem; color: ${isLight ? '#475569' : '#94A3B8'}; line-height: 1.6; margin-top: 6px;">
            You will be notified when <strong style="color: ${isLight ? '#0284c7' : '#38bdf8'}; font-weight: 700; font-family: ui-monospace, monospace; padding: 1px 4px; border-radius: 4px; background: ${isLight ? '#e0f2fe' : 'rgba(56,189,248,0.15)'};">${props.token.asset_code}</strong> triggers your target condition in <strong style="color: ${isLight ? '#0284c7' : '#38bdf8'}; font-weight: 700; font-family: ui-monospace, monospace; padding: 1px 4px; border-radius: 4px; background: ${isLight ? '#e0f2fe' : 'rgba(56,189,248,0.15)'};">${selectedCurrency.value.toUpperCase()}</strong>.
          </div>
        `,
        confirmButtonColor: '#06b6d4',
        timer: 2800,
        showConfirmButton: false,
        background: isLight ? '#FFFFFF' : '#0B0F17',
        color: isLight ? '#0F172A' : '#F8FAFC',
        customClass: {
          popup: 'rounded-2xl border border-slate-200 dark:border-slate-800 shadow-2xl'
        }
      });
      emit('alert-created', res.alert);
      closeModal();
    } else {
      Swal.fire('Error', res.message || 'Failed to create price alert.', 'error');
    }
  } catch (err) {
    console.error('Error creating alert:', err);
    Swal.fire('Error', err.response?.data?.message || 'Failed to create price alert.', 'error');
  } finally {
    isSubmitting.value = false;
  }
}

watch(
  () => [props.modelValue, props.token?.asset_code, props.token?.issuer || props.token?.asset_issuer, props.token?.xlm_price, props.token?.usd_price],
  ([isOpen]) => {
    if (isOpen) {
      if (typeof Notification !== 'undefined') {
        pushPermissionStatus.value = Notification.permission;
        if (!isPushSupported || Notification.permission === 'denied') {
          channelPush.value = false;
        } else if (Notification.permission === 'granted') {
          channelPush.value = true;
        }
      } else if (!isPushSupported) {
        channelPush.value = false;
      }
      conditionType.value = 'price_above';
      selectedCurrency.value = 'xlm';
      nextTick(() => {
        applyPreset(1.05);
      });
    }
  },
  { immediate: true }
);
</script>

<style scoped>
/* LIGHT THEME EXPLICIT TEXT DARKENING */
:global(html.light) .set-alert-dialog {
  color: #0F172A !important;
}

:global(html.light) .alert-modal-title {
  color: #0F172A !important;
}

:global(html.light) .alert-modal-desc {
  color: #475569 !important;
}

:global(html.light) .alert-token-name {
  color: #0F172A !important;
}

:global(html.light) .alert-market-label {
  color: #475569 !important;
}

:global(html.light) .alert-live-xlm {
  color: #0F172A !important;
}

:global(html.light) .alert-live-usd {
  color: #475569 !important;
}

:global(html.light) .alert-label,
:global(html.light) .alert-condition-label,
:global(html.light) .alert-target-label,
:global(html.light) .alert-channels-label {
  color: #0F172A !important;
  font-weight: 800 !important;
}

:global(html.light) .alert-condition-btn {
  color: #0F172A !important;
}

:global(html.light) .alert-condition-btn span {
  color: #0F172A !important;
  font-weight: 800 !important;
}

:global(html.light) .alert-condition-btn.is-active span {
  color: #020617 !important;
  font-weight: 900 !important;
}

:global(html.light) .alert-denom-title {
  color: #0F172A !important;
  font-weight: 900 !important;
}

:global(html.light) .alert-denom-desc {
  color: #334155 !important;
  font-weight: 600 !important;
}

:global(html.light) .alert-currency-btn {
  color: #1E293B !important;
  font-weight: 800 !important;
}

:global(html.light) .alert-currency-btn.is-active {
  color: #020617 !important;
  font-weight: 900 !important;
}

:global(html.light) .alert-current-label {
  color: #1E293B !important;
  font-weight: 700 !important;
}

:global(html.light) .alert-current-val {
  color: #020617 !important;
  font-weight: 900 !important;
}

:global(html.light) .alert-input-prefix {
  color: #0F172A !important;
  font-weight: 900 !important;
}

:global(html.light) .alert-target-input {
  color: #020617 !important;
  font-weight: 900 !important;
}

:global(html.light) .alert-preset-btn {
  color: #0F172A !important;
  font-weight: 900 !important;
  background-color: #F1F5F9 !important;
  border-color: #CBD5E1 !important;
}

:global(html.light) .alert-preset-btn:hover {
  color: #000000 !important;
  background-color: #E2E8F0 !important;
}

:global(html.light) .alert-channel-title {
  color: #0F172A !important;
  font-weight: 900 !important;
}

:global(html.light) .alert-channel-desc {
  color: #334155 !important;
  font-weight: 600 !important;
}

/* DARK THEME EXPLICIT LUMINOUS TEXT */
:global(html:not(.light)) .alert-condition-btn span,
:global(html.dark) .alert-condition-btn span {
  color: #E2E8F0 !important;
}

:global(html:not(.light)) .alert-condition-btn.is-active span,
:global(html.dark) .alert-condition-btn.is-active span {
  color: #FFFFFF !important;
}

:global(html:not(.light)) .alert-currency-btn,
:global(html.dark) .alert-currency-btn {
  color: #CBD5E1 !important;
}

:global(html:not(.light)) .alert-currency-btn.is-active,
:global(html.dark) .alert-currency-btn.is-active {
  color: #FFFFFF !important;
}

:global(html:not(.light)) .alert-target-input,
:global(html.dark) .alert-target-input {
  color: #FFFFFF !important;
}

:global(html:not(.light)) .alert-preset-btn,
:global(html.dark) .alert-preset-btn {
  color: #F1F5F9 !important;
}

:global(html:not(.light)) .alert-channel-title,
:global(html.dark) .alert-channel-title {
  color: #F8FAFC !important;
}

:global(html:not(.light)) .alert-channel-desc,
:global(html.dark) .alert-channel-desc {
  color: #94A3B8 !important;
}
</style>
