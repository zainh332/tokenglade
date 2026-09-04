import { ref, computed } from 'vue';
import axios from 'axios';
import { getCookie } from './utils.js';

const LOCAL_STORAGE_KEY = 'tokenglade_watchlist';

// Global reactive state
export const watchlistItems = ref([]);
export const watchlist = watchlistItems; // Alias for component compatibility
export const watchlistLoading = ref(false);
export const initialized = ref(false);

function getStoredLocalItems() {
  try {
    const raw = localStorage.getItem(LOCAL_STORAGE_KEY);
    if (!raw) return [];
    const parsed = JSON.parse(raw);
    return Array.isArray(parsed) ? parsed : [];
  } catch (e) {
    return [];
  }
}

function saveStoredLocalItems(items) {
  try {
    const simple = items.map(it => ({
      asset_code: it.asset_code,
      asset_issuer: it.asset_issuer
    }));
    localStorage.setItem(LOCAL_STORAGE_KEY, JSON.stringify(simple));
  } catch (e) {}
}

function getConnectedWalletAddress() {
  if (typeof window === 'undefined') return null;
  return getCookie('public_key') || localStorage.getItem('public_key') || localStorage.getItem('wallet_key') || localStorage.getItem('public_address') || null;
}

/**
 * Fetch and enrich watchlist items from server (or enrich local storage items).
 */
export async function fetchWatchlist(customWallet = null) {
  watchlistLoading.value = true;
  const wallet = customWallet || getConnectedWalletAddress();

  try {
    if (wallet) {
      const res = await axios.get('/api/watchlist', {
        params: { wallet_address: wallet, wallet }
      });
      if (res.data && res.data.items) {
        watchlistItems.value = res.data.items;
        // Also keep local storage in sync as offline backup
        saveStoredLocalItems(res.data.items);
      }
    } else {
      const local = getStoredLocalItems();
      if (local.length === 0) {
        watchlistItems.value = [];
      } else {
        const res = await axios.get('/api/watchlist', {
          params: { items: local }
        });
        if (res.data && res.data.items) {
          watchlistItems.value = res.data.items;
        }
      }
    }
  } catch (e) {
    // Fallback to plain local storage items if offline
    if (watchlistItems.value.length === 0) {
      watchlistItems.value = getStoredLocalItems().map(it => ({
        ...it,
        name: it.asset_code,
        usd_price: 0,
        xlm_price: 0,
        price_change_24h: 0
      }));
    }
  } finally {
    watchlistLoading.value = false;
    initialized.value = true;
  }
}
export const fetchLiveWatchlistPrices = fetchWatchlist;

/**
 * Check if a token is in the watchlist.
 */
export function isTokenStarred(code, issuer) {
  if (!code || !issuer) return false;
  const targetCode = String(code).toUpperCase().trim();
  const targetIssuer = String(issuer).toUpperCase().trim();

  return watchlistItems.value.some(it => 
    String(it.asset_code).toUpperCase().trim() === targetCode &&
    String(it.asset_issuer).toUpperCase().trim() === targetIssuer
  );
}
export const isStarred = isTokenStarred;

/**
 * Toggle a token in the watchlist.
 * Works seamlessly whether wallet is connected or not.
 */
export async function toggleTokenStar(token, customWallet = null) {
  const code = (token.asset_code || token.code || '').toUpperCase().trim();
  const issuer = (token.asset_issuer || token.issuer || '').toUpperCase().trim();
  if (!code || !issuer) return false;

  const wallet = customWallet || getConnectedWalletAddress();
  const starred = isTokenStarred(code, issuer);

  if (starred) {
    // Remove from reactive state
    watchlistItems.value = watchlistItems.value.filter(it => 
      !(String(it.asset_code).toUpperCase().trim() === code && String(it.asset_issuer).toUpperCase().trim() === issuer)
    );
    saveStoredLocalItems(watchlistItems.value);

    // Call server if connected
    if (wallet) {
      try {
        await axios.delete('/api/watchlist', {
          data: {
            wallet_address: wallet,
            wallet,
            asset_issuer: issuer,
            asset_code: code
          }
        });
      } catch (e) {
        console.warn('Failed to remove from server watchlist', e);
      }
    }
    return false;
  } else {
    // Add to reactive state immediately
    const newItem = {
      id: null,
      asset_code: code,
      asset_issuer: issuer,
      name: token.name || token.project?.org_name || code,
      image: token.image || token.logo || null,
      usd_price: Number(token.usd_price) || 0,
      xlm_price: Number(token.xlm_price) || 0,
      price_change_24h: Number(token.price_change_24h) || 0,
      is_verified: !!(token.is_verified || token.token_verify === 1),
      created_at: new Date().toISOString()
    };

    watchlistItems.value = [newItem, ...watchlistItems.value];
    saveStoredLocalItems(watchlistItems.value);

    // Call server if connected
    if (wallet) {
      try {
        const res = await axios.post('/api/watchlist', {
          wallet_address: wallet,
          wallet,
          asset_issuer: issuer,
          asset_code: code
        });
        if (res.data?.item?.id) {
          newItem.id = res.data.item.id;
        }
      } catch (e) {
        console.warn('Failed to save to server watchlist', e);
      }
    }
    return true;
  }
}
export const toggleStar = toggleTokenStar;

/**
 * Sync localStorage items with server database upon connecting a wallet.
 */
export async function syncWatchlistOnConnect(wallet) {
  if (!wallet) return;
  const local = getStoredLocalItems();

  try {
    const res = await axios.post('/api/watchlist/sync', {
      wallet_address: wallet,
      wallet,
      items: local
    });

    if (res.data && res.data.items) {
      watchlistItems.value = res.data.items;
      saveStoredLocalItems(res.data.items);
    }
  } catch (e) {
    console.warn('Watchlist sync error:', e);
    fetchWatchlist(wallet);
  }
}

export function useWatchlist() {
  if (!initialized.value) {
    fetchWatchlist();
  }

  return {
    watchlist: watchlistItems,
    watchlistItems,
    loading: watchlistLoading,
    count: computed(() => watchlistItems.value.length),
    fetchWatchlist,
    fetchLiveWatchlistPrices,
    isStarred: isTokenStarred,
    isTokenStarred,
    toggleStar: toggleTokenStar,
    toggleTokenStar,
    syncOnConnect: syncWatchlistOnConnect,
    syncWatchlistOnConnect
  };
}
