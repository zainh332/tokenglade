<template>
  <div class="flex items-center gap-1.5 sm:gap-2">
    <!-- 1. NOTIFICATION BELL BUTTON & DROPDOWN (Desktop / Tablet) -->
    <div class="relative hidden sm:block" ref="bellContainer">
      <button
        @click="toggleBell"
        class="relative p-2 rounded-lg border border-theme-line bg-theme-panel hover:bg-theme-panel2 text-theme-dim hover:text-theme-ink transition-all duration-200 focus:outline-none flex items-center justify-center cursor-pointer shadow-sm hover:scale-[1.03] active:scale-[0.97]"
        aria-label="Price Notifications"
        title="Price Alert Notifications"
      >
        <Bell class="w-4 h-4" :class="unreadCount > 0 ? 'text-cyan-500 dark:text-cyan-400 animate-bounce-short' : 'text-theme-dim'" />
        
        <!-- Unread Badge -->
        <span
          v-if="unreadCount > 0"
          class="absolute -top-1 -right-1 flex h-4 min-w-[16px] px-1 items-center justify-center rounded-full bg-rose-500 text-[9.5px] font-mono font-black text-white shadow-lg shadow-rose-500/30"
        >
          {{ unreadCount > 99 ? '99+' : unreadCount }}
        </span>
      </button>

      <!-- Notifications Dropdown -->
      <transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="isBellOpen"
          class="absolute right-0 mt-2 w-[min(340px,calc(100vw-24px))] sm:w-[380px] rounded-2xl bg-theme-panel border border-theme-line shadow-2xl z-[90] overflow-hidden"
        >
          <!-- Bell Header -->
          <div class="flex items-center justify-between px-4 py-3 border-b border-theme-line bg-theme-panel2">
            <div class="flex items-center gap-2">
              <BellRing class="w-4 h-4 text-cyan-500 dark:text-cyan-400" />
              <span class="text-xs font-bold text-theme-ink uppercase tracking-wider">Alert Notifications</span>
              <span v-if="unreadCount > 0" class="px-1.5 py-0.5 rounded text-[10px] font-mono font-bold bg-cyan-500/15 text-cyan-600 dark:text-cyan-400 border border-cyan-500/30">
                {{ unreadCount }} new
              </span>
            </div>

            <button
              v-if="unreadCount > 0"
              @click="handleMarkAllRead"
              class="text-[11px] font-mono font-bold text-cyan-600 dark:text-cyan-400 hover:opacity-80 transition cursor-pointer"
            >
              Mark all read
            </button>
          </div>

          <!-- Notification Items List -->
          <div class="max-h-[340px] overflow-y-auto custom-scrollbar divide-y divide-theme-line">
            <div v-if="notifications.length === 0" class="p-8 text-center space-y-2">
              <div class="w-10 h-10 mx-auto rounded-full bg-theme-panel2 border border-theme-line flex items-center justify-center text-theme-dim">
                <BellOff class="w-5 h-5" />
              </div>
              <p class="text-xs text-theme-ink font-semibold">No alert notifications yet.</p>
              <p class="text-[10px] text-theme-dim font-mono">Triggered price alerts will appear here in real-time.</p>
            </div>

            <div
              v-for="item in notifications"
              :key="item.id"
              @click="handleNotificationClick(item)"
              :class="[
                !item.is_read ? 'bg-cyan-500/5 hover:bg-cyan-500/10' : 'hover:bg-theme-panel2',
                'p-3.5 flex items-start gap-3 cursor-pointer transition'
              ]"
            >
              <div class="w-8 h-8 rounded-xl bg-theme-panel2 border border-theme-line flex items-center justify-center flex-shrink-0 text-cyan-600 dark:text-cyan-400 font-mono font-bold text-xs overflow-hidden">
                <img v-if="item.token_image || item.image || item.logo || item.data?.token_image || item.data?.image" :src="item.token_image || item.image || item.logo || item.data?.token_image || item.data?.image" class="w-full h-full object-cover" />
                <span v-else>{{ (item.data?.asset_code || item.asset_code || 'TK').substring(0, 2) }}</span>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-1">
                  <span class="text-xs font-bold text-theme-ink truncate">{{ item.title }}</span>
                  <span class="text-[9.5px] font-mono text-theme-dim flex-shrink-0">{{ item.time_ago || 'just now' }}</span>
                </div>
                <p class="text-[11px] text-theme-dim font-sans mt-0.5 line-clamp-2 leading-relaxed">
                  {{ item.message }}
                </p>
                <div class="flex items-center gap-2 mt-1.5">
                  <span class="text-[10px] font-mono text-cyan-600 dark:text-cyan-400 font-semibold">View Token Insight →</span>
                  <span v-if="!item.is_read" class="w-1.5 h-1.5 rounded-full bg-cyan-500 dark:bg-cyan-400"></span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>

    <!-- 2. WATCHLIST & ALERTS COMBINED DROPDOWN -->
    <div class="relative" ref="dropdownContainer">
      <button
        @click="toggleDropdown"
        class="relative px-2 sm:px-3 py-1 sm:py-1.5 rounded-lg border border-theme-line bg-theme-panel hover:bg-theme-panel2 text-theme-dim hover:text-theme-ink transition-all duration-200 focus:outline-none flex items-center gap-1.5 sm:gap-2 cursor-pointer shadow-sm hover:scale-[1.02] active:scale-[0.98]"
        aria-label="Watchlist and Alerts"
      >
        <!-- Star icon with active watchlist count -->
        <div class="flex items-center gap-1">
          <Star class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400 fill-amber-500 dark:fill-amber-400" />
          <span class="text-[11px] sm:text-xs font-extrabold uppercase tracking-wider text-theme-ink font-mono">
            {{ watchlist.length }}
          </span>
        </div>

        <span class="text-theme-line text-xs">|</span>

        <!-- Bell icon with active alerts count -->
        <div class="flex items-center gap-1">
          <BellRing class="w-3.5 h-3.5 text-cyan-500 dark:text-cyan-400" />
          <span class="text-[11px] sm:text-xs font-extrabold uppercase tracking-wider text-theme-ink font-mono">
            {{ activeAlertsCount }}
          </span>
        </div>

        <ChevronDown class="w-3 h-3 text-theme-dim transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''" />
      </button>

      <!-- Panel Dropdown -->
      <transition
        enter-active-class="transition duration-150 ease-out"
        enter-from-class="transform scale-95 opacity-0"
        enter-to-class="transform scale-100 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="transform scale-100 opacity-100"
        leave-to-class="transform scale-95 opacity-0"
      >
        <div
          v-if="isOpen"
          class="absolute right-0 mt-2 w-[min(340px,calc(100vw-24px))] sm:w-[420px] rounded-2xl bg-theme-panel border border-theme-line shadow-2xl z-[90] overflow-hidden"
        >
          <!-- Combined Tab Navigation Bar -->
          <div class="flex items-center p-1.5 bg-theme-panel2 border-b border-theme-line gap-1.5">
            <button
              @click="activeTab = 'watchlist'"
              :class="[
                activeTab === 'watchlist'
                  ? 'bg-amber-500/15 border-amber-500/40 text-slate-900 dark:text-amber-300 font-extrabold shadow-sm tab-watchlist-active'
                  : 'text-theme-dim hover:text-theme-ink border-transparent font-bold',
                'flex-1 py-2 px-3 rounded-xl border text-xs font-sans flex items-center justify-center gap-1.5 transition cursor-pointer'
              ]"
            >
              <Star class="w-3.5 h-3.5 text-amber-500 dark:text-amber-400 fill-amber-500 dark:fill-amber-400 flex-shrink-0" />
              <span class="tab-label">Watchlist</span>
              <span
                class="tab-badge px-1.5 py-0.2 rounded-full text-[10px] font-mono font-black"
                :class="activeTab === 'watchlist' ? 'bg-amber-500/20 text-slate-900 dark:text-amber-300' : 'bg-theme-panel border border-theme-line text-slate-900 dark:text-theme-dim'"
              >
                {{ watchlist.length }}
              </span>
            </button>

            <button
              @click="activeTab = 'alerts'"
              :class="[
                activeTab === 'alerts'
                  ? 'bg-cyan-500/15 border-cyan-500/40 text-slate-900 dark:text-cyan-400 font-extrabold shadow-sm tab-alerts-active'
                  : 'text-theme-dim hover:text-theme-ink border-transparent font-bold',
                'flex-1 py-2 px-3 rounded-xl border text-xs font-sans flex items-center justify-center gap-1.5 transition cursor-pointer'
              ]"
            >
              <BellRing class="w-3.5 h-3.5 text-cyan-600 dark:text-cyan-400 flex-shrink-0" />
              <span class="tab-label">Price Alerts</span>
              <span
                class="tab-badge px-1.5 py-0.2 rounded-full text-[10px] font-mono font-black"
                :class="activeTab === 'alerts' ? 'bg-cyan-500/20 text-slate-900 dark:text-cyan-400' : 'bg-theme-panel border border-theme-line text-slate-900 dark:text-theme-dim'"
              >
                {{ activeAlertsCount }}
              </span>
            </button>
          </div>

          <!-- TAB 1: WATCHLIST CONTENT -->
          <div v-if="activeTab === 'watchlist'" class="max-h-[380px] overflow-y-auto custom-scrollbar divide-y divide-theme-line">
            <div v-if="watchlist.length === 0" class="p-8 text-center space-y-3">
              <div class="w-10 h-10 mx-auto rounded-full bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-500 dark:text-amber-400">
                <Star class="w-5 h-5" />
              </div>
              <div class="space-y-1">
                <h4 class="text-xs font-bold text-theme-ink">Your Watchlist is Empty</h4>
                <p class="text-[11px] text-theme-dim max-w-xs mx-auto leading-relaxed">
                  Click the star icon (★) on any token insight page to track live prices and market caps directly here.
                </p>
              </div>
            </div>

            <div
              v-for="item in enrichedWatchlist"
              :key="`${item.asset_code}_${item.asset_issuer}`"
              class="p-3 hover:bg-theme-panel2 transition flex items-center justify-between gap-3 group"
            >
              <!-- Clickable Token Info -->
              <div
                @click="navigateToToken(item)"
                class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer"
              >
                <!-- Token Icon -->
                <div class="w-8 h-8 rounded-xl bg-theme-panel2 flex items-center justify-center overflow-hidden flex-shrink-0 border border-theme-line">
                  <img v-if="item.image" :src="item.image" class="w-full h-full object-cover" />
                  <span v-else class="text-cyan-500 dark:text-cyan-400 font-mono font-bold text-xs uppercase">{{ item.asset_code.substring(0, 2) }}</span>
                </div>

                <!-- Symbol & Name -->
                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span class="font-bold text-xs text-theme-ink uppercase font-mono">{{ item.asset_code }}</span>
                    <span v-if="item.name && item.name !== item.asset_code" class="text-[10.5px] text-theme-dim truncate max-w-[120px]">
                      {{ item.name }}
                    </span>
                  </div>
                  <div class="text-[9.5px] font-mono text-theme-dim truncate">
                    {{ shorten(item.asset_issuer) }}
                  </div>
                </div>
              </div>

              <!-- Price & 24h Change (Primary XLM, Secondary USD) -->
              <div @click="navigateToToken(item)" class="text-right cursor-pointer flex-shrink-0">
                <div class="text-xs font-mono font-bold text-theme-ink flex items-center justify-end gap-1">
                  <span>{{ formatXlmPrice(item.xlm_price) }}</span>
                  <span class="text-[10px] text-cyan-600 dark:text-cyan-400 font-semibold">XLM</span>
                </div>
                <div class="text-[10.5px] font-mono font-medium text-theme-dim flex items-center justify-end gap-1.5 mt-0.5">
                  <span>${{ formatPrice(item.usd_price) }}</span>
                  <span
                    class="font-bold text-[9.5px]"
                    :class="parseFloat(item.price_change_24h || 0) >= 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400'"
                  >
                    {{ parseFloat(item.price_change_24h || 0) >= 0 ? '▲ +' : '▼ ' }}{{ Number(item.price_change_24h || 0).toFixed(2) }}%
                  </span>
                </div>
              </div>

              <!-- Star Toggle Remove Button -->
              <button
                @click.stop="handleRemoveWatchlist(item)"
                class="p-1.5 rounded-lg text-amber-500 dark:text-amber-400 hover:bg-amber-500/10 transition cursor-pointer flex-shrink-0"
                title="Remove from Watchlist"
              >
                <Star class="w-4 h-4 fill-amber-500 dark:fill-amber-400" />
              </button>
            </div>
          </div>

          <!-- TAB 2: ALERTS CONTENT -->
          <div v-else-if="activeTab === 'alerts'" class="max-h-[380px] overflow-y-auto custom-scrollbar divide-y divide-theme-line">
            <!-- Disconnected Wallet Guard -->
            <div v-if="!walletKey" class="p-8 text-center space-y-3">
              <div class="w-10 h-10 mx-auto rounded-full bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-500 dark:text-cyan-400">
                <Wallet class="w-5 h-5" />
              </div>
              <div class="space-y-1">
                <h4 class="text-xs font-bold text-theme-ink">Wallet Connection Required</h4>
                <p class="text-[11px] text-theme-dim max-w-xs mx-auto leading-relaxed">
                  Connect your Stellar wallet to view, sync, and manage your live price triggers.
                </p>
              </div>
              <button
                @click="triggerConnectWallet"
                class="px-4 py-2 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 transition cursor-pointer inline-flex items-center gap-2"
              >
                <Wallet class="w-3.5 h-3.5" />
                <span>Connect Wallet</span>
              </button>
            </div>

            <!-- Empty State -->
            <div v-else-if="alertsList.length === 0" class="p-8 text-center space-y-3">
              <div class="w-10 h-10 mx-auto rounded-full bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-500 dark:text-cyan-400">
                <BellRing class="w-5 h-5" />
              </div>
              <div class="space-y-1">
                <h4 class="text-xs font-bold text-theme-ink">No Active Alerts</h4>
                <p class="text-[11px] text-theme-dim max-w-xs mx-auto leading-relaxed">
                  Create price and volatility alerts on any token insight page with the "Set Alert" button.
                </p>
              </div>
            </div>

            <!-- Active Alerts List -->
            <div
              v-else
              v-for="alert in alertsList"
              :key="alert.id"
              class="p-3 hover:bg-theme-panel2 transition flex items-center justify-between gap-3"
            >
              <!-- Alert Info -->
              <div
                @click="navigateToAlertToken(alert)"
                class="flex items-center gap-3 flex-1 min-w-0 cursor-pointer"
              >
                <div class="w-8 h-8 rounded-xl bg-theme-panel2 border border-theme-line flex items-center justify-center text-cyan-600 dark:text-cyan-400 font-mono font-bold text-xs flex-shrink-0 uppercase overflow-hidden">
                  <img v-if="alert.token_image || alert.image || alert.logo" :src="alert.token_image || alert.image || alert.logo" class="w-full h-full object-cover" />
                  <span v-else>{{ alert.asset_code.substring(0, 2) }}</span>
                </div>

                <div class="min-w-0">
                  <div class="flex items-center gap-1.5">
                    <span class="font-bold text-xs text-theme-ink uppercase font-mono">{{ alert.asset_code }}</span>
                    <span
                      class="px-1.5 py-0.2 rounded text-[9.5px] font-mono font-bold uppercase"
                      :class="alert.status === 'active' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-theme-panel2 text-theme-dim border border-theme-line'"
                    >
                      {{ alert.status }}
                    </span>
                  </div>
                  <div class="text-[11px] font-mono text-cyan-600 dark:text-cyan-400 font-semibold mt-0.5">
                    {{ formatConditionLabel(alert) }}
                  </div>
                </div>
              </div>

              <!-- Delete Action -->
              <button
                @click.stop="handleDeleteAlert(alert.id)"
                class="p-1.5 rounded-lg text-theme-dim hover:text-rose-500 hover:bg-rose-500/10 transition cursor-pointer flex-shrink-0"
                title="Delete Alert"
              >
                <Trash2 class="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
  Star,
  Bell,
  BellRing,
  BellOff,
  ChevronDown,
  Wallet,
  Trash2
} from 'lucide-vue-next';
import Swal from 'sweetalert2';
import {
  watchlist,
  toggleStar,
  syncWatchlistOnConnect,
  fetchLiveWatchlistPrices
} from '@/utils/watchlist.js';
import {
  alertsList,
  notifications,
  unreadCount,
  clearAlerts,
  fetchAlerts,
  deleteAlert,
  fetchNotifications,
  markAllNotificationsRead,
  markNotificationRead
} from '@/utils/alerts.js';

