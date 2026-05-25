<template>
    <TransitionRoot appear :show="modelValue" as="template">
        <Dialog as="div" class="relative z-[9999]" @close="close">
            <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-150 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 flex items-center justify-center p-4">
                <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="duration-150 ease-in" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">
                    <DialogPanel class="w-[480px] max-w-[92vw] bg-white rounded-2xl shadow-xl overflow-hidden">
                        <div class="bg-[#3A3A3A] text-white px-6 py-4 flex items-center justify-between">
                            <DialogTitle class="text-lg font-medium">Buy TKG</DialogTitle>
                            <button type="button" class="text-white/80 hover:text-white" aria-label="Close"
                                @click="close">
                                ✕
                            </button>
                        </div>

                        <form class="flex flex-col gap-4 p-6" @submit.prevent="onBuy">
                            <p class="text-sm text-gray-600">
                                Swap XLM for TKG using your connected Stellar wallet.
                            </p>

                            <div v-if="!publicKey"
                                class="rounded-lg bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
                                Connect your wallet to buy TKG.
                                <button type="button" class="mt-3 block w-full rounded-lg bg-black py-2 text-white"
                                    @click="requestWalletConnect">
                                    Connect Wallet
                                </button>
                            </div>

                            <template v-else>
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-lg bg-gray-50 p-3">
                                        <div class="text-gray-500">XLM balance</div>
                                        <div class="font-semibold">{{ fmtNum(xlmBalance) }} XLM</div>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-3">
                                        <div class="text-gray-500">TKG balance</div>
                                        <div class="font-semibold">{{ fmtNum(tkgBalance) }} TKG</div>
                                    </div>
                                </div>

                                <div>
                                    <label for="buy-tkg-amount" class="block text-sm font-medium text-gray-700">
                                        XLM to spend
                                    </label>
                                    <input id="buy-tkg-amount" v-model="amountInput" type="number" min="0" step="any"
                                        class="mt-1 w-full rounded-md border border-gray-300 p-2 focus:outline-none focus:ring-2 focus:ring-[#43CDFF]"
                                        placeholder="10" @input="scheduleQuote" />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Pay with XLM · Min {{ meta.min_xlm }} XLM
                                    </p>
                                </div>

                                <div v-if="quoteLoading" class="text-sm text-gray-500">
                                    Fetching quote…
                                </div>
                                <div v-else-if="quote" class="rounded-lg border border-gray-200 p-4 text-sm space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">You pay</span>
                                        <span class="font-medium">~{{ fmtNum(quote.xlm) }} XLM</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">You receive</span>
                                        <span class="font-medium">~{{ fmtNum(quote.tkg) }} TKG</span>
                                    </div>
                                    <div v-if="quote.price_xlm_per_tkg" class="flex justify-between">
                                        <span class="text-gray-500">Rate</span>
                                        <span>{{ fmtNum(quote.price_xlm_per_tkg, 7) }} XLM / TKG</span>
                                    </div>
                                    <p class="text-xs text-gray-500 pt-1">
                                        Final amount depends on path routing and slippage ({{ slippagePercent }}%).
                                    </p>
                                </div>

                                <button type="submit" :disabled="buyLoading || !quote || quoteLoading" class="w-full rounded-xl py-3 text-white font-medium disabled:opacity-50
                                        bg-[linear-gradient(90deg,rgba(220,25,224,1),rgba(67,205,255,1),rgba(0,254,254,1))]
                                        hover:opacity-90 transition">
                                    {{ buyLoading ? "Processing…" : "Buy TKG" }}
                                </button>
                            </template>
                        </form>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import { ref, computed, watch, onUnmounted } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";
import {
    apiHeaders,
    checkTkgBalance,
    checkXlmBalance,
    getCookie,
    getNetwork,
    signXdrWithActiveWallet,
    updateLoader,
} from "../utils/utils.js";

const props = defineProps({
    modelValue: { type: Boolean, default: false },
});

const emit = defineEmits(["update:modelValue", "open-wallet"]);

const publicKey = ref("");
const xlmBalance = ref(0);
const tkgBalance = ref(0);
const amountInput = ref("");
const quote = ref(null);
const quoteLoading = ref(false);
const buyLoading = ref(false);
const slippageBps = ref(100);
const meta = ref({ min_xlm: 1 });
const isTestnet = ref(false);

let quoteTimer = null;

const slippagePercent = computed(() => (slippageBps.value / 100).toFixed(1));

function close() {
    emit("update:modelValue", false);
}

