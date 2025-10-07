<template>
    <TransitionRoot as="template" :show="props.modelValue">
        <Dialog id="connectWalletParent" as="div" class="relative z-10" @close="closeModal">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
            </TransitionChild>

            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex items-end justify-center min-h-full p-2 text-center sm:items-center sm:p-0">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative px-5 pt-5 overflow-hidden text-left transition-all transform bg-white rounded-lg shadow-xl">
                            <!-- Close Button (X) -->
                            <button @click="closeModal"
                                class="absolute text-gray-500 top-2 right-2 hover:text-gray-800 focus:outline-none"
                                aria-label="Close Modal">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            <div :id="modalId" :data-bs-backdrop="backdrop" :data-bs-keyboard="keyboard" tabindex="-1"
                                aria-labelledby="exampleModalLabel" :inert="!props.modelValue">
                                <div class="modal-dialog">
                                    <div class="sm:mx-auto sm:w-full sm:max-w-md">
                                        <img class="w-auto h-16 mx-auto mb-1" :src="Logo" alt="Your Company" />
                                    </div>
                                    <div class="modal-content modal-wallet">
                                        <template v-if="!isWalletConnected">
                                            <div id="connectWalletModal" class="modal-body mx-10">
                                                <h1 class="mb-5">
                                                    Connect Your Wallet
                                                </h1>
                                                <div class="mb-3">
                                                    <label for="selectedBlockchain"
                                                        class="block text-sm font-medium text-black-700 mb-1">
                                                        Select Blockchain
                                                    </label>
                                                    <select id="selectedBlockchain" name="selectedBlockchain" v-model="selectedBlockchain
                                                        "
                                                        class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                        <option value="" disabled selected>
                                                            Choose a blockchain
                                                        </option>
                                                        <option v-for="blockchain in blockchainOptions" :key="blockchain.key
                                                            " :value="blockchain.id
                                                                ">
                                                            {{
                                                                blockchain.name
                                                            }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="selectedWallet"
                                                        class="block text-sm font-medium text-black-700 mb-1">
                                                        Select Wallet
                                                    </label>
                                                    <select id="selectedWallet" name="selectedWallet"
                                                        v-model="selectedWallet"
                                                        class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                                        <option value="" disabled selected>
                                                            Choose your Wallet
                                                        </option>
                                                        <option v-for="wallet in walletOptions" :key="wallet.key"
                                                            :value="wallet.key">
                                                            {{ wallet.name }}
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="mt-5">
                                                    <button id="connectWalletButton" @click="handleConnect"
                                                        type="button" class="walletconnect-btn" :disabled="isLoading">
                                                        {{
                                                            isLoading
                                                                ? "Connecting..."
                                                                : "Connect Wallet"
                                                        }}
                                                    </button>
                                                </div>
                                            </div>
                                        </template>

                                        <!-- If connected -->
                                        <template v-else>
                                            <div class="modal-body" style="word-break: break-all">
                                                <h1 id="public_key" style="font-size: 14px">
                                                    {{ UserData.walletKey }}
                                                </h1>
                                            </div>
                                            <div class="mt-5">
                                                <button id="connectWalletButton" @click="disconnectWallet()"
                                                    type="button" class="walletconnect-btn">
                                                    Disconnect Wallet
                                                </button>
                                            </div>
                                        </template>

                                        <div class="mt-3 mx-3">
                                            <p class="text-center block text-sm font-medium text-gray-700 mb-1">
                                                We never store your wallet info.
                                                Used only for on-chain
                                                operations.
                                            </p>
                                        </div>
                                    </div>
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
import { ref, computed, defineProps, onMounted, watch } from "vue";
import {
    Dialog,
    DialogPanel,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import Logo from "@/assets/token-glade-logo.png";
import axios from "axios";
import {
    isConnected,
    getPublicKey,
    requestAccess,
} from "@stellar/freighter-api";
import Swal from "sweetalert2";
import { getCookie, apiHeaders } from "../utils/utils.js";

const modalId = "ConnectWallet";
const walletOptions = ref([]);
const blockchainOptions = ref([]);
const selectedWallet = ref("");
const selectedBlockchain = ref("");
const isLoading = ref(false);
const props = defineProps({
  modelValue:   { type: Boolean, default: false }, // v-model for open/close
  connected:    { type: Boolean, default: false }, // parent’s connection state
  walletKey:    { type: String,  default: "" },    // parent’s wallet pk (optional)
});

const backdrop = computed(() => (!isWalletConnected.value ? "static" : ""));
const keyboard = computed(() => (!isWalletConnected.value ? "false" : ""));

const emit = defineEmits(["update:modelValue", "close"]);
function closeModal() {
    emit("update:modelValue", false);
    emit("close");
}

const UserData = ref({
    walletKey: "",
    wallet_type_id: "",
});

const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Fetch available blockchains from the server
async function fetchblockchains() {
    try {
        const response = await axios.get("/api/global/blockchains", {});

        if (response.data.status === "success") {
            blockchainOptions.value = response.data.blockchains;
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
            text:
                error.response?.data?.message ||
                "Failed to fetch blockchains. Please try again later.",
        });
    }
}
async function fetchWallets() {
    try {
        const response = await axios.get("/api/global/wallet_types", {});
        if (response.data.status === "success") {
            walletOptions.value = response.data.wallets;
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
            text:
                error.response?.data?.message ||
                "Failed to fetch wallet types. Please try again later.",
        });
    }
}