const props = defineProps({
  walletKey: {
    type: String,
    default: ''
  }
});

const emit = defineEmits(['open-wallet']);
const router = useRouter();

const isOpen = ref(false);
const isBellOpen = ref(false);
const activeTab = ref('watchlist');

const bellContainer = ref(null);
const dropdownContainer = ref(null);

const activeAlertsCount = computed(() => {
  return alertsList.value.filter((a) => a.status === 'active').length;
});

const enrichedWatchlist = computed(() => {
  return watchlist.value;
});

function toggleDropdown() {
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    isBellOpen.value = false;
    refreshData();
  }
}

function toggleBell() {
  isBellOpen.value = !isBellOpen.value;
  if (isBellOpen.value) {
    isOpen.value = false;
    if (props.walletKey) {
      fetchNotifications(props.walletKey);
    }
  }
}

function handleClickOutside(e) {
  if (dropdownContainer.value && !dropdownContainer.value.contains(e.target)) {
    isOpen.value = false;
  }
  if (bellContainer.value && !bellContainer.value.contains(e.target)) {
    isBellOpen.value = false;
  }
}

function formatPrice(val) {
  const p = parseFloat(val || 0);
  if (p === 0) return '0.00';
  if (p < 0.0001) return p.toFixed(8);
  if (p < 0.01) return p.toFixed(6);
  if (p < 1) return p.toFixed(4);
  return p.toFixed(2);
}

