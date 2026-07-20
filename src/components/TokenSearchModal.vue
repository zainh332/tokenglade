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
                                        <span v-if="getAssetName(asset)" class="font-sans font-semibold text-white">
                                            {{ getAssetName(asset) }} <span class="text-slate-400 font-mono font-normal text-xs">· {{ asset.asset_code }}</span>
                                        </span>
                                        <span v-else class="font-mono uppercase text-white">{{ asset.asset_code }}</span>
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
            if (data.names?.[asset.asset_issuer]) {
                asset.name = data.names[asset.asset_issuer]
            }
        }
    } catch {
        for (const asset of assetList) {
            asset.is_verified = false
        }
    }
}

function getAssetName(asset) {
    if (asset.name) return asset.name
    if (asset.asset_name) return asset.asset_name
    if (asset.toml_info?.name) return asset.toml_info.name
    if (asset.toml_info?.orgName) return asset.toml_info.orgName
    if (asset.org_name) return asset.org_name
    return null
}

async function fetchMissingAssetNames(assetList) {
    const missing = assetList.filter((a) => !a.name)
    if (!missing.length) return

    await Promise.all(
        missing.slice(0, 15).map(async (asset) => {
            try {
                const res = await fetch(
                    `https://api.stellar.expert/explorer/public/asset/${asset.asset_code}-${asset.asset_issuer}`
                )
                const data = await res.json()
                if (data.toml_info?.name) {
                    asset.name = data.toml_info.name
                } else if (data.toml_info?.orgName) {
                    asset.name = data.toml_info.orgName
                }
            } catch {}
        })
    )
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

        // 1. Query Horizon by asset code
        for (const code of queries) {
            try {
                const res = await fetch(
                    `https://horizon.stellar.org/assets?asset_code=${encodeURIComponent(code)}&limit=200`
                )
                const data = await res.json()
                if (data._embedded?.records?.length) {
                    allRecords = [...allRecords, ...data._embedded.records]
                }
            } catch {}
        }

        // 2. Query backend database by token name or symbol
        try {
            const { data } = await axios.get(`/api/token/search?q=${encodeURIComponent(rawInput)}`)
            if (data?.tokens?.length) {
                for (const tokenItem of data.tokens) {
                    allRecords.push({
                        asset_code: tokenItem.asset_code,
                        asset_issuer: tokenItem.asset_issuer,
                        name: tokenItem.name,
                        is_verified: true,
                        accounts: tokenItem.accounts || { authorized: 0 }
                    })
                }
            }
        } catch {}

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
                const key = `${asset.asset_code.toUpperCase()}_${asset.asset_issuer}`
                const existing = acc[key]
                if (!existing) {
                    acc[key] = asset
                } else {
                    acc[key] = {
                        ...existing,
                        ...asset,
                        name: asset.name || existing.name,
                        is_verified: asset.is_verified || existing.is_verified,
                        accounts: (asset.accounts?.authorized || 0) >= (existing.accounts?.authorized || 0) ? asset.accounts : existing.accounts
                    }
                }
                return acc
            }, {})
        )

        // 3. Fetch missing holder stats from Horizon for database search matches
        for (const asset of uniqueAssets) {
            if (!asset.accounts || asset.accounts.authorized === 0) {
                try {
                    const res = await fetch(
                        `https://horizon.stellar.org/assets?asset_code=${asset.asset_code}&asset_issuer=${asset.asset_issuer}`
                    )
                    const hData = await res.json()
                    if (hData._embedded?.records?.length) {
                        asset.accounts = hData._embedded.records[0].accounts
                        asset.num_liquidity_pools = hData._embedded.records[0].num_liquidity_pools
                        if (hData._embedded.records[0].domain) {
                            asset.domain = hData._embedded.records[0].domain
                        }
                    }
                } catch {}
            }
        }

        // 4. Enrich verification status and missing asset metadata
        await enrichVerificationStatus(uniqueAssets)
        await fetchMissingAssetNames(uniqueAssets)

        if (requestId !== searchRequestId) {
            return
        }

        // 2. Sort by verified status first, then number of liquidity pools, then active holders
        const sortedAssets = uniqueAssets.sort((a, b) => {
            // Sort by verification status first (verified tokens top)
            if (a.is_verified !== b.is_verified) {
                return (b.is_verified ? 1 : 0) - (a.is_verified ? 1 : 0)
            }
            // Sort by number of liquidity pools second
            if (b.num_liquidity_pools !== a.num_liquidity_pools) {
                return b.num_liquidity_pools - a.num_liquidity_pools
            }
            // Sort by authorized account holders third
            return b.accounts.authorized - a.accounts.authorized
        })

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