function getSelectedWalletMeta() {
    const key = (selectedWallet.value || "").toLowerCase();
    const rec = walletOptions.value.find(
        (w) => (w.key || "").toLowerCase() === key
    );

    if (!rec) return null;
    return {
        walletTypeId: rec.id, // numeric
        blockchainTypeId: rec.blockchain_id, // numeric
        key: rec.key, // "freighter" | "rabet"
        name: rec.name,
    };
}

async function storeWallet(publicKey, walletTypeId, walletKey, blockchainTypeId) {
  try {
    const resp = await axios.post(
      '/api/wallet/store',
      {
        public_key: publicKey,
        wallet_type_id: walletTypeId,
        wallet_key: walletKey || null,
        blockchain_id: blockchainTypeId,
      },
      { headers: apiHeaders(), withCredentials: true }
    );

    const { data } = resp;

    if (data?.status === 'success') {
      // mark connected
      setConnected(data.public ?? publicKey);

      // persist values
      localStorage.setItem('public_key', data.public ?? publicKey);
      localStorage.setItem('wallet_connect', 'true');
      if (data.token) localStorage.setItem('token', data.token);
      localStorage.setItem('wallet_type', String(walletTypeId));
      if (walletKey)  localStorage.setItem('wallet_key', walletKey);

      // optional UX
      if (typeof speak === 'function') speak('connected', true);

      return true; // <-- important
    }

    Swal.fire({ icon: 'error', title: 'Error!', text: data?.message || 'Failed to connect wallet.' });
    return false;
  } catch (err) {
    const resp = err?.response;
    console.error('[storeWallet] error:', {
      status: resp?.status,
      data: resp?.data,
      headers: resp?.headers,
    });
    Swal.fire({
      icon: 'error',
      title: 'Could not save wallet',
      text: resp?.data?.message || resp?.data?.error || `Request failed (${resp?.status || 'network'})`,
    });
    return false;
  }
}

function hasRabet() {
    return typeof window !== "undefined" && !!window.rabet;
}

function hasAlbedo() {
  return typeof window !== "undefined" && !!window.albedo && typeof window.albedo.publicKey === "function";
}

function hasXbull() {
  return typeof window !== 'undefined' && (!!window.xBullSDK || !!window.xBull);
}

async function connectWallet(wallet) {
    const candidate =
        typeof wallet === "string" ? wallet : selectedWallet.value;
    const key = (candidate || "")
        .toString()
        .trim()
        .toLowerCase()
        .replace("frighter", "freighter");

    switch (key) {
        case "freighter": {
            try {
                const installed = await isConnected().catch(() => false);
                if (!installed) throw new Error("Freighter not installed");

                const allowed = await requestAccess().catch(() => null);
                if (!allowed || allowed.address === "User declined access") {
                    throw new Error("Access declined by user");
                }

                const publicKey = await getPublicKey();
                return { publicKey, wallet: "freighter" };
            } catch (e) {
                throw new Error(e.message || "Freighter connection failed");
            }
        }
        case "rabet": {
            if (!hasRabet()) throw new Error("Rabet not installed");
            try {
                const res = await window.rabet.connect();
                if (!res?.publicKey)
                    throw new Error("No public key from Rabet");
                return { publicKey: res.publicKey, wallet: "rabet" };
            } catch {
                throw new Error("Rabet connection rejected");
            }
        }
        case "albedo": {
            if (!hasAlbedo()) throw new Error("Albedo SDK not loaded");
            try {
                const res = await window.albedo.publicKey({ network: 'public' });
                if (!res?.pubkey) throw new Error('No public key returned');
                return {
                    publicKey: res.pubkey,
                    wallet: "albedo",
                    proof: {
                        token: res.token,
                        signed_message: res.signed_message,
                        signature: res.signature,
                    },
                };
            } catch (e) {
                const msg = (e && (e.message || e.error || e.code)) || '';
                const hint = /not selected|cancel|denied/i.test(msg)
                    ? 'Open Albedo and select (or create) an account, then try again.'
                    : 'Could not get a public key.';
                Swal.fire({ icon: 'error', title: 'Albedo', text: hint });
            }
        }
        case "xbull": {
            if (!hasXbull()) throw new Error("xBull not installed");
            try {
                await window.xBullSDK.connect({ canRequestPublicKey: true, canRequestSign: false });
                const publicKey = await window.xBullSDK.getPublicKey();
                if (!publicKey) throw new Error("No public key from xBull");
                return { publicKey, wallet: "xbull" };
            } catch (e) {
                throw new Error("xBull connection rejected");
            }
        }
        default:
            throw new Error("Unsupported wallet");
    }
}