function formatXlmPrice(val) {
  const p = parseFloat(val || 0);
  if (p === 0) return '0.00';
  if (p < 0.0001) return p.toFixed(7);
  if (p < 0.01) return p.toFixed(6);
  if (p < 1) return p.toFixed(4);
  return p.toFixed(2);
}

function shorten(str) {
  if (!str) return '-';
  return str.slice(0, 4) + '...' + str.slice(-4);
}

function formatConditionLabel(alert) {
  const val = Number(alert.target_value ?? alert.condition_value ?? 0);
  const isUsd = alert.currency === 'usd';
  if (alert.condition_type === 'price_above') {
    return isUsd ? `Above $${formatPrice(val)}` : `Above ${formatXlmPrice(val)} XLM`;
  } else if (alert.condition_type === 'price_below') {
    return isUsd ? `Below $${formatPrice(val)}` : `Below ${formatXlmPrice(val)} XLM`;
  } else if (alert.condition_type === 'pct_change_up') {
    return `24h Change > +${val}%`;
  } else if (alert.condition_type === 'pct_change_down') {
    return `24h Change < ${val}%`;
  }
  return `Target: ${val}`;
}

function navigateToToken(item) {
  isOpen.value = false;
  router.push({
    path: '/token-insight',
    query: {
      asset_code: item.asset_code,
      issuer: item.asset_issuer
    }
  });
}

