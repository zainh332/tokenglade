<template>
    <TransitionRoot as="template" :show="props.modelValue">
        <Dialog id="connectWalletParent" as="div" class="relative z-50" @close="closeModal">
            <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
                leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 transition-opacity bg-slate-950/80 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 z-50 w-screen overflow-y-auto">
                <div class="flex items-center justify-center min-h-full p-4 text-center">
                    <TransitionChild as="template" enter="ease-out duration-300"
                        enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        enter-to="opacity-100 translate-y-0 sm:scale-100" leave="ease-in duration-200"
                        leave-from="opacity-100 translate-y-0 sm:scale-100"
                        leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
                        <DialogPanel
                            class="relative w-full max-w-md px-6 py-6 overflow-hidden text-left transition-all transform bg-[#111827] border border-[rgba(148,163,184,0.16)] rounded-[25px] shadow-2xl">
                            <!-- Close Button (X) --> 
                            <button @click="closeModal"
                                class="absolute text-slate-400 top-4 right-4 hover:text-white transition focus:outline-none"
                                aria-label="Close Modal">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            
                            <div :id="modalId" :data-bs-backdrop="backdrop" :data-bs-keyboard="keyboard" tabindex="-1"
                                aria-labelledby="exampleModalLabel" :inert="!props.modelValue">
                                <div class="modal-dialog">
                                    <div class="sm:mx-auto sm:w-full sm:max-w-md">
                                        <img class="w-auto h-14 mx-auto mb-2" :src="Logo" alt="Your Company" />
                                    </div>
                                    <div class="modal-content modal-wallet">
                                        <template v-if="!isWalletConnected">
                                            <div id="connectWalletModal" class="modal-body">
                                                <h1 class="text-xl font-bold text-center text-white tracking-tight mb-6">
                                                    Connect Your Wallet
                                                </h1>
                                                <div class="mb-4">
                                                    <label for="selectedBlockchain"
                                                        class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-1.5">
                                                        Select Blockchain
                                                    </label>
                                                    <select id="selectedBlockchain" name="selectedBlockchain" v-model="selectedBlockchain"
                                                        class="block w-full px-3.5 py-2.5 bg-[#182235] border border-[rgba(148,163,184,0.16)] text-white rounded-xl focus:outline-none focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition sm:text-sm sm:leading-6">
                                                        <option value="" disabled selected>
                                                            Choose a blockchain
                                                        </option>
                                                        <option v-for="blockchain in blockchainOptions" :key="blockchain.key" :value="blockchain.id">
                                                            {{ blockchain.name }}
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="mb-4">
                                                    <label for="selectedWallet"
                                                        class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono mb-1.5">
                                                        Select Wallet
                                                    </label>
                                                    <select id="selectedWallet" name="selectedWallet"
                                                        v-model="selectedWallet"
                                                        class="block w-full px-3.5 py-2.5 bg-[#182235] border border-[rgba(148,163,184,0.16)] text-white rounded-xl focus:outline-none focus:ring-1 focus:ring-violet-500 focus:border-violet-500 transition sm:text-sm sm:leading-6">
                                                        <option value="" disabled selected>
                                                            Choose your Wallet
                                                        </option>
                                                        <option v-for="wallet in displayedWalletOptions" :key="wallet.key"
                                                            :value="wallet.key">
                                                            {{ wallet.name }}
                                                        </option>
                                                    </select>
                                                </div>

                                                 <div class="mt-6">
                                                    <button id="connectWalletButton" @click="handleConnect"
                                                        type="button" class="w-full text-white text-sm font-bold tracking-wide py-3 rounded-xl hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] transition duration-300 bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))] bg-[length:200%_200%] animate-gradientMove shadow-lg" :disabled="isLoading">
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
                                            <div class="modal-body text-center" style="word-break: break-all">
                                                <h1 class="text-sm font-bold text-slate-400 mb-2">Connected Wallet</h1>
                                                <div class="font-mono font-bold text-white bg-[#151D2D] border border-[rgba(148,163,184,0.16)] px-4 py-3 rounded-xl mb-4 text-xs select-all">
                                                    {{ UserData.walletKey || displayPk }}
                                                </div>
                                            </div>
                                            <div class="mt-4">
                                                <button id="connectWalletButton" @click="disconnectWallet()"
                                                    type="button" class="w-full py-3 text-center text-xs font-extrabold uppercase tracking-wider text-red-400 bg-red-950/20 border border-red-900/30 rounded-xl hover:bg-red-950/30 transition duration-300">
                                                    Disconnect Wallet
                                                </button>
                                            </div>
                                        </template>

                                        <div class="mt-4">
                                            <p class="text-center block text-[10px] text-slate-400 font-mono mb-1 leading-relaxed">
                                                We never store your wallet info. Used only for on-chain operations.
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
import { getCookie, apiHeaders, clearWalletSession, disconnectWalletSession } from "../utils/utils.js";
import {
    isConnected as isLobConnected,
    getPublicKey as getLobPublicKey,
    signMessage as lobSignMessage,
} from '@lobstrco/signer-extension-api';

const modalId = "ConnectWallet";
const walletOptions = ref([]);
const blockchainOptions = ref([]);
const selectedWallet = ref("");
const selectedBlockchain = ref("");
const isLoading = ref(false);
let walletSessionInterval = null;

function isMobileDevice() {
    if (typeof window === "undefined") return false;

    const ua = navigator.userAgent || navigator.vendor || "";
    const mobileUa = /android|iphone|ipad|ipod|mobile/i.test(ua);
    const narrowTouchScreen =
        window.matchMedia("(max-width: 640px)").matches &&
        "ontouchstart" in window;

    return mobileUa || narrowTouchScreen;
}

