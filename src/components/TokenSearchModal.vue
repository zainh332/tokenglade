<template>
    <TransitionRoot appear :show="modelValue" as="template">
        <Dialog as="div" class="relative z-[9999]" @close="$emit('update:modelValue', false)">

            <!-- backdrop overlay -->
            <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0" enter-to="opacity-100"
                leave="duration-150 ease-in" leave-from="opacity-100" leave-to="opacity-0">
                <div class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 flex items-center justify-center p-4">

                <TransitionChild enter="duration-200 ease-out" enter-from="opacity-0 scale-95"
                    enter-to="opacity-100 scale-100" leave="duration-150 ease-in" leave-from="opacity-100 scale-100"
                    leave-to="opacity-0 scale-95">

                    <DialogPanel class="w-[520px] max-w-[90vw] bg-[#111827] border border-[rgba(148,163,184,0.16)] rounded-[25px] overflow-hidden shadow-2xl">

                        <!-- Header block -->
                        <div class="bg-[#151D2D] border-b border-[rgba(148,163,184,0.16)] px-6 py-4 flex items-center justify-between">
                            <DialogTitle class="text-sm font-extrabold text-white flex items-center gap-2 font-mono uppercase tracking-wider">
                                🔍 Search Stellar Token
                            </DialogTitle>
                            <button @click="$emit('update:modelValue', false)" class="text-slate-400 hover:text-white transition focus:outline-none">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6">
                            <p class="mb-4 text-xs text-slate-400 font-medium">
                                Look up active assets on the Stellar network by entering their asset code symbol.
                            </p>

                            <!-- Asset Input Field -->
                            <div class="space-y-1.5">
                                <label for="asset_code_search" class="block text-[10px] font-bold uppercase tracking-wider text-slate-400 font-mono">
                                    Stellar Asset Code
                                </label>
                                <input 
                                    v-model="assetCodeInput" 
                                    @keyup.enter="searchAssets" 
                                    type="text" 
                                    id="asset_code_search"
                                    placeholder="e.g. TKG, XLM, USDC"
                                    class="w-full px-3.5 py-2.5 bg-[#182235] border border-[rgba(148,163,184,0.16)] text-white placeholder-slate-500 rounded-xl focus:outline-none focus:ring-1 focus:ring-cyan-500 transition font-mono text-sm" 
                                />
                            </div>

                            <!-- Assets list dropdown -->
                            <div v-if="assets.length" class="mt-4 bg-[#0E131C] border border-[rgba(148,163,184,0.16)] rounded-xl max-h-[220px] overflow-y-auto custom-scrollbar divide-y divide-[rgba(148,163,184,0.12)]">
                                <div v-for="asset in assets" :key="`${asset.asset_code}_${asset.asset_issuer}`"
                                    @click="selectAsset(asset)"
                                    class="p-4 cursor-pointer hover:bg-[#182235]/65 transition duration-150">
                                    <div class="flex items-center gap-1.5 font-bold text-sm text-white">
                                        <span class="font-mono uppercase">{{ asset.asset_code }}</span>
                                        <img v-if="asset.is_verified" :src="verified" alt="Verified"
                                            class="flex-shrink-0 w-3.5 h-3.5" title="Verified Token" />
                                    </div>

                                    <div class="mt-1 text-[11px] font-mono break-all text-slate-400 flex flex-wrap gap-1">
                                        <span class="text-slate-500">Issuer:</span>
                                        <span class="text-slate-400 select-all">{{ asset.asset_issuer }}</span>
                                    </div>

                                    <div class="mt-2 text-[10.5px] font-mono text-cyan-400 font-semibold flex items-center gap-1">
                                        <span>●</span> Active Holders: {{ formatNumber(asset.accounts.authorized) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Error indicator -->
                            <p class="text-rose-400 font-mono text-xs mt-3 min-h-[16px] font-semibold">
                                {{ error }}
                            </p>

                            <!-- Submit action -->
                            <button 
                                :disabled="loading" 
                                @click="searchAssets" 
                                class="w-full mt-2 py-3 rounded-xl text-white font-extrabold uppercase tracking-wider text-xs bg-gradient-to-r from-purple-600 to-cyan-500 hover:opacity-95 hover:scale-[1.01] active:scale-[0.99] transition-all disabled:opacity-50 focus:outline-none"
                            >
                                {{ loading ? "Checking Horizon Ledger..." : "Search Token" }}
                            </button>
                        </div>

                    </DialogPanel>

                </TransitionChild>

            </div>

        </Dialog>
    </TransitionRoot>
</template>

<script setup>

import { ref, watch } from "vue"
import { useRouter } from "vue-router"
import axios from "axios"
import verified from "@/assets/verify.png"
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionRoot,
    TransitionChild
} from "@headlessui/vue"

defineProps({
    modelValue: Boolean
})

const emit = defineEmits(["update:modelValue"])

const router = useRouter()

const assetCodeInput = ref("")

const error = ref("")
const loading = ref(false)
const assets = ref([])

let debounceTimeout = null
let searchRequestId = 0

function formatNumber(value) {
    return new Intl.NumberFormat("en-US", {
        maximumFractionDigits: 0
    }).format(value || 0);
}

async function enrichVerificationStatus(assetList) {
    const issuers = assetList.map((asset) => asset.asset_issuer)

    if (!issuers.length) {
        return
    }

    try {
        const { data } = await axios.post("/api/token/check-verification", {
            issuers,
        })

        for (const asset of assetList) {
            asset.is_verified = data.verified?.[asset.asset_issuer] === true
        }
    } catch {
        for (const asset of assetList) {
            asset.is_verified = false
        }
    }
}

async function searchAssets() {
    error.value = ""

    const rawInput = assetCodeInput.value.trim()

    if (!rawInput) {
        assets.value = []
        return
    }

    const requestId = ++searchRequestId
    loading.value = true

    try {
        const queries = [rawInput]

        if (rawInput !== rawInput.toUpperCase()) {
            queries.push(rawInput.toUpperCase())
        }

        let allRecords = []

        for (const code of queries) {
            const res = await fetch(
                `https://horizon.stellar.org/assets?asset_code=${encodeURIComponent(code)}&limit=200`
            )

            const data = await res.json()

            if (data._embedded?.records?.length) {
                allRecords = [...allRecords, ...data._embedded.records]
            }
        }

        if (requestId !== searchRequestId) {
            return
        }

        if (!allRecords.length) {
            assets.value = []
            error.value = "No token detected on Stellar ledger"
            return
        }

        const uniqueAssets = Object.values(
            allRecords.reduce((acc, asset) => {
                const key = `${asset.asset_code}_${asset.asset_issuer}`
                acc[key] = asset
                return acc
            }, {})
        )

        const sortedAssets = uniqueAssets.sort((a, b) => {
            if (b.num_liquidity_pools !== a.num_liquidity_pools) {
                return b.num_liquidity_pools - a.num_liquidity_pools
            }

            return b.accounts.authorized - a.accounts.authorized
        })

        await enrichVerificationStatus(sortedAssets)

        if (requestId !== searchRequestId) {
            return
        }

        assets.value = sortedAssets
        error.value = ""

    } catch (e) {
        if (requestId !== searchRequestId) {
            return
        }

        error.value = "Failed to communicate with Horizon ledger"
        assets.value = []
    } finally {
        if (requestId === searchRequestId) {
            loading.value = false
        }
    }
}

watch(assetCodeInput, (newValue) => {
    clearTimeout(debounceTimeout)

    if (!newValue.trim()) {
        assets.value = []
        error.value = ""
        return
    }

    debounceTimeout = setTimeout(() => {
        searchAssets()
    }, 500)
})

function selectAsset(asset) {
    router.push({
        path: "/token-insight",
        query: {
            asset_code: asset.asset_code,
            issuer: asset.asset_issuer
        }
    })

    assetCodeInput.value = ""
    assets.value = []
    emit("update:modelValue", false)
}
</script>

<style scoped>
/* Scoped custom scrollbar for asset drop-down */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #090d16;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #28313f;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #3c495e;
}
</style>