function navigateToAlertToken(alert) {
  isOpen.value = false;
  router.push({
    path: '/token-insight',
    query: {
      asset_code: alert.asset_code,
      issuer: alert.asset_issuer
    }
  });
}

async function handleNotificationClick(item) {
  if (!item.is_read) {
    await markNotificationRead(item.id);
  }
  isBellOpen.value = false;
  if (item.data?.asset_code && item.data?.asset_issuer) {
    router.push({
      path: '/token-insight',
      query: {
        asset_code: item.data.asset_code,
        issuer: item.data.asset_issuer
      }
    });
  }
}

async function handleMarkAllRead() {
  if (props.walletKey) {
    await markAllNotificationsRead(props.walletKey);
  }
}

function handleRemoveWatchlist(item) {
  toggleStar(item, props.walletKey);
}

async function handleDeleteAlert(id) {
  try {
    const res = await deleteAlert(id, props.walletKey);
    if (res.status === 'success') {
      // List is reactively updated
    }
  } catch (err) {
    console.error('Error deleting alert:', err);
    Swal.fire('Error', 'Failed to delete price alert.', 'error');
  }
}

function triggerConnectWallet() {
  isOpen.value = false;
  emit('open-wallet');
}

function refreshData() {
  fetchLiveWatchlistPrices();
  if (props.walletKey) {
    fetchAlerts(props.walletKey);
    fetchNotifications(props.walletKey);
  }
}