async function handleConnect() {
    if (!selectedWallet.value) {
        Swal.fire({
            icon: 'warning',
            title: 'Select a wallet',
            text: 'Choose Freighter or Rabet.',
        });
        return;
    }

    isLoading.value = true;

    try {
        const result = await connectWallet(selectedWallet.value);

        const meta = getSelectedWalletMeta();

        if (!meta?.walletTypeId || !meta?.blockchainTypeId) {
            throw new Error(
                'Could not resolve wallet_type_id / blockchain_id from walletOptions.'
            );
        }

        // 3) persist to backend with IDs (no hardcoding)
        const ok = await storeWallet(
            result.publicKey,
            meta.walletTypeId,
            meta.key,
            meta.blockchainTypeId
        );
        if (ok !== true) {
            // Don’t silently bail — show a clear error so you know why it didn’t reload.
            Swal.fire({
                icon: 'error',
                title: 'Could not save wallet',
                text: 'Your wallet was connected locally but could not be saved on the server.',
            });
            return;
        }

        // Update local state so UI reacts immediately (even if we reload)
        UserData.value.walletKey = result.publicKey;
        setConnected(result.publicKey);

        // Ensure any modal is closed before reload
        if (Swal.isVisible()) Swal.close();

        // Give the browser a tick to flush storage/cookies
        setTimeout(() => {
            // Use replace() to avoid storing the pre-login page in history
            window.location.replace(window.location.href);
            // fallback (some browsers): window.location.reload();
        }, 150);
    } catch (e) {
        console.error('[handleConnect] error:', e);
        Swal.fire({ icon: 'error', title: 'Wallet Error', text: e?.message || 'Failed to connect wallet.' });
    } finally {
        isLoading.value = false;
    }
}

async function disconnectWallet() {
    try {
        const public_key = localStorage.getItem("public_key");
        const response = await axios.post(
            "/api/wallet/disconnect",
            { public_key },
            { headers: apiHeaders(), withCredentials: true }
        );
        if (response.data.status === "success") {
            closeModal();

            //clear everthing from cookies
            localStorage.setItem("wallet_connect", "false");
            localStorage.setItem("token", null);
            localStorage.setItem("public_key", null);
            localStorage.setItem("wallet_key", null);
            localStorage.setItem("wallet_type", null);
            speak("connected", false);
            document.cookie =
                "public_key=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie =
                "accessToken=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie =
                "wallet_type_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            document.cookie =
                "blockchain_id=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
            setDisconnected();
            window.location.reload();
        } else {
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: "Failed to disconnect wallet.",
            });
        }
    } catch (error) {
        console.error("Error disconnecting wallet:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: "An error occurred while disconnecting the wallet.",
        });
    }
}


const connectedLocal = ref(false);
const localPk        = ref(getCookie("public_key") || localStorage.getItem("public_key") || "");


function safeGet(v) {
  if (!v) return "";
  const s = String(v).trim().toLowerCase();
  return (s === "null" || s === "undefined") ? "" : String(v);
}

function readPk() {
  return safeGet(getCookie("public_key") || localStorage.getItem("public_key"));
}

function setConnected(pk)   { connectedLocal.value = true;  localPk.value = pk || ""; }
function setDisconnected()  { connectedLocal.value = false; localPk.value = ""; }


const displayPk = computed(() =>
  safeGet(props.walletKey) || localPk.value
);

const isWalletConnected = computed(() => {
  const pk = displayPk.value;
  return !!pk && pk.startsWith("G") && pk.length === 56;
});

onMounted(() => {
    const pk = readPk();
    if (pk) setConnected(pk); else setDisconnected();
});

watch(() => props.modelValue, (open) => {
  if (open) {
    fetchWallets();
    fetchblockchains();
    const pk = readPk();
    if (pk) setConnected(pk); else setDisconnected();
  }
});
</script>