function fmtNum(value, digits = 4) {
    const n = Number(value);
    if (!Number.isFinite(n)) return "0";
    return n.toLocaleString(undefined, {
        minimumFractionDigits: 0,
        maximumFractionDigits: digits,
    });
}

function readPublicKey() {
    return getCookie("public_key") || localStorage.getItem("public_key") || "";
}

function requestWalletConnect() {
    close();
    emit("open-wallet");
}

async function refreshWallet() {
    publicKey.value = readPublicKey();
    if (!publicKey.value) {
        xlmBalance.value = 0;
        tkgBalance.value = 0;
        return;
    }

    xlmBalance.value = await checkXlmBalance(publicKey.value);
    tkgBalance.value = await checkTkgBalance(publicKey.value);
}

function scheduleQuote() {
    if (quoteTimer) clearTimeout(quoteTimer);
    quoteTimer = setTimeout(fetchQuote, 400);
}

async function fetchQuote() {
    const amount = Number(amountInput.value);
    if (!Number.isFinite(amount) || amount <= 0) {
        quote.value = null;
        return;
    }

    quoteLoading.value = true;

    try {
        const { data } = await axios.get("/api/tkg/quote", {
            params: { xlm_amount: amount },
        });

        quote.value = data.status === "success" ? data.quote : null;
    } catch {
        quote.value = null;
    } finally {
        quoteLoading.value = false;
    }
}

async function onBuy() {
    if (!publicKey.value) {
        requestWalletConnect();
        return;
    }

    const amount = Number(amountInput.value);
    if (!Number.isFinite(amount) || amount <= 0) {
        Swal.fire({
            icon: "warning",
            title: "Enter an amount",
            text: "Choose how much XLM you want to spend.",
        });
        return;
    }

    buyLoading.value = true;
    updateLoader("Preparing purchase", "Building your swap transaction…");

    try {
        const prepareResp = await axios.post(
            "/api/tkg/buy/prepare",
            {
                public_key: publicKey.value,
                mode: "xlm",
                amount,
                slippage_bps: slippageBps.value,
            },
            { headers: apiHeaders(), withCredentials: true }
        );

        const prepareData = prepareResp.data;
        if (prepareData.status !== "success" || !prepareData.unsigned_xdr) {
            throw new Error(prepareData.message || "Could not prepare transaction.");
        }

        updateLoader("Sign in wallet", "Approve the swap in your connected wallet…");

        const signedXdr = await signXdrWithActiveWallet(
            prepareData.unsigned_xdr,
            isTestnet.value
        );

        if (!signedXdr) {
            throw new Error("Wallet did not return a signed transaction.");
        }

        updateLoader("Submitting", "Sending transaction to the Stellar network…");

        const submitResp = await axios.post(
            "/api/tkg/buy/submit",
            {
                public_key: publicKey.value,
                signed_xdr: signedXdr,
            },
            { headers: apiHeaders(), withCredentials: true }
        );

        Swal.close();

        if (submitResp.data.status !== "success") {
            throw new Error(submitResp.data.message || "Transaction submission failed.");
        }

        const txId = submitResp.data.transaction_id;
        const explorer = isTestnet.value
            ? `https://stellar.expert/explorer/testnet/tx/${txId}`
            : `https://stellar.expert/explorer/public/tx/${txId}`;

        close();

        await Swal.fire({
            icon: "success",
            title: "TKG purchased!",
            html: `Your swap was submitted successfully.<br><a href="${explorer}" target="_blank" rel="noopener">View on Stellar Expert</a>`,
        });

        amountInput.value = "";
        quote.value = null;
        await refreshWallet();
    } catch (error) {
        Swal.close();
        const msg =
            error?.response?.data?.message ||
            error?.message ||
            "Could not complete the purchase.";
        Swal.fire({ icon: "error", title: "Purchase failed", text: msg });
        console.error("[BuyTkgModal] error:", error);
    } finally {
        buyLoading.value = false;
    }
}

async function loadMeta() {
    try {
        const { data } = await axios.get("/api/tkg/meta");
        if (data.status === "success" && data.data) {
            meta.value = { ...meta.value, ...data.data };
        }
    } catch (error) {
        console.warn("[BuyTkgModal] meta load failed", error);
    }
}

async function initModal() {
    isTestnet.value = (await getNetwork()) !== "public";
    await loadMeta();
    await refreshWallet();
}

watch(
    () => props.modelValue,
    (open) => {
        if (open) {
            initModal();
        } else if (quoteTimer) {
            clearTimeout(quoteTimer);
            quote.value = null;
        }
    }
);

onUnmounted(() => {
    if (quoteTimer) clearTimeout(quoteTimer);
});
</script>