function selectStellarBlockchain() {
    const stellar = blockchainOptions.value.find(
        (blockchain) => (blockchain.name || "").toLowerCase() === "stellar"
    );

    if (stellar) {
        selectedBlockchain.value = stellar.id;
    }
}

function applyDefaults() {
    selectStellarBlockchain();

    if (isMobileDevice()) {
        selectedWallet.value = "";
    }
}

const displayedWalletOptions = computed(() => {
    if (!isMobileDevice()) {
        return walletOptions.value;
    }

    return walletOptions.value.filter(
        (wallet) => (wallet.key || "").toLowerCase() === "albedo"
    );
});

const props = defineProps({
    modelValue: { type: Boolean, default: false }, // v-model for open/close
    connected: { type: Boolean, default: false }, // parent’s connection state
    walletKey: { type: String, default: "" },    // parent’s wallet pk (optional)
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

// Fetch available blockchains from the server
async function fetchblockchains() {
    try {
        const response = await axios.get("/api/global/blockchains", {});

        if (response.data.status === "success") {
            blockchainOptions.value = response.data.blockchains;
            applyDefaults();
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

function hasLobstrESM() {
    try {
        return typeof isLobConnected === 'function' && typeof getLobPublicKey === 'function';
    } catch {
        return false;
    }
}

function hasLobstrCDN() {
    return typeof window !== 'undefined' && !!window.lobstrSignerExtensionApi;
}

async function lobstrIsConnected() {
    if (hasLobstrESM()) return await isLobConnected();
    if (hasLobstrCDN()) return await window.lobstrSignerExtensionApi.isConnected();
    return false;
}

async function lobstrGetPublicKey() {
    if (hasLobstrESM()) return await getLobPublicKey();
    if (hasLobstrCDN()) return await window.lobstrSignerExtensionApi.getPublicKey();
    throw new Error('LOBSTR API not available');
}

async function lobstrSignMessage(message) {
    if (hasLobstrESM()) return await lobSignMessage(message);
    if (hasLobstrCDN()) return await window.lobstrSignerExtensionApi.signMessage(message);
    throw new Error('LOBSTR API not available');
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
            localStorage.setItem("wallet_connected_at", Date.now().toString());

            if (data.token) localStorage.setItem('token', data.token);
            localStorage.setItem('wallet_type', String(walletTypeId));
            if (walletKey) localStorage.setItem('wallet_key', walletKey);

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
        case "lobstr": {
            const connected = await lobstrIsConnected();
            if (!connected) throw new Error("LOBSTR signer extension not installed or not detected");

            const publicKey = await lobstrGetPublicKey();
            if (!publicKey) throw new Error("LOBSTR returned an empty public key");

            let proof = undefined;
            try {
                const msg = `TokenGlade login: ${publicKey} @ ${new Date().toISOString()}`;
                const signed = await lobstrSignMessage(msg);
                if (signed?.signedMessage && signed?.signerAddress) {
                    proof = { signed_message: signed.signedMessage, signer_address: signed.signerAddress, message: msg };
                }
            } catch (e) {
                console.warn('LOBSTR signMessage skipped/failed:', e?.message || e);
            }

            return { publicKey, wallet: "lobstr", proof };
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
            text: isMobileDevice()
                ? 'Choose Albedo to connect on mobile.'
                : 'Choose a wallet to connect.',
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
        await disconnectWalletSession();
        closeModal();
        if (typeof speak === "function") speak("connected", false);
        setDisconnected();
        window.location.reload();
    } catch (error) {
        console.error("Error disconnecting wallet:", error);
        Swal.fire({
            icon: "error",
            title: "Error!",
            text: error.message || "An error occurred while disconnecting the wallet.",
        });
    }
}


const connectedLocal = ref(false);
const localPk = ref(getCookie("public_key") || localStorage.getItem("public_key") || "");


function safeGet(v) {
    if (!v) return "";
    const s = String(v).trim().toLowerCase();
    return (s === "null" || s === "undefined") ? "" : String(v);
}

function readPk() {
    return safeGet(getCookie("public_key") || localStorage.getItem("public_key"));
}

function setConnected(pk) { connectedLocal.value = true; localPk.value = pk || ""; }
function setDisconnected() { connectedLocal.value = false; localPk.value = ""; }


const displayPk = computed(() =>
    safeGet(props.walletKey) || localPk.value
);

const isWalletConnected = computed(() => {
    const pk = displayPk.value;
    return !!pk && pk.startsWith("G") && pk.length === 56;
});

onMounted(() => {

    // initial state check
    if (isWalletSessionExpired()) {

        clearWalletSession();

        setDisconnected();

        return;
    }

    const pk = readPk();

    if (pk) {
        setConnected(pk);
    } else {
        setDisconnected();
    }

    // live session expiry watcher
    walletSessionInterval = setInterval(() => {

        if (isWalletSessionExpired()) {

            clearWalletSession();

            setDisconnected();

            window.location.reload();
        }

    }, 30000); // check every 30 sec
});

watch(() => props.modelValue, (open) => {
    if (open) {
        if (isMobileDevice()) {
            selectedWallet.value = "";
            selectedBlockchain.value = "";
        }

        fetchWallets();
        fetchblockchains();
        const pk = readPk();
        if (pk) setConnected(pk); else setDisconnected();
    }
});

function isWalletSessionExpired() {
    const connectedAt = localStorage.getItem("wallet_connected_at");

    if (!connectedAt) return true;

    const now = Date.now();
    const diff = now - Number(connectedAt);

    // 2 hours
    return diff > 2 * 60 * 60 * 1000;
}
</script>
