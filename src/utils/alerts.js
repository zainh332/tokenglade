import { ref, computed } from 'vue';
import axios from 'axios';
import { getCookie } from './utils.js';

// Global reactive state
export const alerts = ref([]);
export const alertsList = alerts; // Alias for component compatibility
export const notifications = ref([]);
export const unreadCount = ref(0);
export const alertsLoading = ref(false);
export const notificationsLoading = ref(false);

export const isPushSupported = typeof window !== 'undefined' && 'Notification' in window && 'serviceWorker' in navigator && 'PushManager' in window;
export const pushPermission = ref(typeof Notification !== 'undefined' ? Notification.permission : 'default');

function getConnectedWalletAddress() {
  if (typeof window === 'undefined') return null;
  return getCookie('public_key') || localStorage.getItem('public_key') || localStorage.getItem('wallet_key') || localStorage.getItem('public_address') || null;
}

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding)
    .replace(/-/g, '+')
    .replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

/**
 * Fetch active and fired alerts for the connected wallet.
 */
export async function fetchAlerts(customWallet = null) {
  const wallet = customWallet || getConnectedWalletAddress();
  if (!wallet) {
    alerts.value = [];
    return;
  }

  alertsLoading.value = true;
  try {
    const res = await axios.get('/api/alerts', {
      params: { wallet_address: wallet, wallet }
    });
    if (res.data && res.data.alerts) {
      alerts.value = res.data.alerts;
    }
  } catch (e) {
    console.warn('Failed to fetch alerts:', e);
  } finally {
    alertsLoading.value = false;
  }
}

/**
 * Create a new price alert.
 */
export async function createAlert(payload) {
  const wallet = payload.wallet_address || payload.wallet || getConnectedWalletAddress();
  if (!wallet) {
    throw new Error('Wallet must be connected to set price alerts.');
  }

  const res = await axios.post('/api/alerts', {
    ...payload,
    wallet_address: wallet,
    wallet
  });

  if (res.data && res.data.alert) {
    alerts.value = [res.data.alert, ...alerts.value];
    fetchAlerts(wallet);
  }

  return res.data;
}

/**
 * Delete an existing alert.
 */
export async function deleteAlert(id, customWallet = null) {
  const wallet = customWallet || getConnectedWalletAddress();
  if (!id) return;

  alerts.value = alerts.value.filter(a => a.id !== id);

  try {
    const res = await axios.delete(`/api/alerts/${id}`, {
      params: { wallet_address: wallet, wallet }
    });
    return res.data;
  } catch (e) {
    console.warn('Failed to delete alert:', e);
    if (wallet) fetchAlerts(wallet);
    throw e;
  }
}

/**
 * Fetch on-site notifications and unread badge count.
 */
export async function fetchNotifications(customWallet = null) {
  const wallet = customWallet || getConnectedWalletAddress();
  if (!wallet) {
    notifications.value = [];
    unreadCount.value = 0;
    return;
  }

  notificationsLoading.value = true;
  try {
    const res = await axios.get('/api/notifications', {
      params: { wallet_address: wallet, wallet }
    });
    if (res.data) {
      notifications.value = res.data.notifications || [];
      unreadCount.value = res.data.unread_count || 0;
    }
  } catch (e) {
    console.warn('Failed to fetch notifications:', e);
  } finally {
    notificationsLoading.value = false;
  }
}

/**
 * Mark a single notification as read.
 */
export async function markNotificationAsRead(id) {
  const notif = notifications.value.find(n => n.id === id);
  if (notif && !notif.is_read) {
    notif.is_read = true;
    if (unreadCount.value > 0) unreadCount.value--;
  }

  try {
    const res = await axios.post(`/api/notifications/${id}/read`);
    return res.data;
  } catch (e) {
    console.warn('Failed to mark notification read:', e);
  }
}
export const markNotificationRead = markNotificationAsRead;

/**
 * Mark all notifications as read.
 */
export async function markAllNotificationsAsRead(customWallet = null) {
  const wallet = customWallet || getConnectedWalletAddress();
  if (!wallet) return;

  notifications.value.forEach(n => { n.is_read = true; });
  unreadCount.value = 0;

  try {
    const res = await axios.post('/api/notifications/read-all', {
      wallet_address: wallet,
      wallet
    });
    return res.data;
  } catch (e) {
    console.warn('Failed to mark all notifications read:', e);
  }
}
export const markAllNotificationsRead = markAllNotificationsAsRead;

/**
 * Request browser notification permission and subscribe to Web Push.
 */
export async function registerBrowserPush(customWallet = null) {
  const wallet = customWallet || getConnectedWalletAddress();
  if (!isPushSupported) {
    console.warn('Browser push notifications are not supported in this browser/environment (requires HTTPS, Service Worker, and PushManager support).');
    return false;
  }

  try {
    // 1. Request permission
    const permission = await Notification.requestPermission();
    pushPermission.value = permission;

    if (permission !== 'granted') {
      return false;
    }

    // 2. Register Service Worker
    const reg = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
    await navigator.serviceWorker.ready;

    // 3. Get VAPID public key
    const vapidRes = await axios.get('/api/push/vapid-public-key');
    const publicKey = vapidRes.data?.public_key;

    if (!publicKey) {
      console.warn('VAPID public key not found on server.');
      return false;
    }

    // 4. Subscribe to PushManager
    const applicationServerKey = urlBase64ToUint8Array(publicKey);
    let subscription = await reg.pushManager.getSubscription();
    if (!subscription) {
      subscription = await reg.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey
      });
    }

    const subJson = subscription.toJSON();

    // 5. Send subscription to backend tied to connected wallet
    if (wallet) {
      await axios.post('/api/push/subscribe', {
        wallet_address: wallet,
        wallet,
        endpoint: subscription.endpoint,
        keys: {
          p256dh: subJson.keys?.p256dh,
          auth: subJson.keys?.auth
        }
      });
    }

    return subscription;
  } catch (err) {
    console.warn('Failed to register browser push:', err);
    return false;
  }
}

// Background polling helper
let notificationInterval = null;
export function startNotificationPolling(intervalMs = 30000) {
  if (notificationInterval) return;
  fetchNotifications();
  notificationInterval = setInterval(() => {
    const wallet = getConnectedWalletAddress();
    if (wallet) {
      fetchNotifications(wallet);
    }
  }, intervalMs);
}

export function stopNotificationPolling() {
  if (notificationInterval) {
    clearInterval(notificationInterval);
    notificationInterval = null;
  }
}

export function clearAlerts() {
  alerts.value = [];
  notifications.value = [];
  unreadCount.value = 0;
}

if (typeof window !== 'undefined') {
  window.addEventListener('tokenglade-wallet-changed', (e) => {
    if (!e.detail?.connected || !e.detail?.publicKey) {
      clearAlerts();
    } else {
      fetchAlerts(e.detail.publicKey);
      fetchNotifications(e.detail.publicKey);
    }
  });
}

export function useAlerts() {
  return {
    alerts,
    alertsList,
    notifications,
    unreadCount,
    alertsLoading,
    notificationsLoading,
    isPushSupported,
    pushPermission,
    clearAlerts,
    fetchAlerts,
    createAlert,
    deleteAlert,
    fetchNotifications,
    markNotificationAsRead,
    markNotificationRead,
    markAllNotificationsAsRead,
    markAllNotificationsRead,
    registerBrowserPush,
    startNotificationPolling,
    stopNotificationPolling
  };
}