watch(
  () => props.walletKey,
  (newWallet) => {
    if (newWallet) {
      syncWatchlistOnConnect(newWallet);
      fetchAlerts(newWallet);
      fetchNotifications(newWallet);
    } else {
      clearAlerts();
      fetchLiveWatchlistPrices();
    }
  },
  { immediate: true }
);

onMounted(() => {
  window.addEventListener('click', handleClickOutside);
  fetchLiveWatchlistPrices();
  // Poll notifications periodically if wallet connected
  const timer = setInterval(() => {
    if (props.walletKey) {
      fetchNotifications(props.walletKey);
    }
  }, 60000);

  onUnmounted(() => {
    window.removeEventListener('click', handleClickOutside);
    clearInterval(timer);
  });
});
</script>

<style scoped>
@keyframes bounce-short {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-3px); }
}
.animate-bounce-short {
  animation: bounce-short 1.5s ease-in-out infinite;
}

:global(html.light) .tab-badge {
  color: #0F172A !important;
}

:global(html.light) .tab-watchlist-active,
:global(html.light) .tab-watchlist-active .tab-label,
:global(html.light) .tab-watchlist-active .tab-badge {
  color: #0F172A !important;
}

:global(html.light) .tab-alerts-active,
:global(html.light) .tab-alerts-active .tab-label,
:global(html.light) .tab-alerts-active .tab-badge {
  color: #0F172A !important;
}
</style>
